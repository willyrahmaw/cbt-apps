# CBT App – Computer Based Test

Laravel 12 based CBT (Computer Based Test) application with role-based access (Superadmin, Pembuat Soal, Pengguna/Siswa), bank soal, ujian online, penilaian otomatis + essai, ekspor PDF/Excel, dan audit log aktivitas.

---

## Fitur Utama

- **Manajemen Pengguna & Kelas**
  - Role: Superadmin, Pembuat Soal, Pengguna (siswa)
  - Relasi ke kelas (`school_classes`) dan NIS (Nomor Induk Siswa)
  - Import & export user via Excel (template bawaan)

- **Bank Soal & Ujian**
  - Kategori ujian
  - Soal pilihan ganda & essai
  - Gambar soal
  - Durasi, KKM, jadwal mulai–selesai
  - Token ujian

- **Pelaksanaan Ujian (CBT)**
  - Tampilan soal dengan navigasi nomor di samping
  - Timer real-time sinkron dengan server
  - Penandaan “ragu-ragu”
  - Watermark dinamis (nama/email/sesi + waktu)
  - Aksesibilitas:
    - Kontrol ukuran font soal (A-/A+/reset)
    - Mode kontras tinggi
    - Navigasi keyboard (next/prev soal, pilih jawaban, toggle ragu)

- **Anti-Cheating & Monitoring**
  - Deteksi: tab switch, blur jendela, keluar fullscreen, klik kanan, copy/paste, print / screenshot, split screen
  - Log pelanggaran per sesi ujian
  - Opsi penghentian otomatis sesi ujian berdasarkan event
  - Halaman monitor ujian untuk pembuat soal

- **Penilaian & Laporan**
  - Penilaian otomatis untuk pilihan ganda
  - Penilaian essai dengan grading interface
  - Rekap hasil ujian
  - Ekspor PDF & Excel:
    - Header laporan mirip kop resmi
    - Ringkasan (total peserta, lulus, tidak lulus, menunggu, rata-rata)
    - Detail per peserta (termasuk NIS)

- **Audit Log & Keamanan**
  - Audit log terstruktur untuk aksi admin/creator
  - Tampilan log yang rapi dan mudah dibaca
  - Middleware `SecurityHeaders`:
    - CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy

---

## Prasyarat

- **PHP**: ^8.2
- **Composer**
- **Node.js**: versi yang mendukung Vite 7 (minimal Node 18 direkomendasikan)
- **Database**:
  - Default: SQLite (menggunakan `DB_CONNECTION=sqlite` di `.env.example`)
  - Bisa diganti ke MySQL/PostgreSQL sesuai kebutuhan

---

## Instalasi Cepat (Rekomendasi)

Di root project (`cbt`):

```bash
composer install
composer run setup
```

Perintah `composer run setup` akan:
- Menyalin `.env.example` ke `.env` (jika belum ada)
- Generate `APP_KEY`
- Menjalankan migrasi database
- Menjalankan `npm install`
- Menjalankan `npm run build`

Setelah itu, jalankan server:

```bash
php artisan serve
```

Lalu buka `http://localhost:8000` di browser.

---

## Instalasi Manual (Langkah per Langkah)

### 1. Clone / Salin Project

```bash
cd cbt
```

Jika ini bukan hasil clone git, pastikan seluruh source code sudah berada di folder tersebut.

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Buat File `.env` dan Kunci Aplikasi

```bash
cp .env.example .env  # di Windows bisa pakai copy .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di `.env`:

- Untuk **SQLite** (default):
  - Pastikan `DB_CONNECTION=sqlite`
  - Buat file database jika perlu:

    ```bash
    php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
    ```

- Untuk **MySQL** (contoh):

  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=cbt
  DB_USERNAME=root
  DB_PASSWORD=secret
  ```

### 4. Migrasi & Seed (opsional)

```bash
php artisan migrate
```

Jika ada seeder yang ingin dijalankan:

```bash
php artisan db:seed
```

> Catatan: di project ini beberapa data awal seperti kategori ujian disiapkan lewat seeder `CategorySeeder`.

### 5. Install Dependensi Frontend & Build Asset

```bash
npm install
npm run build
```

Untuk pengembangan (hot reload):

```bash
npm run dev
```

> Di mode dev, pastikan Vite berjalan saat mengerjakan tampilan frontend.

### 6. Jalankan Aplikasi

Server Laravel:

```bash
php artisan serve
```

Secara default akan berjalan di `http://localhost:8000`.

Jika ingin menjalankan worker queue dan log real-time (opsional):

```bash
php artisan queue:listen
php artisan pail
```

---

## Akun & Role (Saran Setup)

Tergantung implementasi seeder Anda, biasanya pola berikut digunakan:

- **Superadmin**: mengelola setting global, user, backup.
- **Pembuat Soal**: membuat bank soal, ujian, dan melakukan penilaian.
- **Pengguna/Siswa**: mengikuti ujian yang dijadwalkan.

Jika seeder belum membuat akun default, buat manual melalui:
- Form register/admin di aplikasi, atau
- `php artisan tinker` dengan model `User`.

---

## Pengembangan Harian

- **Jalankan full stack dev** (Laravel + queue + log + Vite) dengan skrip composer yang sudah disiapkan:

  ```bash
  composer run dev
  ```

  Ini akan menjalankan:
  - `php artisan serve`
  - `php artisan queue:listen`
  - `php artisan pail`
  - `npm run dev`

- Untuk build produksi ulang setelah mengubah JS/CSS:

  ```bash
  npm run build
  ```

---

## Catatan Keamanan

- Aplikasi sudah menggunakan middleware `SecurityHeaders` dengan CSP & header keamanan lain.
- Untuk deployment produksi:
  - Pastikan aplikasi berjalan di **HTTPS** supaya HSTS berfungsi penuh.
  - Set `APP_ENV=production` dan `APP_DEBUG=false` di `.env`.
  - Konfigurasi storage & backup database sesuai kebijakan server Anda.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
