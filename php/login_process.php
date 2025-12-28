<?php
session_start();
include 'conn.php';

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['login'] = true;
            $_SESSION['username'] = $user['username'];

            if ($user['role'] === 'admin') {
                $_SESSION['role'] = 'admin';
            } elseif ($user['role'] === 'viewer') {
                $_SESSION['role'] = 'viewer';
            } elseif ($user['role'] === 'uploader') {
                $_SESSION['role'] = 'uploader';
            } elseif ($user['role'] === 'cashier') {
                $_SESSION['role'] = 'cashier';
            }

            header("Location: ../product-in.php");
            exit;

        } else {
            header("Location: ../index.php?error=Password salah");
            exit;
        }

    } else {
        header("Location: ../index.php?error=Username tidak ditemukan");
        exit;
    }
}
