<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:index.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('location:product-in.php');
    exit;
}

include 'php/conn.php';

$info = '';

if (isset($_POST['simpan'])) {
    $kode_d = $_POST['kode_d'] ?? '';
    $kode_t = $_POST['kode_t'] ?? '';
    $kode_a = $_POST['kode_a'] ?? '';
    $tipe   = $_POST['tipe'] ?? '';
    $nama   = $_POST['nama'] ?? '';
    $saldo  = $_POST['saldo'] ?? 0;

    if ($kode_d == '' || $kode_t == '' || $kode_a == '' || $tipe == '' || $nama == '') {
        $info = "Form belum lengkap";
    } else {
        $id = 'COA-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));

        $kd = str_pad($kode_d, 1, '0', STR_PAD_LEFT);
        $kt = str_pad($kode_t, 2, '0', STR_PAD_LEFT);
        $ka = str_pad($kode_a, 2, '0', STR_PAD_LEFT);

        $kode = $kd . '.' . $kt . '.' . $ka;

        $nama = mysqli_real_escape_string($conn, $nama);
        $saldo = max(0, (int)$saldo);

        $cek = mysqli_query($conn, "SELECT id_coa FROM coa WHERE kode_akun = '$kode'");

        if (mysqli_num_rows($cek) > 0) {
            $info = "Kode sudah ada";
        } else {
            mysqli_query($conn, "INSERT INTO coa 
                (id_coa, kode_akun, nama_akun, tipe_akun, saldo_awal, status, created_at)
                VALUES
                ('$id', '$kode', '$nama', '$tipe', '$saldo', 1, NOW())");

            $info = "Berhasil disimpan";
        }
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_edit']);
    mysqli_query($conn, "UPDATE coa SET nama_akun = '$nama' WHERE id_coa = '$id'");
    $info = "Berhasil diupdate";
}

if (isset($_GET['nonaktif'])) {
    $id = $_GET['nonaktif'];
    mysqli_query($conn, "UPDATE coa SET status = 0 WHERE id_coa = '$id'");
}

if (isset($_GET['aktif'])) {
    $id = $_GET['aktif'];
    mysqli_query($conn, "UPDATE coa SET status = 1 WHERE id_coa = '$id'");
}

$search = $_GET['cari'] ?? '';
$filter = $_GET['tipe'] ?? '';

$hide_nonaktif = $_GET['hide_nonaktif'] ?? '';

$where = "WHERE 1=1";

if ($search != '') {
    $where .= " AND (kode_akun LIKE '%$search%' OR nama_akun LIKE '%$search%')";
}

if ($filter != '') {
    $where .= " AND tipe_akun = '$filter'";
}

if ($hide_nonaktif == '1') {
    $where .= " AND status = 1";
}


$data = mysqli_query($conn, "SELECT * FROM coa $where ORDER BY kode_akun ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COA | Stok Barang</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/mng-r.css">
    <script src="data/js/script.js" defer></script>
</head>
<body>

    <?php include 'partials/nav.php'; ?>

<main>

<div class="main">

    <div class="list-card">
        <h2>Input COA</h2>

        <?php if ($info != '') : ?>
            <p><?= $info ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="kode_d" placeholder="X" maxlength="1" pattern="\d{1}" inputmode="numeric" required>
            <input type="text" name="kode_t" placeholder="XX" maxlength="2" pattern="\d{2}" inputmode="numeric" required>
            <input type="text" name="kode_a" placeholder="XX" maxlength="2" pattern="\d{2}" inputmode="numeric" required>

            <input type="text" name="nama" placeholder="Nama akun" required>

            <select name="tipe" required>
                <option value="">Tipe</option>
                <option value="aset">Aset</option>
                <option value="kewajiban">Kewajiban</option>
                <option value="ekuitas">Ekuitas</option>
                <option value="pendapatan">Pendapatan</option>
                <option value="beban">Beban</option>
            </select>

            <input type="number" name="saldo" placeholder="Saldo awal" required>

            <button class="btn-submit" name="simpan">Simpan</button>
        </form>
    </div>

    <br>

    <div class="list-card">
        <h2>Cari COA</h2>

        <form method="get" class="filter-bar">
            <input type="text" name="cari" placeholder="Kode / Nama" value="<?= htmlspecialchars($search) ?>">

            <select name="tipe">
                <option value="">Semua</option>
                <option value="aset" <?= $filter == 'aset' ? 'selected' : '' ?>>Aset</option>
                <option value="kewajiban" <?= $filter == 'kewajiban' ? 'selected' : '' ?>>Kewajiban</option>
                <option value="ekuitas" <?= $filter == 'ekuitas' ? 'selected' : '' ?>>Ekuitas</option>
                <option value="pendapatan" <?= $filter == 'pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                <option value="beban" <?= $filter == 'beban' ? 'selected' : '' ?>>Beban</option>
            </select>

            <label>
                <input type="checkbox" name="hide_nonaktif" value="1" <?= isset($_GET['hide_nonaktif']) ? 'checked' : '' ?>>
                Sembunyikan nonaktif
            </label>

            <button class="btn-submit">Cari</button>
        </form>

    </div>

    <br>
    <div class="list-card">
        <h2>Data COA</h2>
        <br>
        <div class="table-sales">
            <table>
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Saldo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>

                <tbody>
                <?php while ($d = mysqli_fetch_assoc($data)) : 
                $status = $d['status'] ?? 1;
                ?>
                <tr>
                    <td><?= $d['kode_akun'] ?></td>
                    <td style="width: 250px;">
                        <form method="post" class="form-table">
                            <input type="hidden" name="id" value="<?= $d['id_coa'] ?>">
                            <input type="text" name="nama_edit" value="<?= $d['nama_akun'] ?>">
                            <button class="btn-submit" name="update">Edit</button>
                        </form>
                    </td>
                    <td><?= ucfirst($d['tipe_akun']) ?></td>
                    <td><?= number_format($d['saldo_awal']) ?></td>
                    <td><?= $status ? 'Aktif' : 'Nonaktif' ?></td>
                    <td>
                        <?php if ($status) : ?>
                            <a href="?nonaktif=<?= $d['id_coa'] ?>">Nonaktif</a>
                        <?php else : ?>
                            <a href="?aktif=<?= $d['id_coa'] ?>">Aktif</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include "partials/mng-r.php" ?>
</div>

</main>

    <?php include "partials/footer.php" ?>

</body>
</html>