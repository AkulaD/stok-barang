<?php
session_start();
if(!isset($_SESSION['login'])){
    header('location: ../login.php');
    exit;
}


include 'conn.php';
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = $_POST['product'];
    $stock        = (int) $_POST['stock'];
    $price        = (int) $_POST['price'];

    $id_product = 'PRD' . bin2hex(random_bytes(10));

    $query = "SELECT COUNT(*) AS total FROM produk WHERE DATE(created_at) = CURDATE()";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    $list  = $row['total'] + 1;
    $order = str_pad($list, 4, '0', STR_PAD_LEFT);

    $barcode = 'PRD'
        . $order
        . date('d')
        . date('m')
        . date('Y')
        . date('H')
        . date('i');

    $stmt = $conn->prepare(
        "INSERT INTO produk (id_produk, barcode, nama_produk, stok, harga)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssii",
        $id_product,
        $barcode,
        $product_name,
        $stock,
        $price
    );

    if ($stmt->execute()) {
        header("Location: ../products.php?add=success");
        exit;
    } else {
        header("Location: ../products.php?add=failure");
        exit;
    }
}
