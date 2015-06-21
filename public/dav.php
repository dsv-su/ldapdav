<?php
require '../vendor/autoload.php';

use Sabre\DAV;
use Sabre\CardDAV;

set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

$server = new DAV\Server(new SukatAddressBook('dc=dsv,dc=su,dc=se'));
$server->setBaseUri($_SERVER['SCRIPT_NAME']);

$server->addPlugin(new DAV\Browser\Plugin());
$server->addPlugin(new CardDAV\Plugin());
//$server->addPlugin(new DAV\Sync\Plugin());

$server->exec();
