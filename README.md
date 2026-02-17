# Volunteer Event Management API

REST API sederhana untuk sistem manajemen event relawan. API ini memungkinkan pengguna untuk melihat daftar event, melihat detail event, mendaftar (join) ke event, serta mengelola pembuatan event baru.

## Daftar Isi
- [Prasyarat](#prasyarat)
- [Cara Install](#cara-install)
- [Cara Menjalankan Project](#cara-menjalankan-project)
- [Daftar Endpoint API](#daftar-endpoint-api)
- [Catatan Desain & Asumsi](#catatan-desain--asumsi)

## Prasyarat
- PHP >= 8.2
- Composer
- MySQL

## Cara Install

1.  **Clone Repository** (Jika Anda mengunduh source code)
    ```bash
    git clone <repository_url>
    cd volunteer-event-api
    ```

2.  **Install Dependencies**
    Jalankan perintah berikut untuk menginstall library Laravel:
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database:
    ```bash
    cp .env.example .env
    ```
    Buka file `.env` dan atur koneksi database:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=volunteer_event_management
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4.  **Generate App Key**
    ```bash
    php artisan key:generate
    ```

5.  **Migrasi & Seeding Database**
    Jalankan migrasi untuk membuat tabel dan seeder untuk data awal:
    ```bash
    php artisan migrate --seed
    ```

## Cara Menjalankan Project

Jalankan development server Laravel:
```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`.

## Daftar Endpoint API

### Authentication
| Method | Endpoint | Deskripsi | Params/Body | Auth |
| :--- | :--- | :--- | :--- | :--- |
| **POST** | `/api/register` | Mendaftarkan user baru | `name`, `email`, `password` | Tidak |
| **POST** | `/api/login` | Login user | `email`, `password` | Tidak |
| **POST** | `/api/logout` | Logout user (Hapus token) | - | **Ya** |

### Event Management
| Method | Endpoint | Deskripsi | Params/Body | Auth |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/api/events` | Mendapatkan daftar semua event | - | Tidak |
| **GET** | `/api/events/{id}` | Mendapatkan detail event | - | Tidak |
| **POST** | `/api/events` | Membuat event baru | `title`, `description`, `event_date` | **Ya** |
| **POST** | `/api/events/{id}/join` | Join ke sebuah event | - | **Ya** |

### User Info
| Method | Endpoint | Deskripsi | Params/Body | Auth |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/api/user` | Mendapatkan info user yang sedang login | - | **Ya** |

### Alur Penggunaan API

1.  **Register/Login**: User mendaftar atau login untuk mendapatkan `access_token`.
2.  **Lihat Event**: User bisa melihat daftar event tanpa login (`GET /api/events`).
3.  **Buat Event**: User login membuat event baru (`POST /api/events`).
4.  **Join Event**: User login memilih event dan melakukan join (`POST /api/events/{id}/join`).

### Error Handling

API ini menangani error umum dengan kode status HTTP yang sesuai:
- **401 Unauthorized**: Jika akses token tidak valid/kadaluarsa.
- **404 Not Found**: Jika event yang dicari tidak ada.
- **409 Conflict**: Jika user mencoba join event yang sama dua kali.
- **422 Unprocessable Content**: Jika validasi input gagal (misal email kosong atau format salah).

### Contoh Request (Postman/cURL)

**Login:**
```bash
POST /api/login
Content-Type: application/json
{
    "email": "test@example.com",
    "password": "password"
}
```

**Join Event (dengan Token):**
```bash
POST /api/events/1/join
Authorization: Bearer <your_access_token>
Content-Type: application/json
```

## Catatan Desain & Asumsi

### 1. Struktur Data
- **User**: Menggunakan tabel standar `users` Laravel (id, name, email, password).
- **Event**: Tabel `events` memiliki field minimalis: `title`, `description`, `event_date`.
- **Relasi**: Menggunakan relasi **Many-to-Many** antara User dan Event (tabel pivot `event_user`) karena:
    - Satu user bisa mengikuti banyak event.
    - Satu event bisa diikuti banyak user.

### 2. Authentication
- Menggunakan **Laravel Sanctum** untuk token-based authentication yang sederhana dan aman untuk SPA/Mobile Client.
- Token format: `Bearer <token>`.

### 3. Error Handling
- API mengembalikan kode status HTTP standar:
    - `200` OK: Berhasil.
    - `201` Created: Resource berhasil dibuat.
    - `401` Unauthorized: Gagal login atau token tidak valid.
    - `404` Not Found: Event tidak ditemukan.
    - `409` Conflict: User sudah join event tersebut.
    - `422` Unprocessable Entity: Validasi input gagal.

### 4. Validasi
- Semua input user divalidasi menggunakan fitur `Request Validation` Laravel di Controller.

### 5. Lainnya
- Endpoint pembuatan event (`POST /events`) dilindungi Auth agar hanya user terdaftar yang bisa membuat event (asumsi sederhana untuk manajemen event).
- Logic join event mencegah duplikasi (user tidak bisa join event yang sama dua kali).

### 6. Jawaban Pertanyaan dari pdf 
1. Bagian tersulit apa dari assignment ini?
jawaban: 
    - Memastikan user yang sedang login (Auth::id()) tersambung dengan benar ke event ID yang dituju.
    - Mencegah duplikasi data (validasi agar user tidak bisa join ke event yang sama dua kali).
    - Memberikan respon HTTP yang tepat (misalnya 409 Conflict jika sudah join, atau 404 Not Found jika event tidak ada) alih-alih membiarkan aplikasi crash dengan error database SQL.
2. Jika diberi waktu 1 minggu, apa yang akan kamu perbaiki?
jawaban: 
    - Pemisahan Role (Admin vs Volunteer): Saat ini semua user yang login bisa membuat event. Seharusnya hanya Admin yang bisa POST /events (Create/Delete), sedangkan Volunteer hanya bisa GET dan POST /join.
    - Pagination & Filtering: Mengganti Event::all() dengan pagination dan fitur search/filter (misalnya berdasarkan tanggal atau kategori) agar API tidak berat saat data mencapai ribuan.
    - Automated Testing: Menambahkan Unit Test (PHPUnit/Pest) yang lebih komprehensif untuk memastikan setiap endpoint aman dari regresi saat kode diubah.
3. Alasan Memilih Pendekatan Teknis Tersebut
jawaban: 
    - Laravel Sanctum: Dipilih karena ringan dan jauh lebih mudah diimplementasikan untuk API sederhana/SPA dibandingkan JWT Penuh atau Laravel Passport, tapi ini sudah aman untuk autentikasi berbasis token.
    - Eloquent ORM & Pivot Table: Menggunakan fitur bawaan Laravel (belongsToMany, attach) adalah cara paling efisien dan "bersih" untuk menangani database relasional tanpa harus menulis query SQL manual yang rawan SQL Injection
    - RESTful Standard: Menggunakan method HTTP standar (GET, POST) dan kode status yang konsisten (200, 201, 401, 422) agar API mudah diprediksi (predicable) dan mudah dikonsumsi oleh Frontend Developer mana pun
