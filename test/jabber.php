#!/usr/bin/php
<?php       
require_once(dirname(__FILE__).'/../libraries/XMPPHP/BOSH.php');
require_once(dirname(__FILE__).'/../libraries/XMPPHP/XMPP.php');

$start = microtime(true);

/*$conn = new XMPPHP_XMPP('kirby.inspi.re', 5222, 'admin', 'roumb5l5', 'xmpphp', 'kirby.inspi.re', $printlog=False, $loglevel=LOGGING_INFO);
$conn->useEncryption(false);
$conn->connect();
$conn->processUntil('session_start');
$conn->register('bob', 'kangaroo');
$conn->disconnect();*/

$conn = new XMPPHP_BOSH('kirby.inspi.re', 5280, 'bob', 'kangaroo', 'xmpphp', 'kirby.inspi.re', $printlog=False, $loglevel=LOGGING_INFO);
$conn->connect('kirby.inspi.re:5280/http-bind', 1, true);
$conn->processUntil('session_start');
print_r($conn->getSession());
$conn->disconnect();

echo (microtime(true) - $start).' ms taken to register user';
?>