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
    $DSHSummL = str_replace(",",".",$DSHSummL);
    $DSHSummL = preg_replace('/\.(?=.*\.)/', '', $DSHSummL);

    $DSHSummO = str_replace('t', "", $orderData[14]);
    $DSHSummO = str_replace(",",".",$DSHSummO);
    $DSHSummO = preg_replace('/\.(?=.*\.)/', '', $DSHSummO);

    $DSHSummS = str_replace('t', "", $orderData[18]);
    $DSHSummS = str_replace(",",".",$DSHSummS);
    $DSHSummS = preg_replace('/\.(?=.*\.)/', '', $DSHSummS);

    $DSHSummT = str_replace('t', "", $orderData[19]);
    $DSHSummT = str_replace(",",".",$DSHSummT);
    $DSHSummT = preg_replace('/\.(?=.*\.)/', '', $DSHSummT);



    $DSHSumNum = (float)$DSHSummL + (float)$DSHSummO + (float)$DSHSummS + (float)$DSHSummT;

    $DSHSumComment = "";
    if($orderData[12]){
        $DSHSummM = str_replace('t', "", $orderData[12]);
        $DSHSummM = str_replace(",",".",$DSHSummM);
        $DSHSummM = preg_replace('/\.(?=.*\.)/', '', $DSHSummM);

        $DSHSumComment = "\r\nCost payments: $DSHSummM";
    }


    $LogisticSummP = str_replace('t', "", $orderData[15]);
    $LogisticSummP = str_replace(",",".",$LogisticSummP);
    $LogisticSummP = preg_replace('/\.(?=.*\.)/', '', $LogisticSummP);

    $LogisticSummQ = str_replace('t', "", $orderData[16]);
    $LogisticSummQ = str_replace(",",".",$LogisticSummQ);
    $LogisticSummQ = preg_replace('/\.(?=.*\.)/', '', $LogisticSummQ);


    $LogisticSumNum = (float)$LogisticSummP + (float)$LogisticSummQ;

//    var_export("CHECK DSH AND LOG SUMs FIRST, THEN COMMENT THIS!");
//    var_dump("DSHSumNum: " . $DSHSumNum);
//    var_dump("LogisticSumNum: " . $LogisticSumNum);
//    var_dump($DSHSumComment);
//    die();

    $orderMS = new OrderMS('', $ordername);
    $orderDetails = $orderMS->getByName();
    $orderMS->id = $orderDetails['id'];



    $result = $orderMS->setDSHSumAndLogisticSum(
        $orderDetails['description'],
        $DSHSumNum,
        $LogisticSumNum,
        $DSHSumComment);

    if (strpos($result, 'обработка-ошибок') > 0 || $result == '') {
        var_dump($ordername . " has just caused an error. Cannot proceed any further!");
        die();
    }

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










