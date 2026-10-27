/**
 * PRDAI — Komponen salin ke papan klip (clipboard).
 *
 * Menyalin teks ke clipboard menggunakan Clipboard API dengan umpan balik teks
 * sederhana ("Tersalin!") serta mendukung fallback aman.
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const tombolSalin = document.querySelectorAll('[data-salin-target]');

  tombolSalin.forEach(function (tombol) {
    tombol.addEventListener('click', async function () {
      const selector = tombol.getAttribute('data-salin-target');
      const targetEl = document.querySelector(selector);
      if (!targetEl) return;

      const teks = targetEl.value !== undefined ? targetEl.value : targetEl.innerText;
      if (!teks) return;

      const teksSpan = tombol.querySelector('.btn-salin-semua-prd__teks') || tombol.querySelector('span');
      const teksAwal = teksSpan ? teksSpan.innerText : tombol.innerText;

      let berhasil = false;
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(teks);
          berhasil = true;
        }
      } catch (err) {}

      // Fallback
      if (!berhasil) {
        try {
          const ta = document.createElement('textarea');
          ta.value = teks;
          ta.style.position = 'fixed';
          ta.style.left = '-9999px';
          ta.style.top = '0';
          document.body.appendChild(ta);
          ta.focus();
          ta.select();
          berhasil = document.execCommand('copy');
          document.body.removeChild(ta);
        } catch (e) {
          berhasil = false;
        }
      }

      if (berhasil) {
        if (teksSpan) {
          teksSpan.innerText = 'Tersalin!';
        } else {
          tombol.innerText = 'Tersalin!';
        }
        tombol.classList.add('btn-salin-semua-prd--tersalin');
        tombol.setAttribute('disabled', 'true');
        setTimeout(function () {
          if (teksSpan) {
            teksSpan.innerText = teksAwal;
          } else {
            tombol.innerText = teksAwal;
          }
          tombol.classList.remove('btn-salin-semua-prd--tersalin');
          tombol.removeAttribute('disabled');
        }, 2000);
      } else {
        if (teksSpan) {
          teksSpan.innerText = 'Gagal menyalin';
        } else {
          tombol.innerText = 'Gagal menyalin';
        }
        setTimeout(function () {
          if (teksSpan) {
            teksSpan.innerText = teksAwal;
          } else {
            tombol.innerText = teksAwal;
          }
        }, 2000);
      }
    });
  });
});
