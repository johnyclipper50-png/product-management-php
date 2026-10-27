# Aplikasi Pengelolaan Produk (CRUD, API & Unit Testing)

Aplikasi manajemen produk berbasis web dan API menggunakan **PHP Native** dan database **MySQL/MariaDB**. Aplikasi ini dilengkapi dengan fitur otentikasi login, pengelolaan data produk (Create, Read, Update, Delete), pencarian multi-kolom, *pagination*, widget & endpoint statistik, serta pengujian otomatis (*Unit Testing*) menggunakan **PHPUnit**.

---

## Fitur Utama

- **Otentikasi & Keamanan:**
  - Login menggunakan **Email/Username** dan **Password** via Web UI & JSON API.
  - Proteksi sesi (`session_start()`) dan pencegahan SQL Injection menggunakan *Prepared Statements*.
- **Pengelolaan Produk (CRUD):**
  - **Create:** Menambah produk baru melalui modal interaktif.
  - **Read:** Menampilkan daftar produk lengkap dengan format Rupiah dan badge status.
  - **Update:** Mengedit data produk secara instan menggunakan modal otomatis.
  - **Delete:** Menghapus produk berdasarkan kode unik dengan dialog konfirmasi.
- **Pencarian, Filter & Pagination:**
  - Pencarian fleksibel berdasarkan **Nama Produk** atau **Kategori**.
  - Pembatasan **20 record per halaman** untuk menjaga performa rendering tabel.
- **Statistik Produk:**
  - **Dashboard Widget:** Card visual untuk ringkasan Total Produk, Produk Tersedia, dan Produk Habis.
  - **JSON Endpoint (`api_stats.php`):** API publik untuk menyajikan data statistik secara instan.
- **Unit Testing:**
  - Automated testing menggunakan **PHPUnit** untuk menguji keandalan endpoint Login dan Statistik Produk.

---

## Struktur Tabel Database

### Tabel `products`
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `kode` | `VARCHAR(10)` | **Primary Key** (Contoh: `PRD0001`) |
| `nama_produk` | `VARCHAR(50)` | Nama produk |
| `deskripsi` | `VARCHAR(100)` | Deskripsi singkat produk |
| `harga` | `INT(11)` | Harga produk dalam Rupiah |
| `kategori` | `VARCHAR(50)` | Kategori produk |
| `status_ketersediaan` | `TEXT` | Status (`tersedia` / `habis`) |

### Tabel `users`
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `email` | `VARCHAR(100)` | **Primary Key** / Email Pengguna |
| `username` | `VARCHAR(50)` | Username Pengguna |
| `password` | `VARCHAR(255)` | Password Pengguna |

---

## Struktur Folder Proyek

```text
product/
├── data/
│   └── products.json     # Data dummy mentah (opsional)
├── tests/
│   └── ApiTest.php       # File Unit Test PHPUnit
├── vendor/               # Dependensi Composer (PHPUnit)
├── api_stats.php         # Endpoint API Statistik Produk
├── composer.json         # Konfigurasi dependensi proyek
├── dashboard.php         # Dashboard utama (CRUD UI, Widget & Pagination)
├── koneksi.php           # Konfigurasi koneksi database MySQL
├── index.php             # Endpoint & Halaman Login (Web & API)
├── logout.php            # Script untuk menghancurkan sesi
├── phpunit.xml           # Konfigurasi runner PHPUnit
└── README.md             # Dokumentasi proyek

Cara Install & Menjalankan Project
1. Persiapan Environment
Pastikan kamu telah menginstal:

Web Server Local (XAMPP / Laragon) dengan PHP 8.0+

MySQL / MariaDB

Composer (untuk menjalankan PHPUnit)

2. Setup Database
Buka phpMyAdmin (http://localhost/phpmyadmin).

Buat database baru bernama db_inventory (atau sesuaikan dengan konfigurasi kamu).

Buat tabel users dan products sesuai struktur di atas.

Masukkan data dummy produk dan setidaknya 1 pengguna ke tabel users.

3. Setup Project & Dependensi
Salin seluruh folder proyek ke directory htdocs XAMPP:C:\xampp\htdocs\product\
Sesuaikan koneksi database di file koneksi.php:

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_inventory";

Jalankan perintah berikut di terminal/CMD untuk menginstal dependensi PHPUnit:

composer install

Endpoint API & Pengujian

1. Endpoint Login (POST /index.php)
Header: Content-Type: application/json

Body Request:
{
  "email": "admin@inventory.co.id",
  "password": "admin"
}

2. Endpoint Statistik (GET /api_stats.php)
Response Contoh:
{
    "status": "success",
    "data": {
        "total_produk": 100,
        "produk_tersedia": 67,
        "produk_habis": 33
    }
}

Jalankan Unit Test (PHPUnit)
Untuk memastikan seluruh endpoint API berfungsi sebagaimana mestinya, jalankan pengujian otomatis melalui terminal di root folder project:

./vendor/bin/phpunit

Teknologi yang Digunakan

Backend: PHP Native (PHP 8.2+)
Database: MySQL / MariaDB
Frontend UI: HTML5, CSS3, JavaScript (ES6)
CSS Framework: Bootstrap 5.3
Testing Framework: PHPUnit ^9.6