<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 12:19
 */

namespace Avaks\MS;


use Avaks\SQL\AvaksSQL;
//use Avaks\MS\OrderMS;
use Avaks\MS\Products;


use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use MongoDB\Client;

//require_once "vendor/autoload.php";


class Orders
{
    public function getNew()
    {

        $collection = (new Client('mongodb://admin:kmNqjyDM8o7U@62.109.5.225/mysales?authSource=admin'))->mysales->customerorders;

        date_default_timezone_set('Europe/Moscow');
        $epochNow = round(microtime(true) * 1000);

        $offsetNow = (168 + 3) * 60 * 60;
        $now = strtotime(gmdate("Y-m-d")) - $offsetNow;
        $sevenDaysAgo = $now * 1000;

        // less than
        $dateLess = new UTCDateTime($epochNow);

        //more than
        $dateMore = new UTCDatetime($sevenDaysAgo);

        echo 'getNew' . PHP_EOL;

        $cursor = $collection->find([
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327bfd05-75c5-11e5-7a40-e89700139935',
            'deleted' => null,
//            'moment' => [
//                '$gte' => $dateMore,
//                '$lte' => $dateLess
//            ]
        ]);

        $ordersNew = array();
        foreach ($cursor as $document) {
            $orderNew['id'] = $document['uuid'];
            $orderNew['name'] = $document['name'];
            $orderNew['description'] = $document['description'];
            $ordersNew[] = $orderNew;
        }
        var_dump($ordersNew);

        return $ordersNew;

    }

    public function getInWork()
    {
        $collection = (new Client('mongodb://admin:kmNqjyDM8o7U@62.109.5.225/mysales?authSource=admin'))->mysales->customerorders;

        $regex = new Regex("GOODS1364895");


        date_default_timezone_set('Europe/Moscow');
        $epochNow = round(microtime(true) * 1000);

        $offsetNow = (168 + 3) * 60 * 60;
        $now = strtotime(gmdate("Y-m-d")) - $offsetNow;
        $sevenDaysAgo = $now * 1000;

        // less than
        $dateLess = new UTCDateTime($epochNow);

        //more than
        $dateMore = new UTCDatetime($sevenDaysAgo);

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
            $orderByState['name'] = $item['name'];
            $products = array();
            foreach ($item['positions'] as $position) {
                $position = json_decode($position, true);
                $products[] = AvaksSQL::selectProductById($position['id']);
            }
            $orderByState['positions'] = $products;
            $ordersWork[] = $orderByState;
        }
        var_dump($ordersWork);
        return $ordersWork;
    }

    public function getOnDelivery()
    {

        $collection = (new Client('mongodb://admin:kmNqjyDM8o7U@62.109.5.225/mysales?authSource=admin'))->mysales->customerorders;

        $regex = new Regex("GOODS1364895");


        date_default_timezone_set('Europe/Moscow');
        $epochNow = round(microtime(true) * 1000);

        $offsetNow = (168 + 3) * 60 * 60;
        $now = strtotime(gmdate("Y-m-d")) - $offsetNow;
        $sevenDaysAgo = $now * 1000;

        // less than
        $dateLess = new UTCDateTime($epochNow);

        //more than
        $dateMore = new UTCDatetime($sevenDaysAgo);

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


        $ordersDelivery = array();

        foreach ($cursor as $item) {
            $orderByState['name'] = $item['name'];


            $products = array();

            foreach ($item['positions'] as $position) {
                $position = json_decode($position, true);
                $products[] = AvaksSQL::selectProductById($position['id']);
            }
            $orderByState['positions'] = $products;

            $ordersDelivery[] = $orderByState;

        }
        var_dump($ordersDelivery);
        return $ordersDelivery;
    }

    public function getInCancel()
    {

        $collection = (new Client('mongodb://admin:kmNqjyDM8o7U@62.109.5.225/mysales?authSource=admin'))->mysales->customerorders;

        $regex = new Regex("GOODS1364895");


        date_default_timezone_set('Europe/Moscow');
        $epochNow = round(microtime(true) * 1000);

        $offsetNow = (168 + 3) * 60 * 60;
        $now = strtotime(gmdate("Y-m-d")) - $offsetNow;
        $sevenDaysAgo = $now * 1000;

        // less than
        $dateLess = new UTCDateTime($epochNow);

        //more than
        $dateMore = new UTCDatetime($sevenDaysAgo);

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


        $ordersCancel = array();
        foreach ($cursor as $document) {
            $orderCancel['name'] = $document['name'];
            $ordersCancel[] = $orderCancel;
        }

        var_dump($ordersCancel);
        return $ordersCancel;

    }
}