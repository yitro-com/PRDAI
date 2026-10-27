<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token-name" content="<?php echo e($this->security->get_csrf_token_name()); ?>">
  <meta name="csrf-token-hash" content="<?php echo e($this->security->get_csrf_hash()); ?>">
  <title><?php echo e($judul_halaman); ?></title>

  <!-- Gaya Dasar & Desain Token -->
  <link rel="stylesheet" href="<?php echo base_url('assets/css/dasar/variabel.css'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/dasar/reset.css'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/dasar/tipografi.css'); ?>">

  <!-- Gaya Komponen Bersama -->
  <link rel="stylesheet" href="<?php echo base_url('assets/css/komponen/tombol.css'); ?>?v=<?php echo file_exists(FCPATH . 'assets/css/komponen/tombol.css') ? filemtime(FCPATH . 'assets/css/komponen/tombol.css') : 1; ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/komponen/formulir.css'); ?>?v=<?php echo file_exists(FCPATH . 'assets/css/komponen/formulir.css') ? filemtime(FCPATH . 'assets/css/komponen/formulir.css') : 1; ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/komponen/kartu.css'); ?>?v=<?php echo file_exists(FCPATH . 'assets/css/komponen/kartu.css') ? filemtime(FCPATH . 'assets/css/komponen/kartu.css') : 1; ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/komponen/navigasi.css'); ?>?v=<?php echo file_exists(FCPATH . 'assets/css/komponen/navigasi.css') ? filemtime(FCPATH . 'assets/css/komponen/navigasi.css') : 1; ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/komponen/pesan.css'); ?>?v=<?php echo file_exists(FCPATH . 'assets/css/komponen/pesan.css') ? filemtime(FCPATH . 'assets/css/komponen/pesan.css') : 1; ?>">

  <!-- Gaya Khusus Halaman & Komponen Tambahan -->
  <?php if ( ! empty($css_halaman) && is_array($css_halaman)): ?>
    <?php foreach ($css_halaman as $berkas_css): ?>
      <?php
        $jalur_rel_css = (strpos($berkas_css, '/') !== FALSE) ? 'assets/css/' . $berkas_css : 'assets/css/halaman/' . $berkas_css;
        $versi_css = file_exists(FCPATH . $jalur_rel_css) ? filemtime(FCPATH . $jalur_rel_css) : time();
      ?>
      <link rel="stylesheet" href="<?php echo base_url($jalur_rel_css); ?>?v=<?php echo $versi_css; ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body>
