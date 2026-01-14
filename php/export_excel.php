<?php
session_start();
if (!isset($_SESSION['login'])) {
    exit;
}

require '../vendor/autoload.php';
include 'conn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
if (!$from || !$to) {
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->mergeCells('A1:H1');
$sheet->mergeCells('A2:H2');

$sheet->setCellValue('A1', 'LAPORAN TRANSAKSI PENJUALAN');
$sheet->setCellValue(
    'A2',
    ($from === $to)
        ? 'Tanggal : '.date('d-m-Y', strtotime($from))
        : 'Periode : '.date('d-m-Y', strtotime($from)).' s/d '.date('d-m-Y', strtotime($to))
);

$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A2')->getFont()->setSize(11);
$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->fromArray([
    ['No','Tanggal','Produk','Jumlah','Harga','Subtotal','Lokasi','Keterangan']
], null, 'A4');

$sheet->getStyle('A4:H4')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1565C0']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

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

$row = 5;
$no = 1;
$totalQty = 0;
$totalSubtotal = 0;

while ($d = $query->fetch_assoc()) {
    $sheet->setCellValue('A'.$row, $no++);
    $sheet->setCellValue('B'.$row, date('d-m-Y H:i', strtotime($d['tanggal'])));
    $sheet->setCellValue('C'.$row, $d['nama_produk']);
    $sheet->setCellValue('D'.$row, $d['jumlah']);
    $sheet->setCellValue('E'.$row, $d['harga']);
    $sheet->setCellValue('F'.$row, $d['subtotal']);
    $sheet->setCellValue('G'.$row, $d['penjualan']);
    $sheet->setCellValue('H'.$row, $d['keterangan']);

    $sheet->getStyle('E'.$row.':F'.$row)
        ->getNumberFormat()
        ->setFormatCode('"Rp" #,##0.00');

    $sheet->getStyle('A'.$row.':H'.$row)
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $totalQty += $d['jumlah'];
    $totalSubtotal += $d['subtotal'];
    $row++;
}

$sheet->mergeCells('A'.$row.':C'.$row);
$sheet->setCellValue('A'.$row, 'TOTAL');
$sheet->setCellValue('D'.$row, $totalQty);
$sheet->setCellValue('F'.$row, $totalSubtotal);

$sheet->getStyle('F'.$row)
    ->getNumberFormat()
    ->setFormatCode('"Rp" #,##0.00');

$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'A5D6A7']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

foreach (range('A','H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = ($from === $to)
    ? 'Transaksi '.date('d-m-Y', strtotime($from)).'.xlsx'
    : 'Transaksi '.date('d-m-Y', strtotime($from)).' - '.date('d-m-Y', strtotime($to)).'.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
