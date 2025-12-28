<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'cashier' && $_SESSION['role'] !== 'viewer') {
    header('location: ../product-in.php');
    exit;
}

include 'conn.php';

$qr_number = trim($_POST['qr_number']);
$penjualan = trim($_POST['penjualan']);

mysqli_begin_transaction($conn);

try {

    $stmt = $conn->prepare(
        "SELECT id_produk, stok, harga 
         FROM produk 
         WHERE barcode = ? 
         FOR UPDATE"
    );
    $stmt->bind_param("s", $qr_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Produk tidak ditemukan');
    }

    $product = $result->fetch_assoc();

    if ((int)$product['stok'] <= 0) {
        throw new Exception('Stok habis');
    }


    $stmt = $conn->prepare(
        "UPDATE produk 
         SET stok = stok - 1 
         WHERE id_produk = ?"
    );
    $stmt->bind_param("s", $product['id_produk']);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception('Gagal update stok');
    }

    $log_id = 'TRX-' . bin2hex(random_bytes(6));

    $stmt = $conn->prepare(
        "INSERT INTO log_stok
        (id_log, id_produk, tipe, jumlah, keterangan, penjualan, harga)
        VALUES (?, ?, 'keluar', 1, 'Scan barcode', ?, ?)"
    );
    $stmt->bind_param(
        "ssss",
        $log_id,
        $product['id_produk'],
        $penjualan,
        $product['harga']
    );
    $stmt->execute();

    mysqli_commit($conn);

    header("Location: ../product-out.php?out=success");
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);
    header("Location: ../product-out.php?out=failure&msg=" . urlencode($e->getMessage()));
    exit;
}
