<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wadah halaman-ubah-prd">
  <div class="ubah-kepala">
    <h1 class="ubah-kepala__judul">Sunting PRD — <?php echo e($prd->judul); ?></h1>
    <p class="teks-redup">Setiap penyimpanan akan dicatat sebagai nomor versi baru secara otomatis.</p>
  </div>

  <?php $this->load->view('komponen/pesan_kilat'); ?>

  <div class="tab-wadah">
    <button type="button" id="tab-tulis" class="tab-tombol tab-tombol--aktif">Tulis</button>
    <button
      type="button"
      id="tab-pratinjau"
      class="tab-tombol"
      data-pratinjau-url="<?php echo site_url('prd/pratinjau'); ?>"
    >Pratinjau</button>
  </div>

  <?php echo form_open('prd/' . e($prd->kode) . '/ubah', array('id' => 'form-ubah-prd')); ?>

    <!-- Wadah Tulis -->
    <div id="wadah-editor">
      <div class="kelompok-bidang">
        <label for="textarea-markdown" class="kelompok-bidang__label">
          Isi Dokumen (Format Markdown) <span class="kelompok-bidang__wajib">*</span>
        </label>
        <textarea
          id="textarea-markdown"
          name="isi_markdown"
          class="editor-markdown"
          maxlength="200000"
          required
        ><?php echo set_value('isi_markdown', $prd->isi_markdown); ?></textarea>
        <div class="kelompok-bidang__hitungan">
          <span id="hitung-karakter-editor">0</span> / 200.000 karakter
        </div>
      </div>
    </div>

    <!-- Wadah Pratinjau -->
    <div id="wadah-pratinjau" class="sembunyi">
      <div id="area-pratinjau" class="pratinjau-area dokumen-prd"></div>
    </div>

    <div class="kelompok-bidang jarak-atas-4">
      <label for="masukan-catatan" class="kelompok-bidang__label">Catatan Perubahan (Opsional)</label>
      <input
        type="text"
        id="masukan-catatan"
        name="catatan_versi"
        class="masukan-teks"
        placeholder="Contoh: Menambahkan spesifikasi API endpoint dan revisi alur pengguna"
        maxlength="255"
      >
      <span class="kelompok-bidang__bantuan">Catatan ini akan muncul pada riwayat versi PRD.</span>
    </div>

    <div class="ubah-aksi-bar">
      <a href="<?php echo site_url('prd/' . e($prd->kode)); ?>" class="tombol tombol--sekunder">Batal</a>
      <button type="submit" class="tombol tombol--utama">Simpan Perubahan</button>
    </div>

  <?php echo form_close(); ?>
</div>
