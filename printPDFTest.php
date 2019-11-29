<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.09.2019
 * Time: 17:33
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

//header('Content-Type: application/json');

require_once 'vendor/autoload.php';

use Avaks\Goods\OrderTest;

require_once 'class/Telegram.php';

$bot = new Telegram('345217125:AAE4o7Bs-QeQnusf3SQ-xSuSBm2RGMVH97w');

function formList($goodsID, $token)
{


    $goods = new OrderTest();
    $goods->shopToken = $token;
    /*получить список упакованных заказов из Гудс с датой отправки Сегодня*/
    $ordersGoods = $goods->getOrdersPackedByShippingDate();

    /*form html orders table*/
    $ordersTable = '';
    $itemsNumber = 0;
    $productsCost = 0;
    $productsCostFinal = 0;
    foreach ($ordersGoods as $orderGoods) {

        $orderDetailsGoods = $goods->getOrder($orderGoods);
        $deliveryId = $orderDetailsGoods['deliveryId'];
        $shipmentId = $orderDetailsGoods['shipmentId'];
        $orderCode = $orderDetailsGoods['orderCode'];
        $orderPositionsGoods = $orderDetailsGoods['items'];
        $productsQuantity = sizeof($orderPositionsGoods);
        echo "щквук№ " . $orderGoods . " " . $productsQuantity . PHP_EOL;

        if ($productsQuantity > 1) {
            $firstRowCells = '';
            $nextRows = '';
            foreach ($orderPositionsGoods as $key => $value) {
//            if ($orderPositionGoods['status'] != 'PACKED') {
//                continue;
//            }
                echo "$key=>$value" . PHP_EOL;
                if ($key == 0) {
                    $firstRowCells = '<td>' . $value['quantity'] . '</td>
                            <td>' . $value['price'] . '</td>
                            <td>' . $value['finalPrice'] . '</td>';
                } else {
                    $nextRows .= '<td>' . $value['quantity'] . '</td>
                            <td>' . $value['price'] . '</td>
                            <td>' . $value['finalPrice'] . '</td>';

                }


            }


            $orderRow = '<tr class="positions">
                            <td rowspan="' . $productsQuantity . '">№п/п</td>
                            <td rowspan="' . $productsQuantity . '">Номер доставки</td>
                            <td rowspan="' . $productsQuantity . '">Номер отправления goods.ru</td>
                            <td rowspan="' . $productsQuantity . '">Номер заказа продавца</td>
                            <td rowspan="' . $productsQuantity . '">Идентификатор грузового места</td>
                            
                            ' . $firstRowCells . '
                            
                          </tr>
                          
                          ' . $nextRows;

        }
        echo $orderRow;
//        die();

        /*get their positions*/
        foreach ($orderPositionsGoods as $orderPositionGoods) {
//            if ($orderPositionGoods['status'] != 'PACKED') {
//                continue;
//            }


            $itemsNumber++;
            $productsCost += $orderPositionGoods['price'];
            $productsCostFinal += $orderPositionGoods['finalPrice'];
            $orderRow = '<tr class="positions">
                        <td>' . $itemsNumber . '</td>
                        <td>' . $deliveryId . '</td>
                        <td>' . $shipmentId . '</td>
                        <td>' . $orderCode . '</td>
                        <td>' . $goodsID . '*' . $shipmentId . '*' . $orderPositionGoods['boxIndex'] . '</td>
                        <td>' . $orderPositionGoods['quantity'] . '</td>
                        <td>' . $orderPositionGoods['price'] . '</td>
                        <td>' . $orderPositionGoods['finalPrice'] . '</td>
                    </tr>';
            $ordersTable .= $orderRow;
        }
    }


    /*fill list date*/
    $date = date('d.m.Y', strtotime(date('c')));
    $dateNoSeparator = date('dmY', strtotime(date('c')));
    /*how many products in the list*/
    $boxTotal = $itemsNumber;
    $productsTotal = $itemsNumber;

    $shopName = '';
    $contractNo = '';
    switch ($goodsID) {
        case "608":
            $shopName = "НОВИНКИ";
            $contractNo = "№ К-115-03-2018";
            break;
        case "2998":
            $shopName = "НЕЗАБУДКА";
            $contractNo = "№ К-1162-07-2019";
            break;
    }


    $html = '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
       
        table {
            border: 1px solid black;
            width: 670px;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            font-size: 11px;
            text-align: center;
            font-weight: normal;
            border-collapse: collapse;
        }
        tr {
            border-collapse: collapse;
        }

        .total {
            border-top: 1px solid black;
        }

        thead tr th {
            padding: 1.2em;
        }

        .positions td {
            padding: 0.5em 1em;
        }

        .description {
            width: 186px;
        }

        .total td {
            padding: 0.5em 1em;
        }
        .colSigh {
            height: 10px;
            border-bottom: 1px solid black;
            width: 104px;
            float: left;
            margin-left: 1px;
            
        }
        .colLabels {
            width: 104px;
            float: left;
            margin-left: 1px;
           
            text-align: center;
        }
        .colMarg {
            width: 104px;
            float: left;
            margin-left: 25px;
           
            text-align: center;
        }
    </style>
</head>

<body style="font-family: serif;line-height: 16px;">
    <div class="head" style="width: 670px; margin: 40px auto 0 auto;">
        <div style="font-size: 14px;font-weight: bold;text-align: center;">Реестр приема-передачи Отправлений
            №' . $goodsID . $dateNoSeparator . '</div>
        <div style="font-size: 12px;margin-bottom: 16px;margin-top: 16px;">
            <div style="width: 48%;">г. Москва</div>
            <div style="width: 48%;text-align: right;float: right;margin-top: -16px;">' . $date . '.г</div>
        </div>
        <div style="font-size: 11px;line-height: 18px;margin-bottom: 12px;">ООО "' . $shopName . '", именуемое в дальнейшем "Заказчик", в лице
            ___________________________________________________________________________________, действующего на
            основании _____________________________, с одной стороны, и ООО "Маркетплейс", именуемое в дальнейшем
            "Исполнитель", в лице ___________________________________________________________________________________, с
            другой стороны, именуемые в дальнейшем "Стороны", настоящим Реестром удостоверяют, что в соответствии с
            условиями договора ' . $contractNo . ' Заказчик передал, а представитель Исполнителя принял отправления
            согласно следующему перечню:</div>
    </div>
    <table style="width: 670px; margin: 40px auto 0 auto;">
        <thead>
            <tr>
                <th>№п/п</th>
                <th>Номер доставки</th>
                <th>Номер отправления goods.ru</th>
                <th>Номер заказа продавца</th>
                <th>Идентификатор грузового места</th>
                <th>Кол-во единиц</th>
                <th>Объявленная стоимость ед. без скидки, руб.</th>
                <th>Объявленная стоимость отправления со скидкой, руб.</th>
            </tr>
        </thead>
        <tbody>
                ' . $ordersTable . '
                <tr class="total">
                    <td colspan="4">Итого</td>
                    <td>' . $boxTotal . '</td>
                    <td>' . $productsTotal . '</td>
                    <td>' . $productsCost . '</td>
                    <td>' . $productsCostFinal . '</td>
                </tr>
            
        </tbody>
    </table>



    <div class="footer" style="width: 670px; margin: 40px auto 0 auto;">
        <div style="font-size: 11px;margin-bottom: 16px;margin-top: 26px;">Передаваемые места отправлений упакованы в
            индивидуальные упаковки. Упаковка не нарушена. Замечаний по внешнему виду мест отправлений со стороны
            представителя Исполнителя не имеется.</div>
        <div style="font-size: 13px;margin-bottom: 16px;margin-top: 29px;font-weight: bold;">
            <div style="width: 48%;">
                <div>Заказчик:</div>
                <div>Отпустил от ООО "' . $shopName . '"
                </div>
            </div>
            <div style="width: 48%;float: right;margin-top: -32px;">Исполнитель:</div>
        </div>
        <div style="font-size: 11px;margin-top: 100px;">
            <div  class="colSigh">
               
            </div>
            <div  class="colSigh">
               
            </div>
            <div class="colSigh" >
               
            </div>
            <div  class="colSigh colMarg">
                
            </div>
            <div  class="colSigh">
                
            </div>
            <div class="colSigh">
               
            </div>
        </div>
        <div style="font-size: 11px;margin-bottom: 16px;">
            <div class="colLabels">
                <div class="label">должность</div>
            </div>
            <div class="colLabels">
                <div class="label">подпись</div>
            </div>
            <div class="colLabels">
                <div class="label">ФИО</div>
            </div>
            <div class="colLabels colMarg">
                <div class="label">должность</div>
            </div>
            <div class="colLabels">
                <div class="label">подпись</div>
            </div>
            <div class="colLabels">
                <div class="label">ФИО</div>
            </div>
        </div>
    </div>
</body>

</html>';

    echo $html;


    $mpdf = new \Mpdf\Mpdf(['margin_left' => '4',
        'margin_right' => '4',
        'margin_top' => '14',
        'margin_bottom' => '14',
        'margin_header' => '0',
        'margin_footer' => '0']);


    $mpdf->WriteHTML($html);
    $newFileName = 'Реестр отгрузки ' . $shopName . ' от ' . $date . '.pdf';
    $mpdf->Output($newFileName, \Mpdf\Output\Destination::FILE);

    return $newFileName;

}

$goodsTokens = array(
//    '608' => '97B1BC55-189D-4EB4-91AF-4B9E9A985B3D',//amaze
    '2998' => 'C12405BF-01CB-4A6C-A41E-0E179EF00F54', //novinki - firdus
//    'НОВИНКИ test' => '6881430B-882F-4C4F-8DCA-14FDAFEBAFEC'
);

foreach ($goodsTokens as $goodsID => $goodsToken) {
    $documentName = formList($goodsID, $goodsToken);

//    $documentName = 'Реестр отгрузки AMAZE от 25.09.2019.pdf';
//    $sendDocumentResponse = $bot->sendDocument('-289839597', $documentName);
//    if ($sendDocumentResponse['success'] == 0) {
//        telegram('error send ' . $documentName, '-289839597');
//    }
//    unlink($documentName);

}