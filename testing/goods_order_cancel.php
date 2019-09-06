<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 12:55
 */


/**
 * Отмена лотов со стороны Goods
 * Запрос выполняется от Goods к Продавцу
 * Сообщает Продавцу об отмене Лотов в Отпрлавении.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");

$array = json_decode(file_get_contents('php://input'), true);
error_log($array . " \n", 3, "goods_cancels.log");

error_log("cancel\n", 3, "goods_cancels.log");