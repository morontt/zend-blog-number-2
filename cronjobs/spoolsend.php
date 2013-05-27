<?php

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
)));

//get SMTP-config
require_once(realpath(dirname(__FILE__) . '/../library/Zend/Config/Ini.php'));
$configObject = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/../application/configs/application.ini'), 'production');
$optionsArray = $configObject->mail_smtp->toArray();

//swift mailer
$pathToSwiftmailer = realpath(dirname(__FILE__) . '/../library/Swiftmailer/lib/swift_required.php');
require_once($pathToSwiftmailer);

//create file spool
$spoolPath = realpath(dirname(__FILE__) . '/../spool');
$spool = new Swift_FileSpool($spoolPath);
$spool->setMessageLimit(20);
$spool->setTimeLimit(30);

//create SMTP-transport
$transport = Swift_SmtpTransport::newInstance();
$transport->setHost($optionsArray['host'])
          ->setPort($optionsArray['port'])
          ->setEncryption($optionsArray['encryption'])
          ->setUsername($optionsArray['username'])
          ->setPassword($optionsArray['password']);

$spool->flushQueue($transport);