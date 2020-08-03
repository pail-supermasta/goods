<?php
//date_default_timezone_set('UTC');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set("error_log", "php-error.log");
//
//ini_set('memory_limit', '1024M');

$start = microtime(true);
// Settings get token
$urlLogin = 'https://62.109.13.151:3000/api/v1/auth/login';
$userData = array("username" => "mongodb@техтрэнд", "password" => "!!@th9247t924");

// Settings get product list
$urlProduct = 'https://62.109.13.151:3000/api/v1/product';
$urlCategory = 'https://62.109.13.151:3000/api/v1/customentitydata';
$urlStock = 'https://62.109.13.151:3000/api/v1/report_stock_all';

// get Token
$token = getToken($urlLogin, $userData);

// filters
$dataCategory['limit'] = 9999;
$dataCategory['offset'] = 0;
$dataCategory['project'] = json_encode(array(
        '_id' => true,
        'name' => true
    )
);

$dataCategory['filter'] = json_encode(array('_customentity' => '55bae67d-0103-11e8-7a34-5acf000aab6a'));

$data['limit'] = 9999;
$data['offset'] = 0;
$data['project'] = json_encode(array(
        '_id' => true,
        'name' => true,
        '_attributes' => true,
        'barcodes' => true,
        'article' => true
    )
);

$data['filter'] = json_encode(array('_attributes.Отгружается в Goods' => true));

// get data from MongoDB
$category = getData($urlCategory, $dataCategory, $token);
$products = getData($urlProduct, $data, $token);

// Create new SimpleXMLElement object
$dt = new DateTime();
#$dt->modify("3 hour");
$dateTime = $dt->format('Y-m-d H:i');


$itemsXml = new SimpleXMLElement("<yml_catalog date='$dateTime'></yml_catalog>");

$shop = $itemsXml->addChild('shop');

$name = $shop->addChild('name', 'Удивительный интернет-магазин');
$company = $shop->addChild('company', 'ООО «Новинки»');
$url = $shop->addChild('url', 'https://amaze.ru');
$currencies = $shop->addChild('currencies');
$currency = $currencies->addChild('currency');
$currency->addAttribute('id', 'RUR');
$currency->addAttribute('rate', '1');
$categories = $shop->addChild('categories');

$categoryes = [];
$i = 1;

if ($category['rows']) {
    foreach ($category['rows'] as $k => $v) {
        $categoryes[$i] = $v['name'];
        $categoryXML = $categories->addChild('category', $v['name']);
        $categoryXML->addAttribute('id', $i);
        $i++;
    }
}

$stocks = getQuantity($urlStock, $token);
$stocksData = [];

// add stocks in array
if ($stocks['rows']) {
    foreach ($stocks['rows'] as $k => $v) {
        $stocksData[$v['_product']] = ['stock' => $v['stock'], 'reserve' => $v['reserve']];
        $i++;
    }
}

$shipmentoptions = $shop->addChild('shipment-options');
$option = $shipmentoptions->addChild('option');
$option->addAttribute('days', '0');
$option->addAttribute('order-before', '9');
$offers = $shop->addChild('offers');

foreach ($products['rows'] as $k => $v) {
    $offer = $offers->addChild('offer');

    if ($v['name'] != '') {
        $name = $offer->addChild('name', $v['name']);
    } else {
        echo $v['_id'];
    }

    $getData = findValueByKey($stocksData, $v['_id']);
    $inStock = ($getData['stock'] - $getData['reserve']);
    $outlets = $offer->addChild('outlets');
    $outlet = $outlets->addChild('outlet');
    $outlet->addAttribute('id', '0');

    if ($inStock > 0) {
        $offer->addAttribute('available', 'true');
    } else {
        $offer->addAttribute('available', 'false');
    }

    $outlet->addAttribute('instock', $inStock);
    $offer->addChild('model', $v['article']);

    foreach ($v['barcodes'][0] as $f => $t) {
        $offer->addAttribute('id', $t);
        $barcodes = $offer->addChild('barcodes', $t);
    }

    foreach ($v['_attributes'] as $key => $value) {
        if ($key == 'Бренд') {
            $offer->addChild('vendor', $value);
        }

        if ($key == 'Цена Goods.ru') {
            $price = number_format($value, 2, '.', '');
            $offer->addChild('price', $price);
        }

        if ($key == 'Предмет WB') {
            $find = array_search($value, $categoryes);
            $offer->addChild('categoryId', $find);
        }

    }
}

$shipmentoptions = $offer->addChild('shipment-options');
$option = $shipmentoptions->addChild('option');
$option->addAttribute('days', '0');
$option->addAttribute('order-before', '9');

// just some debug at frontend
header('Content-Type: text/xml; charset=utf-8');
echo $itemsXml->asXML();

// Create a new DOMDocument object
$doc = new DOMDocument('1.0', 'utf-8');
$doc->formatOutput = true;
$domnode = dom_import_simplexml($itemsXml);
$domnode->preserveWhiteSpace = false;
$domnode = $doc->importNode($domnode, true);
$domnode = $doc->appendChild($domnode);

// save in file at file system
$doc->save("/var/www/user/data/www/goods.ltplk.ru/goods_feed.xml");

#echo 'Время генерации: ' . ( microtime(true) - $start ) . ' сек.';

header('HTTP/1.1 200 OK');
header("Pragma: no-cache");
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
#header('Content-type', 'text/xml');
header('Content-Type: text/xml; charset=utf-8');



// get storage qnty
function getQuantity($urlProduct, $token) {

    $data['limit'] = 10000;
    $data['offset'] = 0;
    $data['project'] = json_encode(array(
            '_id' => true,
            '_product' => true,
            'quantity' => true,
            'reserve' => true,
            'stock' => true
        )
    );

    $data['filter'] = json_encode(array('_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'));

    $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        sprintf('Authorization: Bearer %s', $token)
    );

    $data_string = http_build_query($data);
    $ch = curl_init($urlProduct);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);

    return $result;
}

// fimd bu key
function findValueByKey($inputArray, $findKey) {
    foreach ($inputArray as $key1 => $value1) {
        if ($findKey == $key1) {
            return $value1;
        } elseif (is_array($value1)) {
            $tmp = findValueByKey($value1, $findKey);
            if ($tmp !== false) {
                return $tmp;
            }
        }
    }
    return false;
}


function getData($urlProduct, $data, $token) {
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        sprintf('Authorization: Bearer %s', $token)
    );

    $data_string = http_build_query($data);

    $ch = curl_init($urlProduct);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);

    return $result;
}

function getToken($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);
    return $result['token'];
}



