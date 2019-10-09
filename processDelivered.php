<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.09.2019
 * Time: 17:33
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';


use Avaks\Goods\Shop;
use Avaks\Goods\Order;
use Avaks\MS\OrderMS;
use Avaks\SQL\AvaksSQL;

require_once 'class/Telegram.php';

//Amaze 97B1BC55-189D-4EB4-91AF-4B9E9A985B3D
//Фирдус C12405BF-01CB-4A6C-A41E-0E179EF00F54


//ID	продавца:	2998
//ID	продавца:	608
$goodsTokens = array(
    '608' => '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D',//amaze
    '2998' => 'C12405BF-01CB-4A6C-A41E-0E179EF00F54', //novinki - firdus
//    'НОВИНКИ test' => '6881430B-882F-4C4F-8DCA-14FDAFEBAFEC'
);


/*ШАГ 0 НАЧАЛО доставленные заказы - ставим Доставлен в МС*/


function getMSOrdersDeliveringMonth()
{
    /*get orders from MS DB*/
    $queryOrderByState = "SELECT id,`name`,positions
                  FROM `ms_customerorder`
                  WHERE agent = '64710328-2e6f-11e8-9ff4-34e8000f81c8'
                    AND state = '327c03c6-75c5-11e5-7a40-e89700139938'
                    AND moment > CURDATE() - INTERVAL 32 DAY
                    AND deleted=''";
    $ordersByState = AvaksSQL::selectOrdersByState($queryOrderByState);

    return $ordersByState;
}


/*№1 получить заказы гудса из МС в статусе Доставлен*/


$MSOrdersDelivering = getMSOrdersDeliveringMonth();


function processDelivered($MSOrdersDelivering, $boxID, $token)
{


    $goods = new Order($boxID, $token);

    /*№2 получить заказы из гудса в статусе Доставлен покупателю*/
    $goodsOrdersDelivered = $goods->getOrdersDelivered();


    foreach ((array)$MSOrdersDelivering as $msOrder) {

        /*№3  - проверить если заказ из МС Доставляется ГУДС в Goods в статусе DELIVERED */
        if (in_array($msOrder['name'], (array)$goodsOrdersDelivered) == 1) {
            $orderMS = new OrderMS();
            $orderMS->id = $msOrder['id'];

            $setDeliveredResp = $orderMS->setDelivered();
            if (strpos($setDeliveredResp, 'обработка-ошибок') > 0) {
                telegram("error found $setDeliveredResp", '-289839597');
            } else {
                $message = "Заказ №" . $msOrder['name'] . " Доставлен покупателю" . PHP_EOL;
                echo $message;
            }

        } else {
            $message = "Заказ №" . $msOrder['name'] . " Еще не доставлен покупателю!" . PHP_EOL;
            echo $message;
        }
    }


    /*ШАГ 0 КОНЕЦ доставленные заказы - ставим Доставлен в МС*/
}

foreach ($goodsTokens as $goodsID => $goodsToken) {
    $id = $goodsID;
    $token = $goodsToken;
    $shop = new Shop($id, $token);

    processDelivered($MSOrdersDelivering, $shop->id, $shop->token);
}