<?php
// src/config.php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Настройки подключения к MySQL
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($mysqli->connect_error) {
    die('Ошибка подключения к БД (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Настройки подключения к LDAP
$ldap_config = [
    'hosts'    => [$_ENV['LDAP_HOST']],
    'base_dn'  => $_ENV['LDAP_BASE_DN'],
    'username' => $_ENV['LDAP_USERNAME'],
    'password' => $_ENV['LDAP_PASSWORD'],
];
