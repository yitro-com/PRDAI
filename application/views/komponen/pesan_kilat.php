<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$pesan_sukses = $this->session->flashdata('pesan_sukses');
$pesan_galat  = $this->session->flashdata('pesan_galat');
$pesan_info   = $this->session->flashdata('pesan_info');
?>
<?php if ( ! empty($pesan_sukses)): ?>
  <div class="pesan-kilat pesan-kilat--sukses" role="status">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pesan-kilat__ikon" aria-hidden="true">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
      <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span><?php echo e($pesan_sukses); ?></span>
  </div>
<?php endif; ?>

<?php if ( ! empty($pesan_galat)): ?>
  <div class="pesan-kilat pesan-kilat--galat" role="alert">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pesan-kilat__ikon" aria-hidden="true">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="8" x2="12" y2="12"></line>
      <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <span><?php echo e($pesan_galat); ?></span>
  </div>
<?php endif; ?>

<?php if ( ! empty($pesan_info)): ?>
  <div class="pesan-kilat pesan-kilat--info" role="status">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pesan-kilat__ikon" aria-hidden="true">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="16" x2="12" y2="12"></line>
      <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>
    <span><?php echo e($pesan_info); ?></span>
  </div>
<?php endif; ?>
