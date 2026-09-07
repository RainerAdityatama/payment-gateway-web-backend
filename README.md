# 🏟️ KelapaDua Sports - Payment Gateway & Booking API

Sistem _backend_ RESTful API untuk aplikasi pemesanan lapangan olahraga. Dibangun dengan fokus pada keandalan transaksi finansial, sistem ini mengintegrasikan _Payment Gateway_ Midtrans untuk pemrosesan pembayaran otomatis, manajemen ketersediaan jadwal (_slotting_), serta pengiriman resi PDF asinkron menggunakan sistem _Queue_.

## 🚀 Teknologi yang Digunakan

- **Framework:** Laravel 11
- **Authentication:** Laravel Sanctum (Token-based Auth)
- **Payment Gateway:** Midtrans PHP (Snap & Core API)
- **Background Jobs:** Laravel Queue (Database Driver)
- **Document Generation:** DomPDF (barryvdh/laravel-dompdf)
- **Email Testing:** Mailtrap SMTP

## ✨ Fitur Utama

- **Manajemen Ketersediaan Real-time:** Pengecekan slot waktu lapangan yang dinamis berdasarkan tanggal untuk mencegah _double-booking_.
- **Secure Checkout & Webhook:** Pemrosesan transaksi via Midtrans dengan pola _idempotency_ untuk mencegah manipulasi atau duplikasi data status pembayaran.
- **Asynchronous Email Notification:** Pembuatan struk PDF dan pengiriman email bukti pembayaran dilakukan di latar belakang (_background job_) agar respons API tetap cepat (non-blocking).
- **Admin Dashboard API:** Endpoint khusus berotentikasi untuk manajemen data lapangan (CRUD).
- **Rate Limiting (Throttle):** Proteksi _endpoint_ dari serangan _spam_ dan _brute-force_ (Public: 60 req/min, Admin: 10 req/min).

---

## 📡 Dokumentasi Endpoint API

Semua respons API menggunakan format standar JSON. _Endpoint_ Admin memerlukan _Header_ `Authorization: Bearer <token>`.

### 🔐 Autentikasi

| Method | Endpoint           | Deskripsi                             | Keterangan      |
| :----- | :----------------- | :------------------------------------ | :-------------- |
| `POST` | `/api/auth/login`  | Autentikasi admin & generate token    | _Guest_         |
| `POST` | `/api/auth/logout` | Menghapus sesi / token saat ini       | _Requires Auth_ |
| `GET`  | `/api/user`        | Mengambil data user yang sedang login | _Requires Auth_ |

### 🌐 Publik & Transaksi (Throttle: 60 req / min)

| Method | Endpoint                                   | Deskripsi                                      | Keterangan               |
| :----- | :----------------------------------------- | :--------------------------------------------- | :----------------------- |
| `GET`  | `/api/public/lapangan`                     | Mengambil daftar semua lapangan                | -                        |
| `GET`  | `/api/public/lapangan/{slug}/kalender`     | Melihat jadwal terisi dalam sebulan            | -                        |
| `GET`  | `/api/public/lapangan/{slug}/availability` | Mengecek slot jam kosong per tanggal           | Kueri `?date=YYYY-MM-DD` |
| `POST` | `/api/public/checkout`                     | Memproses pesanan & memanggil Snap Midtrans    | -                        |
| `POST` | `/api/public/webhook/midtrans`             | _Endpoint_ khusus menerima notifikasi Midtrans | _Bypass CSRF_            |

### 🛡️ Admin Management (Throttle: 10 req / min)

| Method   | Endpoint                         | Deskripsi                              | Keterangan      |
| :------- | :------------------------------- | :------------------------------------- | :-------------- |
| `GET`    | `/api/admin/lapangan`            | Melihat daftar lapangan untuk dikelola | _Requires Auth_ |
| `POST`   | `/api/admin/lapangan`            | Menambahkan lapangan baru              | _Requires Auth_ |
| `PUT`    | `/api/admin/lapangan/{lapangan}` | Memperbarui data lapangan              | _Requires Auth_ |
| `DELETE` | `/api/admin/lapangan/{lapangan}` | Menghapus data lapangan                | _Requires Auth_ |

---

## 🛠️ Cara Instalasi & Menjalankan (Lokal)

### 1. Persiapan Awal

Kloning repositori ini dan instal dependensi PHP:

```bash
git clone [https://github.com/RainerAdityatama/NAMA_REPO_KAMU.git](https://github.com/RainerAdityatama/NAMA_REPO_KAMU.git)
cd NAMA_REPO_KAMU
composer install

```

### 2. Konfigurasi Environment (.env)

Salin file konfigurasi bawaan dan hasilkan _Application Key_:

```bash
cp .env.example .env
php artisan key:generate

```

Buka file `.env` dan konfigurasikan bagian berikut secara spesifik:

```env
# Database
DB_DATABASE=nama_database_lokal_anda

# Konfigurasi Midtrans Sandbox
MIDTRANS_SERVER_KEY="SB-Mid-server-xxxxxxxxx"
MIDTRANS_CLIENT_KEY="SB-Mid-client-xxxxxxxxx"
MIDTRANS_IS_PRODUCTION=false

# Konfigurasi Email (Mailtrap) & Queue
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME="your_mailtrap_username"
MAIL_PASSWORD="your_mailtrap_password"
MAIL_ENCRYPTION=tls

```

### 3. Migrasi Database

Jalankan migrasi untuk membangun struktur tabel dan antrean (pastikan database sudah dibuat di MySQL Anda):

```bash
php artisan migrate

```

### 4. Menjalankan Server Utama

```bash
php artisan serve

```

API akan berjalan di `http://127.0.0.1:8000`.

---

## 💳 Panduan Simulasi Pembayaran & Pengujian Asinkron (Localhost)

Karena sistem ini bergantung pada notifikasi eksternal (_server-to-server_) dari Midtrans dan pemrosesan latar belakang (_Queue_), ikuti 3 langkah berikut agar alur _checkout_ hingga pengiriman email PDF berjalan sempurna di komputer lokal:

### Langkah 1: Buka Tunnel dengan Ngrok

Agar Midtrans bisa mengirim data (Webhook) ke localhost Anda, jalankan Ngrok di terminal terpisah:

```bash
ngrok http 8000

```

_Salin URL Forwarding dari Ngrok (contoh: `https://xxxx.ngrok-free.app`)._

### Langkah 2: Konfigurasi Dashboard Midtrans Sandbox

1. Login ke Dashboard Midtrans Sandbox Anda.
2. Masuk ke **Settings > Snap Preferences > System Settings**.
3. Pada **Notification URL**, masukkan URL Ngrok ditambah rute webhook API:
   `https://xxxx.ngrok-free.app/api/public/webhook/midtrans`

### Langkah 3: Jalankan Queue Worker

Sistem menggunakan _Queue_ agar respons webhook Midtrans sangat cepat (di bawah 1 detik). Pembuatan file PDF dan pengiriman email dilakukan oleh _worker_. Buka terminal terpisah dan jalankan:

```bash
php artisan queue:work

```

_(Catatan Teknis: Jika Anda melakukan perubahan pada kode Job/Notifikasi atau view PDF selama masa development, pastikan untuk me-restart worker dengan perintah `php artisan queue:restart` lalu jalankan kembali `queue:work`)._

```

```
