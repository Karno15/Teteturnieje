-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 26 Lis 2023, 15:15
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
-- Struktura tabeli dla tabeli `answers`
--

CREATE TABLE `answers` (
  `answerId` int(11) NOT NULL,
  `Login` varchar(15) NOT NULL,
  `Points` int(11) NOT NULL,
  `Answer` tinyint(1) NOT NULL,
  `TurniejId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Wyzwalacze `answers`
--
DELIMITER $$
CREATE TRIGGER `trg_UpdateCurrentScore` AFTER INSERT ON `answers` FOR EACH ROW BEGIN
    UPDATE turuserzy
    SET CurrentScore = CurrentScore + NEW.Points
    WHERE UserId = (SELECT UserId FROM users WHERE Login = NEW.Login)
    AND TurniejId=NEW.TurniejID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `buzzes`
--

CREATE TABLE `buzzes` (
  `buzzId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `TurniejId` int(11) NOT NULL,
  `PytId` int(11) NOT NULL,
  `Buzztime` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `nagrody`
--

CREATE TABLE `nagrody` (
  `NagrodaId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Nazwa` varchar(50) NOT NULL,
  `Punkty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `prawiodpo`
--

CREATE TABLE `prawiodpo` (
  `PrawiodpoId` int(11) NOT NULL,
  `PytId` int(11) NOT NULL,
  `PozId` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Struktura tabeli dla tabeli `pytania`
--

CREATE TABLE `pytania` (
  `PytId` int(11) NOT NULL,
  `TurniejId` int(11) NOT NULL,
  `Quest` longblob NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Category` varchar(30) DEFAULT NULL,
  `Rewards` int(11) NOT NULL,
  `Done` tinyint(1) NOT NULL DEFAULT 0,
  `WhoAnswers` int(11) DEFAULT NULL,
  `Order` int(11) DEFAULT NULL,
  `After` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Struktura tabeli dla tabeli `pytaniapoz`
--

CREATE TABLE `pytaniapoz` (
  `PytPozId` int(11) NOT NULL,
  `PytId` int(11) NOT NULL,
  `PozId` int(11) NOT NULL,
  `Value` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Struktura tabeli dla tabeli `turnieje`
--

CREATE TABLE `turnieje` (
  `TurniejId` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Creator` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Code` smallint(4) DEFAULT NULL,
  `Status` varchar(2) NOT NULL,
  `CurrentQuest` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Wyzwalacze `turnieje`
--
DELIMITER $$
CREATE TRIGGER `trg_UpdateDoneStatus` AFTER UPDATE ON `turnieje` FOR EACH ROW BEGIN
    IF NEW.Status = 'A' THEN
        UPDATE pytania
        SET Done = 0
        WHERE TurniejId = NEW.TurniejId;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_UpdateOverallScore` AFTER UPDATE ON `turnieje` FOR EACH ROW BEGIN
    IF NEW.Status = 'Z' THEN
        -- Update users' overall scores
        UPDATE users u
        JOIN turuserzy tu ON tu.UserId = u.UserId
        SET u.OverallScore = u.OverallScore + tu.CurrentScore
        WHERE tu.TurniejId = NEW.TurniejId;

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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `turuserzy`
--

CREATE TABLE `turuserzy` (
  `turuserId` int(11) NOT NULL,
  `turniejId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CurrentScore` int(11) NOT NULL DEFAULT 0,
  `AnsAmt` int(11) DEFAULT 0,
  `JoinDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `UserId` int(11) NOT NULL,
  `Login` varchar(15) DEFAULT NULL,
  `OverallScore` int(11) NOT NULL DEFAULT 0,
  `Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastLogged` timestamp NULL DEFAULT NULL,
  `Pass` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Struktura tabeli dla tabeli `winners`
--

CREATE TABLE `winners` (
  `winnerId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `TurniejId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indeksy dla zrzutów tabel
--
--
-- Struktura tabeli dla tabeli `dictionary`
--

CREATE TABLE `dictionary` (
  `DictionaryId` int(11) NOT NULL,
  `Type` varchar(20) NOT NULL,
  `Symbol` varchar(2) NOT NULL,
  `Label` varchar(50) NOT NULL,
  `Description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `dictionary`
--

INSERT INTO `dictionary` (`DictionaryId`, `Type`, `Symbol`, `Label`, `Description`) VALUES
(1, 'pytania.Status', 'N', 'Nowy', 'Turniej oczekujący na rozpoczęcie poprzez nadanie pierwszego czterocyfrowego kodu'),
(2, 'pytania.Status', 'A', 'Aktywny', 'Turniej, któremu nadany został nowy czterocyfrowy kod; Organizator czeka na dołączenie uczestników'),
(3, 'pytania.Status', 'K', 'Wyświetlanie kategorii', 'Turniej, na którym obecnie wyświetla się lista pytań oraz ich kategorii; Oczekiwanie aż organizator wybierze pytanie'),
(4, 'pytania.Status', 'P', 'Wyświetlanie Pytania', 'Turniej, na którym obecnie wyświetlane jest pytanie; W tym czasie uczestnicy zgłaszają swoją odpowiedź'),
(5, 'pytania.Status', 'O', 'Wyświetlanie odpowiedzi', 'Turniej, na którym obecnie wyświetlana jest odpowiedź to aktualnego pytania'),
(6, 'pytania.Status', 'X', 'Koniec pytań', 'Turniej, na którym obecnie wszystkie pytania zostały już pokazane; Oczekiwanie aż organizator zakończy turniej'),
(7, 'pytania.Status', 'Z', 'Zakończony', 'Turniej został zakończony - uczestnikom zostały przydzielone punkty');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answerId`);

--
-- Indeksy dla tabeli `buzzes`
--
ALTER TABLE `buzzes`
  ADD PRIMARY KEY (`buzzId`);

--
-- Indeksy dla tabeli `prawiodpo`
--
ALTER TABLE `prawiodpo`
  ADD PRIMARY KEY (`PrawiodpoId`);

--
-- Indeksy dla tabeli `pytania`
--
ALTER TABLE `pytania`
  ADD PRIMARY KEY (`PytId`);

--
-- Indeksy dla tabeli `pytaniapoz`
--
ALTER TABLE `pytaniapoz`
  ADD PRIMARY KEY (`PytPozId`);

--
-- Indeksy dla tabeli `turnieje`
--
ALTER TABLE `turnieje`
  ADD PRIMARY KEY (`TurniejId`);

--
-- Indeksy dla tabeli `turuserzy`
--
ALTER TABLE `turuserzy`
  ADD PRIMARY KEY (`turuserId`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserId`);

--
-- Indeksy dla tabeli `winners`
--
ALTER TABLE `winners`
  ADD PRIMARY KEY (`winnerId`);


--
-- Indeksy dla tabeli `dictionary`
--
ALTER TABLE `dictionary`
  ADD PRIMARY KEY (`DictionaryId`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `answers`
--
ALTER TABLE `answers`
  MODIFY `answerId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;

--
-- AUTO_INCREMENT dla tabeli `buzzes`
--
ALTER TABLE `buzzes`
  MODIFY `buzzId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT dla tabeli `prawiodpo`
--
ALTER TABLE `prawiodpo`
  MODIFY `PrawiodpoId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT dla tabeli `pytania`
--
ALTER TABLE `pytania`
  MODIFY `PytId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT dla tabeli `pytaniapoz`
--
ALTER TABLE `pytaniapoz`
  MODIFY `PytPozId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT dla tabeli `turnieje`
--
ALTER TABLE `turnieje`
  MODIFY `TurniejId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT dla tabeli `turuserzy`
--
ALTER TABLE `turuserzy`
  MODIFY `turuserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT dla tabeli `winners`
--
ALTER TABLE `winners`
  MODIFY `winnerId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

--
-- AUTO_INCREMENT dla tabeli `dictionary`
--
ALTER TABLE `dictionary`
  MODIFY `DictionaryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
