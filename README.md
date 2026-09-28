# NGOPITOR

Backend RESTful API untuk platform rekomendasi dan direktori kedai kopi **NGOPITOR**, dibangun menggunakan Laravel 12, PostgreSQL, dan Laravel Sanctum.

## Fitur & Status Implementasi
- [x] **NGO-4**: Fondasi RESTful API, standarisasi format response JSON, exception handling, dan autentikasi token (Register, Login, Me, Logout via Sanctum).
- [x] **NGO-5**: Skema database dan seeder data kedai kopi (Coffee Shops).
- [x] **NGO-6**: Algoritma scoring dan filter pencarian kedai kopi.

## Requirements
- PHP >= 8.3
- Composer >= 2.0
- PostgreSQL
- PHP Extensions: `pdo_pgsql`, `pgsql`

## Panduan Instalasi Lokal
1. **Clone repository:**
   ```bash
   git clone https://github.com/anandra00/NGOPITOR.git
   cd NGOPITOR
   ```
2. **Install dependensi:**
   ```bash
   composer install
   ```
3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Pastikan pengaturan koneksi database PostgreSQL di `.env` sudah sesuai:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=ngopitor
   DB_USERNAME=postgres
   DB_PASSWORD=postgres
   ```
4. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```
5. **Jalankan Automated Tests:**
   ```bash
   php artisan test
   ```
6. **Jalankan Server Development:**
   ```bash
   php artisan serve
   ```
