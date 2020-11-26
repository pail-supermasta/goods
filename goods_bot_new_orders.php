<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.08.2020
 * Time: 13:50
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');
define('MS_PATH', 'https://online.moysklad.ru/api/remap/1.1');

require_once 'vendor/autoload.php';

use Avaks\Goods\Order;
use Avaks\MS\OrderMS;


require_once 'class/Telegram.php';

date_default_timezone_set('Europe/Moscow');
function addCustomerorder($order, $organization)
{


    $put_data = array();
    $put_data['attributes'] = array();

    // Номер
    $put_data['name'] = $order['shipmentId'];
    // Без НДС
    $put_data['vatEnabled'] = false;
    // Не общий доступ
    $put_data['shared'] = false;
    // Проведено
    $put_data['applicable'] = true;
    // ООО «Новинки»
    $put_data['organization']['meta']['href'] = MS_PATH . "/entity/organization/$organization";
    $put_data['organization']['meta']['type'] = "organization";
    // Покупатель Goods.ru
    $put_data['agent']['meta']['href'] = MS_PATH . "/entity/counterparty/64710328-2e6f-11e8-9ff4-34e8000f81c8";
    $put_data['agent']['meta']['type'] = "counterparty";
    // Договор №Прод/КП-Н09.03.18 от 04.05.2018
    $put_data['contract']['meta']['href'] = MS_PATH . "/entity/contract/f2f949de-4f79-11e8-9107-504800047057";
    $put_data['contract']['meta']['type'] = "contract";
    // Склад MP_NFF
    $put_data['store']['meta']['href'] = MS_PATH . "/entity/store/48de3b8e-8b84-11e9-9ff4-34e8001a4ea1";
    $put_data['store']['meta']['type'] = "store";
    // #Логистика: агент — 1 Не нужна доставка
    $put_data['attributes'][0]['id'] = "4552a58b-46a8-11e7-7a34-5acf002eb7ad";
    $put_data['attributes'][0]['value']['meta']['href'] = MS_PATH . "/entity/customentity/10f17383-95f9-11e6-7a69-9711000cd76f/6e64fdac-95f9-11e6-7a69-9711000cea11";
    $put_data['attributes'][0]['value']['meta']['type'] = "customentity";
    // ФИО
    $put_data['attributes'][1]['id'] = "5b766cb9-ef7e-11e6-7a31-d0fd001e5310";
    $put_data['attributes'][1]['value'] = $order['customerFullName'];
    // Адрес
    $put_data['attributes'][2]['id'] = "547ff930-ef8e-11e6-7a31-d0fd0021d13e";
    $put_data['attributes'][2]['value'] = $order['customerAddress'];
    // Источник
    $put_data['attributes'][3]['id'] = "e5c105a8-7c2d-11e6-7a31-d0fd00172c6f";
    $put_data['attributes'][3]['value']['meta']['href'] = MS_PATH . "/entity/customentity/fdaebe54-7c2b-11e6-7a69-8f5500110acc/12e48a58-9e57-11e9-912f-f3d400070a00";
    $put_data['attributes'][3]['value']['meta']['type'] = "customentity";
    // Статус Новый
    $put_data['state']['meta']['href'] = MS_PATH . "/entity/customerorder/metadata/states/327bfd05-75c5-11e5-7a40-e89700139935";
    $put_data['state']['meta']['type'] = "state";
    // Плановая дата отгрузки
//		$put_data['deliveryPlannedMoment'] = date("Y-m-d H:i:s");
    $put_data['deliveryPlannedMoment'] = date("Y-m-d H:i:s", strtotime($order['shipmentDateFrom']));
    $put_data['description'] = '';

    foreach ($order['items'] as $key => $item) {
        $product = new \Avaks\MS\Products();
        $product->code = $item['offerId'];
        $productApi = $product->getOne();
        $product->id = $productApi['rows'][0]['_id'];
        if ($product->id == null) {
            $put_data['description'] .= "Внимание! Ошибка при сопоставлении товара! Проверьте заказ вручную!\n";
            $orderName = $put_data['name'];
            $offerId = $item['offerId'];
            telegram("Внимание! Заказ $orderName  Ошибка при сопоставлении товара $offerId !", '-486167604');
            continue;
        }

        $position = array();
        $position['quantity'] = $item['quantity'];
        $position['reserve'] = $item['quantity'];
        $position['price'] = $item['finalPrice'] * 100;
        $position['vat'] = 0;
        $position['assortment']['meta']['href'] = MS_PATH . '/entity/product/' . $product->id;
        $position['assortment']['meta']['type'] = 'product';
        $put_data['positions'][] = $position;

        // Программа лояльности Гудс
        // $position['price'] = array_sum(array_column($item['discounts'], 'discountAmount')) * 100;
        if ($item['discounts']) {
            foreach ($item['discounts'] as $keyInner => $discount) {
                $position = array();
                $position['quantity'] = 1;
                $position['price'] = $discount['discountAmount'] * 100;
                $position['vat'] = 0;
                $position['assortment']['meta']['href'] = MS_PATH . '/entity/service/a3af6531-6fce-11e9-9109-f8fc0025a229';
                $position['assortment']['meta']['type'] = 'service';
                $put_data['positions'][] = $position;
                $put_data['description'] .= "Предоставлена скидка типа {$discount['discountType']} ({$discount['discountDescription']}) от goods  в размере {$discount['discountAmount']} руб\n";
            }
        }

    }


    $result = \Avaks\MS\CurlMoiSklad::curlMS('/entity/customerorder', json_encode($put_data,JSON_UNESCAPED_UNICODE), 'post');


        if (isset($result['errors'])){
            foreach ($result['errors'] as $key => $error) {
                telegram("<b>\xE2\x9A\xA0 GOODS:</b> Ошибка при обработке заказа №".$order['shipmentId']."\n".$error['error'], '-336297687');
            }
        } else {
            telegram("<b>\xE2\x9A\xA0 GOODS:</b> Добавлен заказ <a href=\"".$result['meta']['uuidHref']."\">№".$order['shipmentId']."</a>", '-336297687');
        }

    return $result;

}


//"organization" => "07bbe005-8b17-11e7-7a34-5acf0019232a",

$orderMS = new OrderMS();

$goods = new Order('608', '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D');

$goodsOrdersNew = $goods->getOrdersNew();

if (is_array($goodsOrdersNew)) {
    $message = "Goods.ru (" . count($goodsOrdersNew) . ")\n";

    foreach ($goodsOrdersNew as $key => $orderToCreate) {
        $orderMS->name = $orderToCreate;
        $customerorder = $orderMS->getByName();
        if (isset($customerorder['id'])) {
            $message .= 'Заказ №' . $customerorder['name'] . " уже существует в МС\n";
        } else {
            $goodsOrderDetails = $goods->getOrder($orderToCreate);
            $customerorder = addCustomerorder($goodsOrderDetails, "07bbe005-8b17-11e7-7a34-5acf0019232a");


            $message .= 'Заказ №' . $orderMS->name . " создан\n";
        }
    }
    echo $message;
    telegram($message,'-1001247438671');
}

