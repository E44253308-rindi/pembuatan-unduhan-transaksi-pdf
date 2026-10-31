# KomikStore

Aplikasi penjualan buku komik berbasis Laravel 12 untuk admin dan kasir.

## Fitur

- Login dan logout dengan pembatasan akses role admin/kasir.
- CRUD komik, kategori, dan akun kasir.
- POS kasir dengan pencarian judul/barcode, pengelolaan keranjang, diskon, pajak, biaya lain, serta pembayaran tunai dan non-tunai.
- Transaksi dapat ditahan, dilanjutkan, dan dicetak sebagai struk browser atau printer thermal ESC/POS.
- Riwayat dan laporan penjualan.

## Menjalankan aplikasi

1. Atur koneksi database di `.env`, lalu pasang dependency:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan storage:link
   ```

   Buat atau perbarui akun admin dan kasir dengan aman menggunakan:

   ```powershell
   php artisan db:seed --class=UserSeeder
   ```

   Pada database kosong, jalankan `php artisan db:seed` untuk menambahkan akun dan data komik demo. Jangan jalankan seeder demo penuh pada database yang sudah memiliki data.

2. Jalankan aplikasi:

   ```powershell
   php artisan serve
   ```

3. Login menggunakan akun demo:

   | Role | Email | Password |
   | --- | --- | --- |
   | Admin | `admin@komik.test` | `admin1234` |
   | Kasir | `kasir@komik.test` | `admin1234` |

Ganti password akun demo sebelum aplikasi digunakan di lingkungan produksi.

## Barcode dan printer thermal

Admin dapat menyimpan barcode unik pada data komik. Di halaman POS, ketik judul atau scan barcode; scanner USB yang mengirimkan input seperti keyboard dapat langsung menambahkan komik saat tombol Enter dikirim.

Struk dapat dicetak dari browser tanpa printer khusus. Untuk mengaktifkan printer thermal yang kompatibel dengan ESC/POS, atur `.env` sesuai koneksi printer:

```dotenv
STORE_NAME="KomikStore"
STORE_ADDRESS=""
STORE_PHONE=""

# Untuk printer yang terpasang/dibagikan pada Windows:
ESCPOS_CONNECTION=windows
ESCPOS_PRINTER_NAME="POS-58"

# Atau untuk printer jaringan:
# ESCPOS_CONNECTION=network
# ESCPOS_PRINTER_HOST=192.168.1.100
# ESCPOS_PRINTER_PORT=9100
```

PHP harus berjalan pada komputer yang dapat menjangkau printer. Nama printer Windows harus sama dengan nama printer yang terdaftar di Windows. Setelah mengubah `.env`, jalankan `php artisan config:clear`.
