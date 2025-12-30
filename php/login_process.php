<?php
session_start();
require 'conn.php';

if (!isset($_POST['login'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_POST['captcha'], $_SESSION['captcha'])) {
    header("Location: ../index.php?error=Captcha tidak valid");
    exit;
}

if (time() - $_SESSION['captcha_time'] < 3) {
    header("Location: ../index.php?error=Terlalu cepat");
    exit;
}

if (strtoupper($_POST['captcha']) !== $_SESSION['captcha']) {
    header("Location: ../index.php?error=Captcha salah");
    exit;
}

unset($_SESSION['captcha'], $_SESSION['captcha_time']);

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: ../index.php?error=Username tidak ditemukan");
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    header("Location: ../index.php?error=Password salah");
    exit;
}

$_SESSION['login'] = true;
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

header("Location: ../product-in.php");
exit;
