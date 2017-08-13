<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

$optionsArray = [
    'env=w' => 'application environment',
];

$optsObject = new Zend_Console_Getopt($optionsArray);
$optsObject->parse();

if (isset($optsObject->env)) {
    $environment = $optsObject->env;
} else {
    $environment = 'production';
}

$configObject = new Zend_Config_Ini(
    realpath(dirname(__FILE__) . '/../application/configs/application.ini'),
    $environment
);
$optionsArray = $configObject->mail_smtp->toArray();

$pathToSwiftmailer = realpath(dirname(__FILE__) . '/../library/Swiftmailer/lib/swift_required.php');
require_once($pathToSwiftmailer);

$spoolPath = realpath(dirname(__FILE__) . '/../spool');
$spool = new Swift_FileSpool($spoolPath);
$spool->setMessageLimit(20);
$spool->setTimeLimit(30);

$transport = Swift_SmtpTransport::newInstance();
$transport->setHost($optionsArray['host'])
          ->setPort($optionsArray['port'])
          ->setEncryption($optionsArray['encryption'])
          ->setUsername($optionsArray['username'])
          ->setPassword($optionsArray['password'])
;

$spool->flushQueue($transport);
