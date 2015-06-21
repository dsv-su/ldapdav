<?php
use DsvSu\LdapDav;
use Sabre\DAV;
use Sabre\CardDAV;

error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

require '../vendor/autoload.php';

$server = new DAV\Server(new LdapDav\AddressBook('dc=dsv,dc=su,dc=se'));
$server->setBaseUri($_SERVER['SCRIPT_NAME']);

$server->addPlugin(new DAV\Browser\Plugin());
$server->addPlugin(new CardDAV\Plugin());
//$server->addPlugin(new DAV\Sync\Plugin());

$server->exec();
