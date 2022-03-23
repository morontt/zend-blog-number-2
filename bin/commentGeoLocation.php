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
$optionsArray = $configObject->backend_api->toArray();

$user = $optionsArray['user'] ?? '';
$wsseKey = $optionsArray['wsse_key'] ?? '';
$apiUrl = $optionsArray['url'] ?? '';
try {
    $nonce = random_bytes(12);
} catch (\Exception $e) {
    $nonce = openssl_random_pseudo_bytes(12);
}

$created = date('c');
$digest = base64_encode(
    sha1($nonce . $created . $wsseKey, true)
);

$wsseHeader = sprintf(
    'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
    $user,
    $digest,
    base64_encode($nonce),
    $created
);

$ch = curl_init($apiUrl . "/api/comments/geo-location");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    $wsseHeader,
]);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_exec($ch);
curl_close($ch);
