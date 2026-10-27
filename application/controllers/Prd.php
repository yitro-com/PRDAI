<?php
/**
 * PRDAI — Pengontrol pengelolaan dokumen PRD.
 *
 * Mengelola pembuatan lewat wizard terpandu, pemanggilan AI berbatas laju,
 * tampilan render Markdown aman, penyuntingan dan versioning, serta ekspor file.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Prd extends Pengontrol_Masuk
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Prd_model');
        $this->load->model('Prd_versi_model');
        $this->load->model('Penggunaan_ai_model');
        $this->load->model('Log_aktivitas_model');
        $this->load->library('Pembatas_laju');
        $this->load->library('Pemroses_markdown');
        $this->load->library('Penyusun_prompt');
        $this->load->library('Klien_ai');

        // Pastikan kedua variasi (huruf kapital dan huruf kecil) selalu merujuk ke objek aktif
        if (isset($this->Prd_model)) { $this->prd_model =& $this->Prd_model; }
        if (isset($this->Prd_versi_model)) { $this->prd_versi_model =& $this->Prd_versi_model; }
        if (isset($this->Penggunaan_ai_model)) { $this->penggunaan_ai_model =& $this->Penggunaan_ai_model; }
        if (isset($this->Log_aktivitas_model)) { $this->log_aktivitas_model =& $this->Log_aktivitas_model; }

        if (isset($this->pembatas_laju)) { $this->Pembatas_laju =& $this->pembatas_laju; }
        if (isset($this->pemroses_markdown)) { $this->Pemroses_markdown =& $this->pemroses_markdown; }
        if (isset($this->penyusun_prompt)) { $this->Penyusun_prompt =& $this->penyusun_prompt; }
        if (isset($this->klien_ai)) { $this->Klien_ai =& $this->klien_ai; }
    }

    /**
     * Alihkan formulir wizard ke antarmuka terpadu satu layar.
     */
    public function buat()
    {
        redirect('/');
    }


    /**
     * Endpoint AJAX untuk memvalidasi input wizard dan menyusun PRD lewat AI.
     */
    public function hasilkan()
    {
        $this->jika_ajax_wajib();
        set_time_limit(150);

        $pengguna_id = $this->pengguna_masuk->id;

        // Cek apakah masukan berupa prompt ringkas sekali isi (ide_produk) atau lampiran dokumen
        $ide_produk      = trim((string) $this->input->post('ide_produk', TRUE));
        $lampiran_nama   = trim((string) $this->input->post('lampiran_nama', TRUE));
        $lampiran_konten = (string) $this->input->post('lampiran_konten');
        $lampiran_tipe   = trim((string) $this->input->post('lampiran_tipe', TRUE));
        $kode_aktif      = trim((string) $this->input->post('kode_aktif', TRUE));

        // Data waktu & zona waktu pengguna dari peramban (mengikuti waktu user login)
        $this->load->helper('cookie');
        $waktu_klien      = trim((string) $this->input->post('waktu_klien', TRUE));
        $zona_waktu_klien = trim((string) $this->input->post('zona_waktu_klien', TRUE));
        if (empty($zona_waktu_klien))
        {
            $zona_waktu_klien = (string) get_cookie('prdai_zona_waktu');
        }
        $offset_klien  = $this->input->post('offset_klien');
        $tanggal_klien = trim((string) $this->input->post('tanggal_klien', TRUE));

        if ( ! empty($zona_waktu_klien) && (empty($this->pengguna_masuk->zona_waktu) || $this->pengguna_masuk->zona_waktu !== $zona_waktu_klien))
        {
            $this->load->model('Pengguna_model');
            $this->Pengguna_model->perbarui_zona_waktu($pengguna_id, $zona_waktu_klien);
            $this->pengguna_masuk->zona_waktu = $zona_waktu_klien;
            $this->session->set_userdata('zona_waktu', $zona_waktu_klien);
        }

        $info_waktu = array(
            'waktu_klien'      => $waktu_klien,
            'zona_waktu_klien' => $zona_waktu_klien,
            'offset_klien'     => $offset_klien,
            'tanggal_klien'    => $tanggal_klien,
        );

        if ( ! empty($ide_produk) || ! empty($lampiran_konten))
        {
            if (empty($ide_produk) && ! empty($lampiran_nama))
            {
                $ide_produk = 'Analisis dan buat spesifikasi PRD berdasarkan dokumen terlampir: ' . $lampiran_nama;
            }

            // Cek data langganan & tier pengguna
            $this->load->model('Langganan_model');
            $langganan = $this->Langganan_model->ambil_langganan($pengguna_id);
            $tier = ($langganan && ! empty($langganan->paket)) ? strtolower($langganan->paket) : 'pro';

            // Cek kuota harian (Pro = tanpa batas, Free/Go/Plus = berbatas)
            $this->pembatas_laju->boleh_susun_ai($pengguna_id);

            // Cek batas upload gambar/berkas untuk tier free, go, plus (Pro = upload gambar tanpa batas)
            $apakah_gambar = (int) $this->input->post('apakah_gambar');
            if ( ! empty($lampiran_konten) || ! empty($apakah_gambar))
            {
                if ($tier !== 'pro')
                {
                    $kuota_user = $this->db->where('pengguna_id', $pengguna_id)->get('kuota_pengguna')->row();
                    $batas_aturan_gambar = array(
                        'free' => 5,
                        'go'   => 25,
                        'plus' => 50,
                    );
                    $batas_gambar = isset($batas_aturan_gambar[$tier]) ? $batas_aturan_gambar[$tier] : 5;
                    $gambar_terpakai = $kuota_user ? (int) $kuota_user->gambar_terpakai : 0;

                    if ($gambar_terpakai >= $batas_gambar)
                    {
                        kirim_galat_json("Batas unggah gambar/berkas untuk paket " . ucfirst($tier) . " ({$batas_gambar} berkas) telah tercapai. Upgrade ke Pro untuk akses upload tanpa batas.", array(), 429);
                        return;
                    }

                    $this->db->where('pengguna_id', $pengguna_id)->set('gambar_terpakai', 'gambar_terpakai + 1', FALSE)->update('kuota_pengguna');
                }
            }

            // Periksa apakah ini kelanjutan dari percakapan / PRD yang sedang dibuka
            $prd = NULL;
            $is_lanjutan = FALSE;
            if ( ! empty($kode_aktif))
            {
                $prd = $this->Prd_model->ambil_milik($kode_aktif, $pengguna_id);
                if ($prd)
                {
                    $is_lanjutan = TRUE;
                }
            }

            $masukan_wizard = array();
            if ($is_lanjutan && ! empty($prd->masukan_wizard))
            {
                $mw = json_decode($prd->masukan_wizard, TRUE);
                if (is_array($mw))
                {
                    $masukan_wizard = $mw;
                }
            }

            $percakapan = isset($masukan_wizard['percakapan']) && is_array($masukan_wizard['percakapan'])
                ? $masukan_wizard['percakapan']
                : array();

            if ($is_lanjutan && empty($percakapan))
            {
                $prompt_awal = isset($masukan_wizard['ide_produk']) ? $masukan_wizard['ide_produk'] : $prd->judul;
                $percakapan[] = array(
                    'id'       => 'msg_' . time() . '_u0',
                    'peran'    => 'user',
                    'pesan'    => $prompt_awal,
                    'lampiran' => isset($masukan_wizard['lampiran_nama']) ? $masukan_wizard['lampiran_nama'] : NULL,
                    'waktu'    => date('H:i', strtotime($prd->dibuat_pada)),
                );
                if ( ! empty($prd->isi_markdown))
                {
                    $percakapan[] = array(
                        'id'       => 'msg_' . time() . '_a0',
                        'peran'    => 'assistant',
                        'pesan'    => $prd->isi_markdown,
                        'waktu'    => date('H:i', strtotime($prd->diperbarui_pada ? $prd->diperbarui_pada : $prd->dibuat_pada)),
                    );
                }
            }

            // Jika pengguna menyunting pesan sebelumnya (fitur edit pesan seperti AI modern)
            $indeks_sunting = $this->input->post('indeks_sunting', TRUE);
            if ($is_lanjutan && $indeks_sunting !== NULL && $indeks_sunting !== '' && is_numeric($indeks_sunting))
            {
                $idx_sunting = (int) $indeks_sunting;
                if ($idx_sunting >= 0 && $idx_sunting < count($percakapan))
                {
                    $percakapan = array_slice($percakapan, 0, $idx_sunting);
                }
            }

            if ( ! $is_lanjutan)
            {
                $judul_sementara = mb_substr(preg_replace('/\s+/', ' ', $ide_produk), 0, 60);
                $masukan_wizard = array(
                    'ide_produk'      => $ide_produk,
                    'tingkat_detail'  => $this->input->post('tingkat_detail', TRUE) ? trim($this->input->post('tingkat_detail', TRUE)) : 'Sangat rinci',
                    'ditujukan_untuk' => $this->input->post('ditujukan_untuk', TRUE) ? trim($this->input->post('ditujukan_untuk', TRUE)) : 'AI coding agent (Google Antigravity)',
                    'lampiran_nama'   => $lampiran_nama,
                    'lampiran_konten' => $lampiran_konten,
                    'lampiran_tipe'   => $lampiran_tipe,
                );

                $prd = $this->Prd_model->buat(array(
                    'judul'          => $judul_sementara,
                    'jenis_produk'   => 'Aplikasi web',
                    'masukan_wizard' => $masukan_wizard,
                    'status'         => 'menyusun',
                ), $pengguna_id);
            }

            try
            {
                $mode_berpikir_post  = trim((string) $this->input->post('mode_berpikir', TRUE));
                $mode_berpikir_aktif = ($mode_berpikir_post === 'ya' || $mode_berpikir_post === '1' || $mode_berpikir_post === 'true');

                // Deteksi jenis masukan: sapaan/waktu santai vs permintaan PRD vs pertanyaan umum
                $apakah_sapaan   = $this->penyusun_prompt->apakah_sapaan_atau_waktu($ide_produk);
                $apakah_minta_prd = $this->penyusun_prompt->apakah_permintaan_prd($ide_produk, $lampiran_konten);

                if ($apakah_sapaan && empty($lampiran_konten))
                {
                    // Percakapan santai / sapaan / pertanyaan waktu: jawab cepat, ramah, to-the-point tanpa format PRD
                    $prompt_sistem = $this->penyusun_prompt->ambil_prompt_sistem_ringkas($this->pengguna_masuk, $info_waktu);
                    $opsi_ai = array(
                        'ringkas'     => TRUE,
                        'max_tokens'  => 800,
                        'temperature' => 0.5,
                    );
                }
                elseif ($mode_berpikir_aktif)
                {
                    // MODE BERPIKIR MENDALAM: ketika menekan berpikir baru berikan penjelasan sedetail-detailnya dan jelas sekali
                    $prompt_sistem = $this->penyusun_prompt->ambil_prompt_sistem_berpikir_mendalam($this->pengguna_masuk, $info_waktu, $apakah_minta_prd);
                    $opsi_ai = array(
                        'ringkas'     => FALSE,
                        'max_tokens'  => (int) $this->config->item('ai_maks_token') ?: 8192,
                        'temperature' => 0.32,
                    );
                }
                else
                {
                    // MODE STANDAR (TIDAK MEMILIH BERPIKIR): jawaban rinci kaya biasa
                    $prompt_sistem = $this->penyusun_prompt->ambil_prompt_sistem_rinci_biasa($this->pengguna_masuk, $info_waktu, $apakah_minta_prd);
                    $opsi_ai = array(
                        'ringkas'     => FALSE,
                        'max_tokens'  => 4096,
                        'temperature' => 0.38,
                    );
                }

                $opsi_ai['tier'] = $tier;

                if ($tier === 'pro')
                {
                    // AKUN PRO: AI MODEL TERBAIK, IDE TERBARU, MEMORI 100 GB
                    if ($mode_berpikir_aktif)
                    {
                        // Jika mode berpikir aktif pada akun Pro: kedalaman maksimal sedetail-detailnya
                        $pro_prompt_addon = "\n\n---\n### PROTOKOL KHUSUS AKUN PRDAI PRO (MODE BERPIKIR MENDALAM AKTIF):\n"
                            . "Pengguna ini memiliki akun PRDAI PRO resmi (Akses Tanpa Batas, Memori Maksimal 100 GB, Model AI Unggulan).\n"
                            . "Instruksi Khusus Mode Berpikir Pro:\n"
                            . "1. **Ide Inovatif Terkini & State-of-the-Art**: Berikan ide-ide produk orisinal, terobosan mutakhir, tren masa kini, dan solusi strategis terbaik.\n"
                            . "2. **Penjelasan Sedetail-detailnya & Jelas Sekali**: Paparkan jawaban secara tuntas dari akar arsitektur hingga edge cases, teknis mendalam tanpa ada detail penting yang dilewatkan.\n"
                            . "3. **Memori Riwayat Obrolan Penuh**: Manfaatkan seluruh memori riwayat percakapan pengguna secara menyeluruh untuk kesinambungan dokumen produk jangka panjang tanpa kehilangan konteks.\n";
                        $opsi_ai['max_tokens'] = (int) ($this->config->item('ai_maks_token') ?: 8192);
                    }
                    else
                    {
                        // Jika mode berpikir TIDAK aktif pada akun Pro: jawaban rinci kaya biasa, tajam dan solutif
                        $pro_prompt_addon = "\n\n---\n### PROTOKOL KHUSUS AKUN PRDAI PRO (KECERDASAN MAKSIMAL):\n"
                            . "Pengguna ini memiliki akun PRDAI PRO resmi (Akses Tanpa Batas, Memori Maksimal 100 GB, Model AI Unggulan).\n"
                            . "Instruksi Khusus Pro:\n"
                            . "1. **Kualitas Jawaban Unggulan**: Berikan jawaban yang rinci, terstruktur rapi, berbobot, dan akurat seperti biasa (rinci kaya biasa).\n"
                            . "2. **Ide Inovatif & Tepat Sasaran**: Tawarkan wawasan cerdas dan solusi praktis terbaik tanpa berbelit-belit.\n"
                            . "3. **Memori Riwayat Obrolan Penuh**: Manfaatkan seluruh memori riwayat percakapan pengguna secara menyeluruh untuk kesinambungan dokumen produk jangka panjang.\n";
                    }
                    $prompt_sistem .= $pro_prompt_addon;
                }

                // Bersihkan pesan pengguna terkini dan sertakan lampiran jika ada
                $pesan_user_saat_ini = $ide_produk;
                if ( ! empty($lampiran_konten))
                {
                    $nama_berkas   = ! empty($lampiran_nama) ? trim($lampiran_nama) : 'Dokumen';
                    $konten_berkas = buang_karakter_kontrol($lampiran_konten);
                    $konten_berkas = str_ireplace(array('<dokumen_konteks>', '</dokumen_konteks>'), '', $konten_berkas);
                    $pesan_user_saat_ini = "<dokumen_konteks nama=\"{$nama_berkas}\">\n{$konten_berkas}\n</dokumen_konteks>\n\n" . $pesan_user_saat_ini;
                }

                // Kirim ke model AI dengan urutan: system prompt, riwayat percakapan, pesan terbaru, dan opsi
                $hasil_ai = $this->klien_ai->hasilkan($prompt_sistem, $pesan_user_saat_ini, $percakapan, $opsi_ai);
                $teks_markdown = trim($hasil_ai['teks']);

                if (empty($teks_markdown))
                {
                    throw new Exception('Hasil keluaran AI kosong.');
                }

                // Perbaiki markdown yang terpotong dan sanitasi sintaks Mermaid agar bebas dari syntax error
                $teks_markdown = $this->pemroses_markdown->perbaiki_markdown_terpotong($teks_markdown);
                $teks_markdown = $this->pemroses_markdown->sanitasi_sintaks_mermaid($teks_markdown);

                $waktu_sekarang = ! empty($waktu_klien) ? $waktu_klien : date('H:i');
                $id_user_msg = 'msg_' . time() . '_u';
                $id_asst_msg = 'msg_' . (time() + 1) . '_a';

                $percakapan[] = array(
                    'id'       => $id_user_msg,
                    'peran'    => 'user',
                    'pesan'    => $ide_produk,
                    'lampiran' => ! empty($lampiran_nama) ? $lampiran_nama : NULL,
                    'waktu'    => $waktu_sekarang,
                );

                $percakapan[] = array(
                    'id'            => $id_asst_msg,
                    'peran'         => 'assistant',
                    'pesan'         => $teks_markdown,
                    'waktu'         => $waktu_sekarang,
                    'mode_berpikir' => $mode_berpikir_aktif,
                );

                $masukan_wizard['percakapan'] = $percakapan;

                // Cek apakah balasan merupakan dokumen PRD lengkap atau revisi dokumen PRD
                $apakah_prd = (bool) preg_match('/(?:^#\s+.*(?:PRD|Product\s+Requirements)|Dokumen\s+Spesifikasi\s+Kebutuhan\s+Produk|##\s+\d+\.\s+Petunjuk\s+Khusus\s+untuk\s+AI\s+Coding\s+Agent)/mi', $teks_markdown);

                if ( ! $is_lanjutan)
                {
                    if ($apakah_prd && preg_match('/^#\s+(?:PRD\s*[-—:]\s*)?([^\r\n]+)/m', $teks_markdown, $cocok_judul))
                    {
                        $judul_bersih = trim($cocok_judul[1], "# \t\n\r\0\x0B");
                        if (mb_strlen($judul_bersih) >= 2)
                        {
                            $this->db->where('id', $prd->id)->update('prd', array('judul' => mb_substr($judul_bersih, 0, 150)));
                        }
                    }
                    else
                    {
                        $judul_obrolan = mb_substr(preg_replace('/\s+/', ' ', $ide_produk), 0, 50);
                        if ( ! empty($judul_obrolan))
                        {
                            $this->db->where('id', $prd->id)->update('prd', array('judul' => $judul_obrolan));
                        }
                    }

                    $versi_baru = 1;
                    $this->Prd_model->perbarui_isi($prd->id, $teks_markdown, 'selesai', 1);
                    $this->Prd_versi_model->tambah_versi($prd->id, $teks_markdown, 'ai', $apakah_prd ? 'Versi awal disusun oleh AI' : 'Respon awal AI');
                    $this->Log_aktivitas_model->catat('prd_dibuat', 'Percakapan dimulai: ' . $prd->kode, $pengguna_id);
                }
                else
                {
                    if ($apakah_prd)
                    {
                        if (preg_match('/^#\s+(?:PRD\s*[-—:]\s*)?([^\r\n]+)/m', $teks_markdown, $cocok_judul))
                        {
                            $judul_bersih = trim($cocok_judul[1], "# \t\n\r\0\x0B");
                            if (mb_strlen($judul_bersih) >= 2)
                            {
                                $this->db->where('id', $prd->id)->update('prd', array('judul' => mb_substr($judul_bersih, 0, 150)));
                            }
                        }

                        $versi_baru = (int) $prd->versi_saat_ini + 1;
                        if ($versi_baru < 1) { $versi_baru = 1; }
                        $this->Prd_model->perbarui_isi($prd->id, $teks_markdown, 'selesai', $versi_baru);
                        $this->Prd_versi_model->tambah_versi($prd->id, $teks_markdown, 'ai', 'Revisi: ' . mb_substr($ide_produk, 0, 60));
                        $this->Log_aktivitas_model->catat('prd_diubah', 'PRD direvisi AI: ' . $prd->kode, $pengguna_id);
                    }
                    else
                    {
                        // Jika respon adalah obrolan/klarifikasi/penjelasan, pertahankan PRD yang sudah ada jika ada
                        if (empty($prd->isi_markdown))
                        {
                            $versi_baru = 1;
                            $this->Prd_model->perbarui_isi($prd->id, $teks_markdown, 'selesai', 1);
                        }
                        else
                        {
                            $versi_baru = max(1, (int) $prd->versi_saat_ini);
                            $this->Prd_model->perbarui_status($prd->id, 'selesai');
                        }
                        $this->Log_aktivitas_model->catat('prd_diubah', 'Pesan percakapan: ' . $prd->kode, $pengguna_id);
                    }
                }

                $this->db->where('id', $prd->id)->update('prd', array(
                    'masukan_wizard'  => json_encode($masukan_wizard, JSON_UNESCAPED_UNICODE),
                    'diperbarui_pada' => date('Y-m-d H:i:s'),
                ));

                $this->penggunaan_ai_model->catat(
                    $pengguna_id,
                    $prd->id,
                    $hasil_ai['model'],
                    $hasil_ai['token_masuk'],
                    $hasil_ai['token_keluar'],
                    'sukses'
                );
                $this->pembatas_laju->tambah_hitungan_ai($pengguna_id);

                $prd_terkini = $this->Prd_model->ambil_milik($prd->kode, $pengguna_id);
                $judul_final = $prd_terkini ? $prd_terkini->judul : $prd->judul;

                foreach ($percakapan as &$msg)
                {
                    if ($msg['peran'] === 'assistant')
                    {
                        $msg['html'] = $this->pemroses_markdown->ubah_ke_html($msg['pesan']);
                    }
                }
                unset($msg);

                $html_terbaru = $this->pemroses_markdown->ubah_ke_html($teks_markdown);

                kirim_json(array(
                    'berhasil'        => TRUE,
                    'pesan'           => $is_lanjutan ? 'Jawaban berhasil dikirim.' : $this->lang->line('prd_dibuat'),
                    'tautan'          => site_url('prd/' . $prd->kode),
                    'kode'            => $prd->kode,
                    'judul'           => $judul_final,
                    'is_lanjutan'     => $is_lanjutan,
                    'apakah_prd'      => $apakah_prd,
                    'mode_berpikir'   => $mode_berpikir_aktif,
                    'percakapan'      => $percakapan,
                    'pesan_terbaru'   => array(
                        'id'            => $id_asst_msg,
                        'peran'         => 'assistant',
                        'pesan'         => $teks_markdown,
                        'html'          => $html_terbaru,
                        'waktu'         => $waktu_sekarang,
                        'mode_berpikir' => $mode_berpikir_aktif,
                    ),
                    'html'            => $html_terbaru,
                    'markdown'        => $teks_markdown,
                    'versi'           => $versi_baru,
                    'diperbarui_pada' => date('d M Y, H:i'),
                ));
            }
            catch (Exception $e)
            {
                if (isset($prd->id) && ! $is_lanjutan)
                {
                    $this->Prd_model->perbarui_status($prd->id, 'gagal');
                    $this->penggunaan_ai_model->catat($pengguna_id, $prd->id, 'ai', 0, 0, 'gagal');
                }

                log_message('error', '[Prd::hasilkan] Gagal: ' . $e->getMessage());
                kirim_galat_json($this->lang->line('ai_gagal') . ' (' . $e->getMessage() . ')');
            }
            finally
            {
                $this->pembatas_laju->lepas_kunci_ai($pengguna_id);
            }
        }

        // Jalur Formulir Wizard Lengkap (Kompatibilitas)
        $this->form_validation->set_rules('nama_produk', 'Nama Produk', 'trim|required|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('jenis_produk', 'Jenis Produk', 'trim|required|in_list[Website,Aplikasi web,Aplikasi mobile,API/Backend,Lainnya]');
        $this->form_validation->set_rules('deskripsi_singkat', 'Deskripsi Singkat', 'trim|required|min_length[20]|max_length[500]');
        $this->form_validation->set_rules('masalah', 'Masalah yang Ingin Diselesaikan', 'trim|required|min_length[20]|max_length[1000]');
        $this->form_validation->set_rules('target_pengguna', 'Target Pengguna', 'trim|required|min_length[10]|max_length[500]');
        $this->form_validation->set_rules('gaya_tampilan', 'Gaya Tampilan', 'trim|in_list[Modern bersih,Minimalis,Korporat,Santai]');
        $this->form_validation->set_rules('tingkat_detail', 'Tingkat Detail', 'trim|in_list[Ringkas,Standar,Sangat rinci]');
        $this->form_validation->set_rules('ditujukan_untuk', 'Ditujukan Untuk', 'trim|in_list[AI coding agent (Google Antigravity),AI coding agent (Cline),Developer manusia,Umum]');

        if ($this->form_validation->run() === FALSE)
        {
            kirim_galat_json('Mohon periksa kembali isian formulir Anda.', $this->form_validation->error_array());
        }

        // Ambil array tujuan dan fitur utama
        $tujuan_input = $this->input->post('tujuan');
        $fitur_input  = $this->input->post('fitur_utama');

        $tujuan = array();
        if (is_array($tujuan_input))
        {
            foreach ($tujuan_input as $item)
            {
                $item_bersih = trim((string) $item);
                if (mb_strlen($item_bersih) >= 5 && mb_strlen($item_bersih) <= 200)
                {
                    $tujuan[] = $item_bersih;
                }
            }
        }
        if (empty($tujuan))
        {
            kirim_galat_json('Mohon isi minimal 1 tujuan produk (5–200 karakter).', array('tujuan' => 'Tujuan wajib diisi.'));
        }

        $fitur = array();
        if (is_array($fitur_input))
        {
            foreach ($fitur_input as $item)
            {
                $item_bersih = trim((string) $item);
                if (mb_strlen($item_bersih) >= 3 && mb_strlen($item_bersih) <= 200)
                {
                    $fitur[] = $item_bersih;
                }
            }
        }
        if (empty($fitur))
        {
            kirim_galat_json('Mohon isi minimal 1 fitur utama (3–200 karakter).', array('fitur_utama' => 'Fitur wajib diisi.'));
        }

        // Cek kuota harian (bebas tanpa batas)
        $this->pembatas_laju->boleh_susun_ai($pengguna_id);

        $masukan_wizard = array(
            'nama_produk'            => trim($this->input->post('nama_produk', TRUE)),
            'jenis_produk'           => trim($this->input->post('jenis_produk', TRUE)),
            'deskripsi_singkat'      => trim($this->input->post('deskripsi_singkat', TRUE)),
            'masalah'                => trim($this->input->post('masalah', TRUE)),
            'target_pengguna'        => trim($this->input->post('target_pengguna', TRUE)),
            'tujuan'                 => $tujuan,
            'fitur_utama'            => $fitur,
            'teknologi'              => trim((string) $this->input->post('teknologi', TRUE)),
            'halaman_diperlukan'     => trim((string) $this->input->post('halaman_diperlukan', TRUE)),
            'kebutuhan_keamanan'     => $this->input->post('kebutuhan_keamanan'),
            'gaya_tampilan'          => trim($this->input->post('gaya_tampilan', TRUE)),
            'catatan_tampilan'       => trim((string) $this->input->post('catatan_tampilan', TRUE)),
            'tingkat_detail'         => trim($this->input->post('tingkat_detail', TRUE)),
            'ditujukan_untuk'        => trim($this->input->post('ditujukan_untuk', TRUE)),
            'sertakan_flowchart'     => $this->input->post('sertakan_flowchart') === 'ya' ? 'ya' : 'tidak',
            'sertakan_struktur_file' => $this->input->post('sertakan_struktur_file') === 'ya' ? 'ya' : 'tidak',
        );

        // Buat baris PRD berstatus menyusun
        $prd = $this->Prd_model->buat(array(
            'judul'          => $masukan_wizard['nama_produk'],
            'jenis_produk'   => $masukan_wizard['jenis_produk'],
            'masukan_wizard' => $masukan_wizard,
            'status'         => 'menyusun',
        ), $pengguna_id);

        try
        {
            // Rakit prompt dan panggil AI (susun PRD lengkap mendalam)
            $prompt = $this->penyusun_prompt->rakit($masukan_wizard);
            $opsi_ai = array(
                'ringkas'     => FALSE,
                'max_tokens'  => (int) $this->config->item('ai_maks_token') ?: 8192,
                'temperature' => 0.35,
            );
            $hasil_ai = $this->klien_ai->hasilkan($prompt['prompt_sistem'], $prompt['prompt_pengguna'], array(), $opsi_ai);

            $teks_markdown = $hasil_ai['teks'];
            $teks_markdown = $this->pemroses_markdown->perbaiki_markdown_terpotong($teks_markdown);
            $teks_markdown = $this->pemroses_markdown->sanitasi_sintaks_mermaid($teks_markdown);
            $panjang_teks  = mb_strlen($teks_markdown);

            if ($panjang_teks < 500)
            {
                throw new Exception('Hasil keluaran AI terlalu pendek atau tidak lengkap.');
            }

            // Siapkan percakapan awal agar dapat dilanjutkan di ruang kerja
            $masukan_wizard['percakapan'] = array(
                array(
                    'id'       => 'msg_' . time() . '_u',
                    'peran'    => 'user',
                    'pesan'    => 'Buatkan PRD untuk ' . $masukan_wizard['nama_produk'] . ': ' . $masukan_wizard['deskripsi_singkat'],
                    'waktu'    => date('H:i'),
                ),
                array(
                    'id'       => 'msg_' . (time() + 1) . '_a',
                    'peran'    => 'assistant',
                    'pesan'    => $teks_markdown,
                    'waktu'    => date('H:i'),
                ),
            );

            // Simpan hasil dan buat versi nomor 1
            $this->Prd_model->perbarui_isi($prd->id, $teks_markdown, 'selesai', 1);
            $this->db->where('id', $prd->id)->update('prd', array(
                'masukan_wizard' => json_encode($masukan_wizard, JSON_UNESCAPED_UNICODE),
            ));
            $this->Prd_versi_model->tambah_versi($prd->id, $teks_markdown, 'ai', 'Versi awal disusun oleh AI');

            // Catat log dan penggunaan
            $this->penggunaan_ai_model->catat(
                $pengguna_id,
                $prd->id,
                $hasil_ai['model'],
                $hasil_ai['token_masuk'],
                $hasil_ai['token_keluar'],
                'sukses'
            );

            $this->Log_aktivitas_model->catat('prd_dibuat', 'PRD dibuat via AI: ' . $prd->kode, $pengguna_id);
            $this->pembatas_laju->tambah_hitungan_ai($pengguna_id);

            kirim_json(array(
                'berhasil' => TRUE,
                'pesan'    => $this->lang->line('prd_dibuat'),
                'tautan'   => site_url('prd/' . $prd->kode),
            ));
        }
        catch (Exception $e)
        {
            // Tandai PRD gagal jika proses terputus
            if (isset($prd->id))
            {
                $this->Prd_model->perbarui_status($prd->id, 'gagal');
                $this->penggunaan_ai_model->catat($pengguna_id, $prd->id, 'ai', 0, 0, 'gagal');
            }

            log_message('error', '[Prd::hasilkan] Gagal: ' . $e->getMessage());
            kirim_galat_json($this->lang->line('ai_gagal') . ' (' . $e->getMessage() . ')');
        }
        finally
        {
            // Selalu lepas kunci proses
            $this->pembatas_laju->lepas_kunci_ai($pengguna_id);
        }
    }

    /**
     * Tampilkan detail dokumen PRD dalam antarmuka terpadu satu layar (ChatGPT Dark Mode).
     */
    public function detail($kode)
    {
        $pengguna_id = $this->pengguna_masuk ? (int) $this->pengguna_masuk->id : 0;
        $prd = $this->Prd_model->ambil_milik($kode, $pengguna_id);
        if ( ! $prd)
        {
            show_404();
        }

        $daftar_terkini = $this->Prd_model->ambil_daftar_milik($pengguna_id, '', 50, 0);
        $html_prd = $this->pemroses_markdown->ubah_ke_html($prd->isi_markdown);
        $daftar_versi = $this->Prd_versi_model->ambil_semua($prd->id);

        $mw = ! empty($prd->masukan_wizard) ? json_decode($prd->masukan_wizard, TRUE) : array();
        $percakapan = isset($mw['percakapan']) && is_array($mw['percakapan'])
            ? $mw['percakapan']
            : array();

        if (empty($percakapan))
        {
            $prompt_awal = isset($mw['ide_produk']) ? $mw['ide_produk'] : $prd->judul;
            $percakapan[] = array(
                'id'       => 'msg_u_0',
                'peran'    => 'user',
                'pesan'    => $prompt_awal,
                'lampiran' => isset($mw['lampiran_nama']) ? $mw['lampiran_nama'] : NULL,
                'waktu'    => date('H:i', strtotime($prd->dibuat_pada)),
            );
            if ( ! empty($prd->isi_markdown))
            {
                $percakapan[] = array(
                    'id'       => 'msg_a_0',
                    'peran'    => 'assistant',
                    'pesan'    => $prd->isi_markdown,
                    'waktu'    => date('H:i', strtotime($prd->diperbarui_pada ? $prd->diperbarui_pada : $prd->dibuat_pada)),
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

        $data = array(
            'judul_halaman'  => $prd->judul . ' — PRDAI',
            'css_halaman'    => array('workspace.css'),
            'js_halaman'     => array('workspace.js'),
            'daftar_terkini' => $daftar_terkini,
            'prd_aktif'      => $prd,
            'percakapan'     => $percakapan,
            'html_prd'       => $html_prd,
            'daftar_versi'   => $daftar_versi,
            'pengguna'       => $this->pengguna_masuk,
        );

        $this->tampilkan('beranda/index', $data);
    }

    /**
     * Endpoint AJAX untuk memuat data PRD secara dinamis tanpa muat ulang halaman.
     */
    public function ambil_json($kode)
    {
        $this->jika_ajax_wajib();

        $pengguna_id = $this->pengguna_masuk ? (int) $this->pengguna_masuk->id : 0;
        $prd = $this->Prd_model->ambil_milik($kode, $pengguna_id);
        if ( ! $prd)
        {
            kirim_galat_json('Dokumen PRD tidak ditemukan.');
        }

        $mw = ! empty($prd->masukan_wizard) ? json_decode($prd->masukan_wizard, TRUE) : array();
        $percakapan = isset($mw['percakapan']) && is_array($mw['percakapan'])
            ? $mw['percakapan']
            : array();

        if (empty($percakapan))
        {
            $prompt_awal = isset($mw['ide_produk']) ? $mw['ide_produk'] : $prd->judul;
            $percakapan[] = array(
                'id'       => 'msg_u_0',
                'peran'    => 'user',
                'pesan'    => $prompt_awal,
                'lampiran' => isset($mw['lampiran_nama']) ? $mw['lampiran_nama'] : NULL,
                'waktu'    => date('H:i', strtotime($prd->dibuat_pada)),
            );
            if ( ! empty($prd->isi_markdown))
            {
                $percakapan[] = array(
                    'id'       => 'msg_a_0',
                    'peran'    => 'assistant',
                    'pesan'    => $prd->isi_markdown,
                    'waktu'    => date('H:i', strtotime($prd->diperbarui_pada ? $prd->diperbarui_pada : $prd->dibuat_pada)),
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

        $html_prd = $this->pemroses_markdown->ubah_ke_html($prd->isi_markdown);
        $daftar_versi = $this->Prd_versi_model->ambil_semua($prd->id);

        kirim_json(array(
            'berhasil'     => TRUE,
            'prd'          => $prd,
            'percakapan'   => $percakapan,
            'html'         => $html_prd,
            'daftar_versi' => $daftar_versi,
        ));
    }

    /**
     * Halaman dan proses sunting manual PRD (mendukung AJAX langsung).
     */
    public function ubah($kode)
    {
        $pengguna_id = $this->pengguna_masuk ? (int) $this->pengguna_masuk->id : 0;
        if ($pengguna_id <= 0)
        {
            show_404();
        }
        $prd = $this->Prd_model->ambil_milik($kode, $pengguna_id);
        if ( ! $prd)
        {
            show_404();
        }

        if ($this->input->method() === 'post')
        {
            $this->form_validation->set_rules('isi_markdown', 'Isi Dokumen', 'required|max_length[200000]');
            $this->form_validation->set_rules('catatan_versi', 'Catatan Revisi', 'trim|max_length[255]');

            if ($this->form_validation->run() === TRUE)
            {
                $isi_baru = (string) $this->input->post('isi_markdown');
                $catatan  = $this->input->post('catatan_versi', TRUE);

                // Buat versi baru dan perbarui PRD
                $nomor_baru = $this->Prd_versi_model->tambah_versi($prd->id, $isi_baru, 'manual', $catatan);
                $this->Prd_model->perbarui_isi($prd->id, $isi_baru, 'selesai', $nomor_baru);

                // Sinkronkan ke percakapan aktif jika ada
                $mw = ! empty($prd->masukan_wizard) ? json_decode($prd->masukan_wizard, TRUE) : array();
                if (isset($mw['percakapan']) && is_array($mw['percakapan']))
                {
                    for ($i = count($mw['percakapan']) - 1; $i >= 0; $i--)
                    {
                        if ($mw['percakapan'][$i]['peran'] === 'assistant')
                        {
                            $mw['percakapan'][$i]['pesan'] = $isi_baru;
                            break;
                        }
                    }
                    $this->db->where('id', $prd->id)->update('prd', array(
                        'masukan_wizard'  => json_encode($mw, JSON_UNESCAPED_UNICODE),
                        'diperbarui_pada' => date('Y-m-d H:i:s'),
                    ));
                }

                $this->Log_aktivitas_model->catat('prd_diubah', 'PRD disunting: ' . $prd->kode . ' ke versi ' . $nomor_baru, $pengguna_id);

                if ($this->input->is_ajax_request())
                {
                    kirim_json(array(
                        'berhasil'   => TRUE,
                        'pesan'      => $this->lang->line('prd_diperbarui'),
                        'nomor_baru' => $nomor_baru,
                        'html'       => $this->pemroses_markdown->ubah_ke_html($isi_baru),
                        'markdown'   => $isi_baru,
                    ));
                }

                $this->session->set_flashdata('pesan_sukses', $this->lang->line('prd_diperbarui'));
                redirect('prd/' . $prd->kode);
            }
            else if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Validasi isi dokumen gagal.', $this->form_validation->error_array());
            }
        }

        $data = array(
            'judul_halaman' => 'Sunting PRD — ' . $prd->judul,
            'css_halaman'   => array('workspace.css'),
            'js_halaman'    => array('workspace.js'),
            'prd'           => $prd,
        );

        $this->tampilkan('prd/ubah', $data);
    }


    /**
     * Endpoint AJAX untuk pratinjau Markdown aman.
     */
    public function pratinjau()
    {
        $this->jika_ajax_wajib();

        $markdown = (string) $this->input->post('markdown');
        if (mb_strlen($markdown) > 200000)
        {
            kirim_galat_json('Isi pratinjau melebihi batas 200.000 karakter.');
        }

        $html = $this->pemroses_markdown->ubah_ke_html($markdown);

        kirim_json(array(
            'berhasil' => TRUE,
            'html'     => $html,
        ));
    }

    /**
     * Tampilkan riwayat versi dokumen PRD.
     */
    public function versi($kode)
    {
        $prd = $this->Prd_model->ambil_milik($kode, $this->pengguna_masuk->id);
        if ( ! $prd)
        {
            show_404();
        }

        $daftar_versi = $this->Prd_versi_model->ambil_semua($prd->id);

        $data = array(
            'judul_halaman' => 'Riwayat Versi — ' . $prd->judul,
            'css_halaman'   => array('prd_detail.css'),
            'prd'           => $prd,
            'daftar_versi'  => $daftar_versi,
        );

        $this->tampilkan('prd/versi', $data);
    }

    /**
     * Pulihkan dokumen PRD ke salah satu versi sebelumnya.
     */
    public function pulihkan($kode, $nomor_versi)
    {
        $pengguna_id = $this->pengguna_masuk ? (int) $this->pengguna_masuk->id : 0;
        if ($pengguna_id <= 0)
        {
            if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Sesi Anda tidak valid.');
            }
            show_404();
        }

        if ($this->input->method() !== 'post')
        {
            redirect('prd/' . $kode);
        }

        $prd = $this->Prd_model->ambil_milik($kode, $pengguna_id);
        if ( ! $prd)
        {
            if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Dokumen PRD tidak ditemukan.');
            }
            show_404();
        }

        $versi = $this->Prd_versi_model->ambil_versi($prd->id, $nomor_versi);
        if ( ! $versi)
        {
            if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Versi dokumen tidak ditemukan.');
            }
            show_404();
        }

        // Simpan pemulihan sebagai versi baru untuk menjaga riwayat utuh
        $catatan = 'Dipulihkan dari versi ' . (int) $nomor_versi;
        $nomor_baru = $this->Prd_versi_model->tambah_versi($prd->id, $versi->isi_markdown, 'pemulihan', $catatan);
        $this->Prd_model->perbarui_isi($prd->id, $versi->isi_markdown, 'selesai', $nomor_baru);

        // Sinkronkan ke percakapan aktif jika ada
        $mw = ! empty($prd->masukan_wizard) ? json_decode($prd->masukan_wizard, TRUE) : array();
        if (isset($mw['percakapan']) && is_array($mw['percakapan']))
        {
            for ($i = count($mw['percakapan']) - 1; $i >= 0; $i--)
            {
                if ($mw['percakapan'][$i]['peran'] === 'assistant')
                {
                    $mw['percakapan'][$i]['pesan'] = $versi->isi_markdown;
                    break;
                }
            }
            $this->db->where('id', $prd->id)->update('prd', array(
                'masukan_wizard'  => json_encode($mw, JSON_UNESCAPED_UNICODE),
                'diperbarui_pada' => date('Y-m-d H:i:s'),
            ));
        }

        $this->Log_aktivitas_model->catat('prd_dipulihkan', 'PRD ' . $prd->kode . ' dipulihkan ke versi ' . $nomor_baru, $pengguna_id);

        if ($this->input->is_ajax_request())
        {
            kirim_json(array(
                'berhasil'   => TRUE,
                'pesan'      => $this->lang->line('prd_dipulihkan'),
                'nomor_baru' => $nomor_baru,
                'html'       => $this->pemroses_markdown->ubah_ke_html($versi->isi_markdown),
                'markdown'   => $versi->isi_markdown,
            ));
        }

        $this->session->set_flashdata('pesan_sukses', $this->lang->line('prd_dipulihkan'));
        redirect('prd/' . $prd->kode);
    }

    /**
     * Unduh dokumen PRD dalam berkas .md.
     */
    public function unduh($kode)
    {
        $pengguna_id = $this->pengguna_masuk ? (int) $this->pengguna_masuk->id : 0;
        if ($pengguna_id <= 0)
        {
            show_404();
        }
        $prd = $this->Prd_model->ambil_milik($kode, $pengguna_id);
        if ( ! $prd)
        {
            show_404();
        }

        // Sanitasi nama berkas (huruf kecil, angka, tanda hubung)
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($prd->judul));
        $slug = trim($slug, '-');
        if (empty($slug))
        {
            $slug = 'dokumen';
        }

        $nama_berkas = 'PRD-' . $slug . '.md';

        $this->output
            ->set_content_type('text/markdown', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="' . $nama_berkas . '"')
            ->set_output($prd->isi_markdown ? $prd->isi_markdown : '');
    }

    /**
     * Hapus lunak dokumen PRD.
     */
    public function hapus($kode)
    {
        $pengguna_id = $this->pengguna_masuk ? (int) $this->pengguna_masuk->id : 0;
        if ($pengguna_id <= 0)
        {
            if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Sesi Anda tidak valid.');
            }
            show_404();
        }

        if ($this->input->method() !== 'post')
        {
            redirect('/');
        }

        $prd = $this->Prd_model->ambil_milik($kode, $pengguna_id);
        if ( ! $prd)
        {
            if ($this->input->is_ajax_request())
            {
                kirim_galat_json('Dokumen PRD tidak ditemukan.');
            }
            show_404();
        }

        $this->Prd_model->hapus_lunak($kode, $pengguna_id);
        $this->Log_aktivitas_model->catat('prd_dihapus', 'PRD dihapus lunak: ' . $kode, $pengguna_id);

        if ($this->input->is_ajax_request())
        {
            kirim_json(array(
                'berhasil' => TRUE,
                'pesan'    => $this->lang->line('prd_dihapus'),
            ));
        }

        $this->session->set_flashdata('pesan_sukses', $this->lang->line('prd_dihapus'));
        redirect('/');
    }

}
