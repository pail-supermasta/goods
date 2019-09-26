<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.09.2019
 * Time: 17:08
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once '../vendor/autoload.php';

use Avaks\Goods\Order;
use Avaks\Goods\Sticker;
use Avaks\MS\Orders;
use Avaks\MS\OrderMS;

//define('BOX_CODE_ID', '1231'); //TEST
//define('BOX_CODE_ID','608'); //PRODUCTION
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

date_default_timezone_set('Europe/Moscow');


function processShop()
{

    $ordersMS = new Orders();

    //

    $ordersMSNew = $ordersMS->getNew();

    foreach ($ordersMSNew as $orderMSNew) {

        $orderMS = new OrderMS($orderMSNew['id'], $orderMSNew['name'], '');
        $oldDescription = $orderMSNew['description'];
        var_dump($orderMS->setInWork($oldDescription));
    }


    //


}

processShop();


