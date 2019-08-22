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

use \Avaks\MS\Orders;
use Avaks\Goods\Order;

$ordersMS = new Orders();

/*шаг 2 из описания - подтверждение или отмена*/
/*№1 получить заказы гудса из МС*/
$ordersMSInWork = $ordersMS->getInWork();


/*№2 получить детали по заказам в выборке №1 из Goods API*/

$goods = new Order();
$notFoundInMS = array();
$foundInMS = array();
foreach ($ordersMSInWork as $orderMSInWork) {
    $goodsOrderDetails = $goods->getOrder($orderMSInWork['name']);

    $goodsOrderItems = $goodsOrderDetails['items'];

    /*сравнить товары выборки №2 и выборки №1*/
    foreach ($goodsOrderItems as $goodsOrderItem) {
        if (!in_array($goodsOrderItem['offerId'], $orderMSInWork['positions'])) {
            $notFoundInMS[] = $goodsOrderItem['offerId'];
        } else {
            $foundInMS[] = $goodsOrderItem['offerId'];
        }

    }

}


var_dump($notFoundInMS);
var_dump($foundInMS);





/*если все позиции совпадают то отправить setConfirm*/

/*если все позиции не совпадают то отправить setReject*/

/*если часть позиций совпадают то отправить setConfirmLots и setRejectLots*/
