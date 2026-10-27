/**
 * PRDAI — Interaktivitas Ruang Kerja Tunggal (ChatGPT Dark Theme).
 *
 * Mengatur seluruh alur kerja dalam satu layar tanpa reload halaman:
 * - Profil pengguna dan inisial dinamis mengikuti sesi login aktif.
 * - Riwayat "Terkini" terisolasi strictly per pengguna.
 * - Seluruh tombol berfungsi normal (Sidebar, New Chat, Pustaka, Mode Switcher,
 *   Tingkatkan, Opsi Prompt +, Mode Berpikir, Suara/Speech, Eksekusi AI,
 *   Salin, Unduh, Sunting Inline, Riwayat Versi & Pulihkan, Hapus, dan Modal Profil).
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const ruangKerja = document.getElementById('ruang-kerja');
  if (!ruangKerja) return;

  // Dapatkan URL dasar situs
  const siteUrl = (function () {
    if (ruangKerja && ruangKerja.dataset.siteUrl) {
      let u = ruangKerja.dataset.siteUrl;
      return u.endsWith('/') ? u : u + '/';
    }
    const metaHref = window.location.origin + window.location.pathname.replace(/\/prd\/.*$/, '').replace(/\/$/, '') + '/';
    return metaHref;
  })();

  // Data Pengguna Dinamis dari Dataset Ruang Kerja
  function ambilUserInisial() {
    return ruangKerja.dataset.userInisial || 'YA';
  }
  function ambilUserNama() {
    return ruangKerja.dataset.userNama || 'Anda';
  }

  // Sinkronkan cookie zona waktu pengguna dari peramban secara otomatis
  try {
    const tzOtomatis = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Makassar';
    document.cookie = 'prdai_zona_waktu=' + encodeURIComponent(tzOtomatis) + '; path=/; max-age=31536000; SameSite=Lax';
  } catch (e) {}

  // Perbarui ucapan salam waktu terkini (Pagi / Siang / Sore / Malam) & sapa pengguna aktif
  function perbaruiSalamWaktu(animasiUlang = false) {
    const elSalam = document.getElementById('salam-tengah');
    if (!elSalam) return;
    const jam = new Date().getHours();
    let salam = 'Selamat Malam';
    if (jam >= 4 && jam < 11) {
      salam = 'Selamat Pagi';
    } else if (jam >= 11 && jam < 15) {
      salam = 'Selamat Siang';
    } else if (jam >= 15 && jam < 18) {
      salam = 'Selamat Sore';
    }
    const namaUser = ambilUserNama();
    elSalam.innerHTML = `Halo, ${salam}, <span class="salam-nama" id="salam-user-nama">${escapeHtml(namaUser)}</span>.`;

    if (animasiUlang) {
      elSalam.style.animation = 'none';
      void elSalam.offsetHeight; // trigger reflow peramban agar animasi mulai dari awal
      elSalam.style.animation = '';
    }
  }
  perbaruiSalamWaktu();

  const sidebar = document.getElementById('sidebar-chat');
  const btnTutupSidebar = document.getElementById('tombol-tutup-sidebar');
  const btnLogoTetap = document.getElementById('tombol-logo-tetap');
  const btnPrdBaru = document.getElementById('tombol-prd-baru');
  const btnSemuaPrd = document.getElementById('tombol-semua-prd');
  const daftarTerkini = document.getElementById('daftar-terkini');
  const wadahTerkiniSidebar = document.getElementById('wadah-terkini-sidebar');
  const headerJudul = document.getElementById('header-judul-dokumen');
  const btnBukaTingkatkan = document.getElementById('tombol-buka-tingkatkan');
  const btnAvatarHeader = document.getElementById('tombol-avatar-header');
  const btnBukaProfil = document.getElementById('tombol-buka-profil');

  // Kontainer Tampilan
  const scrollRuangKerja = document.getElementById('kanvas-konten') || document.getElementById('scroll-ruang-kerja');
  const tampilanKosong = document.getElementById('tampilan-kosong');
  const tampilanMemproses = document.getElementById('tampilan-memproses');
  const tampilanDokumen = document.getElementById('tampilan-dokumen');
  const wadahKapsulBawah = document.getElementById('wadah-kapsul-bawah');
  const daftarPesanChat = document.getElementById('daftar-pesan-chat');
  const indikatorMengetikInline = document.getElementById('indikator-mengetik-inline');

  // Input Prompt & Aksi
  const promptUtama = document.getElementById('prompt-utama');
  const btnKirimUtama = document.getElementById('tombol-kirim-utama');
  const promptBawah = document.getElementById('prompt-bawah');
  const btnKirimBawah = document.getElementById('tombol-kirim-bawah');
  const btnOpsiPlus = document.getElementById('tombol-opsi-plus');
  const btnOpsiPlusBawah = document.getElementById('tombol-opsi-plus-bawah');
  const inputBerkasLampiran = document.getElementById('input-berkas-lampiran');
  const badgeLampiranUtama = document.getElementById('badge-lampiran-utama');
  const namaLampiranUtama = document.getElementById('nama-lampiran-utama');
  const ukuranLampiranUtama = document.getElementById('ukuran-lampiran-utama');
  const btnHapusLampiranUtama = document.getElementById('tombol-hapus-lampiran-utama');
  const badgeLampiranBawah = document.getElementById('badge-lampiran-bawah');
  const namaLampiranBawah = document.getElementById('nama-lampiran-bawah');
  const ukuranLampiranBawah = document.getElementById('ukuran-lampiran-bawah');
  const btnHapusLampiranBawah = document.getElementById('tombol-hapus-lampiran-bawah');
  const btnSuara = document.getElementById('tombol-suara');
  const teksPromptDiajukan = document.getElementById('teks-prompt-diajukan');
  const teksTahapanAi = document.getElementById('teks-tahapan-ai');

  // Tampilan Dokumen
  const teksPromptAktif = document.getElementById('teks-prompt-aktif');
  const badgeVersi = document.getElementById('badge-versi-saat-ini');
  const kontenDokumenHtml = document.getElementById('konten-dokumen-html');

  // Toolbar Aksi Dokumen
  const btnSalinMd = document.getElementById('tombol-salin-md');
  const btnUnduhMd = document.getElementById('tombol-unduh-md');
  const btnSuntingPrd = document.getElementById('tombol-sunting-prd');
  const labelBtnSunting = document.getElementById('label-tombol-sunting');
  const btnRiwayatVersi = document.getElementById('tombol-riwayat-versi');
  const btnHapusPrd = document.getElementById('tombol-hapus-prd');

  // Editor Inline
  const editorInline = document.getElementById('editor-inline');
  const textareaEditorMd = document.getElementById('textarea-editor-md');
  const catatanRevisi = document.getElementById('catatan-revisi');
  const btnBatalSunting = document.getElementById('tombol-batal-sunting');
  const btnSimpanSunting = document.getElementById('tombol-simpan-sunting');

  // Modals & Drawers
  const modalPustakaBackdrop = document.getElementById('modal-pustaka-backdrop');
  const btnTutupPustaka = document.getElementById('tombol-tutup-pustaka');
  const inputCariPustaka = document.getElementById('input-cari-pustaka');
  const badgeTotalPustaka = document.getElementById('badge-total-pustaka');
  const pustakaDaftarGulung = document.getElementById('pustaka-daftar-gulung');

  const modalProfilBackdrop = document.getElementById('modal-profil-backdrop');
  const btnTutupProfil = document.getElementById('tombol-tutup-profil');
  const btnTutupProfilBawah = document.getElementById('tombol-tutup-profil-bawah');

  const modalTingkatkanBackdrop = document.getElementById('modal-tingkatkan-backdrop');
  const btnTutupTingkatkan = document.getElementById('tombol-tutup-tingkatkan');
  const btnMengertiTingkatkan = document.getElementById('tombol-mengerti-tingkatkan');

  const backdropVersi = document.getElementById('backdrop-versi');
  const btnTutupVersi = document.getElementById('tombol-tutup-versi');
  const daftarRiwayatVersi = document.getElementById('daftar-riwayat-versi');

  const modalHapusBackdrop = document.getElementById('modal-hapus-backdrop');
  const btnBatalHapus = document.getElementById('tombol-batal-hapus');
  const btnKonfirmasiHapus = document.getElementById('tombol-konfirmasi-hapus');

  const toastEl = document.getElementById('toast-notifikasi');

  let timerTickerAi = null;
  let kodeMenungguHapus = null;
  let speechRecognizer = null;
  let sedangMerekamSuara = false;
  let pesanAsistenSedangDiedit = null;

  // State Dokumen / Lampiran Aktif (PRD.md, TXT, JSON, CSV, dsb.)
  let lampiranAktif = null;

  // ==================== BANTUAN NOTIFIKASI TOAST ====================
  let timerToast = null;
  function tampilkanToast(pesan, durasi) {
    if (!toastEl) return;
    if (timerToast) clearTimeout(timerToast);
    toastEl.textContent = pesan;
    toastEl.classList.remove('tersembunyi');
    timerToast = setTimeout(function () {
      toastEl.classList.add('tersembunyi');
      timerToast = null;
    }, durasi || 3000);
  }

  // ==================== INISIALISASI & RENDERING MERMAID ====================
  function sanitasiMermaidKode(kode) {
    if (!kode) return '';
    return kode
      .replace(/(\b[A-Za-z0-9_]+)\[([^"\]]+)\]/g, function (m, id, label) {
        return id + '["' + label.replace(/"/g, "'") + '"]';
      })
      .replace(/(\b[A-Za-z0-9_]+)\{([^"\}]+)\}/g, function (m, id, label) {
        return id + '{"' + label.replace(/"/g, "'") + '"}';
      });
  }

  async function renderMermaid() {
    if (typeof window.mermaid === 'undefined') return;

    try {
      window.mermaid.initialize({
        startOnLoad: false,
        theme: 'dark',
        securityLevel: 'loose'
      });

      const blokMermaid = document.querySelectorAll('code.language-mermaid, pre code.language-mermaid');
      for (let i = 0; i < blokMermaid.length; i++) {
        const el = blokMermaid[i];
        const kodeDiagram = sanitasiMermaidKode(el.textContent.trim());
        const preParent = el.closest('pre');
        const idDiagram = 'diagram-mermaid-' + Date.now() + '-' + i;

        try {
          const { svg } = await window.mermaid.render(idDiagram, kodeDiagram);
          const wadahDiv = document.createElement('div');
          wadahDiv.className = 'wadah-mermaid';
          wadahDiv.innerHTML = svg;

          if (preParent) {
            preParent.parentNode.replaceChild(wadahDiv, preParent);
          } else {
            el.parentNode.replaceChild(wadahDiv, el);
          }
        } catch (errDiagram) {
          console.warn('Diagram Mermaid ' + (i + 1) + ' gagal dirender:', errDiagram);
        }
      }
    } catch (e) {
      console.warn('Gagal memproses diagram Mermaid:', e);
    }
  }

  renderMermaid();

  // ==================== BILAH SAMPING (SIDEBAR) TOGGLE ====================
  // Tombol untuk memperlebar ruang kerja (tutup bilah samping) cuma di kiri
  if (btnTutupSidebar) {
    btnTutupSidebar.addEventListener('click', function () {
      if (sidebar) {
        sidebar.classList.add('sidebar-chat--tertutup');
        try { localStorage.setItem('prdai_sidebar', 'tertutup'); } catch (e) {}
      }
    });
  }

  // Tombol logo persisten (diam di tempatnya seperti Gemini):
  // Jika sidebar tertutup, klik logo akan membuka kembali sidebar (memperstengahkan lagi) dan kembali ke beranda.
  // Jika sidebar terbuka, klik logo akan langsung kembali ke beranda.
  if (btnLogoTetap) {
    btnLogoTetap.addEventListener('click', function (e) {
      e.preventDefault();
      if (sidebar && sidebar.classList.contains('sidebar-chat--tertutup')) {
        sidebar.classList.remove('sidebar-chat--tertutup');
        try { localStorage.setItem('prdai_sidebar', 'terbuka'); } catch (err) {}
      }
      resetKePrdBaru();
    });
  }

  // Cek preferensi sidebar
  try {
    if (localStorage.getItem('prdai_sidebar') === 'tertutup' && window.innerWidth > 768) {
      sidebar.classList.add('sidebar-chat--tertutup');
    }
  } catch (e) {}

  // ==================== AUTO-GROWING TEXTAREA & EVENT LISTENERS ====================
  function sesuaikanTinggiTextarea(el) {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 180) + 'px';
  }

  [promptUtama, promptBawah].forEach(function (el) {
    if (!el) return;
    const btnKirimTerkait = (el === promptUtama) ? btnKirimUtama : btnKirimBawah;

    ['input', 'keyup', 'change', 'paste', 'cut'].forEach(function (namaEvent) {
      el.addEventListener(namaEvent, function () {
        sesuaikanTinggiTextarea(this);
        perbaruiIkonKirim(this, btnKirimTerkait);
      });
    });

    el.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        eksekusiPrompt(this.value);
      }
    });
  });

  // ==================== TOMBOL + PRD BARU (PERPINDAHAN KE BERANDA) ====================
  function resetKePrdBaru(perbaruiHistori = true) {
    ruangKerja.dataset.kodeAktif = '';
    if (tampilanDokumen) tampilanDokumen.classList.add('tersembunyi');
    if (tampilanMemproses) tampilanMemproses.classList.add('tersembunyi');
    if (wadahKapsulBawah) wadahKapsulBawah.classList.add('tersembunyi');
    if (editorInline) editorInline.classList.add('tersembunyi');
    pesanAsistenSedangDiedit = null;
    if (tampilanKosong) {
      tampilanKosong.classList.remove('tersembunyi');
      perbaruiSalamWaktu(true);
    }

    if (daftarPesanChat) {
      daftarPesanChat.innerHTML = '';
    }

    if (headerJudul) headerJudul.textContent = 'PRDAI';
    document.title = 'PRDAI';

    document.querySelectorAll('.item-terkini').forEach(function (item) {
      item.classList.remove('item-terkini--aktif');
    });

    if (promptUtama) {
      promptUtama.value = '';
      sesuaikanTinggiTextarea(promptUtama);
      perbaruiIkonKirim(promptUtama, btnKirimUtama);
      promptUtama.focus();
    }
    if (promptBawah) {
      promptBawah.value = '';
      sesuaikanTinggiTextarea(promptBawah);
      perbaruiIkonKirim(promptBawah, btnKirimBawah);
    }

    if (typeof bersihkanLampiran === 'function') {
      bersihkanLampiran();
    }

    if (perbaruiHistori) {
      window.history.pushState(null, '', siteUrl);
    }
  }

  if (btnPrdBaru) {
    btnPrdBaru.addEventListener('click', function () {
      resetKePrdBaru(true);
    });
  }

  // ==================== PUSTAKA PRD MODAL ====================
  function perbaruiBadgeTotalPustaka() {
    if (!badgeTotalPustaka || !pustakaDaftarGulung) return;
    const items = pustakaDaftarGulung.querySelectorAll('.item-pustaka');
    badgeTotalPustaka.textContent = items.length + ' dokumen';
  }

  // Menambahkan atau memperbarui item di modal Pustaka PRD Saya langsung tanpa reset/reload
  function tambahItemKePustaka(kode, judul, waktuStr, versi) {
    if (!pustakaDaftarGulung || !kode) return;

    // Hapus pesan kosong 'Belum ada riwayat dokumen...' jika ada
    const pesanKosong = pustakaDaftarGulung.querySelector('.pustaka-kosong');
    if (pesanKosong) {
      pesanKosong.remove();
    }

    const waktuFormatted = waktuStr || (new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':'));
    const nomorVersi = (versi && parseInt(versi, 10) > 0) ? parseInt(versi, 10) : 1;
    const judulTeks = (judul && judul.trim()) ? judul.trim() : 'Dokumen PRD';

    // Cek apakah item sudah ada di pustaka (misalnya update versi / judul)
    let itemAda = pustakaDaftarGulung.querySelector(`.item-pustaka[data-kode="${kode}"]`);
    if (itemAda) {
      itemAda.setAttribute('data-judul', judulTeks.toLowerCase());
      const elJudul = itemAda.querySelector('.item-pustaka__judul');
      if (elJudul) elJudul.textContent = judulTeks;
      const elWaktu = itemAda.querySelector('.item-pustaka__waktu');
      if (elWaktu) elWaktu.innerHTML = `${escapeHtml(waktuFormatted)} &bull; Versi ${nomorVersi}`;
      // Pindahkan ke paling atas daftar pustaka
      pustakaDaftarGulung.insertBefore(itemAda, pustakaDaftarGulung.firstChild);
    } else {
      const itemDiv = document.createElement('div');
      itemDiv.className = 'item-pustaka';
      itemDiv.setAttribute('data-kode', kode);
      itemDiv.setAttribute('data-judul', judulTeks.toLowerCase());
      itemDiv.style.cursor = 'pointer';
      itemDiv.innerHTML = `
        <div class="item-pustaka__info">
          <div class="item-pustaka__judul">${escapeHtml(judulTeks)}</div>
          <div class="item-pustaka__waktu">${escapeHtml(waktuFormatted)} &bull; Versi ${nomorVersi}</div>
        </div>
        <button type="button" class="btn-buka-pustaka" data-kode="${kode}">Buka Dokumen</button>
      `;
      pustakaDaftarGulung.insertBefore(itemDiv, pustakaDaftarGulung.firstChild);
    }

    perbaruiBadgeTotalPustaka();
  }

  if (btnSemuaPrd && modalPustakaBackdrop) {
    btnSemuaPrd.addEventListener('click', function () {
      // Sinkronisasi otomatis: pastikan dokumen di sidebar langsung masuk di pustaka tanpa reload
      if (daftarTerkini && pustakaDaftarGulung) {
        const itemSidebarDaftar = Array.from(daftarTerkini.querySelectorAll('.item-terkini')).reverse();
        itemSidebarDaftar.forEach(function (el) {
          const kode = el.getAttribute('data-kode');
          const judulEl = el.querySelector('.item-terkini__judul');
          const judul = judulEl ? judulEl.textContent.trim() : '';
          if (kode && judul && !pustakaDaftarGulung.querySelector(`.item-pustaka[data-kode="${kode}"]`)) {
            tambahItemKePustaka(kode, judul, '', 1);
          }
        });
      }
      modalPustakaBackdrop.classList.remove('tersembunyi');
      if (inputCariPustaka) {
        inputCariPustaka.value = '';
        inputCariPustaka.focus();
      }
      filterPustaka('');
    });
  }

  if (btnTutupPustaka) {
    btnTutupPustaka.addEventListener('click', function () {
      modalPustakaBackdrop.classList.add('tersembunyi');
    });
  }

  if (modalPustakaBackdrop) {
    modalPustakaBackdrop.addEventListener('click', function (e) {
      if (e.target === modalPustakaBackdrop) {
        modalPustakaBackdrop.classList.add('tersembunyi');
      }
    });
  }

  function filterPustaka(kataKunci) {
    if (!pustakaDaftarGulung) return;
    const term = kataKunci.toLowerCase().trim();
    const items = pustakaDaftarGulung.querySelectorAll('.item-pustaka');
    let nampak = 0;

    items.forEach(function (el) {
      const judul = (el.getAttribute('data-judul') || '').toLowerCase();
      if (!term || judul.includes(term)) {
        el.style.display = 'flex';
        nampak++;
      } else {
        el.style.display = 'none';
      }
    });

    if (badgeTotalPustaka) {
      badgeTotalPustaka.textContent = nampak + ' dokumen';
    }
  }

  if (inputCariPustaka) {
    inputCariPustaka.addEventListener('input', function () {
      filterPustaka(this.value);
    });
  }

  // Delegasi event klik pada seluruh item pustaka (bekerja instan untuk item dinamis baru)
  if (pustakaDaftarGulung) {
    pustakaDaftarGulung.addEventListener('click', function (e) {
      const targetBtn = e.target.closest('.btn-buka-pustaka');
      const targetItem = e.target.closest('.item-pustaka');
      const kode = targetBtn ? targetBtn.getAttribute('data-kode') : (targetItem ? targetItem.getAttribute('data-kode') : null);
      if (kode) {
        if (modalPustakaBackdrop) modalPustakaBackdrop.classList.add('tersembunyi');
        muatDokumenPrd(kode);
      }
    });
  }

  // ==================== MODAL TINGKATKAN (PRDAI PRO) & VISIBILITAS ====================
  const btnUpgradeSidebar = document.getElementById('tombol-upgrade-sidebar');
  if (btnUpgradeSidebar && modalTingkatkanBackdrop) {
    btnUpgradeSidebar.addEventListener('click', function () {
      modalTingkatkanBackdrop.classList.remove('tersembunyi');
    });
  }

  // Hilangkan tombol Upgrade jika akun sudah Plus atau di atasnya (Plus, Pro)
  // Hanya munculkan jika akun di bawah Plus (Free atau Go)
  function perbaruiVisibilitasTombolUpgrade() {
    const tier = (ruangKerja.dataset.userTier || '').toLowerCase();
    const diBawahPlus = (tier === 'free' || tier === 'go');

    if (btnUpgradeSidebar) {
      btnUpgradeSidebar.style.display = diBawahPlus ? '' : 'none';
    }
    if (btnBukaTingkatkan) {
      btnBukaTingkatkan.style.display = diBawahPlus ? '' : 'none';
    }
    const itemMenuUpgrade = document.getElementById('item-menu-upgrade');
    if (itemMenuUpgrade) {
      itemMenuUpgrade.style.display = diBawahPlus ? '' : 'none';
    }
  }
  perbaruiVisibilitasTombolUpgrade();

  if (btnBukaTingkatkan && modalTingkatkanBackdrop) {
    btnBukaTingkatkan.addEventListener('click', function () {
      modalTingkatkanBackdrop.classList.remove('tersembunyi');
    });
  }

  if (btnTutupTingkatkan) {
    btnTutupTingkatkan.addEventListener('click', function () {
      modalTingkatkanBackdrop.classList.add('tersembunyi');
    });
  }

  if (btnMengertiTingkatkan) {
    btnMengertiTingkatkan.addEventListener('click', function () {
      modalTingkatkanBackdrop.classList.add('tersembunyi');
    });
  }

  if (modalTingkatkanBackdrop) {
    modalTingkatkanBackdrop.addEventListener('click', function (e) {
      if (e.target === modalTingkatkanBackdrop) {
        modalTingkatkanBackdrop.classList.add('tersembunyi');
      }
    });
  }

  // ==================== PENGELOLAAN LAMPIRAN DOKUMEN (+) ====================
  function formatUkuranBerkas(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const dm = 1;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }

  function perbaruiTampilanLampiran() {
    if (lampiranAktif) {
      if (badgeLampiranUtama) {
        if (namaLampiranUtama) namaLampiranUtama.textContent = lampiranAktif.nama;
        if (ukuranLampiranUtama) ukuranLampiranUtama.textContent = formatUkuranBerkas(lampiranAktif.ukuran);
        badgeLampiranUtama.classList.remove('tersembunyi');
      }
      if (badgeLampiranBawah) {
        if (namaLampiranBawah) namaLampiranBawah.textContent = lampiranAktif.nama;
        if (ukuranLampiranBawah) ukuranLampiranBawah.textContent = formatUkuranBerkas(lampiranAktif.ukuran);
        badgeLampiranBawah.classList.remove('tersembunyi');
      }
    } else {
      if (badgeLampiranUtama) badgeLampiranUtama.classList.add('tersembunyi');
      if (badgeLampiranBawah) badgeLampiranBawah.classList.add('tersembunyi');
      if (inputBerkasLampiran) inputBerkasLampiran.value = '';
    }
    if (typeof perbaruiIkonKirim === 'function') {
      perbaruiIkonKirim(promptUtama, btnKirimUtama);
      perbaruiIkonKirim(promptBawah, btnKirimBawah);
    }
  }

  function bersihkanLampiran() {
    lampiranAktif = null;
    perbaruiTampilanLampiran();
  }

  if (btnHapusLampiranUtama) btnHapusLampiranUtama.addEventListener('click', bersihkanLampiran);
  if (btnHapusLampiranBawah) btnHapusLampiranBawah.addEventListener('click', bersihkanLampiran);

  function bukaDialogPilihBerkas() {
    if (inputBerkasLampiran) {
      inputBerkasLampiran.click();
    }
  }

  if (btnOpsiPlus) btnOpsiPlus.addEventListener('click', bukaDialogPilihBerkas);
  if (btnOpsiPlusBawah) btnOpsiPlusBawah.addEventListener('click', bukaDialogPilihBerkas);

  if (inputBerkasLampiran) {
    inputBerkasLampiran.addEventListener('change', function (e) {
      const berkas = e.target.files && e.target.files[0];
      if (!berkas) return;

      const nama = berkas.name;
      const ukuran = berkas.size;
      const tipe = berkas.type || 'text/plain';

      // Maksimal 15MB
      if (ukuran > 15 * 1024 * 1024) {
        tampilkanToast('Ukuran berkas maksimal 15MB.');
        inputBerkasLampiran.value = '';
        return;
      }

      const ekstensi = nama.split('.').pop().toLowerCase();
      const formatTeks = ['md', 'markdown', 'txt', 'json', 'csv', 'xml', 'yaml', 'yml', 'html', 'htm', 'js', 'php', 'sql'];
      const formatGambar = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];

      if (formatGambar.includes(ekstensi) || tipe.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function (evt) {
          lampiranAktif = {
            nama: nama,
            ukuran: ukuran,
            tipe: tipe,
            isImage: true,
            dataUrl: evt.target.result || '',
            konten: '[Gambar terlampir: ' + nama + ' (' + formatUkuranBerkas(ukuran) + ')]'
          };
          perbaruiTampilanLampiran();
        };
        reader.onerror = function () {
          tampilkanToast('Gagal membaca gambar.');
        };
        reader.readAsDataURL(berkas);
      } else if (formatTeks.includes(ekstensi) || tipe.startsWith('text/') || tipe === 'application/json') {
        const reader = new FileReader();
        reader.onload = function (evt) {
          lampiranAktif = {
            nama: nama,
            ukuran: ukuran,
            tipe: tipe,
            isImage: false,
            dataUrl: null,
            konten: evt.target.result || ''
          };
          perbaruiTampilanLampiran();
        };
        reader.onerror = function () {
          tampilkanToast('Gagal membaca dokumen teks.');
        };
        reader.readAsText(berkas, 'UTF-8');
      } else {
        lampiranAktif = {
          nama: nama,
          ukuran: ukuran,
          tipe: tipe,
          isImage: false,
          dataUrl: null,
          konten: '[Dokumen terlampir: ' + nama + ' (' + formatUkuranBerkas(ukuran) + ')]'
        };
        perbaruiTampilanLampiran();
      }
    });
  }

  // ==================== MASUKAN SUARA (WEB SPEECH RECOGNITION) ====================
  const btnSuaraBawah = document.getElementById('tombol-suara-bawah');
  const semuaBtnSuara = [btnSuara, btnSuaraBawah].filter(Boolean);

  if (semuaBtnSuara.length > 0) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
      speechRecognizer = new SpeechRecognition();
      speechRecognizer.lang = 'id-ID';
      speechRecognizer.continuous = false;
      speechRecognizer.interimResults = false;

      speechRecognizer.onstart = function () {
        sedangMerekamSuara = true;
        semuaBtnSuara.forEach(function (b) {
          b.classList.add('tombol-suara--merekam');
        });
      };

      speechRecognizer.onresult = function (event) {
        const hasilTeks = event.results[0][0].transcript;
        const targetInput = (!tampilanKosong || !tampilanKosong.classList.contains('tersembunyi')) ? promptUtama : promptBawah;
        const targetBtn = (targetInput === promptUtama) ? btnKirimUtama : btnKirimBawah;
        if (targetInput) {
          targetInput.value = (targetInput.value ? targetInput.value + ' ' : '') + hasilTeks;
          sesuaikanTinggiTextarea(targetInput);
          perbaruiIkonKirim(targetInput, targetBtn);
          targetInput.focus();
        }
      };

      speechRecognizer.onerror = function (event) {
        sedangMerekamSuara = false;
        semuaBtnSuara.forEach(function (b) {
          b.classList.remove('tombol-suara--merekam');
        });
        if (event.error !== 'no-speech' && event.error !== 'aborted') {
          tampilkanToast('Pengenalan suara gagal (' + event.error + ')');
        }
      };

      speechRecognizer.onend = function () {
        sedangMerekamSuara = false;
        semuaBtnSuara.forEach(function (b) {
          b.classList.remove('tombol-suara--merekam');
        });
      };

      semuaBtnSuara.forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (!sedangMerekamSuara) {
            try { speechRecognizer.start(); } catch (e) {}
          } else {
            try { speechRecognizer.stop(); } catch (e) {}
          }
        });
      });
    } else {
      semuaBtnSuara.forEach(function (btn) {
        btn.addEventListener('click', function () {
          tampilkanToast('Fitur suara tidak didukung di peramban ini. Silakan ketik langsung.');
        });
      });
    }
  }

  // ==================== GENERATE ELEMEN PESAN CHAT ====================
  function buatElemenPesanUser(teks, lampiranInfo) {
    const div = document.createElement('div');
    div.className = 'wadah-pesan-pengguna';

    let htmlLampiran = '';
    const namaFile = typeof lampiranInfo === 'string' ? lampiranInfo : (lampiranInfo ? lampiranInfo.nama : null);
    const dataUrl = (lampiranInfo && typeof lampiranInfo === 'object') ? lampiranInfo.dataUrl : null;
    const isImage = (lampiranInfo && typeof lampiranInfo === 'object' && lampiranInfo.isImage);

    if (namaFile) {
      if (isImage && dataUrl) {
        htmlLampiran = `
          <div class="badge-lampiran-pesan badge-lampiran-pesan--gambar" style="display:block; margin-bottom:8px; padding:6px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); border-radius:10px; max-width:280px;">
            <img src="${dataUrl}" alt="${escapeHtml(namaFile)}" style="width:100%; max-height:180px; object-fit:cover; border-radius:8px; display:block;" />
            <span style="display:block; font-size:11px; margin-top:5px; color:var(--warna-teks-redup, #a0a0a0); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(namaFile)}</span>
          </div>
        `;
      } else {
        htmlLampiran = `
          <div class="badge-lampiran-pesan">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            <span>${escapeHtml(namaFile)}</span>
          </div>
        `;
      }
    }

    div.innerHTML = `
      <div class="gelembung-pesan-user" tabindex="0">
        ${htmlLampiran}
        <div class="teks-pesan-user">${escapeHtml(teks).replace(/\n/g, '<br>')}</div>
        <textarea class="raw-teks-user tersembunyi" style="display:none;">${escapeHtml(teks)}</textarea>
        <div class="baris-aksi-user">
          <button type="button" class="btn-aksi-user btn-salin-pesan-user" title="Salin teks" aria-label="Salin">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
          </button>
          <button type="button" class="btn-aksi-user btn-edit-pesan-user" title="Sunting teks" aria-label="Sunting">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 20h9"></path>
              <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
          </button>
        </div>
      </div>
    `;
    return div;
  }

  // Memeriksa apakah teks respons AI merupakan dokumen PRD terstruktur
  function cekApakahPrd(teks) {
    if (!teks || typeof teks !== 'string') return false;
    return /(?:^#\s+.*(?:PRD|Product\s+Requirements)|Dokumen\s+Spesifikasi\s+Kebutuhan\s+Produk|##\s+\d+\.\s+Petunjuk\s+Khusus\s+untuk\s+AI\s+Coding\s+Agent)/mi.test(teks);
  }

  function buatElemenPesanAsisten(htmlKonten, rawMarkdown, kodeDokumen, nomorVersi, apakahPrd, modeBerpikir) {
    const div = document.createElement('div');
    div.className = 'wadah-respon-asisten';

    const unduhUrl = kodeDokumen ? `${siteUrl}prd/${kodeDokumen}/unduh` : '#';
    const isPrd = (typeof apakahPrd === 'boolean') ? (apakahPrd || cekApakahPrd(rawMarkdown)) : cekApakahPrd(rawMarkdown);

    let lencanaModeBerpikirHtml = '';
    if (modeBerpikir) {
      lencanaModeBerpikirHtml = `
        <div class="lencana-mode-berpikir" title="Dijawab dengan penalaran mendalam sedetail-detailnya">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-2.04"></path>
            <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-2.04"></path>
          </svg>
          <span>Berpikir Mendalam</span>
        </div>
      `;
    }

    let tombolSalinSemuaPrdHtml = '';
    if (isPrd) {
      tombolSalinSemuaPrdHtml = `
        <button type="button" class="btn-salin-semua-prd" title="Salin langsung semua jawaban PRD yang diberikan" aria-label="Salin Semua Jawaban PRD">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
          </svg>
          <span class="btn-salin-semua-prd__teks">Salin Semua Jawaban PRD</span>
        </button>
      `;
    }

    div.innerHTML = `
      <div class="wadah-respon-isi">
        ${lencanaModeBerpikirHtml}
        <article class="konten-markdown-prd">
          ${htmlKonten}
        </article>
        <div class="baris-ikon-ai">
          ${tombolSalinSemuaPrdHtml}
          <button type="button" class="btn-ikon-ai btn-salin-md" title="Salin teks biasa" aria-label="Salin">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
          </button>
          <button type="button" class="btn-ikon-ai btn-jempol-suka" title="Bagus / Suka" aria-label="Suka">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
            </svg>
          </button>
          <button type="button" class="btn-ikon-ai btn-jempol-tidak-suka" title="Kurang / Tidak suka" aria-label="Tidak suka">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path>
            </svg>
          </button>
          <button type="button" class="btn-ikon-ai btn-bagikan-ai" title="Bagikan respons" aria-label="Bagikan">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
              <polyline points="16 6 12 2 8 6"></polyline>
              <line x1="12" y1="2" x2="12" y2="15"></line>
            </svg>
          </button>
          <button type="button" class="btn-ikon-ai btn-regenerasi-ai" title="Buat ulang jawaban" aria-label="Regenerasi">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="23 4 23 10 17 10"></polyline>
              <polyline points="1 20 1 14 7 14"></polyline>
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
          </button>
          <div class="wadah-menu-titik-tiga">
            <button type="button" class="btn-ikon-ai btn-opsi-titik-tiga" title="Opsi lainnya" aria-label="Opsi lainnya">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="1.5"></circle>
                <circle cx="19" cy="12" r="1.5"></circle>
                <circle cx="5" cy="12" r="1.5"></circle>
              </svg>
            </button>
            <div class="menu-dropdown-titik-tiga tersembunyi">
              <a href="${unduhUrl}" class="item-menu-dropdown btn-unduh-link" title="Unduh berkas Markdown (.md)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Unduh .md</span>
              </a>
              <button type="button" class="item-menu-dropdown btn-sunting-prd" title="Sunting">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                <span>Sunting Dokumen</span>
              </button>
              <button type="button" class="item-menu-dropdown btn-riwayat-versi" title="Lihat Riwayat Versi">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Riwayat Versi</span>
              </button>
              <button type="button" class="item-menu-dropdown item-menu-dropdown--bahaya btn-hapus-prd" title="Hapus">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                <span>Hapus Dokumen</span>
              </button>
            </div>
          </div>
        </div>
        <textarea class="raw-markdown-asisten tersembunyi" style="display:none;">${escapeHtml(rawMarkdown || '')}</textarea>
      </div>
    `;
    return div;
  }

  // Batal mode sunting pesan pengguna
  function batalEditUser(gelembung) {
    if (!gelembung) return;
    const wadahEdit = gelembung.querySelector('.kotak-edit-pesan-user');
    if (wadahEdit) wadahEdit.remove();
    const teksEl = gelembung.querySelector('.teks-pesan-user');
    if (teksEl) teksEl.style.display = '';
    const barisAksi = gelembung.querySelector('.baris-aksi-user');
    if (barisAksi) barisAksi.style.display = '';
    gelembung.classList.remove('gelembung-pesan-user--mengedit');
  }

  // Kirim pembaruan teks pesan pengguna
  function kirimEditUser(gelembung) {
    if (!gelembung) return;
    const wadahEdit = gelembung.querySelector('.kotak-edit-pesan-user');
    if (!wadahEdit) return;
    const taEdit = wadahEdit.querySelector('.textarea-edit-user');
    const teksBaru = taEdit ? taEdit.value.trim() : '';

    if (!teksBaru) {
      tampilkanToast('Pesan tidak boleh kosong.');
      return;
    }

    const wadahUser = gelembung.closest('.wadah-pesan-pengguna');
    if (!wadahUser) return;

    // Hitung indeks pesan dalam daftar percakapan
    let indeksPesan = -1;
    if (daftarPesanChat) {
      const semuaPesan = Array.from(daftarPesanChat.children).filter(el =>
        el.classList.contains('wadah-pesan-pengguna') || el.classList.contains('wadah-respon-asisten')
      );
      indeksPesan = semuaPesan.indexOf(wadahUser);
    }

    // Perbarui teks di gelembung yang sedang diedit
    const teksEl = gelembung.querySelector('.teks-pesan-user');
    if (teksEl) {
      teksEl.innerHTML = escapeHtml(teksBaru).replace(/\n/g, '<br>');
      teksEl.style.display = '';
    }
    const rawTa = gelembung.querySelector('.raw-teks-user');
    if (rawTa) rawTa.value = teksBaru;

    const barisAksi = gelembung.querySelector('.baris-aksi-user');
    if (barisAksi) barisAksi.style.display = '';
    wadahEdit.remove();
    gelembung.classList.remove('gelembung-pesan-user--aktif');
    gelembung.classList.remove('gelembung-pesan-user--mengedit');

    // Hapus respons AI dan pesan setelahnya di antarmuka (alur edit seperti ChatGPT / Claude)
    if (daftarPesanChat) {
      let saudara = wadahUser.nextElementSibling;
      while (saudara) {
        const elemenHapus = saudara;
        saudara = saudara.nextElementSibling;
        elemenHapus.remove();
      }
    }

    // Jalankan prompt baru ke AI dengan menyertakan indeks yang disunting
    eksekusiPrompt(teksBaru, { indeksSunting: indeksPesan, elemenUser: wadahUser });
  }

  // Salin teks ke papan klip dengan fallback aman
  async function salinKePapanKlipAman(teks) {
    if (!teks) return false;
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(teks);
        return true;
      }
    } catch (e) {}

    try {
      const textarea = document.createElement('textarea');
      textarea.value = teks;
      textarea.style.position = 'fixed';
      textarea.style.left = '-9999px';
      textarea.style.top = '0';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      const sukses = document.execCommand('copy');
      document.body.removeChild(textarea);
      return sukses;
    } catch (e) {
      return false;
    }
  }

  // Delegasi aksi-aksi respons AI dan pesan pengguna (Salin, Edit, Suka, Tidak Suka, Bagikan, Regenerasi, Dropdown)
  document.addEventListener('click', async function (e) {
    // A. SALIN TEKS PESAN PENGGUNA
    const btnSalinUser = e.target.closest('.btn-salin-pesan-user');
    if (btnSalinUser) {
      e.stopPropagation();
      const gelembung = btnSalinUser.closest('.gelembung-pesan-user');
      if (!gelembung) return;
      const rawTa = gelembung.querySelector('.raw-teks-user');
      const teksEl = gelembung.querySelector('.teks-pesan-user');
      const teks = (rawTa && rawTa.value) ? rawTa.value : (teksEl ? (teksEl.innerText || teksEl.textContent) : '');

      if (!teks || !teks.trim()) {
        tampilkanToast('Tidak ada teks untuk disalin.');
        return;
      }

      const berhasil = await salinKePapanKlipAman(teks.trim());
      if (berhasil) {
        btnSalinUser.classList.add('btn-aksi-user--tersalin');
        const ikonAwal = btnSalinUser.innerHTML;
        btnSalinUser.innerHTML = `
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        `;
        tampilkanToast('Pesan pengguna berhasil disalin!');
        setTimeout(function () {
          btnSalinUser.classList.remove('btn-aksi-user--tersalin');
          btnSalinUser.innerHTML = ikonAwal;
        }, 2000);
      } else {
        tampilkanToast('Gagal menyalin pesan.');
      }
      return;
    }

    // B. SUNTING / EDIT TEKS PESAN PENGGUNA
    const btnEditUser = e.target.closest('.btn-edit-pesan-user');
    if (btnEditUser) {
      e.stopPropagation();
      const gelembung = btnEditUser.closest('.gelembung-pesan-user');
      if (!gelembung) return;
      if (gelembung.querySelector('.kotak-edit-pesan-user')) return;

      gelembung.classList.add('gelembung-pesan-user--mengedit');

      const rawTa = gelembung.querySelector('.raw-teks-user');
      const teksEl = gelembung.querySelector('.teks-pesan-user');
      const teksAwal = (rawTa && rawTa.value) ? rawTa.value : (teksEl ? (teksEl.innerText || teksEl.textContent) : '');

      if (teksEl) teksEl.style.display = 'none';
      const barisAksi = gelembung.querySelector('.baris-aksi-user');
      if (barisAksi) barisAksi.style.display = 'none';

      const wadahEdit = document.createElement('div');
      wadahEdit.className = 'kotak-edit-pesan-user';
      wadahEdit.innerHTML = `
        <textarea class="textarea-edit-user" rows="2" placeholder="Tulis perbaikan pesan...">${escapeHtml(teksAwal.trim())}</textarea>
        <div class="baris-tombol-edit-user">
          <button type="button" class="btn-edit-batal" title="Batal sunting">Batal</button>
          <button type="button" class="btn-edit-kirim" title="Kirim perbaikan prompt">Kirim</button>
        </div>
      `;

      gelembung.appendChild(wadahEdit);
      const taEdit = wadahEdit.querySelector('.textarea-edit-user');
      if (taEdit) {
        taEdit.focus();
        taEdit.setSelectionRange(taEdit.value.length, taEdit.value.length);
        sesuaikanTinggiTextarea(taEdit);
        taEdit.addEventListener('input', function () {
          sesuaikanTinggiTextarea(taEdit);
        });
        taEdit.addEventListener('keydown', function (evt) {
          if (evt.key === 'Escape') {
            evt.preventDefault();
            batalEditUser(gelembung);
          } else if (evt.key === 'Enter' && !evt.shiftKey) {
            evt.preventDefault();
            kirimEditUser(gelembung);
          }
        });
      }
      return;
    }

    // C. TOMBOL BATAL SUNTING PESAN USER
    const btnBatalEdit = e.target.closest('.btn-edit-batal');
    if (btnBatalEdit) {
      e.stopPropagation();
      const gelembung = btnBatalEdit.closest('.gelembung-pesan-user');
      if (gelembung) batalEditUser(gelembung);
      return;
    }

    // D. TOMBOL KIRIM SUNTINGAN PESAN USER
    const btnKirimEdit = e.target.closest('.btn-edit-kirim');
    if (btnKirimEdit) {
      e.stopPropagation();
      const gelembung = btnKirimEdit.closest('.gelembung-pesan-user');
      if (gelembung) kirimEditUser(gelembung);
      return;
    }

    // E. TOGGLE AKTIF PADA GELEMBUNG PESAN USER KETIKA TEKS DIPENCET
    const gelembungUserKlik = e.target.closest('.gelembung-pesan-user');
    if (gelembungUserKlik) {
      if (e.target.closest('.baris-aksi-user') || e.target.closest('.kotak-edit-pesan-user')) {
        return;
      }
      // Jangan toggle jika pengguna sedang menyeleksi teks dengan mouse
      const seleksi = window.getSelection ? window.getSelection().toString() : '';
      if (seleksi && seleksi.trim().length > 0) {
        gelembungUserKlik.classList.add('gelembung-pesan-user--aktif');
        return;
      }
      document.querySelectorAll('.gelembung-pesan-user--aktif').forEach(function (el) {
        if (el !== gelembungUserKlik) el.classList.remove('gelembung-pesan-user--aktif');
      });
      gelembungUserKlik.classList.toggle('gelembung-pesan-user--aktif');
      return;
    }

    // Klik di luar gelembung pengguna manapun: sembunyikan baris aksi aktif
    document.querySelectorAll('.gelembung-pesan-user--aktif').forEach(function (el) {
      el.classList.remove('gelembung-pesan-user--aktif');
    });

    // 1. SALIN SEMUA JAWABAN PRD (Hanya muncul jika jawaban merupakan PRD)
    const btnSalinSemua = e.target.closest('.btn-salin-semua-prd');
    if (btnSalinSemua) {
      const parent = btnSalinSemua.closest('.wadah-respon-isi');
      if (!parent) return;
      const rawTa = parent.querySelector('.raw-markdown-asisten');
      let teks = '';
      if (rawTa && rawTa.value) {
        teks = rawTa.value;
      } else {
        const artikel = parent.querySelector('.konten-markdown-prd');
        teks = artikel ? (artikel.innerText || artikel.textContent) : '';
      }

      if (!teks || !teks.trim()) {
        tampilkanToast('Tidak ada teks jawaban PRD untuk disalin.');
        return;
      }

      const berhasil = await salinKePapanKlipAman(teks);
      if (berhasil) {
        const teksSpan = btnSalinSemua.querySelector('.btn-salin-semua-prd__teks');
        const teksAwal = teksSpan ? teksSpan.innerText : 'Salin Semua Jawaban PRD';
        btnSalinSemua.classList.add('btn-salin-semua-prd--tersalin');
        if (teksSpan) teksSpan.innerText = 'Tersalin!';
        tampilkanToast('Seluruh jawaban PRD berhasil disalin ke papan klip! Siap digunakan di Antigravity.');

        setTimeout(function () {
          btnSalinSemua.classList.remove('btn-salin-semua-prd--tersalin');
          if (teksSpan) teksSpan.innerText = teksAwal;
        }, 2500);
      } else {
        tampilkanToast('Gagal menyalin jawaban PRD.');
      }
      return;
    }

    // 2. SALIN MARKDOWN BIASA
    const btnSalin = e.target.closest('.btn-salin-md');
    if (btnSalin) {
      const parent = btnSalin.closest('.wadah-respon-isi');
      if (!parent) return;
      const rawTa = parent.querySelector('.raw-markdown-asisten');
      let teks = '';
      if (rawTa && rawTa.value) {
        teks = rawTa.value;
      } else {
        const artikel = parent.querySelector('.konten-markdown-prd');
        teks = artikel ? (artikel.innerText || artikel.textContent) : '';
      }
      const berhasil = await salinKePapanKlipAman(teks);
      if (berhasil) {
        tampilkanToast('Markdown berhasil disalin ke papan klip!');
      } else {
        tampilkanToast('Gagal menyalin teks.');
      }
      return;
    }

    const btnSuka = e.target.closest('.btn-jempol-suka');
    if (btnSuka) {
      btnSuka.classList.toggle('btn-ikon-ai--aktif');
      const tidakSuka = btnSuka.parentElement ? btnSuka.parentElement.querySelector('.btn-jempol-tidak-suka') : null;
      if (tidakSuka) tidakSuka.classList.remove('btn-ikon-ai--aktif');
      return;
    }

    const btnTidakSuka = e.target.closest('.btn-jempol-tidak-suka');
    if (btnTidakSuka) {
      btnTidakSuka.classList.toggle('btn-ikon-ai--aktif');
      const suka = btnTidakSuka.parentElement ? btnTidakSuka.parentElement.querySelector('.btn-jempol-suka') : null;
      if (suka) suka.classList.remove('btn-ikon-ai--aktif');
      return;
    }

    const btnBagikan = e.target.closest('.btn-bagikan-ai');
    if (btnBagikan) {
      const parent = btnBagikan.closest('.wadah-respon-isi');
      const artikel = parent ? parent.querySelector('.konten-markdown-prd') : null;
      const teks = artikel ? (artikel.innerText || artikel.textContent) : window.location.href;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(teks).then(function () {
          tampilkanToast('Teks tanggapan berhasil disalin untuk dibagikan!');
        });
      }
      return;
    }

    const btnRegenerasi = e.target.closest('.btn-regenerasi-ai');
    if (btnRegenerasi) {
      const pesanUserDaftar = document.querySelectorAll('.wadah-pesan-pengguna .teks-pesan-user');
      if (pesanUserDaftar.length > 0) {
        const teksTerakhir = pesanUserDaftar[pesanUserDaftar.length - 1].innerText;
        eksekusiPrompt(teksTerakhir);
      } else {
        tampilkanToast('Tidak ada pesan sebelumnya untuk dibuat ulang.');
      }
      return;
    }

    const btnOpsi = e.target.closest('.btn-opsi-titik-tiga');
    if (btnOpsi) {
      const wadah = btnOpsi.closest('.wadah-menu-titik-tiga');
      const menu = wadah ? wadah.querySelector('.menu-dropdown-titik-tiga') : null;
      if (menu) {
        document.querySelectorAll('.menu-dropdown-titik-tiga').forEach(function(m) {
          if (m !== menu) m.classList.add('tersembunyi');
        });
        menu.classList.toggle('tersembunyi');
      }
      return;
    }

    if (!e.target.closest('.wadah-menu-titik-tiga')) {
      document.querySelectorAll('.menu-dropdown-titik-tiga').forEach(function(m) {
        m.classList.add('tersembunyi');
      });
    }
  });

  // Tombol Gulir Bawah Mengambang
  const btnGulirBawah = document.getElementById('tombol-gulir-bawah');
  if (scrollRuangKerja && btnGulirBawah) {
    scrollRuangKerja.addEventListener('scroll', function () {
      const sisaBawah = scrollRuangKerja.scrollHeight - scrollRuangKerja.scrollTop - scrollRuangKerja.clientHeight;
      if (sisaBawah > 120) {
        btnGulirBawah.classList.remove('tersembunyi');
      } else {
        btnGulirBawah.classList.add('tersembunyi');
      }
    });

    btnGulirBawah.addEventListener('click', function () {
      scrollRuangKerja.scrollTo({
        top: scrollRuangKerja.scrollHeight,
        behavior: 'smooth'
      });
    });
  }

  // Toggle Ikon Suara / Kirim
  function perbaruiIkonKirim(ta, btnKirim) {
    if (!ta || !btnKirim) return;
    const audioIcon = btnKirim.querySelector('.ikon-audio');
    const sendIcon = btnKirim.querySelector('.ikon-kirim');
    const adaTeks = (ta.value || '').trim().length > 0;
    const adaLampiran = !!lampiranAktif;
    const siapKirim = adaTeks || adaLampiran;

    if (siapKirim) {
      if (audioIcon) audioIcon.classList.add('tersembunyi');
      if (sendIcon) sendIcon.classList.remove('tersembunyi');
      btnKirim.classList.add('mode-kirim-aktif');
      btnKirim.setAttribute('title', 'Kirim (Enter)');
      btnKirim.setAttribute('aria-label', 'Kirim Pesan');
    } else {
      if (audioIcon) audioIcon.classList.remove('tersembunyi');
      if (sendIcon) sendIcon.classList.add('tersembunyi');
      btnKirim.classList.remove('mode-kirim-aktif');
      btnKirim.setAttribute('title', 'Ketik menggunakan suara (Dikte Mikrofon)');
      btnKirim.setAttribute('aria-label', 'Masukan suara');
    }
  }

  function tanganiAksiKirim(ta, btnKirim) {
    if (!ta) return;
    const teks = (ta.value || '').trim();
    if (teks.length > 0 || lampiranAktif) {
      eksekusiPrompt(teks);
    } else {
      // Jika belum ada teks, tombol audio berfungsi mengaktifkan masukan suara/dikte
      const btnSuaraAktif = (!tampilanKosong || !tampilanKosong.classList.contains('tersembunyi')) ? btnSuara : (btnSuaraBawah || btnSuara);
      if (btnSuaraAktif) {
        btnSuaraAktif.click();
      }
    }
  }

  // Tombol Mode Berpikir
  let modeBerpikirAktif = false;
  const btnBerpikirUtama = document.getElementById('tombol-berpikir-utama');
  const btnBerpikirBawah = document.getElementById('tombol-berpikir-bawah');

  function perbaruiStatusModeBerpikir(aktif, tunjukkanToast) {
    modeBerpikirAktif = !!aktif;
    [btnBerpikirUtama, btnBerpikirBawah].forEach(function(b) {
      if (!b) return;
      if (modeBerpikirAktif) {
        b.classList.add('tombol-mode-berpikir--aktif');
        b.setAttribute('title', 'Mode Berpikir Aktif (Penjelasan Sedetail-detailnya & Jelas Sekali)');
      } else {
        b.classList.remove('tombol-mode-berpikir--aktif');
        b.setAttribute('title', 'Mode Berpikir (Klik untuk Penjelasan Mendalam Sedetail-detailnya)');
      }
    });

    if (tunjukkanToast && typeof tampilkanToast === 'function') {
      if (modeBerpikirAktif) {
        tampilkanToast('Mode Berpikir aktif: Penjelasan akan diberikan sedetail-detailnya & jelas sekali.');
      } else {
        tampilkanToast('Mode Berpikir nonaktif: Jawaban rinci seperti biasa.');
      }
    }
  }

  [btnBerpikirUtama, btnBerpikirBawah].forEach(function(btn) {
    if (!btn) return;
    btn.addEventListener('click', function() {
      perbaruiStatusModeBerpikir(!modeBerpikirAktif, true);
    });
  });

  // Tombol Kirim Manual / Aksi Suara
  if (btnKirimUtama) {
    btnKirimUtama.addEventListener('click', function () {
      tanganiAksiKirim(promptUtama, btnKirimUtama);
    });
  }

  if (btnKirimBawah) {
    btnKirimBawah.addEventListener('click', function () {
      tanganiAksiKirim(promptBawah, btnKirimBawah);
    });
  }

  // ==================== EKSEKUSI PROMPT (CHAT & PRD AI) ====================
  let isSedangMemproses = false;

  async function eksekusiPrompt(ideProduk, opsi) {
    if (isSedangMemproses) {
      return;
    }

    opsi = opsi || {};
    const isEditMode = (typeof opsi.indeksSunting === 'number' && opsi.indeksSunting >= 0);

    let promptBersih = (ideProduk || '').trim();
    if (!promptBersih && !lampiranAktif) {
      tampilkanToast('Silakan ketik pertanyaan, instruksi, atau lampirkan dokumen.');
      return;
    }

    isSedangMemproses = true;
    if (btnKirimUtama) btnKirimUtama.disabled = true;
    if (btnKirimBawah) btnKirimBawah.disabled = true;

    const lampiranData = lampiranAktif ? Object.assign({}, lampiranAktif) : null;
    if (!promptBersih && lampiranData) {
      promptBersih = 'Analisis dan susun spesifikasi PRD dari dokumen ' + lampiranData.nama;
    }

    // Tampilkan kanvas chat thread
    tampilanKosong.classList.add('tersembunyi');
    tampilanDokumen.classList.remove('tersembunyi');
    wadahKapsulBawah.classList.remove('tersembunyi');

    // Sisipkan pesan pengguna langsung ke dalam thread jika bukan mode sunting pesan
    if (!isEditMode && daftarPesanChat) {
      const elUser = buatElemenPesanUser(promptBersih, lampiranData);
      daftarPesanChat.appendChild(elUser);
    }

    // Tampilkan indikator mengetik / proses langsung di bawah pesan user
    if (indikatorMengetikInline) {
      indikatorMengetikInline.classList.remove('tersembunyi');
      daftarPesanChat.appendChild(indikatorMengetikInline);
    }

    // Bersihkan masukan teks dan lampiran
    if (promptUtama) {
      promptUtama.value = '';
      sesuaikanTinggiTextarea(promptUtama);
      perbaruiIkonKirim(promptUtama, btnKirimUtama);
    }
    if (promptBawah) {
      promptBawah.value = '';
      sesuaikanTinggiTextarea(promptBawah);
      perbaruiIkonKirim(promptBawah, btnKirimBawah);
    }
    bersihkanLampiran();

    // Auto-scroll ke pesan terbaru
    if (scrollRuangKerja) {
      scrollRuangKerja.scrollTop = scrollRuangKerja.scrollHeight;
    }

    // Ticker teks proses AI: Berpikir mendalam vs merumuskan jawaban
    const animasiTitik = modeBerpikirAktif ? [
      'AI sedang berpikir mendalam',
      'AI sedang berpikir mendalam.',
      'AI sedang berpikir mendalam..',
      'AI sedang berpikir mendalam...'
    ] : [
      'AI sedang merumuskan jawaban',
      'AI sedang merumuskan jawaban.',
      'AI sedang merumuskan jawaban..',
      'AI sedang merumuskan jawaban...'
    ];
    let indeksTitik = 3;
    if (teksTahapanAi) teksTahapanAi.textContent = animasiTitik[indeksTitik];

    if (timerTickerAi) clearInterval(timerTickerAi);
    timerTickerAi = setInterval(function () {
      indeksTitik = (indeksTitik + 1) % animasiTitik.length;
      if (teksTahapanAi) teksTahapanAi.textContent = animasiTitik[indeksTitik];
    }, 450);

    try {
      const tzKlien = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Makassar';
      const offsetKlien = -new Date().getTimezoneOffset(); // Menit, e.g. 480
      const waktuKlien = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
      const tanggalKlien = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

      const urlHasilkan = siteUrl + 'prd/hasilkan';
      const payload = {
        ide_produk: promptBersih,
        kode_aktif: ruangKerja.dataset.kodeAktif || '',
        tingkat_detail: 'Sangat rinci',
        ditujukan_untuk: 'AI coding agent (Google Antigravity)',
        mode_berpikir: modeBerpikirAktif ? 'ya' : 'tidak',
        waktu_klien: waktuKlien,
        zona_waktu_klien: tzKlien,
        offset_klien: offsetKlien,
        tanggal_klien: tanggalKlien
      };

      if (isEditMode) {
        payload.indeks_sunting = opsi.indeksSunting;
      }

      if (lampiranData) {
        payload.lampiran_nama = lampiranData.nama;
        payload.lampiran_konten = lampiranData.konten;
        payload.lampiran_tipe = lampiranData.tipe;
        if (lampiranData.isImage) {
          payload.apakah_gambar = '1';
        }
      }

      const respons = await window.PRDAI_API.kirim(urlHasilkan, payload);
      clearInterval(timerTickerAi);

      if (indikatorMengetikInline) {
        indikatorMengetikInline.classList.add('tersembunyi');
      }

      if (respons && respons.berhasil) {
        // Sisipkan respon asisten langsung ke thread
        if (daftarPesanChat) {
          const elAsst = buatElemenPesanAsisten(respons.html, respons.markdown, respons.kode, respons.versi, respons.apakah_prd, respons.mode_berpikir);
          daftarPesanChat.appendChild(elAsst);
        }

        if (textareaEditorMd) {
          if (respons.apakah_prd || !textareaEditorMd.value) {
            textareaEditorMd.value = respons.markdown || '';
          }
        }

        renderMermaid();

        // Jika ini percakapan baru, simpan kode aktif, perbarui sidebar dan langsung masukkan ke Pustaka PRD Saya
        if (!ruangKerja.dataset.kodeAktif) {
          ruangKerja.dataset.kodeAktif = respons.kode;
          if (headerJudul) headerJudul.textContent = respons.judul;
          document.title = respons.judul + ' — PRDAI';
          window.history.pushState({ kode: respons.kode }, '', siteUrl + 'prd/' + respons.kode);
          tambahItemKeTerkini(respons.kode, respons.judul);
          tambahItemKePustaka(respons.kode, respons.judul, respons.diperbarui_pada, respons.versi || 1);
        } else {
          // Jika percakapan lanjutan, perbarui waktu dan versi di Pustaka
          tambahItemKePustaka(respons.kode || ruangKerja.dataset.kodeAktif, respons.judul || (headerJudul ? headerJudul.textContent : ''), respons.diperbarui_pada, respons.versi);
        }

        if (scrollRuangKerja) {
          scrollRuangKerja.scrollTop = scrollRuangKerja.scrollHeight;
        }

        if (promptBawah) {
          promptBawah.focus();
        }
      } else {
        throw new Error((respons && respons.pesan) || 'Gagal menyusun jawaban.');
      }
    } catch (galat) {
      clearInterval(timerTickerAi);
      if (indikatorMengetikInline) {
        indikatorMengetikInline.classList.add('tersembunyi');
      }
      tampilkanToast(galat.message || 'Terjadi kesalahan sistem.', 4000);
    } finally {
      isSedangMemproses = false;
      if (btnKirimUtama) btnKirimUtama.disabled = false;
      if (btnKirimBawah) btnKirimBawah.disabled = false;
    }
  }

  // Menambahkan item baru ke atas daftar terkini di sidebar
  function tambahItemKeTerkini(kode, judul) {
    if (typeof tambahItemKePustaka === 'function') {
      tambahItemKePustaka(kode, judul);
    }

    if (!daftarTerkini) return;

    if (wadahTerkiniSidebar) {
      wadahTerkiniSidebar.classList.remove('tersembunyi');
    }

    const teksKosong = document.getElementById('sidebar-kosong-teks');
    if (teksKosong) teksKosong.remove();

    const adaSebelumnya = daftarTerkini.querySelector(`[data-kode="${kode}"]`);
    if (adaSebelumnya) {
      adaSebelumnya.remove();
    }

    document.querySelectorAll('.item-terkini').forEach(function (item) {
      item.classList.remove('item-terkini--aktif');
    });

    const itemDiv = document.createElement('div');
    itemDiv.className = 'item-terkini item-terkini--aktif';
    itemDiv.setAttribute('data-kode', kode);
    itemDiv.setAttribute('role', 'listitem');

    itemDiv.innerHTML = `
      <button type="button" class="item-terkini__btn" data-kode="${kode}" title="${escapeHtml(judul)}">
        <span class="item-terkini__judul">${escapeHtml(judul)}</span>
      </button>
      <button type="button" class="item-terkini__hapus" data-kode="${kode}" title="Hapus Dokumen" aria-label="Hapus Dokumen">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
      </button>
    `;

    daftarTerkini.insertBefore(itemDiv, daftarTerkini.firstChild);
    pasangPendengarItemTerkini(itemDiv);
  }

  function escapeHtml(teks) {
    const div = document.createElement('div');
    div.textContent = teks;
    return div.innerHTML;
  }

  // ==================== BUKA DOKUMEN DARI DAFTAR TERKINI (PERPINDAHAN CHAT) ====================
  let idPermintaanMuat = 0;
  async function muatDokumenPrd(kode, perbaruiHistori = true) {
    if (!kode) return;

    // Jika dokumen ini sudah aktif dan sedang ditampilkan, cukup gulir ke bawah
    if (ruangKerja.dataset.kodeAktif === kode && tampilanDokumen && !tampilanDokumen.classList.contains('tersembunyi')) {
      if (scrollRuangKerja) scrollRuangKerja.scrollTop = scrollRuangKerja.scrollHeight;
      if (promptBawah) promptBawah.focus();
      return;
    }

    const idPermintaanIni = ++idPermintaanMuat;

    try {
      // 1. Umpan balik visual seketika: tandai item yang dipilih di sidebar
      document.querySelectorAll('.item-terkini').forEach(function (item) {
        if (item.getAttribute('data-kode') === kode) {
          item.classList.add('item-terkini--aktif');
        } else {
          item.classList.remove('item-terkini--aktif');
        }
      });

      // 2. Langsung alihkan tampilan kanvas ke area obrolan
      if (tampilanKosong) tampilanKosong.classList.add('tersembunyi');
      if (tampilanMemproses) tampilanMemproses.classList.add('tersembunyi');
      if (tampilanDokumen) tampilanDokumen.classList.remove('tersembunyi');
      if (wadahKapsulBawah) wadahKapsulBawah.classList.remove('tersembunyi');
      if (editorInline) editorInline.classList.add('tersembunyi');
      pesanAsistenSedangDiedit = null;

      // 3. Tutup drawer atau modal yang mungkin sedang terbuka
      if (modalPustakaBackdrop) modalPustakaBackdrop.classList.add('tersembunyi');
      if (modalProfilBackdrop) modalProfilBackdrop.classList.add('tersembunyi');
      if (modalTingkatkanBackdrop) modalTingkatkanBackdrop.classList.add('tersembunyi');
      if (backdropVersi) backdropVersi.classList.add('tersembunyi');

      // 4. Di layar kecil (mobile/tablet), tutup sidebar agar obrolan langsung terlihat
      if (window.innerWidth <= 768 && sidebar) {
        sidebar.classList.add('sidebar-chat--tertutup');
      }

      // 5. Tampilkan indikator transisi pemuatan percakapan
      if (daftarPesanChat) {
        daftarPesanChat.innerHTML = `
          <div class="memuat-chat-indikator" style="display:flex;align-items:center;justify-content:center;padding:48px 16px;color:var(--warna-teks-redup,#a0a0a0);gap:10px;">
            <svg class="animasi-putar" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.2"></circle>
              <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-linecap="round"></path>
            </svg>
            <span>Memuat percakapan...</span>
          </div>
        `;
      }

      const url = siteUrl + 'prd/ambil_json/' + kode;
      const respons = await window.PRDAI_API.kirim(url, {}, 'GET');

      // Hindari race-condition jika pengguna beralih chat lain sebelum respons selesai
      if (idPermintaanIni !== idPermintaanMuat) {
        return;
      }

      if (respons && respons.berhasil) {
        const prd = respons.prd;
        ruangKerja.dataset.kodeAktif = prd.kode;

        if (headerJudul) headerJudul.textContent = prd.judul;
        document.title = prd.judul + ' — PRDAI';

        if (textareaEditorMd) {
          textareaEditorMd.value = prd.isi_markdown || '';
        }

        if (respons.daftar_versi) {
          perbaruiDaftarVersi(respons.daftar_versi);
        }

        if (daftarPesanChat) {
          daftarPesanChat.innerHTML = '';
          if (respons.percakapan && respons.percakapan.length > 0) {
            respons.percakapan.forEach(function (msg) {
              if (msg.peran === 'user') {
                const elUser = buatElemenPesanUser(msg.pesan, msg.lampiran);
                daftarPesanChat.appendChild(elUser);
              } else {
                const elAsst = buatElemenPesanAsisten(msg.html || msg.pesan, msg.pesan, prd.kode, prd.versi_saat_ini, msg.apakah_prd, !!msg.mode_berpikir);
                daftarPesanChat.appendChild(elAsst);
              }
            });
          } else if (prd.isi_markdown) {
            const elUser = buatElemenPesanUser(prd.judul || 'Dokumen PRD');
            daftarPesanChat.appendChild(elUser);
            const elAsst = buatElemenPesanAsisten(respons.html || prd.isi_markdown, prd.isi_markdown, prd.kode, prd.versi_saat_ini);
            daftarPesanChat.appendChild(elAsst);
          }
        }

        renderMermaid();

        if (scrollRuangKerja) {
          scrollRuangKerja.scrollTop = scrollRuangKerja.scrollHeight;
        }

        if (perbaruiHistori) {
          window.history.pushState({ kode: prd.kode }, '', siteUrl + 'prd/' + prd.kode);
        }

        if (promptBawah) {
          promptBawah.value = '';
          sesuaikanTinggiTextarea(promptBawah);
          perbaruiIkonKirim(promptBawah, btnKirimBawah);
          promptBawah.focus();
        }
      } else {
        throw new Error((respons && respons.pesan) || 'Dokumen tidak ditemukan.');
      }
    } catch (galat) {
      if (idPermintaanIni === idPermintaanMuat) {
        tampilkanToast(galat.message || 'Gagal memuat dokumen.');
      }
    }
  }

  // Delegasi Event Klik pada Wadah Terkini Sidebar (Bekerja responsif & instan untuk item lama maupun baru)
  if (daftarTerkini) {
    daftarTerkini.addEventListener('click', function (e) {
      // 1. Tombol Hapus Dokumen
      const btnHapus = e.target.closest('.item-terkini__hapus');
      if (btnHapus) {
        e.stopPropagation();
        e.preventDefault();
        const itemDiv = btnHapus.closest('.item-terkini');
        const kode = btnHapus.getAttribute('data-kode') || (itemDiv ? itemDiv.getAttribute('data-kode') : null);
        if (kode) {
          konfirmasiHapusDokumen(kode);
        }
        return;
      }

      // 2. Klik pada baris dokumen (tombol judul atau seluruh kotak item)
      const itemEl = e.target.closest('.item-terkini');
      if (itemEl) {
        e.preventDefault();
        const btnBuka = itemEl.querySelector('.item-terkini__btn');
        const kode = itemEl.getAttribute('data-kode') || (btnBuka ? btnBuka.getAttribute('data-kode') : null);
        if (kode) {
          muatDokumenPrd(kode, true);
        }
      }
    });
  }

  function pasangPendengarItemTerkini(itemEl) {
    // Delegasi pada daftarTerkini sudah menangani seluruh klik item
  }



  // ==================== SALIN MARKDOWN ====================
  if (btnSalinMd) {
    btnSalinMd.addEventListener('click', function () {
      const teksMd = textareaEditorMd ? textareaEditorMd.value : '';
      if (!teksMd) {
        tampilkanToast('Tidak ada teks Markdown untuk disalin.');
        return;
      }

      navigator.clipboard.writeText(teksMd).then(function () {
        tampilkanToast('Markdown disalin ke papan klip!');
      }).catch(function () {
        tampilkanToast('Gagal menyalin Markdown.');
      });
    });
  }

  // ==================== SUNTING INLINE & SIMPAN VERSI ====================
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-sunting-prd');
    if (!btn) return;
    const parent = btn.closest('.wadah-respon-isi');
    if (!parent || !editorInline) return;

    if (!editorInline.classList.contains('tersembunyi') && pesanAsistenSedangDiedit === parent) {
      editorInline.classList.add('tersembunyi');
      const art = parent.querySelector('.konten-markdown-prd');
      if (art) art.classList.remove('tersembunyi');
      pesanAsistenSedangDiedit = null;
      return;
    }

    if (pesanAsistenSedangDiedit) {
      const artLama = pesanAsistenSedangDiedit.querySelector('.konten-markdown-prd');
      if (artLama) artLama.classList.remove('tersembunyi');
    }

    pesanAsistenSedangDiedit = parent;
    const rawTa = parent.querySelector('.raw-markdown-asisten');
    const artikel = parent.querySelector('.konten-markdown-prd');
    if (rawTa && textareaEditorMd) {
      textareaEditorMd.value = rawTa.value;
    }

    parent.appendChild(editorInline);
    editorInline.classList.remove('tersembunyi');
    if (artikel) artikel.classList.add('tersembunyi');
    if (textareaEditorMd) textareaEditorMd.focus();
  });

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-riwayat-versi');
    if (!btn) return;
    if (backdropVersi) {
      backdropVersi.classList.remove('tersembunyi');
    }
  });

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-hapus-prd');
    if (!btn) return;
    const kodeAktif = ruangKerja.dataset.kodeAktif;
    if (kodeAktif) {
      konfirmasiHapusDokumen(kodeAktif);
    }
  });

  if (btnBatalSunting) {
    btnBatalSunting.addEventListener('click', function () {
      if (editorInline) editorInline.classList.add('tersembunyi');
      if (pesanAsistenSedangDiedit) {
        const art = pesanAsistenSedangDiedit.querySelector('.konten-markdown-prd');
        if (art) art.classList.remove('tersembunyi');
        pesanAsistenSedangDiedit = null;
      }
    });
  }

  if (btnSimpanSunting) {
    btnSimpanSunting.addEventListener('click', async function () {
      const kodeAktif = ruangKerja.dataset.kodeAktif;
      if (!kodeAktif) return;

      const isiBaru = textareaEditorMd ? textareaEditorMd.value.trim() : '';
      if (!isiBaru) {
        tampilkanToast('Isi dokumen tidak boleh kosong.');
        return;
      }

      const catatan = catatanRevisi ? catatanRevisi.value.trim() : '';

      try {
        const urlUbah = siteUrl + 'prd/' + kodeAktif + '/ubah';
        const respons = await window.PRDAI_API.kirim(urlUbah, {
          isi_markdown: isiBaru,
          catatan_versi: catatan
        });

        if (respons && respons.berhasil) {
          if (pesanAsistenSedangDiedit) {
            const artikel = pesanAsistenSedangDiedit.querySelector('.konten-markdown-prd');
            const rawTa = pesanAsistenSedangDiedit.querySelector('.raw-markdown-asisten');
            const badgeV = pesanAsistenSedangDiedit.querySelector('.badge-versi');

            if (artikel) {
              artikel.innerHTML = respons.html;
              artikel.classList.remove('tersembunyi');
            }
            if (rawTa) rawTa.value = isiBaru;
            if (badgeV) badgeV.textContent = 'Versi ' + respons.nomor_baru;
          }

          if (editorInline) editorInline.classList.add('tersembunyi');
          pesanAsistenSedangDiedit = null;
          if (catatanRevisi) catatanRevisi.value = '';

          renderMermaid();
          tampilkanToast('Perubahan tersimpan sebagai Versi ' + respons.nomor_baru + '!');
        } else {
          throw new Error((respons && respons.pesan) || 'Gagal menyimpan perubahan.');
        }
      } catch (galat) {
        tampilkanToast(galat.message || 'Gagal menyimpan perubahan.');
      }
    });
  }

  // ==================== RIWAYAT VERSI & DRAWER ====================
  function perbaruiDaftarVersi(daftarVersi) {
    if (!daftarRiwayatVersi) return;

    if (!daftarVersi || daftarVersi.length === 0) {
      daftarRiwayatVersi.innerHTML = '<div class="drawer-versi__kosong">Belum ada riwayat versi sebelumnya.</div>';
      return;
    }

    let html = '';
    daftarVersi.forEach(function (v) {
      const catatanHtml = v.catatan_revisi ? `<div class="item-versi__catatan">${escapeHtml(v.catatan_revisi)}</div>` : '';
      html += `
        <div class="item-versi">
          <div class="item-versi__info">
            <div class="item-versi__nomor">Versi ${v.nomor_versi}</div>
            <div class="item-versi__waktu">${v.dibuat_pada} &bull; ${escapeHtml(v.dibuat_oleh || 'AI')}</div>
            ${catatanHtml}
          </div>
          <button type="button" class="btn-pulihkan-versi" data-versi="${v.nomor_versi}">Pulihkan</button>
        </div>
      `;
    });

    daftarRiwayatVersi.innerHTML = html;
    pasangPendengarPulihkan();
  }

  function pasangPendengarPulihkan() {
    if (!daftarRiwayatVersi) return;
    daftarRiwayatVersi.querySelectorAll('.btn-pulihkan-versi').forEach(function (btn) {
      btn.addEventListener('click', async function () {
        const nomorVersi = this.getAttribute('data-versi');
        const kodeAktif = ruangKerja.dataset.kodeAktif;
        if (!nomorVersi || !kodeAktif) return;

        try {
          const urlPulihkan = siteUrl + 'prd/' + kodeAktif + '/pulihkan/' + nomorVersi;
          const respons = await window.PRDAI_API.kirim(urlPulihkan, {}, 'POST');

          if (respons && respons.berhasil) {
            // Perbarui respon asisten terakhir di thread
            const asstList = daftarPesanChat ? daftarPesanChat.querySelectorAll('.wadah-respon-asisten') : [];
            if (asstList.length > 0) {
              const lastAsst = asstList[asstList.length - 1];
              const artikel = lastAsst.querySelector('.konten-markdown-prd');
              const rawTa = lastAsst.querySelector('.raw-markdown-asisten');
              const badgeV = lastAsst.querySelector('.badge-versi');
              if (artikel) artikel.innerHTML = respons.html;
              if (rawTa) rawTa.value = respons.markdown;
              if (badgeV) badgeV.textContent = 'Versi ' + respons.nomor_baru;
            }
            if (textareaEditorMd) textareaEditorMd.value = respons.markdown;
            if (backdropVersi) backdropVersi.classList.add('tersembunyi');
            renderMermaid();
            tampilkanToast('Dipulihkan ke versi ' + nomorVersi + ' (sebagai Versi ' + respons.nomor_baru + ')');
          } else {
            throw new Error((respons && respons.pesan) || 'Gagal memulihkan versi.');
          }
        } catch (galat) {
          tampilkanToast(galat.message || 'Gagal memulihkan versi.');
        }
      });
    });
  }

  pasangPendengarPulihkan();

  if (btnRiwayatVersi) {
    btnRiwayatVersi.addEventListener('click', function () {
      backdropVersi.classList.remove('tersembunyi');
    });
  }

  if (btnTutupVersi) {
    btnTutupVersi.addEventListener('click', function () {
      backdropVersi.classList.add('tersembunyi');
    });
  }

  if (backdropVersi) {
    backdropVersi.addEventListener('click', function (e) {
      if (e.target === backdropVersi) {
        backdropVersi.classList.add('tersembunyi');
      }
    });
  }

  // ==================== HAPUS DOKUMEN PRD ====================
  function konfirmasiHapusDokumen(kode) {
    kodeMenungguHapus = kode;
    modalHapusBackdrop.classList.remove('tersembunyi');
  }

  if (btnHapusPrd) {
    btnHapusPrd.addEventListener('click', function () {
      const kodeAktif = ruangKerja.dataset.kodeAktif;
      if (kodeAktif) {
        konfirmasiHapusDokumen(kodeAktif);
      }
    });
  }

  if (btnBatalHapus) {
    btnBatalHapus.addEventListener('click', function () {
      modalHapusBackdrop.classList.add('tersembunyi');
      kodeMenungguHapus = null;
    });
  }

  if (modalHapusBackdrop) {
    modalHapusBackdrop.addEventListener('click', function (e) {
      if (e.target === modalHapusBackdrop) {
        modalHapusBackdrop.classList.add('tersembunyi');
        kodeMenungguHapus = null;
      }
    });
  }

  // Tutup semua modal dan drawer saat tombol Escape ditekan
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      [modalPustakaBackdrop, modalProfilBackdrop, modalTingkatkanBackdrop, backdropVersi, modalHapusBackdrop].forEach(function (el) {
        if (el) el.classList.add('tersembunyi');
      });
      kodeMenungguHapus = null;
    }
  });

  if (btnKonfirmasiHapus) {
    btnKonfirmasiHapus.addEventListener('click', async function () {
      if (!kodeMenungguHapus) return;

      const kode = kodeMenungguHapus;
      modalHapusBackdrop.classList.add('tersembunyi');

      try {
        const urlHapus = siteUrl + 'prd/' + kode + '/hapus';
        const respons = await window.PRDAI_API.kirim(urlHapus, {}, 'POST');

        if (respons && respons.berhasil) {
          const itemSidebar = daftarTerkini.querySelector(`[data-kode="${kode}"]`);
          if (itemSidebar) {
            itemSidebar.remove();
          }

          // Periksa apakah daftar riwayat sekarang kosong
          const sisaItem = daftarTerkini ? daftarTerkini.querySelectorAll('.item-terkini') : [];
          if (sisaItem.length === 0 && wadahTerkiniSidebar) {
            wadahTerkiniSidebar.classList.add('tersembunyi');
          }

          // Hapus juga dari pustaka jika terbuka
          if (pustakaDaftarGulung) {
            const itemPustaka = pustakaDaftarGulung.querySelector(`[data-kode="${kode}"]`);
            if (itemPustaka) itemPustaka.remove();
            perbaruiBadgeTotalPustaka();
            const sisaPustaka = pustakaDaftarGulung.querySelectorAll('.item-pustaka');
            if (sisaPustaka.length === 0) {
              pustakaDaftarGulung.innerHTML = `
                <div class="pustaka-kosong" id="pustaka-keadaan-kosong">
                  <p class="teks-muted">Belum ada dokumen yang disimpan.</p>
                </div>
              `;
            }
          }

          if (ruangKerja.dataset.kodeAktif === kode) {
            resetKePrdBaru();
          }

          tampilkanToast('Dokumen berhasil dihapus.');
        } else {
          throw new Error((respons && respons.pesan) || 'Gagal menghapus dokumen.');
        }
      } catch (galat) {
        tampilkanToast(galat.message || 'Gagal menghapus dokumen.');
      } finally {
        kodeMenungguHapus = null;
      }
    });
  }

  // ==================== PENANGANAN POPSTATE BROWSER ====================
  window.addEventListener('popstate', function () {
    const jalur = window.location.pathname;
    const cocokKode = jalur.match(/\/prd\/([a-zA-Z0-9]{16})/);
    if (cocokKode && cocokKode[1]) {
      muatDokumenPrd(cocokKode[1], false);
    } else {
      resetKePrdBaru(false);
    }
  });

  // ==================== INISIALISASI SAAT HALAMAN DIBUKA ====================
  perbaruiIkonKirim(promptUtama, btnKirimUtama);
  perbaruiIkonKirim(promptBawah, btnKirimBawah);

  // Jika halaman dibuka langsung dengan chat aktif (URL /prd/{kode})
  if (ruangKerja.dataset.kodeAktif && scrollRuangKerja) {
    scrollRuangKerja.scrollTop = scrollRuangKerja.scrollHeight;
    if (promptBawah) {
      promptBawah.focus();
    }
  }

});
