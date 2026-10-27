<?php
/**
 * PRDAI — Helper respons JSON.
 *
 * Membungkus keluaran JSON agar format konsisten antara server dan JavaScript,
 * serta selalu menyertakan token CSRF baru (karena csrf_regenerate = TRUE).
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('kirim_json'))
{
    /**
     * Kirim respons JSON sukses dan hentikan eksekusi.
     *
     * Selalu menyertakan "csrf" berisi token terbaru, supaya permintaan AJAX
     * berikutnya memakai token yang valid (CI3 merotasi token tiap permintaan).
     */
    function kirim_json($data = array(), $kode_http = 200)
    {
        $ci =& get_instance();

        $data['csrf'] = $ci->security->get_csrf_hash();

        $ci->output
            ->set_status_header($kode_http)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // Hentikan eksekusi agar tidak ada keluaran tambahan yang merusak JSON.
        $ci->output->_display();
        exit;
    }
}

if ( ! function_exists('kirim_galat_json'))
{
    /**
     * Kirim respons JSON galat dengan format seragam dan hentikan eksekusi.
     *
     * Format: { "berhasil": false, "pesan": "...", "galat": {kolom: pesan}, "csrf": "..." }
     */
    function kirim_galat_json($pesan = 'Terjadi kesalahan.', $galat = array(), $kode_http = 400)
    {
        kirim_json(array(
            'berhasil' => FALSE,
            'pesan'    => $pesan,
            'galat'    => is_array($galat) ? $galat : array(),
        ), $kode_http);
    }
}
