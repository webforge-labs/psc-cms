CREATE USER 'psc-cms'@'localhost' IDENTIFIED BY 'L6W2vHEbKLjeUEr2';

GRANT USAGE ON * . * TO 'psc-cms'@'localhost' IDENTIFIED BY 'L6W2vHEbKLjeUEr2' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

CREATE DATABASE IF NOT EXISTS `psc-cms` ;
GRANT ALL PRIVILEGES ON `psc-cms` . * TO 'psc-cms'@'localhost';

CREATE DATABASE IF NOT EXISTS `psc-cms_tests` ;
GRANT ALL PRIVILEGES ON `psc-cms_tests` . * TO 'psc-cms'@'localhost';
