/**
 * PRDAI — Pengelola sinkronisasi token CSRF.
 *
 * Mengingat CI3 mengaktifkan csrf_regenerate = TRUE, setiap request AJAX
 * yang mengembalikan token baru harus memperbarui token lokal dan seluruh
 * input CSRF tersembunyi yang ada di halaman.
 */
'use strict';

window.PRDAI_CSRF = (function () {
  const metaNama = document.querySelector('meta[name="csrf-token-name"]');
  const metaNilai = document.querySelector('meta[name="csrf-token-hash"]');

  let nama = metaNama ? metaNama.getAttribute('content') : 'prdai_token';
  let nilai = metaNilai ? metaNilai.getAttribute('content') : '';

  function perbarui(nilaiBaru) {
    if (!nilaiBaru) return;
    nilai = nilaiBaru;
    if (metaNilai) {
      metaNilai.setAttribute('content', nilaiBaru);
    }
    // Perbarui semua formulir yang memuat input CSRF tersembunyi
    const inputForms = document.querySelectorAll(`input[name="${nama}"]`);
    inputForms.forEach(function (el) {
      el.value = nilaiBaru;
    });
  }

  function ambilNama() {
    return nama;
  }

  function ambilNilai() {
    return nilai;
  }

  return {
    ambilNama: ambilNama,
    ambilNilai: ambilNilai,
    perbarui: perbarui
  };
})();
