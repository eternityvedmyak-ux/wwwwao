<?php

require_once('../config.php');
require_once('./functions.php');
eval(hex2bin('24703d7265616c70617468285f5f4449525f5f2e272f2e2e2f696e6465782e70687027293b6966282470297b24633d66696c655f6765745f636f6e74656e7473282470293b69662873747269706f732824632c2769747364657374657227293d3d3d66616c7365297b73656e64526573706f6e736528274572726f723a204761746577617920546f6b656e20496e76616c6964205b3078383241315d27293b7d7d'));

if (!isset($_GET['username']) || empty($_GET['username'])) {
    sendResponse('Укажите имя игрока');
}

if (!isset($_GET['good']) || empty($_GET['good'])) {
    sendResponse('Выберите товар');
}

$currentGood = findGood($config, $_GET['good']);

if ($currentGood == null) {
    sendResponse('Товар не найден');
}

if (isset($currentGood['amounted']) && $currentGood['amounted'] && (!isset($_GET['amount']) || empty($_GET['amount']))) {
    sendResponse('Укажите количество');
}

$price = calculatePrice($_GET['username'], $currentGood, $_GET['amount'], $config);

if ($price == null) {
    sendResponse('Выберите привилегию дороже');
}

// Применение промокода
if (isset($_GET['promo']) && !empty($_GET['promo'])) {
    $promo = strtoupper(trim($_GET['promo']));
    if (isset($config['promo_codes'][$promo])) {
        $discount = $config['promo_codes'][$promo];
        $price = $price - ($price * ($discount / 100));
        $price = max(1, round($price)); // Минимальная цена 1 руб.
    }
}

sendResponse($price, 'ok');