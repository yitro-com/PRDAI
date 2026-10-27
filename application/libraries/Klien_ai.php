<?php
/**
 * PRDAI — Klien komunikasi API AI (Klien_ai).
 *
 * Menghubungkan aplikasi ke model AI (Ollama lokal, Anthropic, OpenAI, Gemini, Groq, OpenRouter)
 * melalui cURL aman dari sisi server. Kunci API tidak pernah bocor ke peramban.
 *
 * Seluruh jawaban berasal murni dari model AI tanpa jawaban hardcode/template.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien_ai
{
    protected $penyedia;
    protected $kunci_api;
    protected $model;
    protected $maks_token;
    protected $batas_waktu;
    protected $ollama_url;
    protected $auto_fallback;
    protected $groq_key;
    protected $groq_model;
    protected $gemini_key;
    protected $gemini_model;
    protected $ollama_model;

    public function __construct()
    {
        $this->penyedia      = function_exists('env') ? strtolower((string) env('AI_PENYEDIA', 'groq')) : 'groq';
        $this->kunci_api     = function_exists('env') ? (string) env('AI_API_KEY', '') : '';
        $this->model         = function_exists('env') ? (string) env('AI_MODEL', '') : '';
        $this->maks_token    = function_exists('env') ? (int) env('AI_MAKS_TOKEN', 8192) : 8192;
        $this->batas_waktu   = function_exists('env') ? (int) env('AI_TIMEOUT_DETIK', 180) : 180;
        $this->ollama_url    = function_exists('env') ? (string) env('AI_OLLAMA_URL', 'http://127.0.0.1:11434') : 'http://127.0.0.1:11434';
        $this->auto_fallback = function_exists('env') ? filter_var(env('AI_AUTO_FALLBACK', TRUE), FILTER_VALIDATE_BOOLEAN) : TRUE;

        // Kunci dan model spesifik per penyedia
        $this->groq_key      = function_exists('env') ? (string) (env('GROQ_API_KEY') ?: $this->kunci_api) : '';
        $this->groq_model    = function_exists('env') ? (string) env('GROQ_MODEL', 'qwen/qwen3.8-27b') : 'qwen/qwen3.8-27b';

        $this->gemini_key    = function_exists('env') ? (string) env('GEMINI_API_KEY', '') : '';
        $this->gemini_model  = function_exists('env') ? (string) env('GEMINI_MODEL', 'gemini-3.8-flash') : 'gemini-3.8-flash';

        $this->ollama_model  = function_exists('env') ? (string) env('OLLAMA_MODEL', 'qwen2.5-coder:7b') : 'qwen2.5-coder:7b';

        // Periksa kunci API spesifik jika AI_API_KEY belum terisi
        if (empty($this->kunci_api) && function_exists('env'))
        {
            if ( ! empty($this->groq_key))
            {
                $this->kunci_api = $this->groq_key;
                $this->penyedia  = 'groq';
            }
            elseif ( ! empty($this->gemini_key))
            {
                $this->kunci_api = $this->gemini_key;
                $this->penyedia  = 'gemini';
            }
            elseif (env('OPENROUTER_API_KEY'))
            {
                $this->kunci_api = env('OPENROUTER_API_KEY');
                $this->penyedia  = 'openrouter';
            }
            elseif (env('OPENAI_API_KEY'))
            {
                $this->kunci_api = env('OPENAI_API_KEY');
                $this->penyedia  = 'openai';
            }
            elseif (env('ANTHROPIC_API_KEY'))
            {
                $this->kunci_api = env('ANTHROPIC_API_KEY');
                $this->penyedia  = 'anthropic';
            }
        }
    }

    /**
     * Susun daftar pesan terstruktur dengan urutan:
     * 1. System prompt
     * 2. Riwayat percakapan (user dan assistant sebelumnya)
     * 3. Pesan terbaru pengguna
     *
     * @param string $prompt_sistem
     * @param string $prompt_pengguna
     * @param array  $riwayat_percakapan
    /**
     * Susun daftar pesan terstruktur dengan urutan:
     * 1. System prompt
     * 2. Riwayat percakapan (user dan assistant sebelumnya)
     * 3. Pesan terbaru pengguna
     *
     * @param string $prompt_sistem
     * @param string $prompt_pengguna
     * @param array  $riwayat_percakapan
     * @param array  $opsi Opsi perakitan (ringkas untuk pertanyaan biasa)
     * @return array
     */
    protected function bangun_daftar_pesan($prompt_sistem, $prompt_pengguna, $riwayat_percakapan = array(), $opsi = array())
    {
        $messages = array();

        if ( ! empty($prompt_sistem))
        {
            $messages[] = array(
                'role'    => 'system',
                'content' => (string) $prompt_sistem,
            );
        }

        if ( ! empty($riwayat_percakapan) && is_array($riwayat_percakapan))
        {
            $tier = isset($opsi['tier']) ? strtolower($opsi['tier']) : 'pro';
            $apakah_ringkas = ! empty($opsi['ringkas']);

            if ($tier === 'pro')
            {
                // AKUN PRO: MEMORI MAKSIMAL & PENYIMPANAN 100 GB INGATAN RIWAYAT LENGKAP SEMUANYA
                // Mengambil seluruh riwayat percakapan tanpa pemotongan
                $terpilih = $riwayat_percakapan;
            }
            elseif ($tier === 'plus')
            {
                // Paket Plus: hingga 20 pesan riwayat (20 GB memory)
                $terpilih = array_slice($riwayat_percakapan, -20);
            }
            elseif ($tier === 'go')
            {
                // Paket Go: hingga 8 pesan riwayat (2 GB memory)
                $terpilih = array_slice($riwayat_percakapan, -8);
            }
            else
            {
                // Paket Free: 4 pesan riwayat (500 MB memory)
                $terpilih = array_slice($riwayat_percakapan, -4);
            }

            foreach ($terpilih as $item)
            {
                $peran = isset($item['role']) ? $item['role'] : (isset($item['peran']) ? $item['peran'] : 'user');
                $role  = ($peran === 'assistant' || $peran === 'model') ? 'assistant' : 'user';
                $teks  = isset($item['content']) ? $item['content'] : (isset($item['pesan']) ? $item['pesan'] : '');

                if (trim((string) $teks) !== '')
                {
                    // Pertahankan dokumen PRD dalam riwayat (hingga 8000 karakter) agar AI selalu mengingat isi dokumen
                    if ($tier !== 'pro' && $apakah_ringkas && mb_strlen($teks) > 8000)
                    {
                        $teks = mb_substr($teks, 0, 8000) . "\n... [Sebagian dokumen riwayat dipangkas untuk efisiensi token]";
                    }

                    $messages[] = array(
                        'role'    => $role,
                        'content' => (string) $teks,
                    );
                }
            }
        }

        if ( ! empty($prompt_pengguna))
        {
            $messages[] = array(
                'role'    => 'user',
                'content' => (string) $prompt_pengguna,
            );
        }

        return $messages;
    }

    /**
     * Hasilkan respons AI dari prompt sistem, prompt pengguna, dan riwayat obrolan.
     *
     * @param string $prompt_sistem
     * @param string $prompt_pengguna
     * @param array  $riwayat_percakapan Riwayat giliran pesan sebelumnya
     * @param array  $opsi Opsi fleksibel (max_tokens, temperature, ringkas)
     * @return array ['teks' => string, 'token_masuk' => int, 'token_keluar' => int, 'model' => string]
     * @throws Exception jika pemanggilan gagal
     */
    public function hasilkan($prompt_sistem, $prompt_pengguna, $riwayat_percakapan = array(), $opsi = array())
    {
        $messages = $this->bangun_daftar_pesan($prompt_sistem, $prompt_pengguna, $riwayat_percakapan, $opsi);

        if ( ! $this->auto_fallback)
        {
            return $this->panggil_berdasarkan_penyedia($this->penyedia, $prompt_sistem, $messages, $opsi);
        }

        // Susun urutan failover otomatis: utama dulu, lalu cadangan lainnya
        $rantai = array($this->penyedia);
        $kandidat = array('groq', 'gemini', 'ollama');
        foreach ($kandidat as $k)
        {
            if ( ! in_array($k, $rantai, TRUE))
            {
                $rantai[] = $k;
            }
        }

        $riwayat_galat = array();

        foreach ($rantai as $penyedia_target)
        {
            // Validasi ketersediaan konfigurasi sebelum eksekusi
            if ($penyedia_target === 'groq' && empty($this->groq_key) && empty($this->kunci_api))
            {
                continue;
            }
            if ($penyedia_target === 'gemini' && empty($this->gemini_key) && empty($this->kunci_api))
            {
                continue;
            }
            if ($penyedia_target === 'ollama' && ! $this->cek_ollama_aktif())
            {
                continue;
            }

            try
            {
                $hasil = $this->panggil_berdasarkan_penyedia($penyedia_target, $prompt_sistem, $messages, $opsi);
                if (count($riwayat_galat) > 0)
                {
                    log_message('info', "[Klien_ai] Permintaan berhasil dialihkan ke cadangan: {$penyedia_target}");
                }
                return $hasil;
            }
            catch (Exception $e)
            {
                $pesan_error = "[{$penyedia_target}] " . $e->getMessage();
                $riwayat_galat[] = $pesan_error;
                log_message('error', "[Klien_ai] Provider '{$penyedia_target}' gagal: {$e->getMessage()}. Mencoba penyedia cadangan...");
            }
        }

        throw new Exception("Semua penyedia AI gagal merespon: " . implode(' | ', $riwayat_galat));
    }

    /**
     * Memanggil penyedia AI tertentu dengan penanganan kunci dan model khusus.
     */
    protected function panggil_berdasarkan_penyedia($penyedia, $prompt_sistem, $messages, $opsi = array())
    {
        $penyedia   = strtolower($penyedia);
        $kunci_asli = $this->kunci_api;
        $model_asli = $this->model;

        try
        {
            switch ($penyedia)
            {
                case 'groq':
                    if ( ! empty($this->groq_key)) { $this->kunci_api = $this->groq_key; }
                    $this->model = ! empty($this->groq_model) ? $this->groq_model : 'qwen/qwen3.8-27b';
                    $hasil = $this->panggil_groq($messages, $opsi);
                    $hasil['penyedia'] = 'groq';
                    break;

                case 'gemini':
                    if ( ! empty($this->gemini_key)) { $this->kunci_api = $this->gemini_key; }
                    $this->model = ! empty($this->gemini_model) ? $this->gemini_model : 'gemini-3.8-flash';
                    $hasil = $this->panggil_gemini($prompt_sistem, $messages, $opsi);
                    $hasil['penyedia'] = 'gemini';
                    break;

                case 'ollama':
                    $this->model = ! empty($this->ollama_model) ? $this->ollama_model : 'qwen2.5-coder:7b';
                    $hasil = $this->panggil_ollama($messages, $opsi);
                    $hasil['penyedia'] = 'ollama';
                    break;

                case 'openai':
                    $hasil = $this->panggil_openai($messages, $opsi);
                    $hasil['penyedia'] = 'openai';
                    break;

                case 'anthropic':
                    $hasil = $this->panggil_anthropic($prompt_sistem, $messages, $opsi);
                    $hasil['penyedia'] = 'anthropic';
                    break;

                case 'openrouter':
                    $hasil = $this->panggil_openrouter($messages, $opsi);
                    $hasil['penyedia'] = 'openrouter';
                    break;

                case 'pollinations':
                    $hasil = $this->panggil_pollinations($messages, $opsi);
                    $hasil['penyedia'] = 'pollinations';
                    break;

                default:
                    if ($this->cek_ollama_aktif())
                    {
                        $hasil = $this->panggil_ollama($messages, $opsi);
                        $hasil['penyedia'] = 'ollama';
                    }
                    else
                    {
                        throw new Exception("Penyedia AI '{$penyedia}' belum dikonfigurasi.");
                    }
                    break;
            }

            $this->kunci_api = $kunci_asli;
            $this->model     = $model_asli;
            return $hasil;
        }
        catch (Exception $e)
        {
            $this->kunci_api = $kunci_asli;
            $this->model     = $model_asli;
            throw $e;
        }
    }

    /**
     * Eksekusi request cURL dengan timeout dan penanganan galat HTTP.
     */
    protected function kirim_curl($url, $headers, $payload_json)
    {
        $percobaan = 0;
        $maks_percobaan = 2;
        $respons_mentah = FALSE;
        $kode_http = 0;

        while ($percobaan < $maks_percobaan)
        {
            $percobaan++;
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->batas_waktu);

            $respons_mentah = curl_exec($ch);
            $kode_http      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error     = curl_error($ch);
            curl_close($ch);

            if ($curl_error)
            {
                log_message('error', "[Klien_ai] cURL Error ke {$url}: {$curl_error}");
                if ($percobaan < $maks_percobaan)
                {
                    sleep(1);
                    continue;
                }
                throw new Exception("Gagal menghubungi server AI: {$curl_error}");
            }

            // Retry jika rate limited atau temporary 5xx
            if (($kode_http === 429 || $kode_http >= 502) && $percobaan < $maks_percobaan)
            {
                log_message('error', "[Klien_ai] HTTP {$kode_http}, mencoba ulang...");
                sleep(2);
                continue;
            }

            break;
        }

        if ($kode_http < 200 || $kode_http >= 300)
        {
            log_message('error', "[Klien_ai] API HTTP {$kode_http}. Body: " . substr((string) $respons_mentah, 0, 300));
            throw new Exception("Server AI mengembalikan kode status HTTP {$kode_http}.");
        }

        return $respons_mentah;
    }

    /**
     * Periksa apakah Ollama aktif di mesin lokal.
     */
    protected function cek_ollama_aktif()
    {
        $ch = curl_init(rtrim($this->ollama_url, '/') . '/api/tags');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code === 200 && ! empty($res));
    }

    /**
     * Integrasi Ollama (OpenAI-compatible Chat Completions API).
     */
    protected function panggil_ollama($messages, $opsi = array())
    {
        $url   = rtrim($this->ollama_url, '/') . '/v1/chat/completions';
        $model = ! empty($this->model) ? $this->model : 'qwen2.5-coder:7b';

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;
        $maks_token  = isset($opsi['max_tokens']) ? (int) $opsi['max_tokens'] : $this->maks_token;

        $payload = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maks_token,
            'stream'      => FALSE,
            'options'     => array(
                'temperature' => $temperature,
                'num_predict' => $maks_token,
            ),
        );

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );

        $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $data = json_decode($respons_mentah, TRUE);

        if (empty($data['choices'][0]['message']['content']))
        {
            throw new Exception('Balasan dari model Ollama kosong.');
        }

        $teks = trim($data['choices'][0]['message']['content']);

        return array(
            'teks'         => $teks,
            'token_masuk'  => isset($data['usage']['prompt_tokens']) ? (int) $data['usage']['prompt_tokens'] : 0,
            'token_keluar' => isset($data['usage']['completion_tokens']) ? (int) $data['usage']['completion_tokens'] : 0,
            'model'        => 'ollama-' . $model,
        );
    }

    /**
     * Integrasi OpenAI Chat Completions API.
     */
    protected function panggil_openai($messages, $opsi = array())
    {
        if (empty($this->kunci_api))
        {
            throw new Exception('Kunci API OpenAI belum diatur.');
        }

        $url   = 'https://api.openai.com/v1/chat/completions';
        $model = ! empty($this->model) ? $this->model : 'gpt-4o-mini';

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;
        $maks_token  = isset($opsi['max_tokens']) ? (int) $opsi['max_tokens'] : $this->maks_token;

        $payload = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maks_token,
        );

        $headers = array(
            'Authorization: Bearer ' . $this->kunci_api,
            'Content-Type: application/json',
        );

        $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $data = json_decode($respons_mentah, TRUE);

        if (empty($data['choices'][0]['message']['content']))
        {
            throw new Exception('Balasan OpenAI kosong.');
        }

        return array(
            'teks'         => trim($data['choices'][0]['message']['content']),
            'token_masuk'  => isset($data['usage']['prompt_tokens']) ? (int) $data['usage']['prompt_tokens'] : 0,
            'token_keluar' => isset($data['usage']['completion_tokens']) ? (int) $data['usage']['completion_tokens'] : 0,
            'model'        => $model,
        );
    }

    /**
     * Integrasi Anthropic Messages API.
     */
    protected function panggil_anthropic($prompt_sistem, $messages, $opsi = array())
    {
        if (empty($this->kunci_api))
        {
            throw new Exception('Kunci API Anthropic belum diatur.');
        }

        $url   = 'https://api.anthropic.com/v1/messages';
        $model = ! empty($this->model) ? $this->model : 'claude-3-5-sonnet-20241022';

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;
        $maks_token  = isset($opsi['max_tokens']) ? (int) $opsi['max_tokens'] : $this->maks_token;

        $anthropic_messages = array();
        foreach ($messages as $m)
        {
            if ($m['role'] === 'system')
            {
                continue;
            }
            $anthropic_messages[] = array(
                'role'    => $m['role'],
                'content' => $m['content'],
            );
        }

        $payload = array(
            'model'       => $model,
            'max_tokens'  => $maks_token,
            'system'      => (string) $prompt_sistem,
            'messages'    => $anthropic_messages,
            'temperature' => $temperature,
        );

        $headers = array(
            'x-api-key: ' . $this->kunci_api,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        );

        $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $data = json_decode($respons_mentah, TRUE);

        $teks = '';
        if (isset($data['content']) && is_array($data['content']))
        {
            foreach ($data['content'] as $blok)
            {
                if (isset($blok['text']))
                {
                    $teks .= $blok['text'];
                }
            }
        }

        if (empty(trim($teks)))
        {
            throw new Exception('Balasan Anthropic kosong.');
        }

        return array(
            'teks'         => trim($teks),
            'token_masuk'  => isset($data['usage']['input_tokens']) ? (int) $data['usage']['input_tokens'] : 0,
            'token_keluar' => isset($data['usage']['output_tokens']) ? (int) $data['usage']['output_tokens'] : 0,
            'model'        => $model,
        );
    }

    /**
     * Integrasi Google Gemini API.
     */
    protected function panggil_gemini($prompt_sistem, $messages, $opsi = array())
    {
        if (empty($this->kunci_api))
        {
            throw new Exception('Kunci API Gemini belum diatur.');
        }

        $model = ! empty($this->model) ? $this->model : 'gemini-3.8-flash';
        $url   = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($this->kunci_api);

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;
        $maks_token  = isset($opsi['max_tokens']) ? (int) $opsi['max_tokens'] : $this->maks_token;

        $gemini_contents = array();
        foreach ($messages as $m)
        {
            if ($m['role'] === 'system')
            {
                continue;
            }
            $role = ($m['role'] === 'assistant') ? 'model' : 'user';
            $gemini_contents[] = array(
                'role'  => $role,
                'parts' => array(array('text' => $m['content'])),
            );
        }

        $payload = array(
            'system_instruction' => array(
                'parts' => array(array('text' => (string) $prompt_sistem)),
            ),
            'contents' => $gemini_contents,
            'generationConfig' => array(
                'temperature'     => $temperature,
                'maxOutputTokens' => $maks_token,
            ),
        );

        $headers = array('Content-Type: application/json');
        $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $data = json_decode($respons_mentah, TRUE);

        $teks = '';
        if (isset($data['candidates'][0]['content']['parts'][0]['text']))
        {
            $teks = $data['candidates'][0]['content']['parts'][0]['text'];
        }

        if (empty(trim($teks)))
        {
            throw new Exception('Balasan Gemini kosong.');
        }

        return array(
            'teks'         => trim($teks),
            'token_masuk'  => isset($data['usageMetadata']['promptTokenCount']) ? (int) $data['usageMetadata']['promptTokenCount'] : 0,
            'token_keluar' => isset($data['usageMetadata']['candidatesTokenCount']) ? (int) $data['usageMetadata']['candidatesTokenCount'] : 0,
            'model'        => $model,
        );
    }

    /**
     * Integrasi Groq Cloud API.
     */
    protected function panggil_groq($messages, $opsi = array())
    {
        if (empty($this->kunci_api))
        {
            throw new Exception('Kunci API Groq belum diatur.');
        }

        $url   = 'https://api.groq.com/openai/v1/chat/completions';
        $model = ! empty($this->model) ? $this->model : 'qwen/qwen3.8-27b';

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;
        $maks_token  = isset($opsi['max_tokens']) ? (int) $opsi['max_tokens'] : $this->maks_token;

        $payload = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maks_token,
        );

        $headers = array(
            'Authorization: Bearer ' . $this->kunci_api,
            'Content-Type: application/json',
        );

        $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $data = json_decode($respons_mentah, TRUE);

        if (empty($data['choices'][0]['message']['content']))
        {
            throw new Exception('Balasan Groq kosong.');
        }

        return array(
            'teks'         => trim($data['choices'][0]['message']['content']),
            'token_masuk'  => isset($data['usage']['prompt_tokens']) ? (int) $data['usage']['prompt_tokens'] : 0,
            'token_keluar' => isset($data['usage']['completion_tokens']) ? (int) $data['usage']['completion_tokens'] : 0,
            'model'        => $model,
        );
    }

    /**
     * Integrasi OpenRouter API.
     */
    protected function panggil_openrouter($messages, $opsi = array())
    {
        if (empty($this->kunci_api))
        {
            throw new Exception('Kunci API OpenRouter belum diatur.');
        }

        $url   = 'https://openrouter.ai/api/v1/chat/completions';
        $model = ! empty($this->model) ? $this->model : 'meta-llama/llama-3.3-70b-instruct';

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;
        $maks_token  = isset($opsi['max_tokens']) ? (int) $opsi['max_tokens'] : $this->maks_token;

        $payload = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maks_token,
        );

        $headers = array(
            'Authorization: Bearer ' . $this->kunci_api,
            'Content-Type: application/json',
            'HTTP-Referer: http://localhost/PRDAI',
            'X-Title: PRDAI',
        );

        $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $data = json_decode($respons_mentah, TRUE);

        if (empty($data['choices'][0]['message']['content']))
        {
            throw new Exception('Balasan OpenRouter kosong.');
        }

        return array(
            'teks'         => trim($data['choices'][0]['message']['content']),
            'token_masuk'  => isset($data['usage']['prompt_tokens']) ? (int) $data['usage']['prompt_tokens'] : 0,
            'token_keluar' => isset($data['usage']['completion_tokens']) ? (int) $data['usage']['completion_tokens'] : 0,
            'model'        => $model,
        );
    }

    /**
     * Integrasi Pollinations AI dengan fallback ke Ollama jika layanan down.
     */
    protected function panggil_pollinations($messages, $opsi = array())
    {
        $url   = 'https://text.pollinations.ai/';
        $model = ! empty($this->model) ? $this->model : 'openai-fast';

        $temperature = isset($opsi['temperature']) ? (float) $opsi['temperature'] : 0.35;

        $payload = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
        );

        $headers = array(
            'Content-Type: application/json',
            'Accept: text/plain, application/json',
        );

        try
        {
            $respons_mentah = $this->kirim_curl($url, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
            $teks = trim((string) $respons_mentah);

            // Jika respons adalah JSON objek yang mengindikasikan error
            if (strpos($teks, '{"error"') !== FALSE)
            {
                throw new Exception('Pollinations API melaporkan error: ' . $teks);
            }

            if (empty($teks))
            {
                throw new Exception('Balasan Pollinations AI kosong.');
            }

            return array(
                'teks'         => $teks,
                'token_masuk'  => 0,
                'token_keluar' => 0,
                'model'        => 'pollinations-' . $model,
            );
        }
        catch (Exception $e)
        {
            log_message('error', '[Klien_ai] Pollinations AI gagal (' . $e->getMessage() . '). Mencoba fallback ke Ollama.');
            if ($this->cek_ollama_aktif())
            {
                return $this->panggil_ollama($messages);
            }
            throw $e;
        }
    }
}
