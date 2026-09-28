# 📋 Progress Tracking Proyek NGOPITOR

Dokumen ini berfungsi sebagai catatan rekam jejak (*changelog* & *progress tracker*) untuk mencatat setiap perubahan, status tiket, file yang ditambahkan/diubah, serta rencana pengembangan backend **NGOPITOR**.

---

## 📌 Ringkasan Status Tiket

| Tiket | Deskripsi | Status | Tanggal Selesai |
| :--- | :--- | :---: | :---: |
| **NGO-4** | Setup Fondasi RESTful API, Standard Response JSON & Autentikasi Token | ✅ **SELESAI** | 2026-09-28 |
| **NGO-5** | Skema Database, Model, Factory & Seeder Data Kedai Kopi | ✅ **SELESAI** | 2026-09-28 |
| **NGO-6** | Algoritma Scoring, Filter Pencarian & Rekomendasi Kedai Kopi | ✅ **SELESAI** | 2026-09-28 |

---

## 🚀 Rincian Pekerjaan yang Sudah Dikerjakan

### 1. Tiket NGO-4: Fondasi Arsitektur RESTful API & Autentikasi Token
**Tujuan**: Membangun fondasi arsitektur RESTful API yang rapi, format respon JSON terpadu, dan sistem autentikasi token menggunakan Laravel Sanctum.

- [x] **Inisialisasi Project**:
  - Laravel 12 dengan PHP 8.3 di environment Laragon.
  - Setup database PostgreSQL (`port: 5432`, `database: ngopitor`, `username: postgres`).
- [x] **Standarisasi JSON Response**:
  - Dibuat Trait [`app/Traits/ApiResponse.php`](app/Traits/ApiResponse.php) dengan metode `successResponse()` dan `errorResponse()`.
  - Diintegrasikan ke [`app/Http/Controllers/Controller.php`](app/Http/Controllers/Controller.php) sehingga dapat digunakan di semua controller API.
  - Handler exception global pada [`bootstrap/app.php`](bootstrap/app.php) untuk mengubah error bawaan Laravel (`ValidationException`, `AuthenticationException`, `NotFoundHttpException`) menjadi format JSON standar seragam.
- [x] **Sistem Autentikasi (Laravel Sanctum)**:
  - Trait `HasApiTokens` diaktifkan pada model [`app/Models/User.php`](app/Models/User.php).
  - Validasi ketat menggunakan Form Request:
    - [`app/Http/Requests/Auth/RegisterRequest.php`](app/Http/Requests/Auth/RegisterRequest.php) (Nama, email unik, konfirmasi password min 8 karakter).
    - [`app/Http/Requests/Auth/LoginRequest.php`](app/Http/Requests/Auth/LoginRequest.php).
  - Transformasi data profil via [`app/Http/Resources/UserResource.php`](app/Http/Resources/UserResource.php).
  - Controller autentikasi [`app/Http/Controllers/Api/V1/AuthController.php`](app/Http/Controllers/Api/V1/AuthController.php) dengan 4 endpoint:
    - `POST /api/v1/auth/register` (Register user baru + generate token Sanctum)
    - `POST /api/v1/auth/login` (Login user + verifikasi kredensial + generate token)
    - `GET /api/v1/auth/me` (Ambil profil user login, protected via `auth:sanctum`)
    - `POST /api/v1/auth/logout` (Revoke token aktif, protected via `auth:sanctum`)
- [x] **Pembersihan Konfigurasi & Gitignore**:
  - Dihapus folder/file generator ekstra yang tidak digunakan (`.claude/`, `.factory/`, `CLAUDE.md`, `opencode.json`, `boost.json`).
  - Menambahkan `/.agents` ke [`.gitignore`](.gitignore) agar folder lokal tetap ada namun tidak mengotori repositori Git.
- [x] **Testing & Formatting**:
  - 8 unit/feature test kasus autentikasi di [`tests/Feature/AuthTest.php`](tests/Feature/AuthTest.php) (100% pass).
  - Formatter Laravel Pint diterapkan (`vendor/bin/pint --format agent`).

---

### 2. Tiket NGO-5: Skema Database & Seeder Data Kedai Kopi
**Tujuan**: Membangun skema tabel `coffee_shops`, model Eloquent beserta query scope, factory pengujian, dan seeder data kedai kopi riil.

