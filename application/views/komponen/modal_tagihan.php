<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRDAI — Panel Tagihan / Billing (Slide-Over Samping Kanan Sesuai Gambar 2).
 *
 * Mengakomodasi permintaan pengguna:
 * "halaman tagihan yg lama upgrade dengan tampilan kaya di gambar 2"
 *
 * Menggunakan background bacroundProfil.jpg yang sama dengan profil & upgrade,
 * header bilah atas dengan status Free Plan, tombol portal, expand, dan tombol [X],
 * serta kartu Paket Saat Ini (Plus aktif), Metode Pembayaran (Mastercard),
 * dan tabel Riwayat Tagihan dengan tombol Unduh PDF.
 */
?>
<!-- Panel Samping Drawer Tagihan (Membentang di Layar Kanan di Samping Sidebar) -->
<aside class="drawer-tagihan tersembunyi" id="modal-tagihan-backdrop" role="dialog" aria-modal="true" aria-labelledby="judul-drawer-tagihan">
  <!-- Header Bilah Atas: Hanya Tombol Tutup X di Kanan Atas Seperti Profil -->
  <div class="drawer-tagihan__header">
    <div class="drawer-tagihan__header-aksi">
      <button type="button" class="drawer-tagihan__btn-tutup" id="tombol-tutup-tagihan" aria-label="Tutup Panel Tagihan" title="Tutup (Esc)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>

  <!-- Area Konten Scrollable -->
  <div class="drawer-tagihan__isi">
    <div class="drawer-tagihan__wadah-konten">
      <!-- Judul & Deskripsi Tagihan -->
      <div class="drawer-tagihan__kepala">
        <h1 class="drawer-tagihan__judul">Tagihan</h1>
        <p class="drawer-tagihan__deskripsi">Kelola metode pembayaran Anda dan lihat riwayat transaksi.</p>
      </div>      <!-- Baris Atas 2 Kartu: Paket Saat Ini & Metode Pembayaran (Sesuai Gambar 2) -->
      <div class="drawer-tagihan__grid-atas">
        <!-- Kartu 1: Paket Saat Ini -->
        <div class="drawer-tagihan__kartu">
          <div class="drawer-tagihan__kartu-kepala">
            <span class="drawer-tagihan__kartu-label">Paket Saat Ini</span>
            <span class="drawer-tagihan__lencana-aktif"><?php echo isset($langganan->status) ? strtoupper(e($langganan->status)) : 'AKTIF'; ?></span>
          </div>

          <div class="drawer-tagihan__paket-nama"><?php echo isset($langganan->paket) ? e($langganan->paket) : 'Plus'; ?></div>
          <div class="drawer-tagihan__paket-harga">
            <?php if (isset($langganan->harga) && (float) $langganan->harga > 0): ?>
              Rp <?php echo number_format($langganan->harga, 0, ',', '.'); ?> / <?php echo isset($langganan->siklus) ? e($langganan->siklus) : 'bulan'; ?>
            <?php else: ?>
              Gratis (Free Tier)
            <?php endif; ?>
          </div>
          <div class="drawer-tagihan__paket-info">
            <?php if (isset($langganan->pembaruan_berikutnya)): ?>
              Pembaruan berikutnya pada <?php echo date('d F Y', strtotime($langganan->pembaruan_berikutnya)); ?>
            <?php else: ?>
              Paket aktif tanpa batas waktu kedaluwarsa
            <?php endif; ?>
          </div>

          <div class="drawer-tagihan__kartu-tombol-baris">
            <button type="button" class="drawer-tagihan__btn-sekunder" id="tombol-ubah-paket-tagihan">
              Ubah Paket
            </button>
            <button type="button" class="drawer-tagihan__btn-sekunder drawer-tagihan__btn-sekunder--bahaya" id="tombol-batalkan-langganan">
              Batalkan Langganan
            </button>
          </div>
        </div>

        <!-- Kartu 2: Metode Pembayaran -->
        <div class="drawer-tagihan__kartu">
          <div class="drawer-tagihan__kartu-kepala">
            <span class="drawer-tagihan__kartu-label">Metode Pembayaran</span>
          </div>

          <div class="drawer-tagihan__pembayaran-baris">
            <div class="drawer-tagihan__kartu-visual">
              <!-- Logo Mastercard SVG Asli Bersusun Bulatan Merah & Oranye -->
              <div class="drawer-tagihan__mastercard-logo" aria-hidden="true">
                <span class="drawer-tagihan__mc-lingkaran drawer-tagihan__mc-merah"></span>
                <span class="drawer-tagihan__mc-lingkaran drawer-tagihan__mc-oranye"></span>
              </div>
              <div class="drawer-tagihan__kartu-detail">
                <div class="drawer-tagihan__kartu-merk"><?php echo isset($langganan->metode_pembayaran_merk) ? e($langganan->metode_pembayaran_merk) : 'Mastercard'; ?></div>
                <div class="drawer-tagihan__kartu-nomor">berakhir di &bull;&bull;&bull;&bull; <?php echo isset($langganan->metode_pembayaran_nomor) ? e($langganan->metode_pembayaran_nomor) : '1234'; ?></div>
              </div>
            </div>

            <button type="button" class="drawer-tagihan__btn-sekunder" id="tombol-perbarui-kartu">
              Perbarui
            </button>
          </div>
        </div>
      </div>

      <!-- Bagian Bawah: Riwayat Tagihan (Tabel Sesuai Gambar 2) -->
      <div class="drawer-tagihan__seksi-riwayat">
        <h3 class="drawer-tagihan__subjudul">Riwayat Tagihan</h3>

        <div class="drawer-tagihan__tabel-wadah">
          <div class="drawer-tagihan__tabel-scroll">
            <table class="drawer-tagihan__tabel">
              <thead>
                <tr>
                  <th scope="col" style="width: 18%;">Tanggal</th>
                  <th scope="col" style="width: 40%;">Deskripsi</th>
                  <th scope="col" style="width: 18%;">Jumlah</th>
                  <th scope="col" style="width: 12%;">Status</th>
                  <th scope="col" style="width: 12%; text-align: right;">Unduh</th>
                </tr>
              </thead>
              <tbody>
                <?php if ( ! empty($riwayat_tagihan)): ?>
                  <?php foreach ($riwayat_tagihan as $inv): ?>
                    <tr>
                      <td class="drawer-tagihan__td-tgl"><?php echo date('d M Y', strtotime($inv->tanggal_bayar)); ?></td>
                      <td class="drawer-tagihan__td-desk"><?php echo e($inv->deskripsi); ?></td>
                      <td class="drawer-tagihan__td-nominal">Rp <?php echo number_format($inv->jumlah, 0, ',', '.'); ?></td>
                      <td>
                        <span class="drawer-tagihan__status-berhasil"><?php echo ucfirst(e($inv->status)); ?></span>
                      </td>
                      <td style="text-align: right;">
                        <button type="button" class="drawer-tagihan__btn-unduh" data-invoice="<?php echo e($inv->nomor_invoice); ?>">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                          </svg>
                          <span>Unduh PDF</span>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">Belum ada riwayat transaksi tagihan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>
