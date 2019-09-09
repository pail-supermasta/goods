<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 9:55
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';

use Avaks\Goods\Order;
use Avaks\Goods\Sticker;
use Avaks\MS\Orders;
use Avaks\MS\OrderMS;

define('BOX_CODE_ID', '1231'); //TEST
//define('BOX_CODE_ID','608'); //PRODUCTION
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

date_default_timezone_set('Europe/Moscow');




/*FUNCTION INIT BEGINS*/

/*ШАГ 2 НАЧАЛО из описания - подтверждение или отмена*/

/*№4 получить детали по заказам в выборке №1 из Goods API*/

function getOrderDetails(Order $goods, array $orderMSInWork)
{

    $notFoundInMS = array();
    $foundInMS = array();

    $goodsOrderDetails = $goods->getOrder($orderMSInWork['name']);

    $goodsOrderItems = $goodsOrderDetails['items'];

    /*сравнить товары выборки №2 и выборки №1*/
    foreach ($goodsOrderItems as $goodsOrderItem) {
        /*есть в заказе в МС*/
        if (!in_array($goodsOrderItem['offerId'], $orderMSInWork['positions'])) {

            $notFoundInMS[$goodsOrderItem['itemIndex']] = $goodsOrderItem['offerId'];
            $pos = array_search($goodsOrderItem['offerId'], $orderMSInWork['positions']);
            unset($orderMSInWork['positions'][$pos]);
        } else {

            $pos = array_search($goodsOrderItem['offerId'], $orderMSInWork['positions']);
            unset($orderMSInWork['positions'][$pos]);
            $foundInMS[$goodsOrderItem['itemIndex']] = $goodsOrderItem['offerId'];
        }
        /*количество найденной позиции совпадает с Гудс*/
        /*Гудс отдает кол-во товара № как отдельный лот с кол-вом 1*/
        /*МС сохраняет кол-во товара № как отдельный лот с кол-вом 1*/

    }

    /*run order confirmation*/
    $res = setOrderConfimation($goods, $orderMSInWork['name'], $notFoundInMS, $foundInMS);
    var_dump($res);


}

/*№5 подтвердить или отклонить позиции в Goods*/
function setOrderConfimation(Order $goods, $orderID, array $notFoundInMS, array $foundInMS)
{

    $toReturn = array();
    /*если все позиции совпадают то отправить setConfirm*/
    /*нет ненайденных в МС*/
    if (sizeof($notFoundInMS) == 0) {
        /*assemble $confirmAll*/
        $shipment = array();
        foreach ($foundInMS as $itemIndex => $offerId) {
            $shipment[] = array('itemIndex' => $itemIndex, 'offerId' => $offerId);
        }
        $confirmAll['shipments'][] = array('shipmentId' => $orderID, 'orderCode' => "", 'items' => $shipment);

        $resConfirmAll = $goods->setConfirm($confirmAll);
        $toReturn = $resConfirmAll;
    }

    /*если все позиции не совпадают то отправить setReject*/
    /*нет найденных в МС*/
    elseif ($foundInMS == 0) {
        /*assemble $rejectAll*/
        $shipment = array();
        foreach ($notFoundInMS as $itemIndex => $offerId) {
            $shipment[] = array('itemIndex' => $itemIndex, 'offerId' => $offerId);
        }
        $rejectAll['shipments'][] = array('shipmentId' => $orderID, 'orderCode' => "", 'items' => $shipment);

        $resRejectAll = $goods->setReject($rejectAll);
        $toReturn = $resRejectAll;
    }

    /*если часть позиций совпадают то отправить setConfirmLots и setRejectLots*/
    /*часть найдена и часть не найдена*/
    elseif ($notFoundInMS != 0 && $foundInMS != 0) {

        /*assemble $confirmSome*/
        $shipment = array();
        foreach ($foundInMS as $itemIndex => $offerId) {
            $shipment[] = array('itemIndex' => $itemIndex, 'offerId' => $offerId);
        }
        $confirmSome['shipments'][] = array('shipmentId' => $orderID, 'orderCode' => "", 'items' => $shipment);

        /*assemble $rejectSome*/
        $shipment = array();
        foreach ($notFoundInMS as $itemIndex => $offerId) {
            $shipment[] = array('itemIndex' => $itemIndex, 'offerId' => $offerId);
        }
        $rejectSome['shipments'][] = array('shipmentId' => $orderID, 'orderCode' => "", 'items' => $shipment);

        $resConfirmSome = $goods->setConfirmLots($confirmSome);
        $resRejectSome = $goods->setRejectLots($rejectSome);
//        $toReturn = 'confirmSome' . json_encode($confirmSome) . 'rejectSome' . json_encode($rejectSome);
        $toReturn = array($resConfirmSome, $resRejectSome);
    }
    return $toReturn;
}

