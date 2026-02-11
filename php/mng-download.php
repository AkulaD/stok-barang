<?php
session_start();
include 'conn.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    exit('Akses ditolak');
}

$spreadsheet = new Spreadsheet();

function getBulanIndo($bulan) {
    $list = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    return $list[$bulan] ?? $bulan;
}

$tgl_cetak = date('d/m/Y');
$nama_bulan = getBulanIndo(date('m'));
$file_name_format = "Laporan Akuntansi - " . date('d') . " " . $nama_bulan . " " . date('Y');

$styleTitle = [
    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => 'center'],
    'fill' => [
        'fillType' => 'solid',
        'startColor' => ['rgb' => '1E40AF']
    ]
];

$styleHeader = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => 'center'],
    'borders' => [
        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
    ],
    'fill' => [
        'fillType' => 'solid',
        'startColor' => ['rgb' => 'F3F4F6']
    ]
];

$styleMoney = [
    'numberFormat' => [
        'formatCode' => '_-[$Rp-421]* #,##0_-;_-[$Rp-421]* (#,##0)_-;_-[$Rp-421]* "-"_-;_-@_-'
    ]
];

/* ==========================================
   SHEET 1: PROFIT & LOSS
   ========================================== */
$rev_q = mysqli_query($conn, "SELECT SUM(j.kredit - j.debit) as total FROM jurnal_detail j JOIN coa c ON j.kode_akun=c.kode_akun WHERE c.tipe_akun='Revenue'");
$cogs_q = mysqli_query($conn, "SELECT SUM(j.debit - j.kredit) as total FROM jurnal_detail j JOIN coa c ON j.kode_akun=c.kode_akun WHERE c.tipe_akun='COGS'");
$exp_q = mysqli_query($conn, "SELECT SUM(j.debit - j.kredit) as total FROM jurnal_detail j JOIN coa c ON j.kode_akun=c.kode_akun WHERE c.tipe_akun='Expense'");

$revenue = mysqli_fetch_assoc($rev_q)['total'] ?? 0;
$cogs = mysqli_fetch_assoc($cogs_q)['total'] ?? 0;
$expense = mysqli_fetch_assoc($exp_q)['total'] ?? 0;
$gross_profit = $revenue - $cogs;
$net_profit = $gross_profit - $expense;

$sheet0 = $spreadsheet->getActiveSheet();
$sheet0->setTitle('Profit & Loss');

$sheet0->mergeCells('A1:B1');
$sheet0->setCellValue('A1', 'LAPORAN LABA RUGI');
$sheet0->getStyle('A1')->applyFromArray($styleTitle);

$sheet0->setCellValue('A2', "Dicetak Tanggal : $tgl_cetak");
$sheet0->getStyle('A2')->getFont()->setItalic(true);

$sheet0->fromArray([
    ['Keterangan', 'Nilai (IDR)'],
    ['Total Revenue (Pendapatan)', (int)$revenue],
    ['Total COGS (HPP)', (int)$cogs],
    ['GROSS PROFIT', (int)$gross_profit],
    ['Total Operating Expense (Beban)', (int)$expense],
    ['NET PROFIT', (int)$net_profit]
], NULL, 'A4');

$sheet0->getStyle('A4:B4')->applyFromArray($styleHeader);
$sheet0->getStyle('B5:B9')->applyFromArray($styleMoney);
$sheet0->getStyle('A7:B7')->getFont()->setBold(true);
$sheet0->getStyle('A9:B9')->getFont()->setBold(true);
$sheet0->getColumnDimension('A')->setWidth(35);
$sheet0->getColumnDimension('B')->setWidth(25);

/* ==========================================
   SHEET 2: HISTORY JURNAL
   ========================================== */
