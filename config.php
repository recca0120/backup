<?php

$now = date('Ymd');
$config = [
    'database' => [
        'enabled' => true,
        // 'dsn' => 'mysql:host=localhost;dbname=test',
        // 'username' => 'root',
        // 'password' => 'root',
        'filename'     => 'mysql'.$now.'.sql',
        'target'       => __DIR__.'/backup/mysql',
        'compress'     => 'gzip',
        'remoteFolder' => 'mysql',
    ],
    'local' => [
        'enabled' => true,
        'target'  => __DIR__.'/backup',
    ],
    'archive' => [
        'enabled' => true,
        'target'  => __DIR__.'/backup/archive'.$now.'.zip',
    ],
    'ftp' => [
        'enabled'  => false,
        'host'     => '',
        'username' => '',
        'password' => '',
        'port'     => 21,
        'root'     => '/',
        'passive'  => true,
        'ssl'      => false,
        'timeout'  => 30000,
    ],
];

return $config;
