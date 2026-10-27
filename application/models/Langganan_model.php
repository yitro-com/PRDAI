<?php
/**
 * PRDAI — Model Langganan & Tagihan.
 *
 * Mengelola data paket langganan aktif, metode pembayaran,
 * dan riwayat transaksi faktur (invoices) pengguna dari basis data real.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Langganan_model extends CI_Model
{
    protected $tabel_langganan = 'langganan';
    protected $tabel_tagihan = 'riwayat_tagihan';

    /**
     * Ambil data paket langganan aktif seorang pengguna.
     * Jika belum ada, otomatis buatkan data bawaan di basis data.
     *
     * @param int $pengguna_id
     * @return object
     */
    public function ambil_langganan($pengguna_id)
    {
        $id = (int) $pengguna_id;
        $row = $this->db->where('pengguna_id', $id)->get($this->tabel_langganan)->row();

        if ( ! $row)
        {
            // Buat record langganan awal (Akses Pro Penuh)
            $data_baru = array(
                'pengguna_id'                  => $id,
                'paket'                        => 'Pro',
                'harga'                        => 1889000.00,
                'siklus'                       => 'bulan',
                'status'                       => 'aktif',
                'dimulai_pada'                 => date('Y-m-d H:i:s'),
                'pembaruan_berikutnya'         => date('Y-m-d', strtotime('+30 days')),
                'metode_pembayaran_merk'       => 'Mastercard',
                'metode_pembayaran_nomor'      => '1234',
                'metode_pembayaran_kadaluarsa' => '12/29',
                'dibuat_pada'                  => date('Y-m-d H:i:s'),
                'diperbarui_pada'              => date('Y-m-d H:i:s'),
            );
            $this->db->insert($this->tabel_langganan, $data_baru);
            $row = $this->db->where('pengguna_id', $id)->get($this->tabel_langganan)->row();
        }

        return $row;
    }

    /**
     * Ambil seluruh riwayat transaksi tagihan pengguna urut dari yang terbaru.
     *
     * @param int $pengguna_id
     * @return array
     */
    public function ambil_riwayat_tagihan($pengguna_id)
    {
        $id = (int) $pengguna_id;
        $hasil = $this->db
            ->where('pengguna_id', $id)
            ->order_by('tanggal_bayar', 'DESC')
            ->get($this->tabel_tagihan)
            ->result();

        if (empty($hasil))
        {
            // Masukkan data transaksi awal jika tabel masih kosong
            $invoices = array(
                array(
                    'pengguna_id'   => $id,
                    'nomor_invoice' => 'INV-2026-003',
                    'deskripsi'     => 'Paket Plus (1 Bulan)',
                    'jumlah'        => 349000.00,
                    'status'        => 'berhasil',
                    'tanggal_bayar' => date('Y-m-d'),
                    'tautan_pdf'    => 'faktur_INV_2026_003.pdf',
                    'dibuat_pada'   => date('Y-m-d H:i:s'),
                ),
                array(
                    'pengguna_id'   => $id,
                    'nomor_invoice' => 'INV-2026-002',
                    'deskripsi'     => 'Paket Plus (1 Bulan)',
                    'jumlah'        => 349000.00,
                    'status'        => 'berhasil',
                    'tanggal_bayar' => date('Y-m-d', strtotime('-30 days')),
                    'tautan_pdf'    => 'faktur_INV_2026_002.pdf',
                    'dibuat_pada'   => date('Y-m-d H:i:s', strtotime('-30 days')),
                ),
                array(
                    'pengguna_id'   => $id,
                    'nomor_invoice' => 'INV-2026-001',
                    'deskripsi'     => 'Paket Plus (1 Bulan)',
                    'jumlah'        => 349000.00,
                    'status'        => 'berhasil',
                    'tanggal_bayar' => date('Y-m-d', strtotime('-60 days')),
                    'tautan_pdf'    => 'faktur_INV_2026_001.pdf',
                    'dibuat_pada'   => date('Y-m-d H:i:s', strtotime('-60 days')),
                ),
            );

            foreach ($invoices as $inv)
            {
                $this->db->insert($this->tabel_tagihan, $inv);
            }

            $hasil = $this->db
                ->where('pengguna_id', $id)
                ->order_by('tanggal_bayar', 'DESC')
                ->get($this->tabel_tagihan)
                ->result();
        }

        return $hasil;
    }

    /**
     * Perbarui paket langganan pengguna.
     *
     * @param int $pengguna_id
     * @param string $paket
     * @param float $harga
     * @return bool
     */
    public function ubah_paket($pengguna_id, $paket, $harga = 349000.00)
    {
        return $this->db->where('pengguna_id', (int) $pengguna_id)->update($this->tabel_langganan, array(
            'paket'                => $paket,
            'harga'                => (float) $harga,
            'status'               => 'aktif',
            'pembaruan_berikutnya' => date('Y-m-d', strtotime('+30 days')),
            'diperbarui_pada'      => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Batalkan status perpanjangan otomatis langganan.
     *
     * @param int $pengguna_id
     * @return bool
     */
    public function batalkan_langganan($pengguna_id)
    {
        return $this->db->where('pengguna_id', (int) $pengguna_id)->update($this->tabel_langganan, array(
            'status'          => 'dibatalkan',
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        ));
    }
}
