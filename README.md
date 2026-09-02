<div align="center">

<a href="#">
  <picture>
    <img alt="Monitoring Kerja Logo" src="assets/favicon.svg" width="120" height="120">
  </picture>
</a>

# Monitoring Kerja

**Sistem Pemantauan & Pengelolaan Pekerjaan**

> Aplikasi web untuk pencatatan, pemantauan, dan pelaporan pekerjaan tim. Dilengkapi dashboard per role, CRUD tugas, filter monitoring, dan rekapitulasi laporan.

</div>

Monitoring Kerja membantu tim melacak seluruh tugas — dari penugasan, pengerjaan, hingga penyelesaian. Setiap role memiliki dashboard yang disesuaikan: **User** mengelola tugas pribadi, **Supervisor** memantau kemajuan tim, dan **Admin** mengelola pengguna serta sistem secara keseluruhan.

---

## Fitur Utama

- **Autentikasi & Role** — Login dengan bcrypt hash, session guard, CSRF token, dan 3 role (User, Supervisor, Admin)
- **Dashboard Per Role** — Dashboard khusus User, Supervisor, dan Admin dengan statistik tugas
- **CRUD Tugas** — Tambah, edit, hapus (modal popup), dan cari data pekerjaan
- **Filter & Pencarian** — Filter berdasarkan status dan prioritas, pencarian realtime
- **Monitoring** — Supervisor memantau tugas tim dengan filter status & user
- **Manajemen User** — Admin buat, edit role, dan haus pengguna sistem
- **Profil** — Edit profil dan unggah foto dengan preview
- **Laporan** — Rekap status pekerjaan dengan filter dan cetak
- **Responsive** — Tampilan optimal di desktop, tablet, dan mobile
- **Overdue Badge** — Badge jumlah tugas terlambat di sidebar

---

## Struktur Proyek

```text
monitoring_kerja/
├── assets/
│   ├── favicon.svg               # Ikon aplikasi (SVG)
│   ├── css/
│   │   ├── style.css             # Design system & layout utama
│   │   ├── task.css              # Styles halaman tugas
│   │   ├── profil.css            # Styles halaman profil
│   │   ├── monitoring_task.css   # Styles halaman monitoring
│   │   └── edit_role.css         # Styles halaman edit role
│   └── js/
│       ├── script.js             # JavaScript aplikasi utama (terpusat)
│       ├── task.js               # Modal & interaksi tugas
│       ├── profil.js             # Upload foto profil & preview
│       ├── edit_profil.js        # Edit profil & preview foto
│       └── monitoring_task.js    # Filter & pencarian tugas monitoring
├── auth/
│   ├── login.php                 # Halaman login
│   ├── register.php              # Pendaftaran pengguna baru
│   └── logout.php                # Proses logout
├── admin/
│   ├── dashboard_admin.php       # Dashboard admin (statistik sistem)
│   ├── users.php                 # Kelola pengguna (CRUD + role)
│   └── edit_role.php             # Proses edit role pengguna
├── user/
│   ├── dashboard_user.php        # Dashboard user
│   ├── task.php                  # Data pekerjaan (list + filter)
│   ├── tambah_task.php           # Form tambah pekerjaan baru
│   ├── edit_task.php             # Form edit pekerjaan
│   └── hapus_task.php            # Proses hapus pekerjaan
├── supervisor/
│   ├── dashboard_supervisor.php  # Dashboard supervisor
│   └── monitoring_task.php       # Monitoring tugas tim
├── shared/
│   ├── layout.php                # Layout bersama (sidebar, topbar, flow)
│   ├── auth.php                  # Guard akses & CSRF terpusat
│   ├── flash.php                 # Sistem pesan success/error
│   ├── init.php                  # Bootstrap bersama (session + koneksi)
│   ├── TaskRepository.php        # Logika akses data tugas (CRUD + filter)
│   ├── user_view.php             # Tampilan & upload foto profil
│   ├── laporan.php               # Halaman laporan/rekap
│   └── profil.php                # Halaman profil
├── database/
│   └── database.sql              # Skema & data dummy SQL
├── config/
│   ├── koneksi.php               # Koneksi database MySQLi
│   ├── setup.php                 # Setup database (jalankan via CLI)
│   └── seed.php                  # Seed data dummy (jalankan via CLI)
├── uploads/                      # Folder foto profil yang di-unggah
├── tests/
│   └── run_tests.php             # Test suite TaskRepository
├── tools/
│   ├── reset_password.php        # Reset password (protected .htaccess)
│   └── test.php                  # Tester hash password
├── index.php                     # Halaman utama & routing role
├── 404.php                       # Halaman tidak ditemukan
└── README.md                     # File ini
```

