<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Stok Barang</title>
    <link rel="stylesheet" href="data/css/login.css">
</head>
<body>
<main>
    <div class="container">
        <form action="php/login_process.php" method="post">
            <h1>Login</h1>

            <div class="inp-body">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="inp-body">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="inp-body captcha-box">
                <label>Captcha</label>
                <img src="php/captcha.php" alt="captcha">
                <input type="text" name="captcha" required>
            </div>

            <div class="submit">
                <button type="submit" name="login">Login</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>
