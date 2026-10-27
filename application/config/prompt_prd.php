<?php
/**
 * PRDAI — Konfigurasi Prompt PRD AI.
 *
 * System prompt dimuat saat runtime dari berkas terpisah application/prompts/system_prompt.md
 * sesuai Bagian A sampai G & H dari Aturan Perilaku AI.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$berkas_prompt = APPPATH . 'prompts/system_prompt.md';
if (file_exists($berkas_prompt)) {
    $config['prompt_sistem'] = file_get_contents($berkas_prompt);
} else {
    $config['prompt_sistem'] = 'Kamu adalah asisten PRDAI. Jawab pertanyaan pengguna dengan benar, relevan, dan langsung ke inti. Membantu menyusun PRD yang rinci dan terstruktur untuk Google Antigravity hanya jika diminta.';
}

// 17 Bagian Wajib PRD (Sesuai instruksi standar kualitas terbaru)
$config['bagian_wajib'] = array(
    'Ringkasan dokumen',
    'Latar belakang dan masalah',
    'Tujuan produk dan indikator keberhasilan',
    'Ruang lingkup dan batasan',
    'Pengguna sasaran dan hak akses',
    'Aturan bisnis dan kebutuhan fungsional',
    'Alur pengguna dan alur sistem',
    'Kebutuhan nonfungsional',
    'Keamanan, privasi, dan pengelolaan data',
    'Model data dan penyimpanan',
    'Kebutuhan UI/UX dan keadaan antarmuka',
    'Arsitektur teknis, integrasi, dan ketergantungan',
    'Asumsi, batasan, dan pertanyaan terbuka',
    'Risiko dan mitigasi',
    'Strategi pengujian dan kriteria kelulusan',
    'Tahapan implementasi: Rencana Pengerjaan dan Roadmap Bertahap (Fase, Fitur, dan Sub-Fitur)',
    'Kriteria kesiapan implementasi dan definisi selesai',
);