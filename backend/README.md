# Sistem Dispensasi SMALA — Backend API

Sistem backend berbasis **Laravel 10** untuk mengelola pengajuan dan persetujuan dispensasi (izin meninggalkan pelajaran) di lingkungan SMA. Dilengkapi fitur manajemen pengguna, ekspor data ke Excel/PDF, analitik, dan audit log.

---

## Daftar Isi

1. [Persyaratan](#1-persyaratan)
2. [Persiapan Awal — Install Composer](#2-persiapan-awal--install-composer)
3. [Menjalankan XAMPP](#3-menjalankan-xampp)
4. [Menyalin & Menyiapkan Proyek](#4-menyalin--menyiapkan-proyek)
5. [Mengatur File Konfigurasi (.env)](#5-mengatur-file-konfigurasi-env)
6. [Membuat Database](#6-membuat-database)
7. [Install Dependensi PHP (Composer)](#7-install-dependensi-php-composer)
8. [Menjalankan Migrasi & Seeder](#8-menjalankan-migrasi--seeder)
9. [Menjalankan Server Aplikasi](#9-menjalankan-server-aplikasi)
10. [Akun Bawaan (Default)](#10-akun-bawaan-default)
11. [Endpoint API](#11-endpoint-api)
12. [Menghentikan Server](#12-menghentikan-server)
13. [Troubleshooting (Masalah Umum)](#13-troubleshooting-masalah-umum)

---

## 1. Persyaratan

Sebelum memulai, pastikan kamu sudah punya:

| Kebutuhan | Keterangan |
|---|---|
| **XAMPP 3.3** | Sudah include PHP 8.2 dan MySQL/MariaDB |
| **Composer** | Tool untuk mengunduh library PHP (gratis, lihat langkah 2) |
| **Koneksi Internet** | Diperlukan satu kali saat mengunduh library |

> **Catatan:** XAMPP 3.3 sudah menyertakan PHP 8.2, jadi kamu **tidak perlu** menginstall PHP secara terpisah.

---

## 2. Persiapan Awal — Install Composer

**Composer** adalah tool yang digunakan untuk mengunduh semua library yang dibutuhkan proyek ini. Ikuti langkah berikut:

1. Buka browser dan pergi ke **[https://getcomposer.org/download/](https://getcomposer.org/download/)**
2. Klik tombol **"Composer-Setup.exe"** untuk mengunduh installer
3. Jalankan file yang sudah diunduh (`Composer-Setup.exe`)
4. Saat installer meminta lokasi PHP, arahkan ke:
   ```
   C:\xampp\php\php.exe
   ```
5. Klik **Next** terus hingga selesai, lalu klik **Finish**

**Cek apakah Composer sudah terinstall:**
1. Buka **Command Prompt** (tekan tombol Windows → ketik `cmd` → Enter)
2. Ketik perintah berikut lalu tekan Enter:
   ```
   composer --version
   ```
3. Jika muncul tulisan seperti `Composer version 2.x.x`, berarti **berhasil** ✓

---

## 3. Menjalankan XAMPP

1. Buka **XAMPP Control Panel** (dari menu Start atau ikon di desktop)
2. Klik tombol **Start** di sebelah **Apache**
3. Klik tombol **Start** di sebelah **MySQL**
4. Pastikan keduanya berubah menjadi hijau (status: **Running**)

---

## 4. Menyalin & Menyiapkan Proyek

Pastikan folder proyek ini sudah berada di dalam folder `htdocs` milik XAMPP. Lokasi default-nya adalah:

```
C:\xampp\htdocs\SistemDispensasiSMALA-back\
```

Jika belum ada di sana, salin seluruh folder proyek ini ke lokasi tersebut.

---

## 5. Mengatur File Konfigurasi (.env)

File `.env` adalah file konfigurasi utama aplikasi (berisi pengaturan database, URL, dsb).

**Langkah-langkahnya:**

1. Buka folder proyek di File Explorer:
   ```
   C:\xampp\htdocs\SistemDispensasiSMALA-back\
   ```
2. Cari file bernama **`.env.example`**
3. Salin file tersebut dan ganti namanya menjadi **`.env`**
   - Klik kanan → Copy → Paste → Rename menjadi `.env`
   - Atau melalui Command Prompt, masuk ke folder proyek lalu jalankan:
     ```
     copy .env.example .env
     ```

4. Buka file `.env` menggunakan **Notepad** atau editor teks lainnya
5. Cari dan ubah bagian berikut sesuai kebutuhanmu:

   ```env
   APP_NAME=SistemDispensasiSMALA
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dispensasi_smala
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   > **Penjelasan:**
   > - `DB_DATABASE` → nama database yang akan dibuat (bebas, tapi harus sama dengan yang dibuat di langkah 6)
   > - `DB_USERNAME` → secara default XAMPP menggunakan `root`
   > - `DB_PASSWORD` → kosongkan saja jika kamu tidak mengatur password MySQL di XAMPP

6. Simpan file `.env`

---

## 6. Membuat Database

1. Buka browser dan masuk ke **phpMyAdmin** di alamat: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Di menu sebelah kiri, klik **"New"** (Baru)
3. Pada kolom **"Database name"**, ketik nama database yang sama dengan yang kamu tulis di `.env`, contoh:
   ```
   dispensasi_smala
   ```
4. Biarkan pilihan kolasi (collation) tetap default, lalu klik **"Create"**

Database kosong sudah siap. Tabel-tabelnya akan dibuat otomatis di langkah berikutnya.

---

## 7. Install Dependensi PHP (Composer)

Langkah ini mengunduh semua library yang dibutuhkan proyek. **Diperlukan koneksi internet.**

1. Buka **Command Prompt**
2. Masuk ke folder proyek dengan perintah:
   ```
   cd C:\xampp\htdocs\SistemDispensasiSMALA-back
   ```
3. Jalankan perintah berikut:
   ```
   composer install
   ```
4. Tunggu prosesnya selesai. Akan ada banyak teks yang berjalan — itu normal. Jika muncul tulisan **"Generating optimized autoload files"** di akhir, berarti **berhasil** ✓

> Proses ini mungkin memakan waktu beberapa menit tergantung kecepatan internet.

---

## 8. Menjalankan Migrasi & Seeder

Langkah ini membuat semua tabel di database dan mengisi data awal (data dummy untuk mencoba aplikasi).

**Pastikan Command Prompt masih di folder proyek**, lalu jalankan satu per satu:

**a. Generate App Key** (kunci enkripsi aplikasi — wajib dilakukan sekali):
```
php artisan key:generate
```

**b. Buat tabel di database:**
```
php artisan migrate
```
Jika ada pertanyaan konfirmasi, ketik `yes` lalu Enter.

**c. Isi data awal:**
```
php artisan db:seed
```

Jika semua berjalan lancar, akan muncul tulisan seperti `Database seeding completed successfully.` ✓

---

## 9. Menjalankan Server Aplikasi

Jalankan perintah berikut di Command Prompt (pastikan masih di folder proyek):

```
php artisan serve
```

Akan muncul pesan seperti ini:
```
INFO  Server running on [http://127.0.0.1:8000].
```

**Aplikasi sudah berjalan!** Kamu bisa mengaksesnya di browser atau API client (seperti Postman) melalui alamat:

```
http://localhost:8000
```

**Test cepat** — buka browser dan akses:
```
http://localhost:8000/api/test
```
Jika muncul respons JSON `"API Laravel berjalan dengan baik!"`, berarti semuanya **sudah benar** ✓

> **Penting:** Jangan tutup jendela Command Prompt selama aplikasi dipakai. Menutupnya akan menghentikan server.

---

## 10. Akun Bawaan (Default)

Setelah menjalankan seeder (langkah 8c), akun-akun berikut sudah tersedia untuk login:

| Nama | Email | Password | Role |
|---|---|---|---|
| Admin System | `admin@sma.com` | `password` | Admin |
| Staff Kesiswaan | `kesiswaan@sma.com` | `password` | Kesiswaan |
| Budi Santoso | `budi@sma.com` | `password` | Guru Mapel + Admin |
| Siti Aminah | `siti@sma.com` | `password` | Guru Mapel + Kesiswaan |
| Ahmad Hidayat | `ahmad@sma.com` | `password` | Guru Mapel |
| Andi Pratama | `siswa@sma.com` | `password` | Siswa |

> **Penjelasan Role:**
> - **Admin** → akses penuh ke semua fitur dan manajemen pengguna
> - **Kesiswaan** → mengelola data dispensasi siswa
> - **Guru Mapel** → bisa menyetujui/menolak dispensasi untuk mata pelajarannya
> - **Siswa** → mengajukan permohonan dispensasi

---

## 11. Endpoint API

Base URL: `http://localhost:8000/api`

### Publik (tanpa login)
| Method | Endpoint | Keterangan |
|---|---|---|
| `GET` | `/test` | Cek apakah API berjalan |
| `POST` | `/login` | Login pengguna |
| `POST` | `/register` | Daftar akun baru |
| `GET` | `/kelas` | Daftar semua kelas |

### Memerlukan Login (butuh token)
Setelah login, tambahkan header `Authorization: Bearer {token}` di setiap request.

| Method | Endpoint | Keterangan |
|---|---|---|
| `POST` | `/logout` | Logout |
| `GET` | `/profile` | Lihat profil sendiri |
| `GET` | `/dispensasi` | Daftar pengajuan dispensasi |
| `POST` | `/dispensasi` | Buat pengajuan dispensasi baru |
| `GET` | `/dispensasi/{id}` | Detail dispensasi |
| `PUT` | `/dispensasi/{id}/status` | Setujui / tolak dispensasi |
| `GET` | `/users` | Daftar pengguna (Admin) |
| `GET` | `/export/excel` | Ekspor data ke Excel |
| `GET` | `/export/pdf` | Ekspor data ke PDF |
| `GET` | `/analytics/summary` | Ringkasan statistik |
| `GET` | `/audit-logs` | Log aktivitas sistem |

---

## 12. Menghentikan Server

Untuk menghentikan server aplikasi, cukup tekan **`Ctrl + C`** di jendela Command Prompt tempat `php artisan serve` berjalan.

Untuk menjalankannya lagi di lain waktu, ulangi langkah 3 (jalankan XAMPP) dan langkah 9 (jalankan server) saja — tidak perlu mengulang semua langkah dari awal.

---

## 13. Troubleshooting (Masalah Umum)

### ❌ "composer" tidak dikenali
**Penyebab:** Composer belum terinstall atau PATH belum dikenali.
**Solusi:** Ulangi langkah 2. Tutup dan buka kembali Command Prompt setelah instalasi.

---

### ❌ Error "php" tidak dikenali di Command Prompt
**Penyebab:** PHP dari XAMPP belum terdaftar di PATH sistem.
**Solusi:**
1. Saat instalasi Composer (langkah 2), Composer biasanya otomatis mendaftarkan PHP ke PATH
2. Jika masih error, buka Command Prompt dan jalankan langsung dengan path lengkap:
   ```
   C:\xampp\php\php artisan serve
   ```
   Atau tambahkan `C:\xampp\php` ke **Environment Variables > PATH** secara manual.

---

### ❌ Error koneksi database (SQLSTATE/Connection refused)
**Penyebab:** MySQL di XAMPP belum berjalan, atau konfigurasi `.env` salah.
**Solusi:**
1. Pastikan MySQL sudah **Start** di XAMPP Control Panel (berwarna hijau)
2. Cek kembali pengaturan `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di file `.env`
3. Pastikan nama database di `.env` sudah sama persis dengan yang dibuat di phpMyAdmin

---

### ❌ Error saat `composer install` (openssl / zip / extension)
**Penyebab:** Ekstensi PHP tertentu belum diaktifkan di XAMPP.
**Solusi:**
1. Buka **XAMPP Control Panel**
2. Klik **Config** di sebelah Apache → pilih **PHP (php.ini)**
3. Cari baris berikut dan hapus tanda titik koma (`;`) di awal setiap baris:
   ```
   ;extension=zip
   ;extension=openssl
   ;extension=gd
   ;extension=mbstring
   ;extension=fileinfo
   ;extension=pdo_mysql
   ;extension=exif
   ```
   Ubah menjadi:
   ```
   extension=zip
   extension=openssl
   extension=gd
   extension=mbstring
   extension=fileinfo
   extension=pdo_mysql
   extension=exif
   ```
4. Simpan file, lalu **Stop** dan **Start** ulang Apache di XAMPP
5. Jalankan kembali `composer install`

---

### ❌ Error "APP_KEY" atau halaman error saat pertama buka
**Penyebab:** App key belum digenerate.
**Solusi:** Jalankan perintah berikut di Command Prompt:
```
php artisan key:generate
```

---

### ❌ Error 404 / "Route not found" saat akses API
**Penyebab:** Server belum berjalan atau URL salah.
**Solusi:**
1. Pastikan `php artisan serve` sudah dijalankan dan jendela Command Prompt tidak ditutup
2. Pastikan mengakses `http://localhost:8000/api/...` (bukan `http://localhost/...`)

---

### ❌ Ingin mengulang dari awal (reset database)
Jalankan perintah berikut untuk menghapus semua tabel dan mengisi ulang data awal:
```
php artisan migrate:fresh --seed
```
> **Peringatan:** Semua data yang ada di database akan **terhapus permanen**.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
