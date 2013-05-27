<?php

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
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

date_default_timezone_set('Europe/Kiev');

$currentDateTime = new \DateTime("now");
$intervalObject = new \DateInterval('P15D');

$currentDateTime->sub($intervalObject);
$dateTimeString = $currentDateTime->format('Y-m-d H:i:s');

$rawSql = <<<RAWSQL
SELECT
    `id`, `post_id`, `user_agent_id`, `ip_addr`, `time_created`
FROM `tracking`
WHERE
    `time_created` < ?
ORDER BY `id`
LIMIT 200;
RAWSQL;

$insertSqlHeader = "INSERT INTO `tracking_archive` (`post_id`, `user_agent_id`, `ip_addr`, `time_created`) VALUES ";
$removeTrackingSql = "DELETE FROM `tracking` WHERE `id` IN ";

$result = $db->fetchAll($rawSql, $dateTimeString);

while (count($result)) {
    $idArray = array();
    $dataArray = array();
    foreach ($result as $item) {
        $idArray[] = $item['id'];
        $postId = $item['post_id'] ? $item['post_id'] : 0;
        $trackingAgentId = $item['user_agent_id'] ? $item['user_agent_id'] : 0;
        $dataArray[] = "({$postId}, {$trackingAgentId}, '{$item['ip_addr']}', '{$item['time_created']}')";
    }

    $idString = '(' . implode(', ', $idArray) . ');';
    $dataValuesString = implode(', ', $dataArray) . ';';

    $db->query($insertSqlHeader . $dataValuesString);
    $db->query($removeTrackingSql . $idString);

    $result = $db->fetchAll($rawSql, $dateTimeString);
}
