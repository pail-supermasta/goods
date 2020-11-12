<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.09.2019
 * Time: 17:33
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';
require_once 'exFinal.php';

use Avaks\Goods\Shop;



$goodsTokens = array(
    '608' => '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D',//amaze
    '9308' => 'B58874A5-7AE5-452A-8C31-F0DDEA37AA56',//АВАКС
//    '2998' => 'C12405BF-01CB-4A6C-A41E-0E179EF00F54', //novinki - firdus
//    'НОВИНКИ test' => '6881430B-882F-4C4F-8DCA-14FDAFEBAFEC'
);

foreach ($goodsTokens as $goodsID => $goodsToken) {
    $id = $goodsID;
    $token = $goodsToken;
    $shop = new Shop($id, $token);


    processShop($shop->id,$shop->token);
}