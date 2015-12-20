<?php

header('Content-type: text/html; charset=utf-8');
set_time_limit(0);
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/function.php';

use Symfony\Component\Finder\Finder;

$config = require __DIR__.'/config.php';

info(' ');

// var_dump([
//     '/backup/'.$config['database']['filename'] => $config['database']['target'].'/'.$config['database']['filename'],
//     '/backup/'.basename($config['archive']['target']) => $config['archive']['target'],
// ]);

// exit;

database_backup($config);

$dir = __DIR__.'/../h/';
$finder = new Finder();
$finder->files()->in($dir);

$source = get_source($finder, '/h/'.$now.'/');
local_backup($config, $source);

$finder2 = new Finder();
$finder2->files()->in($dir)->exclude('upload');
$source2 = get_source($finder2);
archive_backup($config, $source2);

ftp_backup($config, [
    '/backup/'.$config['database']['filename'].'.gz' => $config['database']['target'].'/'.$config['database']['filename'].'.gz',
    '/backup/'.basename($config['archive']['target']) => $config['archive']['target'],
]);

exit;

// if ($config['database']['enabled'] === true) {
//     // $backupFiles = array_merge($backupFiles, database_backup($config));
//     database_backup($config);
// }

// if ($config['local']['enabled'] === true) {
//     local_backup($config, $backupFiles);
// }

// if ($config['archive']['enabled'] === true) {
//     archive_backup($config, $backupFiles);
// }

// if ($config['ftp']['enabled'] === true) {
//     ftp_backup($config, $backupFiles);
// }

echo 'finished';
