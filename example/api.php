<?php
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../vendor/autoload.php';

$config = new ExtDirect\Config(include __DIR__ . '/config/extdirect.php');

$discoverer = new ExtDirect\Discoverer($config);
$discoverer->start();