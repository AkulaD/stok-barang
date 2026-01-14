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
        l.tanggal,
        p.nama_produk,
        l.jumlah,
        l.harga,
        (l.jumlah * l.harga) AS subtotal,
        l.penjualan,
        l.keterangan
    FROM log_stok l
    JOIN produk p ON l.id_produk = p.id_produk
    WHERE l.tipe = 'keluar'
    AND DATE(l.tanggal) BETWEEN '$from' AND '$to'
    ORDER BY l.tanggal ASC
");

$totalQty = 0;
$totalSubtotal = 0;

$rows = '';
$no = 1;

while ($d = $query->fetch_assoc()) {
    $totalQty += $d['jumlah'];
    $totalSubtotal += $d['subtotal'];

    $rows .= "
        <tr>
            <td>{$no}</td>
            <td>".date('d-m-Y H:i', strtotime($d['tanggal']))."</td>
            <td>{$d['nama_produk']}</td>
            <td align='center'>{$d['jumlah']}</td>
            <td align='right'>Rp ".number_format($d['harga'],2,',','.')."</td>
            <td align='right'>Rp ".number_format($d['subtotal'],2,',','.')."</td>
            <td>{$d['penjualan']}</td>
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
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
}
h1 {
    text-align: center;
    color: #0d47a1;
    margin-bottom: 4px;
}
.periode {
    text-align: center;
    margin-bottom: 15px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background-color: #1e88e5 !important;
    color: #ffffff !important;
    padding: 6px;
    border: 1px solid #000;
    font-weight: bold;
}
td {
    padding: 5px;
    border: 1px solid #000;
}
.total {
    background: #c8e6c9;
    font-weight: bold;
}
.footer {
    margin-top: 20px;
    text-align: right;
    font-size: 9px;
}
</style>
</head>
<body>

<h1>LAPORAN TRANSAKSI PENJUALAN</h1>
<div class='periode'>{$judulTanggal}</div>

<table>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Produk</th>
    <th>Qty</th>
    <th>Harga</th>
    <th>Subtotal</th>
    <th>Lokasi</th>
    <th>Keterangan</th>
</tr>

{$rows}

<tr class='total'>
    <td colspan='3' align='center'>TOTAL</td>
    <td align='center'>{$totalQty}</td>
    <td></td>
    <td align='right'>Rp ".number_format($totalSubtotal,2,',','.')."</td>
    <td colspan='2'></td>
</tr>
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
