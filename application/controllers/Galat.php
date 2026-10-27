<?php
/**
 * PRDAI — Pengontrol halaman galat (403, 404).
 *
 * Menampilkan halaman kesalahan ramah pengguna dalam Bahasa Indonesia
 * dengan tata letak baku aplikasi.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Galat extends MY_Controller
{
    /**
     * Tampilkan halaman 404 Tidak Ditemukan.
     */
    public function tidak_ditemukan()
    {
        $this->output->set_status_header(404);
        $data = array(
            'judul_halaman' => 'Halaman Tidak Ditemukan — PRDAI',
            'css_halaman'   => array('galat.css'),
        );
        $this->tampilkan('galat/404', $data);
    }

    /**
     * Tampilkan halaman 403 Akses Ditolak.
     */
    public function akses_ditolak()
    {
        $this->output->set_status_header(403);
        $data = array(
            'judul_halaman' => 'Akses Ditolak — PRDAI',
            'css_halaman'   => array('galat.css'),
        );
        $this->tampilkan('galat/403', $data);
    }
}
