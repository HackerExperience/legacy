<?php

$wikiPath = 'http://localhost/wiki/';

$version = '0.8';
$versionStatus = ' BETA';
$gameTitle = 'Hacker Experience '.$version.$versionStatus; //título do jogo

static $processActions = Array(
    
    'DOWNLOAD' => '1',
    'UPLOAD' => '2',
    'DELETE' => '3',
    'HIDE' => '4',
    'SEEK' => '5',
    'COLLECT' => '6', //DEPRECATED
    'AV' => '7',
    'E_LOG' => '8',
    'D_LOG' => '9', //DEPRECATED
    'FORMAT' => '10',
    'HACK' => '11',
    'BANK_HACK' => '12',
    'INSTALL' => '13',
    'UNINSTALL' => '14',
    'PORT_SCAN' => '15',
    'HACK_XP' => '16',
    'RESEARCH' => '17',
    'UPLOAD_XHD' => '18',
    'DOWNLOAD_XHD' => '19',
    'DELETE_XHD' => '20',
    'NMAP' => '22',
    'ANALYZE' => '23',
    'INSTALL_DOOM' => '24',
    'RESET_IP' => '25',
    'RESET_PWD' => '26',
    'DDOS' => '27',
    'INSTALL_WEBSERVER' => '28',

);

static $processTimeConfig = Array(
    
    'DOWNLOAD_MIN' => '20',
    'DOWNLOAD_MAX' => '7200',
    'UPLOAD_MIN' => '20',
    'UPLOAD_MAX' => '7200',
    'DELETE_MIN' => '20', //hide must be faster than delete*
    'DELETE_MAX' => '1200',
    'HIDE_MIN' => '5',
    'HIDE_MAX' => '1200',
    'SEEK_MIN' => '5',
    'SEEK_MAX' => '1200',
    'INSTALL_MIN' => '4',
    'INSTALL_MAX' => '1200',
    'AV_MIN' => '60',
    'AV_MAX' => '600',
    'LOG_MIN' => '4',
    'LOG_MAX' => '60',
    'FORMAT_MIN' => '1200',
    'FORMAT_MAX' => '3600',
    
);

?>