- [x] **Migrasi Skema Tabel (`coffee_shops`)**:
  - File: [`database/migrations/2026_09_28_063257_create_coffee_shops_table.php`](database/migrations/2026_09_28_063257_create_coffee_shops_table.php)
  - Atribut lengkap:
    - **Identitas**: `name`, `slug` (unique), `description`, `address`, `city` (indexed), koordinat `latitude` & `longitude`.
    - **Kontak & Media**: `phone`, `instagram`, `image_url`.
    - **Harga & Rating**: `price_min`, `price_max`, `price_range` (`$`, `$$`, `$$$`), `rating` (indexed), `review_count`.
    - **Jam Operasional**: `opening_time`, `closing_time`.
    - **Fasilitas WFC (Boolean indexed)**: `has_wifi`, `has_power_outlets`, `is_ac`, `is_outdoor`, `is_smoking_area`, `is_work_friendly`, `has_prayer_room`.
    - **Suasana & Kriteria Scoring**: `ambiance` (aesthetic, cozy, minimalist, industrial, nature), `wifi_speed_mbps`, `noise_level` (quiet, moderate, loud).
    - **Status**: `is_active` (boolean, indexed).
- [x] **Model & Query Scopes**:
  - File: [`app/Models/CoffeeShop.php`](app/Models/CoffeeShop.php)
  - `$fillable` lengkap dan `casts()` untuk tipe data boolean, integer, float.
  - Eloquent Scopes: `scopeActive()`, `scopeInCity()`, `scopeWorkFriendly()`.
- [x] **Factory & Seeder Realistis**:
  - Factory: [`database/factories/CoffeeShopFactory.php`](database/factories/CoffeeShopFactory.php) dengan state `workFriendly()` dan `outdoor()`.
  - Seeder: [`database/seeders/CoffeeShopSeeder.php`](database/seeders/CoffeeShopSeeder.php) yang memuat **10 kedai kopi populer riil**:
    1. *Titik Temu Coffee* (Senopati, Jakarta Selatan)
    2. *Kopi Toko Djawa* (Braga, Bandung)
    3. *Kroma Coffee* (Dharmawangsa, Jakarta Selatan)
    4. *Raindear Coffee & Kitchen* (Baranangsiang, Bogor)
    5. *Space Coffee Roastery* (Sorosutan, Yogyakarta)
    6. *Popolo Coffee* (Sentul, Bogor)
    7. *Tanamera Coffee* (Thamrin City, Jakarta Pusat)
    8. *Armor Kopi* (Dago Pakar, Bandung)
    9. *Kopi Nako* (Pajajaran, Bogor)
    10. *Kala Cemara* (Lembang, Bandung)
    - Ditambah **15 data variatif** dari Factory (Total: 25 record tersimpan di PostgreSQL).
  - Terintegrasi di [`database/seeders/DatabaseSeeder.php`](database/seeders/DatabaseSeeder.php).
- [x] **API Resource & Endpoints**:
  - Resource: [`app/Http/Resources/CoffeeShopResource.php`](app/Http/Resources/CoffeeShopResource.php).
  - Controller: [`app/Http/Controllers/Api/V1/CoffeeShopController.php`](app/Http/Controllers/Api/V1/CoffeeShopController.php).
  - Route:
    - `GET /api/v1/coffee-shops` (List kedai kopi dengan pagination & sorting rating).
    - `GET /api/v1/coffee-shops/{coffeeShop:slug}` (Detail kedai kopi berdasarkan slug).

---

### 3. Tiket NGO-6: Algoritma Scoring, Filter Pencarian & Rekomendasi
**Tujuan**: Mengimplementasikan pencarian multifaset, filter dinamis, dan algoritma scoring rekomendasi terbobot (*multi-criteria weighted scoring*).

- [x] **Pencarian & Filter Lanjutan**:
  - Form Request: [`app/Http/Requests/CoffeeShop/CoffeeShopFilterRequest.php`](app/Http/Requests/CoffeeShop/CoffeeShopFilterRequest.php) dengan normalisasi boolean otomatis via `prepareForValidation()`.
  - Eloquent Scope: `scopeFilter()` di [`app/Models/CoffeeShop.php`](app/Models/CoffeeShop.php) (driver-agnostic SQL, kompatibel dengan PostgreSQL & SQLite).
  - Parameter filter:
    - `search` : Pencarian keyword case-insensitive pada nama, alamat, atau deskripsi.
    - `city` : Filter berdasarkan kota (*partial match*).
    - `price_range`, `min_price`, `max_price` : Filter rentang harga.
    - `min_rating` : Filter rating minimal.
    - Fasilitas WFC: `has_wifi`, `has_power_outlets`, `is_ac`, `is_outdoor`, `is_smoking_area`, `is_work_friendly`, `has_prayer_room`.
    - Suasana & Kebisingan: `ambiance` (*aesthetic, cozy, minimalist, nature, industrial*), `noise_level` (*quiet, moderate, loud*).
    - Sorting: `sort_by` (`rating`, `review_count`, `price_min`, `price_max`, `name`, `wifi_speed_mbps`) & `sort_order` (`asc`, `desc`).
