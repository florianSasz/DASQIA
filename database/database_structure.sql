-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Erstellungszeit: 28. Nov 2022 um 20:07
-- Server-Version: 10.4.24-MariaDB
-- PHP-Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `codes`
--

CREATE TABLE `codes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parentID` int(11) DEFAULT NULL,
  `projectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `codes_in_documents`
--

CREATE TABLE `codes_in_documents` (
  `codeID` int(11) NOT NULL,
  `documentID` int(11) NOT NULL,
  `frequency` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `codes_to_research_questions`
--

CREATE TABLE `codes_to_research_questions` (
  `research_questionID` int(11) NOT NULL,
  `codeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `interviewer` varchar(255) DEFAULT NULL,
  `original_interviewer` varchar(255) NOT NULL,
  `interview_date` date DEFAULT NULL,
  `evaluator` varchar(255) DEFAULT NULL,
  `original_evaluator` varchar(255) NOT NULL,
  `evaluation_date` date DEFAULT NULL,
  `codes` int(11) NOT NULL DEFAULT 0,
  `projectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `selector` text NOT NULL,
  `token` longtext NOT NULL,
  `expires` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `x_axis` varchar(255) NOT NULL DEFAULT 'document',
  `y_axis` varchar(255) NOT NULL DEFAULT 'number of codes',
  `x_resolution` int(11) NOT NULL DEFAULT 1000,
  `y_resolution` int(11) NOT NULL DEFAULT 800,
  `axisFontSize` int(11) NOT NULL DEFAULT 13,
  `labelFontSize` int(11) NOT NULL DEFAULT 13,
  `vGridDivision` int(11) NOT NULL DEFAULT 0,
  `graphColor` varchar(7) NOT NULL DEFAULT '#5773FF',
  `finished` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `research_questions`
--

CREATE TABLE `research_questions` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `projectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `shadowusers`
--

CREATE TABLE `shadowusers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `projectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `shadowuser_aliases`
--

CREATE TABLE `shadowuser_aliases` (
  `alias` varchar(255) NOT NULL,
  `shadowuserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registrationDate` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_in_projects`
--

CREATE TABLE `users_in_projects` (
  `userID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `isLeader` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_aliases`
--

CREATE TABLE `user_aliases` (
  `alias` varchar(255) NOT NULL,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `codes`
--
ALTER TABLE `codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `CodeToProject` (`projectID`),
  ADD KEY `CodeToCode` (`parentID`);

--
-- Indizes für die Tabelle `codes_in_documents`
--
ALTER TABLE `codes_in_documents`
  ADD KEY `Code` (`codeID`),
  ADD KEY `document` (`documentID`);

--
-- Indizes für die Tabelle `codes_to_research_questions`
--
ALTER TABLE `codes_to_research_questions`
  ADD KEY `research_question` (`research_questionID`),
  ADD KEY `code_` (`codeID`);

--
-- Indizes für die Tabelle `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_to_project` (`projectID`);

--
-- Indizes für die Tabelle `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `research_questions`
--
ALTER TABLE `research_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `RqToProjectID` (`projectID`);

--
-- Indizes für die Tabelle `shadowusers`
--
ALTER TABLE `shadowusers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shadowUser_to_project` (`projectID`);

--
-- Indizes für die Tabelle `shadowuser_aliases`
--
ALTER TABLE `shadowuser_aliases`
  ADD KEY `alias_to_shadowuser` (`shadowuserID`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users_in_projects`
--
ALTER TABLE `users_in_projects`
  ADD KEY `userID` (`userID`),
  ADD KEY `projectID` (`projectID`);

--
-- Indizes für die Tabelle `user_aliases`
--
ALTER TABLE `user_aliases`
  ADD KEY `alias_to_user` (`userID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `codes`
--
ALTER TABLE `codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `research_questions`
--
ALTER TABLE `research_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `shadowusers`
--
ALTER TABLE `shadowusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `codes`
--
ALTER TABLE `codes`
  ADD CONSTRAINT `CodeToCode` FOREIGN KEY (`parentID`) REFERENCES `codes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `CodeToProject` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `codes_in_documents`
--
ALTER TABLE `codes_in_documents`
  ADD CONSTRAINT `Code` FOREIGN KEY (`codeID`) REFERENCES `codes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document` FOREIGN KEY (`documentID`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `codes_to_research_questions`
--
ALTER TABLE `codes_to_research_questions`
  ADD CONSTRAINT `code_` FOREIGN KEY (`codeID`) REFERENCES `codes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `research_question` FOREIGN KEY (`research_questionID`) REFERENCES `research_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `document_to_project` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `research_questions`
--
ALTER TABLE `research_questions`
  ADD CONSTRAINT `RqToProjectID` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `shadowusers`
--
ALTER TABLE `shadowusers`
  ADD CONSTRAINT `shadowUser_to_project` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `shadowuser_aliases`
--
ALTER TABLE `shadowuser_aliases`
  ADD CONSTRAINT `alias_to_shadowuser` FOREIGN KEY (`shadowuserID`) REFERENCES `shadowusers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `users_in_projects`
--
ALTER TABLE `users_in_projects`
  ADD CONSTRAINT `projectID` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `userID` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `user_aliases`
--
ALTER TABLE `user_aliases`
  ADD CONSTRAINT `alias_to_user` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
