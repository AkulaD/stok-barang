<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('location:../index.php');
    exit;
}

require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location:../user-management.php?error=invalid_request');
    exit;
}

$id = 'USER-' . bin2hex(random_bytes(3));
$username = trim($_POST['username']);
$password = $_POST['password'];
$note = $_POST['note'] ?? null;
$role = $_POST['role'];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM user WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header('location:../user-management.php?error=username_exists');
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO user (id, username, password, note, role)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $id, $username, $hashed_password, $note, $role);
$stmt->execute();

$stmt->close();
$conn->close();

header('location:../user-management.php?success=1');
exit;
