<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.01.2020
 * Time: 10:20
 */

require_once "vendor/autoload.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;

$collection = (new MongoDB\Client('mongodb://admin:kmNqjyDM8o7U@62.109.5.225/mysales?authSource=admin'))->mysales->customerorders;

date_default_timezone_set('Europe/Moscow');
$epochNow = round(microtime(true) * 1000);
//$offset = 168 * 60 * 60 * 1000;
//$sevenDaysAgo = $epochNow - $offset;


$offsetNow = (168 + 3) * 60 * 60;
$now = strtotime(gmdate("Y-m-d")) - $offsetNow;
$sevenDaysAgo = $now * 1000;


$regex = new Regex("GOODS1364895");

// less than
$dateLess = new UTCDateTime($epochNow);

//more than
$dateMore = new UTCDatetime($sevenDaysAgo);


//getNew
echo 'getNew' . PHP_EOL;

$cursor = $collection->find([
    '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
    '_state' => '327bfd05-75c5-11e5-7a40-e89700139935',
    'deleted' => null,
//    'description' => $regex,
    'moment' => [
        '$gte' => $dateMore,
        '$lte' => $dateLess
    ]
]);

//var_dump($cursor);
$ordersNew = array();
foreach ($cursor as $document) {
    $orderNew['id'] = $document['uuid'];
    $orderNew['name'] = $document['name'];
    $orderNew['description'] = $document['description'];
    $ordersNew[] = $orderNew;
}
var_dump($ordersNew);


//getInWork

echo 'getInWork' . PHP_EOL;


$cursor = $collection->find([
    '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
    '_state' => 'ecf45f89-f518-11e6-7a69-9711000ff0c4',
    'deleted' => null,
    'description' => $regex,
    'moment' => [
        '$gte' => $dateMore,
        '$lte' => $dateLess
    ]
]);

$ordersWork = array();

foreach ($cursor as $item) {
//    echo $item['uuid'], "\n";
    $orderByState['name'] = $item['name'];


    $products = array();

    foreach ($item['positions'] as $position) {
        $position = json_decode($position, true);
//        search index in products table by id
//        echo $position['id'];
//        $products[] = AvaksSQL::selectProductById( $position['id']);
        $products[] = $position['id'];
    }
    $orderByState['positions'] = $products;

    $ordersWork[] = $orderByState;

}
var_dump($ordersWork);


//getInCancel

echo 'getInCancel' . PHP_EOL;


$cursor = $collection->find([
    '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
    '_state' => '327c070c-75c5-11e5-7a40-e8970013993b',
    'deleted' => null,
    'description' => $regex,
    'moment' => [
        '$gte' => $dateMore,
        '$lte' => $dateLess
    ]
]);

//var_dump($cursor);

$ordersCancel = array();
foreach ($cursor as $document) {
    $orderCancel['name'] = $document['name'];
    $ordersCancel[] = $orderCancel;
}

var_dump($ordersCancel);

//getOnDelivery

echo 'getOnDelivery' . PHP_EOL;


$cursor = $collection->find([
    '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
    '_state' => '327c03c6-75c5-11e5-7a40-e89700139938',
    'deleted' => null,
    'description' => $regex,
    'moment' => [
        '$gte' => $dateMore,
        '$lte' => $dateLess
    ]
]);

//var_dump($cursor);

$ordersDelivery = array();

foreach ($cursor as $item) {
//    echo $item['uuid'], "\n";
    $orderByState['name'] = $item['name'];


    $products = array();

    foreach ($item['positions'] as $position) {
        $position = json_decode($position, true);
//        search index in products table by id
//        echo $position['id'];
//        $products[] = AvaksSQL::selectProductById( $position['id']);
        $products[] = $position['id'];
    }
    $orderByState['positions'] = $products;

    $ordersDelivery[] = $orderByState;

}
var_dump($ordersDelivery);