/*ШАГ 2 КОНЕЦ*/


/*ШАГ 4 НАЧАЛО из описания - комплектация заказов*/


/*№2 для каждого заказа - установить в Гудс заказа скомплектован */
/**
 * @param Order $goods
 * @param $toPack
 */
function setOrderPacking(Order $goods,$toPack)
{
    foreach ((array)$toPack as $orderToPackId) {

        /*запросить детали заказа из Гудс*/
        $orderToPackDetails = $goods->getOrder($orderToPackId);

        $shipment = array();
        $boxes[] = array('boxIndex' => 1, 'boxCode' => '1231*' . $orderToPackId . '*1');
        foreach ($orderToPackDetails['items'] as $item) {
            $shipment[] = array('itemIndex' => (int)$item['itemIndex'],
                'quantity' => $item['quantity'],
                'boxes' => $boxes);
        }
        $orderToPack['shipments'][] = array('shipmentId' => $orderToPackId,
            'orderCode' => $orderToPackId,
            'items' => $shipment);
        $res = $goods->setPacking($orderToPack);
    }
}


/*ШАГ 4 КОНЕЦ*/


/*ШАГ 4 НАЧАЛО из описания - наклеивание этикетки*/

/*№4 для каждого заказа - печать этикетки и добавление файла в заказ в МС + Отгрузка заказа */

function uploadSticker($orderPacked)
{
    $sticker = new Sticker();

    /*получить стикер из Гудс для заказа*/
    $pdfCode = $sticker->printPdf($orderPacked, BOX_CODE_ID);
    $orderMS = new OrderMS('', $orderPacked);
    $orderDetails = $orderMS->getByName();
    $orderMS->id = $orderDetails['id'];

    /*получить Статус заказа в МС*/
    preg_match(ID_REGEXP, $orderDetails['state']['meta']['href'], $matches);
    $state_id = $matches[0];
    $orderMS->state = $state_id;


    /*записать в заказ МС файл маркировочного листа*/
    $put_data = array();
    $attribute = array();

//    если надо получить из файла
//    $content = base64_encode(file_get_contents("pdf/sticker-files/Маркировка $orderMS->name.pdf"));
    $content = base64_encode($pdfCode);
    $attribute['id'] = 'b8a8f6d6-5782-11e8-9ff4-34e800181bf6';
    $attribute['file']['filename'] = "Маркировка $orderMS->name.pdf";
    $attribute['file']['content'] = $content;
    $put_data['attributes'][] = $attribute;

    $final = json_encode($put_data);
    $orderMS->setSticker($final);


    /*Отгрузить заказ*/
    $orderMS->setToPack();


}

/*ШАГ 4 КОНЕЦ*/


/*ШАГ 6 НАЧАЛО из описания*/

function sendOrdersToGoods(Order $goods, $goodsOrdersPacked, $ordersMSOnDelivery)
{
    foreach ((array)$goodsOrdersPacked as $key => $orderToShipId) {
        foreach ((array)$ordersMSOnDelivery as $msOrder) {
            /*№2  - проверить если заказ из ГУДС в статусе Packed в МС Доставляется*/
            if (in_array($orderToShipId, $msOrder) == 1) {

                $goods->id = $orderToShipId;

                $boxes[] = array('boxIndex' => 1, 'boxCode' => '1231*' . $goods->id . '*1');


                $shippingDate = date('c');

                $orderToShip['shipments'][] = array('shipmentId' => $goods->id,
                    'boxes' => $boxes,
                    'shipping' => array('shippingDate' => $shippingDate));

                /*№3 - Отгрузить заказ в Гудс*/
                $res = $goods->setShipping($orderToShip);
            }
        }
    }
}

/*ШАГ 6 КОНЕЦ*/



