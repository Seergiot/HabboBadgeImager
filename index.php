<?php

require_once './badge/BadgeImageManager.php';

$mainHostConfig = [
    'username' => 'root',
    'password' => '',
    'host'     => 'localhost',
    'database' => ''
];

$pdo = new PDO("mysql:dbname={$mainHostConfig['database']};host={$mainHostConfig['host']}", $mainHostConfig['username'], $mainHostConfig['password']);

header('Content-type: image/gif');
$bdgmgr = new BadgeImageManager();

$img = $bdgmgr->getGroupBadge('b19134s02054s01134s195115s189113');
imagegif($img);
imagedestroy($img);

function fetchAll(string $sql)
{
    global $pdo;

    $args = func_get_args();
    array_shift($args);
    $statement = $pdo->prepare($sql);
    $statement->execute($args);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}