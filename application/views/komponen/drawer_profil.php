<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- Backdrop Gelap Drawer Profil (Hanya aktif di mobile kecil) -->
<div class="drawer-profil-backdrop tersembunyi" id="backdrop-drawer-profil" aria-hidden="true"></div>

<aside class="drawer-profil tersembunyi" id="drawer-profil" role="dialog" aria-modal="true" aria-label="Profil Pengguna">
  <div class="drawer-profil__header">
    <div class="drawer-profil__header-aksi">
      <button type="button" class="drawer-profil__btn-tutup" id="tombol-tutup-drawer-profil" aria-label="Tutup Panel Profil" title="Tutup (Esc)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>

  <!-- Konten Scrollable Panel Profil -->
  <div class="drawer-profil__isi" id="konten-drawer-profil">
    <?php $this->load->view('profil/bagian_kartu'); ?>
    <div class="drawer-profil__bawah-konten">
      <?php $this->load->view('profil/bagian_aktivitas'); ?>
      <?php $this->load->view('profil/bagian_keamanan'); ?>
    </div>
  </div>
</aside>
