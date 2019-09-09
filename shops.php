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

//Amaze 97B1BC55-189D-4EB4-91AF-4B9E9A985B3D
//Фирдус C12405BF-01CB-4A6C-A41E-0E179EF00F54


$goodsTokens = array(
    'Amaze' => '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D',
    'Фирдус' => 'C12405BF-01CB-4A6C-A41E-0E179EF00F54',
//    'НОВИНКИ test' => '6881430B-882F-4C4F-8DCA-14FDAFEBAFEC'
);

foreach ($goodsTokens as $goodsName => $goodsToken) {
    $name = $goodsName;
    $token = $goodsToken;
    $shop = new Shop($name, $token);


    processShop($shop->token);
}