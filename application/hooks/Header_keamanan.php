<?php
/**
 * PRDAI — Hook header keamanan HTTP.
 *
 * Memasang header keamanan pada setiap respons HTTP untuk melindungi aplikasi
 * dari serangan clickjacking, MIME-sniffing, XSS, dan kebocoran data.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Header_keamanan
{
    /**
     * Pasang header keamanan pada objek Output CI.
     */
    public function pasang()
    {
        $ci =& get_instance();

        // 1. Content Security Policy (CSP)
        // style-src membolehkan unsafe-inline karena Mermaid menyisipkan gaya inline pada SVG diagram.
        $csp = "default-src 'self'; "
             . "script-src 'self'; "
             . "style-src 'self' 'unsafe-inline'; "
             . "img-src 'self' data:; "
             . "font-src 'self'; "
             . "connect-src 'self'; "
             . "object-src 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self'; "
             . "frame-ancestors 'none'";

        $ci->output->set_header('Content-Security-Policy: ' . $csp);
        $ci->output->set_header('X-Content-Type-Options: nosniff');
        $ci->output->set_header('X-Frame-Options: DENY');
        $ci->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
        $ci->output->set_header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        // HSTS hanya di lingkungan produksi dan bila menggunakan HTTPS.
        if (ENVIRONMENT === 'production' && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        {
            $ci->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // Jangan simpan cache bila pengguna sudah masuk sesi.
        if (isset($ci->session) && $ci->session->userdata('pengguna_id'))
        {
            $ci->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $ci->output->set_header('Pragma: no-cache');
        }
    }
}
