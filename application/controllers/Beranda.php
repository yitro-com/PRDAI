<?php
/**
 * PRDAI — Pengontrol Ruang Kerja Utama (Beranda).
 *
 * Mengelola antarmuka terpadu satu layar (ChatGPT Dark Mode):
 * navigasi daftar terkini, input prompt sekali jalan, pratinjau dokumen PRD,
 * serta alur kerja tanpa muat ulang halaman.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Beranda extends Pengontrol_Masuk
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Prd_model');
        $this->load->model('Prd_versi_model');
        $this->load->model('Penggunaan_ai_model');
        $this->load->model('Langganan_model');
        $this->load->model('Kuota_model');
        $this->load->model('Bantuan_model');
        $this->load->library('Pemroses_markdown');
    }

    public function index($kode_prd = NULL)
    {
        $pengguna_id = (int) $this->pengguna_masuk->id;

        // Ambil daftar dokumen PRD terkini HANYA milik pengguna yang sedang aktif login
        $daftar_terkini = $this->Prd_model->ambil_daftar_milik($pengguna_id, '', 50, 0);

        // Periksa apakah ada kode PRD di URL atau query string
        if (empty($kode_prd))
        {
            $kode_prd = $this->input->get('prd', TRUE);
        }

        $prd_aktif = NULL;
        $html_prd = '';
        $daftar_versi = array();

        if ( ! empty($kode_prd))
        {
            $prd_aktif = $this->Prd_model->ambil_milik($kode_prd, $pengguna_id);
            if ($prd_aktif)
            {
                $html_prd = $this->pemroses_markdown->ubah_ke_html($prd_aktif->isi_markdown);
                $daftar_versi = $this->Prd_versi_model->ambil_semua($prd_aktif->id);
            }
        }

        $percakapan = array();
        if ($prd_aktif)
        {
            $mw = ! empty($prd_aktif->masukan_wizard) ? json_decode($prd_aktif->masukan_wizard, TRUE) : array();
            if (isset($mw['percakapan']) && is_array($mw['percakapan']))
            {
                $percakapan = $mw['percakapan'];
            }
            if (empty($percakapan))
            {
                $prompt_awal = isset($mw['ide_produk']) ? $mw['ide_produk'] : $prd_aktif->judul;
                $percakapan[] = array(
                    'id'       => 'msg_u_0',
                    'peran'    => 'user',
                    'pesan'    => $prompt_awal,
                    'lampiran' => isset($mw['lampiran_nama']) ? $mw['lampiran_nama'] : NULL,
                    'waktu'    => date('H:i', strtotime($prd_aktif->dibuat_pada)),
                );
                if ( ! empty($prd_aktif->isi_markdown))
                {
                    $percakapan[] = array(
                        'id'       => 'msg_a_0',
                        'peran'    => 'assistant',
                        'pesan'    => $prd_aktif->isi_markdown,
                        'waktu'    => date('H:i', strtotime($prd_aktif->diperbarui_pada ? $prd_aktif->diperbarui_pada : $prd_aktif->dibuat_pada)),
                    );
                }
            }

            foreach ($percakapan as &$msg)
            {
                if ($msg['peran'] === 'assistant')
                {
                    $msg['html'] = $this->pemroses_markdown->ubah_ke_html($msg['pesan']);
                }
            }
            unset($msg);
        }

        // Data profil dan aktivitas untuk drawer samping kanan & ringkasan
        $statistik = $this->Penggunaan_ai_model->ambil_ringkasan_pengguna($pengguna_id);
        $data_aktivitas = $this->Penggunaan_ai_model->ambil_data_aktivitas_token($pengguna_id);
        $grid_aktivitas = $data_aktivitas['grid_aktivitas'];

        $user_handle = 'palulunyitro';
        if ($this->pengguna_masuk)
        {
            $bagian_email = explode('@', $this->pengguna_masuk->email)[0];
            $user_handle = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $bagian_email));
            if (empty($user_handle)) $user_handle = 'user_' . substr(md5($pengguna_id), 0, 6);
        }

        $daftar_karya = $this->Prd_model->ambil_daftar_milik($pengguna_id, '', 3, 0);

        // Ambil data real dari basis data untuk langganan, tagihan, kuota, dan bantuan
        $langganan = $this->Langganan_model->ambil_langganan($pengguna_id);
        $riwayat_tagihan = $this->Langganan_model->ambil_riwayat_tagihan($pengguna_id);
        $penggunaan_kuota = $this->Kuota_model->ambil_statistik_penggunaan($pengguna_id);
        $bantuan_kategori = $this->Bantuan_model->ambil_kategori();
        $bantuan_faq = $this->Bantuan_model->ambil_faq_populer();

        $data = array(
            'judul_halaman'    => $prd_aktif ? ($prd_aktif->judul . ' — PRDAI') : 'PRDAI',
            'css_halaman'      => array('workspace.css', 'profil.css', 'komponen/menu_profil.css', 'komponen/drawer_profil.css', 'komponen/drawer_tingkatkan.css', 'komponen/drawer_tagihan.css', 'komponen/drawer_bantuan.css', 'komponen/drawer_penggunaan.css'),
            'js_halaman'       => array('workspace.js', 'profil.js', 'komponen/menu_profil.js', 'komponen/drawer_profil.js', 'komponen/drawer_billing.js'),
            'daftar_terkini'   => $daftar_terkini,
            'prd_aktif'        => $prd_aktif,
            'percakapan'       => $percakapan,
            'html_prd'         => $html_prd,
            'daftar_versi'     => $daftar_versi,
            'pengguna'         => $this->pengguna_masuk,
            'statistik'        => $statistik,
            'grid_aktivitas'   => $grid_aktivitas,
            'data_aktivitas'   => $data_aktivitas,
            'user_handle'      => $user_handle,
            'user_tier'        => ($langganan && ! empty($langganan->paket)) ? $langganan->paket : 'Pro',
            'daftar_karya'     => $daftar_karya,
            'langganan'        => $langganan,
            'riwayat_tagihan'  => $riwayat_tagihan,
            'penggunaan_kuota' => $penggunaan_kuota,
            'bantuan_kategori' => $bantuan_kategori,
            'bantuan_faq'      => $bantuan_faq,
        );

        $this->tampilkan('beranda/index', $data);
    }
}


