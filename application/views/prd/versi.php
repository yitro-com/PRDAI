<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wadah wadah--baca halaman-detail-prd">
  <?php $this->load->view('komponen/pesan_kilat'); ?>

  <div class="detail-kepala">
    <h1 class="detail-kepala__judul">Riwayat Versi — <?php echo e($prd->judul); ?></h1>
    <p class="teks-redup">Daftar rekaman perubahan dokumen. Anda dapat memulihkan dokumen ke versi mana pun kapan saja.</p>

    <div class="jarak-atas-4">
      <a href="<?php echo site_url('prd/' . e($prd->kode)); ?>" class="tombol tombol--sekunder">Kembali ke Dokumen</a>
    </div>
  </div>

  <div class="daftar-baris">
    <?php if (empty($daftar_versi)): ?>
      <div class="panel text-center">
        <p class="teks-redup">Belum ada riwayat versi.</p>
      </div>
    <?php else: ?>
      <?php foreach ($daftar_versi as $v): ?>
        <div class="kartu-prd">
          <div class="kartu-prd__konten">
            <div>
              <strong>Versi <?php echo (int) $v->nomor_versi; ?></strong>
              <?php if ((int) $v->nomor_versi === (int) $prd->versi_saat_ini): ?>
                <span class="lencana lencana--selesai jarak-kiri-2">Versi Aktif</span>
              <?php endif; ?>
            </div>

            <div class="kartu-prd__meta">
              <span>Sumber: <?php echo ucfirst(e($v->sumber)); ?></span>
              <span>Dibuat: <?php echo format_tanggal_indonesia($v->dibuat_pada, TRUE); ?></span>
              <?php if ( ! empty($v->catatan)): ?>
                <span>Catatan: &ldquo;<?php echo e($v->catatan); ?>&rdquo;</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="kartu-prd__aksi">
            <?php if ((int) $v->nomor_versi !== (int) $prd->versi_saat_ini): ?>
              <?php echo form_open('prd/' . e($prd->kode) . '/pulihkan/' . (int) $v->nomor_versi, array('class' => 'form-aksi-inline')); ?>
                <button
                  type="submit"
                  class="tombol tombol--sekunder"
                  data-konfirmasi="Pulihkan dokumen ke Versi <?php echo (int) $v->nomor_versi; ?>? Tindakan ini akan membuat versi baru dengan isi dari versi ini."
                >Pulihkan</button>
              <?php echo form_close(); ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