function processShop($token)
{

    $ordersMS = new Orders();

    $goods = new Order($token);


    /*ШАГ 1 НАЧАЛО отмененные покупателем - отмена в МС*/

    /*№1 получить заказы гудса из МС в статусе Отменен*/
    $ordersMSInCancel = $ordersMS->getInCancel();
    var_dump('$ordersMS->getInCancel call'.PHP_EOL);



    /*№2 получить заказы из гудса в статусе Отменен покупателем*/
    $goodsOrdersUserCanceled = $goods->getOrdersCustomerCanceled();
    var_dump('$goods->getOrdersCustomerCanceled'.PHP_EOL);


    foreach ((array)$goodsOrdersUserCanceled as $key => $orderToCancelId) {
        foreach ((array)$ordersMSInCancel as $msOrder) {
            /*№3  - проверить если заказ из ГУДС в статусе Packed в МС Доставляется*/
            if (in_array($orderToCancelId, $msOrder) != 1) {
                $orderMS = new OrderMS('', $orderToCancelId, '');
                $orderMSDetails = $orderMS->getByName();
                if (isset($orderMSDetails['id'])) {
                    $orderMS->id = $orderMSDetails['id'];
                    $orderMS->setCanceled();
                    $message = "Заказ №$orderToCancelId ОТМЕНЕН покупателем";
                    echo $message;
                }

            }
        }
    }

    /*ШАГ 1 КОНЕЦ отмененные покупателем - отмена в МС*/


    /*ШАГ 2 НАЧАЛО из описания - подтверждение или отмена*/

    /*№1 получить заказы гудса из МС в статусе В работе*/
    $ordersMSInWork = $ordersMS->getInWork();
    var_dump('$ordersMS->getInWork call'.PHP_EOL);

    /*№2 получить заказы из гудса в статусе Ожидает подтверждения*/
    $goodsOrdersNew = $goods->getOrdersNew();
    var_dump('$goods->getOrdersNew call'.PHP_EOL);



    foreach ((array)$goodsOrdersNew as $key => $orderToConfirmId) {
        foreach ((array)$ordersMSInWork as $msOrder) {
            /*№3  - проверить если заказ из ГУДС в статусе Packed в МС Доставляется*/
            if (in_array($orderToConfirmId, $msOrder) == 1) {
                getOrderDetails($goods, $msOrder);
                var_dump('getOrderDetails call'.PHP_EOL);
            }
        }
    }

    /*ШАГ 2 КОНЕЦ*/


    /*ШАГ 3 НАЧАЛО из описания - убедиться что заказы подтвердились в Гудс и в МС*/

    /*№1 получить заказы гудса в статусе CONFIRMED */

    $resOrdersConfirmed = $goods->getOrdersConfirmed();
    var_dump('$goods->getOrdersConfirmed call'.PHP_EOL);


    /*Что то в МС делаем если CONFIRMED ?*/
    /*ШАГ 3 КОНЕЦ*/


    /*ШАГ 4 НАЧАЛО из описания - комплектация заказов*/

    /*№1 получить заказы гудса в статусе CONFIRMED */

    $toPack = $resOrdersConfirmed;
//$res = $goods->setPacking();

    /*№2 для каждого заказа - установить в Гудс заказа скомплектован */

    setOrderPacking($goods, $toPack);
    var_dump('setOrderPacking call'.PHP_EOL);

    /*ШАГ 4 КОНЕЦ*/


    /*ШАГ 4 НАЧАЛО из описания - наклеивание этикетки*/

    /*№3  - получить список упакованных заказов из Гудс*/
    $goodsOrdersPacked = $goods->getOrdersPacked();
    var_dump('$goods->getOrdersPacked call'.PHP_EOL);

    foreach ((array)$goodsOrdersPacked as $orderPacked) {
        uploadSticker($orderPacked);
        var_dump('uploadSticker call'.PHP_EOL);
    }

    /*ШАГ 4 КОНЕЦ*/


    /*ШАГ 6 НАЧАЛО из описания*/

    /*№1  - получить список заказов в статусе Доставляется из МС*/
    $ordersMSOnDelivery = $ordersMS->getOnDelivery();
    var_dump('$ordersMS->getOnDelivery call'.PHP_EOL);

    sendOrdersToGoods($goods, $goodsOrdersPacked, $ordersMSOnDelivery);
    var_dump('sendOrdersToGoods call'.PHP_EOL);
    /*ШАГ 6 КОНЕЦ*/

}


/*FUNCTION INIT ENDS*/













