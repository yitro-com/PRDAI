<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Komponen Bagian Bawah Profil (Sesuai Permintaan Pengguna):
 * "dan paling bawahnya itu bisa edit nama ganti password"
 *
 * Terdiri dari:
 * 1. Formulir Perubahan Nama Akun (POST ke profil)
 * 2. Formulir Pembaruan Kata Sandi Akun (POST ke profil/sandi)
 */
?>
<section class="profil-seksi" aria-label="Pengaturan Identitas dan Keamanan">
  <h2 class="profil-seksi-judul">Pengaturan Identitas & Keamanan Akun</h2>

  <div class="profil-keamanan-grid">
    <!-- Panel 1: Ubah Nama Akun -->
    <div class="profil-panel-kartu">
      <div class="profil-panel-kepala">
        <h3 class="profil-panel-judul">Edit Informasi Profil</h3>
        <p class="profil-panel-deskripsi">Perbarui nama lengkap yang ditampilkan pada dokumen PRD dan antarmuka.</p>
      </div>

      <?php echo form_open('profil', array('id' => 'form-edit-nama', 'class' => 'profil-form-isi')); ?>
        <div class="profil-form-bidang-kelompok">
          <div class="profil-form-grup">
            <label for="masukan-profil-nama" class="profil-form-label">Nama Lengkap</label>
            <input
              type="text"
              id="masukan-profil-nama"
              name="nama"
              class="profil-input-teks"
              value="<?php echo set_value('nama', $pengguna->nama); ?>"
              required
              maxlength="100"
            >
            <?php if (form_error('nama')): ?>
              <span class="pesan-galat-bidang"><?php echo form_error('nama'); ?></span>
            <?php else: ?>
              <span class="profil-bantuan-teks">Nama yang akan ditampilkan pada profil dan dokumen PRD Anda.</span>
            <?php endif; ?>
          </div>

          <div class="profil-form-grup">
            <label for="masukan-profil-email" class="profil-form-label">Email Terdaftar</label>
            <input
              type="email"
              id="masukan-profil-email"
              class="profil-input-teks"
              value="<?php echo e($pengguna->email); ?>"
              disabled
              readonly
            >
            <span class="profil-bantuan-teks">Alamat email terkunci demi integritas keamanan akun.</span>
          </div>

          <div class="profil-form-grup">
            <label for="masukan-profil-telepon" class="profil-form-label">Nomor Telepon</label>
            <input
              type="tel"
              id="masukan-profil-telepon"
              name="telepon"
              class="profil-input-teks"
              value="<?php echo set_value('telepon', isset($pengguna->telepon) ? $pengguna->telepon : ''); ?>"
              placeholder="Contoh: 081234567890"
              maxlength="25"
            >
            <span class="profil-bantuan-teks">Nomor kontak aktif untuk verifikasi dan pemulihan keamanan akun.</span>
          </div>
        </div>

        <button type="submit" class="profil-btn-simpan">
          Simpan Perubahan Identitas
        </button>
      <?php echo form_close(); ?>
    </div>

    <!-- Panel 2: Ganti Kata Sandi (dengan Ikon Mata Toggle Sandi) -->
    <div class="profil-panel-kartu">
      <div class="profil-panel-kepala">
        <h3 class="profil-panel-judul">Ganti Kata Sandi</h3>
        <p class="profil-panel-deskripsi">Perbarui kata sandi akun secara berkala untuk menjaga keamanan akses Anda.</p>
      </div>

      <?php echo form_open('profil/sandi', array('id' => 'form-ganti-sandi', 'class' => 'profil-form-isi')); ?>
        <div class="profil-form-bidang-kelompok">
          <div class="profil-form-grup">
            <label for="masukan-sandi-lama" class="profil-form-label">Kata Sandi Saat Ini</label>
            <div class="profil-sandi-wadah">
              <input
                type="password"
                id="masukan-sandi-lama"
                name="sandi_lama"
                class="profil-input-teks profil-input-teks--sandi"
                autocomplete="current-password"
                required
              >
              <button type="button" class="btn-toggle-mata" data-target="masukan-sandi-lama" aria-label="Tampilkan atau sembunyikan kata sandi" title="Tampilkan sandi">
                <svg class="ikon-mata-buka" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="ikon-mata-tutup tersembunyi" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
            <span class="profil-bantuan-teks">Masukkan kata sandi yang sedang Anda gunakan saat ini.</span>
          </div>

          <div class="profil-form-grup">
            <label for="masukan-sandi-baru" class="profil-form-label">Kata Sandi Baru</label>
            <div class="profil-sandi-wadah">
              <input
                type="password"
                id="masukan-sandi-baru"
                name="sandi_baru"
                class="profil-input-teks profil-input-teks--sandi"
                autocomplete="new-password"
                required
              >
              <button type="button" class="btn-toggle-mata" data-target="masukan-sandi-baru" aria-label="Tampilkan atau sembunyikan kata sandi" title="Tampilkan sandi">
                <svg class="ikon-mata-buka" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="ikon-mata-tutup tersembunyi" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
            <span class="profil-bantuan-teks">Minimal 10 karakter dengan kombinasi angka atau simbol.</span>
          </div>

          <div class="profil-form-grup">
            <label for="masukan-konfirmasi-sandi" class="profil-form-label">Ulangi Kata Sandi Baru</label>
            <div class="profil-sandi-wadah">
              <input
                type="password"
                id="masukan-konfirmasi-sandi"
                name="konfirmasi_sandi"
                class="profil-input-teks profil-input-teks--sandi"
                autocomplete="new-password"
                required
              >
              <button type="button" class="btn-toggle-mata" data-target="masukan-konfirmasi-sandi" aria-label="Tampilkan atau sembunyikan kata sandi" title="Tampilkan sandi">
                <svg class="ikon-mata-buka" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="ikon-mata-tutup tersembunyi" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
            <span class="profil-bantuan-teks">Ketik ulang kata sandi baru untuk memastikan kesamaan.</span>
          </div>
        </div>

        <button type="submit" class="profil-btn-simpan">
          Perbarui Kata Sandi
        </button>
      <?php echo form_close(); ?>
    </div>
  </div>
</section>
