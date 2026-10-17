<?php
@session_start();

class Delivery {
    public $cfg;

    public function __construct(){
        global $config;
        require_once(dirname(__DIR__) . '/config.php');
        require_once(__DIR__ . '/functions.php');
        require_once(__DIR__ . '/rcon.class.php');
        $this->cfg = $config;
    }

    public function unitpay_payment($method, $params) {
        $goodParams = explode('-', $params['account']);
        if($params['signature'] != $this->getSignature($method, $params)) return $this->payment_replay($params, "Подпись неверная");
        if(count($goodParams) < 3) return $this->payment_replay($params, "Счет не обнаружен");

        $serverKey = $goodParams[0];
        $username = $goodParams[1];
        $goodId = $goodParams[2];
        $amount = isset($goodParams[3]) ? intval($goodParams[3]) : 0;

        $serverConfig = isset($this->cfg['servers'][$serverKey]) ? $this->cfg['servers'][$serverKey] : null;
        if ($serverConfig == null) return $this->payment_replay($params, "Сервер не найден");

        $currentGood = findGood($serverConfig, $goodId);
        if ($currentGood == null) return $this->payment_replay($params, "Товар не обнаружен");

        if (isset($currentGood['amounted']) && $currentGood['amounted'] && !isset($goodParams[3])) return $this->payment_replay($params, "Счет не обнаружен");

        $price = calculatePrice($username, $currentGood, $amount, $this->cfg, $serverConfig);

        if ($price != (int)$params['orderSum'] || $params['orderCurrency'] != 'RUB') return $this->payment_replay($params, "Неверная цена");

        switch ($method) {
            case 'pay':
                $db = mysqli_connect($this->cfg['db']['host'], $this->cfg['db']['user'], $this->cfg['db']['password'], $this->cfg['db']['db']) or die('Ошибка подключения к БД, обновите страницу.');
                $stmt = $db->prepare("INSERT INTO `purchases` (`username`, `title`, `amount`, `image`) VALUES (?, ?, ?, ?)");
                $goodCount = (isset($currentGood['amounted']) && $currentGood['amounted']) ? $amount : null;
                $stmt->bind_param('ssds',  $username, $currentGood['title'], $goodCount, $currentGood['image']);
                $stmt->execute();
                $this->giveDonate($goodParams);
                return $this->payment_replay($params, "Привилегия выдана!", "success");
                break;
            default :
                return $this->payment_replay($params, "Успешно!", "success");
                break;
        }
        return $this->payment_replay($params, "Ошибка обработки!");
    }

    public function qiwi_payment($order, $amount) {
        $goodParams = explode('-', $order);
        if(count($goodParams) < 3) die();

        $serverKey = $goodParams[0];
        $username = $goodParams[1];
        $goodId = $goodParams[2];
        $goodAmount = isset($goodParams[3]) ? intval($goodParams[3]) : 0;

        $serverConfig = isset($this->cfg['servers'][$serverKey]) ? $this->cfg['servers'][$serverKey] : null;
        if ($serverConfig == null) die('1');

        $currentGood = findGood($serverConfig, $goodId);
        if ($currentGood == null) die('2');

        if (isset($currentGood['amounted']) && $currentGood['amounted'] && !isset($goodParams[3])) die();

        if((int) calculatePrice($username, $currentGood, $goodAmount, $this->cfg, $serverConfig) != $amount) die();

        $db = mysqli_connect($this->cfg['db']['host'], $this->cfg['db']['user'], $this->cfg['db']['password'], $this->cfg['db']['db']) or die('Ошибка подключения к БД, обновите страницу.');
        $stmt = $db->prepare("INSERT INTO `purchases` (`username`, `title`, `amount`, `image`) VALUES (?, ?, ?, ?)");
        $goodCount = (isset($currentGood['amounted']) && $currentGood['amounted']) ? $goodAmount : null;
        $stmt->bind_param('ssds',  $username, $currentGood['title'], $goodCount, $currentGood['image']);
        $stmt->execute();
        $this->giveDonate($goodParams);
        die('Ok');
    }
	
