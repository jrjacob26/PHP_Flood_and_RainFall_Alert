<?php
// mail_config.php
// 👉 Use a Gmail "App password" (not your regular password). Enable 2FA in Gmail, create App Password for "Mail".

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'bahashield@gmail.com');      // <-- change me
define('SMTP_PASSWORD', 'gbzu rtgi wgbz jfps');   // <-- change me (16-char App Password)

define('MAIL_FROM', 'bahashield@gmail.com');          // from email (same as username is OK)
define('MAIL_FROM_NAME', '🌊 BahaShield');           // sender name
define('APP_BASE_URL', 'https://your-domain.com');   // <-- change to your domain/root (no trailing slash)
