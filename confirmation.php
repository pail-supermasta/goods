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
use Avaks\MS\Orders;

define('BOX_CODE_ID', '1231'); //TEST
//define('BOX_CODE_ID','608'); //PRODUCTION
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

$ordersMS = new Orders();

$goods = new Order();

/*ШАГ 2 НАЧАЛО из описания - подтверждение или отмена*/

/*№1 получить заказы гудса из МС в статусе В работе*/
$ordersMSInWork = $ordersMS->getInWork();

/*№2 получить заказы из гудса в статусе Ожидает подтверждения*/
$goodsOrdersNew = $goods->getOrdersNew();

foreach ($goodsOrdersNew as $key => $orderToConfirmId) {
    foreach ($ordersMSInWork as $msOrder) {
        /*№3  - проверить если заказ из ГУДС в статусе Packed в МС Доставляется*/
        if (in_array($orderToConfirmId, $msOrder) == 1) {
            getOrderDetails($goods, $msOrder);
        }
    }
}


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

