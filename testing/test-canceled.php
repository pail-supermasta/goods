<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 25.09.2019
 * Time: 14:21
 */

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'vendor/autoload.php';

//$ordersMS = new \Avaks\MS\Orders();
$good = new \Avaks\Goods\Order();
$good->shopToken = 'C12405BF-01CB-4A6C-A41E-0E179EF00F54';
/*$inCancel = $ordersMS->getInCancel();
var_dump($inCancel);*/
$resp = $good->getOrder('909125127');

$arr = array("shipmentDateFrom"=>"2019-10-03T09:00:00+03:00");
$date = date("Y-m-d H:i:s",strtotime($resp['shipmentDateFrom']));
echo $date;




