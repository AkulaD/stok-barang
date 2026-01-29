<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('location:../index.php');
    exit;
}

require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('location:../user-management.php?error=invalid_request');
    exit;
}

$id = $_POST['id'];
$username = trim($_POST['username']);
$note = $_POST['note'] ?? null;
$role = $_POST['role'];
$password = $_POST['password'] ?? '';

$check = $conn->prepare("SELECT role FROM user WHERE id = ?");
$check->bind_param("s", $id);
$check->execute();
$result = $check->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('location:../user-management.php?error=user_not_found');
    exit;
}

if ($user['role'] === 'admin' && $role !== 'admin') {
    header('location:../user-management.php?error=cannot_change_admin');
    exit;
}

if (empty($password)) {
    $stmt = $conn->prepare(
        "UPDATE user SET username=?, note=?, role=? WHERE id=?"
    );
    $stmt->bind_param("ssss", $username, $note, $role, $id);
} else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "UPDATE user SET username=?, password=?, note=?, role=? WHERE id=?"
    );
    $stmt->bind_param("sssss", $username, $hashed, $note, $role, $id);
}

$stmt->execute();
$stmt->close();
$conn->close();

header('location:../user-management.php?success=edit');
exit;
