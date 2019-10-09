<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 10:15
 */

namespace Avaks\MS;


class OrderMS
{

    public $id;
    public $name;
    public $positions;
    public $state;

    function __construct($id = null, $name = null, $positions = null)
    {
        $this->id = $id;
        $this->positions = $positions;
        $this->name = $name;
    }

    public function getById()
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id);
        return $res;
    }

    public function getByName()
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/?filter=name=' . $this->name);
        return isset((json_decode($res, true))['rows'][0]) ? (json_decode($res, true))['rows'][0] : $res;
    }

    public function setSticker($postdata)
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;
    }

    public function setToPack()
    {
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/327c02b4-75c5-11e5-7a40-e89700139937",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        //    если в МС В работе то поставить отгрузить
        if ($this->state == 'ecf45f89-f518-11e6-7a69-9711000ff0c4') {
            $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
            $this->state = '327c02b4-75c5-11e5-7a40-e89700139937';
        }
        return $res;
    }

    public function setDelivered()
    {
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/8beb27b2-6088-11e7-7a6c-d2a9003b81a5",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = '8beb27b2-6088-11e7-7a6c-d2a9003b81a5';
        return $res;
    }

    public function setCanceled()
    {
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/327c070c-75c5-11e5-7a40-e8970013993b",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = '327c070c-75c5-11e5-7a40-e8970013993b';
        return $res;
    }

    public function setInWork($oldDescription)
    {
        $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/ecf45f89-f518-11e6-7a69-9711000ff0c4",
                    "type": "state",
                    "mediaType": "application/json"
                }
            },
            "description": "' . $oldDescription . ' GOODS1364895"
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
        var_dump($postdata);
        return $res;
    }
}