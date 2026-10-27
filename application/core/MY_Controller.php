<?php
/**
 * PRDAI — Pengontrol dasar aplikasi (MY_Controller).
 *
 * Menyediakan kerangka umum untuk semua controller: perakitan tata letak,
 * verifikasi request AJAX, serta status otentikasi pengguna aktif.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// Muat kelas turunan secara manual karena CI3 hanya otomatis memuat awalan MY_.
require_once APPPATH . 'core/Pengontrol_Tamu.php';
require_once APPPATH . 'core/Pengontrol_Masuk.php';
require_once APPPATH . 'core/Pengontrol_Admin.php';

class MY_Controller extends CI_Controller
{
    /**
     * Data pengguna aktif jika sudah masuk, atau NULL jika tamu.
     * @var object|null
     */
    protected $pengguna_masuk = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->muat_pengguna_aktif();
    }

    /**
     * Muat data pengguna aktif dari sesi basis data.
     * Mengunci sesi dan token persisten ke IP klien agar sesi tidak kadaluarsa
     * selama pengguna tidak keluar (logout), serta mencegah pembajakan sesi.
     */
    protected function muat_pengguna_aktif()
    {
        $ip_klien    = function_exists('ambil_ip') ? ambil_ip() : '0.0.0.0';
        $pengguna_id = $this->session->userdata('pengguna_id');
        $ip_sesi     = $this->session->userdata('ip_terkunci');

        // Jika sesi ada tapi IP sesi tidak cocok dengan IP klien saat ini, putuskan sesi (keamanan kunci IP)
        if ($pengguna_id && ! empty($ip_sesi) && $ip_sesi !== $ip_klien)
        {
            $this->session->sess_destroy();
            $pengguna_id = NULL;
        }

        // Jika sesi belum ada atau baru saja diputus, periksa cookie "Ingat saya" yang terkunci IP
        if ( ! $pengguna_id)
        {
            $this->load->helper('cookie');
            $cookie_ingat = get_cookie('prdai_ingat');
            if ($cookie_ingat && strpos($cookie_ingat, ':') !== FALSE)
            {
                list($ingat_id, $ingat_token) = explode(':', $cookie_ingat, 2);
                $this->load->model('Pengguna_model');
                $token_hash = hash('sha256', $ingat_token);
                $pengguna_ingat = $this->Pengguna_model->ambil_berdasarkan_token_ingat((int) $ingat_id, $token_hash, $ip_klien);
                if ($pengguna_ingat)
                {
                    // Pulihkan sesi secara otomatis tanpa perlu login ulang
                    $this->session->sess_regenerate(TRUE);
                    $this->session->set_userdata('pengguna_id', $pengguna_ingat->id);
                    $this->session->set_userdata('ip_terkunci', $ip_klien);
                    if ( ! empty($pengguna_ingat->zona_waktu))
                    {
                        $this->session->set_userdata('zona_waktu', $pengguna_ingat->zona_waktu);
                    }

                    // Kunci IP di basis data jika belum terisi
                    if (empty($pengguna_ingat->ip_terkunci))
                    {
                        $this->Pengguna_model->simpan_token_ingat(
                            $pengguna_ingat->id,
                            $token_hash,
                            $pengguna_ingat->ingat_kadaluarsa,
                            $ip_klien
                        );
                    }

                    $pengguna_id = $pengguna_ingat->id;
                }
                else
                {
                    // Cookie tidak valid, kadaluarsa, atau berasal dari IP berbeda
                    delete_cookie('prdai_ingat');
                }
            }
        }

        if ($pengguna_id)
        {
            $this->load->model('Pengguna_model');
            $pengguna = $this->Pengguna_model->ambil_berdasarkan_id($pengguna_id);
            if ($pengguna && $pengguna->status === 'aktif')
            {
                // Jika data pengguna memiliki IP terkunci di basis data, pastikan IP cocok
                $ip_db = ! empty($pengguna->ip_terkunci) ? $pengguna->ip_terkunci : '';
                $is_localhost_match = ($ip_db === '127.0.0.1' || $ip_db === '::1') && ($ip_klien === '127.0.0.1' || $ip_klien === '::1');
                if ( ! empty($ip_db) && $ip_db !== $ip_klien && ! $is_localhost_match)
                {
                    $this->session->sess_destroy();
                    $this->load->helper('cookie');
                    delete_cookie('prdai_ingat');
                    $this->pengguna_masuk = NULL;
                    return;
                }

                // Simpan IP terkunci ke sesi jika belum ada
                if (empty($ip_sesi))
                {
                    $this->session->set_userdata('ip_terkunci', $ip_klien);
                }

                $this->pengguna_masuk = $pengguna;
            }
            else
            {
                // Sesi tidak lagi valid di basis data
                $this->session->unset_userdata('pengguna_id');
                $this->pengguna_masuk = NULL;
            }
        }
        else
        {
            $this->pengguna_masuk = NULL;
        }
    }

    /**
     * Tampilkan halaman lengkap dengan susunan tata letak baku:
     * kepala -> navigasi -> konten utama -> kaki.
     *
     * @param string $nama_view Path ke file view (contoh: 'beranda/index')
     * @param array  $data      Data yang dikirim ke view
     */
    public function tampilkan($nama_view, $data = array())
    {
        if ( ! is_array($data))
        {
            $data = array();
        }

        // Teruskan info pengguna masuk ke semua komponen tampilan
        $data['pengguna_masuk'] = $this->pengguna_masuk;

        // Hitung inisial dan data profil dinamis berdasarkan pengguna yang sedang masuk
        $inisial = 'YA';
        $nama_pengguna = 'Tamu';
        $email_pengguna = '';
        $tier_pengguna = 'Pro';

        if ($this->pengguna_masuk)
        {
            $nama_pengguna = $this->pengguna_masuk->nama;
            $email_pengguna = $this->pengguna_masuk->email;
            $this->load->model('Langganan_model');
            $data_langganan = $this->Langganan_model->ambil_langganan($this->pengguna_masuk->id);
            $tier_pengguna = ($data_langganan && ! empty($data_langganan->paket)) ? $data_langganan->paket : 'Pro';

            $bagian_nama = preg_split('/\s+/', trim($this->pengguna_masuk->nama));
            if (count($bagian_nama) >= 2)
            {
                $inisial = strtoupper(mb_substr($bagian_nama[0], 0, 1) . mb_substr($bagian_nama[1], 0, 1));
            }
            else if (count($bagian_nama) === 1 && mb_strlen($bagian_nama[0]) > 0)
            {
                $inisial = strtoupper(mb_substr($bagian_nama[0], 0, min(2, mb_strlen($bagian_nama[0]))));
            }
            $this->load->helper('cookie');
            $cookie_tz = get_cookie('prdai_zona_waktu');
            if ( ! empty($cookie_tz) && empty($this->pengguna_masuk->zona_waktu))
            {
                $this->load->model('Pengguna_model');
                $this->Pengguna_model->perbarui_zona_waktu($this->pengguna_masuk->id, $cookie_tz);
                $this->pengguna_masuk->zona_waktu = $cookie_tz;
            }
        }

        $zona_aktif = ($this->pengguna_masuk && ! empty($this->pengguna_masuk->zona_waktu))
            ? $this->pengguna_masuk->zona_waktu
            : ((function_exists('get_cookie') && get_cookie('prdai_zona_waktu')) ? get_cookie('prdai_zona_waktu') : 'Asia/Makassar');

        $data['user_inisial']        = $inisial;
        $data['user_nama']           = $nama_pengguna;
        $data['user_email']          = $email_pengguna;
        $data['user_tier']           = $tier_pengguna;
        $data['user_zona_waktu']     = $zona_aktif;
        $data['user_terakhir_masuk'] = ($this->pengguna_masuk && ! empty($this->pengguna_masuk->terakhir_masuk)) ? $this->pengguna_masuk->terakhir_masuk : '';

        // Judul halaman bawaan bila belum ditentukan
        if ( ! isset($data['judul_halaman']))
        {
            $data['judul_halaman'] = 'PRDAI — Pembuat PRD Berbasis AI';
        }


        // CSS & JS spesifik halaman
        if ( ! isset($data['css_halaman']))
        {
            $data['css_halaman'] = array();
        }
        if ( ! isset($data['js_halaman']))
        {
            $data['js_halaman'] = array();
        }

        $this->load->view('tata_letak/kepala', $data);
        $this->load->view('tata_letak/navigasi', $data);
        $this->load->view($nama_view, $data);
        $this->load->view('tata_letak/kaki', $data);
    }

    /**
     * Pastikan permintaan adalah AJAX, jika tidak hentikan dengan galat 400.
     */
    protected function jika_ajax_wajib()
    {
        if ( ! $this->input->is_ajax_request())
        {
            kirim_galat_json('Permintaan harus melalui AJAX.', array(), 400);
        }
    }
}
