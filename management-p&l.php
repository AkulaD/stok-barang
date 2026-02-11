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

$rev_q = mysqli_query($conn, "
    SELECT SUM(j.kredit - j.debit) as total
    FROM jurnal_detail j
    JOIN coa c ON j.kode_akun = c.kode_akun
    WHERE c.tipe_akun = 'Revenue'
");
$r = mysqli_fetch_assoc($rev_q);
$revenue = $r['total'] ?? 0;

$cogs_q = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total
    FROM jurnal_detail j
    JOIN coa c ON j.kode_akun = c.kode_akun
    WHERE c.tipe_akun = 'COGS'
");
$c = mysqli_fetch_assoc($cogs_q);
$cogs = $c['total'] ?? 0;

$exp_q = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total
    FROM jurnal_detail j
    JOIN coa c ON j.kode_akun = c.kode_akun
    WHERE c.tipe_akun = 'Expense'
");
$e = mysqli_fetch_assoc($exp_q);
$expense = $e['total'] ?? 0;

$gross_profit = $revenue - $cogs;
$net_profit   = $gross_profit - $expense;
?>
<!DOCTYPE html>
<html lang="id">
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
        <div class="main">
            <div class="list-card">
                <h2>Laporan Laba Rugi (P&L)</h2>
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
                                <td>Revenue (Pendapatan)</td>
                                <td>Rp <?= number_format($revenue, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>COGS (HPP)</td>
                                <td>Rp <?= number_format($cogs, 0, ',', '.') ?></td>
                            </tr>
                            <tr style="font-weight:bold; background:#f2f2f2;">
                                <td>Gross Profit (Laba Kotor)</td>
                                <td>Rp <?= number_format($gross_profit, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Operating Expense (Beban Operasional)</td>
                                <td>Rp <?= number_format($expense, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="row-total" style="font-weight:bold; background:#e7ffe7;">
                                <th>Net Profit (Laba Bersih)</th>
                                <th>Rp <?= number_format($net_profit, 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <br>
                <a href="php/mng-download.php" class="btn-submit">Download Excel Laporan</a>
                <br>
            </div>
            <?php include 'partials/mng-pnl.php' ?>
        </div>
    </main>

    <?php include "partials/footer.php"; ?>

</body>
</html>