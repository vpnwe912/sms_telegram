# SMS Telegram
Прием смс из Telegramm авторизация через Active Directory с сохранением в базу данных mysql и выводом

## **Installation**
Установка зависимостей

` 
composer install 
`

## **Изменить настройки подключения к MySQL и LDAP**
измениете название .env-example на .env после отредактируйте его

```PHP
# Настройки MySQL
DB_HOST=localhost
DB_USER=root
DB_PASS=yourpassword
DB_NAME=yourdatabase

# Настройки LDAP
LDAP_HOST=ldap.example.com
LDAP_BASE_DN=dc=example,dc=com
LDAP_USERNAME=cn=admin,dc=example,dc=com
LDAP_PASSWORD=ldappassword
```

## **License**
MIT License

## **Настройка nginx для красивых URL**
```
 location / {
        try_files $uri $uri/ @rewrite;
    }

    location @rewrite {
        rewrite ^/([a-zA-Z0-9_-]+)$ /pages/$1.php last;
    }

```