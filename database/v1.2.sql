-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 27 Lis 2023, 12:56
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
-- Indeksy dla tabeli `dictionary`
--
ALTER TABLE `dictionary`
  ADD PRIMARY KEY (`DictionaryId`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `dictionary`
--
ALTER TABLE `dictionary`
  MODIFY `DictionaryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
