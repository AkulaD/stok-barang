<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location: ../index.php');
    exit;
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update History | Stok Barang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f4f5f7;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            color: #333;
        }

        header {
            width: 100%;
            max-width: 800px;
            margin-bottom: 20px;
            text-align: right;
        }

        header a {
            text-decoration: none;
            color: #ff4757;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        header a:hover {
            color: #ff6b81;
        }

        main {
            width: 100%;
            max-width: 800px;
        }

        section {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            margin-bottom: 25px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: transform 0.3s ease;
        }

        section:hover {
            transform: translateY(-5px);
        }

        h1 {
            color: #2f3542;
            font-size: 1.8rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        h1::after {
            content: 'Stable';
            font-size: 0.7rem;
            background: #2ed573;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        section:first-of-type h1::after {
            content: 'Latest Update';
            background: #1e90ff;
        }

        section p:nth-of-type(1) {
            color: #747d8c;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        section p:nth-of-type(2) {
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 1rem;
            color: #57606f;
        }

        ul {
            list-style: none;
            padding-left: 5px;
        }

        ul li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 10px;
            line-height: 1.5;
            color: #2f3542;
            font-size: 0.95rem;
        }

        ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #2ed573;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            section {
                padding: 20px;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="../php/logout.php">Back (log Out)</a>
    </header>
    <main>
        <section>
            <h1>2.9</h1>
            <p>Feb 11, 2026</p>
            <p>Perbaikan Bug dan interface product-out dan product-in, dan fitur baru sebagai berikut:</p>
            <ul>
                <li>Penambahan fitur management COA</li>
                <li>Penambahan ledger</li>
            </ul>
        </section>

        <section>
            <h1>V2.8</h1>
            <p>Jan 29, 2026</p>
            <p>Penyesuaian interface, penambahan histori untuk perubahan harga dan perbaikan bug login.</p>
        </section>

        <section>
            <h1>V2.7A</h1>
            <p>Jan 23, 2026</p>
            <p>Perbaikan tampilan dan database.</p>
        </section>

        <section>
            <h1>V2.7</h1>
            <p>Jan 23, 2026</p>
            <p>Perbaikan terhadap tabel SQL dan perbaikan terhadap bug query dan penambahan fitur Baru.</p>
            <ul>
                <li>Perbaikan terhadap tabel SQL.</li>
                <li>Perbaikan bug query transaksi dan log stok.</li>
                <li>Penambahan fitur penjualan.</li>
                <li>Perubahan revenue pada penjualan.</li>
            </ul>
        </section>

        <section>
            <h1>V2.5</h1>
            <p>Jan 18, 2026</p>
            <p>Penambahan fitur warehouse</p>
            <ul>
                <li>Penambahan fitur warehouse.</li>
            </ul>
        </section>

        <section>
            <h1>V2.0</h1>
            <p>Jan 17, 2026</p>
            <p>Perbaikan tampilan dan fitur-fitur baru.</p>
        </section>

        <section>
            <h1>V1.5, V1.5B, V1.5C, V1.5D</h1>
            <p>Jan 14 - 17, 2026</p>
            <p>Perbaikan tampilan dan perbaikan bug serta optimalisasi.</p>
        </section>

        <section>
            <h1>V1.2</h1>
            <p>Jan 14, 2026</p>
            <p>Penambahan fitur penjualan dan transaksi keuangan</p>
            <ul>
                <li>Penambahan fitur penjualan.</li>
                <li>Penambahan fitur mutasi transaksi.</li>
                <li>Perbaikan fitu, dan optimalisasi</li>
            </ul>
        </section>

        <section>
            <h1>V1.0</h1>
            <p>Jan 13, 2026</p>
            <p>Release date</p>
        </section>
    </main>
</body>
</html>