<section class="readme">
    <h2>Penjelasan Ledger</h2>
    <ul>

        <li>
            <h4>1. Fungsi Buku Besar</h4>
            <p>
                Buku besar menampilkan riwayat transaksi setiap akun secara detail.
            </p>
            <ul>
                <li>Menunjukkan pergerakan saldo akun.</li>
                <li>Menjadi dasar laporan keuangan.</li>
                <li>Membantu pengecekan kesalahan pencatatan.</li>
            </ul>
        </li>

        <li>
            <h4>2. Account Code</h4>
            <p>
                Kode unik untuk setiap akun pada COA.
            </p>
            <ul>
                <li>Membedakan jenis akun.</li>
                <li>Digunakan untuk pengelompokan data.</li>
                <li>Tidak boleh sama antar akun.</li>
            </ul>
        </li>

        <li>
            <h4>3. Account Name</h4>
            <p>
                Nama akun yang menjelaskan fungsi akun tersebut.
            </p>
            <ul>
                <li>Contoh: Kas, Persediaan, Pendapatan.</li>
                <li>Memudahkan identifikasi transaksi.</li>
            </ul>
        </li>

        <li>
            <h4>4. Debit dan Credit</h4>
            <p>
                Menampilkan nilai transaksi masuk dan keluar.
            </p>
            <ul>
                <li>Debit menambah akun tertentu.</li>
                <li>Credit mengurangi akun tertentu.</li>
                <li>Semua data berasal dari jurnal umum.</li>
            </ul>
        </li>

        <li>
            <h4>5. Balance (Saldo)</h4>
            <p>
                Saldo berjalan dari setiap akun.
            </p>
            <ul>
                <li>Dihitung dari debit dikurangi kredit.</li>
                <li>Update otomatis setiap transaksi.</li>
                <li>Saldo negatif ditandai khusus.</li>
            </ul>
        </li>

        <li>
            <h4>6. Sumber Data</h4>
            <p>
                Semua data diambil dari jurnal_detail.
            </p>
            <ul>
                <li>Berdasarkan tanggal transaksi.</li>
                <li>Diurutkan per kode akun.</li>
                <li>Tergantung input jurnal yang benar.</li>
            </ul>
        </li>

    </ul>

    <div class="readme-box">
        Buku besar membantu memantau saldo setiap akun secara real-time. Pastikan jurnal diinput dengan benar agar saldo akurat.
    </div>
</section>
