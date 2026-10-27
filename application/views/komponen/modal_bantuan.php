<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- Panel Samping Drawer Bantuan (Membentang di Layar Kanan di Samping Sidebar) -->
<aside class="drawer-bantuan tersembunyi" id="modal-bantuan-backdrop" role="dialog" aria-modal="true" aria-labelledby="judul-drawer-bantuan">
  <!-- Header Bilah Atas: Tombol Tutup X di Kanan Atas Seperti Profil -->
  <div class="drawer-bantuan__header">
    <div class="drawer-bantuan__header-aksi">
      <button type="button" class="drawer-bantuan__btn-tutup" id="tombol-tutup-bantuan" aria-label="Tutup Pusat Bantuan" title="Tutup (Esc)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">  
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>

  <!-- Area Konten Scrollable -->
  <div class="drawer-bantuan__isi">
    <div class="drawer-bantuan__wadah-konten">
      <!-- Judul & Subjudul Pusat Bantuan (Tengah) -->
      <div class="drawer-bantuan__kepala">
        <h1 class="drawer-bantuan__judul" id="judul-drawer-bantuan">Pusat Bantuan</h1>
        <p class="drawer-bantuan__deskripsi">Cari jawaban cepat atau hubungi tim bantuan kami untuk segala pertanyaan.</p>
      </div>

      <!-- 4 Kartu Kategori Bantuan (Dinamis dari Basis Data) -->
      <div class="drawer-bantuan__kategori-grid">
        <?php if ( ! empty($bantuan_kategori)): ?>
          <?php foreach ($bantuan_kategori as $kat): ?>
            <div class="drawer-bantuan__kategori-kartu" data-kategori="<?php echo e($kat->slug); ?>" tabindex="0" role="button">
              <div class="drawer-bantuan__kategori-ikon-wadah">
                <?php if ($kat->slug === 'akun'): ?>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <rect x="9" y="9" width="6" height="5" rx="1"></rect>
                  </svg>
                <?php elseif ($kat->slug === 'tagihan'): ?>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                  </svg>
                <?php elseif ($kat->slug === 'token'): ?>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                  </svg>
                <?php else: ?>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                  </svg>
                <?php endif; ?>
              </div>
              <div class="drawer-bantuan__kategori-konten">
                <h3 class="drawer-bantuan__kategori-judul"><?php echo e($kat->judul); ?></h3>
                <p class="drawer-bantuan__kategori-deskripsi"><?php echo e($kat->deskripsi); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Grid Bagian Bawah: 2 Kolom (Pertanyaan Populer di Kiri, Hubungi Kami di Kanan) -->
      <div class="drawer-bantuan__bawah-grid">
        <!-- Kolom Kiri: Pertanyaan Populer (FAQ Accordion Dinamis dari Basis Data) -->
        <div class="drawer-bantuan__faq-wadah">
          <h2 class="drawer-bantuan__subjudul">Pertanyaan Populer</h2>

          <div class="drawer-bantuan__faq-daftar">
            <?php if ( ! empty($bantuan_faq)): ?>
              <?php foreach ($bantuan_faq as $faq): ?>
                <div class="drawer-bantuan__faq-item" data-faq="<?php echo e($faq->id); ?>">
                  <button type="button" class="drawer-bantuan__faq-pemicu" aria-expanded="false">
                    <span class="drawer-bantuan__faq-pertanyaan"><?php echo e($faq->pertanyaan); ?></span>
                    <svg class="drawer-bantuan__faq-panah" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                  </button>
                  <div class="drawer-bantuan__faq-jawaban">
                    <p><?php echo e($faq->jawaban); ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Kolom Kanan: Hubungi Kami (Permintaan pengguna: "ganti jadi hubungi kami kaya pada umumnya tampilannya") -->
        <div class="drawer-bantuan__hubungi-wadah">
          <div class="drawer-bantuan__hubungi-kartu">
            <h3 class="drawer-bantuan__hubungi-judul">Hubungi Kami</h3>
            <p class="drawer-bantuan__hubungi-deskripsi">Masih butuh bantuan? Tim dukungan kami siap mendampingi Anda menyelesaikan pertanyaan kapan saja.</p>

            <div class="drawer-bantuan__hubungi-opsi">
              <button type="button" class="drawer-bantuan__btn-hubungi drawer-bantuan__btn-hubungi--tiket" id="btn-bantuan-tiket">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13"></line>
                  <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                <span>Kirim Lewat Email</span>
              </button>

              <button type="button" class="drawer-bantuan__btn-hubungi drawer-bantuan__btn-hubungi--chat" id="btn-bantuan-chat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>Mulai Chat Bantuan</span>
              </button>
            </div>

            <!-- Detail Kontak Resmi Standar Aplikasi Modern -->
            <div class="drawer-bantuan__hubungi-kontak-info">
              <div class="drawer-bantuan__kontak-baris">
                <span class="drawer-bantuan__kontak-label">Email Resmi</span>
                <a href="mailto:support@prdai.id" class="drawer-bantuan__kontak-nilai">support@prdai.id</a>
              </div>
              <div class="drawer-bantuan__kontak-baris">
                <span class="drawer-bantuan__kontak-label">Waktu Respon</span>
                <span class="drawer-bantuan__kontak-nilai drawer-bantuan__kontak-nilai--cepat">
                  <span class="drawer-bantuan__titik-status"></span>10 menit
                </span>
              </div>
              <div class="drawer-bantuan__kontak-baris">
                <span class="drawer-bantuan__kontak-label">Jam Operasional</span>
                <span class="drawer-bantuan__kontak-nilai">Senin – Minggu (24/7)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>
