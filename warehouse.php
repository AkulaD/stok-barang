<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: index.php');
    exit;
}

if (!isset($_SESSION['role'])) {
    header('location: index.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'viewer', 'uploader'])) {
    header('location: product-out.php');
    exit;
}

include 'php/conn.php';

$statusG = 1;

if (isset($_POST['submit'])) {
    $id_produk = $_POST['id_produk'];
    $stok = (int) $_POST['stok'];

    $cek = mysqli_query($conn, "SELECT id_gudang FROM gudang WHERE id_barang = '$id_produk'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "
            UPDATE gudang 
            SET stok_gudang = stok_gudang + $stok 
            WHERE id_barang = '$id_produk'
        ");
    } else {
        $id_gudang = uniqid('GD-');
        mysqli_query($conn, "
            INSERT INTO gudang (id_gudang, id_barang, stok_gudang, status) 
            VALUES ('$id_gudang', '$id_produk', $stok, $statusG)
        ");
    }

    $id_log = uniqid('LG-');
    mysqli_query($conn, "
        INSERT INTO log_stok 
        (id_log, id_produk, tipe, jumlah, keterangan) 
        VALUES 
        ('$id_log', '$id_produk', 'masuk', $stok, 'warehouse')
    ");
}

$productForm = mysqli_query($conn, "SELECT id_produk, nama_produk, barcode FROM produk");

$tableQuery = "
    SELECT 
        p.id_produk,
        p.nama_produk,
        p.stok AS stok_toko,
        g.stok_gudang
    FROM produk p
    LEFT JOIN gudang g ON g.id_barang = p.id_produk
";
$tableResult = mysqli_query($conn, $tableQuery);

$totalProduct = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk")
)['total'];

$totalStock = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT SUM(stok) AS total FROM produk")
)['total'] ?? 0;

$needRestock = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk WHERE stok <= 5")
)['total'];

$noStock = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk WHERE stok = 0")
)['total'];

$warehouseStock = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT SUM(stok_gudang) AS total FROM gudang")
)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse | Stok Barang</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/warehouse.css">
    <script src="data/js/products.js" defer></script>
</head>
<body>

<?php include 'partials/nav.php'; ?>

<div id="loading-overlay" style="display:none;">
    <div class="spinner"></div>
    <p>Processing...</p>
</div>

<main>

<section class="nav-product">
    <ul>
        <li><a href="product-in.php">Main</a></li>
        <li><a href="warehouse.php">Warehouse</a></li>
    </ul>
</section>

<section class="info-body">
    <div class="info-wrap">
        <div class="info-stock">
            <h2>Total Product</h2>
            <p><?= $totalProduct ?></p>
        </div>

        <div class="info-tstcok">
            <h2>Total Stock</h2>
            <p><?= $totalStock ?></p>
        </div>

        <div class="info-urgent">
            <h2>Need To Restock</h2>
            <p><?= $needRestock ?> Product</p>
        </div>

        <div class="info-nstock">
            <h2>Product With No Stock</h2>
            <p><?= $noStock ?> Product</p>
        </div>

        <div class="info-stock-w">
            <h2>Warehouse Stock</h2>
            <p><?= $warehouseStock ?></p>
        </div>
    </div>
</section>

<section class="nav-product-1">
    <ul>
        <li><a href="warehouse.php">List Product</a></li>
        <li><a href="warehouse-report.php">Report Monthly</a></li>
    </ul>
</section>

<section class="main-container">

<div class="form-stock">
    <form method="post">
        <h2>Add Stock Warehouse</h2>

        <div class="inp-body">
            <label>Scan Barcode</label>
            <input type="text" id="barcode" placeholder="Scan barcode here" autocomplete="off">
        </div>

        <div class="inp-body">
            <label>Product Name</label>
            <select name="id_produk" id="productSelect" required>
                <option value="">--Select Product--</option>
                <?php while($p = mysqli_fetch_assoc($productForm)) { ?>
                    <option 
                        value="<?= $p['id_produk'] ?>" 
                        data-barcode="<?= $p['barcode'] ?>">
                        <?= $p['nama_produk'] ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="inp-body">
            <label>Add Stock Warehouse</label>
            <input type="number" name="stok" min="1" required>
        </div>

        <div class="inp-btn">
            <button class="btn-submit" type="submit" name="submit">Submit</button>
        </div>
    </form>
</div>

<div class="table-stock">
    <h2>Warehouse Stock</h2>
    <table class="stock">
        <thead>
            <tr>
                <th>No</th>
                <th>Product Name</th>
                <th>Warehouse Stock</th>
                <th>Store Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php $no = 1; ?>
        <?php while($row = mysqli_fetch_assoc($tableResult)) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama_produk'] ?></td>
                <td><?= $row['stok_gudang'] !== null ? $row['stok_gudang'] : 'No Data' ?></td>
                <td><?= $row['stok_toko'] ?></td>
                <td>
                    <?= $row['stok_gudang'] !== null ? '<a href="php/restock-store.php?id='.$row['id_produk'].'">restock to store</a>' : '-' ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</section>
<br>

<?php include "partials/warehouse-1.php"; ?>
</main>
</body>
</html>
