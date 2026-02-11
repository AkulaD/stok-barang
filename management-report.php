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

$allowedTipe = ['Asset', 'Liability', 'Equity', 'Revenue', 'COGS', 'Expense'];

if (isset($_POST['simpan'])) {

    $kode_d = preg_replace('/\D/', '', $_POST['kode_d'] ?? '');
    $kode_t = preg_replace('/\D/', '', $_POST['kode_t'] ?? '');
    $kode_a = preg_replace('/\D/', '', $_POST['kode_a'] ?? '');

    $tipe  = $_POST['tipe'] ?? '';
    $nama  = trim($_POST['nama'] ?? '');
    $saldo = (int)($_POST['saldo'] ?? 0);

    if ($kode_d == '' || $kode_t == '' || $kode_a == '' || $nama == '' || !in_array($tipe, $allowedTipe)) {
        $info = "Form belum lengkap";
    } else {

        if (in_array($tipe, ['Revenue', 'Expense', 'COGS'])) {
            $saldo = 0;
        }

        $kode = str_pad($kode_d, 2, '0', STR_PAD_LEFT) . '.'
              . str_pad($kode_t, 3, '0', STR_PAD_LEFT) . '.'
              . str_pad($kode_a, 3, '0', STR_PAD_LEFT);

        $cek = $conn->prepare("SELECT id_coa FROM coa WHERE kode_akun=?");
        $cek->bind_param("s", $kode);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $info = "Kode sudah ada";
        } else {
            $id = 'COA-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));

            $stmt = $conn->prepare("
                INSERT INTO coa 
                (id_coa, kode_akun, nama_akun, tipe_akun, saldo_awal, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");

            $stmt->bind_param("ssssi", $id, $kode, $nama, $tipe, $saldo);
            $stmt->execute();

            $info = "Berhasil disimpan";
        }
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama_edit'] ?? '');

    if ($id != '' && $nama != '') {
        $stmt = $conn->prepare("UPDATE coa SET nama_akun=? WHERE id_coa=?");
        $stmt->bind_param("ss", $nama, $id);
        $stmt->execute();
        $info = "Berhasil diupdate";
    }
}

if (isset($_GET['nonaktif'])) {
    $stmt = $conn->prepare("UPDATE coa SET status=0 WHERE id_coa=?");
    $stmt->bind_param("s", $_GET['nonaktif']);
    $stmt->execute();
}

if (isset($_GET['aktif'])) {
    $stmt = $conn->prepare("UPDATE coa SET status=1 WHERE id_coa=?");
    $stmt->bind_param("s", $_GET['aktif']);
    $stmt->execute();
}

$search = trim($_GET['cari'] ?? '');
$filter = trim($_GET['tipe'] ?? '');
$hide_nonaktif = $_GET['hide_nonaktif'] ?? '';

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($search != '') {
    $where .= " AND (kode_akun LIKE ? OR nama_akun LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if ($filter != '' && in_array($filter, $allowedTipe)) {
    $where .= " AND tipe_akun=?";
    $params[] = $filter;
    $types .= "s";
}

if ($hide_nonaktif == '1') {
    $where .= " AND status=1";
}

$sql = "SELECT * FROM coa $where ORDER BY kode_akun ASC";
$stmt = $conn->prepare($sql);

if ($types != "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$data = $stmt->get_result();
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
                    <p><?= htmlspecialchars($info) ?></p>
                <?php endif; ?>

                <form method="post">
                    <input type="text" name="kode_d" id="kd" maxlength="2" pattern="\d{1,2}" required placeholder="XX">
                    <input type="text" name="kode_t" id="kt" maxlength="3" pattern="\d{1,3}" required placeholder="XXX">
                    <input type="text" name="kode_a" id="ka" maxlength="3" pattern="\d{1,3}" required placeholder="XXX">
                    <input type="text" name="nama" required placeholder="Nama Akun">

                    <select name="tipe" id="tipe_select" required>
                        <option value="">Tipe</option>
                        <?php foreach ($allowedTipe as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="number" name="saldo" id="saldo_input" min="0" step="1" placeholder="Saldo Awal">

                    <button class="btn-submit" name="simpan">Simpan</button>
                </form>
            </div>

            <br>

            <div class="list-card">
                <h2>Cari COA</h2>
                <form method="get" class="filter-bar">
                    <input type="text" name="cari" value="<?= htmlspecialchars($search) ?>" placeholder="Cari kode/nama...">

                    <select name="tipe">
                        <option value="">Semua Tipe</option>
                        <?php foreach ($allowedTipe as $t): ?>
                            <option value="<?= $t ?>" <?= $filter == $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
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
                            <?php while ($d = $data->fetch_assoc()): $status = $d['status'] ?? 1; ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['kode_akun']) ?></td>
                                    <td>
                                        <form method="post" class="form-table">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($d['id_coa']) ?>">
                                            <input type="text" name="nama_edit" value="<?= htmlspecialchars($d['nama_akun']) ?>" required>
                                            <button class="btn-submit" name="update">Edit</button>
                                        </form>
                                    </td>
                                    <td><?= htmlspecialchars($d['tipe_akun']) ?></td>
                                    <td>Rp <?= number_format($d['saldo_awal'], 0, ',', '.') ?></td>
                                    <td><?= $status ? 'Aktif' : 'Nonaktif' ?></td>
                                    <td>
                                        <?php if ($status): ?>
                                            <a href="?nonaktif=<?= urlencode($d['id_coa']) ?>" onclick="return confirm('Nonaktifkan akun ini?')">Nonaktif</a>
                                        <?php else: ?>
                                            <a href="?aktif=<?= urlencode($d['id_coa']) ?>">Aktif</a>
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
        const kd = document.getElementById('kd');
        const kt = document.getElementById('kt');
        const ka = document.getElementById('ka');
        const tipeSelect = document.getElementById('tipe_select');
        const saldoInput = document.getElementById('saldo_input');

        function pad(el, len) {
            if (el.value !== "") {
                el.value = el.value.replace(/\D/g, '').padStart(len, '0');
            }
        }

        kd.onblur = () => pad(kd, 2);
        kt.onblur = () => pad(kt, 3);
        ka.onblur = () => pad(ka, 3);

        tipeSelect.addEventListener('change', e => {
            if (['Revenue', 'Expense', 'COGS'].includes(e.target.value)) {
                saldoInput.value = 0;
                saldoInput.readOnly = true;
                saldoInput.style.backgroundColor = "#f0f0f0";
            } else {
                saldoInput.readOnly = false;
                saldoInput.style.backgroundColor = "white";
            }
        });
    </script>
</body>
</html>