<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 13:16
 */

namespace Avaks\MS;

use Avaks\BackendAPI;

class Assortment
{
    public $code;
    public $id;
    public $type;

    public function _construct()
    {
        $this->type = 'product';
    }

    public function findPosition()
    {
        /*$query = "SELECT `code` FROM ms_product WHERE id='$id'";
        $result = $sql->query($query);

        if ($result->num_rows > 0) {

            $sql->close();
            return $result->fetch_assoc()['code'];

        } else {
            $query = "SELECT `code` FROM ms_bundle WHERE id='$id'";
            $result = $sql->query($query);

            if ($result->num_rows > 0) {
                $sql->close();
                return $result->fetch_assoc()['code'];
            } else {
                $sql->close();
                return false;
            }

        }*/

        $backendAPI = new BackendAPI();
        $data['filter'] = json_encode(array('_id' => "$this->id"));
        $data['limit'] = 1;
        $data['offset'] = 0;

        if ($this->type == 'bundle'){
            $bundlesCursor = $backendAPI->getData($backendAPI->urlBundle, $data);
            return $bundlesCursor;
        }

        if ($this->type == 'service'){
            $serviceCursor = $backendAPI->getData($backendAPI->urlService, $data);
            return $serviceCursor;
        }

        $productsCursor = $backendAPI->getData($backendAPI->urlProduct, $data);
        return $productsCursor;

    }


}