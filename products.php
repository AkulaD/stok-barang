<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'uploader' && $_SESSION['role'] !== 'viewer') {
    header('location: index.php');
    exit;
}

include 'php/conn.php';

$result = mysqli_query($conn, "SELECT * FROM produk ORDER BY created_at DESC");
$total_product = mysqli_query($conn, "SELECT COUNT(*) FROM produk")->fetch_row()[0];
$total_stock = mysqli_query($conn, "SELECT SUM(stok) FROM produk")->fetch_row()[0];
$chart_stock = mysqli_query(
    $conn,
    "SELECT nama_produk, stok FROM produk WHERE stok > 0
    ORDER BY stok ASC"
);
$no = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="data/css/products.css">
    <link rel="stylesheet" href="data/css/style.css">
    <script src="data/js/products.js" defer></script>
</head>
<body>
    <header>
        <nav>
            <div class="left-side">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="penjualan.php">penjualan</a></li>
                    <?php
                        if($_SESSION['role'] === 'admin'){
                            echo "<li>";
                            echo '<a href="user-management.php">User</a>';
                            echo "</li>";
                        }
                    ?>
                </ul>
            </div>
            <div class="right-side">
                <ul>
                    <li><a href="php/logout.php">log Out</a></li>
                    <li><p><?php echo $_SESSION['username']; ?></p></li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>

        <section class="head-main">
            <div class="add-product">
            <h2>Add Product</h2>
                <form action="php/add-process.php" method="post">
                    <div class="form-add">
                        <div class="input-product">
                            <label>Product Name</label>
                            <input type="text" name="product" required>
                        </div>

                        <div class="input-stock">
                            <label>Add Stock</label>
                            <input type="number" name="stock" required value="1">
                        </div>

                        <div class="input-price">
                            <label>Price</label>
                            <input type="number" name="price" required>
                        </div>
                    </div>
                    <div class="button-submit">
                        <button type="submit">Submit</button>
                    </div>
                </form>
            </div>
            <div class="information-list">
                <h2>Information Products</h2>
                <div class="flex">
                    <div class="sub-products">
                        <div class="sub-total">
                            <h3>Total Product</h3>
                            <p><?= $total_product ?></p>
                        </div>
                        <div class="sub-stock">
                            <h3>Total Stock</h3>
                            <p><?= $total_stock ?></p>
                        </div>
                    </div>
                    <div class="sub-chart-stock">
                        <h3>Chart Stock</h3>
                        <div class="chart-stock">
                            <canvas id="chartStock" width="300" height="300"></canvas>
                            <div id="stockOverlay"></div>
                        </div>
                        <!-- JS -->
                        <script>
                            const chartStock = [
                            <?php
                            $p = [];
                            while ($row = mysqli_fetch_assoc($chart_stock)) {
                                $label = addslashes($row['nama_produk']);
                                $value = (int)$row['stok'];
                                $p[] = "{ label: '$label', value: $value }";
                            }
                            echo implode(",", $p);
                            ?>
                            ];
                        </script>
                    </div>
                </div>
            </div>
        </section>
        <section class="product-list">
            <div class="list-body">
                <h2>Product List</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product Name</th>
                                <th colspan="2">Stock</th>
                                <th>Price</th>
                                <th>QR Download</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                                    <td><?= $row['stok']; ?></td>
                                    <td><a href="php/input-stock.php?id=<?= $row['id_produk']; ?>">Add Stock</a></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>

                                    <td>
                                        <a href="php/barcode.php?code=<?= urlencode($row['barcode']); ?>" target="_blank">
                                            Download Barcode
                                        </a>
                                    </td>

                                    <td>
                                        <a href="php/edit-product.php?id=<?= $row['id_produk']; ?>">Edit</a> |
                                        <a href="php/delete.php?id=<?= $row['id_produk']; ?>"
                                        onclick="return confirm('Delete this product?')">
                                        Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

<script>
function generateColors(count) {
    return Array.from({ length: count }, (_, i) =>
        `hsl(${(360 / count) * i}, 70%, 55%)`
    );
}

function drawPie(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');

    const colors = generateColors(data.length);
    const total = data.reduce((s, d) => s + d.value, 0);

    const cx = canvas.width / 2;
    const cy = canvas.height / 2;
    const radius = 120;

    let angle = 0;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    data.forEach((d, i) => {
        const slice = (d.value / total) * Math.PI * 2;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, radius, angle, angle + slice);
        ctx.fillStyle = colors[i];
        ctx.fill();
        d.color = colors[i];
        angle += slice;
    });
}

function renderOverlayList(data) {
    const box = document.getElementById('stockOverlay');

    let html = `
        <div style="
            width:220px;
            max-height:220px;
            overflow-y:auto;
            background:#fff;
            border:1px solid #ddd;
            padding:10px;
            box-shadow:0 4px 10px rgba(0,0,0,.15);
            border-radius:6px;
            font-size:13px
        ">
        <strong>Daftar Stok</strong>
        <ul style="list-style:none;padding:0;margin:8px 0 0 0">
    `;

    data.forEach(d => {
        html += `
            <li style="display:flex;align-items:center;margin-bottom:6px">
                <span style="
                    width:12px;
                    height:12px;
                    background:${d.color};
                    display:inline-block;
                    margin-right:8px;
                    border-radius:3px
                "></span>
                ${d.label} (${d.value})
            </li>
        `;
    });

    html += "</ul></div>";
    box.innerHTML = html;
}

drawPie('chartStock', chartStock);
renderOverlayList(chartStock);
</script>
</body>
</html>