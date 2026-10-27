<?php
/**
 * PRDAI — Pendaftaran hook CodeIgniter.
 *
 * Header keamanan dipasang pada setiap respons lewat hook ini. Dijalankan di
 * post_controller_constructor agar kelas Output sudah tersedia.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['post_controller_constructor'] = array(
	'class'    => 'Header_keamanan',
	'function' => 'pasang',
	'filename' => 'Header_keamanan.php',
	'filepath' => 'hooks',
	'params'   => array(),
);