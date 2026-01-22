<?php
session_start();
if (!isset($_SESSION['login'])) {
    exit;
}

require '../vendor/autoload.php';
include 'conn.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
if (!$from || !$to) {
    exit;
}

$query = $conn->query("
    SELECT 
        id_log,
        tanggal,
        nama_produk, -- Mengambil data mandiri dari tabel transaksi
        jumlah,
        harga,
        penjualan,
        keterangan
    FROM transaksi
    WHERE tipe = 'keluar'
    AND DATE(tanggal) BETWEEN '$from' AND '$to'
    ORDER BY tanggal ASC
");

$totalQty = 0;
$totalHarga = 0;
$rows = '';
$no = 1;

while ($d = $query->fetch_assoc()) {
    $totalQty += $d['jumlah'];
    $totalHarga += ($d['jumlah'] * $d['harga']);

    $rows .= "
        <tr>
            <td class='center'>{$no}</td>
            <td class='center'>".date('d-m-Y H:i', strtotime($d['tanggal']))."</td>
            <td>{$d['nama_produk']}</td>
            <td class='center'>{$d['jumlah']}</td>
            <td class='right'>Rp ".number_format($d['harga'],2,',','.')."</td>
            <td class='center'>{$d['id_log']}</td>
            <td class='center'>{$d['penjualan']}</td>
            <td>{$d['keterangan']}</td>
        </tr>
    ";
    $no++;
}

$judulTanggal = ($from === $to)
    ? 'Tanggal : '.date('d-m-Y', strtotime($from))
    : 'Periode : '.date('d-m-Y', strtotime($from)).' s/d '.date('d-m-Y', strtotime($to));

$html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #000;
}
.header {
    text-align: center;
    margin-bottom: 15px;
}
.header h1 {
    font-size: 16px;
    margin: 0;
    color: #0d47a1;
}
.header .periode {
    font-size: 11px;
    margin-top: 4px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background-color: #1565c0;
    color: #fff;
    padding: 6px;
    border: 1px solid #000;
    text-align: center;
    font-size: 10px;
}
td {
    padding: 5px;
    border: 1px solid #000;
    font-size: 9.5px;
}
.center {
    text-align: center;
}
.right {
    text-align: right;
}
.total {
    background-color: #c8e6c9;
    font-weight: bold;
}
.footer {
    margin-top: 15px;
    text-align: right;
    font-size: 9px;
}
</style>
</head>
<body>

<div class='header'>
    <h1>LAPORAN TRANSAKSI PENJUALAN</h1>
    <div class='periode'>{$judulTanggal}</div>
</div>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Produk</th>
    <th>Qty</th>
    <th>Harga</th>
    <th>ID Transaksi</th>
    <th>Lokasi</th>
    <th>Keterangan</th>
</tr>
</thead>
<tbody>
{$rows}
<tr class='total'>
    <td colspan='3' class='center'>TOTAL</td>
    <td class='center'>{$totalQty}</td>
    <td class='right'>Rp ".number_format($totalHarga,2,',','.')."</td>
    <td colspan='3'></td>
</tr>
</tbody>
</table>

<div class='footer'>
Dicetak pada ".date('d-m-Y H:i')."
</div>

</body>
</html>
";

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = ($from === $to)
    ? 'Transaksi '.date('d-m-Y', strtotime($from)).'.pdf'
    : 'Transaksi '.date('d-m-Y', strtotime($from)).' - '.date('d-m-Y', strtotime($to)).'.pdf';

$dompdf->stream($filename, ['Attachment' => true]);
exit;
