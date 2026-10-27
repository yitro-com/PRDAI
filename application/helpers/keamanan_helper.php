<?php
/**
 * PRDAI — Helper keamanan.
 *
 * Kumpulan fungsi kecil murni untuk menjaga keamanan keluaran dan identitas
 * permintaan. Tidak menyentuh basis data agar mudah diuji.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('e'))
{
    /**
     * Escape teks untuk keluaran HTML (pembungkus html_escape).
     *
     * Kenapa: seluruh data pengguna yang tampil di view WAJIB lewat fungsi ini
     * agar pesan balasan maupun isi PRD tidak bisa menyuntikkan skrip (XSS).
     */
    function e($teks)
    {
        return html_escape($teks);
    }
}

if ( ! function_exists('buat_kode_acak'))
{
    function buat_kode_acak($panjang = 16)
    {
        $alfabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maks    = strlen($alfabet) - 1;
        $kode    = '';

        for ($i = 0; $i < $panjang; $i++)
        {
            $kode .= $alfabet[random_int(0, $maks)];
        }

        return $kode;
    }
}

if ( ! function_exists('ambil_ip'))
{
    /**
     * Ambil alamat IP pengguna tanpa memercayai header proxy sembarangan.
     *
     * Kenapa: IP dipakai untuk batas laju dan log. Kita HANYA memakai
     * REMOTE_ADDR karena header X-Forwarded-For bisa dipalsukan bila server
     * tidak berada di belakang proxy yang sah (setelan proxy_ips di config).
     */
    function ambil_ip()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

        // Batasi panjang agar sesuai kolom VARCHAR(45).
        return substr($ip, 0, 45);
    }
}

if ( ! function_exists('buang_karakter_kontrol'))
{
    /**
     * Buang karakter kontrol (kecuali spasi, tab, dan baris baru) dari teks.
     *
     * Kenapa: masukan pengguna dipakai untuk menyusun prompt AI dan disimpan ke
     * DB; karakter kontrol dapat merusak keluaran atau menyamarkan serangan.
     */
    function buang_karakter_kontrol($teks)
    {
        $teks = (string) $teks;

        // Hapus semua karakter kontrol ASCII kecuali \t (09), \n (0A), \r (0D).
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $teks);
    }
}
