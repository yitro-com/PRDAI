<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRDAI — Pengontrol Langganan & Tagihan.
 *
 * Mengelola perubahan paket langganan dan pembatalan langsung ke basis data.
 */
class Langganan extends Pengontrol_Masuk
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Langganan_model');
        $this->load->model('Kuota_model');
        $this->load->model('Log_aktivitas_model');
    }

    /**
     * Ubah paket langganan secara interaktif ke basis data.
     */
    public function ubah()
    {
        $this->jika_ajax_wajib();

        $paket = trim($this->input->post('paket', TRUE));
        $paket_valid = array('Free', 'Go', 'Plus', 'Pro');

        if ( ! in_array($paket, $paket_valid, TRUE))
        {
            kirim_galat_json('Paket langganan tidak valid.', array(), 400);
            return;
        }

        $daftar_harga = array(
            'Free' => 0.00,
            'Go'   => 75000.00,
            'Plus' => 349000.00,
            'Pro'  => 1889000.00,
        );
        $harga = $daftar_harga[$paket];

        $pengguna_id = $this->pengguna_masuk->id;

        // 1. Perbarui status langganan di database
        $this->Langganan_model->ubah_paket($pengguna_id, $paket, $harga);

        // 2. Jika paket berbayar, catat invoice baru di riwayat tagihan
        if ($harga > 0)
        {
            $nomor_invoice = 'INV-' . date('Y') . '-' . str_pad(mt_rand(10, 999), 3, '0', STR_PAD_LEFT);
            $data_invoice = array(
                'pengguna_id'   => $pengguna_id,
                'nomor_invoice' => $nomor_invoice,
                'deskripsi'     => 'Paket ' . $paket . ' (1 Bulan)',
                'jumlah'        => $harga,
                'status'        => 'berhasil',
                'tanggal_bayar' => date('Y-m-d'),
                'tautan_pdf'    => 'faktur_' . str_replace('-', '_', $nomor_invoice) . '.pdf',
                'dibuat_pada'   => date('Y-m-d H:i:s'),
            );
            $this->db->insert('riwayat_tagihan', $data_invoice);
        }

        // 3. Perbarui kuota pengguna sesuai paket baru
        $kuota_aturan = array(
            'Free' => array('batas_token' => 5000, 'batas_pesan_harian' => 20, 'batas_gambar' => 5, 'batas_analisis_data' => 2, 'batas_pencarian_web' => 5),
            'Go'   => array('batas_token' => 25000, 'batas_pesan_harian' => 50, 'batas_gambar' => 25, 'batas_analisis_data' => 5, 'batas_pencarian_web' => 10),
            'Plus' => array('batas_token' => 50000, 'batas_pesan_harian' => 100, 'batas_gambar' => 50, 'batas_analisis_data' => 10, 'batas_pencarian_web' => 20),
            'Pro'  => array('batas_token' => 999999999, 'batas_pesan_harian' => 999999, 'batas_gambar' => 999999, 'batas_analisis_data' => 999999, 'batas_pencarian_web' => 999999),
        );

        if (isset($kuota_aturan[$paket]))
        {
            $update_kuota = $kuota_aturan[$paket];
            $update_kuota['paket'] = $paket;
            $update_kuota['sisa_hari_kuota'] = 30;
            $update_kuota['berlaku_hingga'] = date('Y-m-d', strtotime('+30 days'));
            $update_kuota['diperbarui_pada'] = date('Y-m-d H:i:s');
            $this->db->where('pengguna_id', $pengguna_id)->update('kuota_pengguna', $update_kuota);
        }

        $this->Log_aktivitas_model->catat('ubah_paket', 'Paket langganan diubah ke ' . $paket, $pengguna_id);

        kirim_sukses_json('Berhasil beralih ke paket ' . $paket . '.', array(
            'paket' => $paket,
            'harga' => $harga,
        ));
    }

    /**
     * Batalkan status perpanjangan otomatis langganan di basis data.
     */
    public function batal()
    {
        $this->jika_ajax_wajib();

        $pengguna_id = $this->pengguna_masuk->id;
        $this->Langganan_model->batalkan_langganan($pengguna_id);
        $this->Log_aktivitas_model->catat('batal_langganan', 'Langganan perpanjangan otomatis dibatalkan', $pengguna_id);

        kirim_sukses_json('Perpanjangan langganan berhasil dibatalkan. Akses fitur tetap aktif hingga akhir periode.');
    }
}
