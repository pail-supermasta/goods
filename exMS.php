<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 9:51
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';


use \Avaks\MS\Orders;

/*получить заказы в работе готовые к передаче в гудс для статуса CONFIRMED*/
/*получить номера тваров и номер заказа*/
//$orderMS = new Order('b10c6d9b-c3de-11e9-9ff4-31500006cf2c');


//$res = $orderMS->getByName();
$ordersMS = new Orders();
$res = $ordersMS->getInWork();

var_dump($res);
