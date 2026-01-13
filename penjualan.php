<?php
session_start();

if(!isset($_SESSION['login'])){
    header('location:login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'finance') {
    header('location: product-out.php');
    exit;
}

include 'php/conn.php';

$totalItemsQuery = "
    SELECT IFNULL(SUM(jumlah),0) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
";
$totalItems = $conn->query($totalItemsQuery)->fetch_assoc();

$totalRevenueQuery = "
    SELECT IFNULL(SUM(jumlah * harga),0) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
";
$totalRevenue = $conn->query($totalRevenueQuery)->fetch_assoc();

$totalTransactionQuery = "
    SELECT COUNT(id_log) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
";
$totalTransaction = $conn->query($totalTransactionQuery)->fetch_assoc();

$averageRevenue = 0;
if ($totalTransaction['total'] > 0) {
    $averageRevenue = $totalRevenue['total'] / $totalTransaction['total'];
}

$hourlyQuery = "
    SELECT HOUR(tanggal) AS hour, SUM(jumlah) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
    GROUP BY HOUR(tanggal)
    ORDER BY hour
";
$hourlyResult = $conn->query($hourlyQuery);

$hours = [];
$totals = [];

while ($row = $hourlyResult->fetch_assoc()) {
    $hours[] = str_pad($row['hour'], 2, '0', STR_PAD_LEFT) . ':00';
    $totals[] = $row['total'];
}

$allProductQuery = "
    SELECT p.nama_produk, SUM(l.jumlah) AS total
    FROM log_stok l
    JOIN produk p ON l.id_produk = p.id_produk
    WHERE l.tipe = 'keluar'
    AND DATE(l.tanggal) = CURDATE()
    GROUP BY l.id_produk
    ORDER BY p.nama_produk ASC
";
$allProductResult = $conn->query($allProductQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/penjualan.css">
    <title>Sales Dashboard</title>
</head>
<body>

<header>
    <nav class="nav-desktop">
        <div class="left-side">
            <ul>
                <li><a href="product-in.php">Product In</a></li>
                <li><a href="product-out.php">Product Out</a></li>
                <li><a href="penjualan.php">Sales</a></li>
                <?php
                if($_SESSION['role'] === 'admin'){
                    echo '<li><a href="user-management.php">User</a></li>';
                }
                ?>
            </ul>
        </div>
        <div class="right-side">
            <ul>
                <li><a href="php/logout.php">Log Out</a></li>
                <li><p><?= $_SESSION['username']; ?></p></li>
            </ul>
        </div>
    </nav>

    <nav class="nav-mobile">
        <div class="nav-mobile-head">
            <span class="brand">Stok Barang</span>
            <button class="nav-toggle">☰</button>
        </div>

        <ul class="nav-mobile-menu">
            <li><a href="product-in.php">Product In</a></li>
            <li><a href="product-out.php">Product Out</a></li>
            <li><a href="penjualan.php">Sales</a></li>

            <?php
            if($_SESSION['role'] === 'admin'){
                echo '<li><a href="user-management.php">User</a></li>';
            }
            ?>

            <li class="divider"></li>
            <li class="user"><?= $_SESSION['username']; ?></li>
            <li><a href="php/logout.php">Log Out</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="sidebar">
        <ul>
            <li><a href="penjualan.php">Main</a></li>
            <li><a href="mutasi.php">Excel</a></li>
            <li><a href="report.php">Report</a></li>
        </ul>
    </section>

    <section class="main">
        <div class="list-card">
            <h2>Today's Statistics</h2>

            <div class="list-wrap">
                <div class="list-body">
                    <p>Total Items Sold</p>
                    <p><?= $totalItems['total']; ?></p>
                </div>

                <div class="list-body">
                    <p>Total Revenue</p>
                    <p>Rp <?= number_format($totalRevenue['total'], 0, ',', '.'); ?></p>
                </div>

                <div class="list-body">
                    <p>Total Transactions</p>
                    <p><?= $totalTransaction['total']; ?></p>
                </div>

                <div class="list-body">
                    <p>Average Revenue / Transaction</p>
                    <p>Rp <?= number_format($averageRevenue, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <div class="list-card">
            <h2>Hourly Sales</h2>
            <canvas id="salesChart" height="120"></canvas>
        </div>

        <div class="list-card">
            <h2>All Sold Products Today</h2>

            <table cellpadding="8" cellspacing="0">
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Sold</th>
                </tr>
                <?php
                $no = 1;
                if ($allProductResult->num_rows > 0) {
                    while ($row = $allProductResult->fetch_assoc()) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['nama_produk']}</td>
                                <td>{$row['total']}</td>
                            </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr>
                            <td colspan='3'>No sales today</td>
                        </tr>";
                }
                ?>
            </table>
        </div>

    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($hours); ?>,
        datasets: [{
            label: 'Items Sold',
            data: <?= json_encode($totals); ?>,
            borderWidth: 2,
            fill: false
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
