CREATE TABLE `id20965121_teteturnieje`.`masters` (
    `masterId` INT NOT NULL AUTO_INCREMENT,
    `Login` VARCHAR(12) NULL,
    `Pass` VARCHAR(255) NULL DEFAULT NULL,
    `OverallScore` DOUBLE NOT NULL DEFAULT '0',
    `Created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastLogged` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`masterId`)
) ENGINE = InnoDB;


INSERT INTO `masters`(`Login`, `Pass`, `OverallScore`, `Created`, `LastLogged`) 
SELECT Login, Pass, OverallScore, Created, LastLogged from users where Pass is not null;


ALTER TABLE `users` ADD `masterId` INT NULL DEFAULT NULL AFTER `Pass`;

