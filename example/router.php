<?php
require_once __DIR__ . '/../vendor/autoload.php';

$config = new ExtDirect\Config(include __DIR__ . '/config/extdirect.php');

$discoverer = new ExtDirect\Router($config);
$discoverer->route();