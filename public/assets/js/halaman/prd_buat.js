/**
 * PRDAI — Skrip halaman Susun PRD Singkat (Single Input Execution).
 *
 * Mengatur pengiriman prompt tunggal, animasi alur eksekusi real-time
 * seperti AI coding agent, dan pengalihan otomatis saat dokumen selesai disusun.
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formSusun = document.getElementById('form-susun-prd');
  const inputIde = document.getElementById('masukan-ide-produk');
  const tombolEksekusi = document.getElementById('tombol-eksekusi');
  const wadahPesan = document.getElementById('wadah-pesan-eksekusi');

  const wadahInput = document.getElementById('wadah-input-prompt');
  const areaLog = document.getElementById('area-log-eksekusi');
  const teksPromptTerkirim = document.getElementById('teks-prompt-terkirim');
  const daftarLangkah = document.getElementById('daftar-langkah-eksekusi');

  // Klik contoh ide cepat (chips)
  const tombolSaran = document.querySelectorAll('.saran-ide__tombol');
  tombolSaran.forEach(function (tombol) {
    tombol.addEventListener('click', function () {
      const ide = tombol.getAttribute('data-ide');
      if (inputIde && ide) {
        inputIde.value = ide;
        inputIde.focus();
      }
    });
  });

  // Pintasan keyboard: Ctrl+Enter atau Cmd+Enter untuk kirim
  if (inputIde) {
    inputIde.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        formSusun.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  }

  // Langkah-langkah eksekusi yang ditampilkan
  const daftarTahap = [
    { jeda: 500, teks: 'AI sedang berpikir dan menganalisis ide produk...' },
    { jeda: 3500, teks: 'AI sedang berpikir merumuskan modul fungsional dan kriteria penerimaan...' },
    { jeda: 9000, teks: 'AI sedang berpikir menyusun skema basis data dan diagram alur...' },
    { jeda: 16000, teks: 'AI sedang berpikir memvalidasi kelengkapan dokumen PRD...' }
  ];

  let timers = [];

  function tambahLogItem(teks, status) {
    status = status || 'aktif';
    const div = document.createElement('div');
    div.className = `alur-eksekusi-item alur-eksekusi-item--${status}`;
    div.innerHTML = `
      <span class="alur-eksekusi-item__titik"></span>
      <span>${teks}</span>
    `;
    daftarLangkah.appendChild(div);
  }

  function mulaiAlurEksekusi() {
    daftarLangkah.innerHTML = '';
    const waktuMulai = Date.now();

    daftarTahap.forEach(function (tahap) {
      const t = setTimeout(function () {
        const detik = ((Date.now() - waktuMulai) / 1000).toFixed(1);
        tambahLogItem(`${tahap.teks} <span class="alur-eksekusi-item__waktu">(${detik}s)</span>`, 'aktif');
      }, tahap.jeda);
      timers.push(t);
    });
  }

  function hentikanSemuaTimer() {
    timers.forEach(function (t) {
      clearTimeout(t);
    });
    timers = [];
  }

  // Pengiriman Formulir
  if (formSusun) {
    formSusun.addEventListener('submit', async function (e) {
      e.preventDefault();

      const teksIde = inputIde ? inputIde.value.trim() : '';

      if (teksIde.length < 10) {
        if (window.PRDAI_Pesan) {
          window.PRDAI_Pesan.tampilkan(wadahPesan, 'Tuliskan deskripsi ide produk minimal 10 karakter.');
        }
        if (inputIde) inputIde.focus();
        return;
      }

      if (window.PRDAI_Pesan) {
        window.PRDAI_Pesan.bersihkan(wadahPesan);
      }

      // Kunci form dan tampilkan area log eksekusi
      tombolEksekusi.disabled = true;
      wadahInput.classList.add('sembunyi');
      areaLog.classList.remove('sembunyi');
      teksPromptTerkirim.textContent = teksIde;

      mulaiAlurEksekusi();

      const formData = new FormData(formSusun);

      try {
        const hasil = await window.PRDAI_API.kirim(formSusun.getAttribute('action'), formData, 'POST');

        hentikanSemuaTimer();

        if (hasil.berhasil && hasil.tautan) {
          tambahLogItem('Dokumen PRD berhasil disusun! Mengalihkan ke dokumen...', 'sukses');
          setTimeout(function () {
            window.location.href = hasil.tautan;
          }, 800);
        } else {
          throw new Error(hasil.pesan || 'Terjadi kendala saat menyusun PRD.');
        }
      } catch (err) {
        hentikanSemuaTimer();
        areaLog.classList.add('sembunyi');
        wadahInput.classList.remove('sembunyi');
        tombolEksekusi.disabled = false;

        if (window.PRDAI_Pesan) {
          window.PRDAI_Pesan.tampilkan(wadahPesan, err.message || 'Gagal menyusun PRD. Coba lagi beberapa saat lagi.');
        }
      }
    });
  }
});
