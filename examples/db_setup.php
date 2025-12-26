<?php

use Dotenv\Dotenv;
use Hegentopf\EasyOrm\connection\ConnectionManager;
use Hegentopf\EasyOrm\connection\MySQLConnection;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DATABASE_HOST'];
$port = $_ENV['DATABASE_PORT'];
$database = $_ENV['DATABASE_DB'];
$user = $_ENV['DATABASE_USER'];
$password = $_ENV[ 'DATABASE_PW' ];

$connection = new MySQLConnection( $database, $user, $password, $host, $port );
ConnectionManager::setConnection( $connection );
