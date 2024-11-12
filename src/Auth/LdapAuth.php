<?php
namespace Svobo\SmsConsiderPpUa\Auth;

class LdapAuth implements AuthInterface {
    private $ldapConfig;

    public function __construct($ldapConfig) {
        $this->ldapConfig = $ldapConfig;
    }

    public function login($username, $password) {
        // Формируем LDAP URI
        $ldapUri = $this->ldapConfig['host'];
    
        if (isset($this->ldapConfig['port'])) {
            $ldapUri .= ':' . $this->ldapConfig['port'];
        }
    
        $connection = ldap_connect($ldapUri);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    
        if (!$connection) {
            die('Не удалось подключиться к LDAP-серверу');
        }
    
        // Привязываемся с учетными данными администратора
        if (@ldap_bind($connection, $this->ldapConfig['admin_user'], $this->ldapConfig['admin_password'])) {
            // Экранируем имя пользователя
            $escapedUsername = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
    
            // Формируем фильтр поиска
            $filter = "(sAMAccountName={$escapedUsername})";
            $result = ldap_search($connection, $this->ldapConfig['base_dn'], $filter);
    
            if ($result === false) {
                error_log("Ошибка LDAP поиска: " . ldap_error($connection));
                echo "Ошибка при поиске пользователя";
                return false;
            }
    
            $entries = ldap_get_entries($connection, $result);
    
            if ($entries['count'] > 0) {
                $userDN = $entries[0]['dn'];
    
                // Пробуем привязаться с учетными данными пользователя
                if (@ldap_bind($connection, $userDN, $password)) {
                    // Проверяем, отключен ли пользователь
                    $userAccountControl = $entries[0]['useraccountcontrol'][0];
                    if ($userAccountControl & 2) {
                        echo "Пользователь отключен";
                        return false;
                    }
    
                    // Проверяем наличие группы 'sms'
                    $groups = $entries[0]['memberof'] ?? [];
                    $hasGroup = false;
    
                    foreach ($groups as $groupDN) {
                        if (strpos($groupDN, "CN={$this->ldapConfig['group']}") !== false) {
                            $hasGroup = true;
                            break;
                        }
                    }
    
                    if (!$hasGroup) {
                        echo "У вас отсутствуют права для доступа";
                        return false;
                    }
    
                    $_SESSION['user'] = $entries[0];
                    return true;
                } else {
                    echo "Логин или пароль не верен";
                    return false;
                }
            } else {
                echo "Логин или пароль не верен";
                return false;
            }
        } else {
            die('Не удалось привязаться с учетными данными администратора');
        }
    
        ldap_close ($connection);
    }
    
}
