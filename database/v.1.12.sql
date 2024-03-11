DROP PROCEDURE `UpdateTournamentStatus`;

DELIMITER //

CREATE PROCEDURE `CompleteTournament`(IN `tournamentId` INT) 
BEGIN
    UPDATE turnieje SET Status = 'Z' WHERE TurniejId = tournamentId;
	
    INSERT INTO winners (UserId, TurniejId)
    SELECT tu.UserId, tu.TurniejId
    FROM turuserzy tu
    WHERE tu.TurniejId = tournamentId
    AND tu.CurrentScore = (
        SELECT MAX(CurrentScore)
        FROM turuserzy
        WHERE TurniejId = tournamentId
    );

    UPDATE turnieje SET Code = NULL WHERE TurniejId = tournamentId;

    DELETE FROM buzzes WHERE TurniejId = tournamentId;
END //

DELIMITER ;
