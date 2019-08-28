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

define('BOX_CODE_ID', '1231'); //TEST
//define('BOX_CODE_ID','608'); //PRODUCTION

$ordersMS = new Orders();

$goods = new Order();

/*ШАГ 2 из описания - подтверждение или отмена*/

/*№1 получить заказы гудса из МС в статусе В работе*/
//$ordersMSInWork = $ordersMS->getInWork();

/*№2 получить детали по заказам в выборке №1 из Goods API*/

function getOrdersDetails(array $ordersMSInWork)
{
    $goods = new Order();
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
        $res = setOrderConfimation($orderMSInWork['name'], $notFoundInMS, $foundInMS);
        var_dump($res);

    }

}

/*№3 подтвердить или отклонить позиции в Goods*/
function setOrderConfimation($orderID, array $notFoundInMS, array $foundInMS)
{
    $goods = new Order();
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


//getOrdersDetails($ordersMSInWork);


/*ШАГ 3 из описания - убедиться что заказы подтвердились в Гудс и в МС*/

/*№1 получить заказы гудса в статусе CONFIRMED */


$resOrdersConfirmed = $goods->getOrdersConfirmed();


/*Что то в МС делаем если CONFIRMED ?*/


/*ШАГ 4 из описания - комплектация заказов и наклеивание этикетки*/

/*№1 получить заказы гудса в статусе CONFIRMED */

$toPack = $resOrdersConfirmed;
//$res = $goods->setPacking();
var_dump($toPack);


/*№2 для каждого заказа - установить в Гудс заказа скомплектован */


function setOrderPacking($toPack)
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

setOrderPacking($toPack);


/*№3 для каждого заказа - печать этикетки и добавление файла в заказ в МС */

$sticker = new Sticker();
foreach ($resOrdersConfirmed as $orderConfirmed) {
//    $res = $sticker->printPdf($orderConfirmed,BOX_CODE_ID);
}












