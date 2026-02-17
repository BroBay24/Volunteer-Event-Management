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
