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

if (!in_array($_SESSION['role'], ['admin', 'viewer', 'uploader'])) {
    header('location: product-out.php');
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
    <title>Input - Stock | Stok Barang</title>
    <link rel="stylesheet" href="data/css/products.css">
    <link rel="stylesheet" href="data/css/style.css">
    <script src="data/js/products.js" defer></script>
</head>
<body>
    <?php include 'partials/nav.php'; ?>
    
    <main>
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>

        <section class="nav-product">
            <ul>
                <li><a href="product-in.php">Main</a></li>
                <li><a href="warehouse.php">Warehouse</a></li>
            </ul>
        </section>

        <section class="head-main">
            <div class="add-product">
            <h2>Add Product</h2>
                <form action="php/add-process.php" method="post" class="safe-submit">
                    <div class="form-add">
                        <div class="input-product">
                            <label>Product Name</label>
                            <br>
                            <input type="text" name="product" required>
                        </div>

                        <div class="input-stock">
                            <label>Add Stock</label>
                            <br>
                            <input type="number" name="stock" required value="1">
                        </div>

                        <div class="input-price">
                            <label>Price</label>
                            <br>
                            <input type="number" name="price" required>
                        </div>
                    </div>
                    <div class="button-submit">
                        <button class="btn-submit" type="submit">Submit</button>
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
        <section class="add-stock-form">
            <form action="php/form-add-stock.php" method="post" class="safe-submit">
                <h2>Add Stock</h2>
                <div class="form-add-body">
                    <div class="new-stok">
                        <div class="qr-code">
                            <label for="qr-code">QR Code:</label>
                            <input type="text" name="qr-code" id="qr-code">
                        </div>
                        <div class="add-stock">
                            <label for="stock">Stock:</label>
                            <input type="number" name="stock" id="stock">
                        </div>
                    </div>
                </div>
                <div class="body-button">
                    <button class="btn-submit" type="submit">Submit</button>
                </div>
            </form>
        </section>
        <section class="product-list">
            <div class="list-body">
                <h2>Product List</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th class="t-no">No</th>
                                <th class="t-name">Product Name</th>
                                <th class="t-b-stock" colspan="2">Stock</th>
                                <th class="t-price">Price</th>
                                <th class="t-qr-d">QR Download</th>
                                <th class="t-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="t-no"><?= $no++; ?></td>
                                    <td class="t-name"><?= htmlspecialchars($row['nama_produk']); ?></td>
                                    <td class="t-stock"><?= $row['stok']; ?></td>
                                    <td class="t-istock"><a class="a-submit" href="php/input-stock.php?id=<?= $row['id_produk']; ?>">Add</a></td>
                                    <td class="price">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>

                                    <td class="t-qr-d">
                                        <a class="a-submit" href="php/barcode.php?code=<?= urlencode($row['barcode']); ?>" target="_blank">
                                            Download QR
                                        </a>
                                    </td>

                                    <td class="t-action">
                                        <a class="a-submit" class="t-edit" href="php/edit-product.php?id=<?= $row['id_produk']; ?>">Edit</a> |
                                        <a class="a-delete" class="t-delete" href="php/delete.php?id=<?= $row['id_produk']; ?>"
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
        
        <?php include "partials/info-product-in.php"; ?>
    </main>
    
<?php include "partials/footer.php" ?>

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
        <strong>List Stok</strong>
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