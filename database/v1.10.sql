ALTER TABLE `dictionary` ADD `Language` VARCHAR(2) NOT NULL AFTER `Description`;

update dictionary set Type='quest.Status';

UPDATE dictionary set Language='pl';

INSERT INTO `dictionary` (`Type`, `Symbol`, `Label`, `Description`, `Language`) VALUES
('quest.Status', 'N', 'New', 'Tournament waiting to start by giving it the first four-digit code','en'),
('quest.Status', 'A', 'Active', 'Tournament given new four-digit code; Organizer waiting for participants to join','en'),
('quest.Status', 'K', 'Displaying Categories', 'Tournament, which currently displays a list of questions and their categories; Waiting for the organizer to select a question','en'),
('quest.Status', 'P', 'Displaying Question', 'Tournament, where the question is currently displayed; At this time, participants submit their answer','en'),
('quest.Status', 'O', 'Displaying Answer', 'Tournament, which currently displays the answer to the current question','en'),
('quest.Status', 'X', 'End of questions', 'Tournament where all the questions have now been shown; Waiting for the organizer to finish the tournament','en'),
('quest.Status', 'Z', 'Completed', 'The tournament has been completed - participants have been assigned points','en');


DROP TRIGGER IF EXISTS `trg_UpdateOverallScore`;


DELIMITER //

CREATE PROCEDURE UpdateTournamentStatus(IN tournamentId INT)
BEGIN
    -- Update turnieje table status
    UPDATE turnieje SET Status = 'Z' WHERE TurniejId = tournamentId;

    -- Insert winners into the 'winners' table
    INSERT INTO winners (UserId, TurniejId)
    SELECT tu.UserId, tu.TurniejId
    FROM turuserzy tu
    WHERE tu.TurniejId = tournamentId
    AND tu.CurrentScore = (
        SELECT MAX(CurrentScore)
        FROM turuserzy
        WHERE TurniejId = tournamentId
    );

    -- Update Code column to NULL
    UPDATE turnieje SET Code = NULL WHERE TurniejId = tournamentId;
END //

DELIMITER ;
