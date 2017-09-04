<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

$configObject = new Zend_Config_Ini(
    realpath(dirname(__FILE__) . '/../application/configs/application.ini'),
    'production'
);
$optionsArray = $configObject->telegram->toArray();

$data = $argv[1];

$ch = curl_init("https://api.telegram.org/bot{$optionsArray['token']}/sendMessage");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);

curl_exec($ch);
