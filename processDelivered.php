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
use Avaks\MS\Orders;

require_once 'class/Telegram.php';



$goodsTokens = array(
    '608' => '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D',//novinki - amaze
    '9308' => 'B58874A5-7AE5-452A-8C31-F0DDEA37AA56',//АВАКС
//    '2998' => 'C12405BF-01CB-4A6C-A41E-0E179EF00F54', //nezabudka - firdus
//    'НОВИНКИ test' => '6881430B-882F-4C4F-8DCA-14FDAFEBAFEC'
);


/*ШАГ 0 НАЧАЛО доставленные заказы - ставим Доставлен в МС*/





function processDelivered($boxID, $token, $organization)
{
    $ordersMS = new Orders();
    $MSOrdersDelivering = $ordersMS->getDeliveringMonth($organization);


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
    switch ($id) {
        case "608":
            //novink
            $organization = "07bbe005-8b17-11e7-7a34-5acf0019232a";
            break;
        case "9308":
            //avaks
            $organization = "b2c9e371-1b98-11e6-7a69-93a700176cab";//OOО "АВАКС"
            break;
        case "2998":
            // NZ
            $organization = "326d65ca-75c5-11e5-7a40-e8970013991b";
            break;
    }

    /*№1 получить заказы гудса из МС в статусе Доставлен*/

    processDelivered($shop->id, $shop->token, $organization);
}