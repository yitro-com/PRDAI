<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRDAI — Tampilan Utama Halaman Profil (Sesuai Gambar 2).
 * Dipecah rapi ke dalam partial modular terpisah untuk kemudahan pemeliharaan:
 * 1. bagian_kartu: Kotak profil atas dengan background bacroundProfil.jpg & 4 statistik
 * 2. bagian_etalase: Bagian karya unggulan
 * 3. bagian_aktivitas: Aktivitas token heatmap (hijau jika ada aktivitas)
 * 4. bagian_keamanan: Paling bawah untuk edit nama dan ganti password
 */
?>
<main class="halaman-profil-wadah">
  <!-- Bilah Navigasi Atas -->
  <div class="profil-bilah-atas">
    <a href="<?php echo site_url('/'); ?>" class="profil-tombol-kembali" title="Kembali ke Ruang Kerja Beranda">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      <span>Kembali ke Ruang Kerja</span>
    </a>
  </div>

  <!-- Notifikasi Pesan Kilat Sukses / Galat -->
  <?php $this->load->view('komponen/pesan_kilat'); ?>

  <!-- 1. Bagian Kotak Profil Atas & Karya Unggulan (Menyatu dalam latar bacroundProfil.jpg) -->
  <?php $this->load->view('profil/bagian_kartu'); ?>

  <!-- 2. Bagian Aktivitas Token (Heatmap Hijau) -->
  <?php $this->load->view('profil/bagian_aktivitas'); ?>

  <!-- 4. Bagian Paling Bawah: Edit Nama & Ganti Password -->
  <?php $this->load->view('profil/bagian_keamanan'); ?>

  <!-- Modal Pendukung -->
  <?php $this->load->view('komponen/modal_tingkatkan'); ?>
  <?php $this->load->view('komponen/modal_tagihan'); ?>
  <?php $this->load->view('komponen/modal_penggunaan'); ?>
  <?php $this->load->view('komponen/modal_bantuan'); ?>
</main>
