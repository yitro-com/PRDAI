<?php
/**
 * PRDAI — Penyusun prompt AI (Penyusun_prompt).
 *
 * Merakit prompt sistem dan prompt pengguna dari data formulir wizard atau masukan ruang kerja,
 * dengan sanitasi masukan untuk mencegah prompt injection.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Penyusun_prompt
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * Ambil isi system prompt lengkap untuk PRD / analisis sistem mendalam (Sections A - H).
     */
    /**
     * Ambil isi system prompt lengkap untuk PRD / analisis sistem mendalam (Sections A - H).
     */
    public function ambil_prompt_sistem_lengkap($pengguna = NULL, $info_klien = array())
    {
        $berkas = APPPATH . 'prompts/system_prompt.md';
        $isi = '';
        if (file_exists($berkas))
        {
            $isi = file_get_contents($berkas);
        }
        else
        {
            $dari_config = $this->ci->config->item('prompt_sistem');
            if (!empty($dari_config))
            {
                $isi = $dari_config;
            }
            else
            {
                $isi = 'Kamu adalah asisten PRDAI. Jawab pertanyaan pengguna dengan benar, relevan, dan langsung ke inti. Bantu menyusun PRD hanya jika diminta.';
            }
        }

        return $isi . "\n\n" . $this->susun_konteks_waktu_realtime($pengguna, $info_klien);
    }

    /**
     * Ambil isi system prompt ringkas untuk percakapan umum & pertanyaan biasa.
     * Didesain super ringan (sedikit token) agar model lokal AI merespons sangat cepat (1-2 detik)
     * tanpa template kaku dan tanpa membuat dokumen PRD.
     */
    public function ambil_prompt_sistem_ringkas($pengguna = NULL, $info_klien = array())
    {
        $prompt = "# PRDAI: Asisten Cerdas

Kamu adalah asisten AI PRDAI yang ramah, cerdas, dan tangkas. Tugas utamamu adalah menjawab pertanyaan pengguna dengan benar, tepat, relevan, dan langsung ke inti dalam Bahasa Indonesia.

## Panduan Perilaku Menjawab:
1. **Jawab Cepat & Langsung ke Inti**: Jawab persis apa yang ditanyakan pengguna. Jangan berbelit-belit, jangan gunakan pembukaan panjang atau template hafalan kaku.
2. **Jangan Membuat PRD**: Ini adalah pertanyaan atau percakapan biasa. JANGAN membuat dokumen PRD, tabel spesifikasi berlebih, atau template dokumen kecuali pengguna secara eksplisit meminta \"buatkan PRD\".
3. **Pertanyaan Santai & Sapaan**: Jika disapa (seperti 'halo', 'selamat pagi/siang/sore/malam') atau ditanya status/aktivitas, jawablah ramah dan sesuaikan sapaan dengan waktu lokal pengguna yang sedang login.
4. **Pertanyaan Penjelasan / Konsep Teknis**: Jika pengguna menanyakan konsep, istilah, atau pertanyaan umum, jelaskan secara akurat, padat, berbobot, dan mudah dipahami.
5. **Gunakan Konteks Percakapan**: Jika ada riwayat percakapan sebelumnya, jawablah dengan menyambung konteks secara logis.
6. **Jawab Tepat Sesuai yang Ditanya**: Jawab dengan benar, wajar, dan proporsional. Jika pertanyaan biasa atau sederhana, jawab langsung to-the-point tanpa berbelit-belit. Hanya berikan penjelasan mendalam jika diminta secara spesifik.";

        return $prompt . "\n\n" . $this->susun_konteks_waktu_realtime($pengguna, $info_klien);
    }

    /**
     * Ambil isi system prompt untuk Mode Standar (TIDAK memilih Berpikir).
     * Sesuai instruksi: "kalo tidak memilih berpikir maka jawaban nya rinci kaya biasa".
     *
     * Memberikan jawaban yang terstruktur, informatif, berbobot, dan rinci seperti biasa,
     * tanpa analisis mendalam yang berlebihan (karena mode berpikir sedang nonaktif),
     * dan tidak dipotong terlalu pendek.
     */
    public function ambil_prompt_sistem_rinci_biasa($pengguna = NULL, $info_klien = array(), $apakah_prd = FALSE)
    {
        $instruksi_prd = $apakah_prd ? "
3. **Penanganan Spesifikasi Produk / PRD**:
   - Pengguna meminta perancangan ide produk, fitur, atau spesifikasi sistem.
   - Susun spesifikasi PRD fungsional yang rapi, terstruktur, padat berisi, dan rinci standar seperti biasa (meliputi: Konsep & Nilai Produk, Target Pengguna, Fitur Utama & Kebutuhan Fungsional, Alur Pengguna Utama, Tumpukan Teknologi yang Disarankan, dan Kriteria Pengujian Inti).
   - Tidak perlu memaksakan format 17 bab yang berlebihan atau teori filosofis panjang. Buat dokumen praktis yang jelas dan siap dipahami tim pengembang." : "
3. **Penanganan Pertanyaan / Percakapan**:
   - Jawab pertanyaan pengguna secara proporsional dan langsung ke sasaran.
   - JANGAN membuat dokumen PRD formal 17 bab jika pengguna hanya bertanya hal teknis, tanya jawab umum, atau diskusi ide. Buatkan PRD hanya jika pengguna secara spesifik memintanya.";

        $prompt = "# PRDAI: Mode Standar (Jawaban Rinci Seperti Biasa)

Kamu adalah PRDAI, asisten kecerdasan produk dan teknologi yang cerdas, tanggap, dan profesional.
Pengguna saat ini berada dalam **Mode Standar** (Mode Berpikir sedang TIDAK aktif).

## Aturan Utama Jawaban (Rinci Seperti Biasa):
1. **Rinci Seperti Biasa (Standard Detailed)**:
   - Berikan jawaban yang rinci, jelas, berbobot, dan terstruktur dengan rapi seperti asisten profesional pada umumnya (\"rinci kaya biasa\").
   - Jangan memaksakan penjelasan super mendalam (sedetail-detailnya) atau membedah semua edge case ekstrim, karena mode berpikir mendalam sedang nonaktif.
   - Namun JANGAN pula membuat jawaban terlalu pendek, terpotong, atau pelit penjelasan. Paparkan secara tuntas, informatif, dan langsung menjawab kebutuhan pengguna.

2. **Format & Keterbacaan**:
   - Gunakan format Markdown yang rapi (judul heading yang proporsional, poin-poin bullet/daftar nomor, teks tebal pada istilah kunci).
   - Pastikan alur penjelasan mengalir logis dan mudah dipahami.
{$instruksi_prd}

4. **Penanganan Pertanyaan Konsep / Teknis / Analisis**:
   - Jelaskan konsep dengan akurat dan rinci: mulai dari inti masalah/konsep, cara kerja utama, kelebihan/kekurangan, dan contoh praktis bila relevan.
   - Fokus pada substansi tanpa bertele-tele.

5. **Kesesuaian Konteks**:
   - Manfaatkan riwayat percakapan sebelumnya untuk menjaga kesinambungan obrolan secara alami.";

        return $prompt . "\n\n" . $this->susun_konteks_waktu_realtime($pengguna, $info_klien);
    }

    /**
     * Ambil isi system prompt untuk Mode Berpikir Mendalam (KETIKA menekan Berpikir).
     * Sesuai instruksi: "ketika menekan berpikir baru berikan penjelasan sedetail-detailnya... kalo mendalam baru detail jelas sekali".
     *
     * Mengaktifkan Deep Reasoning: penjelasan sedetail-detailnya, tuntas, analitis,
     * mengupas arsitektur teknis, edge cases, alur sistem, dan pertimbangan secara gamblang dan jelas sekali.
     */
    public function ambil_prompt_sistem_berpikir_mendalam($pengguna = NULL, $info_klien = array(), $apakah_prd = FALSE)
    {
        $berkas_master = APPPATH . 'prompts/system_prompt.md';
        $panduan_master = file_exists($berkas_master) ? file_get_contents($berkas_master) : '';

        $instruksi_prd = $apakah_prd ? "
3. **Penanganan Spesifikasi Produk / Dokumen PRD**:
   - Pengguna meminta perancangan produk / sistem. Karena Mode Berpikir aktif, susun **DOKUMEN PRD TINGKAT ENTERPRISE LENGKAP 17 BAB RESMI** secara tuntas dan sedetail-detailnya.
   - Setiap bab dari Bab 1 hingga Bab 17 wajib dijabarkan secara mendalam, mencakup tabel kebutuhan fungsional dengan ID unik & Kriteria Penerimaan Teruji (Given-When-Then), model data lengkap relasi & tipe data, arsitektur teknis, diagram Mermaid alur sistem & roadmap pohon implementasi bertahap, serta kriteria kesiapan Definition of Done (DoD).
   - Dokumen harus siap diimplementasikan langsung oleh software engineer maupun AI coding agent (khususnya Google Antigravity)." : "
3. **Penanganan Penjelasan Konsep / Analisis Teknis**:
   - Pengguna menanyakan topik teknis, analisis arsitektur, pemecahan masalah, atau konsultasi sistem.
   - Bedah topik tersebut secara mendalam: mulai dari akar masalah (root cause), mekanisme internal (under the hood), arsitektur komponen, trade-offs, perbandingan dengan pendekatan lain, studi kasus riil, potensi jebakan (pitfalls) & mitigasinya, hingga panduan implementasi yang detail dan jelas sekali.
   - Jika pengguna secara eksplisit meminta PRD, susun PRD lengkap 17 bab sedetail-detailnya.";

        $prompt = "# PRDAI: PROTOKOL MODE BERPIKIR MENDALAM (DEEP REASONING & ULTRA-DETAILED)

PERHATIAN UTAMA: Pengguna telah secara aktif MENEKAN TOMBOL BERPIKIR (Mode Berpikir / Mendalam Aktif).
Kamu WAJIB mengaktifkan penalaran mendalam (deep reasoning) dan memberikan **PENJELASAN SEDETAIL-DETAILNYA, MENDALAM, DAN DETAIL JELAS SEKALI**.

## Prinsip Mutlak Mode Berpikir Mendalam:
1. **Penjelasan Sedetail-Detailnya (Ultra-Detailed)**:
   - Dilarang keras menyingkat, merangkum, atau melompati detail penting demi mempersingkat teks.
   - Bedah setiap komponen dari akar masalah hingga implikasi teknis tingkat lanjut.
   - Kupas tuntas aspek arsitektural, logika sistem, skema data, aturan bisnis spesifik, validasi masukan, kondisi gagal dan mekanisme recovery, konkurensi, serta keamanan.

2. **Detail Jelas Sekali (Crystal-Clear & Actionable)**:
   - Sampaikan setiap penjelasan secara gamblang, terstruktur rapi, logis, dan sangat jelas sehingga tidak menyisakan ambiguitas.
   - Sertakan skenario batas (edge cases), kondisi batas input, mitigasi risiko teknis, dan trade-offs (pro dan kontra) secara eksplisit.
   - Jika relevan, sertakan Diagram Alur Sistem teknis Mermaid (`flowchart TD` atau `sequenceDiagram`) dengan sintaks ketat bebas galat (selalu gunakan tanda kutip ganda pada label teks, ID node tanpa spasi).
{$instruksi_prd}

4. **Penalaran Multi-Perspektif**:
   - Tinjau dari sudut pandang Arsitektur Sistem, Kebutuhan Bisnis, Pengalaman Pengguna (UX), Keamanan Sistem, dan Kualitas Rekayasa (QA).
   - Jelaskan alasan mendasar di balik setiap rekomendasi dan keputusan teknis yang diambil.";

        if (! empty($panduan_master))
        {
            $prompt .= "\n\n---\n### STANDAR SPESIFIKASI DAN KUALITAS MASTER PRDAI:\n" . $panduan_master;
        }

        return $prompt . "\n\n" . $this->susun_konteks_waktu_realtime($pengguna, $info_klien);
    }


    /**
     * Susun konteks waktu real-time dan panduan komprehensif yang mengikuti waktu pengguna yang sedang login.
     *
     * @param object|array|null $pengguna   Data pengguna yang sedang masuk (dari $this->pengguna_masuk)
     * @param array             $info_klien Informasi waktu lokal peramban (waktu_klien, zona_waktu_klien, offset_klien, tanggal_klien)
     * @return string
     */
    public function susun_konteks_waktu_realtime($pengguna = NULL, $info_klien = array())
    {
        if ( ! is_array($info_klien))
        {
            $info_klien = array();
        }

        // 1. Tentukan zona waktu pengguna (prioritas: kiriman klien -> DB pengguna -> session -> cookie -> offset -> default Asia/Makassar)
        $zona_nama = ! empty($info_klien['zona_waktu_klien']) ? trim($info_klien['zona_waktu_klien']) : '';
        if (empty($zona_nama) && is_object($pengguna) && ! empty($pengguna->zona_waktu))
        {
            $zona_nama = trim($pengguna->zona_waktu);
        }
        if (empty($zona_nama) && isset($this->ci->session))
        {
            $zona_nama = (string) $this->ci->session->userdata('zona_waktu');
        }
        if (empty($zona_nama) && function_exists('get_cookie'))
        {
            $zona_nama = (string) get_cookie('prdai_zona_waktu');
        }
        if (empty($zona_nama) && isset($info_klien['offset_klien']))
        {
            $offset_menit = (int) $info_klien['offset_klien'];
            if ($offset_menit === 420) $zona_nama = 'Asia/Jakarta';      // UTC+7 (WIB)
            elseif ($offset_menit === 480) $zona_nama = 'Asia/Makassar'; // UTC+8 (WITA)
            elseif ($offset_menit === 540) $zona_nama = 'Asia/Jayapura'; // UTC+9 (WIT)
        }
        if (empty($zona_nama) || ! in_array($zona_nama, DateTimeZone::listIdentifiers()))
        {
            $zona_nama = 'Asia/Makassar'; // Bawaan lokasi pengguna (WITA / UTC+8)
        }

        try {
            $tz_user = new DateTimeZone($zona_nama);
        } catch (Exception $e) {
            $zona_nama = 'Asia/Makassar';
            $tz_user = new DateTimeZone('Asia/Makassar');
        }

        $tz_wib  = new DateTimeZone('Asia/Jakarta');
        $tz_wita = new DateTimeZone('Asia/Makassar');
        $tz_wit  = new DateTimeZone('Asia/Jayapura');

        $dt_user = new DateTime('now', $tz_user);
        $dt_wib  = new DateTime('now', $tz_wib);
        $dt_wita = new DateTime('now', $tz_wita);
        $dt_wit  = new DateTime('now', $tz_wit);

        // Tentukan label zona pengguna (WIB, WITA, WIT, atau offset UTC)
        $label_zona = 'WITA';
        if (in_array($zona_nama, array('Asia/Jakarta', 'Asia/Pontianak')))
        {
            $label_zona = 'WIB';
        }
        elseif (in_array($zona_nama, array('Asia/Makassar', 'Asia/Ujung_Pandang', 'Asia/Bali', 'Asia/Banjarmasin', 'Asia/Kupang', 'Asia/Manado')))
        {
            $label_zona = 'WITA';
        }
        elseif (in_array($zona_nama, array('Asia/Jayapura')))
        {
            $label_zona = 'WIT';
        }
        else
        {
            $offset_detik = $tz_user->getOffset($dt_user);
            $offset_jam = $offset_detik / 3600;
            if ($offset_jam == 7) $label_zona = 'WIB';
            elseif ($offset_jam == 8) $label_zona = 'WITA';
            elseif ($offset_jam == 9) $label_zona = 'WIT';
            else $label_zona = 'UTC' . ($offset_jam >= 0 ? '+' . $offset_jam : $offset_jam);
        }

        $daftar_hari  = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
        $daftar_bulan = array('', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

        $hari_user = $daftar_hari[(int) $dt_user->format('w')];
        $tgl_u     = (int) $dt_user->format('j');
        $bln_u     = $daftar_bulan[(int) $dt_user->format('n')];
        $thn_u     = $dt_user->format('Y');

        $tgl_lengkap_user = ! empty($info_klien['tanggal_klien']) ? $info_klien['tanggal_klien'] : "{$hari_user}, {$tgl_u} {$bln_u} {$thn_u}";
        $jam_user = ! empty($info_klien['waktu_klien']) ? $info_klien['waktu_klien'] : $dt_user->format('H:i');

        $jam_wib  = $dt_wib->format('H:i');
        $jam_wita = $dt_wita->format('H:i');
        $jam_wit  = $dt_wit->format('H:i');

        // Data pengguna aktif & waktu sesi login
        $nama_pengguna  = (is_object($pengguna) && ! empty($pengguna->nama)) ? $pengguna->nama : 'Pengguna';
        $email_pengguna = (is_object($pengguna) && ! empty($pengguna->email)) ? $pengguna->email : '-';
        $info_login_teks = 'Sesi aktif saat ini';

        if (is_object($pengguna) && ! empty($pengguna->terakhir_masuk))
        {
            try {
                $dt_masuk = new DateTime($pengguna->terakhir_masuk, $tz_wib);
                $dt_masuk->setTimezone($tz_user);
                $hari_masuk = $daftar_hari[(int) $dt_masuk->format('w')];
                $tgl_m      = (int) $dt_masuk->format('j');
                $bln_m      = $daftar_bulan[(int) $dt_masuk->format('n')];
                $thn_m      = $dt_masuk->format('Y');
                $jam_m      = $dt_masuk->format('H:i');
                $info_login_teks = "{$hari_masuk}, {$tgl_m} {$bln_m} {$thn_m} pukul {$jam_m} {$label_zona}";
            } catch (Exception $e) {
                $info_login_teks = $pengguna->terakhir_masuk;
            }
        }

        // Teks perhitungan selisih Papua (WIT) relatif dari zona pengguna yang sedang login
        $selisih_wit_teks = '';
        if ($label_zona === 'WITA')
        {
            $selisih_wit_teks = "Karena pengguna sedang berada di zona WITA (pukul {$jam_user} WITA), maka waktu di Papua (WIT / UTC+9) adalah lebih cepat 1 jam (+1 jam): {$jam_user} WITA + 1 jam = {$jam_wit} WIT.";
        }
        elseif ($label_zona === 'WIB')
        {
            $selisih_wit_teks = "Karena pengguna sedang berada di zona WIB (pukul {$jam_user} WIB), maka waktu di Papua (WIT / UTC+9) adalah lebih cepat 2 jam (+2 jam): {$jam_user} WIB + 2 jam = {$jam_wit} WIT.";
        }
        elseif ($label_zona === 'WIT')
        {
            $selisih_wit_teks = "Pengguna sudah berada di zona waktu yang sama dengan Papua (WIT), yaitu pukul {$jam_user} WIT.";
        }
        else
        {
            $selisih_wit_teks = "Waktu di Papua (WIT / UTC+9) saat ini adalah {$jam_wit} WIT.";
        }

        return "---
### KONTEKS WAKTU REAL-TIME & PENGGUNA YANG SEDANG LOGIN:
Data Pengguna yang Sedang Login:
- Nama Pengguna: {$nama_pengguna}
- Email: {$email_pengguna}
- Waktu Terakhir Masuk / Login Sesi: {$info_login_teks}

Waktu Lokal Pengguna yang Sedang Login Saat Ini:
- Zona Waktu Pengguna: {$label_zona} ({$zona_nama})
- Hari & Tanggal Pengguna: {$tgl_lengkap_user}
- Jam Lokal Pengguna Saat Ini: {$jam_user} {$label_zona}

Waktu di Zona Lain (Data Referensi):
- Waktu Indonesia Barat (WIB / UTC+7): {$jam_wib} WIB
- Waktu Indonesia Tengah (WITA / UTC+8): {$jam_wita} WITA
- Waktu Indonesia Timur (WIT / UTC+9): {$jam_wit} WIT

ATURAN MENJAWAB PERTANYAAN (SANGAT PENTING):
1. **JAWAB BIASA & SESUAI YANG DITANYA SAJA**:
   - Jika pengguna bertanya hal biasa/sederhana seperti \"sekarang jam berapa?\", \"jam berapa sekarang?\", atau menyapa pagi/siang/sore/malam, JAWAB BIASA SAJA secara wajar, ringkas, dan langsung to-the-point: pukul **{$jam_user} {$label_zona}** (contoh jawaban wajar: \"Saat ini pukul {$jam_user} {$label_zona}.\").
   - JANGAN PERNAH menambahkan kalimat pembuka atau penutup template/hafalan seperti:
     \"Waktu ini mengikuti zona waktu lokal Anda ({$nama_pengguna}) yang sedang login di zona {$label_zona} ({$zona_nama}).\"
   - JANGAN PERNAH memberikan info tambahan yang tidak ditanyakan seperti:
     \"Sebagai informasi tambahan untuk waktu di seluruh Indonesia saat ini: WIB ..., WITA ..., WIT ...\"
   - JANGAN menjawab panjang lebar atau membeberkan perhitungan selisih KECUALI jika pengguna secara spesifik meminta perbandingan zona waktu atau menyuruh menjelaskan secara mendalam. Jika cuma tanya biasa, jawab sesuai yang ditanya saja!
2. **JIKA DITANYA WAKTU DI DAERAH TERTENTU (Contoh: Papua)**:
   - Jawab langsung jam saat ini di daerah yang ditanyakan secara singkat (contoh: \"Di Papua saat ini pukul {$jam_wit} WIT.\").
   - HANYA jelaskan selisih ({$selisih_wit_teks}) jika pengguna secara spesifik meminta penjelasan selisih atau disuruh mendalam.
3. **JIKA DITANYA WAKTU LOGIN / SESI**:
   - Jawab langsung sesuai waktu login pengguna: **{$info_login_teks}**.
4. **JANGAN PERNAH menolak atau berkata 'Saya tidak memiliki akses ke waktu real-time'**.";
    }

    /**
     * Ambil isi system prompt dari berkas (default lengkap, atau ringkas jika tipe = 'ringkas').
     */
    public function ambil_prompt_sistem($tipe = 'lengkap', $pengguna = NULL, $info_klien = array())
    {
        if ($tipe === 'ringkas')
        {
            return $this->ambil_prompt_sistem_ringkas($pengguna, $info_klien);
        }
        return $this->ambil_prompt_sistem_lengkap($pengguna, $info_klien);
    }

    /**
     * Cek apakah pesan pengguna merupakan sapaan murni, pertanyaan waktu, atau ucapan santai singkat.
     *
     * @param string $pesan
     * @return bool
     */
    public function apakah_sapaan_atau_waktu($pesan)
    {
        $pesan_teks = strtolower(trim((string) $pesan));
        if (empty($pesan_teks))
        {
            return TRUE;
        }

        $pola_sapaan_ringkas = '/^(?:'
            . 'halo|hai|hei|hi|pagi|siang|sore|malam|'
            . 'selamat pagi|selamat siang|selamat sore|selamat malam|'
            . 'apa kabar|assalamualaikum|tes|test|ping|'
            . 'kamu siapa|siapa kamu|siapa namamu|kamu bisa apa|'
            . 'jam berapa|sekarang jam berapa|jam berapa sekarang|'
            . 'terima kasih|makasih|thanks|thank you|makasi|'
            . 'ok|oke|siap|baiklah|sip|y|ya|oke siap|noted'
            . ')[\s\.,\!\?]*$/i';

        return (bool) preg_match($pola_sapaan_ringkas, $pesan_teks);
    }

    /**
     * Cek apakah pesan pengguna merupakan permintaan penyusunan dokumen PRD atau perancangan produk/aplikasi.
     *
     * @param string $pesan
     * @param string $lampiran_konten
     * @return bool
     */
    public function apakah_permintaan_prd($pesan, $lampiran_konten = '')
    {
        if ( ! empty($lampiran_konten))
        {
            return TRUE;
        }

        $pesan_teks = strtolower(trim((string) $pesan));
        if (empty($pesan_teks))
        {
            return FALSE;
        }

        // Kata kunci eksplisit PRD, spesifikasi, dan arsitektur produk
        $pola_prd_arsitektur = '/\b(?:'
            . 'prd|product requirement|buatkan prd|bikin prd|susun prd|buat prd|rancang prd|tulis prd|'
            . 'spesifikasi|dokumen spesifikasi|spesifikasi kebutuhan|arsitektur sistem|arsitektur aplikasi|'
            . 'rancang sistem|buatkan sistem|bikin sistem|rancang aplikasi|buatkan aplikasi|bikin aplikasi|'
            . 'skema database|database schema|flowchart|alur sistem|diagram mermaid|'
            . 'erd|entity relationship|api specification|acceptance criteria|kriteria penerimaan|'
            . 'user story|use case|roadmap|rencana kerja|fase pengembangan|'
            . 'audit prd|evaluasi prd|koreksi prd|tinjau prd'
            . ')\b/i';

        if (preg_match($pola_prd_arsitektur, $pesan_teks))
        {
            return TRUE;
        }

        // Cek jika pengguna meminta revisi atau perubahan fitur PRD
        $pola_revisi = '/\b(?:'
            . 'tambahkan fitur|tambah fitur|ubah bagian|revisi prd|perbarui prd|tambah modul|'
            . 'ganti alur|masukkan fitur ke prd|perbaiki alur|tambahkan tabel|ubah bab prd'
            . ')\b/i';

        if (preg_match($pola_revisi, $pesan_teks))
        {
            return TRUE;
        }

        // Deteksi kombinasi kata kerja pembuat produk + kata benda sistem/aplikasi/modul
        $ada_kata_kerja = (bool) preg_match('/\b(?:buat|buatkan|bikin|rancang|kembangkan|bangun|desain|susun)\b/i', $pesan_teks);
        $ada_kata_produk = (bool) preg_match('/\b(?:aplikasi|sistem|web|website|platform|software|app|modul|portal|toko|kasir|pos|e-commerce|klinik|dashboard|crm|erp|saas)\b/i', $pesan_teks);

        if ($ada_kata_kerja && $ada_kata_produk)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Deteksi cerdas apakah masukan pengguna merupakan mode mendalam / kompleks
     *
     * @param string $pesan
     * @param string $lampiran_konten
     * @param bool   $mode_berpikir
     * @param bool   $is_lanjutan
     * @return bool
     */
    public function apakah_pertanyaan_kompleks($pesan, $lampiran_konten = '', $mode_berpikir = FALSE, $is_lanjutan = TRUE)
    {
        // 1. Jika mode berpikir aktif atau ada lampiran dokumen, otomatis masuk kategori mendalam
        if ($mode_berpikir || ! empty($lampiran_konten))
        {
            return TRUE;
        }

        // 2. Sapaan murni tidak pernah kompleks
        if ($this->apakah_sapaan_atau_waktu($pesan))
        {
            return FALSE;
        }

        // 3. Permintaan spesifikasi PRD eksplisit
        return $this->apakah_permintaan_prd($pesan, $lampiran_konten);
    }

    /**
     * Bersihkan input pengguna dari karakter kontrol berbahaya.
     */
    protected function bersihkan_masukan($teks)
    {
        $bersih = buang_karakter_kontrol((string) $teks);
        $bersih = str_ireplace(array('<masukan_pengguna>', '</masukan_pengguna>'), '', $bersih);
        return trim($bersih);
    }

    /**
     * Rakit prompt lengkap dari data wizard 4 langkah.
     *
     * @param array $wizard Data formulir 4 langkah
     * @return array ['prompt_sistem' => string, 'prompt_pengguna' => string]
     */
    public function rakit($wizard, $pengguna = NULL, $info_klien = array())
    {
        $prompt_sistem = $this->ambil_prompt_sistem('lengkap', $pengguna, $info_klien);

        $nama_produk      = $this->bersihkan_masukan(isset($wizard['nama_produk']) ? $wizard['nama_produk'] : '');
        $jenis_produk     = $this->bersihkan_masukan(isset($wizard['jenis_produk']) ? $wizard['jenis_produk'] : '');
        $deskripsi        = $this->bersihkan_masukan(isset($wizard['deskripsi_singkat']) ? $wizard['deskripsi_singkat'] : '');
        $masalah          = $this->bersihkan_masukan(isset($wizard['masalah']) ? $wizard['masalah'] : '');
        $target_pengguna  = $this->bersihkan_masukan(isset($wizard['target_pengguna']) ? $wizard['target_pengguna'] : '');

        $tujuan_list = array();
        if (isset($wizard['tujuan']) && is_array($wizard['tujuan']))
        {
            foreach ($wizard['tujuan'] as $t)
            {
                $t_bersih = $this->bersihkan_masukan($t);
                if ( ! empty($t_bersih))
                {
                    $tujuan_list[] = '- ' . $t_bersih;
                }
            }
        }
        $tujuan_teks = empty($tujuan_list) ? '- Tidak ditentukan secara khusus' : implode("\n", $tujuan_list);

        $fitur_list = array();
        if (isset($wizard['fitur_utama']) && is_array($wizard['fitur_utama']))
        {
            foreach ($wizard['fitur_utama'] as $f)
            {
                $f_bersih = $this->bersihkan_masukan($f);
                if ( ! empty($f_bersih))
                {
                    $fitur_list[] = '- ' . $f_bersih;
                }
            }
        }
        $fitur_teks = empty($fitur_list) ? '- Fitur standar sesuai kebutuhan' : implode("\n", $fitur_list);

        $teknologi        = $this->bersihkan_masukan(isset($wizard['teknologi']) ? $wizard['teknologi'] : 'Bebas, pilih yang paling sesuai');
        $halaman          = $this->bersihkan_masukan(isset($wizard['halaman_diperlukan']) ? $wizard['halaman_diperlukan'] : '-');
        $keamanan_pilihan = isset($wizard['kebutuhan_keamanan']) && is_array($wizard['kebutuhan_keamanan']) ? implode(', ', $wizard['kebutuhan_keamanan']) : '-';
        $gaya_tampilan    = $this->bersihkan_masukan(isset($wizard['gaya_tampilan']) ? $wizard['gaya_tampilan'] : 'Modern bersih');
        $catatan_tampilan = $this->bersihkan_masukan(isset($wizard['catatan_tampilan']) ? $wizard['catatan_tampilan'] : '-');

        $tingkat_detail   = $this->bersihkan_masukan(isset($wizard['tingkat_detail']) ? $wizard['tingkat_detail'] : 'Sangat rinci');
        $ditujukan_untuk  = $this->bersihkan_masukan(isset($wizard['ditujukan_untuk']) ? $wizard['ditujukan_untuk'] : 'AI coding agent (Google Antigravity)');
        $flowchart        = (!empty($wizard['sertakan_flowchart']) && $wizard['sertakan_flowchart'] === 'ya') ? 'Ya' : 'Tidak';
        $struktur_file    = (!empty($wizard['sertakan_struktur_file']) && $wizard['sertakan_struktur_file'] === 'ya') ? 'Ya' : 'Tidak';

        $prompt_pengguna = <<<PROMPT
Buatkan PRD lengkap untuk produk berikut sesuai struktur wajib khusus Google Antigravity (tanpa Rencana Rilis, sertakan Pohon Rencana Pengerjaan Bertahap Fase-Fitur-Subfitur):

Nama Produk: {$nama_produk}
Jenis Produk: {$jenis_produk}
Deskripsi Singkat: {$deskripsi}

Masalah yang Diselesaikan:
{$masalah}

Target Pengguna:
{$target_pengguna}

Tujuan Utama:
{$tujuan_teks}

Fitur-Fitur Utama:
{$fitur_teks}

Preferensi Teknologi:
{$teknologi}

Halaman / Alur yang Diperlukan:
{$halaman}

Kebutuhan Keamanan:
{$keamanan_pilihan}

Gaya Antarmuka:
{$gaya_tampilan} (Catatan: {$catatan_tampilan})

Parameter Dokumen:
- Tingkat Detail: {$tingkat_detail}
- Ditujukan Untuk: {$ditujukan_untuk}
- Sertakan Flowchart Mermaid: {$flowchart}
- Sertakan Struktur File: {$struktur_file}
PROMPT;

        return array(
            'prompt_sistem'   => $prompt_sistem,
            'prompt_pengguna' => trim($prompt_pengguna),
        );
    }

    /**
     * Rakit prompt dari masukan ringkas ruang kerja chat.
     *
     * @param string $pesan_pengguna Masukan pengguna
     * @param array  $opsi           Opsi tambahan (lampiran_nama, lampiran_konten, dll.)
     * @return array ['prompt_sistem' => string, 'prompt_pengguna' => string]
     */
    public function rakit_singkat($pesan_pengguna, $opsi = array())
    {
        $prompt_sistem = $this->ambil_prompt_sistem();
        $pesan_bersih  = $this->bersihkan_masukan($pesan_pengguna);

        $bagian_lampiran = '';
        if ( ! empty($opsi['lampiran_konten']))
        {
            $nama_berkas   = ! empty($opsi['lampiran_nama']) ? $this->bersihkan_masukan($opsi['lampiran_nama']) : 'Dokumen';
            $konten_berkas = buang_karakter_kontrol($opsi['lampiran_konten']);
            $konten_berkas = str_ireplace(array('<dokumen_konteks>', '</dokumen_konteks>'), '', $konten_berkas);

            $bagian_lampiran = "<dokumen_konteks nama=\"{$nama_berkas}\">\n" . $konten_berkas . "\n</dokumen_konteks>\n\n";
        }

        $prompt_pengguna = $bagian_lampiran . "[INSTRUKSI SPESIFIK PENGGUNA - PRIORITAS UTAMA, HARUS DIIKUTI MENGALAHKAN ATURAN DEFAULT]:\n" . $pesan_bersih;

        return array(
            'prompt_sistem'   => $prompt_sistem,
            'prompt_pengguna' => trim($prompt_pengguna),
        );
    }
}
