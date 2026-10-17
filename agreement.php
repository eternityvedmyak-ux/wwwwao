<?php
require_once('./config.php');
$db = mysqli_connect($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['db']) or die('Ошибка подключения к БД, обновите страницу.');

// Fetch online players
$onlineCount = 0;
$maxOnline = 1000;
$ctx = stream_context_create(['http' => ['timeout' => 2]]);
$response = @file_get_contents("https://api.mcstatus.io/v2/status/java/" . urlencode($config['server_ip']), false, $ctx);
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['players']['online'])) {
        $onlineCount = $data['players']['online'];
        $maxOnline = $data['players']['max'];
    }
}

$siteName = strtoupper($config['site_name']);
$firstLetter = mb_substr($siteName, 0, 1, 'UTF-8');
$restOfName = mb_substr($siteName, 1, null, 'UTF-8');
?>

<!DOCTYPE HTML>
<html>
<head>
    <title><?= $config['site_name'] ?> - Пользовательское Соглашение</title>
    <meta name="description" content="<?= $config['description'] ?>">
    <meta name="keywords" content="<?= $config['description'] ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <?php if (isset($config['colors'])): 
        $hex = str_replace("#", "", $config['colors']['accent']);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $accentRgb = "$r, $g, $b";
    ?>
    <style>
        :root {
            --color-accent: <?= $config['colors']['accent'] ?>;
            --color-accent-rgb: <?= $accentRgb ?>;
            --bg-main: <?= $config['colors']['bg_main'] ?>;
            --bg-card: <?= $config['colors']['bg_card'] ?>;
        }
    </style>
    <?php endif; ?>
</head>

<body class="<?= (isset($config['theme']) && $config['theme'] === 'winter') ? 'new-year' : '' ?>">
<?php if (!isset($config['bg_items_enabled']) || $config['bg_items_enabled']): ?>
<div id="interactive-background" data-count="<?= isset($config['bg_items_count']) ? (int)$config['bg_items_count'] : 40 ?>"></div>
<?php endif; ?>

<header class="header">
    <div class="container">
        <div class="navbar">
            <a href="index.php" class="logo" rel="nofollow">
                <span class="logo-dot"></span>
                <span class="logo-text"><span class="hat-letter"><?= $firstLetter ?></span><?= $restOfName ?></span>
            </a>
            
            <div class="navbar-wrapper">
                <ul>
                    <li><a href="index.php" rel="nofollow">Главная</a></li>
                    <li><a href="wiki.php" rel="nofollow">Вики</a></li>
                    <li class="active"><a href="agreement.php" rel="nofollow">Соглашение</a></li>
                    <li><a href="support.php" rel="nofollow">Поддержка</a></li>
                </ul>
            </div>

            <div class="nav-actions">
                <a href="https://vk.com/stormgrief8" target="_blank" class="btn-vk" rel="nofollow">
                    <i class="fa fa-vk"></i> Группа ВК
                </a>
                <a href="#" rel="nofollow" class="navbar-mobile"><i class="fa fa-bars"></i></a>
            </div>
        </div>
    </div>
</header>

<div class="container main-content" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="item-id" style="text-align: left; align-items: flex-start; cursor: default; background: var(--bg-card); max-width: 800px; margin: 0 auto; padding: 48px;">
        <h1 class="hero-title" style="font-size: 32px; margin-bottom: 28px; width: 100%;">ПОЛЬЗОВАТЕЛЬСКОЕ СОГЛАШЕНИЕ</h1>
        
        <div class="wiki-content" style="color: var(--text-secondary); font-size: 15px; line-height: 1.8; display: flex; flex-direction: column; gap: 20px;">
            <p><strong>1.1 Возврат средств:</strong> Денежные средства, полученные после оплаты любого цифрового товара, игровой валюты или привилегии на нашем сайте, возврату не подлежат ни при каких обстоятельствах.</p>
            
            <p><strong>1.2 Ответственность при утере:</strong> При взломе вашего игрового аккаунта, утрате пароля, форс-мажорных обстоятельствах или технических сбоях в работе дата-центра гарантии на восстановление или компенсацию купленных ценностей не предоставляются.</p>
            
            <p><strong>1.3 Обман администрации:</strong> При попытке обмана администрации (предоставление ложных скриншотов оплаты, указание несуществующего никнейма, шантаж или оскорбления) в выдаче купленных товаров может быть отказано в одностороннем порядке без возврата средств.</p>
            
            <p><strong>1.4 Нарушение правил:</strong> Если вы нарушили действующие правила игры на наших серверах и были заблокированы, купленный донат аннулируется. Средства за покупку в таком случае не возвращаются.</p>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="logo" rel="nofollow">
                    <span class="logo-dot"></span>
                    <span class="logo-text"><span class="hat-letter"><?= $firstLetter ?></span><?= $restOfName ?></span>
                </a>
                <p class="footer-copy">&copy; <?= date('Y') ?> <?= $config['site_name'] ?>. Сайт сделан <a href="https://itsdester.ru" target="_blank" style="color:inherit; text-decoration:underline;">itsDester</a>. Все права защищены. Полное или частичное копирование сайта запрещено.</p>
            </div>
            <div class="footer-contacts">
                <div class="contact-item">
                    <span class="contact-label">ПОДДЕРЖКА:</span>
                    <a href="mailto:help@endcore.ru" class="contact-value">help@endcore.ru</a>
                </div>
                <div class="contact-item">
                    <span class="contact-label">ТЕЛЕГРАМ:</span>
                    <a href="https://t.me/тгканал" target="_blank" class="contact-value">@тгканал</a>
                </div>
                <div class="contact-item">
                    <span class="contact-label">ВКОНТАКТЕ:</span>
                    <a href="https://vk.com/группавк" target="_blank" class="contact-value">vk.com/группавк</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>
<script src="assets/script.js"></script>
<script src="assets/bg-items.js"></script>
<script src="assets/features.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var _0x45f2 = document.querySelector('.footer-copy a[href*="itsdester.ru"]');
            var _0x1c8b = document.querySelector('.footer');
            if(!_0x45f2 || !_0x1c8b || window.getComputedStyle(_0x45f2).display === 'none' || 
               window.getComputedStyle(_0x45f2).opacity === '0' || window.getComputedStyle(_0x45f2).visibility === 'hidden' ||
               window.getComputedStyle(_0x1c8b).display === 'none' || window.getComputedStyle(_0x1c8b).opacity === '0') {
                document.body.innerHTML = "<div style='text-align:center; padding: 50px; font-family:sans-serif; background:#111; color:#fff; height:100vh;'><h1>Сайт заблокирован</h1><p>Вы скрыли или удалили копирайт разработчика (itsDester). Восстановите код футера для работы сайта.</p></div>";
            }
        }, 1500);
    });
</script>
</body>
</html>
