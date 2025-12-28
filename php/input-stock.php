<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'uploader' && $_SESSION['role'] !== 'viewer') {
    header('location: ../product-out.php');
    exit;
}

include 'conn.php';

if (!isset($_GET['id'])) {
    header('Location: ../product-in.php');
    exit;
}

$query = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$id_product = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $added_stock = (int) $_POST['stock'];

    $stmt = $conn->prepare(
        "UPDATE produk 
         SET stok = stok + ? 
         WHERE id_produk = ?"
    );
    $stmt->bind_param("is", $added_stock, $id_product);

    if ($stmt->execute()) {
        $history_log = "INSERT INTO log_stok
                (id_log, id_produk, tipe, jumlah, keterangan) 
                VALUES (?, ?, 'masuk', ?, 'Add stock')";
        $id_log = 'UPD -' . bin2hex(random_bytes(6));

        $log_stmt = $conn->prepare($history_log);
        $log_stmt->bind_param("ssi", $id_log, $id_product, $added_stock);
        $log_stmt->execute();
        
        header("Location: ../product-in.php?stock_add=success");
        exit;
    } else {
        header("Location: ../product-in.php?stock_add=failure");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit - Stock</title>
    <link rel="stylesheet" href="../data/css/products.css">
    <script src="../data/js/products.js" defer></script>
    <link rel="stylesheet" href="../data/css/sub-form.css">
</head>
<body>
    <div id="loading-overlay" style="display:none;">
        <div class="spinner"></div>
        <p>Processing...</p>
    </div>
    
    <form action="" method="post">
        <a href="../product-in.php">Back</a>
        <h2>Add Stock</h2>
        <p><b><?= $row['nama_produk']; ?></b></p>
        <p>Current Stock: <?= $row['stok']; ?></p>
        <label for="stock">Input stock</label>
        <input type="number" name="stock" id="stock">
        <br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>