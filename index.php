<?php
require_once('./config.php');
$db = mysqli_connect($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['db']) or die('Ошибка подключения к БД, обновите страницу.');

$lastPurchases = mysqli_query($db, "SELECT * FROM `purchases` ORDER BY id DESC LIMIT 5");

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
reset($config['servers']);
$selectedServerKey = isset($_GET['server']) && isset($config['servers'][$_GET['server']]) ? $_GET['server'] : key($config['servers']);
$serverConfig = $config['servers'][$selectedServerKey];

$siteName = strtoupper($config['site_name']);
$firstLetter = mb_substr($siteName, 0, 1, 'UTF-8');
$restOfName = mb_substr($siteName, 1, null, 'UTF-8');
?>

    <!DOCTYPE HTML>
    <html>
    <head>
        <title><?= $config['site_name'] ?> - Покупка доната, привилегий, ключей, кейсов</title>
        <meta name="description"
              content="<?= $config['description'] ?>">
        <meta name="keywords" content="<?= $config['description'] ?>">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
              rel="stylesheet">
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
    <?php if (isset($_GET['success'])): ?>
    <div class="test-success-alert" style="position: fixed; top: 24px; right: 24px; z-index: 99999; background: rgba(16, 185, 129, 0.95); border: 1px solid rgba(16, 185, 129, 0.2); padding: 16px 24px; border-radius: 16px; color: #fff; box-shadow: 0 20px 40px rgba(0,0,0,0.5); backdrop-filter: blur(10px); display: flex; align-items: center; gap: 12px; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;">
        <i class="fa fa-check-circle" style="font-size: 20px;"></i>
        <span>Тестовый платеж успешно проведен! Донат выдан.</span>
        <i class="fa fa-times" style="margin-left: 12px; cursor: pointer; opacity: 0.7;" onclick="this.parentElement.remove()"></i>
    </div>
    <?php endif; ?>
    <?php if (!isset($config['bg_items_enabled']) || $config['bg_items_enabled']): ?>
    <div id="interactive-background" data-count="<?= isset($config['bg_items_count']) ? (int)$config['bg_items_count'] : 40 ?>"></div>
    <?php endif; ?>

    <header class="header">
        <div class="container">
            <div class="navbar">
                <a href="/" class="logo" rel="nofollow">
                    <span class="logo-dot"></span>
                    <span class="logo-text"><span class="hat-letter"><?= $firstLetter ?></span><?= $restOfName ?></span>
                </a>
                <div class="navbar-wrapper">
                    <ul>
                        <li class="active"><a href="index.php" rel="nofollow">Главная</a></li>
                        <li><a href="wiki.php" rel="nofollow">Вики</a></li>
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

    <!-- Новая секция баннера (Hero Section) -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-banner">
                <?php if (isset($config['theme']) && $config['theme'] === 'winter'): ?>
                    <div class="garland-container"></div>
                <?php endif; ?>
                <div class="hero-header-row">
                    <div class="hero-badge"><i class="fa fa-fire"></i> ЛУЧШИЙ ГРИФЕРСКИЙ СЕРВЕР MINECRAFT</div>
                    <div class="online-pill">
                        <span class="pulse-dot"></span>
                        <span class="online-count">ИГРОКОВ ОНЛАЙН: <strong><?= $onlineCount ?> / <?= $maxOnline ?></strong></span>
                    </div>
                </div>
                <?php if (isset($config['hero_character']) && !empty($config['hero_character'])): ?>
                <div class="hero-character">
                    <img src="img/hero.webp" alt="Hero" />
                </div>
                <?php endif; ?>
                <h1 class="hero-title"><span class="hat-letter"><?= $firstLetter ?></span><?= $restOfName ?></h1>
                <p class="hero-subtitle">Уникальное выживание, крутой геймплей, честная администрация и море ПВП приключений! Начни побеждать прямо сейчас.</p>
                
                <?php if (isset($config['promo']) && $config['promo']['enabled']): ?>
                <div class="promo-timer" data-end="<?= $config['promo']['end_date'] ?>">
                    <div class="promo-text"><i class="fa fa-tag"></i> <?= $config['promo']['text'] ?></div>
                    <div class="timer-countdown">
                        <div class="time-box"><span class="t-days">00</span><small>дн</small></div>:
                        <div class="time-box"><span class="t-hours">00</span><small>ч</small></div>:
                        <div class="time-box"><span class="t-minutes">00</span><small>м</small></div>:
                        <div class="time-box"><span class="t-seconds">00</span><small>с</small></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="hero-actions">
                    <button class="btn btn-hero copy-clipboard" data-clipboard-text="<?= $config['server_ip'] ?>">
                        <i class="fa fa-gamepad"></i> <span><?= $config['server_ip'] ?></span> <i class="fa fa-clone copy-icon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Получаем общее количество покупок
    $totalPurchasesQuery = mysqli_query($db, "SELECT COUNT(id) as total FROM `purchases`");
    $totalPurchases = ($totalPurchasesQuery && $totalPurchasesQuery->num_rows > 0) ? $totalPurchasesQuery->fetch_assoc()['total'] : 0;
    ?>
    <div class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="0">
                    <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                    <div class="stat-info">
                        <div class="stat-number" data-target="<?= $totalPurchases ?>">0</div>
                        <div class="stat-label">Всего покупок</div>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon"><i class="fa fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-number" data-target="<?= isset($config['stats']['players_registered']) ? $config['stats']['players_registered'] : 5800 ?>">0</div>
                        <div class="stat-label">Игроков зарегистрировано</div>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon"><i class="fa fa-server"></i></div>
                    <div class="stat-info">
                        <div class="stat-number" data-target="<?= isset($config['stats']['uptime_days']) ? $config['stats']['uptime_days'] : 365 ?>">0</div>
                        <div class="stat-label">Дней работы сервера</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container main-content">
        <!-- Селектор серверов с премиальным дизайном -->
        <div class="server-selector-container" style="margin-bottom: 40px; text-align: center;" data-aos="fade-up">
            <div class="blockname" style="margin-bottom: 16px;">Выберите сервер</div>
            <div class="server-buttons" style="display: inline-flex; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); padding: 6px; border-radius: 20px; gap: 8px; backdrop-filter: blur(10px);">
                <?php foreach ($config['servers'] as $key => $srv): 
                    $isActive = ($selectedServerKey === $key);
                ?>
                    <a href="?server=<?= $key ?>" rel="nofollow" class="server-btn <?= $isActive ? 'active' : '' ?>" style="
                        padding: 12px 28px;
                        border-radius: 14px;
                        font-weight: 700;
                        font-size: 14px;
                        text-decoration: none;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        transition: all var(--transition-speed);
                        color: <?= $isActive ? '#000' : 'var(--text-secondary)' ?>;
                        background: <?= $isActive ? 'var(--color-accent)' : 'transparent' ?>;
                        box-shadow: <?= $isActive ? '0 8px 20px rgba(var(--color-accent-rgb), 0.25)' : 'none' ?>;
                    ">
                        <i class="fa fa-server" style="font-size: 12px; opacity: 0.8;"></i>
                        <span><?= $srv['title'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tabs">
            <div class="tabs-wrapper">
                <div class="block-left">
                    <ul class="tab-links">
                        <?php
                        foreach ($serverConfig["categories"] as $key => $value):
                            ?>
                            <li class="<?= (isset($value['default']) && $value['default'] ? 'active' : '') ?>" data-id="<?= $key ?>">
                                <a href="#" rel="nofollow">
                                    <span class="block-left"><i class="fa <?= (isset($value['icon']) ? $value['icon'] : 'fa-diamond') ?>"></i></span>
                                    <span class="block-right"><?= $value['title'] ?></span>
                                </a>
                            </li>
                        <?php
                        endforeach;
                        ?>
                    </ul>
                </div>

                <div class="block-right">
                    <button class="btn copy-clipboard text-upper text-bold"
                            data-clipboard-text="<?= $config['server_ip'] ?>">
                        <?= $config['server_ip'] ?>
                    </button>
                </div>
            </div>
            <div class="tab-list">
                <?php foreach ($serverConfig["categories"] as $key => $value): ?>
                    <div class="tab-id <?= ((isset($value['default']) && $value['default']) ? "active" : "") ?>"
                         data-id="<?= $key ?>">
                        <?php if (isset($value['notify'])): ?>
                            <div class="category-text pb-20">
                                <div class="panel"><?= $value['notify'] ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="items">
                            <?php foreach ($value['products'] as $k => $v): ?>
                                <div class="item-id" data-aos="fade-up" data-modal="paymodal"
                                     data-amounted="<?= ((isset($v['amounted']) && $v['amounted']) ? "1" : "") ?>"
                                     data-name="<?= $k ?>" data-price="<?= $v['price'] ?>"
                                     data-withSurcharge="<?= ((isset($v['withSurcharge']) && $v['withSurcharge']) ? "1" : "") ?>">
                                    <div class="image" style="background-image: url('<?= $v['image'] ?>');"></div>
                                    <div class="title"><?= $v['title'] ?></div>
                                    <div class="price"><?= $v['price'] ?> <i class="fa fa-ruble"></i></div>
                                    <?php if (isset($v['discount']) && $v['discount'] > 0): ?>
                                        <div class="discount">-<?= $v['discount'] ?>%</div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="faq-section">
            <div class="blockname">Частые вопросы</div>
            <div class="faq-accordion">
                <?php if (isset($config['faq'])): foreach ($config['faq'] as $faq): ?>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?= $faq['q'] ?></span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p><?= $faq['a'] ?></p>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        
        <div class="payments">
            <div class="blockname">Последние покупки</div>
            <div class="payment-list">
                <?php if ($lastPurchases && $lastPurchases->num_rows > 0):
                    while($lastPurchase = $lastPurchases->fetch_assoc()):
                ?>
                <div class="payment-id window item-id" style="display: flex;">
                    <div class="image" style="background-image: url(<?=$lastPurchase['image']?>);"></div>
                    <div class="title"><?=$lastPurchase['title']?></div>
                    <div class="player">─ <?=$lastPurchase['username']?></div>
                    <?php if (isset($lastPurchase['amount']) && $lastPurchase['amount'] > 1): echo "<div class='amount'>x{$lastPurchase['amount']}</div>"; endif ?>
                </div>
                <?php
                    endwhile;
                    else:
                ?>
                <p>Нет последних покупок</p>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>

    <div class="modal" data-id="paymodal">
        <div class="wrapper">
            <form id="buyform" method="get" action="library/makePayment.php" class="modal-content">
                <div class="modal-header text-center text-bold"></div>

                <input type="hidden" name="price" id="price" value="">
                <input type="hidden" name="good" id="good" value="">
                <input type="hidden" name="server" id="server" value="<?= htmlspecialchars($selectedServerKey) ?>">

                <div class="modal-body modal-split">
                    <div class="modal-left">
                        <div class="input-block">
                            <label for="login">Укажите Ваш никнейм на сервере</label>
                            <input type="text" name="username" id="login" placeholder="ВВЕДИТЕ НИКНЕЙМ">
                        </div>
                        <div id="amounted">
                            <br>
                            <div class="input-block">
                                <label for="amount">Укажите кол-во товара</label>
                                <input id="amount" type="number" min="1" step="1" name="amount" value="1"
                                       placeholder="ВВЕДИТЕ КОЛ-ВО">
                            </div>
                        </div>
                        <br>
                        <div class="input-block">
                            <label for="promo">Промокод (если есть)</label>
                            <input type="text" name="promo" id="promo" placeholder="ВВЕДИТЕ ПРОМОКОД">
                        </div>
                    </div>
                    <div class="modal-right">
                        <div class="payment-selection">
                            <label class="payment-label">Выберите способ оплаты:</label>
                            <div class="payment-methods">
                                <?php 
                                $methods = [
                                    "unitpay" => "UNITPAY",
                                    "enot" => "ENOT.IO",
                                    "ykassa" => "YOOKASSA",
                                    "platega" => "PLATEGA"
                                ];
                                $firstChecked = false;
                                foreach ($methods as $key => $name):
                                    $isEnabled = !isset($config['payment_systems']) || (isset($config['payment_systems'][$key]) && $config['payment_systems'][$key]);
                                    if ($isEnabled):
                                        $checkedAttr = !$firstChecked ? "checked" : "";
                                        $firstChecked = true;
                                ?>
                                <label class="payment-method">
                                    <input type="radio" name="payment_system" value="<?= $key ?>" <?= $checkedAttr ?>>
                                    <div class="method-card <?= $key ?>-card">
                                        <span class="card-title"><?= $name ?></span>
                                    </div>
                                </label>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer text-right">
                    <button class="btn text-upper" id="submitBtn" type="submit">Оплатить</button>
                    <button data-modal-close class="btn text-upper btn-clear">Закрыть</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo" rel="nofollow">
                        <span class="logo-dot"></span>
                        <span class="logo-text"><span class="hat-letter"><?= $firstLetter ?></span><?= $restOfName ?></span>
                    </a>
                    <p class="footer-copy">&copy; <?= date('Y') ?> <?= $config['site_name'] ?>. Сайт сделан <a href="https://itsdester.ru" target="_blank" style="color:inherit; text-decoration:underline;">itsDester</a>. Все права защищены. Полное или частичное копирование сайта запрещено.</p>
                </div>
                <div class="footer-contacts">
                    <div class="contact-item">
                        <span class="contact-label">ПОДДЕРЖКА:</span>
                        <a href="mailto:help@stormgrief.ru" class="contact-value">help@endcore.ru</a>
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

    <div class="modal" data-id="donate">
        <div class="wrapper">
            <div class="modal-content">
                <div class="modal-header text-center text-bold">Описание доната</div>

                <div class="modal-body">

                    <center>
                        <p><a href="/" rel="nofollow" class="btn btn-success">Приобрести донат</a></p>
                        <br>
                        <p>
                        <h2><font color="blue" face="Arial">Возможности привилегий</font></h2></p>
                        <br>
                        <p>
                        <h1>ВОИН</h1></p>
                        <br>
                        <img src="https://i.imgur.com/SsMfNfE.png" class="img-fluid">
                        <hr>
                        <p>
                        <h1>ПРИНЦ</h1></p><br>
                        <img src="https://i.imgur.com/ZculW4a.png" class="img-fluid">
                        <hr>
                        <p>
                        <h1>РЫЦАРЬ</h1></p><br>
                        <img src="https://i.imgur.com/2xklkIb.jpg" class="img-fluid">
                        <hr>
                        <p>
                        <h1>ЛОРД</h1></p><br>
                        <img src="https://i.imgur.com/mYsv1A0.jpg" class="img-fluid">
                        <hr>
                        <p>
                        <h1>ТРИТОН</h1></p><br>
                        <img src="https://i.imgur.com/sdlDDMs.png" class="img-fluid">
                        <hr>
                        <p>
                        <h1>ЗЕВС</h1></p><br>
                        <img src="https://i.imgur.com/OxAAnG4.png" class="img-fluid">
                        <hr>
                        <p>
                        <h1>ЭПИК</h1></p><br>
                        <img src="https://i.imgur.com/2cx1pdP.png" class="img-fluid">
                        <hr>
                        <p>
                        <h1>DRAGON</h1></p><br>
                        <img src="https://i.imgur.com/pENDYnj.png" class="img-fluid">
                        <hr>
                        <p>
                        <h1>Донат кейс</h1></p><br>
                   <center>
						<br>
						<p>В кейс с Донатом входит ВОИН, ПРИНЦ, РЫЦАРЬ, ЛОРД, ТРИТОН, ЗЕВС, ЭПИК, ДРАГОН.</p><br>
                        </center>
                        <h1>Кейс с франками</h1></p><br>
                   <center>
						<br>
						<p>В кейс с Франками входит 250 франков, 500 франков, 1000 франков, 3000 франков, 6000 франков,
						8000 франков, 16000 франков, 25000 франков.</p><br>
                        </center>
                        <h1>Кейс с Аниме титулами</h1></p><br>
                   <center>
						<br>
						<p>В кейс с Аниме титулами входят титулы Кун, Братик, Ня, Семпай, Наруто, Гаара, Киллер Би, Хашимара, Оноки, Майто, Рок Ли, Обито, Шинки, Сай Таюя, Гурен, Саске.</p><br>
                        </center>
                        <h1>Кейс с Новогодними титулами</h1></p><br>
                   <center>
						<br>
						<p>В кейс с Новогодними титулами входят титулы Снеговик, Лядышка, Ёлка, Снег, #2021, Оливье, Чудесный, Санта, Дед-мороз, Новый год, Украшение, Кока-Кола, Закат, Чудо, Загадочный, Подарок, Снежное чудо.</p><br>
                        </center>
                        <h1>Кейс с Приватными титулами</h1></p><br>
                   <center>
						<br>
						<p>В кейс с Приватными титулами входят титулы Mag, Ez, Pvp, :3, LikesCombo, <3, Able, Power, Force, Speed, Cube, Donate, Supreme, Emperor, Killer, Pro, Fire..</p><br>
                        </center>
                        <h1>Кейс с Титулами Рик и Морти</h1></p><br>
                   <center>
						<br>
						<p>В кейс с Титулами Рик и Морти титулами входят титулы Кроненбер, Юнити, Шептун, Морти, Токсик, Джерри, Плюмбус, Швифт, Мисикс, Земля, Бет, Саммер, Рик, Ягуар, Наука, Джессика, Портал.</p><br>
                        </center>
                        <h1>Возникли проблемы?</h1></p><br>
                        <p>Ты всегда можешь обратиться в нашу службу поддержки!</p><br>
                        <p><a rel="nofollow" target="_blank" href="https://vk.com/agemine_ru" class="btn btn-warning">Обратиться
                                в техническую поддержку</a></p>
                    </center>

                </div>

                <div class="modal-footer text-right">
                    <button data-modal-close class="btn btn-clear text-upper">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" data-id="help">
        <div class="wrapper">
            <div class="modal-content">
                <div class="modal-header text-center text-bold">Пользовательское Соглашение</div>

                <div class="modal-body">

                   <center>					
                        <p>1.1 Деньги после покупки любого товара или услуги не возвращаются ни в каком размере.<br>
						<br>
						
<p>1.2 При взломе, несанкционнированном доступе, утери данных в следствии стихийных бедствий, пожаров, технических неполадок, аварий в дата центре и т.д, гарантии возврата доната не предоставляется.<br>
<br>
<p>1.3 При попытке обмануть администрацию сервера: предоставить фейковый скриншот оплаты, другой ник и т.д, или же оскорбить администрацию, даже при действительной покупке привилегии, администратор в выдаче привилегии способен отказать.<br>
<br>
<p>1.4 Если вы нарушали правила сервера, то администратор сервера в праве снять с вас донат и не вернуть деньги за покупку доната.</p><br>
                    </center>

                </div>

                <div class="modal-footer text-right">
                    <button data-modal-close class="btn btn-clear text-upper">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

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
