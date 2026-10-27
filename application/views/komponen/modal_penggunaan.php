<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRDAI — Panel Penggunaan / Usage Analytics (Slide-Over Sesuai Gambar 2).
 *
 * Mengakomodasi permintaan pengguna:
 * - Menggunakan background profil (bacroundProfil.jpg) full sampai bawah
 * - "frepeplen tidak usah ada jadi cuma ada tombol X" -> Header bersih hanya ada tombol X mengambang di kanan atas
 * - Judul "Penggunaan", deskripsi "Lacak kuota, penggunaan token, dan aktivitas akun Anda."
 * - Kartu Penggunaan Token (17.180 / 50.000 Token, progress bar, Token Puncak: 3.850)
 * - Kartu Batas Pesan Harian (35 / 100 Pesan, ring progress 35%)
 * - Kartu Riwayat Aktivitas (Bar chart 7 hari: Sen-Min, skala 5.000 - 0)
 * - Kartu Pembuatan Gambar (12 / 50 Gambar)
 * - Kartu Penggunaan Fitur (Analisis Data 4/10 kali, Pencarian Web 8/20 kali)
 */
?>
<!-- Panel Samping Drawer Penggunaan (Membentang di Layar Kanan di Samping Sidebar) -->
<aside class="drawer-penggunaan tersembunyi" id="modal-penggunaan-backdrop" role="dialog" aria-modal="true" aria-labelledby="judul-drawer-penggunaan">
  <!-- Header Bilah Atas: HANYA ADA TOMBOL X DI KANAN ATAS SEPERTI PROFIL (TIDAK ADA FREE PLAN) -->
  <div class="drawer-penggunaan__header">
    <div class="drawer-penggunaan__header-aksi">
      <button type="button" class="drawer-penggunaan__btn-tutup" id="tombol-tutup-penggunaan" aria-label="Tutup Panel Penggunaan" title="Tutup (Esc)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>

  <!-- Area Konten Scrollable -->
  <div class="drawer-penggunaan__isi">
    <div class="drawer-penggunaan__wadah-konten">
      <!-- Judul & Deskripsi Penggunaan (Sesuai Gambar 2) -->
      <div class="drawer-penggunaan__kepala">
        <h1 class="drawer-penggunaan__judul" id="judul-drawer-penggunaan">Penggunaan</h1>
        <p class="drawer-penggunaan__deskripsi">Lacak kuota, penggunaan token, dan aktivitas akun Anda.</p>
      </div>

      <!-- Baris 1: 2 Kartu (Penggunaan Token & Batas Pesan Harian) -->
      <div class="drawer-penggunaan__grid-atas">
        <!-- Kartu 1: Penggunaan Token -->
        <div class="drawer-penggunaan__kartu">
          <div class="drawer-penggunaan__kartu-header">
            <div class="drawer-penggunaan__kartu-ikon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
              </svg>
            </div>
            <h3 class="drawer-penggunaan__kartu-judul">Penggunaan Token</h3>
          </div>

          <div class="drawer-penggunaan__nilai-utama">
            <span class="drawer-penggunaan__angka-besar"><?php echo number_format($penggunaan_kuota['total_token'], 0, ',', '.'); ?></span>
            <span class="drawer-penggunaan__angka-pembagi">/ <?php echo !empty($penggunaan_kuota['is_pro']) ? 'Tanpa Batas (Unlimited)' : number_format($penggunaan_kuota['batas_token'], 0, ',', '.') . ' Token'; ?></span>
          </div>

          <!-- Progress Bar Token Real -->
          <div class="drawer-penggunaan__progres-bar">
            <div class="drawer-penggunaan__progres-isi" style="width: <?php echo min(100, max(2, $penggunaan_kuota['persen_token'])); ?>%;"></div>
          </div>

          <div class="drawer-penggunaan__kartu-footer">
            <?php if (!empty($penggunaan_kuota['is_pro'])): ?>
              <span class="drawer-penggunaan__info-teks">Paket Pro: <strong>Akses Tanpa Batas &bull; Memori Maksimal 100 GB</strong>.</span>
            <?php else: ?>
              <span class="drawer-penggunaan__info-teks">Masa berlaku kuota sisa <strong><?php echo (int) $penggunaan_kuota['sisa_hari']; ?> hari lagi</strong>.</span>
            <?php endif; ?>
            <span class="drawer-penggunaan__info-tag">Token Puncak: <?php echo number_format($penggunaan_kuota['token_puncak'], 0, ',', '.'); ?></span>
          </div>
        </div>

        <!-- Kartu 2: Batas Pesan Harian -->
        <div class="drawer-penggunaan__kartu">
          <div class="drawer-penggunaan__kartu-header">
            <div class="drawer-penggunaan__kartu-ikon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </div>
            <h3 class="drawer-penggunaan__kartu-judul">Batas Pesan Harian</h3>
          </div>

          <div class="drawer-penggunaan__pesan-baris">
            <div class="drawer-penggunaan__pesan-info">
              <div class="drawer-penggunaan__nilai-utama">
                <span class="drawer-penggunaan__angka-besar"><?php echo (int) $penggunaan_kuota['pesan_hari_ini']; ?></span>
                <span class="drawer-penggunaan__angka-pembagi">/ <?php echo !empty($penggunaan_kuota['is_pro']) ? 'Tanpa Batas (Unlimited)' : (int) $penggunaan_kuota['batas_pesan_harian'] . ' Pesan'; ?></span>
              </div>
              <p class="drawer-penggunaan__sub-info"><?php echo !empty($penggunaan_kuota['is_pro']) ? 'Bebas chat tanpa batas kuota harian.' : 'Diperbarui setiap 24 jam.'; ?></p>
            </div>

            <!-- Ring Lingkaran Persentase Real Sesuai Gambar 2 -->
            <div class="drawer-penggunaan__lingkaran-wadah" aria-label="<?php echo !empty($penggunaan_kuota['is_pro']) ? 'Tanpa batas' : $penggunaan_kuota['persen_pesan'] . '% digunakan'; ?>">
              <svg class="drawer-penggunaan__lingkaran-svg" width="80" height="80" viewBox="0 0 80 80">
                <circle class="drawer-penggunaan__lingkaran-bg" cx="40" cy="40" r="32" stroke-width="6" fill="none" />
                <circle class="drawer-penggunaan__lingkaran-meter" cx="40" cy="40" r="32" stroke-width="6" fill="none" stroke-dasharray="201" stroke-dashoffset="<?php echo !empty($penggunaan_kuota['is_pro']) ? '0' : $penggunaan_kuota['ring_dashoffset']; ?>" stroke-linecap="round" />
              </svg>
              <div class="drawer-penggunaan__lingkaran-teks"><?php echo !empty($penggunaan_kuota['is_pro']) ? 'Pro' : $penggunaan_kuota['persen_pesan'] . '%'; ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Baris 2: Riwayat Aktivitas (Bar Chart 7 Hari Real dari Basis Data) -->
      <div class="drawer-penggunaan__kartu drawer-penggunaan__kartu--chart">
        <div class="drawer-penggunaan__kartu-header">
          <h3 class="drawer-penggunaan__kartu-judul">Riwayat Aktivitas</h3>
        </div>

        <div class="drawer-penggunaan__chart-wadah">
          <!-- Y-Axis Labels: 5.000, 4.000, 3.000, 2.000, 1.000, 0 -->
          <div class="drawer-penggunaan__chart-y-axis">
            <span>5.000</span>
            <span>4.000</span>
            <span>3.000</span>
            <span>2.000</span>
            <span>1.000</span>
            <span>0</span>
          </div>
          <div class="drawer-penggunaan__chart-plot">
            <div class="drawer-penggunaan__chart-grid-line" style="bottom: 100%;"></div>
            <div class="drawer-penggunaan__chart-grid-line" style="bottom: 80%;"></div>
            <div class="drawer-penggunaan__chart-grid-line" style="bottom: 60%;"></div>
            <div class="drawer-penggunaan__chart-grid-line" style="bottom: 40%;"></div>
            <div class="drawer-penggunaan__chart-grid-line" style="bottom: 20%;"></div>
            <div class="drawer-penggunaan__chart-grid-line" style="bottom: 0%;"></div>
            <div class="drawer-penggunaan__bars-wadah">
              <?php foreach ($penggunaan_kuota['aktivitas_7_hari'] as $bar): ?>
                <div class="drawer-penggunaan__bar-kolom <?php echo $bar['is_puncak'] ? 'drawer-penggunaan__bar-kolom--aktif' : ''; ?>" title="<?php echo e($bar['label']); ?> (<?php echo date('d M Y', strtotime($bar['tanggal'])); ?>): <?php echo number_format($bar['token'], 0, ',', '.'); ?> Token">
                  <span class="drawer-penggunaan__bar-nilai"><?php echo e($bar['label_nilai']); ?></span>
                  <div class="drawer-penggunaan__bar-batang" style="height: <?php echo $bar['persen_tinggi']; ?>%;"></div>
                  <span class="drawer-penggunaan__bar-label"><?php echo e($bar['label']); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="drawer-penggunaan__grid-bawah">
        <div class="drawer-penggunaan__kartu">
          <div class="drawer-penggunaan__kartu-header">
            <div class="drawer-penggunaan__kartu-ikon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
            </div>
            <h3 class="drawer-penggunaan__kartu-judul">Upload Gambar</h3>
          </div>
          <div class="drawer-penggunaan__nilai-utama">
            <span class="drawer-penggunaan__angka-besar"><?php echo (int) $penggunaan_kuota['gambar_terpakai']; ?></span>
            <span class="drawer-penggunaan__angka-pembagi">/ <?php echo !empty($penggunaan_kuota['is_pro']) ? 'Tanpa Batas (Unlimited)' : (int) $penggunaan_kuota['batas_gambar'] . ' Gambar'; ?></span>
          </div>
          <div class="drawer-penggunaan__progres-bar">
            <div class="drawer-penggunaan__progres-isi drawer-penggunaan__progres-isi--cyan" style="width: <?php echo min(100, max(2, $penggunaan_kuota['persen_gambar'])); ?>%;"></div>
          </div>
        </div>
        <div class="drawer-penggunaan__kartu">
          <div class="drawer-penggunaan__kartu-header">
            <div class="drawer-penggunaan__kartu-ikon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <h3 class="drawer-penggunaan__kartu-judul">Penggunaan Fitur</h3>
          </div>
          <div class="drawer-penggunaan__fitur-daftar">
            <div class="drawer-penggunaan__fitur-baris">
              <span class="drawer-penggunaan__fitur-nama">Analisis Data:</span>
              <span class="drawer-penggunaan__fitur-jumlah"><?php echo !empty($penggunaan_kuota['is_pro']) ? 'Tanpa Batas' : (int) $penggunaan_kuota['analisis_data_terpakai'] . '/' . (int) $penggunaan_kuota['batas_analisis_data'] . ' kali'; ?></span>
            </div>
            <div class="drawer-penggunaan__fitur-baris">
              <span class="drawer-penggunaan__fitur-nama">Pencarian Web:</span>
              <span class="drawer-penggunaan__fitur-jumlah"><?php echo !empty($penggunaan_kuota['is_pro']) ? 'Tanpa Batas' : (int) $penggunaan_kuota['pencarian_web_terpakai'] . '/' . (int) $penggunaan_kuota['batas_pencarian_web'] . ' kali'; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>
