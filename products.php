<?php
session_start();
if(!isset($_SESSION['login'])){
    header('location: login.php');
    exit;
}
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

        </section>
    </main>
</body>
</html>