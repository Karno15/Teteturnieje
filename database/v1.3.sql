ALTER TABLE `turuserzy` CHANGE `CurrentScore` `CurrentScore` DOUBLE NOT NULL DEFAULT '0';

ALTER TABLE `users` CHANGE `OverallScore` `OverallScore` DOUBLE NOT NULL DEFAULT '0';

ALTER TABLE `pytania` CHANGE `Rewards` `Rewards` DOUBLE NOT NULL;

ALTER TABLE `answers` CHANGE `Points` `Points` DOUBLE NOT NULL;