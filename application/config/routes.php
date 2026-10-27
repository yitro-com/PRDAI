<?php
/**
 * PRDAI — Definisi rute URL (seluruhnya Bahasa Indonesia).
 *
 * Urutan penting: rute dengan segmen statis (prd/buat, prd/hasilkan, prd/pratinjau)
 * HARUS ditulis sebelum rute berkode agar tidak tertutup oleh pola kode acak.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// Kontroler bawaan.
$route['default_controller'] = 'beranda';

// Halaman galat kustom (404 diarahkan ke controller Galat).
$route['404_override'] = 'galat/tidak_ditemukan';
$route['translate_uri_dashes'] = FALSE;

// --- Beranda ---
$route[''] = 'beranda/index';

// --- Autentikasi ---
$route['daftar'] = 'autentikasi/daftar';
$route['masuk'] = 'autentikasi/masuk';
$route['keluar'] = 'autentikasi/keluar';
$route['sosial/(:any)'] = 'autentikasi/sosial/$1';
$route['autentikasi/sosial/(:any)'] = 'autentikasi/sosial/$1';
$route['autentikasi/proses_sosial'] = 'autentikasi/proses_sosial';
$route['autentikasi/oauth_callback/(:any)'] = 'autentikasi/oauth_callback/$1';
$route['autentikasi/beralih/(:num)'] = 'autentikasi/beralih/$1';

// --- Dasbor ---
$route['dasbor'] = 'dasbor/index';

// --- Profil ---
$route['profil'] = 'profil/index';
$route['profil/sandi'] = 'profil/sandi';

// --- Halaman galat ---
$route['akses-ditolak'] = 'galat/akses_ditolak';

// --- PRD: rute statis (harus lebih dulu daripada rute berkode) ---
$route['prd/buat'] = 'prd/buat';
$route['prd/hasilkan'] = 'prd/hasilkan';
$route['prd/pratinjau'] = 'prd/pratinjau';
$route['prd/ambil_json/([a-zA-Z0-9]{16})'] = 'prd/ambil_json/$1';

// --- PRD: rute berkode 16 karakter ---
$route['prd/([a-zA-Z0-9]{16})/ubah'] = 'prd/ubah/$1';
$route['prd/([a-zA-Z0-9]{16})/versi'] = 'prd/versi/$1';
$route['prd/([a-zA-Z0-9]{16})/pulihkan/(:num)'] = 'prd/pulihkan/$1/$2';
$route['prd/([a-zA-Z0-9]{16})/unduh'] = 'prd/unduh/$1';
$route['prd/([a-zA-Z0-9]{16})/hapus'] = 'prd/hapus/$1';
$route['prd/([a-zA-Z0-9]{16})'] = 'beranda/index/$1';