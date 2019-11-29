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


//header('Content-Type: application/json');

require_once 'vendor/autoload.php';


use Avaks\Goods\StickerTest;

$stickerTest = new StickerTest();

$pdfCode = $stickerTest->printPdf('920989210', 'C12405BF-01CB-4A6C-A41E-0E179EF00F54', '2998');




















