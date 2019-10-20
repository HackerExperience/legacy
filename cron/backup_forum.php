<?php

require '/var/www/ses/aws-autoloader.php';

use Aws\S3\S3Client;

$name = date('Ymd-Hi').'_forum';

//ENTER THE RELEVANT INFO BELOW
$mysqlDatabaseName ='forum';
$mysqlUserName ='forum';
$mysqlPassword ='REDACTED';
$mysqlHostName ='localhost';
$mysqlExportPath ='/var/web/backup/forum/'.$name.'.sql';

//DO NOT EDIT BELOW THIS LINE
//Export the database and output the status to the page
$command='/usr/local/mysql/bin/mysqldump --opt -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p\'' .$mysqlPassword .'\' ' .$mysqlDatabaseName .' > ' .$mysqlExportPath;
exec($command);

$client = S3Client::factory(array(
    'key'    => 'REDACTED',
    'secret' => 'REDACTED'
));

$result = $client->putObject(array(
    'Bucket'     => 'REDACTED',
    'Key'    => '/'.date('Y').'/'.date('m').'/'.date('d').'/'.date('Ymd-Hi').'_forum',
    'SourceFile' => '/var/web/backup/forum/'.$name.'.sql'
));


?>
