CREATE DATABASE IF NOT EXISTS sms_telegram DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

USE sms_telegram;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

-- Таблица для хранения пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255),
    salt VARCHAR(255),
    ad_user TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL
);

-- Таблица для хранения настроек авторизации
CREATE TABLE IF NOT EXISTS auth_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auth_type ENUM('local', 'ad') NOT NULL DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);