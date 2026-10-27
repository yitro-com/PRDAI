<?php
/**
 * PRDAI — Pemroses Markdown Cerdas (Pemroses_markdown).
 *
 * Mengonversi Markdown menjadi HTML aman, melakukan perbaikan sintaks otomatis (self-healing)
 * untuk diagram Mermaid, dan memperbaiki format Markdown yang terpotong.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Pemroses_markdown
{
    protected $parsedown;

    public function __construct()
    {
        if (class_exists('Parsedown'))
        {
            $this->parsedown = new Parsedown();
            $this->parsedown->setSafeMode(TRUE);
        }
    }

    /**
     * Konversi string Markdown menjadi string HTML aman dengan perbaikan otomatis.
     *
     * @param string $markdown Teks dalam format Markdown
     * @return string HTML siap tampil
     */
    public function ubah_ke_html($markdown)
    {
        if (empty($markdown))
        {
            return '';
        }

        // 1. Perbaiki markdown jika terpotong oleh batasan token AI
        $markdown = $this->perbaiki_markdown_terpotong($markdown);

        // 2. Sanitasi sintaks Mermaid agar tidak crash saat dirender di peramban
        $markdown = $this->sanitasi_sintaks_mermaid($markdown);

        if ($this->parsedown)
        {
            $html = $this->parsedown->text((string) $markdown);
            // Decode tag <br> aman yang sering dihasilkan AI di dalam sel tabel markdown
            $html = preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $html);
            return $html;
        }

        return nl2br(html_escape((string) $markdown));
    }

    /**
     * Sanitasi dan perbaiki kesalahan sintaks umum diagram Mermaid yang dihasilkan AI.
     * Mencegah "Syntax error in graph" pada flowchart dan sequence diagram.
     *
     * @param string $markdown
     * @return string
     */
    public function sanitasi_sintaks_mermaid($markdown)
    {
        if (empty($markdown) || strpos($markdown, '```mermaid') === FALSE)
        {
            return $markdown;
        }

        return preg_replace_callback('/```mermaid\s*\n([\s\S]*?)```/', function ($matches) {
            $kode = trim($matches[1]);
            if (empty($kode))
            {
                return "```mermaid\nflowchart TD\n    A[Mulai] --> B[Selesai]\n```";
            }

            $baris_array = explode("\n", $kode);
            $baris_bersih = array();

            foreach ($baris_array as $index => $baris)
            {
                $baris_trim = trim($baris);

                // Abaikan baris kosong di awal
                if ($index === 0 && empty($baris_trim))
                {
                    continue;
                }

                // Amankan label dalam tanda kurung siku: Node[Label (Dengan Kurung)] -> Node["Label (Dengan Kurung)"]
                // Hanya bungkus jika belum diawali dan diakhiri dengan tanda kutip dua
                $baris = preg_replace_callback('/(\b[A-Za-z0-9_]+)\[([^"\]]+)\]/', function ($m) {
                    $id_node = $m[1];
                    $label = trim($m[2]);

                    // Jika label mengandung karakter khusus (kurung, strip, slash, petik tunggal, dll)
                    $label_aman = str_replace('"', "'", $label);
                    return $id_node . '["' . $label_aman . '"]';
                }, $baris);

                // Amankan label dalam node keputusan belah ketupat: Node{Label (Keputusan)?} -> Node{"Label (Keputusan)?"}
                $baris = preg_replace_callback('/(\b[A-Za-z0-9_]+)\{([^"\}]+)\}/', function ($m) {
                    $id_node = $m[1];
                    $label = trim($m[2]);
                    $label_aman = str_replace('"', "'", $label);
                    return $id_node . '{"' . $label_aman . '"}';
                }, $baris);

                // Amankan label pada panah: -- Ya (Valid) --> menjadi -- "Ya (Valid)" -->
                $baris = preg_replace_callback('/(--|==)\s*([^\-"\r\n>]+?)\s*(-->|==>)/', function ($m) {
                    $prefix = $m[1];
                    $label = trim($m[2]);
                    $suffix = $m[3];
                    if (!empty($label) && strpos($label, '"') === FALSE)
                    {
                        return $prefix . ' "' . str_replace('"', "'", $label) . '" ' . $suffix;
                    }
                    return $m[0];
                }, $baris);

                // Perbaiki node ID yang tidak sengaja mengandung spasi: misal `Fase 1["Fitur"]` -> `Fase_1["Fitur"]`
                $baris = preg_replace('/(\b[A-Za-z0-9]+)\s+([A-Za-z0-9]+)(\[|\{)/', '$1_$2$3', $baris);

                $baris_bersih[] = $baris;
            }

            return "```mermaid\n" . implode("\n", $baris_bersih) . "\n```";
        }, $markdown);
    }

    /**
     * Deteksi dan perbaiki otomatis Markdown yang terpotong jika batas token AI tercapai.
     * Mencegah tampilan rusak akibat unclosed codeblock atau tag tabel patah.
     *
     * @param string $markdown
     * @return string
     */
    public function perbaiki_markdown_terpotong($markdown)
    {
        if (empty($markdown))
        {
            return '';
        }

        $teks = (string) $markdown;

        // 1. Cek apakah ada blok kode triple-backtick yang belum ditutup
        $jumlah_backtick = substr_count($teks, '```');
        if ($jumlah_backtick % 2 !== 0)
        {
            // Tambahkan penutup code block
            $teks .= "\n```\n";
        }

        // 2. Cek apakah ada blok kode inline single-backtick yang ganjil di baris terakhir
        $baris_terakhir = trim(substr($teks, (int) strrpos($teks, "\n")));
        if (substr_count($baris_terakhir, '`') % 2 !== 0)
        {
            $teks .= '`';
        }

        return $teks;
    }
}
