DROP PROCEDURE IF EXISTS patch_recording;
DELIMITER //
CREATE PROCEDURE patch_recording()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_role_has_operation_role_id" );

    SELECT "Replacing interview and assignment columns with participant column in recording table" AS "";

    SET @test = (
      SELECT COUNT(*)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = "recording"
      AND COLUMN_NAME = "participant" );
    IF @test = 0 THEN
      SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
      SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

      ALTER TABLE recording
      ADD COLUMN participant_id INT UNSIGNED NOT NULL
      AFTER create_timestamp;
      
      SET @sql = CONCAT(
        "ALTER TABLE recording ",
        "ADD INDEX fk_participant_id( participant_id ASC ), ",
        "ADD CONSTRAINT fk_recording_participant_id ",
        "FOREIGN KEY( participant_id ) ",
        "REFERENCES ", @cenozo, ".participant( id ) ",
        "ON DELETE CASCADE ",
        "ON UPDATE CASCADE" );
      PREPARE statement FROM @sql;
      EXECUTE statement;
      DEALLOCATE PREPARE statement;

      -- fill in the participant id from the existing interview id
      UPDATE recording
      JOIN interview ON recording.interview_id = interview.id
      SET recording.participant_id = interview.participant_id;

      ALTER TABLE recording
      ADD UNIQUE KEY uq_participant_id_rank (participant_id, rank),
      DROP KEY uq_interview_rank;
      
      ALTER TABLE recording
      DROP FOREIGN KEY fk_recording_interview_id,
      DROP KEY fk_interview_id;
      ALTER TABLE recording DROP COLUMN interview_id;

      ALTER TABLE recording
      DROP FOREIGN KEY fk_recording_assignment_id,
      DROP KEY fk_assignment_id;
      ALTER TABLE recording DROP COLUMN assignment_id;

      SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
      SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
    END IF;

  END //
DELIMITER ;

CALL patch_recording();
DROP PROCEDURE IF EXISTS patch_recording;
