<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: login.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'viewer', 'uploader'])) {
    header('location: ../product-out.php');
    exit;
}

require 'conn.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../product-in.php?status=invalid');
    exit;
}

$id_produk = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
$stmt->bind_param("s", $id_produk);

if ($stmt->execute()) {
    header('Location: ../product-in.php?status=deleted');
} else {
    header('Location: ../product-in.php?status=error');
}

$stmt->close();
$conn->close();
exit;
