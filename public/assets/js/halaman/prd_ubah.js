/**
 * PRDAI — Skrip halaman Sunting Dokumen PRD.
 *
 * Mengelola tab editor Tulis dan Pratinjau Markdown real-time melalui
 * endpoint AJAX aman di server.
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const tabTulis = document.getElementById('tab-tulis');
  const tabPratinjau = document.getElementById('tab-pratinjau');
  const wadahEditor = document.getElementById('wadah-editor');
  const wadahPratinjau = document.getElementById('wadah-pratinjau');
  const editorEl = document.getElementById('textarea-markdown');
  const areaPratinjau = document.getElementById('area-pratinjau');
  const hitungEl = document.getElementById('hitung-karakter-editor');

  // Penghitung karakter
  if (editorEl && hitungEl) {
    function perbaruiHitungan() {
      hitungEl.innerText = editorEl.value.length;
    }
    editorEl.addEventListener('input', perbaruiHitungan);
    perbaruiHitungan();
  }

  // Penggantian Tab
  if (tabTulis && tabPratinjau) {
    tabTulis.addEventListener('click', function () {
      tabTulis.classList.add('tab-tombol--aktif');
      tabPratinjau.classList.remove('tab-tombol--aktif');
      wadahEditor.classList.remove('sembunyi');
      wadahPratinjau.classList.add('sembunyi');
    });

    tabPratinjau.addEventListener('click', async function () {
      tabPratinjau.classList.add('tab-tombol--aktif');
      tabTulis.classList.remove('tab-tombol--aktif');
      wadahEditor.classList.add('sembunyi');
      wadahPratinjau.classList.remove('sembunyi');

      areaPratinjau.innerHTML = '<p class="teks-redup">Memuat pratinjau...</p>';

      const markdown = editorEl ? editorEl.value : '';

      try {
        const url = tabPratinjau.getAttribute('data-pratinjau-url');
        const hasil = await window.PRDAI_API.kirim(url, { markdown: markdown }, 'POST');

        if (hasil.berhasil && hasil.html !== undefined) {
          areaPratinjau.innerHTML = hasil.html || '<p class="teks-redup">Dokumen kosong.</p>';
        } else {
          areaPratinjau.innerHTML = '<p class="teks-bahaya">Gagal memuat pratinjau.</p>';
        }
      } catch (err) {
        areaPratinjau.innerHTML = `<p class="teks-bahaya">${err.message || 'Gagal memuat pratinjau.'}</p>`;
      }
    });
  }
});
