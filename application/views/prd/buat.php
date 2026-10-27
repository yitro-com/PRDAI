<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wadah halaman-eksekusi-ai">
  <div class="eksekusi-kepala">
    <h1 class="eksekusi-kepala__judul">Susun PRD Sekali Input</h1>
    <p class="eksekusi-kepala__deskripsi">
      Tuliskan ide aplikasi atau fitur produk Anda. Sistem AI akan langsung mengeksekusi dan merakit dokumen PRD lengkap berstandar siap eksekusi.
    </p>
  </div>

  <div id="wadah-pesan-eksekusi"></div>

  <!-- Area Kotak Prompt Utama -->
  <div id="wadah-input-prompt">
    <?php echo form_open('prd/hasilkan', array('id' => 'form-susun-prd', 'novalidate' => 'novalidate')); ?>
      <div class="kotak-prompt">
        <textarea
          id="masukan-ide-produk"
          name="ide_produk"
          class="kotak-prompt__area"
          placeholder="Jelaskan produk atau fitur yang ingin dibuat... (Contoh: Aplikasi kasir warung berbasis web untuk UMKM dengan fitur scan barcode, cetak nota transaksi thermal, laporan laba rugi, dan multi-kasir)"
          required
          rows="4"
        ></textarea>

        <div class="kotak-prompt__bar-bawah">
          <div class="kotak-prompt__opsi">
            <select name="ditujukan_untuk" class="kotak-prompt__opsi-pilihan" aria-label="Format Dokumen">
              <option value="AI coding agent (Google Antigravity)" selected>Format: AI Coding Agent (Google Antigravity)</option>
              <option value="AI coding agent (Cline)">Format: AI Coding Agent (Cline)</option>
              <option value="Developer manusia">Format: Pengembang Manusia</option>
              <option value="Umum">Format: Ringkasan Umum</option>
            </select>

            <select name="tingkat_detail" class="kotak-prompt__opsi-pilihan" aria-label="Tingkat Detail">
              <option value="Sangat rinci" selected>Detail: Sangat Rinci</option>
              <option value="Standar">Detail: Standar</option>
              <option value="Ringkas">Detail: Ringkas</option>
            </select>
          </div>

          <div>
            <button type="submit" id="tombol-eksekusi" class="tombol tombol--utama kotak-prompt__tombol-kirim">
              Susun PRD
            </button>
          </div>
        </div>
      </div>
    <?php echo form_close(); ?>

    <!-- Saran Cepat (Inspirasi Ide) -->
    <div class="saran-ide">
      <span class="saran-ide__label">Contoh ide:</span>
      <button type="button" class="saran-ide__tombol" data-ide="Aplikasi kasir web untuk UMKM dengan scan barcode, cetak nota struk thermal, laporan laba rugi harian, dan manajemen multi-kasir.">
        Kasir Pintar UMKM
      </button>
      <button type="button" class="saran-ide__tombol" data-ide="Sistem reservasi dan antrean klinik gigi berbasis web responsif dengan pengingat jadwal otomatis, rekam medis ringkas, dan jadwal dokter.">
        Reservasi Klinik Gigi
      </button>
      <button type="button" class="saran-ide__tombol" data-ide="Platform SaaS pencatat feedback pelanggan terintegrasi widget web dengan analisis sentimen otomatis, voting fitur, dan papan roadmap publik.">
        Papan Feedback SaaS
      </button>
    </div>
  </div>

  <!-- Area Log Eksekusi Langsung (Tampil Saat Memproses Seperti di Gambar) -->
  <div id="area-log-eksekusi" class="log-eksekusi sembunyi" aria-live="polite">
    <!-- Kartu Permintaan Pengguna -->
    <div class="kartu-prompt-pengguna">
      <strong class="teks-redup">Permintaan:</strong>
      <p id="teks-prompt-terkirim" class="jarak-atas-2"></p>
    </div>

    <!-- Garis Kemajuan Tipis -->
    <div class="garis-kemajuan garis-kemajuan--berjalan">
      <div class="garis-kemajuan__isi"></div>
    </div>

    <!-- Alur Langkah Eksekusi Berjalan -->
    <div id="daftar-langkah-eksekusi" class="jarak-atas-4"></div>
  </div>
</div>
