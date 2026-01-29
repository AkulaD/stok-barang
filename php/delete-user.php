<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('location:../index.php');
    exit;
}

require 'conn.php';

if (!isset($_GET['id'])) {
    header('location:../user-management.php?error=invalid_request');
    exit;
}

$id = $_GET['id'];

$check = $conn->prepare("SELECT role FROM user WHERE id = ?");
$check->bind_param("s", $id);
$check->execute();
$result = $check->get_result();
$user   = $result->fetch_assoc();

if (!$user) {
    header('location:../user-management.php?error=user_not_found');
    exit;
}

if ($user['role'] === 'admin') {
    header('location:../user-management.php?error=cannot_delete_admin');
    exit;
}

$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();

$stmt->close();
$conn->close();

header('location:../user-management.php?success=delete');
exit;
