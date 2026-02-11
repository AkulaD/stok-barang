<section class="readme">
    <h2>Penjelasan Jurnal Umum</h2>
    <ul>
        <li>
            <h4>1. Mekanisme Input Jurnal</h4>
            <p>
                Modul ini digunakan untuk mencatat setiap peristiwa ekonomi perusahaan ke dalam sistem secara kronologis.
            </p>
            <ul>
                <li><strong>Tanggal:</strong> Menentukan periode pelaporan transaksi tersebut.</li>
                <li><strong>Deskripsi:</strong> Keterangan singkat untuk memudahkan audit dan pelacakan di masa depan.</li>
                <li><strong>Nominal:</strong> Nilai moneter transaksi yang harus bernilai positif.</li>
            </ul>
        </li>

        <li>
            <h4>2. Prinsip Double-Entry (Debit & Kredit)</h4>
            <p>
                Setiap transaksi harus melibatkan minimal dua akun untuk menjaga keseimbangan persamaan akuntansi.
            </p>
            <ul>
                <li><strong>Akun Debit:</strong> Digunakan untuk mencatat peningkatan aset/beban atau penurunan kewajiban/modal.</li>
                <li><strong>Akun Kredit:</strong> Digunakan untuk mencatat penurunan aset atau peningkatan kewajiban/modal/pendapatan.</li>
                <li>Sistem secara otomatis memastikan nilai Debit dan Kredit selalu seimbang (balance).</li>
            </ul>
        </li>

        <li>
            <h4>3. Struktur Data Jurnal</h4>
            <p>
                Sistem membagi penyimpanan data jurnal ke dalam dua entitas tabel untuk efisiensi database.
            </p>
            <ul>
                <li><strong>Jurnal Header:</strong> Menyimpan informasi umum seperti ID unik, tanggal, dan siapa pengguna yang melakukan input.</li>
                <li><strong>Jurnal Detail:</strong> Menyimpan rincian setiap baris akun yang terlibat beserta nominal debit atau kreditnya.</li>
            </ul>
        </li>

        <li>
            <h4>4. Validasi & Keamanan Transaksi</h4>
            <p>
                Sistem melakukan pengecekan otomatis (server-side validation) sebelum data masuk ke database.
            </p>
            <ul>
                <li><strong>Mencegah Duplikasi:</strong> Penggunaan ID Jurnal berbasis timestamp dan random bytes untuk menghindari konflik data.</li>
                <li><strong>Integritas Akun:</strong> Akun debit dan kredit tidak diperbolehkan menggunakan akun yang sama dalam satu jurnal.</li>
                <li><strong>Transaction Rollback:</strong> Jika terjadi kegagalan sistem saat menyimpan salah satu baris, maka seluruh transaksi akan dibatalkan otomatis.</li>
            </ul>
        </li>

        <li>
            <h4>5. History & Penelusuran</h4>
            <p>
                Riwayat transaksi ditampilkan secara terkelompok berdasarkan ID Jurnal untuk memudahkan pembacaan.
            </p>
            <ul>
                <li>Data diurutkan dari transaksi terbaru (descending) untuk mempermudah pengawasan.</li>
                <li>Nama pengguna (User Input) dicatat untuk keperluan akuntabilitas data.</li>
                <li>Garis pemisah visual otomatis muncul di antara ID Jurnal yang berbeda pada tabel history.</li>
            </ul>
        </li>
    </ul>

    <div class="readme-box">
        Penting: Jurnal Umum adalah sumber data utama bagi Buku Besar. Pastikan pemilihan akun sudah sesuai dengan klasifikasi COA agar laporan saldo komulatif di Ledger tetap akurat.
    </div>
</section>