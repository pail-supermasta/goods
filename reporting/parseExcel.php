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
    $DSHSummL = str_replace('t', "", $orderData[11]);
//    $DSHSummN = str_replace('t', "", $orderData[13]);
    $DSHSummO = str_replace('t', "", $orderData[14]);
    $DSHSummS = str_replace('t', "", $orderData[18]);
    $DSHSummT = str_replace('t', "", $orderData[19]);

    $DSHSumNum = (float)$DSHSummL + (float)$DSHSummO + (float)$DSHSummS + (float)$DSHSummT;

    /*var_export("CHECK DSH AND LOG SUMs FIRST, THEN COMMENT THIS!");
    var_dump($DSHSummS);
    var_dump($DSHSumNum);
    die();*/
    $DSHSummM = str_replace('t', "", $orderData[12]);
    $DSHSumComment = " Cost payments: $DSHSummM";

    $LogisticSummP = str_replace('t', "", $orderData[15]);
    $LogisticSummQ = str_replace('t', "", $orderData[16]);
    $LogisticSumNum = (float)$LogisticSummP + (float)$LogisticSummQ;

    $orderMS = new OrderMS('', $ordername);
    $orderDetails = $orderMS->getByName();
    $orderMS->id = $orderDetails['id'];


    $result = $orderMS->setDSHSumAndLogisticSum(
        $orderDetails['description'],
        $DSHSumNum,
        $LogisticSumNum,
        $DSHSumComment);


    echo "$ordername $DSHSumNum\n $LogisticSumNum\n $DSHSumComment\n";

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
        $maxCell = $sheet->getHighestDataRow("A");
        $data = $sheet->rangeToArray('A4:T' . $maxCell);
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        die('Error getActiveSheet: ' . $e->getMessage());
    }

    if (isset($data) && sizeof($data) > 0) {
        foreach ($data as $orderData) {

            $result = addDSH($orderData);
            if (strpos($result, 'обработка-ошибок') > 0) {
//                telegram("error found for addDSH", '-289839597');
//                error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $result . " " . $orderData . PHP_EOL, 3, "addDSH.log");
                var_export($result);
                var_dump('error here');
                return false;
            }
        }
        return true;

    } else {
        return false;
    }


}










