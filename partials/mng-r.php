<section class="readme">
    <h2>Penjelasan Chart of Accounts (COA)</h2>
    <ul>
        <li>
            <h4>1. Input Kode Akun</h4>
            <p>
                Sistem menggunakan format penomoran bertingkat (X.XX.XX) untuk pengorganisasian data yang sistematis.
            </p>
            <ul>
                <li><strong>Bagian 1 (2 digit):</strong> Mewakili kategori utama akun.</li>
                <li><strong>Bagian 2 (3 digit):</strong> Mewakili sub-kategori atau kelompok akun.</li>
                <li><strong>Bagian 3 (3 digit):</strong> Mewakili detail spesifik akun tersebut.</li>
            </ul>
        </li>

        <li>
            <h4>2. Klasifikasi Tipe Akun</h4>
            <p>
                Setiap akun harus memiliki tipe yang jelas karena menentukan perlakuan saldo dan posisi di laporan keuangan.
            </p>
            <ul>
                <li><strong>Neraca:</strong> Asset (Harta), Liability (Hutang), dan Equity (Modal).</li>
                <li><strong>Laba Rugi:</strong> Revenue (Pendapatan), COGS (HPP), dan Expense (Beban).</li>
                <li>Tipe Laba Rugi tidak memiliki saldo awal (otomatis diset ke 0).</li>
            </ul>
        </li>

        <li>
            <h4>3. Manajemen Data & Pencarian</h4>
            <p>
                Fitur untuk memudahkan pengelolaan ribuan akun dalam satu antarmuka.
            </p>
            <ul>
                <li>Pencarian fleksibel berdasarkan kode angka maupun nama akun.</li>
                <li>Filter kategori untuk menampilkan kelompok akun tertentu saja.</li>
                <li>Opsi sembunyikan nonaktif untuk membersihkan tampilan dari akun lama.</li>
            </ul>
        </li>

        <li>
            <h4>4. Integrasi Ledger & Transaksi</h4>
            <p>
                COA berfungsi sebagai pondasi utama bagi seluruh modul keuangan di dalam sistem.
            </p>
            <ul>
                <li>Menjadi referensi utama saat melakukan input Jurnal Umum.</li>
                <li>Menentukan bagaimana saldo dihitung pada laporan Buku Besar.</li>
                <li>Akun yang dinonaktifkan tidak akan muncul dalam pilihan input transaksi baru.</li>
            </ul>
        </li>

        <li>
            <h4>5. Status dan Validasi</h4>
            <p>
                Keamanan data dipastikan melalui validasi sistem saat proses penyimpanan.
            </p>
            <ul>
                <li>Sistem mencegah duplikasi kode akun yang sama.</li>
                <li>Perubahan nama akun dapat dilakukan langsung pada tabel data.</li>
                <li>Status nonaktif digunakan untuk arsip tanpa menghapus riwayat transaksi lama.</li>
            </ul>
        </li>
    </ul>

    <div class="readme-box">
        Penting: Pastikan struktur pengkodean akun sudah direncanakan dengan matang, karena konsistensi kode akun sangat berpengaruh pada kerapihan laporan keuangan akhir.
    </div>
</section>