<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengontrol_Admin extends Pengontrol_Masuk
{
    public function __construct()
    {
        parent::__construct();

        if ( ! isset($this->pengguna_masuk->peran) || $this->pengguna_masuk->peran !== 'admin')
        {
            redirect('akses-ditolak');
        }
    }
}
