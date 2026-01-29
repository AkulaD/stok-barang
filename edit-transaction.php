<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:index.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'finance'])) {
    header('location: product-out.php');
    exit;
}

include 'php/conn.php';

$totalItems = $conn->query("
    SELECT IFNULL(SUM(jumlah),0) AS total
    FROM transaksi
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
")->fetch_assoc();

$totalRevenue = $conn->query("
    SELECT IFNULL(SUM(jumlah * harga),0) AS total
    FROM transaksi
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
")->fetch_assoc();

$totalTransaction = $conn->query("
    SELECT COUNT(id_log) AS total
    FROM transaksi
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
")->fetch_assoc();

$totalNeedUpdate = $conn->query("
    SELECT COUNT(id_log) AS total
    FROM transaksi
    WHERE tipe = 'keluar'
    AND DATE(tanggal) = CURDATE()
    AND status = '0'
")->fetch_assoc();

$where = [];

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = (int) $_GET['status'];
    $where[] = "t.status = $status";
} else if (!isset($_GET['status'])) {
    $status = 0;
    $where[] = "t.status = 0";
}

if (!empty($_GET['start']) && !empty($_GET['end'])) {
    $start = $conn->real_escape_string($_GET['start']);
    $end = $conn->real_escape_string($_GET['end']);
    $where[] = "DATE(t.tanggal) BETWEEN '$start' AND '$end'";
}

if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where[] = "(p.nama_produk LIKE '%$search%' OR t.keterangan LIKE '%$search%')";
}

$whereSQL = $where ? 'AND ' . implode(' AND ', $where) : '';

$historyResult = $conn->query("
    SELECT t.id_log, t.tanggal, t.harga, t.status,
           p.nama_produk,
           t.keterangan AS lokasi
    FROM transaksi t
    JOIN produk p ON t.id_produk = p.id_produk
    WHERE t.tipe = 'keluar'
    $whereSQL
    ORDER BY t.tanggal DESC
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/edit-trx.css">
    <script src="data/js/penjualan.js" defer></script>
    <title>Edit Transaction - Sales</title>
</head>
<body>
<?php include 'partials/nav.php'; ?>

<main>
    <section class="sidebar">
        <ul>
            <li><a href="penjualan.php">Main</a></li>
            <li><a href="mutasi.php">Excel</a></li>
            <li><a href="edit-transaction.php">Edit</a></li>
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
                    <p>Need Update (TRX)</p>
                    <p><?= $totalNeedUpdate['total']; ?></p>
                </div>
            </div>
        </div>

        <div class="list-card">
            <h2>List Transactions</h2>
            <p>list of transactions whose prices have not been updated</p>
            <a href="activity-log.php">View History</a>

            <form method="get" class="filter-bar">
                <input type="date" name="start" value="<?= $_GET['start'] ?? '' ?>">
                <input type="date" name="end" value="<?= $_GET['end'] ?? '' ?>">

                <select name="status">
                    <option value="">All Status</option>
                    <option value="0" <?= (!isset($_GET['status']) || $_GET['status'] === '0') ? 'selected' : '' ?>>No Update</option>
                    <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Updated</option>
                </select>

                <input type="text" name="search" placeholder="Search product / location"
                    value="<?= $_GET['search'] ?? '' ?>">

                <button class="btn-submit" type="submit">Filter</button>
                <a href="edit-transaction.php" class="btn-reset">Reset</a>
            </form>

            <form action="php/update-status.php" method="post">
                <div class="table-sales">
                    <table>
                        <tr>
                            <th>No</th>
                            <th class="center">
                                <input type="checkbox" onclick="toggle(this)">
                            </th>
                            <th>Time</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Edit</th>
                        </tr>

                        <?php
                        $no = 1;
                        if ($historyResult->num_rows > 0) {
                            while ($row = $historyResult->fetch_assoc()) {
                                $statusText = $row['status'] == 1 ? 'Updated' : 'No Update';

                                echo "
                                <tr>
                                    <td>{$no}</td>
                                    <td class='center'>
                                        <input type='checkbox' name='id_log[]' value='{$row['id_log']}'>
                                    </td>
                                    <td>".date('H:i', strtotime($row['tanggal']))."</td>
                                    <td>{$row['nama_produk']}</td>
                                    <td>Rp ".number_format($row['harga'],0,',','.')."</td>
                                    <td>{$row['lokasi']}</td>
                                    <td>{$statusText}</td>
                                    <td>
                                        <a href='php/edit-sale.php?id={$row['id_log']}'>Edit</a>
                                    </td>
                                </tr>";

                                $no++;
                            }
                        } else {
                            echo "
                            <tr>
                                <td colspan='8' class='center-p'><p>No sales today</p></td>
                            </tr>";
                        }
                        ?>
                    </table>
                </div>
                <button class="btn-submit margin-1" type="submit" name="update_status">
                    Update Status Selected
                </button>
            </form>
        </div>

        <?php include "partials/info-penjualan-edit.php"; ?>
    </section>
</main>
<?php include 'partials/footer.php'; ?>
</body>