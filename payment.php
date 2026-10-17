<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/library/DeliverPayments.class.php';
$Delivery = new Delivery;

if (isset($_REQUEST['method']) && isset($_REQUEST['params'])) {
    unset($_SESSION);
    echo $Delivery->unitpay_payment(strtolower($_REQUEST['method']) , $_REQUEST['params']);
    exit();
} else {
    header('Location: /');
}

