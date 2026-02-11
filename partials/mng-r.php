<section class="readme">
    <h2>Penjelasan Chart of Accounts (COA)</h2>
    <ul>
        <li>
            <h4>1. Format Kode Akun (XX.XXX.XXX)</h4>
            <p>
                Sistem menggunakan standardisasi kode akun 8 digit untuk memastikan pengurutan laporan yang konsisten.
            </p>
            <ul>
                <li><strong>Bagian 1 (2 digit):</strong> Mewakili kategori utama akun (01: Asset, 02: Liability, dst).</li>
                <li><strong>Bagian 2 (3 digit):</strong> Mewakili sub-kategori atau kelompok akun.</li>
                <li><strong>Bagian 3 (3 digit):</strong> Mewakili detail spesifik akun tersebut untuk identitas unik.</li>
                <li><em>Input akan otomatis ditambahkan angka "0" di depan jika digit yang dimasukkan kurang dari standar melalui fungsi auto-pad.</em></li>
            </ul>
        </li>

        <li>
            <h4>2. Klasifikasi Tipe & Grup Akun</h4>
            <p>
                Setiap akun wajib memiliki Tipe dan Grup yang jelas untuk menentukan posisinya di Laporan Keuangan.
            </p>
            <ul>
                <li><strong>Tipe Akun:</strong> Menentukan apakah akun masuk ke Neraca atau Laba Rugi (Asset, Liability, Equity, Revenue, COGS, Expense).</li>
                <li><strong>Grup Akun:</strong> Pengelompokan lebih detail untuk keperluan analisis laporan (misal: Kas & Bank, Piutang, Marketing).</li>
                <li><strong>Sistem Akumulasi:</strong> Seluruh saldo akun dihitung secara murni dari akumulasi transaksi yang tercatat di modul Jurnal Umum.</li>
            </ul>
        </li>

        <li>
            <h4>3. Manajemen Data & Pencarian</h4>
            <p>
                Fitur untuk memudahkan pengelolaan akun dalam jumlah besar dalam satu antarmuka.
            </p>
            <ul>
                <li>Pencarian fleksibel mendukung kata kunci berdasarkan Kode Akun, Nama Akun, maupun nama Grup.</li>
                <li>Filter kategori membantu menampilkan kelompok akun tertentu untuk mempercepat peninjauan.</li>
                <li>Opsi "Sembunyikan nonaktif" digunakan untuk membersihkan tampilan dari akun yang sudah tidak digunakan lagi.</li>
            </ul>
        </li>

        <li>
            <h4>4. Integrasi Transaksi & Status</h4>
            <p>
                COA berfungsi sebagai pondasi utama bagi seluruh modul keuangan di dalam sistem.
            </p>
            <ul>
                <li>Hanya akun dengan status <strong>Aktif</strong> yang akan muncul sebagai pilihan saat melakukan input Jurnal Umum.</li>
                <li>Status <strong>Nonaktif</strong> digunakan untuk mengarsipkan akun tanpa menghapus riwayat transaksi lama demi keamanan data audit.</li>
                <li>Perubahan nama atau grup melalui fitur "Update" akan langsung tercermin secara real-time pada laporan Buku Besar.</li>
            </ul>
        </li>

        <li>
            <h4>5. Keamanan & Validasi Data</h4>
            <p>
                Sistem memiliki proteksi otomatis untuk menjaga konsistensi database.
            </p>
            <ul>
                <li><strong>Anti-Duplikasi:</strong> Sistem akan menolak secara otomatis jika ada upaya mendaftarkan Kode Akun yang sudah terdaftar.</li>
                <li><strong>Audit Trail:</strong> Setiap akun mencatat waktu pembuatan secara otomatis untuk keperluan pelacakan data.</li>
            </ul>
        </li>
    </ul>

    <div class="readme-box">
        <strong>Penting:</strong> Karena sistem ini tidak menggunakan input saldo awal manual, pastikan saldo pembukaan (jika ada) dimasukkan melalui modul <strong>Jurnal Umum</strong> untuk menjaga integritas dan riwayat data yang akurat.
    </div>
</section>