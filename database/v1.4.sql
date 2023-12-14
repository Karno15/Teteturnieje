-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 05 Gru 2023, 12:53
-- Wersja serwera: 10.4.27-MariaDB
-- Wersja PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `id20965121_teteturnieje`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `questbids`
--

CREATE TABLE `questbids` (
  `questbidId` int(11) NOT NULL,
  `PytId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `questbids`
--
ALTER TABLE `questbids`
  ADD PRIMARY KEY (`questbidId`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `questbids`
--
ALTER TABLE `questbids`
  MODIFY `questbidId` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `pytania` ADD `IsBid` BOOLEAN NOT NULL AFTER `Rewards`; 


DROP TRIGGER IF EXISTS `trg_UpdateOverallScore`;CREATE DEFINER=`root`@`localhost` TRIGGER `trg_UpdateOverallScore` AFTER UPDATE ON `turnieje` FOR EACH ROW BEGIN
    IF NEW.Status = 'Z' THEN

        -- Insert winners into the 'winners' table
        INSERT INTO winners (UserId, TurniejId)
        SELECT tu.UserId, tu.TurniejId
        FROM turuserzy tu
        WHERE tu.TurniejId = NEW.TurniejId
        AND tu.CurrentScore = (
            SELECT MAX(CurrentScore)
            FROM turuserzy
            WHERE TurniejId = NEW.TurniejId
        );
    END IF;
    
END


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `updateOverallScore`(
	IN turniejId INT(11)
)
BEGIN
UPDATE users u JOIN turuserzy tu ON tu.UserId = u.UserId 
SET u.OverallScore = u.OverallScore + tu.CurrentScore WHERE tu.TurniejId = turniejId;
END$$
DELIMITER ;
