<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: login.php');
    exit;
}

if (!isset($_SESSION['role'])) {
    header('location: login.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'viewer', 'cashier'])) {
    header('location: product-in.php');
    exit;
}

include 'php/conn.php';
$result = mysqli_query($conn,"SELECT * FROM produk ORDER BY created_at DESC");
$no = 1;

$history_log = mysqli_query($conn,"
    SELECT log_stok.*, produk.nama_produk
    FROM log_stok
    JOIN produk ON log_stok.id_produk = produk.id_produk
    WHERE 
        log_stok.tipe = 'keluar'
        AND DATE(log_stok.tanggal) = CURDATE()
    ORDER BY log_stok.tanggal DESC
");

$whereDate = "";
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $_GET['from'] . " 00:00:00";
    $to   = $_GET['to'] . " 23:59:59";
    $whereDate = "AND log_stok.tanggal BETWEEN '$from' AND '$to'";
}


$chart_penjualan = mysqli_query($conn, "
    SELECT penjualan, SUM(jumlah) AS total
    FROM log_stok
    WHERE tipe = 'keluar' $whereDate
    GROUP BY penjualan
");

$chart_produk = mysqli_query($conn, "
    SELECT produk.nama_produk, SUM(log_stok.jumlah) AS total
    FROM log_stok
    JOIN produk ON log_stok.id_produk = produk.id_produk
    WHERE log_stok.tipe = 'keluar' $whereDate
    GROUP BY produk.nama_produk
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Out | Stok Barang</title>
    <link rel="stylesheet" href="data/css/home.css">
    <link rel="stylesheet" href="data/css/style.css">
    <script src="data/js/home.js" defer></script>
</head>
<body>
    <?php include 'partials/nav.php'; ?>

    <main>
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>

        <form class="form-out" action="php/out-form.php" method="post">
            <h2>Form Out</h2>
            <div class="qr-number">
                <div class="inp-body">
                    <label>Enter QR Code Number:</label>
                    <input type="text" name="qr_number" required>                    
                </div>
                <div class="inp-body">
                    <label for="penjualan">Shipment Location :</label>
                    <select name="penjualan" id="penjualan">
                        <option value="offline ">Offline</option>
                        <option value="shopee">Shopee</option>
                        <option value="tiktok">Tiktok</option>
                        <option value="tokopedia">Tokopedia</option>
                    </select>
                </div>                
                <br>
            </div>
            <div class="submit-body">
                <button class="btn-submit" type="submit">Submit</button>
            </div>
        </form>
        <div class="content-main">
            <div class="left-body">
                <section class="chart">
                    <h2>Distribusi Penjualan (Barang Keluar)</h2>
                    <section class="chart-filter">
                        <form method="GET">
                            <div class="inp-fil">
                                <label>Dari Tanggal</label>
                                <input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>">
                            </div>

                            <div class="inp-fil">
                                <label>Sampai</label>
                                <input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>">
                            </div>

                            <button class="btn-submit" type="submit">Filter</button>
                        </form>
                    </section>

                    <div class="chart-wrapper">
                        <div class="chart-container">
                            <canvas id="chartPenjualan" width="300" height="300"></canvas>

                            <div class="legend-box">
                                <h4>List Shipment</h4>
                                <div id="legendPenjualan" class="legend-list"></div>
                            </div>
                        </div>

                        <div class="chart-container">
                            <canvas id="chartProduk" width="300" height="300"></canvas>

                            <div class="legend-box">
                                <h4>Produk Keluar</h4>
                                <div id="legendProduk" class="legend-list"></div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- JS -->
                <script>
                    const penjualanData = [
                    <?php
                    $p = [];
                    while ($row = mysqli_fetch_assoc($chart_penjualan)) {
                        $label = $row['penjualan'] ?: 'Unknown';
                        $p[] = "{ label: '$label', value: {$row['total']} }";
                    }
                    echo implode(",", $p);
                    ?>
                    ];

                    const produkData = [
                    <?php
                    $pr = [];
                    while ($row = mysqli_fetch_assoc($chart_produk)) {
                        $pr[] = "{ label: '{$row['nama_produk']}', value: {$row['total']} }";
                    }
                    echo implode(",", $pr);
                    ?>
                    ];
                </script>

                <section class="history-body">
                    <h2>History Stock</h2>
                    <a href="all-history.php">Show All</a>
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
                                        <td colspan="7">No history found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="main-body">
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

        </div>

        <?php include "partials/info-product-out.php"; ?>
    </main>
    
    <?php include "partials/footer.php" ?>

    <script>
        function generateColors(count) {
            return Array.from({ length: count }, (_, i) =>
                `hsl(${(360 / count) * i}, 70%, 60%)`
            );
        }

        function drawPie(canvasId, legendId, data) {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext("2d");
            const legend = document.getElementById(legendId);

            const colors = generateColors(data.length);
            const total = data.reduce((s, d) => s + d.value, 0);

            let angle = 0;
            data.forEach((d, i) => {
                const slice = (d.value / total) * Math.PI * 2;
                ctx.beginPath();
                ctx.moveTo(150, 150);
                ctx.arc(150, 150, 120, angle, angle + slice);
                ctx.fillStyle = colors[i];
                ctx.fill();
                angle += slice;
            });

            legend.innerHTML = "";
            data.forEach((d, i) => {
                const item = document.createElement("div");
                item.className = "legend-item";

                const color = document.createElement("div");
                color.className = "legend-color";
                color.style.background = colors[i];

                const text = document.createElement("span");
                text.textContent = `${d.label} (${d.value})`;

                item.appendChild(color);
                item.appendChild(text);
                legend.appendChild(item);
            });
        }

        drawPie("chartPenjualan", "legendPenjualan", penjualanData);
        drawPie("chartProduk", "legendProduk", produkData);
    </script>


</body>
</html>