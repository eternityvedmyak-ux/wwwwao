-- Создание базы данных autodonate, если она не существует
CREATE DATABASE IF NOT EXISTS `autodonate` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autodonate`;

DROP TABLE IF EXISTS `luckperms_players`;
-- Таблица игроков (имитация таблицы LuckPerms)
CREATE TABLE IF NOT EXISTS `luckperms_players` (
  `uuid` VARCHAR(36) NOT NULL DEFAULT '',
  `username` VARCHAR(16) NOT NULL,
  `primary_group` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`uuid`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `purchases`;
-- Таблица покупок
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(64) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `amount` DOUBLE DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка демонстрационных данных для тестирования доплаты/скидок
INSERT INTO `luckperms_players` (`uuid`, `username`, `primary_group`) VALUES
('d2c67b93-80b6-455b-bb6b-a7de509b5523', 'TestPlayer1', 'default'),
('f5b8c9d1-30c2-4aa8-92b1-12c8509b52a1', 'TestPlayer2', 'voin'),
('e3b5c678-20a1-4321-b1b1-111111111111', 'AdminPlayer', 'admin');

-- Вставка демонстрационных данных для списка последних покупок
INSERT INTO `purchases` (`username`, `title`, `amount`, `image`) VALUES
('TestPlayer1', 'Ключи к кейсу с донатом', 5, 'https://i.imgur.com/3q0MLa2.png'),
('TestPlayer2', '[РЫЦАРЬ]', NULL, 'https://i.imgur.com/kjLgmhD.png'),
('Steve', 'Разбан', NULL, 'https://i.imgur.com/GuxK0vI.png');
