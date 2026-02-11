<section class="readme">
    <h2>Penjelasan Laporan Laba Rugi (P&L)</h2>
    <ul>
        <li>
            <h4>1. Konsep Dasar P&L</h4>
            <p>
                Laporan Profit and Loss (P&L) menyajikan ringkasan pendapatan dan biaya untuk mengukur kinerja keuangan perusahaan.
            </p>
            <ul>
                <li><strong>Profitabilitas:</strong> Menentukan apakah bisnis menghasilkan keuntungan atau kerugian bersih.</li>
                <li><strong>Efisiensi:</strong> Melihat seberapa besar margin yang tersisa setelah menutupi biaya produksi.</li>
                <li><strong>Periode:</strong> Data bersifat akumulatif dari seluruh transaksi yang tercatat di Jurnal.</li>
            </ul>
        </li>

        <li>
            <h4>2. Pendapatan & HPP (Gross Profit)</h4>
            <p>
                Tahap pertama perhitungan untuk mengetahui keuntungan langsung dari penjualan barang atau jasa.
            </p>
            <ul>
                <li><strong>Revenue:</strong> Total nilai kredit dikurangi debit pada akun bertipe 'Revenue'.</li>
                <li><strong>COGS (HPP):</strong> Beban pokok yang dikeluarkan langsung untuk barang yang terjual.</li>
                <li><strong>Gross Profit:</strong> Hasil dari Pendapatan dikurangi HPP sebelum dipotong biaya operasional.</li>
            </ul>
        </li>

        <li>
            <h4>3. Beban Operasional & Laba Bersih</h4>
            <p>
                Tahap akhir perhitungan yang mempertimbangkan biaya pendukung jalannya bisnis.
            </p>
            <ul>
                <li><strong>Operating Expense:</strong> Mencakup biaya seperti gaji, sewa, listrik, dan biaya admin lainnya.</li>
                <li><strong>Net Profit:</strong> Keuntungan murni yang siap digunakan atau diinvestasikan kembali (Gross Profit - Expense).</li>
                <li>Jika Net Profit bernilai negatif, sistem akan menampilkannya sebagai indikasi kerugian.</li>
            </ul>
        </li>

        <li>
            <h4>4. Integritas Sumber Data</h4>
            <p>
                Laporan ini tidak berdiri sendiri, melainkan hasil sintesa dari modul-modul sebelumnya.
            </p>
            <ul>
                <li><strong>Validasi COA:</strong> Hanya akun dengan tipe Revenue, COGS, dan Expense yang masuk ke perhitungan ini.</li>
                <li><strong>Akurasi Jurnal:</strong> Setiap saldo diambil dari nilai riil yang diinput pada Jurnal Umum.</li>
                <li><strong>Real-time:</strong> Angka akan berubah secara otomatis setiap kali ada transaksi jurnal baru yang disimpan.</li>
            </ul>
        </li>

        <li>
            <h4>5. Pelaporan & Analisis</h4>
            <p>
                Fasilitas tambahan untuk kebutuhan dokumentasi dan audit eksternal.
            </p>
            <ul>
                <li><strong>Export Excel:</strong> Memungkinkan data laporan diunduh untuk diolah lebih lanjut atau dicetak.</li>
                <li><strong>Evaluasi:</strong> Digunakan manajemen untuk menentukan strategi efisiensi biaya pada periode berikutnya.</li>
            </ul>
        </li>
    </ul>

    <div class="readme-box">
        Catatan: Pastikan semua akun biaya dikategorikan dengan benar sebagai 'COGS' atau 'Expense' di COA agar perhitungan Laba Kotor (Gross Profit) tidak tertukar dengan Laba Bersih.
    </div>
</section>