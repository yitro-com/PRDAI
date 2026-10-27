<?php
/**
 * PRDAI — Gerbang Keamanan & Pengalihan Direktori Akar.
 *
 * Mencegah pengguna membaca struktur folder jika mengakses direktori akar http://localhost/PRDAI/
 * dan mengalihkan langsung ke halaman login (public/masuk).
 */
$protokol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$jalur_aplikasi = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$url_masuk = $protokol . $host . $jalur_aplikasi . '/public/masuk';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $url_masuk, true, 302);
exit;
