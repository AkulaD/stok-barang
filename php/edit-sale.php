<?php
session_start();

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin','finance'])) {
    header('location: ../penjualan.php');
    exit;
}

include 'conn.php';

$id = $_GET['id'] ?? '';

$data = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
        id_log,
        jumlah,
        harga,
        nama_produk
    FROM transaksi
    WHERE id_log = '$id'
    AND tipe = 'keluar'
"));

if (!$data) {
    header('location: ../penjualan.php');
    exit;
}

if (isset($_POST['update'])) {
    $hargaBaru = (int) $_POST['harga'];
    $hargaLama = (int) $data['harga'];
    $namaProduk = $data['nama_produk'];
    $idLog = $data['id_log'];
    $idHistory = 'HIS -' . date('YmdHis') . '-' . bin2hex(random_bytes(3));

    mysqli_query($conn,"
        INSERT INTO history_perubahan 
        (id, id_log, nama_produk, hargaLama, hargaBaru)
        VALUES 
        ('$idHistory', '$idLog', '$namaProduk', $hargaLama, $hargaBaru)
    ");

    mysqli_query($conn,"
        UPDATE transaksi 
        SET harga = $hargaBaru,
            status = '1'
        WHERE id_log = '$id'
    ");

    header('location: ../penjualan.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Harga</title>
    <link rel="stylesheet" href="../data/css/style.css">
    <link rel="stylesheet" href="../data/css/edit-sale.css">
</head>
<body>


<main>
    <div class="edit-container">
        <form method="post" class="edit-card">
            <h2>Edit Harga Penjualan</h2>

            <div class="form-row">
                <label>Product</label>
                <input type="text" value="<?= $data['nama_produk'] ?>" disabled>
            </div>

            <div class="form-row">
                <label>Quantity</label>
                <input type="number" value="<?= $data['jumlah'] ?>" disabled>
            </div>

            <div class="form-row">
                <label>Harga</label>
                <input type="number" name="harga" value="<?= $data['harga'] ?>" required>
            </div>

            <div class="form-btn">
                <button class="btn-submit" type="submit" name="update">Update Harga</button>
                <a href="../penjualan.php">Cancel</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
