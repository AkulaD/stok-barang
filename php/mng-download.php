<?php
session_start();
include 'conn.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

$styleTitle = [
    'font'=>['bold'=>true,'size'=>18,'color'=>['rgb'=>'FFFFFF']],
    'alignment'=>['horizontal'=>'center'],
    'fill'=>[
        'fillType'=>'solid',
        'startColor'=>['rgb'=>'2563EB']
    ]
];

$styleHeader = [
    'font'=>['bold'=>true],
    'alignment'=>['horizontal'=>'center'],
    'fill'=>[
        'fillType'=>'solid',
        'startColor'=>['rgb'=>'E5E7EB']
    ]
];

$styleMoney = [
    'numberFormat'=>[
        'formatCode'=>'#,##0'
    ]
];

$pendapatan_q = mysqli_query($conn,"
SELECT SUM(j.kredit - j.debit) as total
FROM jurnal_detail j
JOIN coa c ON j.kode_akun=c.kode_akun
WHERE c.tipe_akun='pendapatan'
");

$beban_q = mysqli_query($conn,"
SELECT SUM(j.debit - j.kredit) as total
FROM jurnal_detail j
JOIN coa c ON j.kode_akun=c.kode_akun
WHERE c.tipe_akun='beban'
");

$p=mysqli_fetch_assoc($pendapatan_q);
$b=mysqli_fetch_assoc($beban_q);

$total_pendapatan=$p['total']??0;
$total_beban=$b['total']??0;
$laba=$total_pendapatan-$total_beban;

$sheet0=$spreadsheet->getActiveSheet();
$sheet0->setTitle('Profit & Loss');

$sheet0->mergeCells('A1:B1');
$sheet0->setCellValue('A1','LAPORAN LABA RUGI');
$sheet0->getStyle('A1')->applyFromArray($styleTitle);

$sheet0->fromArray([
['Keterangan','Nilai (IDR)'],
['Total Pendapatan',$total_pendapatan],
['Total Beban',$total_beban],
['Laba / Rugi',$laba]
],NULL,'A3');

$sheet0->getStyle('A3:B3')->applyFromArray($styleHeader);
$sheet0->getStyle('B4:B6')->applyFromArray($styleMoney);
$sheet0->getColumnDimension('A')->setWidth(30);
$sheet0->getColumnDimension('B')->setWidth(20);

$history=mysqli_query($conn,"
SELECT h.tanggal,h.id_jurnal,h.deskripsi,h.user_input,
d.kode_akun,d.nama_akun,d.debit,d.kredit
FROM jurnal_header h
JOIN jurnal_detail d ON h.id_jurnal=d.id_jurnal
ORDER BY h.tanggal DESC
");

$sheet1=$spreadsheet->createSheet();
$sheet1->setTitle('History Jurnal');

$sheet1->mergeCells('A1:H1');
$sheet1->setCellValue('A1','HISTORY JURNAL');
$sheet1->getStyle('A1')->applyFromArray($styleTitle);

$sheet1->fromArray(
['Tanggal','ID','Deskripsi','User','Kode','Nama Akun','Debit','Kredit'],
NULL,'A3'
);
$sheet1->getStyle('A3:H3')->applyFromArray($styleHeader);

$r=4;
while($d=mysqli_fetch_assoc($history)){
    $sheet1->fromArray([
        $d['tanggal'],
        $d['id_jurnal'],
        $d['deskripsi'],
        $d['user_input'],
        $d['kode_akun'],
        $d['nama_akun'],
        $d['debit'],
        $d['kredit']
    ],NULL,"A$r");
    $r++;
}

$sheet1->getStyle("G4:H$r")->applyFromArray($styleMoney);

$coa_q=mysqli_query($conn,"SELECT kode_akun,nama_akun,tipe_akun FROM coa");

$sheet2=$spreadsheet->createSheet();
$sheet2->setTitle('COA');

$sheet2->mergeCells('A1:C1');
$sheet2->setCellValue('A1','DATA COA');
$sheet2->getStyle('A1')->applyFromArray($styleTitle);

$sheet2->fromArray(['Kode','Nama Akun','Tipe'],NULL,'A3');
$sheet2->getStyle('A3:C3')->applyFromArray($styleHeader);

$r=4;
while($c=mysqli_fetch_assoc($coa_q)){
    $sheet2->fromArray([
        $c['kode_akun'],
        $c['nama_akun'],
        $c['tipe_akun']
    ],NULL,"A$r");
    $r++;
}

$ledger_q=mysqli_query($conn,"
SELECT h.tanggal,d.kode_akun,d.nama_akun,d.debit,d.kredit
FROM jurnal_detail d
JOIN jurnal_header h ON d.id_jurnal=h.id_jurnal
ORDER BY d.kode_akun,h.tanggal
");

$sheet3=$spreadsheet->createSheet();
$sheet3->setTitle('Ledger');

$sheet3->mergeCells('A1:E1');
$sheet3->setCellValue('A1','GENERAL LEDGER');
$sheet3->getStyle('A1')->applyFromArray($styleTitle);

$sheet3->fromArray(
['Tanggal','Kode','Nama Akun','Debit','Kredit'],
NULL,'A3'
);
$sheet3->getStyle('A3:E3')->applyFromArray($styleHeader);

$r=4;
while($l=mysqli_fetch_assoc($ledger_q)){
    $sheet3->fromArray([
        $l['tanggal'],
        $l['kode_akun'],
        $l['nama_akun'],
        $l['debit'],
        $l['kredit']
    ],NULL,"A$r");
    $r++;
}

$sheet3->getStyle("D4:E$r")->applyFromArray($styleMoney);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="laporan_akuntansi.xlsx"');
header('Cache-Control: max-age=0');

$writer=new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
