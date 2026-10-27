<?php
/**
 * PRDAI — Model percobaan masuk (brute force protection).
 *
 * Mencatat percobaan autentikasi berhasil maupun gagal untuk membatasi
 * laju login per kombinasi email dan IP pengguna.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Percobaan_masuk_model extends CI_Model
{
    protected $tabel = 'percobaan_masuk';

    /**
     * Catat riwayat percobaan masuk.
     */
    public function catat($email, $alamat_ip, $berhasil = FALSE)
    {
        return $this->db->insert($this->tabel, array(
            'email'       => strtolower(trim($email)),
            'alamat_ip'   => $alamat_ip,
            'berhasil'    => $berhasil ? 1 : 0,
            'dibuat_pada' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Hitung berapa kali percobaan gagal dalam jendela waktu tertentu (detik).
     */
    public function hitung_gagal($email, $alamat_ip, $jendela_detik = 900)
    {
        $batas_waktu = date('Y-m-d H:i:s', time() - $jendela_detik);

        return $this->db
            ->where('email', strtolower(trim($email)))
            ->where('alamat_ip', $alamat_ip)
            ->where('berhasil', 0)
            ->where('dibuat_pada >=', $batas_waktu)
            ->count_all_results($this->tabel);
    }
}
