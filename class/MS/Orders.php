<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 12:19
 */

namespace Avaks\MS;


use Avaks\SQL\AvaksSQL;
use Avaks\MS\Products;
use Avaks\MS\MSSync;


use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use MongoDB\Client;



class Orders
{
    public function getNew()
    {

        $collection = (new MSSync())->MSSync;



        echo 'getNew' . PHP_EOL;

        $cursor = $collection->customerorder->find([
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327bfd05-75c5-11e5-7a40-e89700139935',
            'deleted' => null
        ]);

        $ordersNew = array();
        foreach ($cursor as $document) {
            $orderNew['id'] = $document['id'];
            $orderNew['name'] = $document['name'];
            $orderNew['description'] = $document['description'];
            $ordersNew[] = $orderNew;
        }
        var_dump($ordersNew);

        return $ordersNew;

    }

    public function getInWork()
    {

        $collection = (new MSSync())->MSSync;

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

        $cursor = $collection->customerorder->find([
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

        $collection = (new MSSync())->MSSync;;

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


        $cursor = $collection->customerorder->find([
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

    public function getDeliveringMonth($organization){

        $collection = (new MSSync())->MSSync;

        $regex = new Regex("GOODS1364895");


        date_default_timezone_set('Europe/Moscow');
        /*14 days*/
        $offsetNow = 768 * 60 * 60;
        $monthAgo = strtotime(gmdate("Y-m-d")) - $offsetNow;
        $monthAgo = date("Y-m-d H:i:s", $monthAgo);
        echo $monthAgo;


        $todayDay = strtotime(gmdate("Y-m-d")) + 1;
        $todayDay = date("Y-m-d H:i:s", $todayDay);
        echo $todayDay;


        $cursor = $collection->customerorder->find([
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327c03c6-75c5-11e5-7a40-e89700139938',
            '_organization' => "$organization",
            'deleted' => null,
            'description' => $regex,
            'moment' => [
                '$gte' => $monthAgo,
                '$lte' => $todayDay
            ]
        ]);

        $ordersDeliveringMonth = array();
        foreach ($cursor as $document) {
            $orderDelivering['id'] = $document['_id'];
            $orderDelivering['name'] = $document['name'];
            $orderDelivering['moment'] = $document['moment'];
            $ordersDeliveringMonth[] = $orderDelivering;
        }
//        var_dump($ordersDeliveringMonth);



        return $ordersDeliveringMonth;
    }

    public function getInCancel()
    {

        $collection = (new MSSync())->MSSync;

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

        $cursor = $collection->customerorder->find([
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