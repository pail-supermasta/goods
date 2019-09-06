<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 12:55
 */


/**
 * Создание и передача отправления Продавцу
 * Запрос выполняется от Goods к Продавцу
 * Передает Продавцу информацию о новом Заказе, оформленном на Goods.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
ini_set("error_log", "goods_orders.log");


//$raw_payload = file_get_contents('php://input');
//$payload = json_decode($raw_payload, true);
//error_log($payload['data']['shipments'] . "\n", 3, "goods_orders.log");


//error_log("orders\n", 3, "goods_orders.log");