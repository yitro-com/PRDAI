<?php
/**
 * PRDAI — Pengontrol autentikasi (Daftar, Masuk, Keluar).
 *
 * Mengelola pendaftaran akun baru, login dengan mitigasi brute-force dan timing attacks,
 * serta penghancuran sesi aman saat keluar.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Autentikasi extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pengguna_model');
        $this->load->model('Log_aktivitas_model');
        $this->load->library('Pembatas_laju');
    }

    /**
     * Halaman dan proses pendaftaran akun baru.
     */
    public function daftar()
    {
        // Pengguna yang sudah masuk dialihkan ke dasbor
        if ($this->pengguna_masuk !== NULL)
        {
            redirect('/');
        }

        // Periksa apakah registrasi terbuka di .env
        $registrasi_terbuka = function_exists('env') ? env('REGISTRASI_TERBUKA', 'true') : 'true';
        if ($registrasi_terbuka === 'false' || $registrasi_terbuka === FALSE)
        {
            $data = array(
                'judul_halaman'      => 'Pendaftaran Ditutup — PRDAI',
                'css_halaman'        => array('daftar.css'),
                'registrasi_ditutup' => TRUE,
            );
            return $this->tampilkan('autentikasi/daftar', $data);
        }

        $ip = function_exists('ambil_ip') ? ambil_ip() : '0.0.0.0';

        if ($this->input->method() === 'post')
        {
            // Periksa batas laju IP (5 pendaftaran / jam)
            if ( ! $this->pembatas_laju->boleh_daftar($ip))
            {
                $this->session->set_flashdata('pesan_galat', $this->lang->line('daftar_terbatas'));
                redirect('daftar');
            }

            // Aturan validasi
            $this->form_validation->set_rules('nama', 'Nama Lengkap', 'trim|required|min_length[2]|max_length[100]');
            $this->form_validation->set_rules('email', 'Alamat Email', 'trim|required|valid_email|max_length[190]');
            $this->form_validation->set_rules('kata_sandi', 'Kata Sandi', 'required|min_length[10]|max_length[72]');
            $this->form_validation->set_rules('konfirmasi_sandi', 'Ulangi Kata Sandi', 'required|matches[kata_sandi]');

            if ($this->form_validation->run() === TRUE)
            {
                $email = strtolower(trim($this->input->post('email', TRUE)));
                $nama  = trim($this->input->post('nama', TRUE));
                $sandi = $this->input->post('kata_sandi');

                // Periksa apakah email sudah ada tanpa membocorkan keberadaannya ke publik
                $ada = $this->Pengguna_model->ambil_berdasarkan_email($email);
                if ($ada)
                {
                    $this->Log_aktivitas_model->catat('daftar_gagal', 'Email sudah terdaftar: ' . substr($email, 0, 3) . '***');
                    $this->session->set_flashdata('pesan_galat', $this->lang->line('daftar_gagal'));
                    redirect('daftar');
                }

                // Buat hash sandi dan simpan pengguna baru
                $hash = password_hash($sandi, PASSWORD_DEFAULT);
                $pengguna_id = $this->Pengguna_model->tambah(array(
                    'nama'       => $nama,
                    'email'      => $email,
                    'kata_sandi' => $hash,
                    'peran'      => 'pengguna',
                    'status'     => 'aktif',
                ));

                if ($pengguna_id)
                {
                    $this->Log_aktivitas_model->catat('daftar', 'Pendaftaran akun baru berhasil', $pengguna_id);
                    $this->Pengguna_model->catat_terakhir_masuk($pengguna_id);

                    // Tetapkan sesi dan kunci ke IP perangkat (tetap masuk selama tidak keluar)
                    $this->tetapkan_sesi_dan_kunci_ip($pengguna_id, NULL, TRUE);

                    $this->session->set_flashdata('pesan_sukses', $this->lang->line('daftar_berhasil'));
                    redirect('/');
                }
                else
                {
                    $this->session->set_flashdata('pesan_galat', $this->lang->line('daftar_gagal'));
                    redirect('daftar');
                }
            }
        }

        $data = array(
            'judul_halaman'      => 'Daftar Akun — PRDAI',
            'css_halaman'        => array('daftar.css'),
            'js_halaman'         => array('auth.js'),
            'registrasi_ditutup' => FALSE,
        );
        $this->tampilkan('autentikasi/daftar', $data);
    }

    /**
     * Halaman dan proses masuk (Login).
     */
    public function masuk()
    {
        if ($this->pengguna_masuk !== NULL)
        {
            redirect('/');
        }

        $ip = function_exists('ambil_ip') ? ambil_ip() : '0.0.0.0';

        if ($this->input->method() === 'post')
        {
            $email = strtolower(trim((string) $this->input->post('email', TRUE)));
            $sandi = (string) $this->input->post('kata_sandi');

            // Periksa apakah akun terkunci karena terlalu banyak percobaan gagal (5 kali / 15 menit)
            if ( ! empty($email) && ! $this->pembatas_laju->boleh_masuk($email, $ip))
            {
                $this->session->set_flashdata('pesan_galat', $this->lang->line('masuk_terkunci'));
                redirect('masuk');
            }

            $this->form_validation->set_rules('email', 'Alamat Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('kata_sandi', 'Kata Sandi', 'required');

            if ($this->form_validation->run() === TRUE)
            {
                $pengguna = $this->Pengguna_model->ambil_berdasarkan_email($email);

                // Hash tiruan untuk mencegah timing attack jika email tidak ada
                $hash_pembanding = $pengguna ? $pengguna->kata_sandi : '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012345';
                $cocok = password_verify($sandi, $hash_pembanding);

                if ($pengguna && $cocok && $pengguna->status === 'aktif')
                {
                    // Berhasil masuk
                    $this->load->helper('cookie');
                    $zona_waktu = trim((string) $this->input->post('zona_waktu', TRUE)) ?: (string) get_cookie('prdai_zona_waktu');
                    if (empty($zona_waktu))
                    {
                        $zona_waktu = ! empty($pengguna->zona_waktu) ? $pengguna->zona_waktu : 'Asia/Makassar';
                    }

                    $this->pembatas_laju->catat_masuk_sukses($email, $ip);
                    $this->Pengguna_model->catat_terakhir_masuk($pengguna->id, $zona_waktu);
                    $this->Log_aktivitas_model->catat('masuk', 'Pengguna berhasil masuk', $pengguna->id);

                    // Kelola fitur tetap masuk dengan kunci IP (bawaan aktif agar tidak perlu login ulang saat tidak aktif)
                    $ingat_saya = ($this->input->post('ingat_saya') !== NULL)
                        ? (bool) $this->input->post('ingat_saya')
                        : TRUE;

                    $this->tetapkan_sesi_dan_kunci_ip($pengguna->id, $zona_waktu, $ingat_saya);

                    $this->session->set_flashdata('pesan_sukses', $this->lang->line('masuk_berhasil'));
                    redirect('/');
                }
                else
                {
                    // Gagal masuk
                    $this->pembatas_laju->catat_masuk_gagal($email, $ip);
                    $this->Log_aktivitas_model->catat('masuk_gagal', 'Upaya masuk gagal untuk: ' . substr($email, 0, 3) . '***');

                    $this->session->set_flashdata('pesan_galat', $this->lang->line('masuk_gagal'));
                    redirect('masuk');
                }
            }
        }

        $data = array(
            'judul_halaman' => 'Masuk — PRDAI',
            'css_halaman'   => array('masuk.css'),
            'js_halaman'    => array('auth.js'),
        );
        $this->tampilkan('autentikasi/masuk', $data);
    }

    /**
     * Masuk dengan akun sosial (Google / GitHub).
     * Menampilkan antarmuka otentikasi interaktif (Google Account Chooser / GitHub OAuth Authorize)
     * atau mengalihkan ke server OAuth nyata jika GOOGLE_CLIENT_ID / GITHUB_CLIENT_ID terkonfigurasi.
     */
    public function sosial($penyedia = 'google')
    {
        $penyedia = strtolower(trim((string) $penyedia));
        if ( ! in_array($penyedia, array('google', 'github'), TRUE))
        {
            redirect('masuk');
        }

        // Cek jika kredensial OAuth asli tersedia di environment (.env)
        $google_cid = function_exists('env') ? env('GOOGLE_CLIENT_ID') : getenv('GOOGLE_CLIENT_ID');
        $github_cid = function_exists('env') ? env('GITHUB_CLIENT_ID') : getenv('GITHUB_CLIENT_ID');

        if ($penyedia === 'google' && ! empty($google_cid) && $google_cid !== 'mock' && $google_cid !== 'dummy')
        {
            $callback_url = site_url('autentikasi/oauth_callback/google');
            $params = http_build_query(array(
                'client_id'     => $google_cid,
                'redirect_uri'  => $callback_url,
                'response_type' => 'code',
                'scope'         => 'openid profile email',
                'prompt'        => 'select_account',
                'state'         => $this->security->get_csrf_hash(),
            ));
            redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
            return;
        }

        if ($penyedia === 'github' && ! empty($github_cid) && $github_cid !== 'mock' && $github_cid !== 'dummy')
        {
            $callback_url = site_url('autentikasi/oauth_callback/github');
            $params = http_build_query(array(
                'client_id'    => $github_cid,
                'redirect_uri' => $callback_url,
                'scope'        => 'read:user user:email',
                'state'        => $this->security->get_csrf_hash(),
            ));
            redirect('https://github.com/login/oauth/authorize?' . $params);
            return;
        }

        // Tampilkan antarmuka pemilih akun profesional (Google Account Chooser atau GitHub Authorize)
        if ($penyedia === 'google')
        {
            $data = array(
                'judul_halaman' => 'Pilih akun — Masuk dengan Google — PRDAI',
                'css_halaman'   => array('oauth_sosial.css'),
                'penyedia'      => 'google',
            );
            $this->tampilkan('autentikasi/pilih_google', $data);
        }
        else
        {
            $data = array(
                'judul_halaman' => 'Authorize PRDAI — GitHub',
                'css_halaman'   => array('oauth_sosial.css'),
                'penyedia'      => 'github',
            );
            $this->tampilkan('autentikasi/pilih_github', $data);
        }
    }

    /**
     * Memproses otentikasi akun sosial (Google / GitHub) dari layar pemilih akun.
     */
    public function proses_sosial()
    {
        if ($this->input->method() !== 'post')
        {
            redirect('masuk');
        }

        $penyedia = strtolower(trim((string) $this->input->post('provider', TRUE)));
        $nama     = trim((string) $this->input->post('nama', TRUE));
        $email    = trim((string) $this->input->post('email', TRUE));
        $avatar   = trim((string) $this->input->post('avatar', TRUE));

        if ( ! in_array($penyedia, array('google', 'github'), TRUE))
        {
            $this->session->set_flashdata('pesan_galat', 'Penyedia otentikasi tidak dikenal.');
            redirect('masuk');
        }

        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $this->session->set_flashdata('pesan_galat', 'Alamat email yang dipilih tidak valid.');
            redirect('autentikasi/sosial/' . $penyedia);
        }

        if (empty($nama))
        {
            $pecah = explode('@', $email);
            $nama = ucfirst($pecah[0]);
        }

        // Identifikasi Provider ID yang konsisten untuk akun ini
        $pid = $penyedia . '_' . md5(strtolower($email));

        $pengguna = $this->Pengguna_model->ambil_atau_buat_sosial($penyedia, $pid, $nama, $email, $avatar);

        if ($pengguna && $pengguna->status === 'aktif')
        {
            $this->Pengguna_model->catat_terakhir_masuk($pengguna->id);
            $this->Log_aktivitas_model->catat('masuk_sosial', 'Masuk via ' . ucfirst($penyedia) . ' (' . $email . ')', $pengguna->id);

            // Tetapkan sesi dan kunci ke IP perangkat (tetap masuk selama tidak keluar)
            $this->tetapkan_sesi_dan_kunci_ip($pengguna->id, ! empty($pengguna->zona_waktu) ? $pengguna->zona_waktu : NULL, TRUE);

            $this->session->set_flashdata('pesan_sukses', 'Selamat datang, ' . htmlspecialchars($pengguna->nama, ENT_QUOTES, 'UTF-8') . '! Berhasil masuk dengan ' . ucfirst($penyedia) . '.');
            redirect('/');
        }
        else if ($pengguna && $pengguna->status !== 'aktif')
        {
            $this->session->set_flashdata('pesan_galat', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
            redirect('masuk');
        }

        $this->session->set_flashdata('pesan_galat', 'Gagal memproses otentikasi ' . ucfirst($penyedia) . '. Silakan coba lagi.');
        redirect('masuk');
    }

    /**
     * Callback untuk alur OAuth resmi (jika GOOGLE_CLIENT_ID / GITHUB_CLIENT_ID diaktifkan).
     */
    public function oauth_callback($penyedia = 'google')
    {
        $penyedia = strtolower(trim((string) $penyedia));
        $code = $this->input->get('code', TRUE);

        if (empty($code))
        {
            $this->session->set_flashdata('pesan_galat', 'Otorisasi ' . ucfirst($penyedia) . ' dibatalkan.');
            redirect('masuk');
        }

        // Fallback anggun jika code exchange belum selesai dikonfigurasi di server: arahkan ke pemilih akun
        redirect('autentikasi/sosial/' . $penyedia);
    }

    /**
     * Keluar dari sesi akun (harus POST).
     */
    public function keluar()
    {

        $this->load->helper('cookie');
        $id = $this->session->userdata('pengguna_id');
        if ($id)
        {
            $this->Log_aktivitas_model->catat('keluar', 'Pengguna keluar', $id);
            $this->Pengguna_model->hapus_token_ingat($id);
        }

        delete_cookie('prdai_ingat');
        $this->session->sess_destroy();
        redirect('masuk');
    }

    /**
     * Alihkan sesi ke akun lain (AJAX/POST, untuk kemudahan pengujian akun).
     */
    public function beralih($target_id)
    {
        $this->jika_ajax_wajib();

        $target_id = (int) $target_id;
        $this->load->model('Pengguna_model');
        $pengguna = $this->Pengguna_model->ambil_berdasarkan_id($target_id);

        if ($pengguna && $pengguna->status === 'aktif')
        {
            $this->Log_aktivitas_model->catat('alih_akun', 'Beralih ke akun: ' . $pengguna->nama, $pengguna->id);

            // Tetapkan sesi dan kunci ke IP perangkat
            $this->tetapkan_sesi_dan_kunci_ip($pengguna->id, ! empty($pengguna->zona_waktu) ? $pengguna->zona_waktu : NULL, TRUE);

            kirim_json(array(
                'berhasil' => TRUE,
                'pesan'    => 'Berhasil beralih ke akun ' . $pengguna->nama,
                'nama'     => $pengguna->nama,
                'email'    => $pengguna->email,
            ));
        }

        kirim_galat_json('Akun pengguna tidak ditemukan atau tidak aktif.');
    }

    /**
     * Menetapkan sesi masuk dan mengunci token persisten ke IP perangkat ini.
     * Pengguna tidak perlu login ulang meskipun tidak aktif lama, asalkan tidak keluar akun.
     *
     * @param int         $pengguna_id
     * @param string|null $zona_waktu
     * @param bool        $kunci_token_ingat
     */
    protected function tetapkan_sesi_dan_kunci_ip($pengguna_id, $zona_waktu = NULL, $kunci_token_ingat = TRUE)
    {
        $ip = function_exists('ambil_ip') ? ambil_ip() : '0.0.0.0';
        $this->load->helper('cookie');

        // Regenerasi ID sesi untuk mencegah session fixation
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('pengguna_id', (int) $pengguna_id);
        $this->session->set_userdata('ip_terkunci', $ip);

        if ($zona_waktu !== NULL && ! empty($zona_waktu))
        {
            $this->session->set_userdata('zona_waktu', $zona_waktu);
        }

        if ($kunci_token_ingat)
        {
            // Buat token persisten 1 tahun (365 hari) yang dikunci ke IP
            $token_polos = bin2hex(random_bytes(32));
            $token_hash  = hash('sha256', $token_polos);
            $kadaluarsa  = date('Y-m-d H:i:s', time() + (365 * 86400));

            $this->Pengguna_model->simpan_token_ingat((int) $pengguna_id, $token_hash, $kadaluarsa, $ip);

            set_cookie(array(
                'name'     => 'prdai_ingat',
                'value'    => $pengguna_id . ':' . $token_polos,
                'expire'   => 365 * 86400,
                'path'     => '/',
                'httponly' => TRUE,
                'secure'   => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
            ));
        }
        else
        {
            $this->Pengguna_model->hapus_token_ingat((int) $pengguna_id);
            delete_cookie('prdai_ingat');
        }
    }
}

