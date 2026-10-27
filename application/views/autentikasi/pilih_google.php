<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="oauth-latar">
  <div class="google-card">
    
    <!-- Logo Google Berwarna Asli -->
    <div class="google-card__header">
      <svg class="google-logo-svg" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
      </svg>
      <h1 class="google-card__judul">Pilih akun</h1>
      <p class="google-card__subjudul">untuk melanjutkan ke <span class="google-card__app-nama">PRDAI</span></p>
    </div>

    <!-- Daftar Pilihan Akun Google -->
    <div class="google-akun-list">
      
      <!-- Akun 1: Yitro Alexander -->
      <?php echo form_open('autentikasi/proses_sosial', array('class' => 'google-akun-form')); ?>
        <input type="hidden" name="provider" value="google">
        <input type="hidden" name="nama" value="Yitro Alexander">
        <input type="hidden" name="email" value="alexander@gmail.com">
        <button type="submit" class="google-akun-item" id="btn-google-akun-1" title="Masuk sebagai Yitro Alexander">
          <div class="google-avatar google-avatar--blue">Y</div>
          <div class="google-akun-info">
            <div class="google-akun-nama">Yitro Alexander</div>
            <div class="google-akun-email">alexander@gmail.com</div>
          </div>
          <div class="google-akun-panah">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </div>
        </button>
      <?php echo form_close(); ?>

      <!-- Akun 2: Alexander Palulun -->
      <?php echo form_open('autentikasi/proses_sosial', array('class' => 'google-akun-form')); ?>
        <input type="hidden" name="provider" value="google">
        <input type="hidden" name="nama" value="Alexander Palulun">
        <input type="hidden" name="email" value="palulun.yitro@gmail.com">
        <button type="submit" class="google-akun-item" id="btn-google-akun-2" title="Masuk sebagai Alexander Palulun">
          <div class="google-avatar google-avatar--purple">A</div>
          <div class="google-akun-info">
            <div class="google-akun-nama">Alexander Palulun</div>
            <div class="google-akun-email">palulun.yitro@gmail.com</div>
          </div>
          <div class="google-akun-panah">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </div>
        </button>
      <?php echo form_close(); ?>

      <!-- Opsi Gunakan Akun Lain -->
      <button type="button" class="google-akun-item" id="tombol-toggle-akun-lain" onclick="var f=document.getElementById('wadah-form-akun-lain'); f.classList.toggle('aktif'); if(f.classList.contains('aktif')){document.getElementById('manual-google-nama').focus();}">
        <div class="google-avatar google-avatar--icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <div class="google-akun-info">
          <div class="google-akun-nama">Gunakan akun lain</div>
          <div class="google-akun-email">Masuk dengan akun Google Anda sendiri</div>
        </div>
        <div class="google-akun-panah">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </div>
      </button>

    </div>

    <!-- Formulir Akun Kustom (Tampil saat klik 'Gunakan akun lain') -->
    <div class="google-form-lain" id="wadah-form-akun-lain">
      <?php echo form_open('autentikasi/proses_sosial', array('id' => 'form-manual-google')); ?>
        <input type="hidden" name="provider" value="google">
        <div class="google-form-grup">
          <label class="google-form-label" for="manual-google-nama">Nama Lengkap</label>
          <input type="text" id="manual-google-nama" name="nama" class="google-form-input" placeholder="Nama Anda di Google" required>
        </div>
        <div class="google-form-grup">
          <label class="google-form-label" for="manual-google-email">Email Google</label>
          <input type="email" id="manual-google-email" name="email" class="google-form-input" placeholder="nama@gmail.com" required>
        </div>
        <button type="submit" class="google-btn-submit" id="btn-submit-google-manual">Lanjutkan dengan Google</button>
      <?php echo form_close(); ?>
    </div>

    <!-- Catatan Privasi Standar Google -->
    <div class="google-disclaimer">
      Untuk melanjutkan, Google akan membagikan nama, alamat email, dan preferensi bahasa Anda dengan PRDAI. Sebelum menggunakan aplikasi ini, tinjau Kebijakan Privasi dan Ketentuan Layanannya.
    </div>

    <!-- Tombol Batal -->
    <div class="google-batal-wadah">
      <a href="<?php echo site_url('masuk'); ?>" class="google-batal-link">Batal dan kembali ke Masuk</a>
    </div>

  </div>
</div>
