/**
 * PRDAI — Interaktivitas Panel Upgrade Paket (Gambar 1) & Tagihan (Gambar 2).
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const drawerUpgrade = document.getElementById('modal-tingkatkan-backdrop');
    const drawerTagihan = document.getElementById('modal-tagihan-backdrop');
    const drawerBantuan = document.getElementById('modal-bantuan-backdrop');
    const drawerPenggunaan = document.getElementById('modal-penggunaan-backdrop');

    // Tombol Tutup
    const btnTutupUpgrade = document.getElementById('tombol-tutup-tingkatkan');
    const btnTutupTagihan = document.getElementById('tombol-tutup-tagihan');
    const btnTutupBantuan = document.getElementById('tombol-tutup-bantuan');
    const btnTutupPenggunaan = document.getElementById('tombol-tutup-penggunaan');

    const drawerProfil = document.getElementById('drawer-profil');
    const popoverMenu = document.getElementById('popover-menu-profil');

    function tutupSemuaDrawerKecuali(kecuali) {
      if (popoverMenu) popoverMenu.classList.add('tersembunyi');
      if (drawerProfil && drawerProfil !== kecuali) drawerProfil.classList.add('tersembunyi');
      if (drawerUpgrade && drawerUpgrade !== kecuali) drawerUpgrade.classList.add('tersembunyi');
      if (drawerTagihan && drawerTagihan !== kecuali) drawerTagihan.classList.add('tersembunyi');
      if (drawerBantuan && drawerBantuan !== kecuali) drawerBantuan.classList.add('tersembunyi');
      if (drawerPenggunaan && drawerPenggunaan !== kecuali) drawerPenggunaan.classList.add('tersembunyi');
    }

    // Monitor observer atau hook pemicu buka
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const target = mutation.target;
          if (!target.classList.contains('tersembunyi')) {
            tutupSemuaDrawerKecuali(target);
          }
        }
      });
    });

    if (drawerUpgrade) observer.observe(drawerUpgrade, { attributes: true });
    if (drawerTagihan) observer.observe(drawerTagihan, { attributes: true });
    if (drawerProfil) observer.observe(drawerProfil, { attributes: true });
    if (drawerBantuan) observer.observe(drawerBantuan, { attributes: true });
    if (drawerPenggunaan) observer.observe(drawerPenggunaan, { attributes: true });

    // ==================== BANTUAN TOAST ====================
    function tampilkanToast(pesan) {
      let toast = document.getElementById('toast-notifikasi');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast-notifikasi';
        toast.className = 'toast-notifikasi';
        document.body.appendChild(toast);
      }
      toast.textContent = pesan;
      toast.classList.remove('tersembunyi');
      toast.classList.add('aktif');

      setTimeout(function () {
        toast.classList.remove('aktif');
        setTimeout(function () {
          toast.classList.add('tersembunyi');
        }, 300);
      }, 3500);
    }

    // ==================== DRAWER UPGRADE PAKET (GAMBAR 1) ====================
    // 1. Toggle Tab: Pribadi | Bisnis
    const tabBtns = document.querySelectorAll('.drawer-tingkatkan__tab-btn');
    tabBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        tabBtns.forEach(function (b) {
          b.classList.remove('drawer-tingkatkan__tab-btn--aktif');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('drawer-tingkatkan__tab-btn--aktif');
        btn.setAttribute('aria-selected', 'true');

        const kategori = btn.getAttribute('data-kategori');
        if (kategori === 'bisnis') {
          tampilkanToast('Menampilkan paket tim & bisnis dengan lisensi terpusat.');
        } else {
          tampilkanToast('Menampilkan paket langganan personal.');
        }
      });
    });

    // 2. Tombol Pilih Paket (Simpan perubahan ke basis data real)
    document.querySelectorAll('.btn-pilih-paket').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const paket = btn.getAttribute('data-paket') || 'Plus';
        const harga = btn.getAttribute('data-harga') || '349.000';
        tampilkanToast('Memproses pergantian ke paket ' + paket + ' di basis data...');

        const formData = new FormData();
        if (window.PRDAI_CSRF) {
          formData.append(window.PRDAI_CSRF.ambilNama(), window.PRDAI_CSRF.ambilNilai());
        }
        formData.append('paket', paket);

        const baseUrl = window.location.pathname.includes('/PRDAI/public/') 
          ? window.location.origin + '/PRDAI/public/' 
          : window.location.origin + '/';

        fetch(baseUrl + 'langganan/ubah', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (json.csrf_hash && window.PRDAI_CSRF) {
            window.PRDAI_CSRF.perbarui(json.csrf_hash);
          }
          if (json.sukses) {
            tampilkanToast('Paket berhasil diubah ke ' + paket + '! Memuat ulang data...');
            setTimeout(function () {
              window.location.reload();
            }, 800);
          } else {
            tampilkanToast(json.pesan || 'Gagal mengubah paket langganan.');
          }
        })
        .catch(function () {
          tampilkanToast('Gagal menghubungi server.');
        });
      });
    });

    // 3. Tutup Upgrade
    if (btnTutupUpgrade && drawerUpgrade) {
      btnTutupUpgrade.addEventListener('click', function () {
        drawerUpgrade.classList.add('tersembunyi');
      });
    }

    // ==================== DRAWER TAGIHAN (GAMBAR 2) ====================
    // 1. Tutup Tagihan
    if (btnTutupTagihan && drawerTagihan) {
      btnTutupTagihan.addEventListener('click', function () {
        drawerTagihan.classList.add('tersembunyi');
      });
    }

    // 2. Tombol Ubah Paket (Beralih langsung ke drawer upgrade Gambar 1)
    const btnUbahPaket = document.getElementById('tombol-ubah-paket-tagihan');
    if (btnUbahPaket && drawerUpgrade && drawerTagihan) {
      btnUbahPaket.addEventListener('click', function () {
        drawerTagihan.classList.add('tersembunyi');
        drawerUpgrade.classList.remove('tersembunyi');
      });
    }

    // 3. Tombol Batalkan Langganan (Simpan status ke basis data)
    const btnBatalLangganan = document.getElementById('tombol-batalkan-langganan');
    if (btnBatalLangganan) {
      btnBatalLangganan.addEventListener('click', function () {
        if (!confirm('Apakah Anda yakin ingin membatalkan perpanjangan langganan? Fitur tetap aktif hingga akhir periode pembayaran berjalan.')) {
          return;
        }

        tampilkanToast('Memproses pembatalan di basis data...');

        const formData = new FormData();
        if (window.PRDAI_CSRF) {
          formData.append(window.PRDAI_CSRF.ambilNama(), window.PRDAI_CSRF.ambilNilai());
        }

        const baseUrl = window.location.pathname.includes('/PRDAI/public/') 
          ? window.location.origin + '/PRDAI/public/' 
          : window.location.origin + '/';

        fetch(baseUrl + 'langganan/batal', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (json.csrf_hash && window.PRDAI_CSRF) {
            window.PRDAI_CSRF.perbarui(json.csrf_hash);
          }
          if (json.sukses) {
            tampilkanToast('Perpanjangan langganan berhasil dibatalkan.');
            setTimeout(function () {
              window.location.reload();
            }, 1000);
          } else {
            tampilkanToast(json.pesan || 'Gagal membatalkan langganan.');
          }
        })
        .catch(function () {
          tampilkanToast('Gagal menghubungi server.');
        });
      });
    }

    // 4. Tombol Perbarui Kartu Pembayaran
    const btnPerbaruiKartu = document.getElementById('tombol-perbarui-kartu');
    if (btnPerbaruiKartu) {
      btnPerbaruiKartu.addEventListener('click', function () {
        tampilkanToast('Membuka formulir perbaruan kartu pembayaran terenkripsi...');
      });
    }

    // 5. Tombol Unduh PDF Invoice
    document.querySelectorAll('.drawer-tagihan__btn-unduh').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const inv = btn.getAttribute('data-invoice') || 'INV';
        tampilkanToast('Mengunduh faktur tagihan ' + inv + '.pdf...');
      });
    });

    // 6. Tombol Layar Penuh (Expand / Fullscreen)
    const btnExpandTagihan = document.getElementById('tombol-expand-tagihan');
    if (btnExpandTagihan && drawerTagihan) {
      btnExpandTagihan.addEventListener('click', function () {
        drawerTagihan.classList.toggle('drawer-tagihan--fullscreen');
        const isFull = drawerTagihan.classList.contains('drawer-tagihan--fullscreen');
        tampilkanToast(isFull ? 'Beralih ke tampilan layar penuh.' : 'Kembali ke tampilan standar.');
      });
    }

    // 7. Tombol Portal Eksternal
    const btnPortalTagihan = document.getElementById('tombol-portal-tagihan');
    if (btnPortalTagihan) {
      btnPortalTagihan.addEventListener('click', function () {
        tampilkanToast('Membuka portal pelanggan Stripe / Midtrans aman...');
      });
    }

    // ==================== DRAWER PUSAT BANTUAN (GAMBAR 1) ====================
    // 1. Tutup Bantuan
    if (btnTutupBantuan && drawerBantuan) {
      btnTutupBantuan.addEventListener('click', function () {
        drawerBantuan.classList.add('tersembunyi');
      });
    }

    // 2. FAQ Accordion Toggle
    document.querySelectorAll('.drawer-bantuan__faq-pemicu').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const item = btn.closest('.drawer-bantuan__faq-item');
        if (!item) return;
        const buka = item.classList.contains('buka');
        
        // Tutup item FAQ lainnya untuk tampilan rapi
        document.querySelectorAll('.drawer-bantuan__faq-item').forEach(function (el) {
          el.classList.remove('buka');
          const pemicu = el.querySelector('.drawer-bantuan__faq-pemicu');
          if (pemicu) pemicu.setAttribute('aria-expanded', 'false');
        });

        if (!buka) {
          item.classList.add('buka');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });

    // 3. Pencarian Bantuan Interaktif
    const inputCariBantuan = document.getElementById('masukan-cari-bantuan');
    const btnBersihCari = document.getElementById('btn-bersihkan-cari-bantuan');
    if (inputCariBantuan) {
      inputCariBantuan.addEventListener('input', function () {
        const query = inputCariBantuan.value.toLowerCase().trim();
        if (btnBersihCari) {
          if (query.length > 0) {
            btnBersihCari.classList.remove('tersembunyi');
          } else {
            btnBersihCari.classList.add('tersembunyi');
          }
        }

        // Filter FAQ items
        document.querySelectorAll('.drawer-bantuan__faq-item').forEach(function (faq) {
          const text = faq.textContent.toLowerCase();
          if (query === '' || text.includes(query)) {
            faq.style.display = '';
            if (query !== '') faq.classList.add('buka');
          } else {
            faq.style.display = 'none';
          }
        });

        // Filter Category cards
        document.querySelectorAll('.drawer-bantuan__kategori-kartu').forEach(function (card) {
          const text = card.textContent.toLowerCase();
          if (query === '' || text.includes(query)) {
            card.style.opacity = '1';
          } else {
            card.style.opacity = '0.4';
          }
        });
      });
    }

    if (btnBersihCari && inputCariBantuan) {
      btnBersihCari.addEventListener('click', function () {
        inputCariBantuan.value = '';
        btnBersihCari.classList.add('tersembunyi');
        inputCariBantuan.dispatchEvent(new Event('input'));
        inputCariBantuan.focus();
      });
    }

    // 4. Kartu Kategori Bantuan Click
    document.querySelectorAll('.drawer-bantuan__kategori-kartu').forEach(function (card) {
      card.addEventListener('click', function () {
        const judul = card.querySelector('.drawer-bantuan__kategori-judul')?.textContent || '';
        tampilkanToast('Membuka dokumentasi kategori: ' + judul);
      });
    });

    // 5. Tombol Hubungi Kami
    const btnBantuanTiket = document.getElementById('btn-bantuan-tiket');
    if (btnBantuanTiket) {
      btnBantuanTiket.addEventListener('click', function () {
        tampilkanToast('Membuka formulir tiket dukungan resmi...');
        window.location.href = 'mailto:support@prdai.id?subject=Tiket%20Bantuan%20PRDAI';
      });
    }

    const btnBantuanChat = document.getElementById('btn-bantuan-chat');
    if (btnBantuanChat) {
      btnBantuanChat.addEventListener('click', function () {
        tampilkanToast('Menghubungkan sesi live chat dukungan pelanggan...');
      });
    }

    // ==================== DRAWER PENGGUNAAN (GAMBAR 2) ====================
    // 1. Tutup Penggunaan
    if (btnTutupPenggunaan && drawerPenggunaan) {
      btnTutupPenggunaan.addEventListener('click', function () {
        drawerPenggunaan.classList.add('tersembunyi');
      });
    }

    // 2. Interaktivitas Batang Chart
    document.querySelectorAll('.drawer-penggunaan__bar-batang').forEach(function (batang) {
      batang.addEventListener('click', function () {
        const barKolom = batang.closest('.drawer-penggunaan__bar-kolom');
        const label = barKolom?.querySelector('.drawer-penggunaan__bar-label')?.textContent || '';
        const nilai = barKolom?.querySelector('.drawer-penggunaan__bar-nilai')?.textContent || '';
        tampilkanToast('Hari ' + label + ': Konsumsi ' + nilai + ' token.');
      });
    });

    // Otomatis tutup panel saat pengguna klik tombol sidebar
    const btnPrdBaru = document.getElementById('tombol-prd-baru');
    const btnSemuaPrd = document.getElementById('tombol-semua-prd');
    const wadahTerkini = document.getElementById('wadah-terkini-sidebar');

    if (btnPrdBaru) {
      btnPrdBaru.addEventListener('click', function () {
        if (drawerUpgrade) drawerUpgrade.classList.add('tersembunyi');
        if (drawerTagihan) drawerTagihan.classList.add('tersembunyi');
        if (drawerBantuan) drawerBantuan.classList.add('tersembunyi');
        if (drawerPenggunaan) drawerPenggunaan.classList.add('tersembunyi');
      });
    }

    if (btnSemuaPrd) {
      btnSemuaPrd.addEventListener('click', function () {
        if (drawerUpgrade) drawerUpgrade.classList.add('tersembunyi');
        if (drawerTagihan) drawerTagihan.classList.add('tersembunyi');
        if (drawerBantuan) drawerBantuan.classList.add('tersembunyi');
        if (drawerPenggunaan) drawerPenggunaan.classList.add('tersembunyi');
      });
    }

    if (wadahTerkini) {
      wadahTerkini.addEventListener('click', function (e) {
        if (e.target.closest('.item-terkini__btn') || e.target.closest('.item-terkini__hapus')) {
          if (drawerUpgrade) drawerUpgrade.classList.add('tersembunyi');
          if (drawerTagihan) drawerTagihan.classList.add('tersembunyi');
          if (drawerBantuan) drawerBantuan.classList.add('tersembunyi');
          if (drawerPenggunaan) drawerPenggunaan.classList.add('tersembunyi');
        }
      });
    }

    // Tutup panel dengan tombol Escape jika sedang terbuka
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (drawerUpgrade && !drawerUpgrade.classList.contains('tersembunyi')) {
          drawerUpgrade.classList.add('tersembunyi');
        }
        if (drawerTagihan && !drawerTagihan.classList.contains('tersembunyi')) {
          drawerTagihan.classList.add('tersembunyi');
        }
        if (drawerBantuan && !drawerBantuan.classList.contains('tersembunyi')) {
          drawerBantuan.classList.add('tersembunyi');
        }
        if (drawerPenggunaan && !drawerPenggunaan.classList.contains('tersembunyi')) {
          drawerPenggunaan.classList.add('tersembunyi');
        }
      }
    });
  });
})();
