<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    realpath(dirname(__FILE__) . '/../application/models'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

$optionsArray = array(
    'env=w' => 'application environment',
);

$optsObject = new Zend_Console_Getopt($optionsArray);

$optsObject->parse();

if (isset($optsObject->env)) {
    $environment = $optsObject->env;
} else {
    $environment = 'production';
}

$configObject = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/../application/configs/application.ini'), $environment);
$optionsArray = $configObject->resources->db;

$db = Zend_Db::factory($optionsArray);

require_once 'Avatar.php';
$avatarTable = new Application_Model_Avatar($db);
$avatar = $avatarTable->getOldenRow();

$defaultsArray = array(
    'wavatar',
    'monsterid',
);

if ($avatar) {
    $client = new Zend_Http_Client(null, array(
        'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11',
    ));

    $hash = $avatar->hash;
    $defaultImage = $defaultsArray[(int) $avatar->default];

    $uri = "http://www.gravatar.com/avatar/{$hash}?d={$defaultImage}";

    $client->setUri($uri);
    $response = $client->request();
    $headers  = $response->getHeaders();

    $contentType = $headers['Content-type'];
    if (strpos($contentType, 'image') !== false) {
        $fileName = $hash . str_replace('image/', '.', $contentType);
        $newFile = realpath(dirname(__FILE__) . '/../img/avatar') . DIRECTORY_SEPARATOR . $fileName;
        copy($uri, $newFile);

        if (file_exists($newFile)) {
            $avatar->src           = $fileName;
            $avatar->last_modified = new Zend_Db_Expr('NOW()');
            $avatar->save();
        }
    }
}
