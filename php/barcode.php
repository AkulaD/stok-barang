<?php
session_start();
if(!isset($_SESSION['login'])){
    header('location: ../login.php');
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

if (!isset($_GET['code'])) {
    exit('Barcode not found');
}

$code = $_GET['code'];

$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode(
    $code,
    $generator::TYPE_CODE_128
);

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="barcode-'.$code.'.png"');

echo $barcode;
