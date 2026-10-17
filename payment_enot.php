<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/library/DeliverPayments.class.php';
$Delivery = new Delivery;

$Delivery->enot_payment($_REQUEST['merchant_id'], $_REQUEST['amount'], $_REQUEST['sign_2']);

