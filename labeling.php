<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 9:55
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';

use Avaks\Goods\Order;
use Avaks\Goods\Sticker;
use Avaks\MS\Orders;
use Avaks\MS\OrderMS;

define('BOX_CODE_ID', '1231'); //TEST
//define('BOX_CODE_ID','608'); //PRODUCTION
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

$ordersMS = new Orders();

$goods = new Order();


/*ШАГ 4 НАЧАЛО из описания - наклеивание этикетки*/



/*№3  - получить список упакованных заказов из Гудс*/
$goodsOrdersPacked = $goods->getOrdersPacked();


/*№4 для каждого заказа - печать этикетки и добавление файла в заказ в МС + Отгрузка заказа */

function uploadSticker($orderPacked)
{
    $sticker = new Sticker();

    /*получить стикер из Гудс для заказа*/
    $pdfCode = $sticker->printPdf($orderPacked, BOX_CODE_ID);
    $orderMS = new OrderMS('', $orderPacked);
    $orderDetails = $orderMS->getByName();
    $orderMS->id = $orderDetails['id'];

    /*получить Статус заказа в МС*/
    preg_match(ID_REGEXP, $orderDetails['state']['meta']['href'], $matches);
    $state_id = $matches[0];
    $orderMS->state = $state_id;


    /*записать в заказ МС файл маркировочного листа*/
    $put_data = array();
    $attribute = array();

//    если надо получить из файла
//    $content = base64_encode(file_get_contents("pdf/sticker-files/Маркировка $orderMS->name.pdf"));
    $content = base64_encode($pdfCode);
    $attribute['id'] = 'b8a8f6d6-5782-11e8-9ff4-34e800181bf6';
    $attribute['file']['filename'] = "Маркировка $orderMS->name.pdf";
    $attribute['file']['content'] = $content;
    $put_data['attributes'][] = $attribute;

    $final = json_encode($put_data);
    $orderMS->setSticker($final);


    /*Отгрузить заказ*/
    $orderMS->setToPack();


}

foreach ($goodsOrdersPacked as $orderPacked) {
    uploadSticker($orderPacked);
}




/*ШАГ 4 КОНЕЦ*/









