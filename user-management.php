<?php
session_start();

if(!isset($_SESSION['login'])){
    header('location:login.php');
    exit;
}

if ($_SESSION['role']!=='admin'){
    header('location:product-out.php');
    exit;
}

include 'php/conn.php';

$result = mysqli_query($conn, "SELECT * FROM user ORDER BY tanggal DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/userM.css">
    <link rel="stylesheet" href="data/css/style.css">
    <script src="data/js/userM.js" defer></script>
    <title>Document</title>
</head>
<body>
    <header>
        <nav>
            <div class="left-side">
                <ul>
                <li><a href="product-in.php">Product In</a></li>
                <li><a href="product-out.php">Product Out</a></li>
                    <li><a href="penjualan.php">penjualan</a></li>
                    <?php
                        if($_SESSION['role'] === 'admin'){
                            echo "<li>";
                            echo '<a href="user-management.php">User</a>';
                            echo "</li>";
                        }
                    ?>
                </ul>
            </div>
            <div class="right-side">
                <ul>
                    <li><a href="php/logout.php">log Out</a></li>
                    <li><p><?php echo $_SESSION['username']; ?></p></li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>
        <h1>User-Management</h1>
        <div class="new-user">
            <form action="php/add-new-user.php" class="input-new-user safe-submit" method="post">
                <h2>Add New User</h2>
                <div class="inp-content">
                    <div class="inpt-body">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="inpt-body">
                        <label for="password">password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="inpt-body">
                        <label for="note">Note (Optional)</label>
                        <input type="text" name="note" id="note">
                    </div>
                    <div class="inpt-body">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="uploader">In-put</option>
                            <option value="cashier">Out-put</option>
                            <option value="viewer">Output-Input</option>
                        </select>
                    </div>
                </div>
                <div class="action-add">
                    <button type="submit">Add User</button>
                </div>
            </form>
        </div>
        <hr>
        <section class="flex">
            <div class="information-body">
                <h2>Informasi Tentang Role</h2>
                <div class="content">
                    <ul>
                        <li>
                            <p><b>Admin</b></p>
                            <p>Bisa mengakses semua fitur termasuk Input (Product-in), Output (product-out), Penjualan, User-Management.</p>
                        </li>
                        <li>
                            <p><b>In-put</b></p>
                            <p>Memiliki akses untuk Input (Product-in), tidak bisa mengakses Output (product-out).</p>
                        </li>
                            <p><b>Output</b></p>
                            <p>Memiliki akses untuk Output (product-out), tidak bisa mengakses Input (Product-in).</p>
                        </li>
                        <li>
                            <p><b>Output-Input</b></p>
                            <p>Bisa memiliki akses untuk Output (product-out) dan Input (Product-in).</p>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="list-user">
                <h2>List User</h2>
                <?php
                    while($row = mysqli_fetch_assoc($result)):
                ?>
                <section class="body-user">
                    <form class="card" method="post" action="php/edit-user.php" >
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <div class="inp-content">
                            <div class="left-body">
                                <div class="inp-body">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" value="<?= $row['username'];  ?>" required>
                                </div>
                                <div class="inp-body">
                                    <label for="datetime">Date Time</label>
                                    <input type="text" name="datetime" id="datetime" value="<?= $row['tanggal'];?>" readonly>
                                </div>
                            </div>
                            <div class="right-body">
                                <div class="inp-body">
                                    <label for="note">Note</label>
                                    <input type="text" name="note" id="note" value="<?= $row['note'];  ?>">
                                </div>
                                <div class="inp-body">
                                    <label for="role">Role</label>
                                    <select name="role" id="role" required>
                                        <option value="admin"   <?= $row['role']=='admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="uploader"<?= $row['role']=='uploader' ? 'selected' : '' ?>>In-put</option>
                                        <option value="cashier" <?= $row['role']=='cashier' ? 'selected' : '' ?>>Out-put</option>
                                        <option value="viewer"  <?= $row['role']=='viewer' ? 'selected' : '' ?>>Output-Input</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="Npassword-body">
                            <div class="inp-body">
                                <label for="password">New Password</label>
                                <input type="password" name="password" id="password" placeholder="Optional">
                            </div>
                        </div>

                        <div class="action">
                            <button type="submit" onclick="return confirm('Confirm Edit-user?')"name="edit">Edit</button>

                            <?php if ($row['role'] === 'admin'): ?>
                                <p>Can't Delete role Admin</p>
                            <?php else: ?>
                                <a href="php/delete-user.php?id=<?= $row['id'] ?>"
                                onclick="return confirm('Confirm delete-user?')">
                                Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>
                <?php
                    endwhile;
                ?>
            </div>
        </section>
    </main>
</body>
</html>