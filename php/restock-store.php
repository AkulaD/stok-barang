<?php
session_start();

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'uploader'])) {
    header('location: ../index.php');
    exit;
}

include 'conn.php';

$id_produk = $_GET['id'] ?? '';

if ($id_produk == '') {
    header('location: ../warehouse.php');
    exit;
}

$product = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        p.nama_produk,
        p.stok AS stok_toko,
        g.stok_gudang
    FROM produk p
    JOIN gudang g ON g.id_barang = p.id_produk
    WHERE p.id_produk = '$id_produk'
"));

if (!$product) {
    header('location: ../warehouse.php');
    exit;
}

if (empty($_SESSION['restock_token'])) {
    $_SESSION['restock_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if (isset($_POST['submit'])) {

    if (!hash_equals($_SESSION['restock_token'], $_POST['token'])) {
        $error = 'Invalid request';
    } else {

        $jumlah = (int) $_POST['jumlah'];

        if ($jumlah <= 0) {
            $error = 'Jumlah tidak valid';
        } elseif ($jumlah > $product['stok_gudang']) {
            $error = 'Stok warehouse tidak mencukupi';
        } else {

            mysqli_begin_transaction($conn);

            try {

                mysqli_query($conn, "
                    UPDATE gudang 
                    SET stok_gudang = stok_gudang - $jumlah
                    WHERE id_barang = '$id_produk'
                ");

                mysqli_query($conn, "
                    UPDATE produk 
                    SET stok = stok + $jumlah
                    WHERE id_produk = '$id_produk'
                ");

                $id_log = uniqid('LG-');

                mysqli_query($conn, "
                    INSERT INTO log_stok 
                    (id_log, id_produk, tipe, jumlah, keterangan)
                    VALUES
                    ('$id_log', '$id_produk', 'masuk', $jumlah, 'warehouse')
                ");

                mysqli_commit($conn);

                unset($_SESSION['restock_token']);
                $_SESSION['restock_token'] = bin2hex(random_bytes(32));

                $success = 'Restock berhasil';
                $product['stok_gudang'] -= $jumlah;
                $product['stok_toko'] += $jumlah;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Terjadi kesalahan';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restock Store</title>
    <link rel="stylesheet" href="../data/css/style.css">
    <link rel="stylesheet" href="../data/css/restock-store.css">
</head>
<body>

<main class="restock-container">

    <div class="restock-card">
        <h2>Restock to Store</h2>

        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <div class="info-box">
            <p><strong>Product</strong> : <?= $product['nama_produk'] ?></p>
            <p><strong>Warehouse Stock</strong> : <?= $product['stok_gudang'] ?></p>
            <p><strong>Store Stock</strong> : <?= $product['stok_toko'] ?></p>
        </div>

        <form method="post">
            <input type="hidden" name="token" value="<?= $_SESSION['restock_token'] ?>">

            <label>Jumlah Restock</label>
            <input 
                type="number" 
                name="jumlah" 
                min="1" 
                max="<?= $product['stok_gudang'] ?>" 
                required
            >

            <button type="submit" name="submit" class="btn-submit">
                Restock
            </button>

            <a href="../warehouse.php" class="btn-back">Back</a>
        </form>
    </div>

</main>

</body>
</html>
