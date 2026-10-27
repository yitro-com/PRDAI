<?php
/**
 * PRDAI — Model PRD.
 *
 * Mengelola pembuatan, pembacaan, pembaruan, dan penghapusan lunak dokumen PRD.
 * Seluruh method wajib menyertakan pengguna_id untuk mencegah akses tanpa izin (IDOR).
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Prd_model extends CI_Model
{
    protected $tabel = 'prd';

    /**
     * Ambil satu dokumen PRD milik pengguna berdasarkan kode acak 16 karakter.
     */
    public function ambil_milik($kode, $pengguna_id)
    {
        return $this->db
            ->where('kode', $kode)
            ->where('pengguna_id', (int) $pengguna_id)
            ->where('dihapus_pada IS NULL', NULL, FALSE)
            ->get($this->tabel)
            ->row();
    }

    /**
     * Ambil daftar PRD milik pengguna dengan pencarian dan paginasi.
     */
    public function ambil_daftar_milik($pengguna_id, $q = '', $batas = 10, $mulai = 0)
    {
        $this->db
            ->where('pengguna_id', (int) $pengguna_id)
            ->where('dihapus_pada IS NULL', NULL, FALSE);

        if ( ! empty($q))
        {
            $this->db->like('judul', substr($q, 0, 100));
        }

        return $this->db
            ->order_by('diperbarui_pada', 'DESC')
            ->limit((int) $batas, (int) $mulai)
            ->get($this->tabel)
            ->result();
    }

    /**
     * Hitung total PRD aktif milik pengguna (untuk konfigurasi paginasi).
     */
    public function hitung_milik($pengguna_id, $q = '')
    {
        $this->db
            ->where('pengguna_id', (int) $pengguna_id)
            ->where('dihapus_pada IS NULL', NULL, FALSE);

        if ( ! empty($q))
        {
            $this->db->like('judul', substr($q, 0, 100));
        }

        return $this->db->count_all_results($this->tabel);
    }

    /**
     * Buat draf PRD baru dengan kode acak unik 16 karakter.
     */
    public function buat($data, $pengguna_id)
    {
        // Pastikan kode acak 16 karakter unik
        do {
            $kode = buat_kode_acak(16);
            $sudah_ada = $this->db->where('kode', $kode)->count_all_results($this->tabel);
        } while ($sudah_ada > 0);

        $waktu = date('Y-m-d H:i:s');
        $baris = array(
            'kode'           => $kode,
            'pengguna_id'    => (int) $pengguna_id,
            'judul'          => substr(trim($data['judul']), 0, 150),
            'jenis_produk'   => substr(trim($data['jenis_produk']), 0, 40),
            'masukan_wizard' => isset($data['masukan_wizard']) ? json_encode($data['masukan_wizard'], JSON_UNESCAPED_UNICODE) : NULL,
            'isi_markdown'   => isset($data['isi_markdown']) ? $data['isi_markdown'] : NULL,
            'status'         => isset($data['status']) ? $data['status'] : 'draf',
            'versi_saat_ini' => isset($data['versi_saat_ini']) ? (int) $data['versi_saat_ini'] : 0,
            'dibuat_pada'    => $waktu,
            'diperbarui_pada'=> $waktu,
            'dihapus_pada'   => NULL,
        );

        $this->db->insert($this->tabel, $baris);
        $baris['id'] = $this->db->insert_id();

        return (object) $baris;
    }

    /**
     * Perbarui isi PRD dan status saat penyusunan selesai atau disunting manual.
     */
    public function perbarui_isi($id, $isi_markdown, $status = 'selesai', $versi = 1)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, array(
                'isi_markdown'   => $isi_markdown,
                'status'         => $status,
                'versi_saat_ini' => (int) $versi,
                'diperbarui_pada'=> date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Perbarui status proses penyusunan (misal: gagal atau menyusun).
     */
    public function perbarui_status($id, $status)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, array(
                'status'         => $status,
                'diperbarui_pada'=> date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Hapus lunak dokumen PRD milik pengguna.
     */
    public function hapus_lunak($kode, $pengguna_id)
    {
        return $this->db
            ->where('kode', $kode)
            ->where('pengguna_id', (int) $pengguna_id)
            ->update($this->tabel, array(
                'dihapus_pada' => date('Y-m-d H:i:s'),
            ));
    }
}
