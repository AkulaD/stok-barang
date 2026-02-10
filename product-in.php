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
    <script src="data/js/script.js"></script>
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
                    <br>
                    <div class="button-submit">
                        <button class="btn-submit" type="submit">Submit</button>
                    </div>
                </form>
            </div>
            <div class="information-list">
                <h2>Information Products</h2>
                <div class="flex-info">
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
                        <script>
                            const chartStock = [
                            <?php
                            $p_data = [];
                            while ($row = mysqli_fetch_assoc($chart_stock)) {
                                $label = addslashes($row['nama_produk']);
                                $value = (int)$row['stok'];
                                $p_data[] = "{ label: '$label', value: $value }";
                            }
                            echo implode(",", $p_data);
                            ?>
                            ];
                        </script>
                    </div>
                </div>
            </div>
        </section>

        <section class="add-stock-form">
            <h2>Add Stock</h2>
            <form action="php/form-add-stock.php" method="post" class="safe-submit form-grid-product-in">
                <div class="field">
                    <label for="qr_code_input">QR Code:</label>
                    <input type="text" name="qr-code" id="qr_code_input" autocomplete="off">
                </div>
                <div class="field">
                    <label for="product_name_select">Product Name:</label>
                    <select name="name" id="product_name_select">
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
                    <label for="stock">Stock:</label>
                    <input type="number" name="stock" id="stock">
                </div>
                <div class="body-button">
                    <button class="btn-submit" type="submit">Submit</button>
                </div>
            </form>
        </section>

        <section class="product-list">
            <div class="list-body">
                <h2>Product List</h2>
                <div class="search-bar">
                    <input type="text" id="productSearch" placeholder="Search product name..." autocomplete="off">
                </div>

                <div class="table-container">
                    <table id="productTable">
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
                        <tbody id="productTableBody">
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
                                        <a class="a-submit" href="php/edit-product.php?id=<?= $row['id_produk']; ?>">Edit</a> |
                                        <a class="a-delete" href="php/delete.php?id=<?= $row['id_produk']; ?>" onclick="return confirm('Delete this product?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <br>     
        <?php include "partials/info-product-in.php"; ?>
    </main>
    
    <?php include "partials/footer.php" ?>
</body>
</html>