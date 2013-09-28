<?php
/**
 * Created by JetBrains PhpStorm.
 * User: morontt
 * Date: 28.09.13
 * Time: 12:39
 */

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
$dbOptions = $configObject->resources->db;

$db = Zend_Db::factory($dbOptions);

$disqusOptions = $configObject->disqus;

require_once(realpath(__DIR__ . '/../library/disqusapi/disqusapi.php'));

$disqus = new DisqusAPI($disqusOptions->secretKey);

$disqusPosts = $disqus->forums->listPosts(
    array(
        'forum' => $disqusOptions->shortname,
        'limit' => 100,
        'order' => 'asc',
    )
);

$comments = array();
foreach ($disqusPosts as $item) {
    $comment = array(
        'id' => $item->id,
        'parent' => $item->parent,
        'thread' => $item->thread,
        'message' => $item->raw_message,
        'created' => $item->createdAt,
        'author' => array(
            'name' => $item->author->name,
            //'mail' => $item->author->email,
            'mailHash' => $item->author->emailHash,
            'website' => $item->author->url,
        ),
    );
    $comments[] = $comment;
}