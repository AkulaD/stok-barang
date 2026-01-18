
<?php
session_start();

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin','finance'])) {
    header('location: penjualan.php');
    exit;
}

include 'conn.php';

$id = $_GET['id'] ?? '';

$data = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
        l.id_log,
        l.jumlah,
        l.harga,
        p.nama_produk
    FROM log_stok l
    JOIN produk p ON l.id_produk = p.id_produk
    WHERE l.id_log = '$id'
    AND l.tipe = 'keluar'
"));

if (!$data) {
    header('location: penjualan.php');
    exit;
}

if (isset($_POST['update'])) {
    $harga = (int) $_POST['harga'];

    mysqli_query($conn,"
        UPDATE log_stok 
        SET harga = $harga
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
                <button type="submit" name="update">Update Harga</button>
                <a href="../penjualan.php">Cancel</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
