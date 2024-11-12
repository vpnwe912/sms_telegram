<?php
return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'sms_telegram',
        'user' => 'root',
        'password' => 'so7h8m90a',
    ],
    'auth' => [
        'method' => 'ldap', // Возможные значения: 'local', 'ldap'
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
