<?php

use Ifsnop\Mysqldump\Mysqldump;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

function info($message)
{
    echo $message;
    flush();
    ob_flush();
    ob_implicit_flush();
}

function get_source($finder, $prefix = '')
{
    $results = [];
    foreach ($finder as $file) {
        $results[$prefix.$file->getRelativePathname()] = $file->getRealpath();
    }

    return $results;
}

function database_backup($config)
{
    info(__FUNCTION__.' start...<br />');
    $file = $config['database']['target'].'/'.$config['database']['filename'];

    if (is_dir(dirname($file)) === false) {
        mkdir(dirname($file), 0777, true);
    }
    try {
        $compress = Mysqldump::NONE;
        switch ($config['database']['compress']) {
            case 'gzip':
            case Mysqldump::GZIP:
                $file .= '.gz';
                $compress = Mysqldump::GZIP;
                break;
        }

        $dump = new Mysqldump($config['database']['dsn'], $config['database']['username'], $config['database']['password'], [
            'single-transaction' => false,
            'compress'           => $compress,
        ]);
        $dump->start($file);
    } catch (\Exception $e) {
        info('mysqldump-php error: '.$e->getMessage());
    }

    info(__FUNCTION__.' finished...<br />');

    $remote = $config['database']['remoteFolder'].'/'.basename($file);

    return [
        $remote => $file,
    ];
}

function local_backup($config, $files)
{
    info(__FUNCTION__.' start...<br />');
    $fs = new Filesystem(new Local($config['local']['target']));
    info('local backup...<br />');
    foreach ($files as $remote => $file) {
        // info(sprintf('put: %s to %s<br />', $file, $remote));
        try {
            $fp = fopen($file, 'r+');
            $fs->putStream($remote, $fp);
            fclose($fp);
        } catch (Exception $e) {
            info('<span class="color:red">error: '.$e->getMessage().'</span>');
            info('<span class="color:red">file: '.$file.'</span>');
        }
    }
    info(__FUNCTION__.' finished...<br />');
}

function ftp_backup($config, $files)
{
    info(__FUNCTION__.' start...<br />');
    $fs = new Filesystem(new Ftp($config['ftp']));
    foreach ($files as $remote => $file) {
        // info(sprintf('put: %s to %s<br />', $file, $remote));
        try {
            $fp = fopen($file, 'r+');
            $fs->putStream($remote, $fp);
            fclose($fp);
        } catch (Exception $e) {
            info('<span class="color:red">error: '.$e->getMessage().'</span>');
            info('<span class="color:red">file: '.$file.'</span>');
        }
    }
    info(__FUNCTION__.' finished...<br />');
}

function archive_backup($config, $files)
{
    info(__FUNCTION__.' start...<br />');
    $fs = new Filesystem(new ZipArchiveAdapter($config['archive']['target']));
    foreach ($files as $remote => $file) {
        // info(sprintf('put: %s to %s<br />', $file, $remote));
        try {
            $fp = fopen($file, 'r+');
            $fs->putStream($remote, $fp);
            fclose($fp);
        } catch (Exception $e) {
            info('<span class="color:red">error: '.$e->getMessage().'</span>');
            info('<span class="color:red">file: '.$file.'</span>');
        }
    }
    $fs->getAdapter()->getArchive()->close();
    info(__FUNCTION__.' finished...<br />');
}
