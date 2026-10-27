<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- Skrip Inti & Vendor Lokal -->
<script src="<?php echo base_url('assets/js/inti/csrf.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/inti/api.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/mermaid/mermaid.min.js'); ?>"></script>

<!-- Skrip Khusus Halaman & Komponen Tambahan -->
<?php if ( ! empty($js_halaman) && is_array($js_halaman)): ?>
  <?php foreach ($js_halaman as $berkas_js): ?>
    <?php
      $jalur_rel_js = (strpos($berkas_js, '/') !== FALSE) ? 'assets/js/' . $berkas_js : 'assets/js/halaman/' . $berkas_js;
      $versi_js = file_exists(FCPATH . $jalur_rel_js) ? filemtime(FCPATH . $jalur_rel_js) : time();
    ?>
    <script src="<?php echo base_url($jalur_rel_js); ?>?v=<?php echo $versi_js; ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>

