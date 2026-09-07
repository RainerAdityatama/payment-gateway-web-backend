# 🏟️ KelapaDua Sports - Payment Gateway & Booking API

Sistem *backend* RESTful API untuk aplikasi pemesanan lapangan olahraga. Dibangun dengan fokus pada keandalan transaksi finansial, sistem ini mengintegrasikan *Payment Gateway* Midtrans untuk pemrosesan pembayaran otomatis, manajemen ketersediaan jadwal (*slotting*), serta pengiriman resi PDF asinkron menggunakan sistem *Queue*.

## 🚀 Teknologi yang Digunakan
- **Framework:** Laravel 11
- **Authentication:** Laravel Sanctum (Token-based Auth)
- **Payment Gateway:** Midtrans PHP (Snap & Core API)
- **Background Jobs:** Laravel Queue (Database Driver)
- **Document Generation:** DomPDF (barryvdh/laravel-dompdf)
- **Email Testing:** Mailtrap SMTP

## ✨ Fitur Utama
- **Manajemen Ketersediaan Real-time:** Pengecekan slot waktu lapangan yang dinamis berdasarkan tanggal untuk mencegah *double-booking*.
- **Secure Checkout & Webhook:** Pemrosesan transaksi via Midtrans dengan pola *idempotency* untuk mencegah manipulasi atau duplikasi data status pembayaran.
- **Asynchronous Email Notification:** Pembuatan struk PDF dan pengiriman email bukti pembayaran dilakukan di latar belakang (*background job*) agar respons API tetap cepat (non-blocking).
- **Admin Dashboard API:** Endpoint khusus berotentikasi untuk manajemen data lapangan (CRUD).
- **Rate Limiting (Throttle):** Proteksi *endpoint* dari serangan *spam* dan *brute-force* (Public: 60 req/min, Admin: 10 req/min).

---

## 📡 Dokumentasi Endpoint API

Semua respons API menggunakan format standar JSON. *Endpoint* Admin memerlukan *Header* `Authorization: Bearer <token>`.

### 🔐 Autentikasi
| Method | Endpoint | Deskripsi | Keterangan |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/auth/login` | Autentikasi admin & generate token | *Guest* |
| `POST` | `/api/auth/logout` | Menghapus sesi / token saat ini | *Requires Auth* |
| `GET`  | `/api/user` | Mengambil data user yang sedang login | *Requires Auth* |

### 🌐 Publik & Transaksi (Throttle: 60 req / min)
| Method | Endpoint | Deskripsi | Keterangan |
| :--- | :--- | :--- | :--- |
| `GET`  | `/api/public/lapangan` | Mengambil daftar semua lapangan | - |
| `GET`  | `/api/public/lapangan/{slug}/kalender` | Melihat jadwal terisi dalam sebulan | - |
| `GET`  | `/api/public/lapangan/{slug}/availability` | Mengecek slot jam kosong per tanggal | Kueri `?date=YYYY-MM-DD` |
| `POST` | `/api/public/checkout` | Memproses pesanan & memanggil Snap Midtrans | - |
| `POST` | `/api/public/webhook/midtrans` | *Endpoint* khusus menerima notifikasi Midtrans | *Bypass CSRF* |

### 🛡️ Admin Management (Throttle: 10 req / min)
| Method | Endpoint | Deskripsi | Keterangan |
| :--- | :--- | :--- | :--- |
| `GET`  | `/api/admin/lapangan` | Melihat daftar lapangan untuk dikelola | *Requires Auth* |
| `POST` | `/api/admin/lapangan` | Menambahkan lapangan baru | *Requires Auth* |
| `PUT`  | `/api/admin/lapangan/{lapangan}` | Memperbarui data lapangan | *Requires Auth* |
| `DELETE`| `/api/admin/lapangan/{lapangan}`| Menghapus data lapangan | *Requires Auth* |

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

Salin file konfigurasi bawaan dan hasilkan *Application Key*:

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

Karena sistem ini bergantung pada notifikasi eksternal (*server-to-server*) dari Midtrans dan pemrosesan latar belakang (*Queue*), ikuti 3 langkah berikut agar alur *checkout* hingga pengiriman email PDF berjalan sempurna di komputer lokal:

### Langkah 1: Buka Tunnel dengan Ngrok

Agar Midtrans bisa mengirim data (Webhook) ke localhost Anda, jalankan Ngrok di terminal terpisah:

```bash
ngrok http 8000

```

*Salin URL Forwarding dari Ngrok (contoh: `https://xxxx.ngrok-free.app`).*

### Langkah 2: Konfigurasi Dashboard Midtrans Sandbox

1. Login ke Dashboard Midtrans Sandbox Anda.
2. Masuk ke **Settings > Snap Preferences > System Settings**.
3. Pada **Notification URL**, masukkan URL Ngrok ditambah rute webhook API:
`https://xxxx.ngrok-free.app/api/public/webhook/midtrans`
4. Pada **Finish / Unfinish / Error URL**, masukkan alamat URL aplikasi Frontend React Anda (contoh: `http://localhost:5173/pembayaran-sukses`).

### Langkah 3: Jalankan Queue Worker

Sistem menggunakan *Queue* agar respons webhook Midtrans sangat cepat (di bawah 1 detik). Pembuatan file PDF dan pengiriman email dilakukan oleh *worker*. Buka terminal terpisah dan jalankan:

```bash
php artisan queue:work

```

*(Catatan Teknis: Jika Anda melakukan perubahan pada kode Job/Notifikasi atau view PDF selama masa development, pastikan untuk me-restart worker dengan perintah `php artisan queue:restart` lalu jalankan kembali `queue:work`).*

```
