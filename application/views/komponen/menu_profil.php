<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="menu-profil-popover tersembunyi" id="popover-menu-profil" role="menu" aria-label="Menu Akun Pengguna">
  <!-- Wadah Baris Akun Pengguna + Flyout Ringkasan di Sebelah Kanan (Sesuai Permintaan) -->
  <div class="menu-profil__user-wadah" id="wadah-user-baris-popover">
    <a href="<?php echo site_url('profil'); ?>" class="menu-profil__user-baris" id="item-menu-user-baris" role="menuitem" data-aksi="buka-drawer-profil">
      <div class="menu-profil__avatar" id="popover-user-avatar"><?php echo e($user_inisial); ?></div>
      <div class="menu-profil__user-info">
        <span class="menu-profil__user-nama" id="popover-user-nama"><?php echo e($user_nama); ?></span>
        <span class="menu-profil__user-tier" id="popover-user-tier"><?php echo e($user_tier); ?></span>
      </div>
      <span class="menu-profil__panah-kanan" aria-hidden="true">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </span>
    </a>

    <!-- Flyout Ringkasan Profil Awal di Sebelah Kanan (Muncul saat kursor di kotak akun pengguna > tapi belum di-klik) -->
    <div class="flyout-ringkasan-profil tersembunyi" id="flyout-ringkasan-profil" role="tooltip" aria-label="Ringkasan Profil Awal" data-aksi="buka-drawer-profil">
      <div class="flyout-ringkasan__isi">
        <!-- Bagian Atas: Avatar, Nama, Handle, Email -->
        <div class="flyout-ringkasan__kartu-header">
          <div class="flyout-ringkasan__avatar"><?php echo e($user_inisial); ?></div>
          <div class="flyout-ringkasan__identitas">
            <div class="flyout-ringkasan__nama"><?php echo e($user_nama); ?></div>
            <div class="flyout-ringkasan__email"><?php echo e($user_email); ?></div>
          </div>
        </div>

        <!-- 4 Kolom Metrik Statistik Persis Ringkasan Awal Gambar 2 -->
        <div class="flyout-ringkasan__stats-grid">
          <div class="flyout-ringkasan__stat-item">
            <div class="flyout-ringkasan__stat-angka"><?php echo isset($statistik['total_token']) ? number_format($statistik['total_token'], 0, ',', '.') : '0'; ?></div>
            <div class="flyout-ringkasan__stat-label">Total token</div>
          </div>
          <div class="flyout-ringkasan__stat-item">
            <div class="flyout-ringkasan__stat-angka"><?php echo isset($statistik['token_puncak']) ? number_format($statistik['token_puncak'], 0, ',', '.') : '0'; ?></div>
            <div class="flyout-ringkasan__stat-label">Token puncak</div>
          </div>
          <div class="flyout-ringkasan__stat-item">
            <div class="flyout-ringkasan__stat-angka"><?php echo isset($statistik['rekor_terlama']) ? (int) $statistik['rekor_terlama'] : 0; ?> hari</div>
            <div class="flyout-ringkasan__stat-label">Rekor terlama</div>
          </div>
          <div class="flyout-ringkasan__stat-item">
            <div class="flyout-ringkasan__stat-angka"><?php echo isset($statistik['hari_aktif_beruntun']) ? (int) $statistik['hari_aktif_beruntun'] : 0; ?> hari</div>
            <div class="flyout-ringkasan__stat-label">Hari aktif beruntun</div>
          </div>
        </div>

        <!-- Bagian Bawah: Paket & Petunjuk Klik Buka Drawer Samping -->
        <div class="flyout-ringkasan__footer">
          <div class="flyout-ringkasan__status">
            <span class="flyout-ringkasan__titik-aktif"></span>
            <span class="flyout-ringkasan__paket"><?php echo e($user_tier); ?> Plan</span>
          </div>
          <span class="flyout-ringkasan__buka-teks">Buka panel profil &rarr;</span>
        </div>
      </div>
    </div>
  </div>

  <div class="menu-profil__pembatas"></div>

  <!-- 1. Upgrade Paket -->
  <button type="button" class="menu-profil__item" id="item-menu-upgrade" role="menuitem">
    <span class="menu-profil__ikon" aria-hidden="true">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2l2.4 7.2L22 12l-7.6 2.8L12 22l-2.4-7.2L2 12l7.6-2.8z"></path>
      </svg>
    </span>
    <span class="menu-profil__label">Upgrade paket</span>
  </button>

  <!-- 2. Tagihan (Menggantikan Personalisasi) -->
  <button type="button" class="menu-profil__item" id="item-menu-tagihan" role="menuitem">
    <span class="menu-profil__ikon" aria-hidden="true">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
        <line x1="2" y1="10" x2="22" y2="10"></line>
      </svg>
    </span>
    <span class="menu-profil__label">Tagihan</span>
  </button>

  <!-- 3. Profil (Membuka Drawer Kanan) -->
  <a href="<?php echo site_url('profil'); ?>" class="menu-profil__item" id="item-menu-profil" role="menuitem" data-aksi="buka-drawer-profil">
    <span class="menu-profil__ikon" aria-hidden="true">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
    </span>
    <span class="menu-profil__label">Profil</span>
  </a>

  <!-- 4. Penggunaan (Menggantikan Pengaturan) -->
  <button type="button" class="menu-profil__item" id="item-menu-penggunaan" role="menuitem">
    <span class="menu-profil__ikon" aria-hidden="true">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="20" x2="18" y2="10"></line>
        <line x1="12" y1="20" x2="12" y2="4"></line>
        <line x1="6" y1="20" x2="6" y2="14"></line>
      </svg>
    </span>
    <span class="menu-profil__label">Penggunaan</span>
  </button>

  <div class="menu-profil__pembatas"></div>

  <!-- 5. Bantuan -->
  <button type="button" class="menu-profil__item" id="item-menu-bantuan" role="menuitem">
    <span class="menu-profil__ikon" aria-hidden="true">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <circle cx="12" cy="12" r="4"></circle>
        <line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line>
        <line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line>
        <line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line>
        <line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line>
      </svg>
    </span>
    <span class="menu-profil__label">Bantuan</span>
    <span class="menu-profil__item-panah" aria-hidden="true">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"></polyline>
      </svg>
    </span>
  </button>

  <!-- 6. Keluar -->
  <form action="<?php echo site_url('keluar'); ?>" method="post" class="menu-profil__form-keluar">
    <input type="hidden" name="<?php echo e($this->security->get_csrf_token_name()); ?>" value="<?php echo e($this->security->get_csrf_hash()); ?>">
    <button type="submit" class="menu-profil__item" role="menuitem">
      <span class="menu-profil__ikon" aria-hidden="true">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </span>
      <span class="menu-profil__label">Keluar</span>
    </button>
  </form>
</div>
