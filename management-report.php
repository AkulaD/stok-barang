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

    $kode_d = preg_replace('/\D/', '', $_POST['kode_d'] ?? '');
    $kode_t = preg_replace('/\D/', '', $_POST['kode_t'] ?? '');
    $kode_a = preg_replace('/\D/', '', $_POST['kode_a'] ?? '');

    $tipe  = $_POST['tipe'] ?? '';
    $nama  = trim($_POST['nama'] ?? '');
    $saldo = (int)($_POST['saldo'] ?? 0);

    if ($kode_d == '' || $kode_t == '' || $kode_a == '' || $tipe == '' || $nama == '') {
        $info = "Form belum lengkap";
    } else {

        if (in_array($tipe, ['pendapatan', 'beban'])) {
            $saldo = 0;
        }

        $kd = str_pad($kode_d, 2, '0', STR_PAD_LEFT);
        $kt = str_pad($kode_t, 3, '0', STR_PAD_LEFT);
        $ka = str_pad($kode_a, 3, '0', STR_PAD_LEFT);

        $kode = "$kd.$kt.$ka";

        $nama = mysqli_real_escape_string($conn, $nama);
        $tipe = mysqli_real_escape_string($conn, $tipe);

        $cek = mysqli_query($conn, "SELECT id_coa FROM coa WHERE kode_akun='$kode'");

        if (mysqli_num_rows($cek) > 0) {
            $info = "Kode sudah ada";
        } else {

            $id = 'COA-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));

            mysqli_query($conn, "
                INSERT INTO coa 
                (id_coa, kode_akun, nama_akun, tipe_akun, saldo_awal, status, created_at) 
                VALUES 
                ('$id', '$kode', '$nama', '$tipe', '$saldo', 1, NOW())
            ");

            $info = "Berhasil disimpan";
        }
    }
}

if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_edit']));
    mysqli_query($conn, "UPDATE coa SET nama_akun='$nama' WHERE id_coa='$id'");
    $info = "Berhasil diupdate";
}

if (isset($_GET['nonaktif'])) {
    $id = mysqli_real_escape_string($conn, $_GET['nonaktif']);
    mysqli_query($conn, "UPDATE coa SET status=0 WHERE id_coa='$id'");
}

if (isset($_GET['aktif'])) {
    $id = mysqli_real_escape_string($conn, $_GET['aktif']);
    mysqli_query($conn, "UPDATE coa SET status=1 WHERE id_coa='$id'");
}

$search = trim($_GET['cari'] ?? '');
$filter = trim($_GET['tipe'] ?? '');
$hide_nonaktif = $_GET['hide_nonaktif'] ?? '';

$search = mysqli_real_escape_string($conn, $search);
$filter = mysqli_real_escape_string($conn, $filter);

$where = "WHERE 1=1";

if ($search != '') {
    $where .= " AND (kode_akun LIKE '%$search%' OR nama_akun LIKE '%$search%')";
}

if ($filter != '') {
    $where .= " AND tipe_akun='$filter'";
}

if ($hide_nonaktif == '1') {
    $where .= " AND status=1";
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

                <?php if ($info != ''): ?>
                    <p><?= $info ?></p>
                <?php endif; ?>

                <form method="post">
                    <input type="text" name="kode_d" id="kd" maxlength="2" pattern="\d{1,2}" required placeholder="XX">
                    <input type="text" name="kode_t" id="kt" maxlength="3" pattern="\d{1,3}" required placeholder="XXX">
                    <input type="text" name="kode_a" id="ka" maxlength="3" pattern="\d{1,3}" required placeholder="XXX">
                    <input type="text" name="nama" required placeholder="Nama Akun">

                    <select name="tipe" required>
                        <option value="">Tipe</option>
                        <option value="aset">Aset</option>
                        <option value="kewajiban">Kewajiban</option>
                        <option value="ekuitas">Ekuitas</option>
                        <option value="pendapatan">Pendapatan</option>
                        <option value="beban">Beban</option>
                    </select>

                    <input type="number" name="saldo" min="0" step="1" required placeholder="Saldo Awal">

                    <button class="btn-submit" name="simpan">Simpan</button>
                </form>
            </div>

            <br>

            <div class="list-card">
                <h2>Cari COA</h2>

                <form method="get" class="filter-bar">
                    <input type="text" name="cari" value="<?= htmlspecialchars($search) ?>">

                    <select name="tipe">
                        <option value="">Semua</option>
                        <option value="aset" <?= $filter == 'aset' ? 'selected' : '' ?>>Aset</option>
                        <option value="kewajiban" <?= $filter == 'kewajiban' ? 'selected' : '' ?>>Kewajiban</option>
                        <option value="ekuitas" <?= $filter == 'ekuitas' ? 'selected' : '' ?>>Ekuitas</option>
                        <option value="pendapatan" <?= $filter == 'pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                        <option value="beban" <?= $filter == 'beban' ? 'selected' : '' ?>>Beban</option>
                    </select>

                    <label>
                        <input type="checkbox" name="hide_nonaktif" value="1" <?= $hide_nonaktif == '1' ? 'checked' : '' ?>>
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
                            <?php while ($d = mysqli_fetch_assoc($data)):
                                $status = $d['status'] ?? 1;
                            ?>
                                <tr>
                                    <td><?= $d['kode_akun'] ?></td>

                                    <td>
                                        <form method="post" class="form-table">
                                            <input type="hidden" name="id" value="<?= $d['id_coa'] ?>">
                                            <input type="text" name="nama_edit" value="<?= $d['nama_akun'] ?>">
                                            <button class="btn-submit" name="update">Edit</button>
                                        </form>
                                    </td>

                                    <td><?= ucfirst($d['tipe_akun']) ?></td>
                                    <td>Rp <?= number_format($d['saldo_awal'], 0, ',', '.') ?></td>
                                    <td><?= $status ? 'Aktif' : 'Nonaktif' ?></td>

                                    <td>
                                        <?php if ($status): ?>
                                            <a href="?nonaktif=<?= $d['id_coa'] ?>">Nonaktif</a>
                                        <?php else: ?>
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

    <script>
        function pad(el, len) {
            el.value = el.value.replace(/\D/g, '').padStart(len, '0')
        }
        document.getElementById('kd').addEventListener('blur', function() {
            pad(this, 2)
        })
        document.getElementById('kt').addEventListener('blur', function() {
            pad(this, 3)
        })
        document.getElementById('ka').addEventListener('blur', function() {
            pad(this, 3)
        })
    </script>

</body>
</html>