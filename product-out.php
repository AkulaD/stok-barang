<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: index.php');
    exit;
}

if (!isset($_SESSION['role'])) {
    header('location: index.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin','viewer','cashier'])) {
    header('location: product-in.php');
    exit;
}

include 'php/conn.php';

$result = mysqli_query($conn,"
    SELECT * FROM produk 
    ORDER BY stok DESC
");

$whereDate = "";
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $_GET['from']." 00:00:00";
    $to   = $_GET['to']." 23:59:59";
    $whereDate = "AND transaksi.tanggal BETWEEN '$from' AND '$to'";
}

$history_log = mysqli_query($conn,"
    SELECT *
    FROM transaksi
    WHERE tipe='keluar'
    AND DATE(tanggal)=CURDATE()
    ORDER BY tanggal DESC
");

$chart_penjualan = mysqli_query($conn,"
    SELECT penjualan, SUM(jumlah) total
    FROM transaksi
    WHERE tipe='keluar' $whereDate
    GROUP BY penjualan
");

$chart_produk = mysqli_query($conn,"
    SELECT produk.nama_produk, SUM(transaksi.jumlah) total
    FROM transaksi
    JOIN produk ON transaksi.id_produk=produk.id_produk
    WHERE transaksi.tipe='keluar' $whereDate
    GROUP BY produk.nama_produk
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Output - Stock | Stok Baran</title>
    <link rel="stylesheet" href="data/css/style.css">
    <link rel="stylesheet" href="data/css/home.css">
	<script src="data/js/home.js" defer></script>
	<script src="data/js/script.js" defer></script>
</head>
<div id="loading-overlay" style="display:none;">
    <div class="spinner"></div>
    <p>Processing...</p>
</div>
<body>

<?php include 'partials/nav.php'; ?>

<main>
    
	<div class="top-section">
		<div class="card form-card">
			<h2>Product Out</h2>
			<form action="php/out-form.php" method="post" class="form-grid-product-out">
				<div class="field">
					<label>QR Code</label>
					<input type="text" name="qr_number" id="qr_number" placeholder="Scan atau ketik..." required>
				</div>
				
				<div class="field">
					<label for="name">Product Name</label>
					<select name="name" id="name">
						<option value="">-- Select Product --</option>
						<?php 
						$prod = mysqli_query($conn, "SELECT * FROM produk ORDER BY nama_produk ASC");
						while($p = mysqli_fetch_assoc($prod)):
						?>
						<option value="<?= $p['id_produk'] ?>" data-qr="<?= $p['barcode'] ?>">
							<?= $p['nama_produk'] ?>
						</option>
						<?php endwhile; ?>
					</select>
				</div>

				<div class="field">
					<label>Sales Channel</label>
					<select name="penjualan">
						<option value="offline">Offline</option>
						<option value="shopee">Shopee</option>
						<option value="tiktok">Tiktok</option>
						<option value="tokopedia">Tokopedia</option>
					</select>
				</div>

				<div class="form-actions">
					<button type="submit" class="btn-submit">Submit</button>
				</div>
			</form>
		</div>
	</div>

    <div class="flex-content">
        
        <div class="left-column">
			<div class="card filter-card">
				<h3>Filter Chart</h3>
				<form method="GET" class="form-grid">
					<div class="field">
						<label>From</label>
						<input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>">
					</div>
					<div class="field">
						<label>To</label>
						<input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>">
					</div>
					
					<div class="form-actions">
						<button class="btn-submit">Apply</button>
					</div>
				</form>
			</div>

            <div class="grid-charts">
                <div class="card">
                    <h3>Distribusi Penjualan</h3>
                    <canvas id="chartPenjualan" width="250" height="250"></canvas>
                    <div id="legendPenjualan"></div>
                </div>

                <div class="card">
                    <h3>Produk Keluar</h3>
                    <canvas id="chartProduk" width="250" height="250"></canvas>
                    <div id="legendProduk"></div>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="card table-card">
                <h3>History Today</h3>
				<a href="all-history.php">View All History</a>
				<br>
                <div class="table-wrap scrollable">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Date</th>
                                <th>Channel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no=1;
                            while($row=mysqli_fetch_assoc($history_log)):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nama_produk'] ?></td>
                                <td><?= $row['jumlah'] ?></td>
                                <td><?= $row['tanggal'] ?></td>
                                <td><?= $row['penjualan'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card table-card">
                <h3>Inventory</h3>
                <div class="table-wrap scrollable">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no=1;
                            while($row=mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nama_produk'] ?></td>
                                <td><?= $row['stok'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <?php include "partials/info-product-out.php"; ?>

</main>

<script>
const penjualanData = [
<?php
$d=[];
while($r=mysqli_fetch_assoc($chart_penjualan)){
    $d[]="{ label:'".$r['penjualan']."', value:".$r['total']." }";
}
echo implode(",",$d);
?>
];

const produkData = [
<?php
$d=[];
while($r=mysqli_fetch_assoc($chart_produk)){
    $d[]="{ label:'".$r['nama_produk']."', value:".$r['total']." }";
}
echo implode(",",$d);
?>
];

function generateColors(n){
    return Array.from({length:n},(_,i)=>`hsl(${360/n*i},70%,60%)`)
}

function drawPie(canvasId,data,legendId){
    const canvas=document.getElementById(canvasId);
    const ctx=canvas.getContext("2d");
    const colors=generateColors(data.length);
    const total=data.reduce((s,d)=>s+d.value,0);
    let angle=0;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if(total === 0) return;

    data.forEach((d,i)=>{
        const slice=(d.value/total)*Math.PI*2;
        ctx.beginPath();
        ctx.moveTo(125,125);
        ctx.arc(125,125,100,angle,angle+slice);
        ctx.fillStyle=colors[i];
        ctx.fill();
        angle+=slice;
    });

    const legend=document.getElementById(legendId);
    legend.innerHTML="";
    data.forEach((d,i)=>{
        const div=document.createElement("div");
        div.innerHTML=`
            <span style="background:${colors[i]}; width:0.75rem; height:0.75rem; display:inline-block; margin-right:0.5rem"></span>
            ${d.label} (${d.value})
        `;
        legend.appendChild(div);
    });
}

drawPie("chartPenjualan",penjualanData,"legendPenjualan");
drawPie("chartProduk",produkData,"legendProduk");
</script>

<?php include 'partials/footer.php'; ?>

</body>
</html>