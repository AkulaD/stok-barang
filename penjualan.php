<?php
session_start();

if(!isset($_SESSION['login'])){
    header('location:login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'finance') {
    header('location: product-out.php');
    exit;
}

include 'php/conn.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <title>Document</title>
</head>
<body>
    <header>
        <!-- NAV DESKTOP -->
        <nav class="nav-desktop">
            <div class="left-side">
                <ul>
                    <li><a href="product-in.php">Product In</a></li>
                    <li><a href="product-out.php">Product Out</a></li>
                    <li><a href="penjualan.php">Penjualan</a></li>
                    <?php
                    if($_SESSION['role'] === 'admin'){
                        echo '<li><a href="user-management.php">User</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="right-side">
                <ul>
                    <li><a href="php/logout.php">Log Out</a></li>
                    <li><p><?= $_SESSION['username']; ?></p></li>
                </ul>
            </div>
        </nav>

        <!-- NAV MOBILE -->
        <nav class="nav-mobile">
            <div class="nav-mobile-head">
                <span class="brand">Stok Barang</span>
                <button class="nav-toggle">☰</button>
            </div>

            <ul class="nav-mobile-menu">
                <li><a href="product-in.php">Product In</a></li>
                <li><a href="product-out.php">Product Out</a></li>
                <li><a href="penjualan.php">Penjualan</a></li>

                <?php
                if($_SESSION['role'] === 'admin'){
                    echo '<li><a href="user-management.php">User</a></li>';
                }
                ?>

                <li class="divider"></li>
                <li class="user"><?= $_SESSION['username']; ?></li>
                <li><a href="php/logout.php">Log Out</a></li>
            </ul>
        </nav>
    </header>
    
</body>
</html>