<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- Panel Upgrade Paket (Membentang di Layar Kanan di Samping Sidebar) -->
<aside class="drawer-tingkatkan tersembunyi" id="modal-tingkatkan-backdrop" role="dialog" aria-modal="true" aria-labelledby="judul-upgrade-paket">
  <!-- Tombol X Tutup Mengambang di Kanan Atas -->
  <div class="drawer-tingkatkan__header-aksi">
    <button type="button" class="drawer-tingkatkan__btn-tutup" id="tombol-tutup-tingkatkan" aria-label="Tutup Panel Upgrade" title="Tutup (Esc)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  </div>

  <!-- Area Konten Scrollable -->
  <div class="drawer-tingkatkan__isi">
    <!-- Judul & Subjudul Tengah -->
    <div class="drawer-tingkatkan__kepala">
      <h1 class="drawer-tingkatkan__judul" id="judul-upgrade-paket">Upgrade paket Anda</h1>
      <p class="drawer-tingkatkan__subjudul">
        Plus kini punya Suara yang disempurnakan, mode Work untuk melakukan tindakan di aplikasi favorit Anda, dan model canggih terbaru kami.
      </p>
    </div>

    <?php $aktif_tier = isset($user_tier) ? strtolower($user_tier) : 'free'; ?>
    <!-- Grid 4 Kartu Paket (Sesuai Gambar 1) -->
    <div class="drawer-tingkatkan__grid" id="grid-paket-pribadi">
      <!-- 1. KARTU FREE -->
      <div class="drawer-tingkatkan__kartu" data-tier="free">
        <div class="drawer-tingkatkan__kartu-top">
          <span class="drawer-tingkatkan__tier-label">Free</span>
          <h2 class="drawer-tingkatkan__kartu-judul">Coba PRDAI</h2>
          <p class="drawer-tingkatkan__kartu-deskripsi">
            Lihat bagaimana AI sehari membantu menemukan jawaban, menggali ide, dan menyelesaikan berbagai hal pekerjaan.
          </p>

          <?php if ($aktif_tier === 'free'): ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--saat-ini" disabled>
              Paket Anda saat ini
            </button>
          <?php else: ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--putih btn-pilih-paket" data-paket="Free" data-harga="0">
              Pilih Free
            </button>
          <?php endif; ?>

          <div class="drawer-tingkatkan__harga-baris">
            <span class="drawer-tingkatkan__harga-rp">Rp</span>
            <span class="drawer-tingkatkan__harga-angka">0</span>
            <span class="drawer-tingkatkan__harga-periode">/ bulan</span>
          </div>
        </div>

        <ul class="drawer-tingkatkan__fitur-list">
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Obrolan teks terbatas 20 pesan/hari</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <span>Upload berkas terbatas (Maks 5 gambar, 2MB)</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Memori terbatas 500 MB (4 riwayat chat)</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 5L6 9H2v6h4l5 4V5z"></path>
              <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            </svg>
            <span>Didukung iklan</span>
          </li>
        </ul>
      </div>

      <!-- 2. KARTU GO -->
      <div class="drawer-tingkatkan__kartu" data-tier="go">
        <div class="drawer-tingkatkan__kartu-top">
          <span class="drawer-tingkatkan__tier-label">Go</span>
          <h2 class="drawer-tingkatkan__kartu-judul">Akses lebih luas</h2>
          <p class="drawer-tingkatkan__kartu-deskripsi">
            Tulis, belajar, buat, dan mengobrol lebih banyak dengan kuota harian yang lebih lapang.
          </p>

          <?php if ($aktif_tier === 'go'): ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--saat-ini" disabled>
              Paket Anda saat ini
            </button>
          <?php else: ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--putih btn-pilih-paket" data-paket="Go" data-harga="75.000">
              Mulai
            </button>
          <?php endif; ?>

          <div class="drawer-tingkatkan__harga-baris">
            <span class="drawer-tingkatkan__harga-rp">Rp</span>
            <span class="drawer-tingkatkan__harga-angka">75.000</span>
            <span class="drawer-tingkatkan__harga-periode">/ bulan</span>
          </div>
        </div>

        <ul class="drawer-tingkatkan__fitur-list">
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Kuota hingga 50 pesan harian</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <span>Upload berkas hingga 25 gambar (Maks 5MB)</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Memori dan penyimpanan 2 GB (8 riwayat chat)</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 5L6 9H2v6h4l5 4V5z"></path>
              <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            </svg>
            <span>Didukung iklan</span>
          </li>
        </ul>
      </div>

      <!-- 3. KARTU PLUS (DIREKOMENDASIKAN) -->
      <div class="drawer-tingkatkan__kartu drawer-tingkatkan__kartu--unggulan" data-tier="plus">
        <div class="drawer-tingkatkan__kartu-top">
          <div class="drawer-tingkatkan__badge-baris">
            <span class="drawer-tingkatkan__tier-label">Plus</span>
            <span class="drawer-tingkatkan__badge-rekomendasi">DIREKOMENDASIKAN</span>
          </div>

          <h2 class="drawer-tingkatkan__kartu-judul">Asisten AI Anda</h2>
          <p class="drawer-tingkatkan__kartu-deskripsi">
            Akses kecerdasan tingkat lanjut dengan kuota tinggi untuk produktivitas tim.
          </p>

          <?php if ($aktif_tier === 'plus'): ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--saat-ini" disabled>
              Paket Anda saat ini
            </button>
          <?php else: ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--biru btn-pilih-paket" data-paket="Plus" data-harga="349.000">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              <span>Gabung ke Plus</span>
            </button>
          <?php endif; ?>

          <div class="drawer-tingkatkan__harga-baris">
            <span class="drawer-tingkatkan__harga-rp">Rp</span>
            <span class="drawer-tingkatkan__harga-angka">349.000</span>
            <span class="drawer-tingkatkan__harga-periode">/ bulan</span>
          </div>
        </div>

        <ul class="drawer-tingkatkan__fitur-list">
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
            <span>Kecerdasan tingkat lanjut (Batas 100 pesan/hari)</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <span>Upload berkas hingga 50 gambar (Maks 10MB)</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            <span>Agen Work yang bekerja lintas aplikasi</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="16 18 22 12 16 6"></polyline>
              <polyline points="8 6 2 12 8 18"></polyline>
            </svg>
            <span>Codex untuk mengotomatiskan penulisan kode</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <span>Memori lebih besar dan penyimpanan 20 GB</span>
          </li>
        </ul>
      </div>

      <!-- 4. KARTU PRO (DAYA MAKSIMUM) -->
      <div class="drawer-tingkatkan__kartu" data-tier="pro">
        <div class="drawer-tingkatkan__kartu-top">
          <span class="drawer-tingkatkan__tier-label">Pro</span>
          <h2 class="drawer-tingkatkan__kartu-judul">Daya Maksimum</h2>
          <p class="drawer-tingkatkan__kartu-deskripsi">
            Akses tak terbatas dengan kecerdasan AI terkuat, memori penuh, dan jawaban paling mendalam.
          </p>

          <?php if ($aktif_tier === 'pro'): ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--saat-ini" disabled>
              Paket Anda saat ini
            </button>
          <?php else: ?>
            <button type="button" class="drawer-tingkatkan__btn drawer-tingkatkan__btn--putih btn-pilih-paket" data-paket="Pro" data-harga="1.889.000">
              Mulai
            </button>
          <?php endif; ?>

          <div class="drawer-tingkatkan__harga-baris">
            <span class="drawer-tingkatkan__harga-rp">Rp</span>
            <span class="drawer-tingkatkan__harga-angka">1.889.000</span>
            <span class="drawer-tingkatkan__harga-periode">/ bulan</span>
          </div>
        </div>

        <ul class="drawer-tingkatkan__fitur-list">
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span><strong>Akses pesan & fitur TANPA BATAS</strong></span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <span><strong>Hak upload gambar & berkas TANPA BATAS</strong></span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
            <span><strong>Model AI terbaik dengan ide terbaru & jawaban paling mendalam, bagus, dan sangat rinci</strong></span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <span><strong>Memori maksimal dan penyimpanan 100 GB ingatan riwayat chat semuanya</strong></span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            <span>Akses penuh ke Work dan Codex</span>
          </li>
          <li class="drawer-tingkatkan__fitur-item">
            <svg class="drawer-tingkatkan__fitur-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Tanpa iklan & performa super cepat</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</aside>
