<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 13:16
 */

namespace Avaks\MS;

use  Avaks\MS\MSSync;

class Bundles
{

    public function getMassBundles()
    {
        $collection = (new MSSync())->MSSync;

        $filter = [
            '_attributes.Отгружается в опт' => true,
            'archived'=> false
        ];
        $bundlesCursor = $collection->bundle->find($filter)->toArray();
        return $bundlesCursor;
    }
    

}
