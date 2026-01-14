<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'cashier' && $_SESSION['role'] !== 'viewer') {
    header('location: products.php');
    exit;
}

include 'php/conn.php';

$history_log = mysqli_query($conn," SELECT log_stok.*, produk.nama_produk FROM log_stok JOIN produk ON log_stok.id_produk = produk.id_produk ORDER BY log_stok.tanggal DESC ");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All History | Stok Barang</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/home.css">
</head>
<body>
    <div class="container-all-history">
        <a href="product-out.php">Back</a>
        <h2>All History</h2>
        <hr>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Product Name</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Note</th>
                        <th>Shipment Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no_log = 1;
                    if(mysqli_num_rows($history_log) > 0): ?>
                        <?php while($row_log = mysqli_fetch_assoc($history_log)): ?>
                        <tr>
                            <td><?= $no_log++; ?></td>
                            <td><?= htmlspecialchars($row_log['nama_produk']) ?></td>
                            <td>
                        <?= $row_log['tipe'] === 'keluar' ? 'OUT' : 'IN'; ?>
                        </td>
                        <td><?= $row_log['jumlah'] ?></td>
                        <td><?= $row_log['tanggal'] ?></td>
                        <td><?= htmlspecialchars($row_log['keterangan']) ?></td>
                        <td><?= $row_log['penjualan'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>