/**
 * PRDAI — Pengelola tampilan pesan dan notifikasi kilat.
 */
'use strict';

window.PRDAI_Pesan = (function () {
  function tampilkan(wadahEl, teks, tipe) {
    if (!wadahEl) return;
    tipe = tipe || 'galat';
    const kelasTipe = tipe === 'sukses' ? 'pesan-kilat--sukses' : 'pesan-kilat--galat';
    const role = tipe === 'sukses' ? 'status' : 'alert';

    wadahEl.innerHTML = `<div class="pesan-kilat ${kelasTipe}" role="${role}">${teks}</div>`;
    wadahEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function bersihkan(wadahEl) {
    if (!wadahEl) return;
    wadahEl.innerHTML = '';
  }

  return {
    tampilkan: tampilkan,
    bersihkan: bersihkan
  };
})();
