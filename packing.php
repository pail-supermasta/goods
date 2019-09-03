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

$ordersMS = new Orders();

$goods = new Order();


/*ШАГ 3 НАЧАЛО из описания - убедиться что заказы подтвердились в Гудс и в МС*/

/*№1 получить заказы гудса в статусе CONFIRMED */


$resOrdersConfirmed = $goods->getOrdersConfirmed();


/*Что то в МС делаем если CONFIRMED ?*/
/*ШАГ 3 КОНЕЦ*/


/*ШАГ 4 НАЧАЛО из описания - комплектация заказов*/

/*№1 получить заказы гудса в статусе CONFIRMED */

$toPack = $resOrdersConfirmed;
//$res = $goods->setPacking();


/*№2 для каждого заказа - установить в Гудс заказа скомплектован */

/**
 * @param Order $goods
 * @param $toPack
 */
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

setOrderPacking($goods, $toPack);




/*ШАГ 4 КОНЕЦ*/









