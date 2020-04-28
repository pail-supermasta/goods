<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

ini_set('memory_limit', '1024M');

require_once '../vendor/autoload.php';
require_once '../class/Telegram.php';


use Avaks\SQL\AvaksSQL;
use Avaks\MS\MSSync;
use Avaks\MS\Stocks;
use Avaks\MS\Products;
use Avaks\MS\Bundles;

// new product db new stocks - no usage


$products = array();
$errors = 0;


//report_stock_all
$stocks = new Stocks();
$stockMS = $stocks->getAll();


$productsMS = new Products();
$productsMongo = $productsMS->getMassProducts();

$bundlesMS = new Bundles();
$bundlesMongo = $bundlesMS->getMassBundles();


//нет на монге - бренд
$rows = AvaksSQL::selectAllAssoc("SELECT *  FROM `ms_customEntities`");
//$rows = sql->query("SELECT *  FROM `ms_customEntities`")->rows;
foreach ($rows as $key => $row) {
    $customEntities[$row['name']] = $row;
}

//нет на монге - категория
$categories = array();
$rows = AvaksSQL::selectAllAssoc("SELECT *  FROM `ms_customEntities` WHERE `customEntityMeta` = '55bae67d-0103-11e8-7a34-5acf000aab6a'");
//$rows = $sql->query("SELECT *  FROM `ms_customEntities` WHERE `customEntityMeta` = '55bae67d-0103-11e8-7a34-5acf000aab6a'")->rows;
foreach ($rows as $key => $row) {
    $row['index'] = $key + 1;
    $row['products_count'] = 0;
    $categories[$row['name']] = $row;
}


