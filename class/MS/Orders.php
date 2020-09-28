<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 12:19
 */

namespace Avaks\MS;


use Avaks\BackendAPI;


use MongoDB\BSON\UTCDateTime;


class Orders
{
    public function getNew()
    {

        echo 'getNew' . PHP_EOL;


        $backendAPI = new BackendAPI();
        $filter = array(
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327bfd05-75c5-11e5-7a40-e89700139935',
            'deleted' => null
        );
        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;
        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $cursor = $orderCursor['rows'];

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
        echo 'getInWork' . PHP_EOL;

        $regex = array('$regex' => "GOODS1364895");
        $backendAPI = new BackendAPI();
        $filter = array(
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => 'ecf45f89-f518-11e6-7a69-9711000ff0c4',
            'applicable'=> true,
            'description' => $regex
        );
        $data['project'] = json_encode(array(
                'name' => true,
                '_positions.id' => true,
                '_positions.type' => true
            )
        );
        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;
        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $cursor = $orderCursor['rows'];

        $ordersWork = array();
        $assortment = new Assortment();

        foreach ($cursor as $item) {
            $orderByState['name'] = $item['name'];
            $products = array();
            foreach ($item['_positions'] as $position) {
                $assortment->id = $position['id'];
                $assortment->type = $position['type'];
                $products[] = $assortment->findPosition()['rows'][0]['code'];
            }
            $orderByState['positions'] = $products;
            $ordersWork[] = $orderByState;
        }
        return $ordersWork;
    }

    public function getOnDelivery()
    {




        date_default_timezone_set('Europe/Moscow');
        $plusDay = 24 * 60 * 60;
        $tomorrow = strtotime(date("Y-m-d")) + $plusDay;

        $offsetNow = (168 + 3) * 60 * 60;
        $sevenDaysAgo = strtotime(gmdate("Y-m-d")) - $offsetNow;

        // less than
//        $dateLess = new UTCDateTime($epochNow);

        //more than
//        $dateMore = new UTCDatetime($sevenDaysAgo);

        echo 'getOnDelivery' . PHP_EOL;




        $regex = array('$regex' => "GOODS1364895");
        $backendAPI = new BackendAPI();
        $filter = array(
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327c03c6-75c5-11e5-7a40-e89700139938',
            'applicable'=> true,
            'description' => $regex,
            'moment' => [
                '$gte' => date("yy-m-d", $sevenDaysAgo),
                '$lte' => date("yy-m-d", $tomorrow),
            ]
        );
        $data['project'] = json_encode(array(
                'name' => true,
                '_positions' => true
            )
        );
        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;
        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $cursor = $orderCursor['rows'];

        $ordersDelivery = array();
        $assortment = new Assortment();

        foreach ($cursor as $item) {
            $orderByState['name'] = $item['name'];


            $products = array();

            foreach ($item['_positions'] as $position) {
                $assortment->id = $position['id'];
                $assortment->type = $position['type'];
                $products[] = $assortment->findPosition()['rows'][0]['code'];
            }
            $orderByState['positions'] = $products;

            $ordersDelivery[] = $orderByState;

        }
        var_dump($ordersDelivery);
        return $ordersDelivery;
    }

    public function getDeliveringMonth($organization)
    {




        date_default_timezone_set('Europe/Moscow');
        /*14 days*/
        $offsetNow = 768 * 60 * 60;
        $monthAgo = strtotime(gmdate("Y-m-d")) - $offsetNow;
        $monthAgo = date("Y-m-d H:i:s", $monthAgo);
        echo $monthAgo;


        $todayDay = strtotime(gmdate("Y-m-d")) + 1;
        $todayDay = date("Y-m-d H:i:s", $todayDay);
        echo $todayDay;




        $regex = array('$regex' => "GOODS1364895");
        $backendAPI = new BackendAPI();
        $filter = array(
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327c03c6-75c5-11e5-7a40-e89700139938',
            '_organization' => "$organization",
            'applicable'=> true,
            'description' => $regex,
            'moment' => [
                '$gte' => $monthAgo,
                '$lte' => $todayDay
            ]
        );
        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;
        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $cursor = $orderCursor['rows'];

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




        date_default_timezone_set('Europe/Moscow');
        $plusDay = 24 * 60 * 60;
        $tomorrow = strtotime(date("Y-m-d")) + $plusDay;

        $offsetNow = (168 + 3) * 60 * 60;
        $sevenDaysAgo = strtotime(date("Y-m-d")) - $offsetNow;

        // less than
        $dateLess = new UTCDateTime($tomorrow);

        //more than
        $dateMore = new UTCDatetime($sevenDaysAgo);


        $regex = array('$regex' => "GOODS1364895");
        $backendAPI = new BackendAPI();
        $filter = array(
            '_agent' => '64710328-2e6f-11e8-9ff4-34e8000f81c8',
            '_state' => '327c070c-75c5-11e5-7a40-e8970013993b',
            'applicable'=> true,
            'description' => $regex,
            'moment' => [
                '$gte' => date("yy-m-d", $sevenDaysAgo),
                '$lte' => date("yy-m-d", $tomorrow),
            ]
        );
        $data['project'] = json_encode(array(
                'name' => true
            )
        );
        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;
        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $cursor = $orderCursor['rows'];


        $ordersCancel = array();
        foreach ($cursor as $document) {
            $orderCancel['name'] = $document['name'];
            $ordersCancel[] = $orderCancel;
        }

        var_dump($ordersCancel);
        return $ordersCancel;

    }
}