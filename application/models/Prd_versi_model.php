<?php
/**
 * PRDAI — Model riwayat versi PRD.
 *
 * Setiap simpan otomatis membuat versi baru (sumber: ai, manual, pemulihan).
 * Menjaga batas maksimal 50 versi per PRD sesuai Bagian 8.7 PRD.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Prd_versi_model extends CI_Model
{
    protected $tabel = 'prd_versi';

    /**
     * Tambah versi baru untuk dokumen PRD.
     */
    public function tambah_versi($prd_id, $isi_markdown, $sumber = 'manual', $catatan = NULL)
    {
        $prd_id = (int) $prd_id;

        // Ambil nomor versi terakhir
        $terakhir = $this->db
            ->select_max('nomor_versi')
            ->where('prd_id', $prd_id)
            ->get($this->tabel)
            ->row();

        $nomor_baru = ($terakhir && $terakhir->nomor_versi) ? ((int) $terakhir->nomor_versi + 1) : 1;

        $this->db->insert($this->tabel, array(
            'prd_id'       => $prd_id,
            'nomor_versi'  => $nomor_baru,
            'isi_markdown' => $isi_markdown,
            'sumber'       => $sumber,
            'catatan'      => $catatan ? substr($catatan, 0, 255) : NULL,
            'dibuat_pada'  => date('Y-m-d H:i:s'),
        ));

        // Pangkas versi tertua bila melebihi batas 50 versi
        $total = $this->db->where('prd_id', $prd_id)->count_all_results($this->tabel);
        if ($total > 50)
        {
            $jumlah_pangkas = $total - 50;
            $versi_lama = $this->db
                ->select('id')
                ->where('prd_id', $prd_id)
                ->order_by('nomor_versi', 'ASC')
                ->limit($jumlah_pangkas)
                ->get($this->tabel)
                ->result();

            if ( ! empty($versi_lama))
            {
                $id_dihapus = array_map(function($v) { return $v->id; }, $versi_lama);
                $this->db->where_in('id', $id_dihapus)->delete($this->tabel);
            }
        }

        return $nomor_baru;
    }

    /**
     * Ambil seluruh riwayat versi untuk satu PRD.
     */
    public function ambil_semua($prd_id)
    {
        return $this->db
            ->where('prd_id', (int) $prd_id)
            ->order_by('nomor_versi', 'DESC')
            ->get($this->tabel)
            ->result();
    }

    /**
     * Ambil satu versi spesifik dari suatu PRD.
     */
    public function ambil_versi($prd_id, $nomor_versi)
    {
        return $this->db
            ->where('prd_id', (int) $prd_id)
            ->where('nomor_versi', (int) $nomor_versi)
            ->get($this->tabel)
            ->row();
    }
}
