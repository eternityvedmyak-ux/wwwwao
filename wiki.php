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
    <title><?= $config['site_name'] ?> - База Знаний (Вики)</title>
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
                    <li class="active"><a href="wiki.php" rel="nofollow">Вики</a></li>
                    <li><a href="agreement.php" rel="nofollow">Соглашение</a></li>
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
    <div class="hero-banner" style="margin-bottom: 40px; text-align: center;">
        <div class="hero-badge"><i class="fa fa-book"></i> ИНСТРУКЦИИ И ОПИСАНИЯ</div>
        <h1 class="hero-title">БАЗА ЗНАНИЙ (WIKI)</h1>
        <p class="hero-subtitle">Здесь собрана вся информация о том, как правильно приобрести донат, проверить его возможности и активировать покупки на сервере.</p>
    </div>

    <!-- How to buy section -->
    <h2 class="hero-title" style="font-size: 24px; text-align: center; margin-bottom: 32px; letter-spacing: -0.5px;"><?= isset($config['wiki']['steps_title']) ? $config['wiki']['steps_title'] : 'КАК КУПИТЬ ДОНАТ — ПОШАГОВО' ?></h2>
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 60px;">
        <?php 
        $steps = isset($config['wiki']['steps']) ? $config['wiki']['steps'] : [];
        foreach ($steps as $step):
        ?>
        <div class="stat-card" style="flex-direction: column; text-align: center; padding: 32px 24px;">
            <div class="stat-icon" style="margin: 0 auto 16px auto; font-size: 22px; font-weight: 800;"><?= $step['num'] ?></div>
            <div class="stat-number" style="font-size: 16px; margin-bottom: 8px;"><?= $step['title'] ?></div>
            <div class="stat-label" style="line-height: 1.5;"><?= $step['desc'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Donation privileges possibilities -->
    <h2 class="hero-title" style="font-size: 24px; text-align: center; margin-bottom: 32px; letter-spacing: -0.5px;"><?= isset($config['wiki']['privileges_title']) ? $config['wiki']['privileges_title'] : 'ВОЗМОЖНОСТИ ПРИВИЛЕГИЙ' ?></h2>
    <div class="items" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <?php 
        $privileges = isset($config['wiki']['privileges']) ? $config['wiki']['privileges'] : [];
        foreach ($privileges as $priv):
        ?>
        <div class="item-id" style="align-items: flex-start; text-align: left; padding: 32px; cursor: default;">
            <h3 class="title" style="color: var(--color-accent); font-size: 18px; margin-bottom: 16px;"><?= $priv['title'] ?></h3>
            <ul style="color: var(--text-secondary); font-size: 13px; line-height: 1.8; padding-left: 16px;">
                <?php foreach ($priv['features'] as $feature): ?>
                <li><?= $feature ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
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
