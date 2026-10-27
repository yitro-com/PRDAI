# PRDAI — Pembuat PRD Berbasis AI

Website penyusun **Product Requirements Document (PRD)** berbasis AI yang dirancang untuk menghasilkan dokumen Markdown lengkap, terstruktur, dan siap dikerjakan oleh AI coding agent (seperti Cline) maupun pengembang perangkat lunak manusia.

Dibangun dengan **CodeIgniter 3.1.13 (PHP)**, antarmuka murni **Vanilla HTML/CSS/JavaScript**, serta seluruh antarmuka dan dokumen dalam **Bahasa Indonesia**.

---

## 1. Kebutuhan Sistem

- **PHP**: 7.4 s.d. 8.2 (kompatibel 8.4) dengan ekstensi `curl`, `mbstring`, `mysqli`, `json`, `openssl`.
- **Basis Data**: MySQL 5.7+ atau MariaDB 10.4+ (Engine InnoDB, charset `utf8mb4`).
- **Web Server**: Apache 2.4 (dengan `mod_rewrite` aktif) atau Nginx.
- **Composer**: Untuk dependensi `vlucas/phpdotenv` dan `erusev/parsedown`.

---

## 2. Langkah Pemasangan

### 1. Salin Proyek & Unduh Dependensi
```bash
git clone <url-repositori> prdai
cd prdai
composer install --no-dev --optimize-autoloader
```

### 2. Konfigurasi Lingkungan (`.env`)
Salin berkas `.env.contoh` menjadi `.env`:
```bash
cp .env.contoh .env
```
Buka berkas `.env` dan sesuaikan nilainya:
- `APP_ENV`: `development` untuk pengembangan lokal, atau `production` untuk live.
- `APP_URL`: Alamat URL aplikasi (contoh XAMPP: `http://localhost/PRDAI/public/`).
- `DB_HOST`, `DB_NAMA`, `DB_PENGGUNA`, `DB_KATA_SANDI`: Kredensial MySQL.
- `ENCRYPTION_KEY`: Kunci acak 32 karakter hexadecimal (buat dengan `openssl rand -hex 32`).
- `AI_PENYEDIA`: `anthropic`, `openai`, atau `gemini`.
- `AI_API_KEY`: Kunci API penyedia AI pilihan Anda.
- `AI_MODEL`: Model LLM (contoh: `claude-3-5-sonnet-20241022`, `gpt-4o-mini`, atau `gemini-1.5-flash`).

### 3. Impor Skema Basis Data
Impor seluruh tabel dari berkas `basis_data/skema.sql`:
```bash
mysql -u root -p prdai < basis_data/skema.sql
```
Tabel-tabel yang dibuat:
- `pengguna`: Akun pengguna dan peran.
- `prd`: Dokumen PRD utama dan status penyusunan.
- `prd_versi`: Riwayat perubahan dan versi dokumen (maks 50 per PRD).
- `percobaan_masuk`: Log percobaan login untuk mitigasi brute force.
- `batas_laju`: Rate limiting IP registrasi dan mutex kunci AI.
- `penggunaan_ai`: Pencatatan konsumsi token dan status panggilan AI.
- `log_aktivitas`: Jejak audit peristiwa autentikasi dan data.
- `ci_sessions`: Penyimpanan sesi web terenkripsi di basis data.

---

## 3. Konfigurasi Web Server

### Apache (XAMPP / Standar)
Pastikan berkas `public/.htaccess` aktif dan modul `mod_rewrite` diaktifkan:
```apache
DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/PRDAI/public"
<Directory "/Applications/XAMPP/xamppfiles/htdocs/PRDAI/public">
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Nginx
```nginx
server {
    listen 80;
    server_name prdai.local;
    root /path/to/prdai/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(env|git|htaccess) {
        deny all;
    }
}
```

---

## 4. Struktur Folder & Tanggung Jawab

```
PRDAI/
├── .env                       # Rahasia & konfigurasi (tidak di-commit)
├── .env.contoh                # Templat konfigurasi lingkungan
├── composer.json              # Dependensi PHP
├── prd.md                     # Dokumen spesifikasi PRD asli
├── basis_data/
│   └── skema.sql              # DDL lengkap basis data InnoDB utf8mb4
├── system/                    # Inti CodeIgniter 3.1.13
├── application/
│   ├── config/                # routes.php, keamanan.php, prompt_prd.php, config.php
│   ├── core/                  # MY_Controller, Pengontrol_Tamu, Pengontrol_Masuk
│   ├── controllers/           # Beranda, Autentikasi, Dasbor, Prd, Profil, Galat
│   ├── models/                # Pengguna_model, Prd_model, Prd_versi_model, dll.
│   ├── libraries/             # Klien_ai, Penyusun_prompt, Pembatas_laju, Pemroses_markdown
│   ├── helpers/               # keamanan_helper, tampilan_helper, respons_helper
│   ├── hooks/                 # Header_keamanan (CSP, nosniff, DENY)
│   ├── language/indonesian/   # Terjemahan teks pesan dan validasi
│   └── views/                 # View bersih bebas inline CSS & JS
└── public/                    # SATU-SATUNYA folder yang diakses publik
    ├── index.php              # Pintu masuk CI3
    ├── .htaccess              # Perlindungan berkas sensitif & rewrite
    └── assets/
        ├── css/               # dasar/, komponen/, halaman/
        ├── js/                # inti/, komponen/, halaman/
        └── vendor/mermaid/    # mermaid.min.js lokal
