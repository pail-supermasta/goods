<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.12.2019
 * Time: 13:29
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../class/Telegram.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Avaks\MS\OrderMS;

$spreadsheet = new Spreadsheet();


$directory = 'files/new';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
foreach ($scanned_directory as $file) {
    $result = getDSH($file);
    if ($result != false) {
        rename("files/new/$file", "files/old/$file");
    }

}

function addDSH($orderData)
{
    $result = false;

    $ordername = $orderData[0];
    $DSHSumm = str_replace('t', "", $orderData[6]);
    $DSHSumNum = (float)$DSHSumm;
//    $DSHSumComment = " ДШ Сумма: $DSHSumm";

    $orderMS = new OrderMS('', $ordername);
    $orderDetails = $orderMS->getByName();
    $orderMS->id = $orderDetails['id'];


//    $result = $orderMS->setDSHSum($orderDetails['description'], $DSHSumNum, $DSHSumComment);
    $result = $orderMS->setDSHSum($DSHSumNum);


    echo "$ordername $DSHSumNum\n";

    return $result;
}


function getDSH($inputFileName)
{
    try {
        $spreadsheet = IOFactory::load("files/new/$inputFileName");
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        die('Error loading file: ' . $e->getMessage());
    }

    try {
        $sheet = $spreadsheet->getActiveSheet();
        $maxCell = $sheet->getHighestDataRow("B");
        $data = $sheet->rangeToArray('B5:H' . $maxCell);
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        die('Error getActiveSheet: ' . $e->getMessage());
    }

    if (isset($data) && sizeof($data) > 0) {
        foreach ($data as $orderData) {

            $result = addDSH($orderData);
            if (strpos($result, 'обработка-ошибок') > 0) {
                telegram("error found for addDSH", '-289839597');
                error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $result . " " . $orderData . PHP_EOL, 3, "addDSH.log");
                return false;
            }
        }
        return true;

    } else {
        return false;
    }


}










