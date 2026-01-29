<?php
session_start();

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin','finance'])) {
    header('location: ../penjualan.php');
    exit;
}

include 'php/conn.php';

$data = mysqli_query($conn,"
    SELECT 
        id,
        id_log,
        nama_produk,
        hargaLama,
        hargaBaru,
        tanggal
    FROM history_perubahan
    ORDER BY tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History | </title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/table.css">
</head>
<body>

<main>
    <div class="main">
        <div class="list-card">
            <a href="edit-transaction.php">Back</a>
            <h2>Log History Perubahan Harga</h2>

            <div class="table-sales">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaction</th>
                            <th>Nama Produk</th>
                            <th>Harga Lama</th>
                            <th>Harga Baru</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($data)) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['id_log'] ?></td>
                            <td><?= $row['nama_produk'] ?></td>
                            <td>Rp <?= number_format($row['hargaLama'],0,',','.') ?></td>
                            <td>Rp <?= number_format($row['hargaBaru'],0,',','.') ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>