//$rows = AvaksSQL::selectAllAssoc("SELECT * FROM `ms_product` WHERE `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ");
//$rows = $sql->query("SELECT * FROM `ms_product` WHERE `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ")->rows; //LIMIT 100
foreach ($productsMongo as $key => $row) {
//    $row['meta'] = json_decode($row['meta'], true);
//    $row['attributes'] = json_decode($row['attributes'], true);
//    $row['salePrices'] = json_decode($row['salePrices'], true);
//    $row['barcodes'] = json_decode($row['barcodes'], true);
    $product = array();
    $product['index'] = $key;
    $product['available'] = 'available';
    $product['name'] = preg_replace('/[^0-9a-zа-я _-]/ui', '', $row['name']);
    if (isset($row['_attributes']['Цена Goods.ru'])) {
        $product['price'] = $row['_attributes']['Цена Goods.ru'];
    } elseif (isset($row['salePrices'][0]['value'])) {
        $product['price'] = $row['salePrices'][0]['value'] / 100;
    } else {
        $product['price'] = false;
    }

    if ($product['price'] == false || !isset($product['price'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана цена (РРЦ) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }

    /*if (!isset($row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана категория (Предмет WB) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }*/
    if (!isset($row['_attributes']['Предмет WB'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана категория (Предмет WB) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }

    $product['categoryId'] = $categories[$row['_attributes']['Предмет WB']]['index'];
    $product['outlets'] = 'outlets';
    $product['stock'] = $stockMS[$row['id']]['available'];
    if ($product['stock'] > 10) $product['stock'] = 10;
    if ($product['stock'] < 0) $product['stock'] = 0;
    $product['barcode'] = $row['code'];

    if (isset($row['_attributes']['Бренд'])) {
        $product['vendor'] = $customEntities[$row['_attributes']['Бренд']]['name'];
    } else {
        $product['vendor'] = false;
    }

//    echo $row['name'] . '<br>';
    flush();
    @ob_flush();

    $product['model'] = $row['_attributes']['Индекс / модель товара'] ?? false;

    $categories[$row['_attributes']['Предмет WB']]['products_count']++;

    $products[] = $product;

}

$offerId = sizeof($products);
//$rows = AvaksSQL::selectAllAssoc("SELECT * FROM `ms_bundle` WHERE `name` like '%GD' and  `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ");
//$rows = $sql->query("SELECT * FROM `ms_bundle` WHERE `name` like '%GD' and  `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ")->rows; //LIMIT 100
foreach ($bundlesMongo as $key => $row) {
//    $row['meta'] = json_decode($row['meta'], true);
//    $row['attributes'] = json_decode($row['attributes'], true);
//    $row['salePrices'] = json_decode($row['salePrices'], true);
//    $row['barcodes'] = json_decode($row['barcodes'], true);
    $bundle = array();
    $bundle['index'] = $offerId + $key;
    $bundle['available'] = 'available';
    $bundle['name'] = preg_replace('/[^0-9a-zа-я _-]/ui', '', $row['name']);
    if (isset($row['_attributes']['Цена Goods.ru'])) {
        $bundle['price'] = $row['_attributes']['Цена Goods.ru'];
    } elseif (isset($row['salePrices'][0]['value'])) {
        $bundle['price'] = $row['salePrices'][0]['value'] / 100;
    } else {
        $bundle['price'] = false;
    }

    if ($bundle['price'] == false || !isset($bundle['price'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана цена (РРЦ) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }

    /*if (!isset($row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана категория (Предмет WB) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }*/
    if (!isset($row['_attributes']['Предмет WB'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана категория (Предмет WB) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }

    $bundle['categoryId'] = $categories[$row['_attributes']['Предмет WB']]['index'];
    $bundle['outlets'] = 'outlets';
    $bundle['stock'] = $stockMS[$row['id']]['available'];
    if ($bundle['stock'] > 10) $bundle['stock'] = 10;
    if ($bundle['stock'] < 0) $bundle['stock'] = 0;
    $bundle['barcode'] = $row['code'];

    if (isset($row['_attributes']['Бренд'])) {
        $bundle['vendor'] = $customEntities[$row['_attributes']['Бренд']]['name'];
    } else {
        $bundle['vendor'] = false;
    }

//    echo $row['name'] . '<br>';

    $bundle['model'] = $row['_attributes']['Индекс / модель товара'] ?? false;

    $categories[$row['_attributes']['Предмет WB']]['products_count']++;

    $products[] = $bundle;

}
ob_start(); ?>
<yml_catalog date="<?= date('Y-m-d H:i'); ?>">
    <shop>
        <name>Удивительный интернет-магазин</name>
        <company>ООО «Новинки»</company>
        <url>https://amaze.ru</url>
        <currencies>
            <currency id="RUR" rate="1"/>
        </currencies>
        <categories>
            <?php foreach ($categories as $key => $category) {
                if ($category['products_count'] == 0) continue;
                if (isset($category['index']) && isset($category['name'])) {
                    echo '<category id="' . $category['index'] . '">' . $category['name'] . '</category>';
                }

            } ?>
        </categories>
        <shipment-options>
            <option days="0" order-before="9"/>
        </shipment-options>
        <offers>
            <?php foreach ($products as $key => $product) { ?>
                <offer id="<?= $product['barcode']; ?>" available="<?= $product['stock'] ? 'true' : 'false'; ?>">
                    <name><?= $product['name']; ?></name>
                    <price><?= $product['price']; ?></price>
                    <currencyId>RUR</currencyId>
                    <categoryId><?= $product['categoryId']; ?></categoryId>
                    <shipment-options>
                        <option days="0" order-before="9"/>
                    </shipment-options>
                    <outlets>
                        <outlet id="0" instock="<?= $product['stock']; ?>"/>
                    </outlets>
                    <vendor><?= $product['vendor']; ?></vendor>
                    <model><?= $product['model']; ?></model>
                    <barcode><?= $product['barcode']; ?></barcode>
                </offer>
            <?php } ?>
        </offers>
    </shop>
</yml_catalog>
<?php
$xml = ob_get_contents();
ob_end_clean();

file_put_contents('amaze_feed_goodsv3.xml', '<?xml version="1.0" encoding="utf-8"?>' . $xml);
if ($errors) {
    telegram('В выгрузке GOODS amaze_feed_goodsv3 найдены ошибки (' . $errors . ') <a href="http://goods.ltplk.ru/feed/feed_goodsv3.php">Посмотреть</a>', '-289839597');
}
else{
    telegram('Обновление amaze_feed_goodsv3.xml без ошибок.<a href="http://goods.ltplk.ru/feed/amaze_feed_goodsv3.xml">Посмотреть</a>', '-289839597');
}

ob_start(); ?>

<yml_catalog date="<?= date('Y-m-d H:i'); ?>">
    <shop>
        <name>Интернет-магазин</name>
        <company>ООО «Незабудка»</company>
        <currencies>
            <currency id="RUR" rate="1"/>
        </currencies>
        <categories>
            <?php foreach ($categories as $key => $category) {
                if ($category['products_count'] == 0) continue;
                if (isset($category['index']) && isset($category['name'])) {
                    echo '<category id="' . $category['index'] . '">' . $category['name'] . '</category>';
                }

            } ?>
        </categories>
        <shipment-options>
            <option days="0" order-before="9"/>
        </shipment-options>
        <offers>
            <?php foreach ($products as $key => $product) { ?>
                <offer id="<?= $product['barcode']; ?>" available="<?= $product['stock'] ? 'true' : 'false'; ?>">
                    <name><?= $product['name']; ?></name>
                    <price><?= $product['price']; ?></price>
                    <currencyId>RUR</currencyId>
                    <categoryId><?= $product['categoryId']; ?></categoryId>
                    <shipment-options>
                        <option days="0" order-before="9"/>
                    </shipment-options>
                    <outlets>
                        <outlet id="0" instock="<?= $product['stock']; ?>"/>
                    </outlets>
                    <vendor><?= $product['vendor']; ?></vendor>
                    <model><?= $product['model']; ?></model>
                    <barcode><?= $product['barcode']; ?></barcode>
                </offer>
            <?php } ?>
        </offers>
    </shop>
</yml_catalog>
<?php
echo 'Обновление nz_feed_goodsv3.xml без ошибок.';
$xml = ob_get_contents();
ob_end_clean();

file_put_contents('nz_feed_goodsv3.xml', '<?xml version="1.0" encoding="utf-8"?>' . $xml);


if ($errors) {
    telegram('В выгрузке GOODS nz_feed_goodsv3 найдены ошибки (' . $errors . ') <a href="http://goods.ltplk.ru/feed/feed_goodsv3.php">Посмотреть</a>', '-289839597');
}
else{
    telegram('Обновление nz_feed_goodsv3.xml без ошибок.<a href="http://goods.ltplk.ru/feed/nz_feed_goodsv3.xml">Посмотреть</a>', '-289839597');
}

?>
<style>
    a:visited {
        opacity: 0.5;
    }
</style>

