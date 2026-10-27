<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wadah wadah--baca halaman-detail-prd">
  <?php $this->load->view('komponen/pesan_kilat'); ?>

  <div class="detail-kepala">
    <h1 class="detail-kepala__judul"><?php echo e($prd->judul); ?></h1>

    <div class="detail-kepala__meta">
      <span class="lencana lencana--selesai">Selesai</span>
      <span><?php echo e($prd->jenis_produk); ?></span>
      <span>Versi <?php echo (int) $prd->versi_saat_ini; ?></span>
      <span>Diperbarui <?php echo format_tanggal_indonesia($prd->diperbarui_pada, TRUE); ?></span>
    </div>

    <!-- Bilah Aksi -->
    <div class="detail-aksi-bar">
      <a href="<?php echo site_url('prd/' . e($prd->kode) . '/ubah'); ?>" class="tombol tombol--utama">Sunting</a>
      <a href="<?php echo site_url('prd/' . e($prd->kode) . '/versi'); ?>" class="tombol tombol--sekunder">Riwayat Versi</a>
      <a href="<?php echo site_url('prd/' . e($prd->kode) . '/unduh'); ?>" class="tombol tombol--sekunder">Unduh .md</a>
      <button type="button" class="tombol tombol--utama btn-salin-semua-prd" data-salin-target="#teks-mentah-markdown" title="Salin langsung semua jawaban PRD yang diberikan" aria-label="Salin Semua Jawaban PRD">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        <span class="btn-salin-semua-prd__teks">Salin Semua Jawaban PRD</span>
      </button>

      <?php echo form_open('prd/' . e($prd->kode) . '/hapus', array('class' => 'form-aksi-inline')); ?>
        <button
          type="submit"
          class="tombol tombol--bahaya"
          data-konfirmasi="Apakah Anda yakin ingin menghapus PRD ini? Tindakan ini tidak dapat dibatalkan."
        >Hapus</button>
      <?php echo form_close(); ?>
    </div>
  </div>

  <div class="tata-letak-baca">
    <!-- Konten Dokumen PRD Hasil Markdown -->
    <article class="dokumen-prd">
      <?php if ( ! empty($html_prd)): ?>
        <?php echo $html_prd; ?>
      <?php else: ?>
        <p class="teks-redup">Dokumen PRD ini belum memiliki isi konten.</p>
      <?php endif; ?>
    </article>

    <!-- Bilah Daftar Isi Samping -->
    <aside class="bilah-daftar-isi" aria-label="Daftar Isi Dokumen">
      <div class="daftar-isi__judul">Daftar Bagian</div>
      <nav class="daftar-isi__nav">
        <ul id="daftar-isi-list"></ul>
      </nav>
    </aside>
  </div>

  <!-- Teks Mentah untuk Kebutuhan Salin Papan Klip (Sembunyi) -->
  <textarea id="teks-mentah-markdown" class="sembunyi" readonly><?php echo e($prd->isi_markdown); ?></textarea>
</div>
