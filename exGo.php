<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 13:57
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';
//require_once 'goods.php';

use Avaks\Goods\Cargo;
use Avaks\Goods\Order;
use Avaks\Goods\Curl;
use Avaks\Goods\Sticker;






/*после создания нового заказа*/
/*берем доставкку и формируем новый экземпляр класса
передаем лоты
лот и отправляем в МС на проверку*/

//$items = $goods['data']['shipments'][0]['items'];
//$cargo = new Cargo($items);
//print_r($cargo->validateCargo());


$goods = new Order();
$res = $goods->getOrder('863018218');
//$res = $goods->getOrdersNew();
//$res = $goods->setConfirmLots();
//$res = $goods->setRejectLots();
//$res = $goods->setConfirm();
//$res = $goods->setReject();
//$res = $goods->getOrdersConfirmed();

/*$sticker= new Sticker();
$res= $sticker->printPdf();*/

//$res = $goods->setPacking();
//$res = $goods->getOrdersPacked();
var_dump($res);






