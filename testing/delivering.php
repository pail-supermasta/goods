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


$goodsOrdersPacked = $goods->getOrdersPacked();


/*ШАГ 6 НАЧАЛО из описания*/


/*№1  - получить список заказов в статусе Доставляется из МС*/
$ordersMSOnDelivery = $ordersMS->getOnDelivery();

function sendOrdersToGoods(Order $goods, $goodsOrdersPacked, $ordersMSOnDelivery)
{
    foreach ($goodsOrdersPacked as $key => $orderToShipId) {
        foreach ($ordersMSOnDelivery as $msOrder) {
            /*№2  - проверить если заказ из ГУДС в статусе Packed в МС Доставляется*/
            if (in_array($orderToShipId, $msOrder) == 1) {

                $goods->id = $orderToShipId;

                $boxes[] = array('boxIndex' => 1, 'boxCode' => '1231*' . $goods->id . '*1');

                date_default_timezone_set('Europe/Moscow');
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

sendOrdersToGoods($goods, $goodsOrdersPacked, $ordersMSOnDelivery);
/*ШАГ 6 КОНЕЦ*/














