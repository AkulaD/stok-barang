<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'finance') {
    header('location: product-out.php');
    exit;
}

include 'php/conn.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$data = [];
$totalItem = 0;
$totalRevenue = 0;

if ($from && $to) {
    $query = $conn->query("
        SELECT 
            l.tanggal,
            p.nama_produk,
            l.jumlah,
            l.harga,
            (l.jumlah * l.harga) AS revenue,
            l.penjualan,
            l.keterangan
        FROM log_stok l
        JOIN produk p ON l.id_produk = p.id_produk
        WHERE l.tipe = 'keluar'
        AND DATE(l.tanggal) BETWEEN '$from' AND '$to'
        ORDER BY l.tanggal DESC
    ");

    while ($row = $query->fetch_assoc()) {
        $data[] = $row;
        $totalItem += $row['jumlah'];
        $totalRevenue += $row['revenue'];
    }
}

$chartLabels = [];
$chartValues = [];

if ($from && $to) {
    $chartQuery = $conn->query("
        SELECT 
            DATE(tanggal) AS tgl,
            SUM(jumlah * harga) AS total
        FROM log_stok
        WHERE tipe = 'keluar'
        AND DATE(tanggal) BETWEEN '$from' AND '$to'
        GROUP BY DATE(tanggal)
        ORDER BY DATE(tanggal) ASC
    ");

    while ($row = $chartQuery->fetch_assoc()) {
        $chartLabels[] = date('d M', strtotime($row['tgl']));
        $chartValues[] = (float)$row['total'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/mutasi.css">
    <title>Mutation - Stock | Stok Barang</title>
</head>
<body>

<?php include 'Partials/nav.php' ?>

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
            <h2>Filter Tanggal</h2>

            <form method="GET" class="filter-form">
                <input type="date" name="from" value="<?= $from ?>" required>
                <input type="date" name="to" value="<?= $to ?>" required>
                <button type="submit">Tampilkan</button>
            </form>
        </div>

        <?php if ($from && $to): ?>

        <div class="list-card">
            <h2>Ringkasan</h2>

            <div class="list-wrap">
                <div class="list-body">
                    <p>Total Item Terjual</p>
                    <p><?= $totalItem ?></p>
                </div>

                <div class="list-body">
                    <p>Total Penghasilan</p>
                    <p>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <div class="list-card">
            <h2>Revenue Trend</h2>

            <div class="chart-scroll">
                <div class="chart-canvas">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>


        <div class="list-card">
            <h2>Transaksi Penjualan</h2>

            <div class="download-wrap">
                <a href="php/export_excel.php?from=<?= $from ?>&to=<?= $to ?>" class="btn">Download Excel</a>
                <a href="php/export_pdf.php?from=<?= $from ?>&to=<?= $to ?>" class="btn">Download PDF</a>
            </div>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Revenue</th>
                        <th>Lokasi</th>
                        <th>Keterangan</th>
                    </tr>

                    <?php if (count($data) > 0): ?>
                        <?php foreach ($data as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['nama_produk'] ?></td>
                                <td><?= $row['jumlah'] ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['revenue'], 0, ',', '.') ?></td>
                                <td><?= $row['penjualan'] ?></td>
                                <td><?= $row['keterangan'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Tidak ada data</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            
        </div>

        <?php endif; ?>
        <?php include "partials/info-penjualan-mt.php"; ?>
    </section>
</main>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
const chartEl = document.getElementById('revenueChart')

if (chartEl) {
    const labels = <?= json_encode($chartLabels ?? []) ?>;
    const values = <?= json_encode($chartValues ?? []) ?>;

    new Chart(chartEl, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: values,
                borderColor: '#0055ff',
                backgroundColor: 'rgba(0,85,255,0.15)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })
}
</script>



</body>
</html>
