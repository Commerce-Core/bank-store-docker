CREATE DATABASE IF NOT EXISTS `exampledb`;
CREATE DATABASE IF NOT EXISTS `exampledb2`;
CREATE DATABASE IF NOT EXISTS `exampledb3`;

GRANT ALL PRIVILEGES ON `exampledb`.* TO 'exampleuser'@'%';
GRANT ALL PRIVILEGES ON `exampledb2`.* TO 'exampleuser'@'%';
GRANT ALL PRIVILEGES ON `exampledb3`.* TO 'exampleuser'@'%';