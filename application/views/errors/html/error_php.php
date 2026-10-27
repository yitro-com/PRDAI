<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if (ENVIRONMENT === 'production'): ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kendala — PRDAI</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #FAFAF8; color: #1A1A18; text-align: center; padding: 48px; }
        .kotak { max-width: 480px; margin: 0 auto; background: #FFF; padding: 32px; border: 1px solid #E5E4DF; border-radius: 8px; }
        a { color: #2F5D50; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="kotak">
        <h2>Terjadi Kendala Sistem</h2>
        <p>Aplikasi mengalami kendala teknis. Silakan muat ulang halaman.</p>
        <p><a href="/">Kembali ke Beranda</a></p>
    </div>
</body>
</html>
<?php else: ?>
<div style="border:1px solid #990000;padding-left:20px;margin:10px 0;background:#fff;">
<h4>Pemberitahuan PHP</h4>
<p>Keparahan: <?php echo htmlspecialchars($severity, ENT_QUOTES, 'UTF-8'); ?></p>
<p>Pesan: <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<p>Nama Berkas: <?php echo htmlspecialchars($filepath, ENT_QUOTES, 'UTF-8'); ?></p>
<p>Nomor Baris: <?php echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>