<?php
/**
 * PRDAI — Kelas dasar pengontrol masuk (hanya untuk pengguna yang SUDAH masuk).
 *
 * Bila pengunjung belum masuk, dialihkan ke /masuk.
 * Memeriksa status akun setiap request; jika nonaktif, sesi dihapus.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengontrol_Masuk extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->pengguna_masuk === NULL)
        {
            if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Silakan masuk terlebih dahulu untuk mengakses.', 401);
            }

            // Belum masuk, simpan pesan kilat dan alihkan ke halaman masuk.
            $this->session->set_flashdata('pesan_galat', 'Silakan masuk terlebih dahulu untuk mengakses.');
            redirect('masuk');
        }

        // Periksa status akun tetap aktif di basis data.
        if (isset($this->pengguna_masuk->status) && $this->pengguna_masuk->status !== 'aktif')
        {
            $this->session->sess_destroy();
            redirect('masuk');
        }
    }
}
