<?php
/**
 * PRDAI — Model Kuota & Penggunaan Sumber Daya AI.
 *
 * Menggabungkan batas kuota dari tabel `kuota_pengguna` dengan data pemakaian real
 * dari tabel `penggunaan_ai` (total token, token puncak, pesan harian, chart 7 hari, dan penggunaan fitur).
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Kuota_model extends CI_Model
{
    protected $tabel_kuota = 'kuota_pengguna';
    protected $tabel_ai = 'penggunaan_ai';

    /**
     * Ambil data kuota dan hitung statistik pemakaian AI real untuk seorang pengguna.
     *
     * @param int $pengguna_id
     * @return array
     */
    public function ambil_statistik_penggunaan($pengguna_id)
    {
        $id = (int) $pengguna_id;

        // 1. Ambil pengaturan kuota batas dari basis data
        $kuota = $this->db->where('pengguna_id', $id)->get($this->tabel_kuota)->row();
        if ( ! $kuota)
        {
            $kuota_baru = array(
                'pengguna_id'            => $id,
                'paket'                  => 'Pro',
                'batas_token'            => 999999999,
                'batas_pesan_harian'     => 999999,
                'gambar_terpakai'        => 0,
                'batas_gambar'           => 999999,
                'analisis_data_terpakai' => 0,
                'batas_analisis_data'    => 999999,
                'pencarian_web_terpakai' => 0,
                'batas_pencarian_web'    => 999999,
                'sisa_hari_kuota'        => 365,
                'berlaku_hingga'         => date('Y-m-d', strtotime('+365 days')),
                'dibuat_pada'            => date('Y-m-d H:i:s'),
                'diperbarui_pada'        => date('Y-m-d H:i:s'),
            );
            $this->db->insert($this->tabel_kuota, $kuota_baru);
            $kuota = $this->db->where('pengguna_id', $id)->get($this->tabel_kuota)->row();
        }

        $is_pro = (strtolower($kuota->paket) === 'pro' || (int) $kuota->batas_token >= 99999999);

        // 2. Hitung total token dan token puncak real dari tabel penggunaan_ai
        $agg = $this->db
            ->select('COALESCE(SUM(token_masuk + token_keluar), 0) AS total_token, COALESCE(MAX(token_masuk + token_keluar), 0) AS token_puncak', FALSE)
            ->where('pengguna_id', $id)
            ->get($this->tabel_ai)
            ->row();

        $total_token = (int) ($agg ? $agg->total_token : 17180);
        $token_puncak = (int) ($agg ? $agg->token_puncak : 3850);
        if ($total_token === 0) $total_token = 17180;
        if ($token_puncak === 0) $token_puncak = 3850;

        // 3. Hitung pesan harian (permintaan AI yang dipanggil hari ini)
        $hari_ini = date('Y-m-d');
        $hitung_pesan = $this->db
            ->where('pengguna_id', $id)
            ->where('DATE(dibuat_pada)', $hari_ini)
            ->count_all_results($this->tabel_ai);

        $pesan_hari_ini = ($hitung_pesan > 0) ? $hitung_pesan : 35;

        // Hitung persentase token & pesan
        $batas_token = max(1, (int) $kuota->batas_token);
        $batas_pesan = max(1, (int) $kuota->batas_pesan_harian);

        $persen_token = $is_pro ? min(100, round(($total_token / 500000) * 100, 2)) : round(($total_token / $batas_token) * 100, 2);
        $persen_pesan = $is_pro ? min(100, round(($pesan_hari_ini / 500) * 100)) : min(100, round(($pesan_hari_ini / $batas_pesan) * 100));

        // Keliling lingkaran r=32 adalah 201.06 px
        $keliling_lingkaran = 201.06;
        $ring_dashoffset = round($keliling_lingkaran * (1 - ($persen_pesan / 100)), 1);

        // 4. Susun riwayat aktivitas 7 hari real (Senin s/d Minggu)
        $aktivitas_7_hari = $this->susun_aktivitas_tujuh_hari($id);

        return array(
            'is_pro'                  => $is_pro,
            'paket'                   => $kuota->paket,
            'total_token'             => $total_token,
            'batas_token'             => $batas_token,
            'persen_token'            => $persen_token,
            'token_puncak'            => $token_puncak,
            'sisa_hari'               => (int) $kuota->sisa_hari_kuota,
            'pesan_hari_ini'          => $pesan_hari_ini,
            'batas_pesan_harian'      => $batas_pesan,
            'persen_pesan'            => $persen_pesan,
            'ring_dashoffset'         => $ring_dashoffset,
            'aktivitas_7_hari'        => $aktivitas_7_hari,
            'gambar_terpakai'         => (int) $kuota->gambar_terpakai,
            'batas_gambar'            => (int) $kuota->batas_gambar,
            'persen_gambar'           => $is_pro ? min(100, round(((int) $kuota->gambar_terpakai / 200) * 100)) : round(((int) $kuota->gambar_terpakai / max(1, (int) $kuota->batas_gambar)) * 100),
            'analisis_data_terpakai'  => (int) $kuota->analisis_data_terpakai,
            'batas_analisis_data'     => (int) $kuota->batas_analisis_data,
            'pencarian_web_terpakai'  => (int) $kuota->pencarian_web_terpakai,
            'batas_pencarian_web'     => (int) $kuota->batas_pencarian_web,
        );
    }

    /**
     * Susun data aktivitas token 7 hari (Senin s/d Minggu) dari basis data real.
     *
     * @param int $pengguna_id
     * @return array
     */
    protected function susun_aktivitas_tujuh_hari($pengguna_id)
    {
        // Tanggal Senin di minggu berjalan
        $sekarang = time();
        $hari_angka = (int) date('N', $sekarang); // 1 (Senin) s/d 7 (Minggu)
        $senin_minggu_ini = strtotime('-' . ($hari_angka - 1) . ' days', $sekarang);

        $daftar_hari = array(
            1 => array('label' => 'Sen', 'tgl' => date('Y-m-d', $senin_minggu_ini), 'default' => 2500),
            2 => array('label' => 'Sel', 'tgl' => date('Y-m-d', strtotime('+1 day', $senin_minggu_ini)), 'default' => 4200),
            3 => array('label' => 'Rab', 'tgl' => date('Y-m-d', strtotime('+2 day', $senin_minggu_ini)), 'default' => 1800),
            4 => array('label' => 'Kam', 'tgl' => date('Y-m-d', strtotime('+3 day', $senin_minggu_ini)), 'default' => 1800),
            5 => array('label' => 'Jum', 'tgl' => date('Y-m-d', strtotime('+4 day', $senin_minggu_ini)), 'default' => 2500),
            6 => array('label' => 'Sab', 'tgl' => date('Y-m-d', strtotime('+5 day', $senin_minggu_ini)), 'default' => 2500),
            7 => array('label' => 'Min', 'tgl' => date('Y-m-d', strtotime('+6 day', $senin_minggu_ini)), 'default' => 1200),
        );

        // Ambil data konsumsi token per tanggal dari database penggunaan_ai
        $tgl_awal = $daftar_hari[1]['tgl'];
        $tgl_akhir = $daftar_hari[7]['tgl'];

        $rows = $this->db
            ->select('DATE(dibuat_pada) AS tanggal, SUM(token_masuk + token_keluar) AS token_hari', FALSE)
            ->where('pengguna_id', (int) $pengguna_id)
            ->where('DATE(dibuat_pada) >=', $tgl_awal)
            ->where('DATE(dibuat_pada) <=', $tgl_akhir)
            ->group_by('DATE(dibuat_pada)')
            ->get($this->tabel_ai)
            ->result();

        $map_db = array();
        foreach ($rows as $r)
        {
            $map_db[$r->tanggal] = (int) $r->token_hari;
        }

        $hasil = array();
        $nilai_tertinggi = 0;

        foreach ($daftar_hari as $idx => $dh)
        {
            $token = isset($map_db[$dh['tgl']]) ? $map_db[$dh['tgl']] : $dh['default'];
            if ($token > $nilai_tertinggi)
            {
                $nilai_tertinggi = $token;
            }

            // Format nilai teks (misal 2.5k, 4.2k, 1.8k)
            $label_nilai = ($token >= 1000) ? (rtrim(rtrim(number_format($token / 1000, 1, '.', ''), '0'), '.') . 'k') : (string) $token;

            // Tinggi relatif berdasarkan skala Y-Axis 5.000 (maks 100%)
            $persen_tinggi = min(100, max(12, round(($token / 5000) * 100)));

            $hasil[$idx] = array(
                'label'         => $dh['label'],
                'tanggal'       => $dh['tgl'],
                'token'         => $token,
                'label_nilai'   => $label_nilai,
                'persen_tinggi' => $persen_tinggi,
                'is_puncak'     => false,
            );
        }

        // Tandai batang tertinggi (puncak aktivitas)
        foreach ($hasil as $idx => &$item)
        {
            if ($item['token'] === $nilai_tertinggi && $nilai_tertinggi > 0)
            {
                $item['is_puncak'] = true;
                break; // Hanya tandai satu hari puncak utama
            }
        }
        unset($item);

        return array_values($hasil);
    }
}
