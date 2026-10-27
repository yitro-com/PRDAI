<?php
/**
 * PRDAI — Pengontrol dasbor pengguna (Dasbor).
 *
 * Menampilkan daftar PRD milik pengguna aktif dengan fitur pencarian,
 * pengurutan terbaru, dan paginasi halaman.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Dasbor extends Pengontrol_Masuk
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Prd_model');
        $this->load->library('pagination');
    }

    /**
     * Alihkan dasbor ke antarmuka terpadu satu layar.
     */
    public function index()
    {
        redirect('/');
    }
}

