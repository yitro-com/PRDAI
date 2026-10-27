<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Komponen Bagian Karya Unggulan (Etalase Profil).
 * Menampilkan grafis bertumpuk, kartu etalase, dan tombol peningkatan paket.
 */
?>
<section class="profil-seksi" aria-label="Karya Unggulan">
  <h2 class="profil-seksi-judul">Karya Unggulan</h2>

  <div class="profil-etalase-kotak">
    <!-- Grafis Kartu & Ikon Web Globe -->
    <div class="profil-etalase-grafis" aria-hidden="true">
      <div class="etalase-kartu-layer etalase-kartu-layer--kiri"></div>
      <div class="etalase-kartu-layer etalase-kartu-layer--kanan"></div>
      <div class="etalase-kartu-layer etalase-kartu-layer--tengah">
        <svg class="etalase-globe-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="2" y1="12" x2="22" y2="12"></line>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
        </svg>
        <span class="etalase-www-teks">www</span>
      </div>
    </div>

    <p class="profil-etalase-deskripsi">Tampilkan situs di profil Anda</p>

    <button type="button" class="profil-etalase-btn" id="tombol-etalase-tingkatkan">
      Tingkatkan paket untuk mengedit etalase Anda
    </button>
  </div>
</section>
