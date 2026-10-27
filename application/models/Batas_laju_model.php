<?php
/**
 * PRDAI — Model pembatasan laju (rate limiting).
 *
 * Mengelola batasan kuota dan laju request berdasarkan kunci (IP, ID, atau aksi).
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Batas_laju_model extends CI_Model
{
    protected $tabel = 'batas_laju';

    /**
     * Periksa dan tingkatkan hitungan untuk suatu kunci dalam jendela detik tertentu.
     * Mengembalikan jumlah percobaan saat ini dalam jendela waktu aktif.
     */
    public function catat_dan_hitung($kunci, $jendela_detik = 3600)
    {
        $sekarang = date('Y-m-d H:i:s');
        $baris = $this->db->where('kunci', $kunci)->get($this->tabel)->row();

        if ( ! $baris)
        {
            $this->db->insert($this->tabel, array(
                'kunci'         => $kunci,
                'jumlah'        => 1,
                'jendela_mulai' => $sekarang,
            ));
            return 1;
        }

        $waktu_mulai = strtotime($baris->jendela_mulai);
        if ((time() - $waktu_mulai) >= $jendela_detik)
        {
            // Jendela waktu telah lewat, reset hitungan
            $this->db->where('id', $baris->id)->update($this->tabel, array(
                'jumlah'        => 1,
                'jendela_mulai' => $sekarang,
            ));
            return 1;
        }

        // Masih dalam jendela aktif, naikkan hitungan
        $jumlah_baru = $baris->jumlah + 1;
        $this->db->where('id', $baris->id)->update($this->tabel, array(
            'jumlah' => $jumlah_baru,
        ));

        return $jumlah_baru;
    }

    /**
     * Kunci "sedang berjalan" (mutex lock) dengan masa kedaluwarsa.
     * Mengembalikan TRUE jika berhasil mengunci, FALSE jika kunci masih aktif.
     */
    public function kunci_proses($kunci, $ttl_detik = 120)
    {
        $sekarang = date('Y-m-d H:i:s');
        $baris = $this->db->where('kunci', $kunci)->get($this->tabel)->row();

        if ( ! $baris)
        {
            $this->db->insert($this->tabel, array(
                'kunci'         => $kunci,
                'jumlah'        => 1,
                'jendela_mulai' => $sekarang,
            ));
            return TRUE;
        }

        $waktu_mulai = strtotime($baris->jendela_mulai);
        if ((time() - $waktu_mulai) > $ttl_detik)
        {
            // Kunci sebelumnya sudah kedaluwarsa (misal skrip mati di tengah proses)
            $this->db->where('id', $baris->id)->update($this->tabel, array(
                'jumlah'        => 1,
                'jendela_mulai' => $sekarang,
            ));
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Lepas kunci proses segera setelah selesai.
     */
    public function lepas_kunci($kunci)
    {
        return $this->db->where('kunci', $kunci)->delete($this->tabel);
    }
}
