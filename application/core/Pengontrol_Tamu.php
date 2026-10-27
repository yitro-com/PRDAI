<?php
/**
 * PRDAI — Kelas dasar pengontrol tamu (hanya untuk pengguna yang BELUM masuk).
 *
 * Bila pengguna yang sudah masuk mencoba membuka halaman tamu (seperti /masuk
 * atau /daftar), mereka akan dialihkan otomatis ke /dasbor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengontrol_Tamu extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->pengguna_masuk !== NULL)
        {
            redirect('/');
        }
    }
}
