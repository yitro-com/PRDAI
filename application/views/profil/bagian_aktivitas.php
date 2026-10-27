<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Komponen Bagian Aktivitas Token (Harian, Mingguan, Bulanan).
 * Sesuai rancangan referensi dengan tab:
 * 1. Harian: Grafik batang ramping harian dengan spike hijau & penanda tanggal 01 Nov, 05, 10, 15 Apr, 20, 25, 30 Okt.
 * 2. Mingguan: Grafik batang mingguan (M1, M2, M3, M4) terkelompok per bulan dengan pemisah vertikal.
 * 3. Bulanan: Heatmap dot-matrix 30 kolom x 7 baris per bulan (menggantikan label Kumulatif sesuai permintaan pengguna).
 */

// Fallback aman jika $data_aktivitas belum terisi dari controller
if (empty($data_aktivitas)) {
    if (isset($this->Penggunaan_ai_model)) {
        $pengguna_id = isset($pengguna->id) ? (int) $pengguna->id : (isset($this->pengguna_masuk->id) ? (int) $this->pengguna_masuk->id : 0);
        $data_aktivitas = $this->Penggunaan_ai_model->ambil_data_aktivitas_token($pengguna_id);
    } else {
        $data_aktivitas = array(
            'harian'   => array(),
            'mingguan' => array(),
            'bulanan'  => array(),
        );
    }
}

