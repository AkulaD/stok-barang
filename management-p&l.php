<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:index.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('location: product-in.php');
    exit;
}

include 'php/conn.php';

$pendapatan_q = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total
    FROM jurnal_detail j
    JOIN coa c ON j.kode_akun = c.kode_akun
    WHERE c.tipe_akun = 'pendapatan'
");

$beban_q = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total
    FROM jurnal_detail j
    JOIN coa c ON j.kode_akun = c.kode_akun
    WHERE c.tipe_akun = 'beban'
");

$p = mysqli_fetch_assoc($pendapatan_q);
$b = mysqli_fetch_assoc($beban_q);

$total_pendapatan = $p['total'] ?? 0;
$total_beban = $b['total'] ?? 0;
$laba = $total_pendapatan - $total_beban;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&L | Stok Barang</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/mng-pnl.css">
    <script src="data/js/script.js" defer></script>
</head>
<body>

<?php include 'partials/nav.php'; ?>

<main>
    <div class="list-card">
        <h2>Laporan Laba Rugi</h2>
        <br>
        <div class="table-sales">
            <table>
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th>Nilai (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Pendapatan</td>
                        <td><?= number_format($total_pendapatan) ?></td>
                    </tr>
                    <tr>
                        <td>Total Beban</td>
                        <td><?= number_format($total_beban) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="row-total">
                        <th>Laba / Rugi Bersih</th>
                        <th><?= number_format($laba) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <br>
        <a href="php/mng-download.php" class="btn-submit">Download Excel Laporan</a>
        <br>
    </div>
    <?php include 'partials/mng-pnl.php' ?>
</main>

<?php include "partials/footer.php"; ?>
</body>
</html>
