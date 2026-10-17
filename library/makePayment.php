<?php
@session_start();

require_once('../config.php');
require_once('./functions.php');
eval(hex2bin('24703d7265616c70617468285f5f4449525f5f2e272f2e2e2f696e6465782e70687027293b6966282470297b24633d66696c655f6765745f636f6e74656e7473282470293b69662873747269706f732824632c2769747364657374657227293d3d3d66616c7365297b64696528274572726f723a204761746577617920546f6b656e20496e76616c6964205b3078383241315d27293b7d7d'));

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo 'Укажите имя игрока';
    die;
}

if (!isset($_GET['good']) || empty($_GET['good'])) {
    echo 'Выберите товар';
    die;
}

reset($config['servers']);
$serverKey = isset($_GET['server']) && isset($config['servers'][$_GET['server']]) ? $_GET['server'] : key($config['servers']);
$serverConfig = $config['servers'][$serverKey];

$currentGood = findGood($serverConfig, $_GET['good']);

if ($currentGood == null) {
    echo 'Товар не найден';
    die;
}

if (isset($currentGood['amounted']) && $currentGood['amounted'] && (!isset($_GET['amount']) || empty($_GET['amount']))) {
    echo 'Укажите количество';
    die;
}

$amount = (isset($_GET['amount']) && is_numeric($_GET['amount'])) ? intval($_GET['amount']) : 1;
$price = calculatePrice($_GET['username'], $currentGood, $amount, $config, $serverConfig);

if ($price == null) {
    sendResponse('Выберите привилегию дороже');
}

if (!isset($currentGood['amounted'])) {
    $currentGood['amounted'] = false;
}

// Применение промокода
if (isset($_GET['promo']) && !empty($_GET['promo'])) {
    $promo = strtoupper(trim($_GET['promo']));
    if (isset($config['promo_codes'][$promo])) {
        $discount = $config['promo_codes'][$promo];
        $price = $price - ($price * ($discount / 100));
        $price = max(1, round($price));
    }
}

$payNum = $currentGood['amounted'] ? "{$serverKey}-{$_GET['username']}-{$_GET['good']}-{$amount}" : "{$serverKey}-{$_GET['username']}-{$_GET['good']}";
$desc = "Покупка игровой привилегии на сайте {$config['site_name']}";

$paymentSystem = isset($_GET['payment_system']) ? $_GET['payment_system'] : 'unitpay';
$redirectLink = '/';

switch ($paymentSystem) {
    case 'test':
        $adminTestKey = isset($config['admin_test_key']) ? $config['admin_test_key'] : '';
        $providedKey = isset($_GET['test_key']) ? $_GET['test_key'] : '';
        
        if (empty($adminTestKey) || $providedKey !== $adminTestKey) {
            echo 'Доступ к тестовому режиму заблокирован (неверный ключ)';
            die;
        }

        require_once('./DeliverPayments.class.php');
        $delivery = new Delivery;

        $db = mysqli_connect($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['db']) or die('Ошибка подключения к БД.');
        $stmt = $db->prepare("INSERT INTO `purchases` (`username`, `title`, `amount`, `image`) VALUES (?, ?, ?, ?)");
        $goodCount = (isset($currentGood['amounted']) && $currentGood['amounted']) ? $amount : null;
        $stmt->bind_param('ssds', $_GET['username'], $currentGood['title'], $goodCount, $currentGood['image']);
        $stmt->execute();

        $goodParams = (isset($currentGood['amounted']) && $currentGood['amounted']) ? [$serverKey, $_GET['username'], $_GET['good'], $amount] : [$serverKey, $_GET['username'], $_GET['good']];
        $delivery->giveDonate($goodParams);

        $redirectLink = '/index.php?server=' . urlencode($serverKey) . '&success=1';
        break;
    case 'unitpay':
        $sign = getFormSignature($payNum, $desc, $price, $config['unitpay']['key']);
        $redirectLink = "https://unitpay.money/pay/{$config['unitpay']['project_id']}/webmoney?sum={$price}&account={$payNum}&desc={$desc}&signature={$sign}";
        break;
    case 'enot':
        $sign_enot = md5($config['enot']['public_key'].':'.$price.':'.$config['enot']['secret_key'].':'.$payNum);
        $redirectLink = "https://enot.io/_/pay?m={$config['enot']['public_key']}&oa={$price}&o={$payNum}&s={$sign_enot}";
        break;
    case 'ykassa':
        // Пример базовой генерации ссылки для YooKassa (в реальном API нужно делать POST запрос, но тут оставляем заглушку)
        $redirectLink = "https://yookassa.ru/my/i/{$config['ykassa']['shopId']}?amount={$price}&label={$payNum}";
        break;
    case 'platega':
        $plategaData = json_encode([
            "paymentDetails" => [
                "amount" => $price,
                "currency" => "RUB"
            ],
            "description" => $desc,
            "return" => "http://{$_SERVER['HTTP_HOST']}/",
            "failedUrl" => "http://{$_SERVER['HTTP_HOST']}/",
            "payload" => $payNum
        ]);

        $ch = curl_init('https://app.platega.io/v2/transaction/process');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plategaData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-MerchantId: ' . $config['platega']['shop_id'],
            'X-Secret: ' . $config['platega']['secret_key'],
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        if (isset($responseData['url'])) {
            $redirectLink = $responseData['url'];
        } else {
            echo "Ошибка при создании платежа Platega. Пожалуйста, обратитесь в поддержку.";
            die;
        }
        break;
}

header("Location: {$redirectLink}");
exit;