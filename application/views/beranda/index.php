<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="ruang-kerja" id="ruang-kerja" 
     data-kode-aktif="<?php echo $prd_aktif ? e($prd_aktif->kode) : ''; ?>"
     data-user-inisial="<?php echo e($user_inisial); ?>"
     data-user-nama="<?php echo e($user_nama); ?>"
     data-user-email="<?php echo e($user_email); ?>"
     data-user-tier="<?php echo e($user_tier); ?>"
     data-user-zona-waktu="<?php echo e(isset($user_zona_waktu) ? $user_zona_waktu : 'Asia/Makassar'); ?>"
     data-user-terakhir-masuk="<?php echo e(isset($user_terakhir_masuk) ? $user_terakhir_masuk : ''); ?>"
     data-site-url="<?php echo rtrim(site_url(), '/') . '/'; ?>">

  <!-- TOMBOL LOGO PERSISTEN PRDAI (Diam di tempatnya di kiri atas seperti Gemini) -->
  <button type="button" class="tombol-logo-tetap" id="tombol-logo-tetap" aria-label="PRDAI — Buka Bilah Samping & Kembali ke Beranda" title="PRDAI — Buka Bilah Samping & Kembali ke Beranda">
    <img src="<?php echo base_url('assets/img/lgo.png'); ?>" alt="Logo PRDAI" class="logo-tetap-img">
  </button>

  <!-- ==================== BILAH SAMPING (SIDEBAR) ==================== -->
  <aside class="sidebar-chat" id="sidebar-chat" aria-label="Bilah Samping PRDAI">
    <!-- Bagian Atas: Judul & Alat (Logo persisten berada di jangkar kiri atas) -->
    <div class="sidebar-chat__header">
      <div class="sidebar-chat__brand-label" id="brand-label-sidebar">
        <span class="sidebar-chat__brand-text">PRDAI</span>
      </div>
      <div class="sidebar-chat__header-tools">
        <button type="button" class="sidebar-chat__tool-btn" id="tombol-tutup-sidebar" aria-label="Lebarkan Ruang Kerja" title="Lebarkan Ruang Kerja">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <line x1="9" y1="3" x2="9" y2="21"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Tombol PRD Baru (Obrolan Baru) -->
    <div class="sidebar-chat__aksi-baru">
      <button type="button" class="tombol-obrolan-baru" id="tombol-prd-baru" aria-label="Buat PRD Baru">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        <span>PRD Baru</span>
      </button>
    </div>

    <!-- Menu Pintasan Cepat (Pustaka PRD Pengguna) -->
    <nav class="sidebar-chat__menu-pintas">
      <button type="button" class="sidebar-chat__menu-item" id="tombol-semua-prd" title="Buka Seluruh Koleksi PRD Saya">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
          <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
        </svg>
        <span>Pustaka PRD</span>
      </button>
    </nav>

    <!-- Bagian Terkini (Hanya tampil jika benar-benar ada riwayat tersimpan) -->
    <div class="sidebar-chat__wadah-terkini <?php echo empty($daftar_terkini) ? 'tersembunyi' : ''; ?>" id="wadah-terkini-sidebar">
      <div class="sidebar-chat__label-terkini" id="label-terkini">Terkini</div>
      <div class="sidebar-chat__daftar" id="daftar-terkini" role="list">
        <?php if ( ! empty($daftar_terkini)): ?>
          <?php foreach ($daftar_terkini as $item): ?>
            <?php $aktif = ($prd_aktif && $prd_aktif->kode === $item->kode); ?>
            <div class="item-terkini <?php echo $aktif ? 'item-terkini--aktif' : ''; ?>" data-kode="<?php echo e($item->kode); ?>" role="listitem">
              <button type="button" class="item-terkini__btn" data-kode="<?php echo e($item->kode); ?>" title="<?php echo e($item->judul); ?>">
                <span class="item-terkini__judul"><?php echo e($item->judul); ?></span>
              </button>
              <button type="button" class="item-terkini__hapus" data-kode="<?php echo e($item->kode); ?>" title="Hapus Dokumen" aria-label="Hapus Dokumen">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bagian Profil Pengguna di Bawah Sidebar (Mengikuti Akun Login Dinamis) -->
    <div class="sidebar-chat__profil" id="profil-sidebar-bar">
      <button type="button" class="sidebar-chat__profil-user" id="tombol-buka-profil" aria-label="Buka Profil Pengguna" title="Lihat Profil & Pengaturan Akun">
        <div class="avatar-lingkaran" id="avatar-user-sidebar"><?php echo e($user_inisial); ?></div>
        <div class="sidebar-chat__profil-info">
          <div class="sidebar-chat__profil-nama" id="nama-user-sidebar"><?php echo e($user_nama); ?></div>
          <div class="sidebar-chat__profil-tier" id="tier-user-sidebar"><?php echo e($user_tier); ?></div>
        </div>
      </button>
      <?php $tier_bawah_plus = in_array(strtolower($user_tier), array('free', 'go')); ?>
      <?php if ($tier_bawah_plus): ?>
        <button type="button" class="tombol-upgrade-sidebar" id="tombol-upgrade-sidebar" title="Upgrade ke PRDAI Pro">Upgrade</button>
      <?php endif; ?>

      <!-- Ringkasan Profil Melayang (Muncul Saat Hover Sebelum Di-klik Sesuai Gambar 1) -->
      <div class="ringkasan-profil-melayang tersembunyi" id="ringkasan-profil-hover" role="tooltip" aria-label="Ringkasan Profil Pengguna">
        <div class="ringkasan-profil__kartu">
          <div class="ringkasan-profil__atas">
            <div class="ringkasan-profil__avatar"><?php echo e($user_inisial); ?></div>
            <div class="ringkasan-profil__info">
              <div class="ringkasan-profil__nama"><?php echo e($user_nama); ?></div>
              <div class="ringkasan-profil__email"><?php echo e($user_email); ?></div>
            </div>
          </div>
          <div class="ringkasan-profil__garis"></div>
          <div class="ringkasan-profil__metrik-baris">
            <div class="ringkasan-profil__metrik-item">
              <span class="ringkasan-profil__metrik-label">Paket</span>
              <span class="ringkasan-profil__metrik-nilai ringkasan-profil__metrik-nilai--tier"><?php echo e($user_tier); ?></span>
            </div>
            <div class="ringkasan-profil__metrik-item">
              <span class="ringkasan-profil__metrik-label">Token AI</span>
              <span class="ringkasan-profil__metrik-nilai"><?php echo number_format($statistik['total_token'], 0, ',', '.'); ?></span>
            </div>
            <div class="ringkasan-profil__metrik-item">
              <span class="ringkasan-profil__metrik-label">Hari Aktif</span>
              <span class="ringkasan-profil__metrik-nilai"><?php echo (int) $statistik['hari_aktif_beruntun']; ?> hr</span>
            </div>
          </div>
          <div class="ringkasan-profil__petunjuk" data-aksi="buka-drawer-profil">
            <span class="ringkasan-profil__titik-hijau"></span>
            <span>Akun Aktif &bull; Klik untuk kelola profil di panel kanan</span>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ==================== KANVAS UTAMA (MAIN CANVAS) ==================== -->
  <main class="kanvas-utama" id="kanvas-utama">
    
    <!-- Header Atas Kanvas (Mode Switcher, Judul Aktif, Tingkatkan & Avatar) -->
    <header class="kanvas-header">
      <div class="kanvas-header__kiri"></div>

      <div class="kanvas-header__tengah"></div>

      <div class="kanvas-header__kanan">
        <?php if ($tier_bawah_plus): ?>
          <button type="button" class="tombol-tingkatkan" id="tombol-buka-tingkatkan" title="Lihat Fitur PRDAI Pro">
            <span>✦ Tingkatkan</span>
          </button>
        <?php endif; ?>
        <button type="button" class="btn-avatar-header" id="tombol-avatar-header" title="Profil Akun">
          <div class="avatar-lingkaran avatar-lingkaran--kecil" id="avatar-user-header"><?php echo e($user_inisial); ?></div>
        </button>
      </div>
    </header>

    <!-- Area Konten (Scrollable) -->
    <div class="kanvas-konten" id="kanvas-konten">
      
      <!-- 1. KEADAAN KOSONG / WELCOME SCREEN (Persis Screenshot) -->
      <section class="keadaan-kosong <?php echo $prd_aktif ? 'tersembunyi' : ''; ?>" id="tampilan-kosong">
        <div class="wadah-logo-dasbor">
          <div class="logo-animasi-wadah">
            <img src="<?php echo base_url('assets/img/lgo_rapi.png'); ?>" alt="Logo PRDAI" class="logo-dasbor-utama" width="88" height="88">
          </div>
        </div>
        <?php
        $tz_aktif_user = ! empty($user_zona_waktu) ? $user_zona_waktu : 'Asia/Makassar';
        try {
            $dt_salam = new DateTime('now', new DateTimeZone($tz_aktif_user));
            $jam_sekarang = (int) $dt_salam->format('H');
        } catch (Exception $e) {
            $jam_sekarang = (int) date('H');
        }
        if ($jam_sekarang >= 4 && $jam_sekarang < 11) {
            $salam_waktu = 'Selamat Pagi';
        } elseif ($jam_sekarang >= 11 && $jam_sekarang < 15) {
            $salam_waktu = 'Selamat Siang';
        } elseif ($jam_sekarang >= 15 && $jam_sekarang < 18) {
            $salam_waktu = 'Selamat Sore';
        } else {
            $salam_waktu = 'Selamat Malam';
        }
        ?>
        <h1 class="salam-tengah" id="salam-tengah">Halo, <?php echo $salam_waktu; ?>, <span class="salam-nama" id="salam-user-nama"><?php echo e($user_nama); ?></span>.</h1>

        <div class="kapsul-masukan-tengah">
          <!-- Preview Dokumen / Lampiran Aktif -->
          <div class="wadah-lampiran-preview tersembunyi" id="badge-lampiran-utama">
            <span class="lampiran-icon">📎</span>
            <span class="lampiran-nama" id="nama-lampiran-utama"></span>
            <span class="lampiran-ukuran" id="ukuran-lampiran-utama"></span>
            <button type="button" class="btn-hapus-lampiran" id="tombol-hapus-lampiran-utama" title="Hapus Lampiran">✕</button>
          </div>
          <textarea 
            class="masukan-prompt" 
            id="prompt-utama" 
            placeholder="Tanyakan apa saja" 
            rows="1"
          ></textarea>
          <div class="kapsul-masukan-tengah__bawah">
            <div class="kapsul-alat-kiri">
              <button type="button" class="tombol-plus-opsi" id="tombol-opsi-plus" title="Tambahkan Dokumen (PRD.md, TXT, JSON, CSV, PDF, dll)" aria-label="Tambahkan Dokumen">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
              </button>
            </div>
            <div class="kapsul-alat-kanan">
              <button type="button" class="tombol-mode-berpikir" id="tombol-berpikir-utama" title="Mode Berpikir (Klik untuk Penjelasan Mendalam Sedetail-detailnya)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-2.04"></path>
                  <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-2.04"></path>
                </svg>
                <span>Berpikir</span>
              </button>
              <button type="button" class="tombol-suara" id="tombol-suara" title="Ketik menggunakan suara (Dikte Mikrofon)" aria-label="Masukan suara">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                  <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                  <line x1="12" y1="19" x2="12" y2="23"></line>
                  <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
              </button>
              <button type="button" class="tombol-kirim-bulat tombol-kirim-audio" id="tombol-kirim-utama" aria-label="Kirim Permintaan">
                <svg class="ikon-audio" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="6" y1="9" x2="6" y2="15"></line>
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="18" y1="9" x2="18" y2="15"></line>
                </svg>
                <svg class="ikon-kirim tersembunyi" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="19" x2="12" y2="5"></line>
                  <polyline points="5 12 12 5 19 12"></polyline>
                </svg>
              </button>
            </div>
          </div>
        </div>

      </section>

      <!-- 2. KEADAAN PERCAKAPAN & DOKUMEN AKTIF (THREAD CHAT STREAM) -->
      <section class="keadaan-dokumen <?php echo $prd_aktif ? '' : 'tersembunyi'; ?>" id="tampilan-dokumen">
        <div id="daftar-pesan-chat" class="daftar-pesan-chat">
          <?php if ($prd_aktif && ! empty($percakapan)): ?>
            <?php foreach ($percakapan as $item): ?>
              <?php if ($item['peran'] === 'user'): ?>
                <div class="wadah-pesan-pengguna">
                  <div class="gelembung-pesan-user" tabindex="0">
                    <?php if (! empty($item['lampiran'])): ?>
                      <div class="badge-lampiran-pesan">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <span><?php echo e($item['lampiran']); ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="teks-pesan-user"><?php echo nl2br(e($item['pesan'])); ?></div>
                    <textarea class="raw-teks-user tersembunyi" style="display:none;"><?php echo e($item['pesan']); ?></textarea>
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
                </div>
              <?php else: ?>
                <div class="wadah-respon-asisten">
                  <div class="wadah-respon-isi">
                    <article class="konten-markdown-prd">
                      <?php echo isset($item['html']) ? $item['html'] : $this->pemroses_markdown->ubah_ke_html($item['pesan']); ?>
                    </article>
                    <div class="baris-ikon-ai">
                      <?php
                        $is_prd_item = (bool) preg_match('/(?:^#\s+.*(?:PRD|Product\s+Requirements)|Dokumen\s+Spesifikasi\s+Kebutuhan\s+Produk|##\s+\d+\.\s+Petunjuk\s+Khusus\s+untuk\s+AI\s+Coding\s+Agent)/mi', $item['pesan']);
                      ?>
                      <?php if ($is_prd_item): ?>
                        <button type="button" class="btn-salin-semua-prd" title="Salin langsung semua jawaban PRD yang diberikan" aria-label="Salin Semua Jawaban PRD">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                          </svg>
                          <span class="btn-salin-semua-prd__teks">Salin Semua Jawaban PRD</span>
                        </button>
                      <?php endif; ?>
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
                          <a href="<?php echo $prd_aktif ? site_url('prd/' . e($prd_aktif->kode) . '/unduh') : '#'; ?>" class="item-menu-dropdown btn-unduh-link" title="Unduh berkas Markdown (.md)">
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
                    <textarea class="raw-markdown-asisten tersembunyi" style="display:none;"><?php echo e($item['pesan']); ?></textarea>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Mode Sunting Inline Universal -->
        <div class="editor-inline tersembunyi" id="editor-inline">
          <div class="editor-inline__toolbar">
            <span class="editor-inline__label">Mode Penyuntingan Markdown</span>
          </div>
          <textarea class="editor-inline__textarea" id="textarea-editor-md" rows="22"><?php echo $prd_aktif ? e($prd_aktif->isi_markdown) : ''; ?></textarea>
          <div class="editor-inline__bawah">
            <input type="text" class="editor-inline__catatan" id="catatan-revisi" placeholder="Catatan revisi (opsional, contoh: Menambahkan endpoint API)" maxlength="200">
            <div class="editor-inline__tombol-kelompok">
              <button type="button" class="btn-batal-editor" id="tombol-batal-sunting">Batal</button>
              <button type="button" class="btn-simpan-editor" id="tombol-simpan-sunting">Simpan Versi Baru</button>
            </div>
          </div>
        </div>

        <!-- Indikator Mengetik Inline (Muncul saat proses AI di tempat percakapan) -->
        <div class="wadah-respon-asisten tersembunyi" id="indikator-mengetik-inline">
          <div class="wadah-respon-isi">
            <div class="kartu-proses-ai-minimal">
              <div class="indikator-denyut"></div>
              <span class="status-tahapan-ai" id="teks-tahapan-ai">AI sedang berpikir...</span>
            </div>
          </div>
        </div>
      </section>

    </div>

    <!-- 4. BOTTOM INPUT CAPSULE (Persis Screenshot) -->
    <div class="kanvas-bawah-kapsul <?php echo $prd_aktif ? '' : 'tersembunyi'; ?>" id="wadah-kapsul-bawah">
      <!-- Tombol Gulir Bawah Mengambang -->
      <button type="button" class="tombol-gulir-bawah tersembunyi" id="tombol-gulir-bawah" aria-label="Gulir ke bawah" title="Gulir ke pesan terbaru">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <polyline points="19 12 12 19 5 12"></polyline>
        </svg>
      </button>

      <div class="kapsul-masukan-bawah">
        <!-- Preview Dokumen / Lampiran Aktif (Bawah) -->
        <div class="wadah-lampiran-preview tersembunyi" id="badge-lampiran-bawah">
          <span class="lampiran-icon">📎</span>
          <span class="lampiran-nama" id="nama-lampiran-bawah"></span>
          <span class="lampiran-ukuran" id="ukuran-lampiran-bawah"></span>
          <button type="button" class="btn-hapus-lampiran" id="tombol-hapus-lampiran-bawah" title="Hapus Lampiran">✕</button>
        </div>

        <div class="kapsul-baris-utama">
          <button type="button" class="tombol-plus-opsi" id="tombol-opsi-plus-bawah" title="Tambahkan Dokumen (PRD.md, TXT, JSON, CSV, PDF, dll)" aria-label="Tambahkan Dokumen">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
          
          <textarea 
            class="masukan-prompt" 
            id="prompt-bawah" 
            placeholder="Tanyakan apa saja" 
            rows="1"
          ></textarea>

          <div class="kapsul-alat-kanan">
            <button type="button" class="tombol-mode-berpikir" id="tombol-berpikir-bawah" title="Mode Berpikir (Klik untuk Penjelasan Mendalam Sedetail-detailnya)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-2.04"></path>
                <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-2.04"></path>
              </svg>
              <span>Berpikir</span>
            </button>

            <button type="button" class="tombol-suara" id="tombol-suara-bawah" title="Ketik menggunakan suara (Dikte Mikrofon)" aria-label="Masukan suara">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <line x1="12" y1="19" x2="12" y2="23"></line>
                <line x1="8" y1="23" x2="16" y2="23"></line>
              </svg>
            </button>

            <button type="button" class="tombol-kirim-bulat tombol-kirim-audio" id="tombol-kirim-bawah" aria-label="Kirim Pesan">
              <svg class="ikon-audio" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="6" y1="9" x2="6" y2="15"></line>
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="18" y1="9" x2="18" y2="15"></line>
              </svg>
              <svg class="ikon-kirim tersembunyi" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5"></line>
                <polyline points="5 12 12 5 19 12"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- ==================== MODAL PUSTAKA PRD (DAFTAR SEMUA DOKUMEN) ==================== -->
  <div class="modal-konfirmasi-backdrop tersembunyi" id="modal-pustaka-backdrop">
    <div class="modal-konfirmasi modal-lebar">
      <div class="modal-header-fleksibel">
        <h3 class="modal-konfirmasi__judul">Pustaka PRD Saya</h3>
        <button type="button" class="btn-tutup-silang" id="tombol-tutup-pustaka" aria-label="Tutup">✕</button>
      </div>
      <p class="modal-konfirmasi__deskripsi">Seluruh dokumen spesifikasi produk yang pernah Anda susun dengan PRDAI.</p>
      
      <div class="pustaka-pencarian-box">
        <input type="text" class="input-cari-pustaka" id="input-cari-pustaka" placeholder="Cari nama produk atau modul...">
        <span class="badge-total-pustaka" id="badge-total-pustaka"><?php echo count($daftar_terkini); ?> dokumen</span>
      </div>

      <div class="pustaka-daftar-gulung" id="pustaka-daftar-gulung">
        <?php if ( ! empty($daftar_terkini)): ?>
          <?php foreach ($daftar_terkini as $item): ?>
            <div class="item-pustaka" data-kode="<?php echo e($item->kode); ?>" data-judul="<?php echo e(strtolower($item->judul)); ?>">
              <div class="item-pustaka__info">
                <div class="item-pustaka__judul"><?php echo e($item->judul); ?></div>
                <div class="item-pustaka__waktu"><?php echo date('d M Y, H:i', strtotime($item->diperbarui_pada)); ?> &bull; Versi <?php echo (int) $item->versi_saat_ini; ?></div>
              </div>
              <button type="button" class="btn-buka-pustaka" data-kode="<?php echo e($item->kode); ?>">Buka Dokumen</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="pustaka-kosong">Belum ada riwayat dokumen untuk akun ini. Ketik ide produk pertama Anda di kanvas!</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ==================== POPOVER MENU PROFIL (SESUAI GAMBAR 1) ==================== -->
  <?php $this->load->view('komponen/menu_profil'); ?>

  <!-- ==================== DRAWER PROFIL SAMPING KANAN ==================== -->
  <?php $this->load->view('komponen/drawer_profil'); ?>

  <!-- Modal Tagihan, Penggunaan, dan Bantuan -->
  <?php $this->load->view('komponen/modal_tagihan'); ?>
  <?php $this->load->view('komponen/modal_penggunaan'); ?>
  <?php $this->load->view('komponen/modal_bantuan'); ?>

  <!-- Modal Tingkatkan ke PRDAI Pro -->
  <?php $this->load->view('komponen/modal_tingkatkan'); ?>

  <!-- Input Berkas Tersembunyi untuk Lampiran Dokumen (+) -->
  <input type="file" id="input-berkas-lampiran" class="tersembunyi" accept=".md,.markdown,.txt,.json,.csv,.pdf,.doc,.docx,.xls,.xlsx,image/*">

  <!-- ==================== DRAWER RIWAYAT VERSI ==================== -->
  <div class="drawer-versi-backdrop tersembunyi" id="backdrop-versi">
    <div class="drawer-versi" id="drawer-versi">
      <div class="drawer-versi__header">
        <h3 class="drawer-versi__judul">Riwayat Versi Dokumen</h3>
        <button type="button" class="drawer-versi__tutup" id="tombol-tutup-versi" aria-label="Tutup">✕</button>
      </div>
      <div class="drawer-versi__isi" id="daftar-riwayat-versi">
        <?php if ( ! empty($daftar_versi)): ?>
          <?php foreach ($daftar_versi as $v): ?>
            <div class="item-versi">
              <div class="item-versi__info">
                <div class="item-versi__nomor">Versi <?php echo (int) $v->nomor_versi; ?></div>
                <div class="item-versi__waktu"><?php echo date('d M Y, H:i', strtotime($v->dibuat_pada)); ?> &bull; <?php echo e(ucfirst($v->dibuat_oleh)); ?></div>
                <?php if ( ! empty($v->catatan_revisi)): ?>
                  <div class="item-versi__catatan"><?php echo e($v->catatan_revisi); ?></div>
                <?php endif; ?>
              </div>
              <button type="button" class="btn-pulihkan-versi" data-versi="<?php echo (int) $v->nomor_versi; ?>">Pulihkan</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="drawer-versi__kosong">Belum ada riwayat versi sebelumnya.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal-konfirmasi-backdrop tersembunyi" id="modal-hapus-backdrop">
    <div class="modal-konfirmasi">
      <h3 class="modal-konfirmasi__judul">Hapus Dokumen PRD?</h3>
      <p class="modal-konfirmasi__deskripsi">Dokumen ini akan dihapus dari daftar Anda. Tindakan ini tidak dapat dibatalkan.</p>
      <div class="modal-konfirmasi__aksi">
        <button type="button" class="btn-batal-editor" id="tombol-batal-hapus">Batal</button>
        <button type="button" class="btn-aksi-dokumen--bahaya-solid" id="tombol-konfirmasi-hapus">Hapus Dokumen</button>
      </div>
    </div>
  </div>

  <!-- Toast Notifikasi Ringan -->
  <div class="toast-notifikasi tersembunyi" id="toast-notifikasi"></div>

</div>
