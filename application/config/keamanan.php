<?php
/**
 * PRDAI — Konfigurasi keamanan dan batas (menghindari angka "ajaib").
 *
 * Semua nilai batas, panjang, dan jendela waktu dikumpulkan di sini agar mudah
 * ditinjau dan diubah tanpa menyentuh logika controller/model.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// --- Kata sandi & akun ---
$config['sandi_panjang_min'] = 10;
$config['sandi_panjang_maks'] = 72;
$config['nama_panjang_min'] = 2;
$config['nama_panjang_maks'] = 100;
$config['email_panjang_maks'] = 190;

// --- Batas laju daftar: maksimal N pendaftaran per jam per IP ---
$config['daftar_batas_jumlah'] = 5;
$config['daftar_jendela_detik'] = 3600;

// --- Batas laju masuk: maksimal N kegagalan per jendela per email+IP ---
$config['masuk_gagal_maks'] = 5;
$config['masuk_jendela_detik'] = 900; // 15 menit
$config['masuk_kunci_detik'] = 900;   // durasi kunci 15 menit

// --- Panjang input wizard (sesuai Bagian 8.4) ---
$config['wizard'] = [
    'nama_produk_min'        => 3,
    'nama_produk_maks'       => 100,
    'deskripsi_singkat_min'  => 20,
    'deskripsi_singkat_maks' => 500,
    'masalah_min'            => 20,
    'masalah_maks'           => 1000,
    'target_pengguna_min'    => 10,
    'target_pengguna_maks'   => 500,
    'tujuan_maks_butir'      => 5,
    'tujuan_butir_min'       => 5,
    'tujuan_butir_maks'      => 200,
    'fitur_maks_butir'       => 15,
    'fitur_butir_min'        => 3,
    'fitur_butir_maks'       => 200,
    'teknologi_maks'         => 200,
    'halaman_maks'           => 500,
    'catatan_tampilan_maks'  => 300,
];

// --- PRD & versi ---
$config['prd'] = [
    'judul_maks'         => 150,
    'isi_maks_karakter'  => 200000,
    'versi_maks_per_prd' => 50,
    'cari_panjang_maks'  => 100,
    'per_halaman'        => 10,
    'pratinjau_maks'     => 200000,
    'unduh_isi_maks'     => 150000,
];

// --- Verifikasi keluaran AI ---
$config['ai_keluaran_min_karakter'] = 500;
$config['ai_keluaran_maks_karakter'] = 150000;