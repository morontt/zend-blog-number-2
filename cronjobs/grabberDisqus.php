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

$configObject = new Zend_Config_Ini(realpath(__DIR__ . '/../application/configs/application.ini'), $environment);
$dbOptions = $configObject->resources->db;

$db = Zend_Db::factory($dbOptions);
Zend_Db_Table_Abstract::setDefaultAdapter($db);

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
$threads = array();
foreach ($disqusPosts as $item) {
    $comment = array(
        'id' => (int) $item->id,
        'parent' => $item->parent,
        'thread' => (int) $item->thread,
        'message' => $item->raw_message,
        'created' => $item->createdAt,
        'author' => array(
            'name' => $item->author->name,
            'emailHash' => $item->author->emailHash,
            'website' => $item->author->url,
            'id' => (int) $item->author->id,
        ),
    );
    $comments[] = $comment;
    $threads[] = $comment['thread'];
}
$threads = array_unique($threads);

require_once('Commentators.php');
$commentatorsTable = new Application_Model_Commentators();
foreach ($comments as $key => $comment) {
    $comments[$key]['commentator_id'] = $commentatorsTable->getDisqusAuthor($comment['author']);
}

require_once('Posts.php');
require_once('Row/Post.php');
$postsTable = new Application_Model_Posts();
$postsArray = $postsTable->getPostsByDisqusThreads($threads);

if (!empty($postsArray['unknown'])) {
    $disqusThreads = $disqus->forums->listThreads(
        array(
            'forum' => $disqusOptions->shortname,
            'thread' => $threads,
        )
    );

    $postsTable->saveDisqusThreads($disqusThreads);

    $postsArray = $postsTable->getPostsByDisqusThreads($threads);
}