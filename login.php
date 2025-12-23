<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stok Barang</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="left-content">
                <h1>Login</h1>
                <h2>Stok Barang</h2>
                <p>Manajemen Stok Barang</p>
            </div>
            <div class="right-content">
                <form action="php/login_process.php" method="post">
                    <div class="username">
                        <label for="username">Username: </label>
                        <input type="username" name="username" id="username" required>
                    </div>
                    <div class="password">
                        <label for="password">Password: </label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="submit">
                        <button type="submit" name="login">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>