/**
 * PRDAI — Interaktivitas Halaman Autentikasi (Masuk & Daftar).
 * Mengatur toggle lihat kata sandi, interaksi checkbox, dan pesan modal ramah pengguna.
 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // Toggle Lihat/Sembunyikan Kata Sandi
  const toggleButtons = document.querySelectorAll('.auth-toggle-sandi');
  toggleButtons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = btn.getAttribute('data-target');
      const input = targetId ? document.getElementById(targetId) : btn.parentElement.querySelector('input');
      if (!input) return;

      const isCurrentlyPassword = input.type === 'password';
      input.type = isCurrentlyPassword ? 'text' : 'password';

      const iconMata = btn.querySelector('.ikon-mata');
      const iconMataTutup = btn.querySelector('.ikon-mata-tutup');

      if (iconMata && iconMataTutup) {
        if (input.type === 'text') {
          // Sandi terlihat -> tampilkan ikon mata terbuka, sembunyikan mata tertutup
          iconMata.classList.remove('tersembunyi');
          iconMataTutup.classList.add('tersembunyi');
          btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
          btn.setAttribute('title', 'Sembunyikan kata sandi');
        } else {
          // Sandi tersembunyi -> tampilkan ikon mata tertutup, sembunyikan mata terbuka
          iconMata.classList.add('tersembunyi');
          iconMataTutup.classList.remove('tersembunyi');
          btn.setAttribute('aria-label', 'Lihat kata sandi');
          btn.setAttribute('title', 'Lihat kata sandi');
        }
      }

      // Kembalikan fokus ke kolom input
      input.focus();
    });
  });

  // Penanganan "Lupa kata sandi?"
  const btnLupaSandi = document.getElementById('tautan-lupa-sandi');
  if (btnLupaSandi) {
    btnLupaSandi.addEventListener('click', function (e) {
      e.preventDefault();
      alert('Untuk menyetel ulang kata sandi, silakan hubungi administrator sistem atau gunakan kredensial akun uji coba:\n\nEmail: alexander@gmail.com\nSandi: password123');
    });
  }
});
