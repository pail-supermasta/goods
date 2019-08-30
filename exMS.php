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
use \Avaks\MS\OrderMS;

/*получить заказы в работе готовые к передаче в гудс для статуса CONFIRMED*/
/*получить номера тваров и номер заказа*/
//$orderMS = new OrderMS('e6ae45cf-ca68-11e9-9ff4-34e8000982f8');
$orderMS = new OrderMS('9083c131-cb2e-11e9-9ff4-31500008994c');


//$res = $orderMS->getByName();
/*$ordersMS = new Orders();
$res = $ordersMS->getInWork();

var_dump($res);*/




$put_data = array();
$attribute = array();

$content = base64_encode(file_get_contents('pdf/sticker-files/Маркировка 863018218.pdf'));
$attribute['id'] = 'b8a8f6d6-5782-11e8-9ff4-34e800181bf6';
$attribute['file']['filename'] = 'Маркировка 863018218.pdf';
$attribute['file']['content'] = $content;

$put_data['attributes'][] = $attribute;


$final = json_encode($put_data);
//$res = $orderMS->setSticker($final);
$res = $orderMS->setSticker($final);
