<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;

// Nilai koneksi diambil dari berkas .env (bagian Basis data).
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => function_exists('env') ? env('DB_HOST', '127.0.0.1') : '127.0.0.1',
	'username' => function_exists('env') ? env('DB_PENGGUNA', '') : '',
	'password' => function_exists('env') ? env('DB_KATA_SANDI', '') : '',
	'database' => function_exists('env') ? env('DB_NAMA', 'prdai') : 'prdai',
	'port'     => function_exists('env') ? (int) env('DB_PORT', 3306) : 3306,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_unicode_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
