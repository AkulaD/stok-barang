<?php
session_start();
include 'conn.php';

if (!isset($_POST['id_log'])) {
    header('location: ../edit-transaction.php');
    exit;
}

$ids = $_POST['id_log'];

$idList = "'" . implode("','", $ids) . "'";

$conn->query("
    UPDATE transaksi
    SET status = 1
    WHERE id_log IN ($idList)
");

header('location: ../edit-transaction.php');
exit;