$harian_data = isset($data_aktivitas['harian']) ? $data_aktivitas['harian'] : array();
$mingguan_data = isset($data_aktivitas['mingguan']) ? $data_aktivitas['mingguan'] : array();
$bulanan_data = isset($data_aktivitas['bulanan']) ? $data_aktivitas['bulanan'] : array();
?>
<section class="profil-seksi profil-seksi--aktivitas" id="aktivitas-token" aria-label="Aktivitas Token">
  <!-- Kartu Utama dengan Background Aurora Sunset/Purple Gelap Estetik -->
  <div class="profil-aktivitas-kartu">

    <!-- Header: Judul di Kiri & Segmented Pill Switcher di Kanan -->
    <div class="profil-aktivitas-header">
      <div class="profil-aktivitas-header-kiri">
        <h2 class="profil-aktivitas-judul">Aktivitas token</h2>
      </div>

      <div class="profil-tab-kelompok" role="tablist" aria-label="Pilihan Rentang Aktivitas Token">
        <button
          type="button"
          class="profil-tab-btn profil-tab-btn--aktif"
          data-target="panel-grafik-harian"
          data-mode="harian"
          id="tab-harian"
          role="tab"
          aria-selected="true"
          aria-controls="panel-grafik-harian"
        >Harian</button>
        <button
          type="button"
          class="profil-tab-btn"
          data-target="panel-grafik-mingguan"
          data-mode="mingguan"
          id="tab-mingguan"
          role="tab"
          aria-selected="false"
          aria-controls="panel-grafik-mingguan"
        >Mingguan</button>
        <button
          type="button"
          class="profil-tab-btn"
          data-target="panel-grafik-bulanan"
          data-mode="bulanan"
          id="tab-bulanan"
          role="tab"
          aria-selected="false"
          aria-controls="panel-grafik-bulanan"
        >Bulanan</button>
      </div>
    </div>

    <!-- Papan Visualisasi Grafik (Inner Glass Surface Transparan) -->
    <div class="profil-grafik-papan" id="wadah-papan-aktivitas">

      <!-- ==================== 1. PANEL HARIAN ==================== -->
      <div
        class="profil-grafik-panel profil-grafik-panel--aktif"
        id="panel-grafik-harian"
        data-panel="harian"
        role="tabpanel"
        aria-labelledby="tab-harian"
      >
        <div class="grafik-harian-wadah">
          <!-- Area Batang Vertikal Slender -->
          <div class="grafik-harian-batang-baris" role="graphics-chart" aria-label="Grafik Batang Harian Token">
            <?php foreach ($harian_data as $b): ?>
              <?php
                $kelas_batang = 'batang-harian';
                if (!empty($b['hijau'])) {
                    $kelas_batang .= ' batang-harian--hijau';
                }
                $token_fmt = number_format($b['token'], 0, ',', '.');
              ?>
              <div
                class="<?php echo $kelas_batang; ?>"
                style="height: <?php echo (int) $b['tinggi']; ?>%;"
                data-tgl="<?php echo e($b['tgl']); ?>"
                data-token="<?php echo (int) $b['token']; ?>"
                data-token-fmt="<?php echo e($token_fmt); ?>"
                data-aksi="<?php echo (int) $b['aksi']; ?>"
                data-mode="harian"
                role="img"
                tabindex="0"
                aria-label="<?php echo e($b['tgl']); ?>: <?php echo $token_fmt; ?> token"
              ></div>
            <?php endforeach; ?>
          </div>

          <!-- Label Sumbu X Harian (01 Nov, 05, 10, 15 Apr, 20, 25, 30 Okt) -->
          <div class="grafik-harian-axis-baris" aria-hidden="true">
            <?php foreach ($harian_data as $b): ?>
              <div class="grafik-harian-axis-slot">
                <?php if (!empty($b['tick'])): ?>
                  <span class="grafik-harian-axis-angka"><?php echo e($b['tick']['angka']); ?></span>
                  <?php if (!empty($b['tick']['sub'])): ?>
                    <span class="grafik-harian-axis-sub"><?php echo e($b['tick']['sub']); ?></span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- ==================== 2. PANEL MINGGUAN ==================== -->
      <div
        class="profil-grafik-panel"
        id="panel-grafik-mingguan"
        data-panel="mingguan"
        role="tabpanel"
        aria-labelledby="tab-mingguan"
        hidden
      >
        <div class="grafik-mingguan-wadah" role="graphics-chart" aria-label="Grafik Batang Mingguan Token">
          <?php foreach ($mingguan_data as $nama_bulan => $bln_info): ?>
            <div class="grafik-mingguan-bulan-kolom">
              <!-- Baris Batang Mingguan di Dalam Bulan Ini -->
              <div class="grafik-mingguan-batang-grup">
                <?php foreach ($bln_info['minggu'] as $m): ?>
                  <?php
                    $kelas_m = 'batang-mingguan';
                    if (!empty($m['hijau'])) {
                        $kelas_m .= ' batang-mingguan--hijau';
                    }
                    $token_m_fmt = number_format($m['token'], 0, ',', '.');
                  ?>
                  <div class="grafik-mingguan-batang-item">
                    <div
                      class="<?php echo $kelas_m; ?>"
                      style="height: <?php echo (int) $m['tinggi']; ?>%;"
                      data-label="<?php echo e($m['label']); ?> <?php echo e($nama_bulan); ?>"
                      data-token="<?php echo (int) $m['token']; ?>"
                      data-token-fmt="<?php echo e($token_m_fmt); ?>"
                      data-aksi="<?php echo (int) $m['aksi']; ?>"
                      data-mode="mingguan"
                      role="img"
                      tabindex="0"
                      aria-label="<?php echo e($m['label']); ?> <?php echo e($nama_bulan); ?>: <?php echo $token_m_fmt; ?> token"
                    ></div>
                    <span class="grafik-mingguan-label-minggu"><?php echo e($m['label']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Label Nama Bulan di Bagian Bawah -->
              <div class="grafik-mingguan-bulan-label"><?php echo e($nama_bulan); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ==================== 3. PANEL BULANAN (MENGGANTIKAN KUMULATIF) ==================== -->
      <div
        class="profil-grafik-panel"
        id="panel-grafik-bulanan"
        data-panel="bulanan"
        role="tabpanel"
        aria-labelledby="tab-bulanan"
        hidden
      >
        <div class="grafik-bulanan-wadah" role="grid" aria-label="Matriks Heatmap Bulanan Token">
          <?php foreach ($bulanan_data as $nama_bulan => $bln_info): ?>
            <?php
              $total_fmt = number_format($bln_info['total_token'], 0, ',', '.');
            ?>
            <div
              class="grafik-bulanan-bulan-grup"
              data-bulan="<?php echo e($nama_bulan); ?>"
              data-total="<?php echo e($total_fmt); ?>"
              data-aksi="<?php echo (int) $bln_info['total_aksi']; ?>"
            >
              <!-- Kolom-Kolom Titik Matrix (7 Baris Hari per Kolom) -->
              <div class="grafik-bulanan-kolom-wadah">
                <?php foreach ($bln_info['kolom_list'] as $kolom): ?>
                  <div class="grafik-bulanan-kolom-hari" role="row">
                    <?php foreach ($kolom as $titik): ?>
                      <?php
                        $kelas_titik = 'titik-bulanan';
                        if (!empty($titik['is_aktif'])) {
                            $kelas_titik .= ' titik-bulanan--hijau';
                        }
                        $token_t_fmt = number_format($titik['token'], 0, ',', '.');
                      ?>
                      <div
                        class="<?php echo $kelas_titik; ?>"
                        data-label="<?php echo e($titik['label']); ?>"
                        data-token="<?php echo (int) $titik['token']; ?>"
                        data-token-fmt="<?php echo e($token_t_fmt); ?>"
                        data-aksi="<?php echo (int) $titik['aksi']; ?>"
                        data-mode="bulanan"
                        role="gridcell"
                        tabindex="0"
                        aria-label="<?php echo e($titik['label']); ?>: <?php echo $token_t_fmt; ?> token"
                      ></div>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Label Bulan di Bawah Kolom Titik -->
              <div class="grafik-bulanan-bulan-label" title="Total <?php echo e($nama_bulan); ?>: <?php echo $total_fmt; ?> token">
                <?php echo e($nama_bulan); ?>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /.profil-grafik-papan -->
  </div><!-- /.profil-aktivitas-kartu -->
</section>
