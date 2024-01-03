ALTER TABLE `turnieje` ADD `Columns` INT NOT NULL AFTER `CurrentQuest`;

UPDATE `turnieje` SET `Columns` = '5';

ALTER TABLE `turnieje` CHANGE `Columns` `Columns` INT(11) NOT NULL DEFAULT '5';