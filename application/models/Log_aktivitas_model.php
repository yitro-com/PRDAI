<?php
/**
 * PRDAI — Model log aktivitas audit.
 *
 * Mencatat peristiwa penting autentikasi dan manipulasi data untuk
 * keperluan keamanan dan audit trail tanpa mencatat data rahasia.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_aktivitas_model extends CI_Model
{
    protected $tabel = 'log_aktivitas';

    /**
     * Catat aksi pengguna atau tamu.
     */
    public function catat($aksi, $keterangan = NULL, $pengguna_id = NULL)
    {
        $ip = function_exists('ambil_ip') ? ambil_ip() : '0.0.0.0';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '-';

        return $this->db->insert($this->tabel, array(
            'pengguna_id'   => $pengguna_id ? (int) $pengguna_id : NULL,
            'aksi'          => substr($aksi, 0, 60),
            'keterangan'    => $keterangan ? substr($keterangan, 0, 255) : NULL,
            'alamat_ip'     => $ip,
            'agen_pengguna' => $ua,
            'dibuat_pada'   => date('Y-m-d H:i:s'),
        ));
    }
}
