# Sistem Dispensasi SMALA

Aplikasi manajemen dispensasi (izin meninggalkan pelajaran) berbasis web untuk lingkungan SMA. Dibangun dengan **Laravel 10** (backend API) dan **Vue 3 + Vite** (frontend), dilengkapi fitur manajemen pengguna, multi-role, ekspor Excel/PDF, analitik, audit log, dan backup otomatis.

---

## Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Teknologi yang Digunakan](#2-teknologi-yang-digunakan)
3. [Persyaratan Perangkat Lunak](#3-persyaratan-perangkat-lunak)
4. [Instalasi Perangkat Lunak Prasyarat](#4-instalasi-perangkat-lunak-prasyarat)
   - [4.1 Visual Studio Code (sudah ada)](#41-visual-studio-code-sudah-ada)
   - [4.2 XAMPP](#42-xampp)
   - [4.3 Composer](#43-composer)
   - [4.4 Node.js](#44-nodejs)
   - [4.5 Git (Opsional)](#45-git-opsional)
5. [Menyiapkan Proyek](#5-menyiapkan-proyek)
   - [5.1 Letakkan Folder Proyek](#51-letakkan-folder-proyek)
   - [5.2 Buka di VS Code](#52-buka-di-vs-code)
6. [Setup Backend (Laravel)](#6-setup-backend-laravel)
   - [6.1 Jalankan XAMPP](#61-jalankan-xampp)
   - [6.2 Aktifkan Ekstensi PHP yang Diperlukan](#62-aktifkan-ekstensi-php-yang-diperlukan)
   - [6.3 Install Dependensi PHP](#63-install-dependensi-php)
   - [6.4 Buat File Konfigurasi .env](#64-buat-file-konfigurasi-env)
   - [6.5 Buat Database](#65-buat-database)
   - [6.6 Generate App Key](#66-generate-app-key)
   - [6.7 Jalankan Migrasi dan Seeder](#67-jalankan-migrasi-dan-seeder)
   - [6.8 Jalankan Server Backend](#68-jalankan-server-backend)
7. [Setup Frontend (Vue)](#7-setup-frontend-vue)
   - [7.1 Install Dependensi Node](#71-install-dependensi-node)
   - [7.2 Konfigurasi Environment Frontend (Opsional)](#72-konfigurasi-environment-frontend-opsional)
   - [7.3 Jalankan Server Frontend](#73-jalankan-server-frontend)
8. [Mengakses Aplikasi](#8-mengakses-aplikasi)
9. [Akun Bawaan (Default)](#9-akun-bawaan-default)
10. [Peran Pengguna (Role)](#10-peran-pengguna-role)
11. [Halaman dan Fitur Aplikasi](#11-halaman-dan-fitur-aplikasi)
12. [Struktur Folder Proyek](#12-struktur-folder-proyek)
13. [Referensi API Backend](#13-referensi-api-backend)
14. [Menghentikan dan Menjalankan Ulang](#14-menghentikan-dan-menjalankan-ulang)
15. [Troubleshooting (Masalah Umum)](#15-troubleshooting-masalah-umum)
16. [Ekstensi VS Code yang Disarankan](#16-ekstensi-vs-code-yang-disarankan)

---

## 1. Gambaran Umum Sistem

Sistem ini terdiri dari dua bagian yang bekerja bersama:

```
┌─────────────────────────────────────────────────────────┐
│                     Browser Pengguna                    │
│                  http://localhost:5173                  │
│                                                         │
│             Frontend — Vue 3 + Vite                     │
│         (tampilan web, form, grafik, dll.)              │
└───────────────────────┬─────────────────────────────────┘
                        │ HTTP Request (JSON)
                        ▼
┌─────────────────────────────────────────────────────────┐
│           Backend API — Laravel 10                      │
│                http://localhost:8000                    │
│                                                         │
│   (autentikasi, logika bisnis, database, ekspor)        │
└───────────────────────┬─────────────────────────────────┘
                        │ SQL Query
                        ▼
┌─────────────────────────────────────────────────────────┐
│              MySQL / MariaDB (via XAMPP)                │
│                   dispensasi_smala                      │
└─────────────────────────────────────────────────────────┘
```

**Backend** mengelola semua data dan logika bisnis — ia adalah API murni yang tidak menampilkan halaman.
**Frontend** adalah tampilan yang diakses pengguna di browser — ia mengambil dan mengirim data ke backend.

---

## 2. Teknologi yang Digunakan

| Komponen | Teknologi | Versi |
|---|---|---|
| Backend Framework | Laravel | 10.x |
| Bahasa Backend | PHP | 8.1 – 8.2 |
| Database | MySQL / MariaDB | via XAMPP |
| Autentikasi API | Laravel Sanctum | 3.x |
| Ekspor Excel | Maatwebsite Excel | 3.1 |
| Ekspor PDF | barryvdh/laravel-dompdf | 3.x |
| Backup Otomatis | spatie/laravel-backup | 8.x |
| Frontend Framework | Vue | 3.x |
| Build Tool Frontend | Vite | 7.x |
| CSS Framework | Tailwind CSS | 3.x |
| Grafik | Chart.js + vue-chartjs | 4.x |
| State Management | Pinia | 3.x |
| HTTP Client | Axios | 1.x |
| Router Frontend | Vue Router | 5.x |

---

## 3. Persyaratan Perangkat Lunak

Berikut adalah semua yang dibutuhkan sebelum memulai. Bagian selanjutnya menjelaskan cara menginstalnya satu per satu.

| Perangkat Lunak | Versi Minimal | Catatan |
|---|---|---|
| **VS Code** | Terbaru | Sudah ada — diasumsikan terinstal |
| **XAMPP** | 3.3 | Menyertakan PHP 8.2 dan MySQL |
| **Composer** | 2.x | Manajer paket PHP |
| **Node.js** | 20.19.0 atau 22.12.0+ | Untuk menjalankan frontend |

> **Catatan Penting:** XAMPP sudah menyertakan PHP 8.2 — kamu **tidak perlu** menginstal PHP secara terpisah. Namun Node.js dan Composer tetap harus diinstal sendiri.

---

## 4. Instalasi Perangkat Lunak Prasyarat

### 4.1 Visual Studio Code (sudah ada)

VS Code sudah terinstal. Tidak ada langkah tambahan.

---

### 4.2 XAMPP

XAMPP menyediakan PHP dan MySQL yang dibutuhkan oleh backend Laravel.

**Unduh dan Install:**

1. Buka browser, pergi ke: **https://www.apachefriends.org/download.html**
2. Klik **"XAMPP for Windows"** — pilih versi **8.2.x** (sudah include PHP 8.2)
3. Unduh file installer `.exe`
4. Jalankan installer, klik **Next** terus hingga selesai
   - Lokasi instalasi default: `C:\xampp` — **disarankan dibiarkan default**
   - Centang minimal: **Apache**, **MySQL**, **PHP**, **phpMyAdmin**
5. Klik **Finish** — XAMPP Control Panel akan terbuka

**Cek XAMPP terinstal:**
- Buka File Explorer, pastikan folder `C:\xampp\` ada
- Pastikan `C:\xampp\php\php.exe` ada

---

### 4.3 Composer

Composer adalah manajer paket untuk PHP — digunakan untuk mengunduh semua library Laravel.

**Unduh dan Install:**

1. Buka browser, pergi ke: **https://getcomposer.org/download/**
2. Klik **"Composer-Setup.exe"** untuk mengunduh
3. Jalankan file `Composer-Setup.exe`
4. Saat installer meminta **lokasi PHP**, klik Browse dan arahkan ke:
   ```
   C:\xampp\php\php.exe
   ```
5. Klik **Next** terus, lalu **Finish**

**Cek Composer terinstal:**
1. Tekan **Win + R**, ketik `cmd`, tekan Enter
2. Ketik perintah berikut:
   ```
   composer --version
   ```
3. Jika muncul `Composer version 2.x.x`, berarti **berhasil** ✓

> **Jika `composer` tidak dikenali:** Tutup CMD dan buka kembali. Jika masih gagal, restart komputer.

---

### 4.4 Node.js

Node.js dibutuhkan untuk menjalankan frontend Vue/Vite. XAMPP tidak menyertakannya.

**Unduh dan Install:**

1. Buka browser, pergi ke: **https://nodejs.org**
2. Klik tombol **"LTS"** — pilih versi **20.x** atau **22.x**
3. Unduh file installer `.msi` untuk Windows
4. Jalankan installer, klik **Next** terus
   - Pastikan centang **"Add to PATH"** jika ditanya — ini **wajib**
5. Klik **Install**, tunggu selesai, klik **Finish**

**Cek Node.js terinstal:**
1. Buka CMD baru (tutup yang lama jika ada)
2. Cek Node.js:
   ```
   node --version
   ```
   Harus muncul `v20.x.x` atau `v22.x.x` ✓
3. Cek npm:
   ```
   npm --version
   ```
   Harus muncul angka versi, contoh `10.x.x` ✓

> **Jika `node` tidak dikenali:** Tutup CMD dan buka kembali, atau restart komputer.

---

### 4.5 Git (Opsional)

Git dibutuhkan hanya jika kamu mengkloning repositori dari GitHub/GitLab. Jika proyek sudah ada dalam bentuk folder ZIP yang sudah diekstrak, langkah ini bisa dilewati.

1. Unduh dari: **https://git-scm.com/download/win**
2. Jalankan installer, klik **Next** terus dengan pengaturan default
3. Cek: buka CMD baru dan ketik `git --version`

---

## 5. Menyiapkan Proyek

### 5.1 Letakkan Folder Proyek

Folder proyek ini harus berada di dalam folder `htdocs` milik XAMPP agar dapat diakses.

**Jika kamu punya file ZIP:**
1. Ekstrak ZIP tersebut
2. Salin folder hasil ekstrak ke:
   ```
   C:\xampp\htdocs\SistemDispensasi\
   ```
   Sehingga struktur foldernya menjadi:
   ```
   C:\xampp\htdocs\SistemDispensasi\
   ├── backend\
   ├── frontend\
   └── README.md
   ```

**Jika menggunakan Git:**
1. Buka CMD
2. Masuk ke folder htdocs:
   ```
   cd C:\xampp\htdocs
   ```
3. Clone repositori:
   ```
   git clone <URL_REPOSITORI> SistemDispensasi
   ```

---

### 5.2 Buka di VS Code

1. Buka **VS Code**
2. Klik menu **File → Open Folder...**
3. Navigasi ke `C:\xampp\htdocs\SistemDispensasi`
4. Klik **Select Folder**

Sekarang seluruh proyek (backend dan frontend) terbuka dalam satu workspace VS Code.

---

## 6. Setup Backend (Laravel)

> Semua perintah di bagian ini dijalankan **di dalam folder `backend`**.
> Kamu bisa menggunakan **Terminal bawaan VS Code**: tekan `` Ctrl + ` `` untuk membukanya.

---

### 6.1 Jalankan XAMPP

Sebelum melakukan apapun dengan database, XAMPP harus berjalan.

1. Buka **XAMPP Control Panel** (dari Start Menu atau ikon di desktop)
2. Klik **Start** di baris **Apache**
3. Klik **Start** di baris **MySQL**
4. Pastikan keduanya berstatus **hijau (Running)**

> Biarkan XAMPP Control Panel tetap terbuka selama menggunakan aplikasi.

---

### 6.2 Aktifkan Ekstensi PHP yang Diperlukan

Beberapa ekstensi PHP mungkin belum aktif secara default di XAMPP. Lakukan langkah ini **sebelum** menjalankan `composer install`.

1. Di **XAMPP Control Panel**, klik tombol **Config** di baris Apache
2. Pilih **PHP (php.ini)** — file ini akan terbuka di Notepad
3. Tekan **Ctrl + H** untuk membuka Replace, lalu aktifkan ekstensi berikut dengan menghapus titik koma (`;`) di awalnya:

   Cari dan ganti:
   ```
   ;extension=zip
   ```
   Dengan:
   ```
   extension=zip
   ```

   Ulangi untuk baris-baris berikut:
   ```
   ;extension=openssl       →  extension=openssl
   ;extension=gd            →  extension=gd
   ;extension=mbstring      →  extension=mbstring
   ;extension=fileinfo      →  extension=fileinfo
   ;extension=pdo_mysql     →  extension=pdo_mysql
   ;extension=exif          →  extension=exif
   ;extension=intl          →  extension=intl
   ```

4. Simpan file `php.ini` (**Ctrl + S**)
5. Kembali ke XAMPP Control Panel, klik **Stop** lalu **Start** ulang Apache

---

### 6.3 Install Dependensi PHP

Langkah ini mengunduh semua library yang dibutuhkan backend. **Diperlukan koneksi internet (hanya satu kali).**

1. Di VS Code, buka Terminal (`` Ctrl + ` ``)
2. Masuk ke folder backend:
   ```
   cd backend
   ```
3. Jalankan:
   ```
   composer install
   ```
4. Tunggu hingga selesai. Proses bisa memakan **3–10 menit** tergantung kecepatan internet.
5. Jika berhasil, akan muncul tulisan:
   ```
   Generating optimized autoload files
   ```

---

### 6.4 Buat File Konfigurasi .env

File `.env` berisi konfigurasi database, URL aplikasi, dan pengaturan lainnya.

1. Pastikan kamu masih di folder `backend` di Terminal
2. Jalankan:
   ```
   copy .env.example .env
   ```
3. Buka file `backend\.env` yang baru dibuat di VS Code
4. Sesuaikan bagian berikut:

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
   > - `DB_DATABASE` → nama database yang akan dibuat di langkah berikutnya (bisa diganti, tapi harus konsisten)
   > - `DB_USERNAME` → default XAMPP adalah `root`
   > - `DB_PASSWORD` → kosongkan jika kamu tidak mengatur password MySQL di XAMPP

5. Simpan file `.env`

---

### 6.5 Buat Database

1. Buka browser, masuk ke: **http://localhost/phpmyadmin**
2. Di panel kiri, klik **"New"** (atau **"Baru"**)
3. Di kolom **"Database name"**, ketik:
   ```
   dispensasi_smala
   ```
   (harus sama persis dengan nilai `DB_DATABASE` di file `.env`)
4. Biarkan collation default, klik **"Create"**

Database kosong sudah siap. Tabel-tabelnya akan dibuat otomatis di langkah berikutnya.

---

### 6.6 Generate App Key

App Key adalah kunci enkripsi unik untuk aplikasi. Wajib dilakukan sekali.

Di Terminal VS Code (pastikan di folder `backend`), jalankan:
```
php artisan key:generate
```

Jika berhasil, file `.env` akan diperbarui otomatis dengan nilai `APP_KEY=base64:...`

---

### 6.7 Jalankan Migrasi dan Seeder

Langkah ini membuat semua tabel di database dan mengisi data awal (termasuk akun pengguna untuk login pertama kali).

**Buat semua tabel:**
```
php artisan migrate
```
Jika ada pertanyaan konfirmasi, ketik `yes` dan tekan Enter.

**Isi data awal:**
```
php artisan db:seed
```

Jika berhasil akan muncul:
```
Database seeding completed successfully.
```

> **Catatan:** Kamu juga bisa menggabungkan keduanya dengan satu perintah:
> ```
> php artisan migrate --seed
> ```

---

### 6.8 Jalankan Server Backend

Di Terminal VS Code (masih di folder `backend`), jalankan:
```
php artisan serve
```

Jika berhasil akan muncul:
```
INFO  Server running on [http://127.0.0.1:8000].
```

**Verifikasi backend berjalan:** Buka browser dan akses:
```
http://localhost:8000/api/test
```
Harus muncul respons JSON: `"API Laravel berjalan dengan baik!"` ✓

> **Penting:** Jangan tutup Terminal ini selama menggunakan aplikasi. Server akan berhenti jika Terminal ditutup.

---

## 7. Setup Frontend (Vue)

> Buka **Terminal baru** di VS Code untuk frontend (klik ikon **+** di panel Terminal).
> Perintah dijalankan **di dalam folder `frontend`**.

---

### 7.1 Install Dependensi Node

Langkah ini mengunduh semua paket JavaScript. **Diperlukan koneksi internet (hanya satu kali).**

1. Di Terminal baru, masuk ke folder frontend:
   ```
   cd frontend
   ```
2. Jalankan:
   ```
   npm install
   ```
3. Tunggu hingga selesai. Proses bisa memakan **2–5 menit**.
4. Jika berhasil, folder `node_modules` akan terbuat di dalam `frontend\`

---

### 7.2 Konfigurasi Environment Frontend (Opsional)

Secara default, frontend sudah dikonfigurasi untuk terhubung ke backend di `http://127.0.0.1:8000`. Langkah ini hanya diperlukan jika kamu menggunakan port atau host yang berbeda.

1. Buat file baru bernama `.env` di dalam folder `frontend\`
2. Isi dengan:
   ```
   VITE_API_BASE_URL=http://127.0.0.1:8000/api
   ```
3. Simpan file

> Jika tidak membuat file ini, aplikasi akan tetap berjalan dengan nilai default yang sudah benar.

---

### 7.3 Jalankan Server Frontend

Di Terminal (pastikan di folder `frontend`), jalankan:
```
npm run dev
```

Jika berhasil akan muncul:
```
  VITE v7.x.x  ready in xxx ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
```

> **Penting:** Jangan tutup Terminal ini. Kamu sekarang punya **dua Terminal yang harus tetap terbuka**: satu untuk backend (`php artisan serve`) dan satu untuk frontend (`npm run dev`).

---

## 8. Mengakses Aplikasi

Setelah kedua server berjalan:

| Alamat | Keterangan |
|---|---|
| **http://localhost:5173** | Aplikasi web (frontend) — buka ini di browser |
| http://localhost:8000/api | Backend API |
| http://localhost/phpmyadmin | Manajemen database |

1. Buka browser (Chrome / Edge / Firefox)
2. Pergi ke **http://localhost:5173**
3. Kamu akan diarahkan ke halaman **Login**
4. Gunakan salah satu akun dari tabel di bawah untuk masuk

---

## 9. Akun Bawaan (Default)

Setelah menjalankan seeder (langkah 6.7), akun-akun berikut tersedia:

| Nama | Email | Password | Role |
|---|---|---|---|
| Admin System | `admin@sma.com` | `password` | Admin |
| Staff Kesiswaan | `kesiswaan@sma.com` | `password` | Kesiswaan |
| Budi Santoso | `budi@sma.com` | `password` | Guru Mapel + Admin |
| Siti Aminah | `siti@sma.com` | `password` | Guru Mapel + Kesiswaan |
| Ahmad Hidayat | `ahmad@sma.com` | `password` | Guru Mapel |
| Andi Pratama | `siswa@sma.com` | `password` | Siswa |

> **Disarankan:** Login pertama kali dengan akun `admin@sma.com` untuk menjelajahi semua fitur.

---

## 10. Peran Pengguna (Role)

| Role | Hak Akses |
|---|---|
| **Admin** | Akses penuh — manajemen pengguna, audit log, backup, semua fitur |
| **Kesiswaan** | Mengelola dan memproses pengajuan dispensasi seluruh siswa |
| **Guru Mapel** | Menyetujui atau menolak dispensasi untuk mata pelajarannya |
| **Siswa** | Mengajukan permohonan dispensasi, melihat status pengajuan sendiri |

Satu pengguna bisa memiliki lebih dari satu role (contoh: Budi Santoso memiliki role Guru Mapel dan Admin sekaligus).

---

## 11. Halaman dan Fitur Aplikasi

| Halaman | URL | Role yang Bisa Akses |
|---|---|---|
| Login | `/login` | Semua |
| Register | `/register` | Semua (belum login) |
| Dashboard | `/dashboard` | Semua |
| Daftar Dispensasi | `/dispensasi` | Semua (data disesuaikan role) |
| Ajukan Dispensasi | `/dispensasi/create` | Siswa |
| Detail Dispensasi | `/dispensasi/:id` | Semua |
| Edit Dispensasi | `/dispensasi/:id/edit` | Siswa (milik sendiri), Admin |
| Manajemen Pengguna | `/users` | Admin |
| Tambah Pengguna | `/users/create` | Admin |
| Analitik & Statistik | `/analytics` | Admin, Kesiswaan |
| Profil Akun | `/profile` | Semua |
| Audit Log | `/audit-logs` | Admin |
| Manajemen Backup | `/backups` | Admin |

**Fitur Utama:**
- Pengajuan dispensasi dengan upload dokumen pendukung
- Alur persetujuan: Guru Mapel → Kesiswaan
- Ekspor data ke **Excel** dan **PDF**
- Grafik dan statistik dispensasi (Chart.js)
- Rate limiting untuk proteksi dari spam dan brute force
- Audit log — mencatat semua aktivitas penting (siapa, apa, kapan, dari mana)
- Backup otomatis database tiap 6 bulan + weekly safety net

---

## 12. Struktur Folder Proyek

```
SistemDispensasi/
├── README.md                  ← File ini
├── .gitignore
│
├── backend/                   ← Laravel 10 (API)
│   ├── app/
│   │   ├── Console/           ← Scheduled commands (backup, dll.)
│   │   ├── Exports/           ← Kelas ekspor Excel
│   │   ├── Http/
│   │   │   ├── Controllers/   ← Logika request/response API
│   │   │   ├── Middleware/    ← Auth, CORS, Rate Limiting
│   │   │   └── Requests/      ← Validasi input
│   │   ├── Models/            ← Model database (User, Dispensasi, dll.)
│   │   └── Providers/
│   ├── config/                ← Konfigurasi Laravel
│   ├── database/
│   │   ├── migrations/        ← Definisi struktur tabel
│   │   └── seeders/           ← Data awal (akun default, dll.)
│   ├── public/                ← Entry point web server
│   ├── resources/views/       ← Template Blade (untuk PDF)
│   ├── routes/
│   │   ├── api.php            ← Definisi semua endpoint API
│   │   └── web.php
│   ├── storage/               ← File upload, cache, log
│   ├── .env.example           ← Template konfigurasi
│   └── composer.json          ← Dependensi PHP
│
└── frontend/                  ← Vue 3 + Vite (UI)
    ├── src/
    │   ├── assets/            ← CSS global, gambar
    │   ├── components/        ← Komponen UI yang dapat digunakan ulang
    │   ├── composables/       ← Logika reusable (hooks)
    │   ├── router/            ← Konfigurasi rute URL
    │   ├── services/
    │   │   └── api.js         ← Konfigurasi Axios / koneksi ke backend
    │   ├── stores/            ← State management (Pinia)
    │   ├── utils/             ← Fungsi helper
    │   ├── views/             ← Halaman-halaman utama aplikasi
    │   ├── App.vue            ← Komponen root
    │   └── main.js            ← Entry point aplikasi
    ├── public/                ← File statis (favicon, dll.)
    ├── .env                   ← Konfigurasi (buat manual jika perlu)
    ├── package.json           ← Dependensi JavaScript
    ├── vite.config.js         ← Konfigurasi Vite
    └── tailwind.config.js     ← Konfigurasi Tailwind CSS
```

---

## 13. Referensi API Backend

Base URL: `http://localhost:8000/api`

### Endpoint Publik (tanpa login)

| Method | Endpoint | Keterangan |
|---|---|---|
| `GET` | `/test` | Cek apakah API berjalan |
| `POST` | `/login` | Login, mendapatkan token |
| `POST` | `/register` | Daftar akun baru |
| `GET` | `/kelas` | Daftar semua kelas |

### Endpoint yang Memerlukan Login

Setelah login, sertakan header berikut di setiap request:
```
Authorization: Bearer {token}
```

| Method | Endpoint | Keterangan |
|---|---|---|
| `POST` | `/logout` | Logout |
| `GET` | `/profile` | Lihat profil sendiri |
| `PUT` | `/profile` | Perbarui profil |
| `GET` | `/dispensasi` | Daftar dispensasi |
| `POST` | `/dispensasi` | Buat dispensasi baru |
| `GET` | `/dispensasi/{id}` | Detail dispensasi |
| `PUT` | `/dispensasi/{id}` | Edit dispensasi |
| `DELETE` | `/dispensasi/{id}` | Hapus dispensasi |
| `PUT` | `/dispensasi/{id}/status` | Setujui / tolak dispensasi |
| `GET` | `/users` | Daftar pengguna *(Admin)* |
| `POST` | `/users` | Tambah pengguna *(Admin)* |
| `PUT` | `/users/{id}` | Edit pengguna *(Admin)* |
| `DELETE` | `/users/{id}` | Hapus pengguna *(Admin)* |
| `GET` | `/export/excel` | Ekspor data ke Excel |
| `GET` | `/export/pdf` | Ekspor data ke PDF |
| `GET` | `/export/csv` | Ekspor data ke CSV |
| `GET` | `/analytics/summary` | Ringkasan statistik |
| `GET` | `/analytics/monthly` | Data bulanan |
| `GET` | `/audit-logs` | Log aktivitas *(Admin)* |
| `GET` | `/backups` | Daftar backup *(Admin)* |
| `POST` | `/backups` | Buat backup manual *(Admin)* |
| `DELETE` | `/backups/{filename}` | Hapus backup *(Admin)* |

### Rate Limiting

| Endpoint | Batas |
|---|---|
| Login / Register | 5 request / menit |
| API umum | 100 request / menit |
| Export / Download | 10 request / menit |
| Hapus Pengguna | 20 request / menit |

---

## 14. Menghentikan dan Menjalankan Ulang

### Menghentikan Server

- **Backend:** Di Terminal yang menjalankan `php artisan serve`, tekan **Ctrl + C**
- **Frontend:** Di Terminal yang menjalankan `npm run dev`, tekan **Ctrl + C**
- **XAMPP:** Di XAMPP Control Panel, klik **Stop** untuk Apache dan MySQL

### Menjalankan Ulang (lain kali / setelah restart komputer)

Kamu tidak perlu mengulang proses setup dari awal. Cukup lakukan ini setiap kali ingin menggunakan aplikasi:

1. Buka **XAMPP Control Panel** → Start **Apache** dan **MySQL**
2. Buka VS Code → buka Terminal → masuk ke `backend`:
   ```
   cd backend
   php artisan serve
   ```
3. Buka Terminal baru → masuk ke `frontend`:
   ```
   cd frontend
   npm run dev
   ```
4. Buka browser → akses **http://localhost:5173**

---

## 15. Troubleshooting (Masalah Umum)

### ❌ `composer` tidak dikenali di Terminal

**Penyebab:** Composer belum terinstall atau PATH belum diperbarui.
**Solusi:** Tutup VS Code dan buka kembali setelah menginstal Composer. Jika masih gagal, restart komputer.

---

### ❌ `php` tidak dikenali di Terminal

**Penyebab:** PHP dari XAMPP belum terdaftar di PATH sistem.
**Solusi:**
1. Composer biasanya otomatis mendaftarkan PHP ke PATH saat instalasi
2. Jika masih gagal, tambahkan `C:\xampp\php` ke **System Environment Variables → PATH** secara manual:
   - Tekan **Win + R** → ketik `sysdm.cpl` → Enter
   - Klik tab **Advanced** → **Environment Variables**
   - Di bagian **System variables**, cari dan klik **Path** → klik **Edit**
   - Klik **New** → ketik `C:\xampp\php` → klik OK

---

### ❌ `node` atau `npm` tidak dikenali

**Penyebab:** Node.js belum terinstal atau PATH belum diperbarui.
**Solusi:** Tutup VS Code dan buka kembali. Jika masih gagal, reinstall Node.js dan pastikan centang **"Add to PATH"**.

---

### ❌ Error koneksi database (SQLSTATE / Connection refused)

**Penyebab:** MySQL belum berjalan atau konfigurasi `.env` salah.
**Solusi:**
1. Pastikan MySQL berstatus **Running** (hijau) di XAMPP Control Panel
2. Cek nilai `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `backend\.env`
3. Pastikan database `dispensasi_smala` sudah dibuat di phpMyAdmin

---

### ❌ Error saat `composer install` (openssl / zip / extension)

**Penyebab:** Ekstensi PHP belum diaktifkan.
**Solusi:** Lakukan langkah **6.2** (aktifkan ekstensi di `php.ini`) lalu jalankan ulang `composer install`.

---

### ❌ Error `APP_KEY` atau halaman error saat pertama buka backend

**Penyebab:** App key belum digenerate.
**Solusi:**
```
php artisan key:generate
```

---

### ❌ Error `EADDRINUSE` saat `npm run dev`

**Penyebab:** Port 5173 sudah dipakai proses lain.
**Solusi:** Restart komputer, atau jalankan perintah berikut di CMD untuk menutup proses di port tersebut:
```
netstat -ano | findstr :5173
taskkill /PID <nomor_PID> /F
```

---

### ❌ Halaman web muncul tapi tidak bisa login / data tidak tampil

**Penyebab:** Backend tidak berjalan atau frontend tidak terhubung ke backend.
**Solusi:**
1. Pastikan `php artisan serve` masih berjalan di Terminal
2. Buka browser, akses `http://127.0.0.1:8000/api/test` — harus muncul JSON
3. Pastikan tidak ada pesan error di Terminal backend

---

### ❌ MySQL tidak mau menyala di XAMPP

**Penyebab:** Port 3306 sudah dipakai oleh MySQL lain yang terinstal terpisah.
**Solusi:**
1. Di XAMPP Control Panel → klik **Logs** di baris MySQL untuk melihat error
2. Buka **Task Manager** → cari proses `mysqld.exe` yang lain → End Task
3. Atau ubah port MySQL XAMPP menjadi `3307` di `C:\xampp\mysql\bin\my.ini` dan sesuaikan `DB_PORT` di `.env`

---

### ❌ Reset database (ingin mengulang dari awal)

> ⚠️ **Peringatan:** Semua data akan terhapus permanen.

```
php artisan migrate:fresh --seed
```

---

### ❌ Error `CORS` — blocked by browser

**Penyebab:** Konfigurasi CORS backend tidak mengizinkan permintaan dari frontend.
**Solusi:** Pastikan `APP_URL` di `backend\.env` sudah benar (`http://localhost:8000`), dan file `config/cors.php` memiliki `allowed_origins` yang mencakup `http://localhost:5173`.

---

## 16. Ekstensi VS Code yang Disarankan

Instal ekstensi berikut di VS Code untuk pengalaman pengembangan yang lebih baik:

**Untuk Backend (PHP / Laravel):**
- **PHP Intelephense** (`bmewburn.vscode-intelephense-client`) — autocomplete dan analisis PHP
- **Laravel Artisan** (`ryannaddy.laravel-artisan`) — jalankan perintah artisan dari VS Code
- **Laravel Blade Snippets** (`onecentlin.laravel-blade`) — syntax highlight untuk Blade template

**Untuk Frontend (Vue / JS):**
- **Vue - Official** (`Vue.volar`) — dukungan Vue 3, syntax highlight, IntelliSense
- **Tailwind CSS IntelliSense** (`bradlc.vscode-tailwindcss`) — autocomplete class Tailwind
- **ESLint** (`dbaeumer.vscode-eslint`) — linting JavaScript

**Umum:**
- **GitLens** (`eamodio.gitlens`) — fitur Git yang lebih lengkap
- **Thunder Client** (`rangav.vscode-thunder-client`) — test API langsung dari VS Code (pengganti Postman)
- **DotENV** (`mikestead.dotenv`) — syntax highlight untuk file `.env`

---

> Dibuat untuk SMALA. Jika mengalami kendala yang tidak tercakup di sini, buka file log di `backend/storage/logs/laravel.log` untuk melihat pesan error detail dari backend.
