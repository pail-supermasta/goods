<?
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once '../vendor/autoload.php';
require_once '../class/Telegram.php';


use Avaks\SQL\AvaksSQL;
use Avaks\MS\MSSync;
use Avaks\MS\Stocks;
use Avaks\MS\Products;
use Avaks\MS\Bundles;

// old product db new stocks - no usage

//report_stock_all
$stocks = new Stocks();
$stockMS = $stocks->getAll();


$products = array();
$errors = 0;

$stores = array(
    'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
    // 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);

//$rows = $sql->query("SELECT *  FROM `ms_customEntities`")->rows;
$rows = AvaksSQL::selectAllAssoc("SELECT *  FROM `ms_customEntities`");

foreach ($rows as $key => $row) {
    $customEntities[$row['id']] = $row;
}

$categories = array();
$rows = AvaksSQL::selectAllAssoc("SELECT *  FROM `ms_customEntities` WHERE `customEntityMeta` = '55bae67d-0103-11e8-7a34-5acf000aab6a'");
//$rows = $sql->query("SELECT *  FROM `ms_customEntities` WHERE `customEntityMeta` = '55bae67d-0103-11e8-7a34-5acf000aab6a'")->rows;
foreach ($rows as $key => $row) {
    $row['index'] = $key + 1;
    $row['products_count'] = 0;
    $categories[$row['id']] = $row;
}



//$rows = $sql->query("SELECT * FROM `ms_product` WHERE `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ")->rows; //LIMIT 100
$rows = AvaksSQL::selectAllAssoc("SELECT * FROM `ms_product` WHERE `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ");

foreach ($rows as $key => $row) {
    $row['meta'] = json_decode($row['meta'], true);
    $row['attributes'] = json_decode($row['attributes'], true);
    $row['salePrices'] = json_decode($row['salePrices'], true);
    $row['barcodes'] = json_decode($row['barcodes'], true);
    $product = array();
    $product['index'] = $row['index'];
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

    if (!isset($row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана категория (Предмет WB) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }

    $product['categoryId'] = $categories[$row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7']]['index'];
    $product['outlets'] = 'outlets';
//    $product['stock'] = getMsStock($row['id'], $stores);
    $product['stock'] = $stockMS[$row['id']];
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
    // continue;

    $product['model'] = $row['attributes']['0df1fcd2-42e3-11e8-9109-f8fc000411c7'] ?? false;

    $categories[$row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7']]['products_count']++;

    $products[] = $product;
}


//$rows = $sql->query("SELECT * FROM `ms_bundle` WHERE `name` like '%GD' and  `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ")->rows; //LIMIT 100
$rows = AvaksSQL::selectAllAssoc("SELECT * FROM `ms_bundle` WHERE `name` like '%GD' and  `deleted` = '' AND `attributes` LIKE '%7dec0412-3fed-11e9-9109-f8fc00040f83\":true%' ");

foreach ($rows as $key => $row) {
    $row['meta'] = json_decode($row['meta'], true);
    $row['attributes'] = json_decode($row['attributes'], true);
    $row['salePrices'] = json_decode($row['salePrices'], true);
    $row['barcodes'] = json_decode($row['barcodes'], true);
    $product = array();
    $product['index'] = $row['index'];
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

    if (!isset($row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7'])) {
        echo '<a class="btn btn-outline-danger mb-3" href="' . $row['meta']['uuidHref'] . '" target="_blank">Не указана категория (Предмет WB) для «' . $row['name'] . '»</a><br>';
        $errors++;
        flush();
        @ob_flush();
        continue;
    }

    $product['categoryId'] = $categories[$row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7']]['index'];
    $product['outlets'] = 'outlets';
//    $product['stock'] = getMsStock($row['id'], $stores);
    $product['stock'] = $stockMS[$row['id']];
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
    // continue;

    $product['model'] = $row['attributes']['0df1fcd2-42e3-11e8-9109-f8fc000411c7'] ?? false;

    $categories[$row['attributes']['031b1310-0106-11e8-7a6c-d2a9000c0ea7']]['products_count']++;

    $products[] = $product;
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
            <? foreach ($categories as $key => $category) {
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
            <? foreach ($products as $key => $product) { ?>
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
            <? } ?>
        </offers>
    </shop>
</yml_catalog>
<?
$xml = ob_get_contents();
ob_end_clean();

/*file_put_contents(__DIR__ . '/../feed_goodsv2.xml', '<?xml version="1.0" encoding="utf-8"?>' . $xml);*/
file_put_contents('amaze_feed_goodsv4.xml', '<?xml version="1.0" encoding="utf-8"?>' . $xml);


ob_start(); ?>

<yml_catalog date="<?= date('Y-m-d H:i'); ?>">
    <shop>
        <name>Интернет-магазин</name>
        <company>ООО «Незабудка»</company>
        <currencies>
            <currency id="RUR" rate="1"/>
        </currencies>
        <categories>
            <? foreach ($categories as $key => $category) {
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
            <? foreach ($products as $key => $product) { ?>
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
            <? } ?>
        </offers>
    </shop>
</yml_catalog>
<?
$xml = ob_get_contents();
ob_end_clean();

/*file_put_contents(__DIR__ . '/../../ltplk.ru/feed_goodsv2.xml', '<?xml version="1.0" encoding="utf-8"?>' . $xml);*/
file_put_contents('nz_feed_goodsv4.xml', '<?xml version="1.0" encoding="utf-8"?>' . $xml);


var_dump('Complete');

echo "Ссылка на фид: <a href=\"https://api.avaks.org/feed_goodsv2.xml\" target=\"_blank\">https://api.avaks.org/feed_goodsv2.xml</a>";

if ($errors) {
    telegram('В выгрузке GOODS найдены ошибки (' . $errors . ') <a href="https://api.avaks.org/?page=feed_goods">Посмотреть</a>', '-307834682');
}

?>
<style>
    a:visited {
        opacity: 0.5;
    }
</style>