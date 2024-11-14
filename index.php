<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


require 'vendor/autoload.php';

use Svobo\SmsConsiderPpUa\Database\Database;
use Svobo\SmsConsiderPpUa\Auth\LocalAuth;
use Svobo\SmsConsiderPpUa\Auth\LdapAuth;

$config = require 'config/config.php';

$db = new Database($config['db']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authMethod = $config['auth']['method'];

    if ($authMethod === 'local') {
        $auth = new LocalAuth($db);
    } else {
        $auth = new LdapAuth($config['ldap']);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header('Location: /dashboard');
        exit;
    }
}

include 'pages/login.php';