$history = mysqli_query($conn, "
    SELECT h.tanggal, h.id_jurnal, h.deskripsi, h.user_input, d.kode_akun, d.nama_akun, d.debit, d.kredit
    FROM jurnal_header h
    JOIN jurnal_detail d ON h.id_jurnal = d.id_jurnal
    ORDER BY h.tanggal DESC, h.id_jurnal DESC
");

$sheet1 = $spreadsheet->createSheet();
$sheet1->setTitle('History Jurnal');

$sheet1->mergeCells('A1:H1');
$sheet1->setCellValue('A1', 'HISTORY JURNAL UMUM');
$sheet1->getStyle('A1')->applyFromArray($styleTitle);

$sheet1->setCellValue('A2', "Dicetak Tanggal : $tgl_cetak");

$sheet1->fromArray(['Tanggal', 'ID Jurnal', 'Deskripsi', 'User Input', 'Kode Akun', 'Nama Akun', 'Debit', 'Kredit'], NULL, 'A4');
$sheet1->getStyle('A4:H4')->applyFromArray($styleHeader);

$r = 5;
while ($d = mysqli_fetch_assoc($history)) {
    $sheet1->fromArray([
        $d['tanggal'], $d['id_jurnal'], $d['deskripsi'], $d['user_input'],
        $d['kode_akun'], $d['nama_akun'], (int)$d['debit'], (int)$d['kredit']
    ], NULL, "A$r");
    $r++;
}
$sheet1->getStyle("G5:H$r")->applyFromArray($styleMoney);
foreach (range('A', 'H') as $col) $sheet1->getColumnDimension($col)->setAutoSize(true);

/* ==========================================
   SHEET 3: GENERAL LEDGER
   ========================================== */
$ledger_q = mysqli_query($conn, "
    SELECT h.tanggal, h.deskripsi, d.kode_akun, d.nama_akun, d.debit, d.kredit, c.tipe_akun
    FROM jurnal_detail d
    JOIN jurnal_header h ON d.id_jurnal = h.id_jurnal
    JOIN coa c ON d.kode_akun = c.kode_akun
    ORDER BY d.kode_akun ASC, h.tanggal ASC
");

$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Ledger');

$sheet2->mergeCells('A1:F1');
$sheet2->setCellValue('A1', 'BUKU BESAR (GENERAL LEDGER)');
$sheet2->getStyle('A1')->applyFromArray($styleTitle);

$sheet2->setCellValue('A2', "Dicetak Tanggal : $tgl_cetak");

$sheet2->fromArray(['Tanggal', 'Kode Akun', 'Nama Akun', 'Debit', 'Kredit', 'Balance'], NULL, 'A4');
$sheet2->getStyle('A4:F4')->applyFromArray($styleHeader);

$r = 5;
$last_acc = '';
$balance = 0;
while ($l = mysqli_fetch_assoc($ledger_q)) {
    if ($last_acc != $l['kode_akun']) {
        $balance = 0;
        $last_acc = $l['kode_akun'];
    }

    if (in_array($l['tipe_akun'], ['Asset', 'Expense', 'COGS'])) {
        $balance += ($l['debit'] - $l['kredit']);
    } else {
        $balance += ($l['kredit'] - $l['debit']);
    }

    $sheet2->fromArray([
        $l['tanggal'], $l['kode_akun'], $l['nama_akun'], 
        (int)$l['debit'], (int)$l['kredit'], (int)$balance
    ], NULL, "A$r");
    $r++;
}
$sheet2->getStyle("D5:F$r")->applyFromArray($styleMoney);
foreach (range('A', 'F') as $col) $sheet2->getColumnDimension($col)->setAutoSize(true);

/* ==========================================
   SHEET 4: DATA COA
   ========================================== */
$coa_q = mysqli_query($conn, "SELECT kode_akun, nama_akun, tipe_akun, `group`, status FROM coa ORDER BY kode_akun ASC");

$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Data COA');

$sheet3->mergeCells('A1:E1');
$sheet3->setCellValue('A1', 'CHART OF ACCOUNTS');
$sheet3->getStyle('A1')->applyFromArray($styleTitle);

$sheet3->setCellValue('A2', "Dicetak Tanggal : $tgl_cetak");

$sheet3->fromArray(['Kode Akun', 'Nama Akun', 'Tipe', 'Grup Akun', 'Status'], NULL, 'A4');
$sheet3->getStyle('A4:E4')->applyFromArray($styleHeader);

$r = 5;
while ($c = mysqli_fetch_assoc($coa_q)) {
    $sheet3->fromArray([
        $c['kode_akun'], $c['nama_akun'], $c['tipe_akun'], 
        $c['group'], ($c['status'] ? 'Aktif' : 'Nonaktif')
    ], NULL, "A$r");
    $r++;
}
foreach (range('A', 'E') as $col) $sheet3->getColumnDimension($col)->setAutoSize(true);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $file_name_format . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;