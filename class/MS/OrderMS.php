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

        /*удалить двойные ковычки*/
        $oldDescription = str_replace('"', '', $oldDescription);

        /*удалить новую строку*/
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

    public function setProcessManually()
    {

        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/552a994e-2905-11e7-7a31-d0fd002c3df2",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = '552a994e-2905-11e7-7a31-d0fd002c3df2';
        var_dump($postdata);
        return $res;
    }

    public function setDSHSum($DSHSumNum)
    {

        /*удалить двойные ковычки*/
//        $oldDescription = str_replace('"', '', $oldDescription);

        /*удалить новую строку*/
//        $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));


        $put_data = array();
        $attribute = array();

        $attribute['id'] = '535dd809-1db1-11ea-0a80-04c00009d6bf';
        $attribute['value'] = $DSHSumNum;
        $put_data['attributes'][] = $attribute;
//        $put_data['description'] = $oldDescription .' '.$DSHSumComment;

        $postdata = json_encode($put_data,JSON_UNESCAPED_UNICODE);

        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;
        
    }

    public function setDSHSumAndLogisticSum($DSHSumNum,$LogisticSumNum)
    {

        $put_data = array();
        $attribute = array();

//        DSH sum new
        $attribute['id'] = '535dd809-1db1-11ea-0a80-04c00009d6bf';
        $attribute['value'] = $DSHSumNum;
        $put_data['attributes'][] = $attribute;

//        logistic sum
        $attribute['id'] = '8a500531-10fc-11ea-0a80-0533000590c7';
        $attribute['value'] = $LogisticSumNum;
        $put_data['attributes'][] = $attribute;

        $postdata = json_encode($put_data,JSON_UNESCAPED_UNICODE);

        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;

    }
}