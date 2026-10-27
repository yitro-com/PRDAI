<?php
/**
 * PRDAI — Model pengguna.
 *
 * Mengelola data akun pengguna, pencarian email, dan pembaruan profil/sandi.
 * Seluruh query menggunakan Query Builder CI3 untuk mencegah injeksi SQL.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengguna_model extends CI_Model
{
    protected $tabel = 'pengguna';

    /**
     * Cari pengguna berdasarkan ID.
     */
    public function ambil_berdasarkan_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->get($this->tabel)
            ->row();
    }

    /**
     * Cari pengguna berdasarkan alamat email (huruf kecil).
     */
    public function ambil_berdasarkan_email($email)
    {
        return $this->db
            ->where('email', strtolower(trim($email)))
            ->get($this->tabel)
            ->row();
    }

    /**
     * Tambah pengguna baru.
     */
    public function tambah($data)
    {
        $waktu = date('Y-m-d H:i:s');
        $baris = array(
            'nama'            => trim($data['nama']),
            'email'           => strtolower(trim($data['email'])),
            'kata_sandi'      => $data['kata_sandi'],
            'peran'           => isset($data['peran']) ? $data['peran'] : 'pengguna',
            'status'          => isset($data['status']) ? $data['status'] : 'aktif',
            'terakhir_masuk'  => NULL,
            'dibuat_pada'     => $waktu,
            'diperbarui_pada' => $waktu,
        );

        $this->db->insert($this->tabel, $baris);
        return $this->db->insert_id();
    }

    /**
     * Catat waktu terakhir pengguna masuk.
     */
    public function catat_terakhir_masuk($id, $zona_waktu = NULL)
    {
        $data = array(
            'terakhir_masuk'  => date('Y-m-d H:i:s'),
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        );

        if ($zona_waktu !== NULL && ! empty($zona_waktu))
        {
            $data['zona_waktu'] = trim($zona_waktu);
        }

        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, $data);
    }

    /**
     * Perbarui zona waktu pengguna.
     */
    public function perbarui_zona_waktu($id, $zona_waktu)
    {
        if (empty($zona_waktu))
        {
            return FALSE;
        }

        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, array(
                'zona_waktu'      => trim($zona_waktu),
                'diperbarui_pada' => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Perbarui data profil (nama dan nomor telepon).
     */
    public function perbarui_profil($id, $nama, $telepon = NULL)
    {
        $data = array(
            'nama'            => trim($nama),
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        );

        if ($telepon !== NULL)
        {
            $data['telepon'] = trim($telepon);
        }

        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, $data);
    }

    /**
     * Perbarui kata sandi hash.
     */
    public function perbarui_sandi($id, $hash_sandi_baru)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, array(
                'kata_sandi'      => $hash_sandi_baru,
                'diperbarui_pada' => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Simpan token "Ingat saya" ke basis data terkunci IP.
     */
    public function simpan_token_ingat($id, $token_hash, $kadaluarsa, $ip_terkunci = NULL)
    {
        $data = array(
            'token_ingat'      => $token_hash,
            'ingat_kadaluarsa' => $kadaluarsa,
            'diperbarui_pada'  => date('Y-m-d H:i:s'),
        );

        if ($ip_terkunci !== NULL)
        {
            $data['ip_terkunci'] = trim($ip_terkunci);
        }

        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, $data);
    }

    /**
     * Hapus token "Ingat saya" saat keluar.
     */
    public function hapus_token_ingat($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->tabel, array(
                'token_ingat'      => NULL,
                'ip_terkunci'      => NULL,
                'ingat_kadaluarsa' => NULL,
                'diperbarui_pada'  => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Cari pengguna berdasarkan token "Ingat saya" yang masih berlaku dan mencocokkan IP terkunci.
     */
    public function ambil_berdasarkan_token_ingat($id, $token_hash, $ip_terkunci = NULL)
    {
        $this->db
            ->where('id', (int) $id)
            ->where('token_ingat', $token_hash)
            ->where('ingat_kadaluarsa >=', date('Y-m-d H:i:s'))
            ->where('status', 'aktif');

        if ($ip_terkunci !== NULL)
        {
            $ip_bersih = trim($ip_terkunci);
            $this->db->group_start()
                ->where('ip_terkunci', $ip_bersih)
                ->or_where('ip_terkunci IS NULL', NULL, FALSE);

            // Toleransi representasi IPv4 dan IPv6 pada mesin lokal (localhost)
            if ($ip_bersih === '127.0.0.1')
            {
                $this->db->or_where('ip_terkunci', '::1');
            }
            elseif ($ip_bersih === '::1')
            {
                $this->db->or_where('ip_terkunci', '127.0.0.1');
            }

            $this->db->group_end();
        }

        return $this->db
            ->get($this->tabel)
            ->row();
    }

    /**
     * Ambil atau buat akun pengguna lewat login sosial (Google / GitHub).
     */
    public function ambil_atau_buat_sosial($provider, $provider_id, $nama, $email, $avatar = NULL)
    {
        $email = strtolower(trim($email));
        $pengguna = $this->ambil_berdasarkan_email($email);

        if ($pengguna)
        {
            // Perbarui info provider jika belum terhubung
            $update = array(
                'provider'        => $provider,
                'provider_id'     => $provider_id,
                'diperbarui_pada' => date('Y-m-d H:i:s'),
            );
            if ( ! empty($avatar) && empty($pengguna->avatar))
            {
                $update['avatar'] = $avatar;
            }
            $this->db->where('id', $pengguna->id)->update($this->tabel, $update);
            return $pengguna;
        }

        // Buat akun baru jika belum terdaftar
        $waktu = date('Y-m-d H:i:s');
        $data_baru = array(
            'nama'             => trim($nama),
            'email'            => $email,
            'kata_sandi'       => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'provider'         => $provider,
            'provider_id'      => $provider_id,
            'avatar'           => $avatar,
            'peran'            => 'pengguna',
            'status'           => 'aktif',
            'terakhir_masuk'   => $waktu,
            'dibuat_pada'      => $waktu,
            'diperbarui_pada'  => $waktu,
        );

        $this->db->insert($this->tabel, $data_baru);
        $id_baru = $this->db->insert_id();

        return $this->ambil_berdasarkan_id($id_baru);
    }
}
