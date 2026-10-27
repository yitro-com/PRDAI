<?php
/**
 * PRDAI — Model Pusat Bantuan & FAQ.
 *
 * Mengelola kategori panduan dan daftar pertanyaan populer dari basis data real.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Bantuan_model extends CI_Model
{
    protected $tabel_kategori = 'bantuan_kategori';
    protected $tabel_faq = 'bantuan_faq';

    /**
     * Ambil seluruh kategori panduan bantuan urut berdasarkan urutan.
     *
     * @return array
     */
    public function ambil_kategori()
    {
        return $this->db
            ->order_by('urutan', 'ASC')
            ->get($this->tabel_kategori)
            ->result();
    }

    /**
     * Ambil daftar pertanyaan populer untuk accordion FAQ.
     *
     * @param int $limit
     * @return array
     */
    public function ambil_faq_populer($limit = 10)
    {
        return $this->db
            ->where('populer', 1)
            ->order_by('urutan', 'ASC')
            ->limit($limit)
            ->get($this->tabel_faq)
            ->result();
    }
}
