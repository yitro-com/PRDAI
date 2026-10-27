<?php
/**
 * PRDAI — Helper tampilan.
 *
 * Fungsi pemformatan untuk kebutuhan antarmuka (tanggal, pemotongan teks).
 * Semua teks keluaran tetap harus di-escape di view memakai e().
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('format_tanggal_indonesia'))
{
    /**
     * Ubah datetime basis data ("Y-m-d H:i:s") menjadi tanggal Indonesia.
     *
     * Contoh keluaran: "3 Oktober 2024". Bila $dengan_jam true, ditambah
     * ", 14:30". Berguna untuk kolom "Diperbarui" di dasbor.
     */
    function format_tanggal_indonesia($datetime, $dengan_jam = FALSE)
    {
        if (empty($datetime))
        {
            return '-';
        }

        $cap_waktu = strtotime($datetime);
        if ($cap_waktu === FALSE)
        {
            return '-';
        }

        $bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        );

        $teks = (int) date('j', $cap_waktu) . ' '
              . $bulan[(int) date('n', $cap_waktu)] . ' '
              . date('Y', $cap_waktu);

        if ($dengan_jam)
        {
            $teks .= ', ' . date('H:i', $cap_waktu);
        }

        return $teks;
    }
}

if ( ! function_exists('potong_teks'))
{
    /**
     * Potong teks menjadi maksimal $panjang karakter, tambahkan elipsis.
     *
     * Kenapa: ringkasan PRD di dasbor tidak boleh merusak tata letak. Potongan
     * dilakukan pada batas kata bila memungkinkan.
     */
    function potong_teks($teks, $panjang = 120)
    {
        $teks = trim(preg_replace('/\s+/', ' ', (string) $teks));

        if (mb_strlen($teks) <= $panjang)
        {
            return $teks;
        }

        $potong = mb_substr($teks, 0, $panjang);
        $spasi  = mb_strrpos($potong, ' ');

        if ($spasi !== FALSE && $spasi > 0)
        {
            $potong = mb_substr($potong, 0, $spasi);
        }

        return $potong . '…';
    }
}
