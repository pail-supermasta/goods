<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 13:16
 */

namespace Avaks\MS;

use Avaks\BackendAPI;
use Avaks\Customs;
use Avaks\SQL\AvaksSQL;

class Products
{
    public $orderPositionsRaw;
    public $code;
    public $id;

    public function __construct($orderPositions = false)
    {
        $this->orderPositionsRaw = $orderPositions;
    }

    public function getOrderProducts()
    {
        /*parse raw json and get product id*/

        $positions = json_decode($this->orderPositionsRaw, true);

        $products = array();
        foreach ((array)$positions as $position) {
            /*search by id in ms_product*/

            $position = Customs::findUUID($position['assortment']['meta']['href']);
            $products[] = AvaksSQL::selectProductById($position);
        }
        return $products;
    }




    public function getOne()
    {
        /*$collection = (new MSSync())->MSSync;
        $filter = ['code' => $this->code];

        $productCursor = $collection->product->findOne($filter);*/
        $backendAPI = new BackendAPI();
        $filter = array(
            'code'=> $this->code,
        );
        $data['project'] = json_encode(array(
                'name' => true
            )
        );
        $data['filter'] = json_encode($filter);
        $data['offset'] = 0;
        $productCursor = $backendAPI->getData($backendAPI->urlProduct, $data);
        return $productCursor ?? null;
    }
}