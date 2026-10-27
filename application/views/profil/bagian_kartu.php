<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Komponen Bagian Atas Profil (Sesuai Gambar 2).
 * Kotak profil dengan latar belakang bacroundProfil.jpg,
 * avatar besar, nama pengguna, handle, dan 4 kolom metrik statistik.
 */
?>
<section class="profil-kartu-atas" aria-label="Informasi Utama Pengguna">
  <div class="profil-kartu-atas__isi">
    <!-- Avatar Besar -->
    <div class="profil-avatar-besar" aria-hidden="true">
      <?php echo e($user_inisial); ?>
    </div>

    <!-- Nama Lengkap Pengguna -->
    <h1 class="profil-nama-utama"><?php echo e($pengguna->nama); ?></h1>

    <!-- Email Pengguna yang Login -->
    <div class="profil-handle"><?php echo e($pengguna->email); ?></div>

    <!-- Kotak 4 Metrik Statistik -->
    <div class="profil-stats-grid">
      <!-- 1. Total Token -->
      <div class="profil-stat-item">
        <div class="profil-stat-angka">
          <?php echo number_format($statistik['total_token'], 0, ',', '.'); ?>
        </div>
        <div class="profil-stat-label">Total token</div>
      </div>

      <!-- 2. Token Puncak -->
      <div class="profil-stat-item">
        <div class="profil-stat-angka">
          <?php echo number_format($statistik['token_puncak'], 0, ',', '.'); ?>
        </div>
        <div class="profil-stat-label">Token puncak</div>
      </div>

      <!-- 3. Rekor Terlama -->
      <div class="profil-stat-item">
        <div class="profil-stat-angka">
          <?php echo (int) $statistik['rekor_terlama']; ?> hari
        </div>
        <div class="profil-stat-label">Rekor terlama</div>
      </div>

      <!-- 4. Hari Aktif Beruntun Saat Ini -->
      <div class="profil-stat-item">
        <div class="profil-stat-angka">
          <?php echo (int) $statistik['hari_aktif_beruntun']; ?> hari
        </div>
        <div class="profil-stat-label">Hari aktif beruntun saat ini</div>
      </div>
    </div>

    <!-- Bagian Karya Unggulan (Termasuk di dalam latar belakang bacroundProfil.jpg) -->
    <div class="profil-wadah-etalase-dalam">
      <?php $this->load->view('profil/bagian_etalase'); ?>
    </div>
  </div>
</section>
