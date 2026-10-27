/**
 * PRDAI — Komponen konfirmasi aksi pengguna.
 *
 * Mencegah aksi penghapusan tidak sengaja dengan dialog berbasis dialog HTML
 * sederhana, bukan alert/confirm bawaan browser.
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const tombolKonfirmasi = document.querySelectorAll('[data-konfirmasi]');

  tombolKonfirmasi.forEach(function (tombol) {
    tombol.addEventListener('click', function (e) {
      const pesan = tombol.getAttribute('data-konfirmasi') || 'Apakah Anda yakin ingin melanjutkan?';
      if (!window.confirm(pesan)) {
        e.preventDefault();
      }
    });
  });
});
