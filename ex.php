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
require_once 'goods.php';

use Avaks\Goods\Cargo;
use Avaks\Goods\Order;
use Avaks\Goods\Curl;
use Avaks\Goods\Sticker;




$newOrder = '{	"meta": {},	"data": {		"shipments": [{			"shipmentId": "846882375",			"shipmentDate": "2019-08-19T16:08:58+03:00",
			"items": [{				"itemIndex": "1",				"offerId": "390",				"price": 700,				"finalPrice": 700,				"discounts": [],				"quantity": 1
			}],			"label": {				"deliveryId": "880376857",				"region": "Москва",				"city": "Москва",
				"address": "г Москва, р-н Аэропорт, ул Балтийская, д 4, кв. 3",				"fullName": "Тест Тест",				"merchantName": "ООО \"ЭДИЛ-ИМПОРТ\"",
				"merchantId": 1231,				"shipmentId": "846882375",				"shippingDate": "2019-08-19T20:00:00+03:00",
				"deliveryType": "Доставка курьером"				},			"shipping": {				"shippingDate": "2019-08-19T20:00:00+03:00",
				"shippingPoint": 123257			},
			"fulfillmentMethod": "FULFILLMENT_BY_MERCHANT"		}],		"merchantId": 1231	}}';
$goods = json_decode($newOrder, true);

/*после создания нового заказа*/
/*берем доставкку и формируем новый экземпляр класса
передаем лоты
лот и отправляем в МС на проверку*/

$items = $goods['data']['shipments'][0]['items'];
//$cargo = new Cargo($items);
//print_r($cargo->validateCargo());


$goods = new Order();
//$res = $goods->getOrder('842818431');
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
//var_dump($res);






