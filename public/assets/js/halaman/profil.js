/**
 * PRDAI — Interaktivitas Halaman Profil (Sesuai Gambar 2).
 *
 * Mengelola:
 * 1. Tooltip Melayang pada Heatmap Aktivitas Token
 * 2. Pergantian Tab Aktivitas (Harian, Mingguan, Kumulatif)
 * 3. Verifikasi Formulir Edit Nama & Ganti Password
 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // ==================== 1. TOOLTIP INTERAKTIF AKTIVITAS TOKEN ====================
  let tooltip = document.querySelector('.profil-tooltip-aktivitas');
  if (!tooltip) {
    tooltip = document.createElement('div');
    tooltip.className = 'profil-tooltip-aktivitas';
    document.body.appendChild(tooltip);
  }

  function posisikanTooltip(e) {
    const margin = 14;
    let left = e.clientX + margin;
    let top = e.clientY - 38;

    if (left + 260 > window.innerWidth) {
      left = e.clientX - 260;
    }
    if (top < 10) {
      top = e.clientY + 22;
    }

    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
  }

  // Delegasi Event Mouseover untuk Semua Elemen Grafik (Batang Harian, Mingguan, Titik Bulanan)
  document.addEventListener('mouseover', function (e) {
    const target = e.target.closest('.batang-harian, .batang-mingguan, .titik-bulanan, .titik-aktivitas, .grafik-bulanan-bulan-grup');
    if (!target) return;

    let pesan = '';
    const mode = target.getAttribute('data-mode');

    if (target.classList.contains('batang-harian')) {
      const tgl = target.getAttribute('data-tgl') || 'Tanggal';
      const token = parseInt(target.getAttribute('data-token') || '0', 10);
      const tokenFmt = target.getAttribute('data-token-fmt') || token.toLocaleString('id-ID');
      const aksi = parseInt(target.getAttribute('data-aksi') || '0', 10);

      if (token > 0) {
        pesan = `${tgl}: <strong>${tokenFmt} token</strong> (${aksi} aksi)`;
      } else {
        pesan = `${tgl}: Tidak ada aktivitas token`;
      }
    } else if (target.classList.contains('batang-mingguan')) {
      const label = target.getAttribute('data-label') || 'Minggu';
      const token = parseInt(target.getAttribute('data-token') || '0', 10);
      const tokenFmt = target.getAttribute('data-token-fmt') || token.toLocaleString('id-ID');
      const aksi = parseInt(target.getAttribute('data-aksi') || '0', 10);

      pesan = `<strong>${label}</strong>: ${tokenFmt} token (${aksi} aktivitas)`;
    } else if (target.classList.contains('titik-bulanan')) {
      const label = target.getAttribute('data-label') || 'Hari';
      const token = parseInt(target.getAttribute('data-token') || '0', 10);
      const tokenFmt = target.getAttribute('data-token-fmt') || token.toLocaleString('id-ID');
      const aksi = parseInt(target.getAttribute('data-aksi') || '0', 10);

      if (token > 0) {
        pesan = `${label}: <strong>${tokenFmt} token</strong> (${aksi} aksi)`;
      } else {
        pesan = `${label}: Tidak ada aktivitas token`;
      }
    } else if (target.classList.contains('titik-aktivitas')) {
      const tgl = target.getAttribute('data-tgl') || '';
      const token = parseInt(target.getAttribute('data-token') || '0', 10);
      const aksi = parseInt(target.getAttribute('data-aksi') || '0', 10);

      if (token > 0) {
        pesan = `${tgl}: <strong>${token.toLocaleString('id-ID')} token</strong> (${aksi} aksi)`;
      } else {
        pesan = `${tgl}: Tidak ada aktivitas token`;
      }
    }

    if (pesan) {
      tooltip.innerHTML = pesan;
      tooltip.classList.add('aktif');
      posisikanTooltip(e);
    }
  });

  document.addEventListener('mousemove', function (e) {
    if (tooltip.classList.contains('aktif')) {
      posisikanTooltip(e);
    }
  });

  document.addEventListener('mouseout', function (e) {
    const target = e.target.closest('.batang-harian, .batang-mingguan, .titik-bulanan, .titik-aktivitas');
    if (target) {
      tooltip.classList.remove('aktif');
    }
  });

  // ==================== 2. TAB AKTIVITAS TOKEN (HARIAN, MINGGUAN, BULANAN) ====================
  const wadahKartuAktivitasList = document.querySelectorAll('.profil-aktivitas-kartu');

  wadahKartuAktivitasList.forEach(function (kartu) {
    const tabButtons = kartu.querySelectorAll('.profil-tab-btn');
    const panelList = kartu.querySelectorAll('.profil-grafik-panel');

    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const mode = btn.getAttribute('data-mode') || 'harian';
        const targetId = btn.getAttribute('data-target') || ('panel-grafik-' + mode);

        // Nonaktifkan semua tab & sembunyikan semua panel di dalam kartu ini
        tabButtons.forEach(function (b) {
          b.classList.remove('profil-tab-btn--aktif');
          b.setAttribute('aria-selected', 'false');
        });

        panelList.forEach(function (p) {
          p.classList.remove('profil-grafik-panel--aktif');
          p.setAttribute('hidden', '');
        });

        // Aktifkan tab terpilih
        btn.classList.add('profil-tab-btn--aktif');
        btn.setAttribute('aria-selected', 'true');

        // Tampilkan panel target
        const targetPanel = kartu.querySelector('[data-panel="' + mode + '"]') || kartu.querySelector('#' + targetId) || document.getElementById(targetId);
        if (targetPanel) {
          targetPanel.removeAttribute('hidden');
          targetPanel.classList.add('profil-grafik-panel--aktif');
        }

        // Auto-scroll ke posisi paling kanan agar data terbaru (hari/bulan ini) langsung terlihat
        const papan = kartu.querySelector('.profil-grafik-papan');
        if (papan) {
          requestAnimationFrame(function () {
            papan.scrollLeft = papan.scrollWidth;
          });
        }

        // Tutup tooltip saat pergantian tab
        tooltip.classList.remove('aktif');
      });
    });

    // Inisialisasi awal: scroll papan ke kanan
    const papanAwal = kartu.querySelector('.profil-grafik-papan');
    if (papanAwal) {
      papanAwal.scrollLeft = papanAwal.scrollWidth;
    }
  });

  // ==================== 3. TOGGLE VISIBILITAS KATA SANDI (IKON MATA) ====================
  const btnToggleMataList = document.querySelectorAll('.btn-toggle-mata');
  btnToggleMataList.forEach(function (btn) {
    if (btn.dataset.toggleInited) return;
    btn.dataset.toggleInited = '1';

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const targetId = btn.getAttribute('data-target');
      if (!targetId) return;

      const inputSandi = document.getElementById(targetId);
      if (!inputSandi) return;

      const ikonBuka = btn.querySelector('.ikon-mata-buka');
      const ikonTutup = btn.querySelector('.ikon-mata-tutup');

      if (inputSandi.type === 'password') {
        inputSandi.type = 'text';
        if (ikonBuka) ikonBuka.classList.add('tersembunyi');
        if (ikonTutup) ikonTutup.classList.remove('tersembunyi');
        btn.setAttribute('title', 'Sembunyikan kata sandi');
        btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
      } else {
        inputSandi.type = 'password';
        if (ikonBuka) ikonBuka.classList.remove('tersembunyi');
        if (ikonTutup) ikonTutup.classList.add('tersembunyi');
        btn.setAttribute('title', 'Tampilkan kata sandi');
        btn.setAttribute('aria-label', 'Tampilkan kata sandi');
      }
    });
  });

  // ==================== 4. VALIDASI FORMULIR KEAMANAN ====================
  const formSandi = document.getElementById('form-ganti-sandi');
  if (formSandi) {
    formSandi.addEventListener('submit', function (e) {
      const sandiBaru = document.getElementById('masukan-sandi-baru');
      const konfirmasiSandi = document.getElementById('masukan-konfirmasi-sandi');

      if (sandiBaru && konfirmasiSandi) {
        if (sandiBaru.value.length < 10) {
          e.preventDefault();
          alert('Kata sandi baru minimal harus 10 karakter.');
          sandiBaru.focus();
          return false;
        }

        if (sandiBaru.value !== konfirmasiSandi.value) {
          e.preventDefault();
          alert('Konfirmasi kata sandi tidak cocok dengan kata sandi baru.');
          konfirmasiSandi.focus();
          return false;
        }
      }
    });
  }

  // ==================== 5. MODAL TINGKATKAN DARI ETALASE ====================
  const btnEtalaseTingkatkan = document.getElementById('tombol-etalase-tingkatkan');
  const modalUpgrade = document.getElementById('modal-tingkatkan-backdrop');
  const btnTutupUpgrade = document.getElementById('tombol-tutup-tingkatkan');
  const btnMengertiUpgrade = document.getElementById('tombol-mengerti-tingkatkan');

  if (btnEtalaseTingkatkan && modalUpgrade) {
    btnEtalaseTingkatkan.addEventListener('click', function () {
      modalUpgrade.classList.remove('tersembunyi');
    });
  }

  if (btnTutupUpgrade && modalUpgrade) {
    btnTutupUpgrade.addEventListener('click', function () {
      modalUpgrade.classList.add('tersembunyi');
    });
  }

  if (btnMengertiUpgrade && modalUpgrade) {
    btnMengertiUpgrade.addEventListener('click', function () {
      modalUpgrade.classList.add('tersembunyi');
    });
  }

  if (modalUpgrade) {
    modalUpgrade.addEventListener('click', function (e) {
      if (e.target === modalUpgrade) {
        modalUpgrade.classList.add('tersembunyi');
      }
    });
  }
});
