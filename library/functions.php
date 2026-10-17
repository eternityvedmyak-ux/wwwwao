<?php

function formatResponse($message = '', $status) {
    return json_encode(array(
        'status' => $status,
        'message' => $message,
    ), JSON_UNESCAPED_UNICODE);
}

function sendResponse($message, $status = 'failed') {
    echo formatResponse($message, $status);

    die;
}

function findGood($config, $good) {

    foreach ($config['categories'] as $category) {
        foreach ($category['products'] as $k => $v) {
            if ($k === $good) {
                return $v;
            }
        }
    }

    return null;
}

function oldGoodPrice($user, $config, $serverConfig = null) {
    $db = mysqli_connect($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['db']) or die('Ошибка подключения к БД, обновите страницу.');

    $stmt = $db->prepare("SELECT * FROM `luckperms_players` WHERE `username`=? LIMIT 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc();

    if (!$group) {
        return 0;
    }

    if(in_array($group["primary_group"], $config['super_products'])) return 999999999;

    $targetConfig = $serverConfig ? $serverConfig : $config;
    $oldGood = findGood($targetConfig, $group["primary_group"]);

    if (!$oldGood)
        return 0;

    return $oldGood['price'];
}

function calculatePrice($user, $good, $amount, $config, $serverConfig = null) {
    if (isset($good['amounted']) && $good['amounted']) {
        return $good['price'] * $amount;
    }

    if (!$good['withSurcharge']) {
        return $good['price'];
    }

    $oldPrice = oldGoodPrice($user, $config, $serverConfig);

    if ($oldPrice == 0) {
        return $good['price'];
    } else if ($oldPrice < $good['price']) {
        return intval($good['price'] - $oldPrice);
    } else {
        return null;
    }
}

function getFormSignature($account, $desc, $sum, $secretKey) {
    $hashStr = $account.'{up}'.$desc.'{up}'.$sum.'{up}'.$secretKey;
    return hash('sha256', $hashStr);
}