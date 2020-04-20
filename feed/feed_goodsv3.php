<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('memory_limit', '1024M');

require_once '../vendor/autoload.php';

use Avaks\SQL\AvaksSQL;
use Avaks\MS\MSSync;
use Avaks\MS\Stocks;
use Avaks\MS\Products;
use Avaks\MS\Bundles;


$products = array();
$errors = 0;


//report_stock_all
$stocks = new Stocks();
$stockMS = $stocks->getAll();


$productsMS = new Products();
$productsMongo = $productsMS->getMassProducts();

$bundlesMS = new Bundles();
$bundlesMongo = $bundlesMS->getMassBundles();


//нет на монге
$rows = AvaksSQL::selectAllAssoc("SELECT *  FROM `ms_customEntities`");
//$rows = sql->query("SELECT *  FROM `ms_customEntities`")->rows;
foreach ($rows as $key => $row) {
    $customEntities[$row['id']] = $row;
}

//нет на монге
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
    if (isset($row['attributes']['a4e869cf-8dc0-11e9-9ff4-31500015e1ea'])) {
        $product['price'] = $row['attributes']['a4e869cf-8dc0-11e9-9ff4-31500015e1ea'];
    } else {
        $product['price'] = $row['salePrices'][0]['value'] / 100;
    }

    if ($product['price'] < 100) {
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

    if (isset($row['attributes']['f3f556dc-afe9-11e7-7a6c-d2a900036ddd'])) {
        $product['vendor'] = $customEntities[$row['attributes']['f3f556dc-afe9-11e7-7a6c-d2a900036ddd']]['name'];
    } else {
        $product['vendor'] = false;
    }

    echo $row['name'] . '<br>';
    flush();
    @ob_flush();

    $product['model'] = $row['attributes']['0df1fcd2-42e3-11e8-9109-f8fc000411c7'] ?? false;

    $categories[$row['_attributes']['Предмет WB']]['products_count']++;

    $products[] = $product;

}


//$rows = AvaksSQL::selectAllAssoc("SELECT * FROM `ms_bundle` WHERE `name` like '%GD' and  `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ");
//$rows = $sql->query("SELECT * FROM `ms_bundle` WHERE `name` like '%GD' and  `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ")->rows; //LIMIT 100

foreach ($bundlesMongo as $key => $row) {
//    $row['meta'] = json_decode($row['meta'], true);
//    $row['attributes'] = json_decode($row['attributes'], true);
//    $row['salePrices'] = json_decode($row['salePrices'], true);
//    $row['barcodes'] = json_decode($row['barcodes'], true);
    $bundle = array();
    $bundle['index'] = $key;
    $bundle['available'] = 'available';
    $bundle['name'] = preg_replace('/[^0-9a-zа-я _-]/ui', '', $row['name']);
    if (isset($row['attributes']['a4e869cf-8dc0-11e9-9ff4-31500015e1ea'])) {
        $bundle['price'] = $row['attributes']['a4e869cf-8dc0-11e9-9ff4-31500015e1ea'];
    } else {
        $bundle['price'] = $row['salePrices'][0]['value'] / 100;
    }

    if ($bundle['price'] < 100) {
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

    if (isset($row['attributes']['f3f556dc-afe9-11e7-7a6c-d2a900036ddd'])) {
        $bundle['vendor'] = $customEntities[$row['attributes']['f3f556dc-afe9-11e7-7a6c-d2a900036ddd']]['name'];
    } else {
        $bundle['vendor'] = false;
    }

    echo $row['name'] . '<br>';
    flush();
    @ob_flush();

    $bundle['model'] = $row['attributes']['0df1fcd2-42e3-11e8-9109-f8fc000411c7'] ?? false;

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
                <offer id="<?= $product['index']; ?>" available="<?= $product['stock'] ? 'true' : 'false'; ?>">
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

file_put_contents('amaze_feed_goodsv3.xml', '<?phpxml version="1.0" encoding="utf-8"?>' . $xml);


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
                <offer id="<?= $product['index']; ?>" available="<?= $product['stock'] ? 'true' : 'false'; ?>">
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

file_put_contents('nz_feed_goodsv3.xml', '<?phpxml version="1.0" encoding="utf-8"?>' . $xml);



if ($errors) {
    telegram('В выгрузке GOODS найдены ошибки (' . $errors . ') <a href="http://goods.ltplk.ru/feed/feed_goodsv3.php">Посмотреть</a>', '-289839597');
}

?>
<style>
    a:visited {
        opacity: 0.5;
    }
</style>