	public function enot_payment($order, $amount, $sign) {
		$real_sign = md5($this->cfg['enot']['public_key'].':'.$amount.':'.$this->cfg['enot']['secret_key'].':'.$order);

		if($sign != $real_sign) {
			die('Не проверена подпись!');
		}
		
        $goodParams = explode('-', $order);
        if(count($goodParams) < 3) die();

        $serverKey = $goodParams[0];
        $username = $goodParams[1];
        $goodId = $goodParams[2];
        $goodAmount = isset($goodParams[3]) ? intval($goodParams[3]) : 0;

        $serverConfig = isset($this->cfg['servers'][$serverKey]) ? $this->cfg['servers'][$serverKey] : null;
        if ($serverConfig == null) die('1');

        $currentGood = findGood($serverConfig, $goodId);
        if ($currentGood == null) die('2');

        if (isset($currentGood['amounted']) && $currentGood['amounted'] && !isset($goodParams[3])) die();

        if((int) calculatePrice($username, $currentGood, $goodAmount, $this->cfg, $serverConfig) != $amount) die();

        $db = mysqli_connect($this->cfg['db']['host'], $this->cfg['db']['user'], $this->cfg['db']['password'], $this->cfg['db']['db']) or die('Ошибка подключения к БД, обновите страницу.');
        $stmt = $db->prepare("INSERT INTO `purchases` (`username`, `title`, `amount`, `image`) VALUES (?, ?, ?, ?)");
        $goodCount = (isset($currentGood['amounted']) && $currentGood['amounted']) ? $goodAmount : null;
        $stmt->bind_param('ssds',  $username, $currentGood['title'], $goodCount, $currentGood['image']);
        $stmt->execute();
        $this->giveDonate($goodParams);
        die('Ok');
    }

    public function getSignature($method, array $params) {
        $delimiter = "{up}";
        ksort($params);
        unset($params['sign']);
        unset($params['signature']);
        return hash('sha256', $method.$delimiter.join($delimiter, $params).$delimiter.$this->cfg['unitpay']['key']);
    }

    public function payment_replay($params, $message, $type = "error"){
        if($type == "success")
        {
            return json_encode(
                array(
                    "jsonrpc" => "2.0",
                    "result" => array(
                        "message" => $message
                    ),
                    'id' => $params['projectId']
                )
            );
        }
        else
        {
            return json_encode(
                array(
                    "jsonrpc" => "2.0",
                    "error" => array(
                        "code" => -32000,
                        "message" => $message
                    ),
                    'id' => $params['projectId']
                )
            );
        }
    }

    public function giveDonate($goodParams, $rLength = 0) {
        if ($rLength == 10) {
            return;
        }

        $serverKey = $goodParams[0];
        $serverConfig = isset($this->cfg['servers'][$serverKey]) ? $this->cfg['servers'][$serverKey] : null;
        if (!$serverConfig) return;

        $currentGood = findGood($serverConfig, $goodParams[2]);
        if (!$currentGood) return;

        $cmd = str_replace(['%user%', '%group%', '%amount%'], [$goodParams[1], $goodParams[2], isset($goodParams[3]) ? $goodParams[3] : 0], $currentGood['command']);
        
        $rconHost = isset($serverConfig['rcon']['ip']) ? $serverConfig['rcon']['ip'] : $this->cfg['server_ip'];
        $rconPort = $serverConfig['rcon']['port'];
        $rconPassword = $serverConfig['rcon']['password'];

        $rcon = new Rcon($rconHost, $rconPort, $rconPassword, 10);

        if (@$rcon->connect()) {
            @$rcon->send_command($cmd);
        } else {
            $this->giveDonate($goodParams, $rLength + 1);
        }
    }
}