---

## Arsitektur

| Layer | Penjelasan |
|---|---|
| **Shared** | `shared/` — layout, auth guard, flash messages, TaskRepository, user view |
| **Role Pages** | `admin/`, `user/`, `supervisor/` — halaman khusus per role |
| **Auth** | `auth/` — login, register, logout |
| **Config** | `config/` — koneksi DB, setup, seed |
| **Assets** | `assets/css/` & `assets/js/` — CSS dan JavaScript terpusat |

Setiap halaman memulai diri dengan `require_once __DIR__ . "/../shared/init.php"` yang menginisialisasi session, koneksi DB, dan helper. Layout halaman dikelola oleh `page_start()` / `page_end()` di `shared/layout.php`.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Frontend | HTML, CSS (Custom Design System), Vanilla JavaScript |
| Backend | PHP 7+ (Native, tanpa framework) |
| Database | MySQL / MariaDB |
| Auth | bcrypt (`password_hash` / `password_verify`) |
| Security | CSRF token, session guard, path traversal prevention |
| Server | XAMPP / Apache / any PHP server |

---

## Instalasi

### 1) Clone atau download

```bash
git clone https://github.com/username/monitoring_kerja.git
cd monitoring_kerja
```

### 2) Pindahkan ke htdocs

```bash
# XAMPP
cp -r monitoring_kerja /opt/lampp/htdocs/

# Laragon
cp -r monitoring_kerja C:/laragon/www/
```

### 3) Buat database & setup

Jalankan dari CLI:

```bash
# Setup database (buat tabel users & tasks)
php config/setup.php

# (Opsional) Seed data dummy
php config/seed.php
```

Atau import `database/database.sql` via phpMyAdmin, lalu jalankan `php config/seed.php` untuk data dummy. (Pastikan `config/setup.php` hanya diakses sekali untuk instalasi pertama, lalu batasi aksesnya.)

### 4) Buka di browser

```
http://localhost/monitoring_kerja/
```

---

## Konfigurasi Database

Pastikan `config/koneksi.php` sesuai dengan pengaturan MySQL Anda:

```php
$conn = new mysqli("127.0.0.1", "root", "", "monitoring_kerja", 3306);
```

| Field | Default | Keterangan |
|---|---|---|
| Host | `127.0.0.1` | Host MySQL |
| User | `root` | Username MySQL |
| Password | `` | Password MySQL (kosong di XAMPP default) |
| DB Name | `monitoring_kerja` | Nama database |
| Port | `3306` | Port MySQL (3306 default XAMPP) |

---

## Halaman

| Halaman | URL | Role | Keterangan |
|---|---|---|---|
| Login | `index.php` | Semua | Halaman login & register |
| Dashboard | `dashboard_user.php`, `dashboard_supervisor.php`, `dashboard_admin.php` | User/Supervisor/Admin | Dashboard per role dengan statistik |
| Data Pekerjaan | `user/task.php` | User, Admin | List tugas + filter + pencarian |
| Tambah Pekerjaan | `user/tambah_task.php` | User, Admin | Form tambah tugas baru |
| Edit Pekerjaan | `user/edit_task.php?id=X` | User, Admin | Form edit tugas |
| Hapus Pekerjaan | `user/hapus_task.php` | User, Admin | Proses hapus tugas |
| Monitoring | `supervisor/monitoring_task.php` | Supervisor | Pantau tugas tim |
| Kelola User | `admin/users.php` | Admin | CRUD pengguna & edit role |
| Laporan | `shared/laporan.php` | Semua | Rekap status pekerjaan |
| Profil | `shared/profil.php` | Semua | Edit profil & unggah foto |
| Logout | `auth/logout.php` | Semua | Keluar dari aplikasi |

---

## Role & Hak Akses

| Role | Dashboard | Tugas | Monitoring | User Mgmt | Laporan | Profil |
|---|---|---|---|---|---|---|
| **User** | ✅ | ✅ (milik sendiri) | — | — | ✅ | ✅ |
| **Supervisor** | ✅ | ✅ | ✅ (tim) | — | ✅ | ✅ |
| **Admin** | ✅ | ✅ (semua) | ✅ | ✅ | ✅ | ✅ |

---

## Database Schema

### Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK) | Auto-increment |
| `nama` | VARCHAR(100) | Nama lengkap |
| `username` | VARCHAR(50) | Unique, digunakan login |
| `email` | VARCHAR(150) | Email (unique, bisa null) |
| `password` | VARCHAR(255) | Hash bcrypt |
| `role` | ENUM | `user`, `supervisor`, `admin` |
| `foto` | VARCHAR(255) | Nama file foto profil |
| `created_at` | TIMESTAMP | Waktu pembuatan akun |

### Tabel `tasks`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK) | Auto-increment |
| `title` | VARCHAR(200) | Judul pekerjaan |
| `description` | TEXT | Deskripsi tugas |
| `assigned_to` | INT (FK) | User yang ditugaskan |
| `assigned_by` | INT (FK) | User yang membuat tugas |
| `priority` | ENUM | `low`, `medium`, `high` |
| `status` | ENUM | `pending`, `in_progress`, `completed` |
| `deadline` | DATE | Tenggat waktu |
| `created_at` | TIMESTAMP | Waktu pembuatan |

**FK Constraints:** `assigned_to` → `users(id)` ON DELETE CASCADE, `assigned_by` → `users(id)` ON DELETE SET NULL.

---

## Fitur Teknis

### Autentikasi & Security
- Password di-hash dengan bcrypt (`password_hash` / `password_verify`)
- Session guard di semua halaman — redirect ke login jika belum autentikasi
- CSRF token per session diverifikasi di setiap POST
- Path traversal dicegah pada upload foto (`basename()` sanitization)
- Role-based access control dengan `require_role()`

### Task Repository
- Satu-satunya tempat aplikasi menyentuh tabel `tasks` (`shared/TaskRepository.php`)
- Validasi input: title, description, deadline, priority, status
- Filter: status, prioritas, pencarian judul
- Pagination dengan limit/offset
- `task_countdown()` — hitung selisih hari (late/today/later/done)
- `task_overdue_count()` — hitung tugas terlambat (belum selesai & deadline terlewat)

### Layout Bersama
- `page_start("Judul", "Subjudul", "task")` — menghasilkan sidebar, topbar, skip-link
- `page_end()` — menutup layout & memuat script.js
- Sidebar navigasi berbeda per role, dengan badge overdue tugas
- Profile dropdown di topbar (Profil, Laporan, Keluar)
- Flash message: success & error

### Upload Foto Profil
- Validasi ukuran maksimal 2MB
- Format: JPG, PNG, WEBP
- Nama file digenerate (`foto_<hex8>.<ext>`) — tidak bisa XSS/traversal
- Preview gambar sebelum submit (FileReader API)

### Responsive Design
- Desktop: sidebar + tata letak penuh
- Tablet (≤1100px): grid kartu 2 kolom, chart stack
- Mobile (≤768px): sidebar stack horizontal, konten single column, tabel scroll horizontal
- `overflow-x: auto` pada tabel untuk scroll horizontal di mobile

### Database
- Setup database via CLI (`php config/setup.php`) atau browser
- Seed data dummy via CLI (`php config/seed.php`)
- Test suite (`php tests/run_tests.php`) menggunakan database terpisah `monitoring_kerja_test`
- FK constraints dengan ON DELETE CASCADE untuk integritas data

---

## Pengembangan

### Jalankan Test Suite

```bash
php tests/run_tests.php
```

Membuat database uji `monitoring_kerja_test` dan menjalankan test untuk `TaskRepository` (validate, create, find, update, delete, filter, pagination, overdue, countdown).

### Setup Database

```bash
php config/setup.php
```

Membuat database `monitoring_kerja` dan tabel `users` serta `tasks` jika belum ada.

### Seed Data

```bash
php config/seed.php
```

Membuat data user dan tugas contoh untuk pengujian dashboard.

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| Database error | Pastikan MySQL running & `config/setup.php` sudah dijalankan |
| Halaman redirect ke login | Session expired atau belum login — akses `index.php` |
| Foto profil tidak muncul | Pastikan folder `uploads/` ada dan writable (chmod 775) |
| Tampilan berantakan di mobile | Hard refresh (`Ctrl+Shift+R`) untuk clear cache CSS |
| CSRF error (419) | Muat ulang halaman dan coba lagi — session kedaluwarsa |
| "Task tidak ditemukan" | ID task salah atau tidak memiliki akses ke tugas tersebut |

---

## License

Dibuat untuk keperluan pengelolaan dan pemantauan pekerjaan tim.
