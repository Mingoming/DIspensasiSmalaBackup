# Sistem Dispensasi SMALA — Frontend

Aplikasi web untuk pengelolaan dispensasi siswa di sekolah, dibangun dengan **Vue 3**, **Vite**, dan **Tailwind CSS**.

---

## Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Yang Kamu Butuhkan Sebelum Mulai](#yang-kamu-butuhkan-sebelum-mulai)
3. [Langkah 1 — Instal Node.js](#langkah-1--instal-nodejs)
4. [Langkah 2 — Siapkan Backend (Laravel)](#langkah-2--siapkan-backend-laravel)
5. [Langkah 3 — Jalankan Proyek Frontend Ini](#langkah-3--jalankan-proyek-frontend-ini)
6. [Mengakses Aplikasi di Browser](#mengakses-aplikasi-di-browser)
7. [Fitur-Fitur Aplikasi](#fitur-fitur-aplikasi)
8. [Struktur Folder](#struktur-folder)
9. [Pertanyaan Umum / Troubleshooting](#pertanyaan-umum--troubleshooting)

---

## Gambaran Umum

Ini adalah bagian **frontend** (tampilan web) dari Sistem Dispensasi SMALA. Aplikasi ini terhubung ke sebuah **backend API berbasis Laravel** yang berjalan di komputer yang sama.

> **Penting:** Proyek ini bukan file HTML statis biasa. Ia menggunakan **Node.js** dan **Vite** sebagai alat bantu pengembangan, sehingga kamu **wajib menginstal Node.js** meskipun sudah punya XAMPP.

---

## Yang Kamu Butuhkan Sebelum Mulai

| Kebutuhan | Versi | Keterangan |
|---|---|---|
| **XAMPP** | 3.3 | Sudah kamu miliki — menyediakan PHP dan MySQL |
| **Node.js** | 20 atau 22 ke atas | Wajib diinstal, belum ada di XAMPP |
| **Composer** | Terbaru | Untuk menginstal dependensi Laravel (backend) |
| **Browser** | Chrome / Edge / Firefox | Untuk membuka aplikasinya |

---

## Langkah 1 — Instal Node.js

XAMPP **tidak menyertakan Node.js**, jadi kamu perlu menginstalnya sendiri. Ikuti langkah berikut:

### 1.1 Unduh Node.js

1. Buka browser dan pergi ke: **https://nodejs.org**
2. Klik tombol **"LTS"** (versi yang disarankan, lebih stabil).
   - Pastikan versinya **20.x** atau **22.x ke atas**.
3. Unduh file installer `.msi` untuk Windows.

### 1.2 Instal Node.js

1. Buka file `.msi` yang sudah diunduh.
2. Klik **Next** terus sampai selesai (tidak perlu mengubah pengaturan apa pun).
3. Centang opsi **"Add to PATH"** jika ditanya — ini penting!
4. Klik **Install**, lalu tunggu sampai selesai.
5. Klik **Finish**.

### 1.3 Cek Apakah Node.js Berhasil Terinstal

1. Tekan **Win + R**, ketik `cmd`, lalu tekan **Enter**.
2. Ketik perintah berikut dan tekan Enter:
   ```
   node --version
   ```
3. Jika muncul tulisan seperti `v20.19.0` atau `v22.x.x`, berarti **berhasil**.
4. Cek juga npm (manajer paket Node.js):
   ```
   npm --version
   ```
   Harus muncul angka versi, contoh: `10.x.x`.

---

## Langkah 2 — Siapkan Backend (Laravel)

Proyek frontend ini membutuhkan **backend API Laravel** yang berjalan di `http://127.0.0.1:8000`. Tanpa backend, aplikasi tidak bisa login atau menampilkan data.

### 2.1 Instal Composer (jika belum ada)

Composer adalah alat untuk menginstal Laravel dan dependensinya.

1. Buka: **https://getcomposer.org/download/**
2. Klik **"Composer-Setup.exe"** dan unduh.
3. Jalankan installer-nya, ikuti langkah-langkah yang muncul.
4. Saat diminta memilih PHP, arahkan ke PHP milik XAMPP:
   ```
   C:\xampp\php\php.exe
   ```
5. Selesai instal, cek dengan membuka CMD dan ketik:
   ```
   composer --version
   ```

### 2.2 Letakkan Folder Backend

1. Pastikan folder backend Laravel sudah ada di dalam `C:\xampp\htdocs\` (contoh: `C:\xampp\htdocs\SistemDispensasiSMALA-backend`).
2. Buka **CMD**, lalu masuk ke folder backend:
   ```
   cd C:\xampp\htdocs\SistemDispensasiSMALA-backend
   ```
3. Instal dependensi Laravel:
   ```
   composer install
   ```
4. Salin file konfigurasi:
   ```
   copy .env.example .env
   ```
5. Generate kunci aplikasi:
   ```
   php artisan key:generate
   ```

### 2.3 Buat Database

1. Buka **XAMPP Control Panel**, lalu klik **Start** di baris **Apache** dan **MySQL**.
2. Buka browser, pergi ke: **http://localhost/phpmyadmin**
3. Klik **"New"** di sisi kiri untuk membuat database baru.
4. Beri nama database (contoh: `dispensasi_smala`), lalu klik **Create**.

### 2.4 Konfigurasi File `.env` Backend

1. Buka file `.env` di folder backend menggunakan Notepad atau editor teks lainnya.
2. Cari bagian ini dan sesuaikan:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dispensasi_smala
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   > Di XAMPP, username MySQL defaultnya adalah `root` dan password-nya **kosong**.

3. Simpan file.

### 2.5 Jalankan Migrasi Database

Masih di CMD di dalam folder backend, jalankan:
```
php artisan migrate --seed
```
Ini akan membuat tabel-tabel yang dibutuhkan dan mengisi data awal.

### 2.6 Jalankan Server Backend

Jalankan perintah ini di CMD (tetap di folder backend):
```
php artisan serve
```

Jika berhasil, akan muncul tulisan:
```
INFO  Server running on [http://127.0.0.1:8000].
```

> **Jangan tutup jendela CMD ini!** Server backend harus tetap berjalan selama kamu menggunakan aplikasi.

---

## Langkah 3 — Jalankan Proyek Frontend Ini

Sekarang saatnya menjalankan bagian frontend (proyek ini).

### 3.1 Buka Folder Proyek di CMD

1. Buka **CMD baru** (jangan tutup CMD backend).
2. Masuk ke folder frontend:
   ```
   cd C:\xampp\htdocs\SistemDispensasiSMALA-front
   ```

### 3.2 Instal Dependensi

Jalankan perintah ini **satu kali saja** saat pertama kali menyiapkan proyek:
```
npm install
```

Proses ini akan mengunduh semua paket yang dibutuhkan. Tunggu sampai selesai (bisa beberapa menit tergantung koneksi internet). Setelah selesai, akan muncul folder `node_modules` di dalam folder proyek — itu normal.

### 3.3 (Opsional) Buat File Konfigurasi Environment

Jika URL backend kamu berbeda dari default (`http://127.0.0.1:8000`), buat file `.env` di folder frontend:

1. Buat file baru bernama `.env` di `C:\xampp\htdocs\SistemDispensasiSMALA-front\`.
2. Isi dengan:
   ```
   VITE_API_BASE_URL=http://127.0.0.1:8000/api
   ```
3. Simpan file.

> Jika kamu tidak membuat file ini, aplikasi akan otomatis menggunakan `http://127.0.0.1:8000/api` sebagai alamat backend — jadi langkah ini bisa dilewati jika backend berjalan di port default.

### 3.4 Jalankan Server Frontend

```
npm run dev
```

Jika berhasil, akan muncul tampilan seperti ini di CMD:
```
  VITE v7.x.x  ready in xxx ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
```

> **Jangan tutup jendela CMD ini!** Server frontend harus tetap berjalan selama kamu menggunakan aplikasi.

---

## Mengakses Aplikasi di Browser

Setelah kedua server (backend dan frontend) berjalan:

1. Buka browser (Chrome / Edge / Firefox).
2. Pergi ke alamat: **http://localhost:5173**
3. Kamu akan diarahkan ke halaman **Login**.

### Login

Masukkan akun yang sudah ada di database. Jika kamu menjalankan `php artisan migrate --seed`, biasanya ada akun admin bawaan — cek dokumentasi backend untuk username dan password-nya.

---

## Fitur-Fitur Aplikasi

| Halaman | Alamat URL | Keterangan |
|---|---|---|
| **Login** | `/login` | Halaman masuk ke aplikasi |
| **Register** | `/register` | Daftar akun baru |
| **Dashboard** | `/dashboard` | Ringkasan data dispensasi |
| **Dispensasi** | `/dispensasi` | Daftar semua pengajuan dispensasi |
| **Buat Dispensasi** | `/dispensasi/create` | Ajukan dispensasi baru |
| **Detail Dispensasi** | `/dispensasi/:id` | Lihat detail satu pengajuan |
| **Edit Dispensasi** | `/dispensasi/:id/edit` | Ubah data pengajuan |
| **Data Pengguna** | `/users` | Kelola akun pengguna *(admin)* |
| **Tambah Pengguna** | `/users/create` | Buat akun pengguna baru *(admin)* |
| **Analitik** | `/analytics` | Grafik dan statistik dispensasi |
| **Profil** | `/profile` | Lihat dan edit profil akun kamu |
| **Audit Log** | `/audit-logs` | Riwayat aktivitas sistem *(admin)* |
| **Backup** | `/backups` | Manajemen backup data *(admin)* |

> Halaman bertanda *(admin)* hanya bisa diakses oleh pengguna dengan peran admin.

---

## Struktur Folder

```
SistemDispensasiSMALA-front/
├── public/               # File statis (favicon, dll.)
├── src/
│   ├── assets/           # CSS global dan gambar
│   ├── components/       # Komponen UI yang dapat digunakan ulang
│   ├── composables/      # Logika yang bisa dipakai di banyak halaman
│   ├── router/           # Pengaturan rute/URL halaman
│   ├── services/
│   │   └── api.js        # Konfigurasi koneksi ke backend API
│   ├── stores/           # Manajemen state (data global aplikasi)
│   ├── utils/            # Fungsi-fungsi pembantu
│   ├── views/            # Halaman-halaman utama aplikasi
│   ├── App.vue           # Komponen utama Vue
│   └── main.js           # Titik masuk aplikasi
├── .env                  # Konfigurasi environment (buat sendiri jika perlu)
├── package.json          # Daftar dependensi proyek
├── vite.config.js        # Konfigurasi Vite
└── tailwind.config.js    # Konfigurasi Tailwind CSS
```

---

## Pertanyaan Umum / Troubleshooting

### "npm tidak dikenali" setelah instal Node.js

Tutup CMD dan buka CMD baru, lalu coba lagi. Jika masih gagal, coba restart komputer.

---

### Halaman web muncul tapi tidak bisa login / data tidak muncul

Pastikan:
1. Server backend sudah berjalan (`php artisan serve` di CMD backend).
2. Tidak ada pesan error di CMD backend.
3. URL backend sudah benar (`http://127.0.0.1:8000`).

Buka browser, pergi ke **http://127.0.0.1:8000/api** — jika muncul respons JSON (bukan halaman error), berarti backend hidup.

---

### Error saat `npm install`: `ENOENT` atau `permission denied`

Pastikan kamu sudah masuk ke folder yang benar:
```
cd C:\xampp\htdocs\SistemDispensasiSMALA-front
```
Lalu coba lagi `npm install`.

---

### Error `EADDRINUSE: address already in use` saat `npm run dev`

Port 5173 sedang dipakai aplikasi lain. Coba hentikan semua proses di port itu atau restart komputer, lalu jalankan ulang.

---

### MySQL tidak mau menyala di XAMPP

- Pastikan tidak ada aplikasi lain yang memakai port 3306 (misalnya MySQL yang diinstal terpisah).
- Coba klik **Stop** lalu **Start** lagi di XAMPP Control Panel.
- Jika masih gagal, buka **XAMPP Control Panel → MySQL → Logs** untuk melihat pesan error.

---

### Cara Menghentikan Server

- Untuk menghentikan server frontend: di CMD yang menjalankan `npm run dev`, tekan **Ctrl + C**, lalu ketik `Y` dan Enter.
- Untuk menghentikan server backend: di CMD yang menjalankan `php artisan serve`, tekan **Ctrl + C**.

---

## Ringkasan Perintah Penting

```bash
# Instal dependensi (hanya sekali)
npm install

# Jalankan server frontend (mode pengembangan)
npm run dev

# Build untuk produksi (menghasilkan file siap deploy)
npm run build
```

---

> Dibuat untuk Sistem Dispensasi SMALA. Jika mengalami kendala lain, hubungi pengembang proyek.
