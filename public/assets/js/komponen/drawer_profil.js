/**
 * PRDAI — Interaktivitas Drawer Profil Samping Kanan (Slide-Over).
 *
 * Mengelola:
 * 1. Buka & Tutup drawer dari berbagai pemicu (popover, menu profil, preview hover).
 * 2. Toggle visibilitas kata sandi (ikon mata).
 * 3. Kirim AJAX formulir Edit Profil (Nama & Telepon) dan Ganti Sandi tanpa reload.
 * 4. Heatmap tooltip & tab di dalam drawer.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const backdrop = document.getElementById('backdrop-drawer-profil');
    const drawer = document.getElementById('drawer-profil');
    const btnTutup = document.getElementById('tombol-tutup-drawer-profil');

    if (!backdrop || !drawer) return;

    function bukaDrawer() {
      // Tutup popover menu jika sedang terbuka
      const popoverMenu = document.getElementById('popover-menu-profil');
      if (popoverMenu) {
        popoverMenu.classList.add('tersembunyi');
      }

      // Sembunyikan ringkasan hover jika sedang tampil
      const hoverRingkasan = document.getElementById('ringkasan-profil-hover');
      if (hoverRingkasan) {
        hoverRingkasan.classList.add('tersembunyi');
      }

      backdrop.classList.remove('tersembunyi');
      drawer.classList.remove('tersembunyi');
    }

    function tutupDrawer() {
      backdrop.classList.add('tersembunyi');
      drawer.classList.add('tersembunyi');
    }

    // Ekspor ke objek global agar bisa dipanggil dari mana saja
    window.PRDAI_DrawerProfil = {
      buka: bukaDrawer,
      tutup: tutupDrawer,
      toggle: function () {
        if (drawer.classList.contains('tersembunyi')) {
          bukaDrawer();
        } else {
          tutupDrawer();
        }
      }
    };

    // Event Tutup
    if (btnTutup) {
      btnTutup.addEventListener('click', function (e) {
        e.preventDefault();
        tutupDrawer();
      });
    }

    backdrop.addEventListener('click', function () {
      tutupDrawer();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !drawer.classList.contains('tersembunyi')) {
        tutupDrawer();
      }
    });

    // Otomatis tutup panel profil saat pengguna membuat PRD baru atau memilih dokumen di sidebar
    const btnPrdBaru = document.getElementById('tombol-prd-baru');
    const btnSemuaPrd = document.getElementById('tombol-semua-prd');
    const wadahTerkini = document.getElementById('wadah-terkini-sidebar');

    if (btnPrdBaru) {
      btnPrdBaru.addEventListener('click', function () {
        tutupDrawer();
      });
    }

    if (btnSemuaPrd) {
      btnSemuaPrd.addEventListener('click', function () {
        tutupDrawer();
      });
    }

    if (wadahTerkini) {
      wadahTerkini.addEventListener('click', function (e) {
        if (e.target.closest('.item-terkini__btn') || e.target.closest('.item-terkini__hapus')) {
          tutupDrawer();
        }
      });
    }

    // Pemicu tombol dengan atribut data-aksi="buka-drawer-profil"
    document.querySelectorAll('[data-aksi="buka-drawer-profil"]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        bukaDrawer();
      });
    });

    // ==================== TOGGLE MATA DI DALAM DRAWER ====================
    drawer.querySelectorAll('.btn-toggle-mata').forEach(function (btn) {
      if (btn.dataset.toggleInited) return;
      btn.dataset.toggleInited = '1';

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const targetId = btn.getAttribute('data-target');
        if (!targetId) return;

        const inputSandi = drawer.querySelector('#' + targetId) || document.getElementById(targetId);
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

    // ==================== AJAX SUBMIT EDIT NAMA & TELEPON ====================
    const formNama = drawer.querySelector('#form-edit-nama');
    if (formNama) {
      formNama.addEventListener('submit', function (e) {
        e.preventDefault();

        const btnSimpan = formNama.querySelector('button[type="submit"]');
        const teksAsli = btnSimpan ? btnSimpan.innerHTML : 'Simpan';
        if (btnSimpan) {
          btnSimpan.disabled = true;
          btnSimpan.innerHTML = 'Menyimpan...';
        }

        const formData = new FormData(formNama);

        fetch(formNama.action || (window.location.origin + '/PRDAI/public/profil'), {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(function (res) { return res.json(); })
        .then(function (hasil) {
          if (btnSimpan) {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = teksAsli;
          }

          if (hasil.sukses) {
            // Perbarui nama di antarmuka secara realtime
            const namaBaru = (hasil.data && hasil.data.nama) || formData.get('nama');
            const teleponBaru = (hasil.data && hasil.data.telepon) || formData.get('telepon');

            // Hitung inisial baru
            const parts = namaBaru.trim().split(/\s+/);
            let inisialBaru = 'AP';
            if (parts.length >= 2) {
              inisialBaru = (parts[0][0] + parts[1][0]).toUpperCase();
            } else if (parts.length === 1 && parts[0].length > 0) {
              inisialBaru = parts[0].substring(0, 2).toUpperCase();
            }

            // Update DOM di workspace & drawer
            document.querySelectorAll('#nama-user-sidebar, #popover-user-nama, .profil-nama-utama').forEach(function (el) {
              el.textContent = namaBaru;
            });
            document.querySelectorAll('#salam-user-nama').forEach(function (el) {
              el.textContent = namaBaru;
            });
            document.querySelectorAll('#avatar-user-sidebar, #avatar-user-header, #popover-user-avatar, .profil-avatar-besar, .ringkasan-profil__avatar').forEach(function (el) {
              el.textContent = inisialBaru;
            });

            // Tampilkan Toast
            tampilkanToast(hasil.pesan || 'Profil berhasil diperbarui.');
          } else {
            alert(hasil.pesan || 'Gagal memperbarui profil.');
          }
        })
        .catch(function (err) {
          if (btnSimpan) {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = teksAsli;
          }
          console.error('Galat simpan profil:', err);
          alert('Terjadi kesalahan jaringan saat menyimpan profil.');
        });
      });
    }

    // ==================== AJAX SUBMIT GANTI KATA SANDI ====================
    const formSandiDrawer = drawer.querySelector('#form-ganti-sandi');
    if (formSandiDrawer) {
      formSandiDrawer.addEventListener('submit', function (e) {
        e.preventDefault();

        const sandiBaru = formSandiDrawer.querySelector('#masukan-sandi-baru');
        const konfirmasi = formSandiDrawer.querySelector('#masukan-konfirmasi-sandi');

        if (sandiBaru && sandiBaru.value.length < 10) {
          alert('Kata sandi baru minimal harus 10 karakter.');
          sandiBaru.focus();
          return;
        }

        if (sandiBaru && konfirmasi && sandiBaru.value !== konfirmasi.value) {
          alert('Konfirmasi kata sandi tidak cocok dengan kata sandi baru.');
          konfirmasi.focus();
          return;
        }

        const btnSimpan = formSandiDrawer.querySelector('button[type="submit"]');
        const teksAsli = btnSimpan ? btnSimpan.innerHTML : 'Simpan';
        if (btnSimpan) {
          btnSimpan.disabled = true;
          btnSimpan.innerHTML = 'Memperbarui...';
        }

        const formData = new FormData(formSandiDrawer);

        fetch(formSandiDrawer.action || (window.location.origin + '/PRDAI/public/profil/sandi'), {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(function (res) { return res.json(); })
        .then(function (hasil) {
          if (btnSimpan) {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = teksAsli;
          }

          if (hasil.sukses) {
            formSandiDrawer.reset();
            tampilkanToast(hasil.pesan || 'Kata sandi berhasil diperbarui.');
          } else {
            alert(hasil.pesan || 'Gagal mengubah kata sandi.');
          }
        })
        .catch(function (err) {
          if (btnSimpan) {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = teksAsli;
          }
          console.error('Galat ganti sandi:', err);
          alert('Terjadi kesalahan jaringan saat memperbarui kata sandi.');
        });
      });
    }

    // Helper Toast Notifikasi
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
  });
})();