```

---

## 5. Keputusan Teknis (Technical Decisions)

1. **Bebas Library CSS/JS Berat & Bebas CDN:**
   - Tidak menggunakan Bootstrap, Tailwind, atau jQuery. Seluruh tata letak menggunakan Vanilla CSS berbasis token desain (`variabel.css`) dan Vanilla ES6 JavaScript murni.
   - Semua aset dimuat lokal (`public/assets/`) tanpa dependensi ke CDN pihak ketiga demi privasi dan kepatuhan Content Security Policy (`script-src 'self'`).
2. **Desain Tenang & Bebas Polusi Ikon:**
   - Mengikuti instruksi perancangan manusia profesional: navigasi dan tombol menggunakan teks yang jelas, hierarki tipografi sistem, rasio kontras WCAG AA, tanpa tumpukan ikon dekoratif yang tidak perlu.
3. **Keamanan Berlapis (Defense in Depth):**
   - **XSS**: Markdown diurai lewat Parsedown dalam Safe Mode (`setSafeMode(true)`). Seluruh keluaran view di-escape via `e()`.
   - **CSRF**: Perlindungan CSRF aktif dengan rotasi token (`csrf_regenerate = TRUE`) yang disinkronkan otomatis oleh `csrf.js` dan `api.js` pada setiap respons JSON.
   - **IDOR**: Akses PRD menggunakan kode acak kriptografis 16 karakter (`buat_kode_acak(16)`), dan semua query wajib memfilter ID pengguna aktif dari sesi basis data.
   - **Brute Force & Enumerasi Akun**: Maksimal 5 percobaan gagal dalam 15 menit per kombinasi email+IP memicu penguncian sementara. Pesan galat pendaftaran dan login dibuat generik tanpa membocorkan keberadaan akun.
   - **Timing Attack Defense**: Verifikasi kata sandi menjalankan hash pembanding tiruan ketika email tidak ditemukan di basis data untuk menjaga waktu respons server seragam.
   - **SSRF & Prompt Injection**: URL API AI dikunci statis di server; masukan pengguna dibungkus dalam tag `<masukan_pengguna>` dengan pembersihan kurung pembatas dan karakter kontrol.
   - **Penanganan PHP 8.4**: Deprecation warnings ditekan sebelum autoloader Composer di `public/index.php` untuk mencegah pengiriman header HTTP sebelum inisialisasi sesi.

---

## 6. Panduan Menelusuri Galat (Troubleshooting)

| Gejala | Penyebab Umum | Penanganan |
|---|---|---|
| Halaman 500 saat dibuka | Sesi atau DB belum terhubung | Periksa kredensial MySQL di `.env` dan pastikan tabel `ci_sessions` ada di database `prdai`. |
| 403 Forbidden pada Form/AJAX | Token CSRF kedaluwarsa atau hilang | Muat ulang halaman, atau periksa apakah form mengirim input tersembunyi `prdai_token`. |
| Gagal Menyusun PRD | Kuota harian habis, API Key kosong, atau batas waktu cURL | Periksa `AI_API_KEY` di `.env` dan tinjau berkas log di `application/logs/`. |
| 404 pada URL PRD | Akses bukan pemilik dokumen atau dokumen telah dihapus lunak | Pastikan Anda masuk dengan akun pemilik PRD terkait. |

Berkas log terperinci tersimpan di:
`application/logs/log-YYYY-MM-DD.php`
Log ini hanya memuat ringkasan modul dan aksi tanpa pernah mencatat kata sandi, token CSRF, atau kunci API.
