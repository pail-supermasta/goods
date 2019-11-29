<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 15:57
 */


namespace Avaks\Goods;

class OrderTest
{
//    private $token = "97B1BC55-189D-4EB4-91AF-4B9E9A985B3D";
//    private $api_url = "https://partner.goods.ru/api/market/v1/";
    public $state;
    public $id;
    public $shopToken;
    public $dateFrom;
    public $shopID;
    public $shippingDate;
    public $deliveredRange;

    /**
     * GETTERS
     * @param $id
     * @return array
     */


    public function __construct($shopID = false, $token= false)
    {
        /*for 3 prev days*/
        $offsetNow = 72 * 60 * 60;
        $now = strtotime(date('c')) - $offsetNow;
        $this->dateFrom = date('c', $now);

        /*for 32 prev days*/
        $offsetDays = 768 * 60 * 60;
        $daysAgo = strtotime(date('c')) - $offsetDays;
        $this->deliveredRange = date('c', $daysAgo);

        $this->shopToken = $token;
        $this->shopID = $shopID;
//        $this->shippingDate = date('c');
        $this->shippingDate = '2019-11-27T00:00:00+03:00';
    }


    public function getOrder($id)
    {
        $result = Curl::execute('orderService/order/get', $this->shopToken, array(
            "shipments" => array(
                0 => $id,
            )
        ));
        return $result['data']['shipments'][0] ?? null;
    }


    public function getOrdersNew()
    {
        $result = Curl::execute('orderService/order/search', $this->shopToken, array(
            "statuses" => array(
                0 => "NEW",
            ),
            "dateFrom" => $this->dateFrom
        ));
        return $result['data']['shipments'] ?? null;
    }

    public function getOrdersCustomerCanceled()
    {

        $result = Curl::execute('orderService/order/search', $this->shopToken, array(
            "statuses" => array(
                0 => "CUSTOMER_CANCELED",
            ),
            "dateFrom" => $this->dateFrom
        ));

        return $result['data']['shipments'] ?? null;
    }

    public function getOrdersConfirmed()
    {
        $result = Curl::execute('orderService/order/search', $this->shopToken, array(
            "statuses" => array(
                0 => "CONFIRMED",
            ),
            "dateFrom" => $this->dateFrom
        ));
        return $result['data']['shipments'] ?? null;
    }


    public function getOrdersPacked()
    {

        $result = Curl::execute('orderService/order/search', $this->shopToken, array(
            "statuses" => array(
                0 => "PACKED"
            )
        ));
        return $result['data']['shipments'] ?? null;
    }

    public function getOrdersPackedByShippingDate()
    {

        $result = Curl::execute('orderService/order/search', $this->shopToken, array(
//            "statuses" => array(
//                0 => "PACKED"
//            ),
            "shippingDate" => $this->shippingDate
        ));
        return $result['data']['shipments'] ?? null;
    }

    public function getOrdersDelivered()
    {

        $result = Curl::execute('orderService/order/search', $this->shopToken, array(
            "statuses" => array(
                0 => "DELIVERED"
            ),
            "dateFrom" => $this->deliveredRange
        ));
        return $result['data']['shipments'] ?? null;
    }




    /**
     * SETTERS
     */

    /**
     * в котором содержатся все Лоты, которые Продавец отгрузить не готов
     * @param array $rejectSome
     * @return mixed
     */
    public function setRejectLots(array $rejectSome)
    {
        /*        $data = '{
                                "shipments": [{
                                    "shipmentId": "842818431",
                                    "orderCode": "",
                                    "items": [{
                                        "itemIndex": 1,
                                        "offerId": "390"
                                    }]
                                }]
                        }';*/
//        $data = json_decode($data, true);
        $data = $rejectSome;
        $goodsOrderNames = Curl::execute('orderService/order/reject', $this->shopToken, $data, true);

        if ($this->state == 'NEW') {
            $this->state = 'REJECT PARTIALLY';
        }
        return $goodsOrderNames;
    }

