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

$ordersMS = new Orders();

$goods = new Order();

/*ШАГ 2 НАЧАЛО из описания - подтверждение или отмена*/

/*№1 получить заказы гудса из МС в статусе В работе*/
//$ordersMSInWork = $ordersMS->getInWork();

/*№2 получить детали по заказам в выборке №1 из Goods API*/

function getOrdersDetails(Order $goods, array $ordersMSInWork)
{

    $notFoundInMS = array();
    $foundInMS = array();
    foreach ($ordersMSInWork as $orderMSInWork) {
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

}

/*№3 подтвердить или отклонить позиции в Goods*/
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

//getOrdersDetails($goods, $ordersMSInWork);


/*ШАГ 3 НАЧАЛО из описания - убедиться что заказы подтвердились в Гудс и в МС*/

/*№1 получить заказы гудса в статусе CONFIRMED */


$resOrdersConfirmed = $goods->getOrdersConfirmed();


/*Что то в МС делаем если CONFIRMED ?*/
/*ШАГ 3 КОНЕЦ*/

/*ШАГ 4 НАЧАЛО из описания - комплектация заказов и наклеивание этикетки*/

/*№1 получить заказы гудса в статусе CONFIRMED */

$toPack = $resOrdersConfirmed;
//$res = $goods->setPacking();
var_dump($toPack);


/*№2 для каждого заказа - установить в Гудс заказа скомплектован */

function setOrderPacking(Order $goods, $toPack)
{
    foreach ($toPack as $orderToPackId) {

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

//setOrderPacking($goods, $toPack);

/*№3  - получить список упакованных заказов из Гудс*/
$resOrdersPacked = $goods->getOrdersPacked();

/*№4 для каждого заказа - печать этикетки и добавление файла в заказ в МС */
$sticker = new Sticker();

foreach ($resOrdersPacked as $orderPacked) {

    /*получить стикер из Гудс для заказа*/
    $pdfCode = $sticker->printPdf($orderPacked, BOX_CODE_ID);
    $orderMS = new OrderMS('', $orderPacked);
    $orderMS->id = $orderMS->getByName()['id'];

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
    $res = $orderMS->setSticker($final);
}


/*ШАГ 4 КОНЕЦ*/


/*ШАГ 6 НАЧАЛО из описания*/

/*отгрузить все заказы которые в МС со статусом доставляются но в Гудс статус Ожидает отгрузки*/

/*ШАГ 6 КОНЕЦ*/











