<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wadah halaman-dasbor">
  <div class="dasbor-kepala">
    <div>
      <h1 class="dasbor-kepala__judul">PRD Saya</h1>
      <p class="teks-redup">Kelola dan tinjau seluruh dokumen PRD yang telah Anda buat.</p>
    </div>

    <div>
      <a href="<?php echo site_url('prd/buat'); ?>" class="tombol tombol--utama">Buat PRD Baru</a>
    </div>
  </div>

  <?php $this->load->view('komponen/pesan_kilat'); ?>

  <!-- Pencarian -->
  <form action="<?php echo site_url('dasbor'); ?>" method="get" class="dasbor-cari">
    <input
      type="search"
      id="input-cari-prd"
      name="q"
      class="masukan-teks dasbor-cari__input"
      placeholder="Cari berdasarkan judul PRD..."
      value="<?php echo e($kata_kunci); ?>"
      maxlength="100"
    >
    <button type="submit" class="tombol tombol--sekunder">Cari</button>
    <?php if ($kata_kunci !== ''): ?>
      <a href="<?php echo site_url('dasbor'); ?>" class="tombol tombol--teks">Reset</a>
    <?php endif; ?>
  </form>

  <!-- Daftar PRD -->
  <?php if (empty($daftar_prd)): ?>
    <div class="dasbor-kosong">
      <p class="dasbor-kosong__teks">
        <?php if ($kata_kunci !== ''): ?>
          Tidak ditemukan PRD dengan judul "<?php echo e($kata_kunci); ?>".
        <?php else: ?>
          Belum ada PRD. Buat yang pertama sekarang.
        <?php endif; ?>
      </p>
      <div>
        <a href="<?php echo site_url('prd/buat'); ?>" class="tombol tombol--utama">Buat PRD Sekarang</a>
      </div>
    </div>
  <?php else: ?>
    <div class="daftar-baris">
      <?php foreach ($daftar_prd as $item): ?>
        <?php $this->load->view('komponen/kartu_prd', array('item' => $item)); ?>
      <?php endforeach; ?>
    </div>

    <?php if ( ! empty($tautan_paginasi)): ?>
      <?php echo $tautan_paginasi; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
