<?php
/**
 * PRDAI — Library pembatas laju (Rate Limiter).
 *
 * Mengendalikan frekuensi pendaftaran, upaya login, dan pembatasan kuota AI harian
 * untuk mencegah serangan brute force dan eksploitasi biaya server.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembatas_laju
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('Batas_laju_model');
        $this->ci->load->model('Percobaan_masuk_model');
    }

    /**
     * Periksa apakah IP diizinkan mendaftar akun baru.
     */
    public function boleh_daftar($alamat_ip)
    {
        $batas   = (int) $this->ci->config->item('daftar_batas_jumlah');
        $jendela = (int) $this->ci->config->item('daftar_jendela_detik');
        $kunci   = 'daftar_ip_' . $alamat_ip;

        $hitungan = $this->ci->Batas_laju_model->catat_dan_hitung($kunci, $jendela);
        return ($hitungan <= $batas);
    }

    /**
     * Periksa apakah kombinasi email dan IP tidak sedang terkunci dari login.
     */
    public function boleh_masuk($email, $alamat_ip)
    {
        $maks_gagal = (int) $this->ci->config->item('masuk_gagal_maks');
        $jendela    = (int) $this->ci->config->item('masuk_jendela_detik');

        $gagal = $this->ci->Percobaan_masuk_model->hitung_gagal($email, $alamat_ip, $jendela);
        return ($gagal < $maks_gagal);
    }

    /**
     * Catat upaya login gagal.
     */
    public function catat_masuk_gagal($email, $alamat_ip)
    {
        return $this->ci->Percobaan_masuk_model->catat($email, $alamat_ip, FALSE);
    }

    /**
     * Catat login berhasil.
     */
    public function catat_masuk_sukses($email, $alamat_ip)
    {
        return $this->ci->Percobaan_masuk_model->catat($email, $alamat_ip, TRUE);
    }

    /**
     * Periksa kuota harian penyusunan AI pengguna berdasarkan paket tier.
     * Pro memiliki akses tanpa batas, sedangkan Free, Go, Plus memiliki batas harian.
     */
    public function boleh_susun_ai($pengguna_id)
    {
        $this->ci->load->model('Langganan_model');
        $langganan = $this->ci->Langganan_model->ambil_langganan($pengguna_id);
        $tier = ($langganan && ! empty($langganan->paket)) ? strtolower($langganan->paket) : 'pro';

        // Akun Pro memiliki akses tanya jawab tanpa batas
        if ($tier === 'pro')
        {
            return TRUE;
        }

        $aturan_pesan = array(
            'free' => 20,
            'go'   => 50,
            'plus' => 100,
        );
        $batas_pesan = isset($aturan_pesan[$tier]) ? $aturan_pesan[$tier] : 20;

        $hari_ini = date('Y-m-d');
        $hitung = $this->ci->db
            ->where('pengguna_id', (int) $pengguna_id)
            ->where('DATE(dibuat_pada)', $hari_ini)
            ->count_all_results('penggunaan_ai');

        if ($hitung >= $batas_pesan)
        {
            throw new Exception("Batas harian untuk paket " . ucfirst($tier) . " ({$batas_pesan} pesan/hari) telah tercapai. Tingkatkan ke Pro untuk akses tanpa batas.");
        }

        return TRUE;
    }

    /**
     * Tambah hitungan kuota harian setelah AI berhasil atau diproses (pencatatan metrik).
     */
    public function tambah_hitungan_ai($pengguna_id)
    {
        $kunci = 'ai_harian_' . date('Ymd') . '_' . (int) $pengguna_id;
        return $this->ci->Batas_laju_model->catat_dan_hitung($kunci, 86400);
    }

    /**
     * Kunci eksklusif dinonaktifkan agar tidak menyebabkan deadlock pemblokiran
     * saat pengguna bertanya jawab ("Penyusunan PRD sebelumnya masih berlangsung").
     */
    public function kunci_proses_ai($pengguna_id, $ttl_detik = 150)
    {
        return TRUE;
    }

    /**
     * Lepas kunci eksklusif proses AI.
     */
    public function lepas_kunci_ai($pengguna_id)
    {
        $kunci = 'kunci_ai_' . (int) $pengguna_id;
        return $this->ci->Batas_laju_model->lepas_kunci($kunci);
    }
}
