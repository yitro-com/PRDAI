/**
 * PRDAI — Interaktivitas Popover Menu Profil & Modal Terkait.
 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  const popoverMenu = document.getElementById('popover-menu-profil');
  const btnBukaProfilSidebar = document.getElementById('tombol-buka-profil');
  const btnAvatarHeader = document.getElementById('tombol-avatar-header');
  const ringkasanProfilHover = document.getElementById('ringkasan-profil-hover');

  // Modals
  const modalUpgrade = document.getElementById('modal-tingkatkan-backdrop');
  const modalTagihan = document.getElementById('modal-tagihan-backdrop');
  const modalPenggunaan = document.getElementById('modal-penggunaan-backdrop');
  const modalBantuan = document.getElementById('modal-bantuan-backdrop');

  // Tombol Menu
  const btnItemUpgrade = document.getElementById('item-menu-upgrade');
  const btnItemTagihan = document.getElementById('item-menu-tagihan');
  const btnItemPenggunaan = document.getElementById('item-menu-penggunaan');
  const btnItemBantuan = document.getElementById('item-menu-bantuan');
  const btnItemProfil = document.getElementById('item-menu-profil');
  const btnUserBaris = document.getElementById('item-menu-user-baris');

  // Tombol Tutup Modal Tagihan
  const btnTutupTagihan = document.getElementById('tombol-tutup-tagihan');
  const btnTutupTagihanBawah = document.getElementById('tombol-tutup-tagihan-bawah');
  const btnUpgradeDariTagihan = document.getElementById('tombol-upgrade-dari-tagihan');

  // Tombol Tutup Modal Penggunaan
  const btnTutupPenggunaan = document.getElementById('tombol-tutup-penggunaan');
  const btnTutupPenggunaanBawah = document.getElementById('tombol-tutup-penggunaan-bawah');

  // Tombol Tutup Modal Bantuan
  const btnTutupBantuan = document.getElementById('tombol-tutup-bantuan');
  const btnTutupBantuanBawah = document.getElementById('tombol-tutup-bantuan-bawah');

  if (!popoverMenu) return;

  function sembunyikanRingkasan() {
    if (ringkasanProfilHover) {
      ringkasanProfilHover.classList.add('tersembunyi');
    }
  }

  function tampilkanRingkasan() {
    // Hanya tampilkan jika popover menu sedang tertutup
    if (ringkasanProfilHover && popoverMenu.classList.contains('tersembunyi')) {
      ringkasanProfilHover.classList.remove('tersembunyi');
    }
  }

  function bukaPopover(pemicu) {
    if (!pemicu) return;
    sembunyikanRingkasan();

    const rect = pemicu.getBoundingClientRect();
    const ruangBawah = window.innerHeight - rect.bottom;

    // Posisi popover: jika di bagian bawah sidebar, posisikan ke atas
    if (ruangBawah < 300) {
      popoverMenu.style.position = 'fixed';
      popoverMenu.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
      popoverMenu.style.left = Math.max(12, rect.left) + 'px';
      popoverMenu.style.top = 'auto';
      popoverMenu.style.right = 'auto';
    } else {
      // Posisi di bawah tombol (misal dari header avatar)
      popoverMenu.style.position = 'fixed';
      popoverMenu.style.top = (rect.bottom + 8) + 'px';
      popoverMenu.style.right = Math.max(12, window.innerWidth - rect.right) + 'px';
      popoverMenu.style.left = 'auto';
      popoverMenu.style.bottom = 'auto';
    }

    popoverMenu.classList.remove('tersembunyi');
  }

  const wadahUserBaris = document.getElementById('wadah-user-baris-popover');
  const flyoutRingkasan = document.getElementById('flyout-ringkasan-profil');

  function tutupPopover() {
    popoverMenu.classList.add('tersembunyi');
    if (flyoutRingkasan) {
      flyoutRingkasan.classList.add('tersembunyi');
    }
  }

  function togglePopover(pemicu) {
    if (popoverMenu.classList.contains('tersembunyi')) {
      bukaPopover(pemicu);
    } else {
      tutupPopover();
    }
  }

  // Buka Drawer Profil Samping Kanan
  function triggerBukaDrawerProfil(e) {
    if (e) e.preventDefault();
    tutupPopover();
    sembunyikanRingkasan();
    if (flyoutRingkasan) {
      flyoutRingkasan.classList.add('tersembunyi');
    }

    if (window.PRDAI_DrawerProfil && typeof window.PRDAI_DrawerProfil.buka === 'function') {
      window.PRDAI_DrawerProfil.buka();
    } else {
      window.location.href = window.location.origin + '/PRDAI/public/profil';
    }
  }

  if (btnItemProfil) {
    btnItemProfil.addEventListener('click', triggerBukaDrawerProfil);
  }

  if (btnUserBaris) {
    btnUserBaris.addEventListener('click', triggerBukaDrawerProfil);
  }

  // Hover di kotak akun pengguna (Sesuai Permintaan: muncul ringkasan profil di sebelah kanan)
  if (wadahUserBaris && flyoutRingkasan) {
    wadahUserBaris.addEventListener('mouseenter', function () {
      flyoutRingkasan.classList.remove('tersembunyi');
    });

    wadahUserBaris.addEventListener('mouseleave', function () {
      flyoutRingkasan.classList.add('tersembunyi');
    });

    flyoutRingkasan.addEventListener('click', function (e) {
      e.stopPropagation();
      triggerBukaDrawerProfil(e);
    });
  }

  // Hover & klik profil di sidebar bawah -> buka popover menu seperti biasa
  if (btnBukaProfilSidebar) {
    btnBukaProfilSidebar.addEventListener('mouseenter', tampilkanRingkasan);
    btnBukaProfilSidebar.addEventListener('mouseleave', sembunyikanRingkasan);
    btnBukaProfilSidebar.addEventListener('click', function (e) {
      e.stopPropagation();
      sembunyikanRingkasan();
      togglePopover(btnBukaProfilSidebar);
    });
  }

  // Klik akun/avatar di bar atas -> hanya tombol kanan ini yang langsung buka panel profil di kanan
  if (btnAvatarHeader) {
    btnAvatarHeader.addEventListener('click', function (e) {
      e.stopPropagation();
      tutupPopover();
      if (window.PRDAI_DrawerProfil && typeof window.PRDAI_DrawerProfil.toggle === 'function') {
        window.PRDAI_DrawerProfil.toggle();
      } else {
        window.location.href = window.location.origin + '/PRDAI/public/profil';
      }
    });
  }

  // Tutup bila klik di luar
  document.addEventListener('click', function (e) {
    if (!popoverMenu.contains(e.target)) {
      tutupPopover();
    }
  });

  // Tutup dengan Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      tutupPopover();
    }
  });

  // Aksi Menu: Upgrade
  if (btnItemUpgrade && modalUpgrade) {
    btnItemUpgrade.addEventListener('click', function () {
      tutupPopover();
      modalUpgrade.classList.remove('tersembunyi');
    });
  }

  // Aksi Menu: Tagihan
  if (btnItemTagihan && modalTagihan) {
    btnItemTagihan.addEventListener('click', function () {
      tutupPopover();
      modalTagihan.classList.remove('tersembunyi');
    });
  }

  // Aksi Menu: Penggunaan
  if (btnItemPenggunaan && modalPenggunaan) {
    btnItemPenggunaan.addEventListener('click', function () {
      tutupPopover();
      modalPenggunaan.classList.remove('tersembunyi');
    });
  }

  // Aksi Menu: Bantuan
  if (btnItemBantuan && modalBantuan) {
    btnItemBantuan.addEventListener('click', function () {
      tutupPopover();
      modalBantuan.classList.remove('tersembunyi');
    });
  }

  // Event tutup modal Tagihan
  if (btnTutupTagihan) btnTutupTagihan.addEventListener('click', function () { modalTagihan.classList.add('tersembunyi'); });
  if (btnTutupTagihanBawah) btnTutupTagihanBawah.addEventListener('click', function () { modalTagihan.classList.add('tersembunyi'); });
  if (modalTagihan) {
    modalTagihan.addEventListener('click', function (e) {
      if (e.target === modalTagihan) modalTagihan.classList.add('tersembunyi');
    });
  }

  if (btnUpgradeDariTagihan && modalUpgrade) {
    btnUpgradeDariTagihan.addEventListener('click', function () {
      if (modalTagihan) modalTagihan.classList.add('tersembunyi');
      modalUpgrade.classList.remove('tersembunyi');
    });
  }

  // Event tutup modal Penggunaan
  if (btnTutupPenggunaan) btnTutupPenggunaan.addEventListener('click', function () { modalPenggunaan.classList.add('tersembunyi'); });
  if (btnTutupPenggunaanBawah) btnTutupPenggunaanBawah.addEventListener('click', function () { modalPenggunaan.classList.add('tersembunyi'); });
  if (modalPenggunaan) {
    modalPenggunaan.addEventListener('click', function (e) {
      if (e.target === modalPenggunaan) modalPenggunaan.classList.add('tersembunyi');
    });
  }

  // Event tutup modal Bantuan
  if (btnTutupBantuan) btnTutupBantuan.addEventListener('click', function () { modalBantuan.classList.add('tersembunyi'); });
  if (btnTutupBantuanBawah) btnTutupBantuanBawah.addEventListener('click', function () { modalBantuan.classList.add('tersembunyi'); });
  if (modalBantuan) {
    modalBantuan.addEventListener('click', function (e) {
      if (e.target === modalBantuan) modalBantuan.classList.add('tersembunyi');
    });
  }
});
