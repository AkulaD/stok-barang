<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: ../index.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'uploader' && $_SESSION['role'] !== 'viewer') {
    header('location: ../product-out.php');
    exit;
}

include 'conn.php';

if(!isset($_GET['id'])){
    header('location: ../product-in.php');
    exit;
}

$id_product = $_GET['id'];

$query = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = $conn->prepare($query);
$stmt ->bind_Param("s",$id_product);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();


if($_SERVER['REQUEST_METHOD'] === "POST"){
    $edit_name = $_POST['product'];
    $edit_price = $_POST['price'];

    $update_query = "UPDATE produk SET nama_produk = ?, harga = ? WHERE id_produk = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sis", $edit_name, $edit_price, $id_product);
    if($update_stmt->execute()){
        $update_stmt->close();
        header('location: ../product-in.php?edit=success');
    }else{
        header('location: ../product-in.php?edit=failure');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit | Stok Barang</title>
    <link rel="stylesheet" href="../data/css/edit.css">
    <script src="../data/js/products.js" defer></script>
</head>
<body>
    <div class="form-container">
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>
        
        <form action="" method="post">
            <a href="../product-in.php">Back</a>
            <h2>Edit Product</h2>
            <div class="edit-name">
                <label for="product">Product Name:</label>
                <input type="text" name="product" id="product" value="<?= $row['nama_produk']; ?>">
            </div>
            <div class="edit-price">
                <label for="price">Price:</label>
                <input type="number" name="price" id="price" value="<?= $row["harga"]; ?>">
            </div>
            <button type="submit" name="update">Update Product</button>
        </form>
    </div>
</body>
</html>