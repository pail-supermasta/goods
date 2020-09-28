<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.08.2020
 * Time: 11:23
 */


require_once 'vendor/autoload.php';

use Avaks\Goods\Sticker;
use Avaks\MS\OrderMS;


$orderPacked = '946568599';
$orderMS = new OrderMS('', $orderPacked);
$orderDetails = $orderMS->getByName();
$orderMS->id = $orderDetails['id'];

$shopToken = '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D';
$boxCode = '608';

/*записать в заказ МС файл маркировочного листа*/
$put_data = array();
$attribute = array();

$sticker = new Sticker();

/*получить стикер из Гудс для заказа*/
$pdfCode = $sticker->printPdf($orderPacked, $shopToken, $boxCode);
die();

//    если надо получить из файла
//    $content = base64_encode(file_get_contents("pdf/sticker-files/Маркировка $orderMS->name.pdf"));
$content = base64_encode($pdfCode);
$attribute['id'] = 'b8a8f6d6-5782-11e8-9ff4-34e800181bf6';
$attribute['file']['filename'] = "Маркировка $orderMS->name.pdf";
$attribute['file']['content'] = $content;
$put_data['attributes'][] = $attribute;

$final = json_encode($put_data);
var_dump($orderMS->setSticker($final));