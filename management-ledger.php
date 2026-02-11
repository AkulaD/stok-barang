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

$q = mysqli_query($conn, "
    SELECT 
        d.kode_akun,
        d.nama_akun,
        d.debit,
        d.kredit,
        h.tanggal,
        h.deskripsi,
        c.tipe_akun
    FROM jurnal_detail d
    JOIN jurnal_header h ON d.id_jurnal = h.id_jurnal
    JOIN coa c ON d.kode_akun = c.kode_akun
    ORDER BY d.kode_akun ASC, h.tanggal ASC, d.id_jurnal ASC
");

$running_balance = 0;
$last_account = null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger | Stok Barang</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/mng-ldgr.css">
    <script src="data/js/script.js" defer></script>
</head>
<body>

    <?php include 'partials/nav.php'; ?>

    <main>
        <div class="list-card">
            <h2>General Ledger</h2>
            
            <div class="table-sales">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo Komulatif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($q && mysqli_num_rows($q) > 0):
                            while ($d = mysqli_fetch_assoc($q)): 
                                
                                if ($last_account !== $d['kode_akun']):
                                    $running_balance = 0; 
                                    $last_account = $d['kode_akun'];
                        ?>
                                    <tr style="background: #f3f4f6; font-weight: bold;">
                                        <td colspan="5">
                                            [<?= htmlspecialchars($d['kode_akun']) ?>] <?= htmlspecialchars($d['nama_akun']) ?> 
                                            <small style="color: #6b7280; font-weight: normal; margin-left: 5px;">
                                                (<?= htmlspecialchars($d['tipe_akun']) ?>)
                                            </small>
                                        </td>
                                    </tr>
                        <?php 
                                endif;

                                if (in_array($d['tipe_akun'], ['Asset', 'Expense', 'COGS'])) {
                                    $running_balance += ($d['debit'] - $d['kredit']);
                                } else {
                                    $running_balance += ($d['kredit'] - $d['debit']);
                                }

                                $color_balance = ($running_balance < 0) ? 'color: #dc2626;' : 'color: #374151;';
                        ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($d['deskripsi']) ?></td>
                                    <td>Rp <?= number_format($d['debit'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($d['kredit'], 0, ',', '.') ?></td>
                                    <td style="font-weight: bold; <?= $color_balance ?>">
                                        Rp <?= number_format($running_balance, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Tidak ada data transaksi ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <br>
        <?php include 'partials/mng-l.php' ?>
    </main>

    <?php include "partials/footer.php"; ?>

</body>
</html>