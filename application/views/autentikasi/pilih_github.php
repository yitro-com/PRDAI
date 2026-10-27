<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="oauth-latar">
  <div class="github-card">
    
    <!-- Koneksi Logo GitHub <-> PRDAI -->
    <div class="github-header-koneksi">
      <div class="github-logo-box" title="GitHub">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="currentColor">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"></path>
        </svg>
      </div>
      <div class="github-koneksi-panah">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </div>
      <div class="prdai-logo-box" title="PRDAI">
        <img src="<?php echo base_url('assets/img/lgo.png'); ?>" alt="Logo PRDAI">
      </div>
    </div>

    <h1 class="github-card__judul">Authorize PRDAI</h1>
    <p class="github-card__subjudul">by <strong>PRDAI-App</strong></p>

    <!-- Kotak Hak Akses (Scopes) -->
    <div class="github-scope-box">
      <div class="github-scope-judul">Izin yang diminta:</div>
      <div class="github-scope-item">
        <svg class="github-scope-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>Akses data profil publik pengguna GitHub</span>
      </div>
      <div class="github-scope-item">
        <svg class="github-scope-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>Verifikasi alamat email utama dan terdaftar</span>
      </div>
    </div>

    <!-- Kotak Akun GitHub Aktif yang Terpilih -->
    <div class="github-user-card" id="wadah-user-aktif">
      <div class="github-avatar" id="avatar-user-tampil">A</div>
      <div class="github-user-info">
        <div class="github-user-nama" id="nama-user-tampil">Alexander Palulun</div>
        <div class="github-user-login" id="email-user-tampil">alexander.palulun@github.test</div>
      </div>
      <button type="button" class="github-btn-toggle-akun" id="btn-toggle-github-lain" onclick="var f=document.getElementById('wadah-form-github-lain'); f.classList.toggle('aktif'); if(f.classList.contains('aktif')) document.getElementById('input-github-nama').focus();">Ganti akun</button>
    </div>

    <!-- Form Kustom Akun GitHub Lain -->
    <div class="github-form-kustom" id="wadah-form-github-lain">
      <div class="google-form-grup">
        <label class="google-form-label" for="input-github-nama">Nama / Username GitHub</label>
        <input type="text" id="input-github-nama" class="google-form-input" value="Alexander Palulun" placeholder="Username GitHub">
      </div>
      <div class="google-form-grup">
        <label class="google-form-label" for="input-github-email">Email GitHub</label>
        <input type="email" id="input-github-email" class="google-form-input" value="alexander.palulun@github.test" placeholder="username@github.com">
      </div>
      <button type="button" class="google-btn-submit" id="btn-terapkan-akun-github" onclick="(function(){ var n=document.getElementById('input-github-nama').value.trim(); var e=document.getElementById('input-github-email').value.trim(); if(!n||!e){alert('Mohon isi nama dan email GitHub.');return;} document.getElementById('nama-user-tampil').textContent=n; document.getElementById('email-user-tampil').textContent=e; document.getElementById('avatar-user-tampil').textContent=n.charAt(0).toUpperCase(); document.getElementById('form-val-nama').value=n; document.getElementById('form-val-email').value=e; document.getElementById('wadah-form-github-lain').classList.remove('aktif'); })()">Terapkan Akun Ini</button>
    </div>

    <!-- Formulir Otorisasi Utama -->
    <?php echo form_open('autentikasi/proses_sosial', array('id' => 'form-auth-github')); ?>
      <input type="hidden" name="provider" value="github">
      <input type="hidden" name="nama" id="form-val-nama" value="Alexander Palulun">
      <input type="hidden" name="email" id="form-val-email" value="alexander.palulun@github.test">
      
      <div class="github-aksi-grid">
        <button type="submit" class="github-btn-authorize">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"></path>
          </svg>
          <span>Authorize PRDAI</span>
        </button>
        <a href="<?php echo site_url('masuk'); ?>" class="github-btn-batal">Cancel</a>
      </div>
    <?php echo form_close(); ?>

    <div class="github-catatan-keamanan">
      Otorisasi aplikasi ini akan mengalihkan kembali ke <strong><?php echo site_url(); ?></strong>. PRDAI tidak memiliki akses ke repositori privat atau pengaturan keamanan akun GitHub Anda.
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var btnToggle = document.getElementById('btn-toggle-github-lain');
  var formKustom = document.getElementById('wadah-form-github-lain');
  var btnTerapkan = document.getElementById('btn-terapkan-akun-github');

  var inputNama = document.getElementById('input-github-nama');
  var inputEmail = document.getElementById('input-github-email');

  var tampilNama = document.getElementById('nama-user-tampil');
  var tampilEmail = document.getElementById('email-user-tampil');
  var tampilAvatar = document.getElementById('avatar-user-tampil');

  var formValNama = document.getElementById('form-val-nama');
  var formValEmail = document.getElementById('form-val-email');

  if (btnToggle && formKustom) {
    btnToggle.addEventListener('click', function () {
      formKustom.classList.toggle('aktif');
      if (formKustom.classList.contains('aktif')) {
        inputNama.focus();
      }
    });
  }

  if (btnTerapkan) {
    btnTerapkan.addEventListener('click', function () {
      var n = inputNama.value.trim();
      var e = inputEmail.value.trim();
      if (!n || !e) {
        alert('Mohon isi nama dan email GitHub Anda.');
        return;
      }
      tampilNama.textContent = n;
      tampilEmail.textContent = e;
      tampilAvatar.textContent = n.charAt(0).toUpperCase();
      formValNama.value = n;
      formValEmail.value = e;
      formKustom.classList.remove('aktif');
    });
  }
});
</script>
