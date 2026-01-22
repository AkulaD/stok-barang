<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'uploader' && $_SESSION['role'] !== 'viewer') {
    header('location: ../product-out.php');
    exit;
}

include 'conn.php';
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = trim($_POST['product']);
    $stock        = (int) $_POST['stock'];
    $price        = (int) $_POST['price'];

    $id_product = 'PRD-' . date('YmdHis') . bin2hex(random_bytes(4));

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

    mysqli_begin_transaction($conn);

    try {
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
        $stmt->execute();

        $id_log = 'NEW-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
        $log_stmt = $conn->prepare(
            "INSERT INTO log_stok (id_log, id_produk, nama_produk, tipe, jumlah, keterangan, harga) 
             VALUES (?, ?, ?, 'masuk', ?, 'Produk Baru', ?)"
        );
        
        $log_stmt->bind_param("sssii", $id_log, $id_product, $product_name, $stock, $price);
        $log_stmt->execute();

        mysqli_commit($conn);
        header("Location: ../product-in.php?add=success");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../product-in.php?add=failure");
        exit;
    }
}