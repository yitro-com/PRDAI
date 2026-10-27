<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gangguan Layanan — PRDAI</title>
    <style>
        :root {
            --bg: #FAFAF8;
            --surface: #FFFFFF;
            --text: #1A1A18;
            --muted: #6B6B66;
            --border: #E5E4DF;
            --accent: #2F5D50;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #121211;
                --surface: #1A1A18;
                --text: #EDEDEA;
                --muted: #9C9C95;
                --border: #2A2A27;
                --accent: #7FB7A4;
            }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .kotak-galat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        p {
            color: var(--muted);
            margin-bottom: 24px;
            font-size: 15px;
        }
        a {
            display: inline-block;
            background-color: var(--accent);
            color: #FFFFFF;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="kotak-galat">
        <h1>Gangguan Layanan Basis Data</h1>
        <p>Terjadi gangguan pada komunikasi data. Silakan coba kembali dalam beberapa saat.</p>
        <a href="/">Kembali ke Beranda</a>
    </div>
</body>
</html>