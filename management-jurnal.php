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

$akun = mysqli_query($conn, "SELECT kode_akun, nama_akun FROM coa WHERE status=1 ORDER BY kode_akun");
$info = '';

if (isset($_POST['simpan'])) {

    $id_jurnal = 'JUR-' . date('YmdHis') . '-' . bin2hex(random_bytes(2));
    $tanggal = $_POST['tanggal'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $debit = $_POST['akun_debit'] ?? '';
    $kredit = $_POST['akun_kredit'] ?? '';
    $nominal = (int)($_POST['nominal'] ?? 0);

    if (!$tanggal || !$deskripsi || !$debit || !$kredit || $nominal <= 0) {
        $info = "Data tidak valid";
    } elseif ($debit == $kredit) {
        $info = "Akun debit dan kredit tidak boleh sama";
    } else {

        $d = explode('|', $debit);
        $kd = $d[0];
        $nd = $d[1];

        $k = explode('|', $kredit);
        $kk = $k[0];
        $nk = $k[1];

        mysqli_begin_transaction($conn);

        try {

            $stmt = $conn->prepare("INSERT INTO jurnal_header (id_jurnal, tanggal, deskripsi, user_input) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $id_jurnal, $tanggal, $deskripsi, $_SESSION['username']);
            $stmt->execute();

            $id1 = 'DTL-' . bin2hex(random_bytes(4));
            $id2 = 'DTL-' . bin2hex(random_bytes(4));
            $nol = 0;

            $stmt = $conn->prepare("INSERT INTO jurnal_detail (id_detail, id_jurnal, kode_akun, nama_akun, debit, kredit, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssiis", $id1, $id_jurnal, $kd, $nd, $nominal, $nol, $tanggal);
            $stmt->execute();

            $stmt->bind_param("ssssiis", $id2, $id_jurnal, $kk, $nk, $nol, $nominal, $tanggal);
            $stmt->execute();

            mysqli_commit($conn);
            $info = "Jurnal berhasil disimpan";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $info = "Gagal simpan jurnal: " . $e->getMessage();
        }
    }
}

$history = mysqli_query($conn, "
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
    ORDER BY h.tanggal DESC, h.id_jurnal DESC, d.debit DESC
");
?>

<!DOCTYPE html>
<html lang="id">
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
        <div class="main">
            <div class="list-card">
                <h2>Input Jurnal Umum</h2>

                <?php if ($info != ''): ?>
                    <p class="alert"><?= htmlspecialchars($info) ?></p>
                <?php endif; ?>

                <form method="post">
                    <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>">
                    <input type="text" name="deskripsi" placeholder="Deskripsi Transaksi" required>

                    <select name="akun_debit" required>
                        <option value="">-- Pilih Akun Debit --</option>
                        <?php mysqli_data_seek($akun, 0); ?>
                        <?php while ($a = mysqli_fetch_assoc($akun)): ?>
                            <option value="<?= $a['kode_akun'] . '|' . $a['nama_akun'] ?>">
                                <?= $a['kode_akun'] . ' - ' . $a['nama_akun'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="akun_kredit" required>
                        <option value="">-- Pilih Akun Kredit --</option>
                        <?php mysqli_data_seek($akun, 0); ?>
                        <?php while ($a = mysqli_fetch_assoc($akun)): ?>
                            <option value="<?= $a['kode_akun'] . '|' . $a['nama_akun'] ?>">
                                <?= $a['kode_akun'] . ' - ' . $a['nama_akun'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input type="number" name="nominal" placeholder="Nominal (Rp)" min="1" required>

                    <button class="btn-submit" name="simpan" type="submit">Simpan Jurnal</button>
                </form>
            </div>

            <br>

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
                            <?php 
                            $last_id = '';
                            while ($h = mysqli_fetch_assoc($history)): 
                            ?>
                                <tr style="<?= ($last_id != $h['id_jurnal'] && $last_id != '') ? 'border-top: 2px solid #ccc;' : '' ?>">
                                    <td><?= ($last_id != $h['id_jurnal']) ? $h['tanggal'] : '' ?></td>
                                    <td><?= ($last_id != $h['id_jurnal']) ? $h['id_jurnal'] : '' ?></td>
                                    <td><?= ($last_id != $h['id_jurnal']) ? htmlspecialchars($h['deskripsi']) : '' ?></td>
                                    <td><?= ($last_id != $h['id_jurnal']) ? htmlspecialchars($h['user_input']) : '' ?></td>
                                    <td><?= $h['kode_akun'] ?></td>
                                    <td><?= htmlspecialchars($h['nama_akun']) ?></td>
                                    <td><?= number_format($h['debit'], 0, ',', '.') ?></td>
                                    <td><?= number_format($h['kredit'], 0, ',', '.') ?></td>
                                </tr>
                            <?php 
                                $last_id = $h['id_jurnal'];
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include 'partials/mng-j.php' ?>
        </div>
    </main>

    <?php include "partials/footer.php"; ?>

</body>
</html>