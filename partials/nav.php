<header>
    <!-- NAV DESKTOP -->
    <nav class="nav-desktop">
        <div class="left-side">
            <ul>
                <li><a href="product-in.php">Product In</a></li>
                <li><a href="product-out.php">Product Out</a></li>
                <li><a href="penjualan.php">Penjualan</a></li>
                <?php
                if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){
                    echo '
                    <li class="dropdown">
                        <a href="#">Management ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="management-report.php">Report</a></li>
                            <li><a href="management-jurnal.php">Jurnal</a></li>
                            <li><a href="management-ledger.php">Ledger</a></li>
                            <li><a href="management-p&l.php">P&L</a></li>
                        </ul>
                    </li>';
                    echo '<li><a href="user-management.php">User</a></li>';
                }
                ?>
            </ul>
        </div>
        <div class="right-side">
            <ul>
                <li><a href="php/logout.php">Log Out</a></li>
                <li><p><?= $_SESSION['username']; ?></p></li>
            </ul>
        </div>
    </nav>

    <!-- NAV MOBILE -->
    <nav class="nav-mobile">
        <div class="nav-mobile-head">
            <span class="brand">Stok Barang</span>
            <button class="nav-toggle">☰</button>
        </div>

        <ul class="nav-mobile-menu">
            <li><a href="product-in.php">Product In</a></li>
            <li><a href="product-out.php">Product Out</a></li>
            <li><a href="penjualan.php">Penjualan</a></li>

            <?php
            if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){
                echo '
                <li class="dropdown">
                    <a href="#">Management ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="management-report.php">Report</a></li>
                        <li><a href="management-jurnal.php">Jurnal</a></li>
                        <li><a href="management-ledger.php">Ledger</a></li>
                        <li><a href="management-p&l.php">P&L</a></li>
                    </ul>
                </li>';
                echo '<li><a href="user-management.php">User</a></li>';
            }
            ?>

            <li class="divider"></li>
            <li class="user"><?= $_SESSION['username']; ?></li>
            <li><a href="php/logout.php">Log Out</a></li>
        </ul>
    </nav>
</header>