CREATE USER
    IF NOT EXISTS 'wp_test_user' @'%' IDENTIFIED BY 'password';

CREATE DATABASE IF NOT EXISTS `wp_test_db`;

GRANT ALL PRIVILEGES ON `wp_test_db`.* TO 'wp_test_user'@'%';