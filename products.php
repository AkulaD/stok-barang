<?php
session_start();
if(!isset($_SESSION['login'])){
    header('location: login.php');
    exit;
}

include 'php/conn.php';

$result = mysqli_query($conn, "SELECT * FROM produk ORDER BY created_at DESC");
$no = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="data/css/products.css">
    <script src="data/js/products.js" defer></script>
</head>
<body>
    <head>
        <nav>
            <div class="left-side">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="info.php">info</a></li>
                </ul>
            </div>
            <div class="right-side">
                <ul>
                    <li><a href="php/logout.php">log Out</a></li>
                    <li><p><?php echo $_SESSION['username']; ?></p></li>
                </ul>
            </div>
        </nav>
    </head>
    <main>
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>

        <section class="add-product">
            <div class="add-body">
                <button onclick="popup_add()">Add Products</button>
                <div class="add-popup">
                    <form action="php/add-process.php" method="post">
                        <div class="body-popup">
                            <div class="close-button">
                                <button type="button" onclick="close_add()">X</button>
                            </div>

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

                            <div class="button-submit">
                                <button type="submit">Submit</button>
                            </div>
                        </div>
                    </form>
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
</body>
</html>