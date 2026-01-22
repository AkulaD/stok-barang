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

$bulan = $_GET['bulan'] ?? date('Y-m');

$totalMasukWarehouse = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(jumlah) AS total
    FROM log_stok
    WHERE tipe='masuk'
    AND keterangan='Warehouse In'
    AND DATE_FORMAT(tanggal,'%Y-%m')='$bulan'
"))['total'] ?? 0;

$totalKeluarWarehouse = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(jumlah) AS total
    FROM log_stok
    WHERE tipe='keluar'
    AND DATE_FORMAT(tanggal,'%Y-%m')='$bulan'
"))['total'] ?? 0;

$topOutProduk = mysqli_query($conn,"
    SELECT nama_produk, SUM(jumlah) AS total
    FROM log_stok
    WHERE tipe='keluar'
    AND DATE_FORMAT(tanggal,'%Y-%m')='$bulan'
    GROUP BY nama_produk
    ORDER BY total DESC
    LIMIT 5
");

$warehouseHistory = mysqli_query($conn,"
    SELECT *
    FROM log_stok
    WHERE (keterangan='Warehouse In' OR keterangan='warehouse')
    AND DATE_FORMAT(tanggal,'%Y-%m')='$bulan'
    ORDER BY tanggal DESC
");

$tableQuery = "
    SELECT 
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
    <link rel="stylesheet" href="data/css/report.css">
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
    <div class="background-auto">

        <div class="warehouse-dashboard">

            <form method="get" class="table-section">
                <h3>Filter Report</h3>

                <div style="display:flex;gap:1rem;flex-wrap:wrap">
                    <input type="month" name="bulan" value="<?= $_GET['bulan'] ?? date('Y-m') ?>">
                    <button class="btn-submit" type="submit">Apply</button>
                </div>
            </form>

            <div class="summary-box">
                <div>
                    <h4>Total Warehouse Intake</h4>
                    <p><?= $totalMasukWarehouse ?? 0 ?></p>
                </div>

                <div>
                    <h4>Total Warehouse Outgoing</h4>
                    <p><?= $totalKeluarWarehouse ?? 0 ?></p>
                </div>

                <div>
                    <h4>Net Stock Change</h4>
                    <p><?= ($totalMasukWarehouse ?? 0) - ($totalKeluarWarehouse ?? 0) ?></p>
                </div>
            </div>

            <div class="chart-box">
                <canvas id="warehouseFlowChart"></canvas>
            </div>

            <div class="chart-box">
                <canvas id="stockCompareChart"></canvas>
            </div>

            <div class="table-section">
                <h3>Top Product Out (Warehouse)</h3>

                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Product</th>
                            <th>Total Out</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($topOutProduk)) {
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['nama_produk'] ?></td>
                            <td><?= $row['total'] ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="table-section">
                <h3>Warehouse History</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($warehouseHistory)) {
                    ?>
                        <tr>
                            <td><?= $row['tanggal'] ?></td>
                            <td><?= $row['nama_produk'] ?></td>
                            <td><?= strtoupper($row['tipe']) ?></td>
                            <td><?= $row['jumlah'] ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</section>
<?php include "partials/warehouse-2.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const productLabels = [
<?php
$labels = [];
$toko = [];
$gudang = [];

mysqli_data_seek($tableResult, 0);
while ($row = mysqli_fetch_assoc($tableResult)) {
    $labels[] = '"' . $row['nama_produk'] . '"';
    $toko[] = (int)$row['stok_toko'];
    $gudang[] = (int)($row['stok_gudang'] ?? 0);
}

echo implode(',', $labels);
?>
];

const stokToko = [<?= implode(',', $toko) ?>];
const stokGudang = [<?= implode(',', $gudang) ?>];

new Chart(document.getElementById('warehouseFlowChart'), {
    type: 'bar',
    data: {
        labels: ['IN', 'OUT'],
        datasets: [{
            label: 'Warehouse Stock Flow',
            data: [<?= $totalMasukWarehouse ?>, <?= $totalKeluarWarehouse ?>]
        }]
    },
    options: {
        responsive: true
    }
});

new Chart(document.getElementById('stockCompareChart'), {
    type: 'bar',
    data: {
        labels: productLabels,
        datasets: [
            {
                label: 'Stok Toko',
                data: stokToko
            },
            {
                label: 'Stok Warehouse',
                data: stokGudang
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                stacked: false
            },
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>




</main>
</body>
</html>
