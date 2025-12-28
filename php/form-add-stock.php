<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'uploader') {
    header('location: ../products.php');
    exit;
}

include 'conn.php';

if (!isset($_POST['qr-code'], $_POST['stock'])) {
    header('location: ../product-in.php');
    exit;
}

$qr_code = mysqli_real_escape_string($conn, $_POST['qr-code']);
$jumlah  = (int) $_POST['stock'];

if ($jumlah <= 0) {
    header('location: ../product-in.php?error=invalid_stock');
    exit;
}

$qProduk = mysqli_query($conn, "SELECT * FROM produk WHERE barcode = '$qr_code'");

if (mysqli_num_rows($qProduk) === 0) {
    header('location: ../product-in.php?error=not_found');
    exit;
}

$produk = mysqli_fetch_assoc($qProduk);

$id_produk = $produk['id_produk'];
$stok_lama = (int) $produk['stok'];
$harga     = (int) $produk['harga'];

$stok_baru = $stok_lama + $jumlah;

mysqli_query($conn, "
    UPDATE produk 
    SET stok = $stok_baru 
    WHERE id_produk = '$id_produk'
");

$id_log = 'IN-' . strtoupper(bin2hex(random_bytes(6)));

mysqli_query($conn, "
    INSERT INTO log_stok 
    (id_log, id_produk, tipe, jumlah, keterangan, harga)
    VALUES 
    ('$id_log', '$id_produk', 'masuk', $jumlah, 'Add Stock', $harga)
");

header('location: ../product-in.php?success=1');
exit;
