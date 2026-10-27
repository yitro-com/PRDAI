<?php
/**
 * PRDAI — Model penggunaan AI.
 *
 * Mencatat pemakaian token dan status keberhasilan penyusunan PRD
 * untuk audit biaya dan pemantauan performa model AI.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Penggunaan_ai_model extends CI_Model
{
    protected $tabel = 'penggunaan_ai';

    /**
     * Catat riwayat pemanggilan API AI.
     */
    public function catat($pengguna_id, $prd_id, $model, $token_masuk = 0, $token_keluar = 0, $status = 'sukses')
    {
        return $this->db->insert($this->tabel, array(
            'pengguna_id'  => (int) $pengguna_id,
            'prd_id'       => $prd_id ? (int) $prd_id : NULL,
            'model'        => substr($model, 0, 80),
            'token_masuk'  => (int) $token_masuk,
            'token_keluar' => (int) $token_keluar,
            'status'       => ($status === 'sukses') ? 'sukses' : 'gagal',
            'dibuat_pada'  => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Ambil ringkasan statistik penggunaan token AI seorang pengguna.
     *
     * @param int $pengguna_id
     * @return array
     */
    public function ambil_ringkasan_pengguna($pengguna_id)
    {
        $id = (int) $pengguna_id;

        $ringkasan = $this->db
            ->select('COALESCE(SUM(token_masuk + token_keluar), 0) AS total_token, COALESCE(MAX(token_masuk + token_keluar), 0) AS token_puncak, COUNT(DISTINCT DATE(dibuat_pada)) AS total_hari_aktif', FALSE)
            ->where('pengguna_id', $id)
            ->get($this->tabel)
            ->row();

        // Ambil daftar tanggal aktif urut menaik untuk perhitungan rekor hari beruntun
        $baris_tanggal = $this->db
            ->select('DISTINCT DATE(dibuat_pada) AS tgl', FALSE)
            ->where('pengguna_id', $id)
            ->order_by('tgl', 'ASC')
            ->get($this->tabel)
            ->result();

        $daftar_tgl = array();
        foreach ($baris_tanggal as $b)
        {
            $daftar_tgl[] = $b->tgl;
        }

        $rekor_terlama = 0;
        $rentetan_sekarang = 0;
        $panjang_sementara = 0;
        $tgl_sebelumnya = NULL;

        foreach ($daftar_tgl as $tgl)
        {
            if ($tgl_sebelumnya === NULL)
            {
                $panjang_sementara = 1;
            }
            else
            {
                $selisih_hari = (strtotime($tgl) - strtotime($tgl_sebelumnya)) / 86400;
                if ($selisih_hari == 1)
                {
                    $panjang_sementara++;
                }
                else
                {
                    $panjang_sementara = 1;
                }
            }

            if ($panjang_sementara > $rekor_terlama)
            {
                $rekor_terlama = $panjang_sementara;
            }
            $tgl_sebelumnya = $tgl;
        }

        // Hitung hari beruntun saat ini
        $hari_ini = date('Y-m-d');
        $kemarin = date('Y-m-d', strtotime('-1 day'));

        if (in_array($hari_ini, $daftar_tgl, TRUE) || in_array($kemarin, $daftar_tgl, TRUE))
        {
            $rentetan_sekarang = $panjang_sementara;
        }

        return array(
            'total_token'             => (int) ($ringkasan ? $ringkasan->total_token : 0),
            'token_puncak'            => (int) ($ringkasan ? $ringkasan->token_puncak : 0),
            'total_hari_aktif'        => (int) ($ringkasan ? $ringkasan->total_hari_aktif : 0),
            'rekor_terlama'           => (int) $rekor_terlama,
            'hari_aktif_beruntun'     => (int) $rentetan_sekarang,
        );
    }

    /**
     * Ambil data aktivitas harian token untuk visualisasi heatmap (Aktivitas Token).
     *
     * @param int $pengguna_id
     * @param int $jumlah_hari
     * @return array
     */
    public function ambil_aktivitas_harian($pengguna_id, $jumlah_hari = 365)
    {
        $id = (int) $pengguna_id;
        $tgl_mulai = date('Y-m-d', strtotime("-{$jumlah_hari} days"));

        $hasil = $this->db
            ->select('DATE(dibuat_pada) AS tanggal, SUM(token_masuk + token_keluar) AS total_token, COUNT(*) AS jumlah_aksi', FALSE)
            ->where('pengguna_id', $id)
            ->where('DATE(dibuat_pada) >=', $tgl_mulai)
            ->group_by('DATE(dibuat_pada)')
            ->order_by('tanggal', 'ASC')
            ->get($this->tabel)
            ->result();

        $peta = array();
        foreach ($hasil as $baris)
        {
            $peta[$baris->tanggal] = array(
                'total_token'  => (int) $baris->total_token,
                'jumlah_aksi'  => (int) $baris->jumlah_aksi,
            );
        }

        return $peta;
    }

    /**
     * Susun matriks kalender 52 minggu untuk heatmap aktivitas token.
     *
     * @param array $aktivitas_harian
     * @return array
     */
    public function susun_grid_aktivitas($aktivitas_harian)
    {
        $nama_bulan_id = array(
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        );

        $sekarang = time();
        $hari_ini = date('Y-m-d', $sekarang);

        // Akhir grid: Sabtu dari minggu aktif agar tersusun 7 baris penuh
        $hari_dalam_minggu = (int) date('w', $sekarang);
        $tgl_akhir = strtotime("+ " . (6 - $hari_dalam_minggu) . " days", $sekarang);
        $tgl_mulai = strtotime("- 363 days", $tgl_akhir);

        $minggu_list = array();
        $bulan_header = array();
        $bulan_terakhir = '';

        $kursor = $tgl_mulai;
        for ($w = 0; $w < 52; $w++)
        {
            $kolom_hari = array();
            for ($d = 0; $d < 7; $d++)
            {
                $tgl_str = date('Y-m-d', $kursor);
                $bln_str = date('m', $kursor);
                $label_bln = isset($nama_bulan_id[$bln_str]) ? $nama_bulan_id[$bln_str] : date('M', $kursor);

                if ($d === 0 && $label_bln !== $bulan_terakhir)
                {
                    $bulan_header[$w] = $label_bln;
                    $bulan_terakhir = $label_bln;
                }

                $token_hari = 0;
                $aksi_hari = 0;
                if (isset($aktivitas_harian[$tgl_str]) && is_array($aktivitas_harian[$tgl_str]))
                {
                    if (isset($aktivitas_harian[$tgl_str]['total_token']))
                    {
                        $token_hari = (int) $aktivitas_harian[$tgl_str]['total_token'];
                    }
                    elseif (isset($aktivitas_harian[$tgl_str]['token']))
                    {
                        $token_hari = (int) $aktivitas_harian[$tgl_str]['token'];
                    }

                    if (isset($aktivitas_harian[$tgl_str]['jumlah_aksi']))
                    {
                        $aksi_hari = (int) $aktivitas_harian[$tgl_str]['jumlah_aksi'];
                    }
                    elseif (isset($aktivitas_harian[$tgl_str]['aksi']))
                    {
                        $aksi_hari = (int) $aktivitas_harian[$tgl_str]['aksi'];
                    }
                }
                $ada_aktivitas = ($token_hari > 0);

                $level = 0;
                if ($token_hari > 0 && $token_hari <= 2000) $level = 1;
                elseif ($token_hari > 2000 && $token_hari <= 5000) $level = 2;
                elseif ($token_hari > 5000 && $token_hari <= 10000) $level = 3;
                elseif ($token_hari > 10000) $level = 4;

                $kolom_hari[] = array(
                    'tanggal'        => $tgl_str,
                    'label_tanggal'  => date('j', $kursor) . ' ' . $label_bln . ' ' . date('Y', $kursor),
                    'total_token'    => $token_hari,
                    'jumlah_aksi'    => $aksi_hari,
                    'ada_aktivitas'  => $ada_aktivitas,
                    'level'          => $level,
                    'lewat_hari_ini' => ($tgl_str > $hari_ini),
                );

                $kursor = strtotime('+1 day', $kursor);
            }
            $minggu_list[] = $kolom_hari;
        }

        return array(
            'minggu'       => $minggu_list,
            'bulan_header' => $bulan_header,
        );
    }

    /**
     * Ambil data asli aktivitas token akun dari basis data untuk 3 mode:
     * 1. Harian: 45 hari terakhir riil dari tabel penggunaan_ai (tanpa dummy).
     * 2. Mingguan: 12 bulan terakhir riil (Nov s/d Okt) dibagi per minggu M1..M4.
     * 3. Bulanan: Heatmap dot-matrix riil per tanggal (menggantikan Kumulatif).
     *
     * @param int $pengguna_id
     * @return array
     */
    public function ambil_data_aktivitas_token($pengguna_id)
    {
        $id = (int) $pengguna_id;

        // Ambil SEMUA rekaman riil penggunaan AI akun ini dari basis data
        $aktivitas_db = $this->db
            ->select('DATE(dibuat_pada) AS tgl, SUM(token_masuk + token_keluar) AS total_token, COUNT(*) AS jumlah_aksi', FALSE)
            ->where('pengguna_id', $id)
            ->group_by('DATE(dibuat_pada)')
            ->order_by('tgl', 'ASC')
            ->get($this->tabel)
            ->result();

        $peta_harian = array();
        $puncak_harian = 0;
        foreach ($aktivitas_db as $b)
        {
            $tok = (int) $b->total_token;
            $aks = (int) $b->jumlah_aksi;
            $peta_harian[$b->tgl] = array(
                'token'       => $tok,
                'aksi'        => $aks,
                'total_token' => $tok,
                'jumlah_aksi' => $aks,
            );
            if ($tok > $puncak_harian)
            {
                $puncak_harian = $tok;
            }
        }
        if ($puncak_harian <= 0)
        {
            $puncak_harian = 5000;
        }

        $nama_bulan_id = array(
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        );

        $sekarang = time();
        $hari_ini = date('Y-m-d', $sekarang);

        // =========================================================================
        // 1. DATA ASLI HARIAN (45 Hari Terakhir sampai Hari Ini)
        // =========================================================================
        $total_hari = 45;
        $tgl_awal_harian = strtotime('-' . ($total_hari - 1) . ' days', $sekarang);
        $harian = array();

        for ($i = 0; $i < $total_hari; $i++)
        {
            $cur_time = strtotime("+{$i} days", $tgl_awal_harian);
            $tgl_str = date('Y-m-d', $cur_time);
            $hari_angka = date('d', $cur_time);
            $bln_angka = date('m', $cur_time);
            $bln_str = isset($nama_bulan_id[$bln_angka]) ? $nama_bulan_id[$bln_angka] : date('M', $cur_time);
            $tgl_format = date('j', $cur_time) . ' ' . $bln_str . ' ' . date('Y', $cur_time);

            $token_hari = isset($peta_harian[$tgl_str]) ? $peta_harian[$tgl_str]['token'] : 0;
            $aksi_hari  = isset($peta_harian[$tgl_str]) ? $peta_harian[$tgl_str]['aksi'] : 0;
            $ada_token  = ($token_hari > 0);

            // Tinggi batang asli proporsional terhadap penggunaan puncak akun ini
            if ($ada_token)
            {
                $rasio = $token_hari / $puncak_harian;
                $tinggi = min(96, max(22, round($rasio * 96)));
            }
            else
            {
                $tinggi = 8; // Baseline flat netral jika 0
            }

            // Ticks sumbu X asli: tanggal 1 (dengan nama bulan), kelipatan 5, atau hari ini
            $tick = NULL;
            if ($hari_angka === '01')
            {
                $tick = array('angka' => '01', 'sub' => $bln_str);
            }
            elseif (in_array($hari_angka, array('05', '10', '15', '20', '25', '30'), TRUE))
            {
                $sub = ($hari_angka === '15' && $i >= 15 && $i <= 28) ? $bln_str : '';
                $tick = array('angka' => $hari_angka, 'sub' => $sub);
            }
            elseif ($i === ($total_hari - 1))
            {
                $tick = array('angka' => $hari_angka, 'sub' => $bln_str);
            }

            $harian[] = array(
                'indeks'  => $i,
                'tanggal' => $tgl_str,
                'tgl'     => $tgl_format,
                'token'   => $token_hari,
                'aksi'    => $aksi_hari,
                'tinggi'  => $tinggi,
                'hijau'   => $ada_token,
                'tick'    => $tick,
            );
        }

        // =========================================================================
        // 2. DATA ASLI MINGGUAN (12 Bulan Rolling Terakhir, e.g. Nov s/d Okt)
        // =========================================================================
        $daftar_bulan_rolling = array();
        for ($m = 11; $m >= 0; $m--)
        {
            $time_bln = strtotime("-{$m} months", $sekarang);
            $bln_key = date('Y-m', $time_bln);
            $bln_num = date('m', $time_bln);
            $bln_label = isset($nama_bulan_id[$bln_num]) ? $nama_bulan_id[$bln_num] : date('M', $time_bln);
            $daftar_bulan_rolling[$bln_key] = array(
                'label'         => $bln_label,
                'tahun'         => date('Y', $time_bln),
                'bulan'         => $bln_num,
                'days_in_month' => (int) date('t', $time_bln),
            );
        }

        // Hitung token mingguan riil untuk tiap minggu (M1: 1-7, M2: 8-14, M3: 15-21, M4: 22-akhir)
        $puncak_mingguan = 0;
        $mingguan_raw = array();
        foreach ($daftar_bulan_rolling as $bln_key => $binfo)
        {
            $thn = $binfo['tahun'];
            $bln = $binfo['bulan'];
            $dim = $binfo['days_in_month'];

            $minggu_def = array(
                'M1' => array(1, 7),
                'M2' => array(8, 14),
                'M3' => array(15, 21),
                'M4' => array(22, $dim),
            );

            $minggu_list = array();
            foreach ($minggu_def as $wlabel => $range)
            {
                $w_token = 0;
                $w_aksi = 0;
                for ($d = $range[0]; $d <= $range[1]; $d++)
                {
                    $cur_t = sprintf('%s-%s-%02d', $thn, $bln, $d);
                    if (isset($peta_harian[$cur_t]))
                    {
                        $w_token += $peta_harian[$cur_t]['token'];
                        $w_aksi  += $peta_harian[$cur_t]['aksi'];
                    }
                }

                if ($w_token > $puncak_mingguan)
                {
                    $puncak_mingguan = $w_token;
                }

                $minggu_list[$wlabel] = array(
                    'label' => $wlabel,
                    'token' => $w_token,
                    'aksi'  => $w_aksi,
                );
            }
            $mingguan_raw[$binfo['label']] = $minggu_list;
        }
        if ($puncak_mingguan <= 0)
        {
            $puncak_mingguan = 10000;
        }

        $mingguan = array();
        foreach ($daftar_bulan_rolling as $bln_key => $binfo)
        {
            $bln_label = $binfo['label'];
            $w_items = $mingguan_raw[$bln_label];
            $formatted_weeks = array();

            foreach ($w_items as $wlabel => $winfo)
            {
                $ada_w = ($winfo['token'] > 0);
                $tinggi = $ada_w ? min(95, max(24, round(($winfo['token'] / $puncak_mingguan) * 95))) : 12;

                $formatted_weeks[] = array(
                    'label'  => $wlabel,
                    'token'  => $winfo['token'],
                    'aksi'   => $winfo['aksi'],
                    'tinggi' => $tinggi,
                    'hijau'  => $ada_w,
                );
            }

            $mingguan[$bln_label] = array(
                'minggu' => $formatted_weeks,
            );
        }

        // =========================================================================
        // 3. DATA ASLI BULANAN (Dot Matrix Heatmap Riil per Tanggal)
        // =========================================================================
        $bulanan = array();
        foreach ($daftar_bulan_rolling as $bln_key => $binfo)
        {
            $thn = $binfo['tahun'];
            $bln = $binfo['bulan'];
            $dim = $binfo['days_in_month'];
            $bln_label = $binfo['label'];

            $bln_kolom_list = array();
            $total_token_bln = 0;
            $total_aksi_bln  = 0;

            // Bagi hari dalam bulan menjadi kolom-kolom 7 hari (maks 4 atau 5 kolom)
            $jml_kolom = (int) ceil($dim / 7);
            for ($c = 0; $c < $jml_kolom; $c++)
            {
                $titik_list = array();
                for ($r = 0; $r < 7; $r++)
                {
                    $day_num = ($c * 7) + $r + 1;
                    if ($day_num <= $dim)
                    {
                        $tgl_str = sprintf('%s-%s-%02d', $thn, $bln, $day_num);
                        $tok = isset($peta_harian[$tgl_str]) ? $peta_harian[$tgl_str]['token'] : 0;
                        $aks = isset($peta_harian[$tgl_str]) ? $peta_harian[$tgl_str]['aksi'] : 0;
                        $is_aktif = ($tok > 0);

                        if ($is_aktif)
                        {
                            $total_token_bln += $tok;
                            $total_aksi_bln  += $aks;
                        }

                        $titik_list[] = array(
                            'baris'    => $r,
                            'is_aktif' => $is_aktif,
                            'token'    => $tok,
                            'aksi'     => $aks,
                            'label'    => "{$day_num} {$bln_label} {$thn}",
                        );
                    }
                    else
                    {
                        $titik_list[] = array(
                            'baris'    => $r,
                            'is_aktif' => FALSE,
                            'token'    => 0,
                            'aksi'     => 0,
                            'label'    => '',
                        );
                    }
                }
                $bln_kolom_list[] = $titik_list;
            }

            $bulanan[$bln_label] = array(
                'kolom_list'  => $bln_kolom_list,
                'total_token' => $total_token_bln,
                'total_aksi'  => $total_aksi_bln,
            );
        }

        return array(
            'harian'         => $harian,
            'mingguan'       => $mingguan,
            'bulanan'        => $bulanan,
            'grid_aktivitas' => $this->susun_grid_aktivitas($peta_harian),
        );
    }
}
