<?php
session_start();

if(!isset($_SESSION['login'])){
    header('location:login.php');
    exit;
}

if ($_SESSION['role']!=='admin'){
    header('location:product-out.php');
    exit;
}

include 'php/conn.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
    
</body>
</html>