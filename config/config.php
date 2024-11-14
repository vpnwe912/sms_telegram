<?php
return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'sms_telegram',
        'user' => 'root',
        'password' => 'so7h8m90a',
        'charset'  => 'utf8mb4',
    ],

    'auth' => [
        'method' => 'ldap', // Возможные значения: 'local', 'ldap'
    ],

    'zadarma' => [
        'api_secret' => 'ca867ebcdfd8ac5dfae0', // Параметры API Zadarma, Ваш секретный ключ API
    ],
    
    'ldap' => [
        'host' => 'ldap://195.78.93.180',
        'port' => 389,
        'base_dn' => 'dc=GPR,dc=AD',
        'admin_user' => 'cn=Администратор,cn=Users,dc=GPR,dc=AD',
        'admin_password' => 'KeWiReSon!',
        'group' => 'SMS',
    ],
];
