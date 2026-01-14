<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:login.php');
    exit;
}
if (!isset($_SESSION['role'])) {
    header('location: login.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'viewer', 'finance'])) {
    header('location: product-in.php');
    exit;
}

include 'php/conn.php';

$totalItems = $conn->query("
    SELECT IFNULL(SUM(jumlah),0) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
")->fetch_assoc();

$totalRevenue = $conn->query("
    SELECT IFNULL(SUM(jumlah * harga),0) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
")->fetch_assoc();

$totalTransaction = $conn->query("
    SELECT COUNT(id_log) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
")->fetch_assoc();

$averageRevenue = $totalTransaction['total'] > 0
    ? $totalRevenue['total'] / $totalTransaction['total']
    : 0;

$hourlyResult = $conn->query("
    SELECT HOUR(tanggal) AS jam, SUM(jumlah) AS total
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
    GROUP BY HOUR(tanggal)
    ORDER BY jam
");

$hours = [];
$totals = [];

while ($row = $hourlyResult->fetch_assoc()) {
    $hours[] = str_pad($row['jam'], 2, '0', STR_PAD_LEFT) . ':00';
    $totals[] = $row['total'];
}

$shipmentResult = $conn->query("
    SELECT IFNULL(penjualan,'Unknown') AS lokasi,
           SUM(jumlah) AS total_item,
           SUM(jumlah * harga) AS total_revenue
    FROM log_stok
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
    GROUP BY lokasi
");

$shipLabels = [];
$shipTotals = [];
$shipRevenue = [];

while ($row = $shipmentResult->fetch_assoc()) {
    $shipLabels[] = $row['lokasi'];
    $shipTotals[] = $row['total_item'];
    $shipRevenue[] = $row['total_revenue'];
}

$allProductResult = $conn->query("
    SELECT 
        p.nama_produk,
        SUM(l.jumlah) AS total,
        SUM(l.jumlah * l.harga) AS revenue
    FROM log_stok l
    JOIN produk p ON l.id_produk = p.id_produk
    WHERE l.tipe = 'keluar'
    AND DATE(l.tanggal) = CURDATE()
    GROUP BY l.id_produk
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="data/js/userM.js" defer></script>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/penjualan.css">
    <title>Sales - Stock | Stok Barang</title>
</head>
<body>

<?php include 'Partials/nav.php'; ?>

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
                    <p>Average Revenue</p>
                    <p>Rp <?= number_format($averageRevenue, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>


        <div class="chart-grid">

            <div class="list-card chart-card">
                <h2>Hourly Sales</h2>
                <div class="chart-scroll">
                    <div class="chart-canvas">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="list-card chart-card">
                <h2>Shipment Revenue Today</h2>
                <div class="chart-scroll">
                    <div class="chart-canvas">
                        <canvas id="shipmentChart"></canvas>
                    </div>

                    <div class="shipment-info">
                        <?php foreach ($shipLabels as $i => $loc): ?>
                            <div class="ship-row">
                                <span><?= $loc; ?></span>
                                <strong>Rp <?= number_format($shipRevenue[$i], 0, ',', '.'); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>  

        <div class="list-card">
            <h2>All Sold Products Today</h2>

            <table>
                <tr>
                    <th>No</th>
                    <th>Product</th>
                    <th>Sold</th>
                    <th>Revenue</th>
                </tr>

                <?php
                $no = 1;
                if ($allProductResult->num_rows > 0) {
                    while ($row = $allProductResult->fetch_assoc()) {
                        echo "
                        <tr>
                            <td>{$no}</td>
                            <td>{$row['nama_produk']}</td>
                            <td>{$row['total']}</td>
                            <td>Rp " . number_format($row['revenue'], 0, ',', '.') . "</td>
                        </tr>";
                        $no++;
                    }
                } else {
                    echo "
                    <tr>
                        <td colspan='4'>No sales today</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
        <?php include "partials/info-penjualan-m.php"; ?>
    </section>
    
</main>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('hourlyChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($hours); ?>,
        datasets: [{
            data: <?= json_encode($totals); ?>,
            borderWidth: 2,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        resizeDelay: 100,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('shipmentChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($shipLabels); ?>,
        datasets: [{
            data: <?= json_encode($shipTotals); ?>,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        resizeDelay: 100,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

</script>

</body>
</html>
