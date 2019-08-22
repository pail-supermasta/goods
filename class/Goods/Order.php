<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 15:57
 */


namespace Avaks\Goods;

class Order
{
//    private $token = "97B1BC55-189D-4EB4-91AF-4B9E9A985B3D";
//    private $api_url = "https://partner.goods.ru/api/market/v1/";
    public $state = "NEW";
    private $postToken = '6881430B-882F-4C4F-8DCA-14FDAFEBAFEC';

    /**
     * GETTERS
     */

    public function getOrder($id)
    {
        $result = Curl::curl('orderService/order/get', array(
            "shipments" => array(
                0 => $id,
            )
        ));
        return $result['data']['shipments'][0];
    }


    public function getOrdersNew()
    {
        $result = Curl::curl('orderService/order/search', array(
            "statuses" => array(
                0 => "NEW",
            )
        ));
        return $result['data']['shipments'];
    }

    public function getOrdersConfirmed()
    {
        $result = Curl::curl('orderService/order/search', array(
            "statuses" => array(
                0 => "CONFIRMED",
            )
        ));
        return $result['data']['shipments'];
    }


    public function getOrdersPacked()
    {
        $result = Curl::curl('orderService/order/search', array(
            "statuses" => array(
                0 => "PACKED",
            )
        ));
        return $result['data']['shipments'];
    }


    /**
     * SETTERS
     */

    /**
     * в котором содержатся все Лоты, которые Продавец отгрузить не готов
     */
    public function setRejectLots()
    {
        $data = '{
                        "shipments": [{
                            "shipmentId": "842818431",
                            "orderCode": "",
                            "items": [{
                                "itemIndex": 1,
                                "offerId": "390"
                            }]
                        }]                    
                }';
        $data = json_decode($data, true);
        $goodsOrderNames = Curl::curl('orderService/order/reject', $data);

        if ($this->state == 'NEW') {
            $this->state = 'REJECT PARTIALLY';
        }
        return $goodsOrderNames;
    }

    /**
     * в котором содержатся все Лоты, которые Продавец готов отгрузить
     */
    public function setConfirmLots()
    {

        $data = '{
                        "shipments": [{
                            "shipmentId": "842818431",
                            "orderCode": "",
                            "items": [{
                                "itemIndex": 2,
                                "offerId": "392"
                            }]
                        }]                    
                }';
        $data = json_decode($data, true);
        $goodsOrderNames = Curl::curl('orderService/order/confirm', $data);

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
     */
    public function setReject()
    {
        $data = '{
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
                }';
        $data = json_decode($data, true);
        $goodsOrderNames = Curl::curl('orderService/order/reject', $data);

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
     */

    public function setConfirm()
    {
        $data = '{
                        "shipments": [{
                            "shipmentId": "846882375",
                            "orderCode": "",
                            "items": [{
                                "itemIndex": 1,
                                "offerId": "390"
                            }]
                        }]                    
                }';
        $data = json_decode($data, true);
        $goodsOrderNames = Curl::curl('orderService/order/confirm', $data);

        if ($this->state == 'NEW') {
            $this->state = 'CONFIRM';
        }
        return $goodsOrderNames;
    }

    /**
     * комплектации Отправления
     */

    public function setPacking()
    {
        /*показал ошибку почему то хотя перешел в packed*/
        /*        $data = '{
                        "shipments": [{
                            "shipmentId": "846882375",
                            "orderCode": "846882375",
                            "items": [{
                                "itemIndex": 1,
                                "quantity": 1,
                                "boxes": [{
                                    "boxIndex": 1,
                                    "boxCode": "1231*846882375*1"
                                }]
                            }]
                        }]
                    }';*/
        $data = '{
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
            }';

        $data = json_decode($data, true);
//        $items = $data['shipments']['items'];
        /*        $cargo = new Cargo($items);
                $cargo->setCargo();*/


        $goodsOrderNames = Curl::curl('orderService/order/packing', $data);

        if ($this->state == 'CONFIRM') {
            $this->state = 'PACKED';
        }
        return $goodsOrderNames;


    }

    public function setShipping()
    {
        /*изменился с на выдаче на доставляется = shippingDate*/
        $data = '{
                "shipments": [{
                    "shipmentId": "842818431",
                    "orderCode": "842818431",
                    "items": [{
                        "itemIndex": 2,
                        "quantity": 1,
                        "boxes": [{
                            "boxIndex": 1,
                            "boxCode": "1231*842818431*1"
                        }],
                        "shipping":{  "shippingDate":"2019-11-23T14:00:00+03:00"}
                    }]
                }]
            }';

        $data = json_decode($data, true);

        $goodsOrderNames = Curl::curl('orderService/order/shipping', $data);

        if ($this->state == 'PACKED') {
            $this->state = 'SHIPPED';
        }
        return $goodsOrderNames;
    }
}
