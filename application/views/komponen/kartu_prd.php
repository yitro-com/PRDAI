<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Komponen satu baris PRD di dasbor.
 * Variabel input: $item (objek prd)
 */
$kelas_lencana = 'lencana--draf';
$teks_status = 'Draf';

switch ($item->status)
{
    case 'selesai':
        $kelas_lencana = 'lencana--selesai';
        $teks_status = 'Selesai';
        break;
    case 'menyusun':
        $kelas_lencana = 'lencana--menyusun';
        $teks_status = 'Menyusun...';
        break;
    case 'gagal':
        $kelas_lencana = 'lencana--gagal';
        $teks_status = 'Gagal';
        break;
}
?>
<div class="kartu-prd">
  <div class="kartu-prd__konten">
    <a href="<?php echo site_url('prd/' . e($item->kode)); ?>" class="kartu-prd__judul-tautan">
      <?php echo e($item->judul); ?>
    </a>

    <div class="kartu-prd__meta">
      <span class="lencana <?php echo $kelas_lencana; ?>"><?php echo $teks_status; ?></span>
      <span><?php echo e($item->jenis_produk); ?></span>
      <span>Diperbarui <?php echo format_tanggal_indonesia($item->diperbarui_pada); ?></span>
      <?php if ($item->versi_saat_ini > 0): ?>
        <span>v<?php echo (int) $item->versi_saat_ini; ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="kartu-prd__aksi">
    <a href="<?php echo site_url('prd/' . e($item->kode)); ?>" class="tombol tombol--sekunder">Buka</a>
    <a href="<?php echo site_url('prd/' . e($item->kode) . '/unduh'); ?>" class="tombol tombol--teks">Unduh .md</a>
  </div>
</div>
