<?php
session_start();

if(!isset($_SESSION['login'])){
    header('location:index.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('location: product-in.php');
    exit;
}

include 'php/conn.php';

$q = mysqli_query($conn,"
    SELECT kode_akun,nama_akun,debit,kredit,tanggal
    FROM jurnal_detail
    ORDER BY kode_akun,tanggal ASC
");

$saldo = [];
?>
<!DOCTYPE html>
<html lang="en">
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
    <div class="table-container">
        <h2>Ledger</h2>
        <br>
        <table>
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php while($d=mysqli_fetch_assoc($q)){ 
                    $kode = $d['kode_akun'];
                    if(!isset($saldo[$kode])){
                        $saldo[$kode] = 0;
                    }
                    $saldo[$kode] += $d['debit'];
                    $saldo[$kode] -= $d['kredit'];
                    
                    $class_balance = ($saldo[$kode] < 0) ? 'negative-balance' : '';
                ?>
                <tr>
                    <td><?= $d['kode_akun'] ?></td>
                    <td><?= $d['nama_akun'] ?></td>
                    <td><?= number_format($d['debit']) ?></td>
                    <td><?= number_format($d['kredit']) ?></td>
                    <td class="<?= $class_balance ?>"><?= number_format($saldo[$kode]) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <br>
    <?php include 'partials/mng-l.php' ?>
    <br>
</main>

<?php include "partials/footer.php"; ?>

</body>
</html>