- [x] **Algoritma Scoring Rekomendasi**:
  - Service: [`app/Services/RecommendationService.php`](app/Services/RecommendationService.php).
  - Mendukung 3 mode preset dan mode kustom:
    1. **Mode WFC (`wfc`)**:
       Prioritas: Kecepatan Wi-Fi & colokan listrik (45%), Suasana hening & AC (25%), Rating (15%), Efisiensi harga (15%).
    2. **Mode Hangout (`hangout`)**:
       Prioritas: Suasana estetik/nature & area outdoor (45%), Rating & popularitas ulasan (30%), Efisiensi harga (15%), Wi-Fi (10%).
    3. **Mode Budget (`budget`)**:
       Prioritas: Harga termurah (45%), Rating (25%), Fasilitas Wi-Fi/colokan (15%), Kenyamanan (15%).
    4. **Mode Kustom (`custom`)**:
       Menerima bobot dinamis dari client (`rating_weight`, `wfc_weight`, `comfort_weight`, `price_weight`).
  - Output transparan: Menyertakan `match_score` (0.0 - 100.0), `match_percentage`, `score_breakdown` (skor rating, skor wfc, skor kenyamanan, skor harga), dan *contextual highlights* alasan rekomendasi.
- [x] **Endpoint Rekomendasi**:
  - Request: [`app/Http/Requests/CoffeeShop/RecommendationRequest.php`](app/Http/Requests/CoffeeShop/RecommendationRequest.php).
  - Controller: [`app/Http/Controllers/Api/V1/RecommendationController.php`](app/Http/Controllers/Api/V1/RecommendationController.php).
  - Route: `GET /api/v1/coffee-shops/recommendations` (ditempatkan sebelum `{coffeeShop:slug}`).
- [x] **Pengujian & Validasi**:
  - Feature test pencarian & filter: [`tests/Feature/CoffeeShopTest.php`](tests/Feature/CoffeeShopTest.php) (8 test cases).
  - Feature test algoritma rekomendasi: [`tests/Feature/RecommendationTest.php`](tests/Feature/RecommendationTest.php) (5 test cases).
  - Total pengujian proyek: **23 tests, 178 assertions (100% Pass)**.
  - Kode telah lolos pemformatan Laravel Pint.

---

## 📁 Struktur File Utama Terkini

```text
NGOPITOR/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php (Base Controller + ApiResponse trait)
│   │   │   └── Api/V1/
│   │   │       ├── AuthController.php (Register, Login, Me, Logout)
│   │   │       ├── CoffeeShopController.php (Index dengan filter & Show)
│   │   │       └── RecommendationController.php (Scored recommendations)
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── RegisterRequest.php
│   │   │   │   └── LoginRequest.php
│   │   │   └── CoffeeShop/
│   │   │       ├── CoffeeShopFilterRequest.php
│   │   │       └── RecommendationRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       └── CoffeeShopResource.php
│   ├── Models/
│   │   ├── User.php (HasApiTokens)
│   │   └── CoffeeShop.php (Fillable, Casts, Scopes: active, inCity, workFriendly, filter)
│   ├── Services/
│   │   └── RecommendationService.php (Multi-criteria weighted scoring algorithm)
│   └── Traits/
│       └── ApiResponse.php (Standarisasi response JSON API)
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── CoffeeShopFactory.php (states: workFriendly, outdoor)
│   ├── migrations/
│   │   ├── ..._create_users_table.php
│   │   ├── ..._create_personal_access_tokens_table.php
│   │   └── 2026_09_28_063257_create_coffee_shops_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── CoffeeShopSeeder.php (10 Real Cafes + 15 Factory Cafes)
├── routes/
│   └── api.php (Prefix v1: auth, coffee-shops & recommendations)
├── tests/
│   └── Feature/
│       ├── AuthTest.php (8 test cases)
│       ├── CoffeeShopTest.php (8 test cases)
│       └── RecommendationTest.php (5 test cases)
├── .gitignore (Termasuk ignore /.agents)
├── progres.md (Dokumen tracking ini)
└── README.md
```
