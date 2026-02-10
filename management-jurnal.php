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

$akun = mysqli_query($conn, "SELECT kode_akun, nama_akun FROM coa ORDER BY kode_akun");
$info = '';

if(isset($_POST['simpan'])){
    $id_jurnal = 'JUR-' . date('YmdHis') . '-' . bin2hex(random_bytes(2));
    $tanggal = $_POST['tanggal'];
    $deskripsi = $_POST['deskripsi'];
    $debit = $_POST['akun_debit'];
    $kredit = $_POST['akun_kredit'];
    $nominal = $_POST['nominal'];

    if($debit == $kredit){
        $info = "Akun debit dan kredit tidak boleh sama";
    } else {
        mysqli_query($conn, "INSERT INTO jurnal_header VALUES(
            '$id_jurnal',
            '$tanggal',
            '$deskripsi',
            '".$_SESSION['username']."'
        )");

        $d = explode('|', $debit);
        $kd = $d[0];
        $nd = $d[1];

        $k = explode('|', $kredit);
        $kk = $k[0];
        $nk = $k[1];

        $id1 = 'DTL-' . bin2hex(random_bytes(4));
        $id2 = 'DTL-' . bin2hex(random_bytes(4));

        mysqli_query($conn, "INSERT INTO jurnal_detail VALUES(
            '$id1',
            '$id_jurnal',
            '$kd',
            '$nd',
            '$nominal',
            0,
            NOW()
        )");

        mysqli_query($conn, "INSERT INTO jurnal_detail VALUES(
            '$id2',
            '$id_jurnal',
            '$kk',
            '$nk',
            0,
            '$nominal',
            NOW()
        )");

        $info = "Jurnal berhasil disimpan";
    }
}

$history = mysqli_query($conn,"
    SELECT 
        h.id_jurnal,
        h.tanggal,
        h.deskripsi,
        h.user_input,
        d.kode_akun,
        d.nama_akun,
        d.debit,
        d.kredit
    FROM jurnal_header h
    JOIN jurnal_detail d ON h.id_jurnal = d.id_jurnal
    ORDER BY h.tanggal DESC, h.id_jurnal DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal | Stok Barang</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/mng-j.css">
    <script src="data/js/script.js" defer></script>
</head>
<body>

<?php include 'partials/nav.php'; ?>

<main>
    <div class="list-card">
        <h2>Input Jurnal Umum</h2>

        <?php if($info != ''): ?>
            <p><?= $info ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="date" name="tanggal" required>
            <input type="text" name="deskripsi" placeholder="Deskripsi" required>

            <select name="akun_debit" required>
                <option value="">Pilih Akun Debit</option>
                <?php mysqli_data_seek($akun, 0); ?>
                <?php while($a = mysqli_fetch_assoc($akun)): ?>
                    <option value="<?= $a['kode_akun'] . '|' . $a['nama_akun'] ?>">
                        <?= $a['kode_akun'] . ' - ' . $a['nama_akun'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="akun_kredit" required>
                <option value="">Pilih Akun Kredit</option>
                <?php mysqli_data_seek($akun, 0); ?>
                <?php while($a = mysqli_fetch_assoc($akun)): ?>
                    <option value="<?= $a['kode_akun'] . '|' . $a['nama_akun'] ?>">
                        <?= $a['kode_akun'] . ' - ' . $a['nama_akun'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="nominal" placeholder="Nominal" required>

            <button class="btn-submit" name="simpan" type="submit">Simpan</button>
        </form>

    </div>

    <div class="list-card">
        <h2>History Jurnal</h2>
        <div class="table-sales">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>ID Jurnal</th>
                        <th>Deskripsi</th>
                        <th>User</th>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($h = mysqli_fetch_assoc($history)): ?>
                    <tr>
                        <td><?= $h['tanggal'] ?></td>
                        <td><?= $h['id_jurnal'] ?></td>
                        <td><?= $h['deskripsi'] ?></td>
                        <td><?= $h['user_input'] ?></td>
                        <td><?= $h['kode_akun'] ?></td>
                        <td><?= $h['nama_akun'] ?></td>
                        <td><?= number_format($h['debit']) ?></td>
                        <td><?= number_format($h['kredit']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'partials/mng-j.php' ?>
</main>

<?php include "partials/footer.php"; ?>

</body>
</html>