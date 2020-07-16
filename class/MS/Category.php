<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 13:16
 */

namespace Avaks\MS;

use  Avaks\MS\MSSync;

class Category
{

    public function getAll()
    {
        $collection = (new MSSync())->MSSync;

        /*$filter = [
            '_attributes.Отгружается в опт' => true,
            'archived'=> false
        ];*/
        $categoriesCursor = $collection->customentitydata->find()->toArray();
        return $categoriesCursor;
    }
    

}