    /**
     * в котором содержатся все Лоты, которые Продавец готов отгрузить
     * @param array $confirmSome
     * @return mixed
     */
    public function setConfirmLots(array $confirmSome)
    {

        /*        $data = '{
                                "shipments": [{
                                    "shipmentId": "842818431",
                                    "orderCode": "",
                                    "items": [{
                                        "itemIndex": 2,
                                        "offerId": "392"
                                    }]
                                }]
                        }';*/
//        $data = json_decode($data, true);
        $data = $confirmSome;
        $goodsOrderNames = Curl::execute('orderService/order/confirm', $this->shopToken, $data, true);

        if ($this->state == 'NEW') {
            $this->state = 'CONFIRMED PARTIALLY';
        }
        return $goodsOrderNames;
    }

    /**
     * Продавец не готов отгрузить все Лоты
     * 1. Данные об отправлениях / shipmentsДа
     *  1. Идентификатор отправления Goods/ shipmentId Да
     *  2. Данные о лотах / itemsДа
     *      1. Порядковый номер лота / itemIndexДа
     *      2. Идентификатор офера Продавца/ offerId Да
     * 2. Причина отмены / reason
     *  1. Тип причины / type
     *  2. Комментарий / comment
     * @param array $rejectAll
     * @return mixed
     */
    public function setReject(array $rejectAll)
    {
        /*        $data = '{
                                "shipments": [{
                                    "shipmentId": "812224691",
                                    "orderCode": "",
                                    "items": [{
                                        "itemIndex": 1,
                                        "offerId": "421"
                                    },{
                                        "itemIndex": 2,
                                        "offerId": "425"
                                    }]
                                }]
                        }';*/
        $data = $rejectAll;
//        $data = json_decode($data, true);
        $goodsOrderNames = Curl::execute('orderService/order/reject', $this->shopToken, $data, true);

        if ($this->state == 'NEW') {
            $this->state = 'REJECT';
        }
        return $goodsOrderNames;
    }

    /**
     * Продавец готов отгрузить все Лоты
     * 1. Данные об Отправлениях / shipmentsДа
     *  1. Идентификатор Отправления Goods/ shipmentIdДа
     *  2. Идентификаторзаказа Продавца / orderCodeНет
     *  3. Данные о Лотах / itemsДа
     *      1. Порядковый номер Лота / itemIndexДа
     *      2. Идентификатор Оффера Продавца/ offerIdДа
     * @param array $confirmAll
     * @return mixed
     */

    public function setConfirm(array $confirmAll)
    {
        /*        $data = '{
                                "shipments": [{
                                    "shipmentId": "846882375",
                                    "orderCode": "",
                                    "items": [{
                                        "itemIndex": 1,
                                        "offerId": "390"
                                    }]
                                }]
                        }';*/
        $data = $confirmAll;
//        $data = json_decode($data, true);
        $goodsOrderNames = Curl::execute('orderService/order/confirm', $this->shopToken, $data, true);

        if ($this->state == 'NEW') {
            $this->state = 'CONFIRM';
        }
        return $goodsOrderNames;
    }

    /**
     * комплектации Отправления
     * @param $orderToPack
     * @return mixed
     */

    public function setPacking(array $orderToPack)
    {

        /*        $data = '{
                        "shipments": [{
                            "shipmentId": "842818431",
                            "orderCode": "842818431",
                            "items": [{
                                "itemIndex": 2,
                                "quantity": 1,
                                "boxes": [{
                                    "boxIndex": 1,
                                    "boxCode": "1231*842818431*1"
                                }]
                            }]
                        }]
                    }';    */

        $data = $orderToPack;
//        $data = json_decode($data, true);
//        $items = $data['shipments']['items'];
        /*        $cargo = new Cargo($items);
                $cargo->setCargo();*/


        $goodsOrderNames = Curl::execute('orderService/order/packing', $this->shopToken, $data);

        if ($this->state == 'CONFIRM') {
            $this->state = 'PACKED';
        }
        return $goodsOrderNames;


    }

    public function setShipping(array $orderToShip)
    {


        $ThatTime = "14:00:00";
        if (time() >= strtotime($ThatTime)) {
            var_dump("order set to shipped" . PHP_EOL);
            var_dump($orderToShip);
            $data = $orderToShip;

            $goodsOrderNames = Curl::execute('orderService/order/shipping', $this->shopToken, $data);

            if ($this->state == 'PACKED') {
                $this->state = 'SHIPPED';
            }
            return $goodsOrderNames;
            //return true;
        } else {
            var_dump("too early to ship this order" . PHP_EOL);
            var_dump($orderToShip);

            return false;
        }

    }
}


