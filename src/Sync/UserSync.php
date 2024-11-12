<?php
namespace Svobo\SmsConsiderPpUa\Sync;

use Svobo\SmsConsiderPpUa\Database\Database;

class UserSync {
    private $db;
    private $ldapConfig;

    public function __construct(Database $db, $ldapConfig) {
        $this->db = $db->getConnection();
        $this->ldapConfig = $ldapConfig;
    }

    public function sync() {
        $connection = ldap_connect($this->ldapConfig['host'], $this->ldapConfig['port']);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_bind($connection, $this->ldapConfig['admin_user'], $this->ldapConfig['admin_password']);

        $filter = "(objectClass=person)";
        $result = ldap_search($connection, $this->ldapConfig['base_dn'], $filter);
        $entries = ldap_get_entries($connection, $result);

        foreach ($entries as $entry) {
            if (isset($entry['uid'][0])) {
                $username = $entry['uid'][0];
                // Проверяем и обновляем информацию в базе данных
                $stmt = $this->db->prepare('REPLACE INTO users (username) VALUES (?)');
                $stmt->bind_param('s', $username);
                $stmt->execute();
            }
        }

        ldap_close($connection);
    }
}
