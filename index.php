<?php
session_start();
if(!isset($_SESSION['login'])){
    header('location: login.php');
    exit;
}

include 'php/conn.php';
$result = mysqli_query($conn,"SELECT * FROM produk ORDER BY created_at DESC");
$no = 1;

$history_log = mysqli_query($conn,"
    SELECT log_stok.*, produk.nama_produk
    FROM log_stok
    JOIN produk ON log_stok.id_produk = produk.id_produk
    ORDER BY log_stok.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="data/css/home.css">
    <script src="data/js/home.js" defer></script>
</head>
<body>
    <head>
        <nav>
            <div class="left-side">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="info.php">info</a></li>
                </ul>
            </div>
            <div class="right-side">
                <ul>
                    <li><a href="php/logout.php">log Out</a></li>
                    <li><p><?php echo $_SESSION['username']; ?></p></li>
                </ul>
            </div>
        </nav>
    </head>
    <main>
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>

        <form action="php/out-form.php" method="post">
            <div class="qr-number">
                <label>Enter QR Code Number:</label>
                <input type="text" name="qr_number" required>
                <button type="submit">Submit</button>
            </div>
        </form>
        <div class="left-body">
            <h2>Inventory Stock</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Product Name</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><?= $row['stok'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <h2>History Stock</h2>
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
    </main>
</body>
</html>