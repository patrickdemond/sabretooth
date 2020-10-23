DROP PROCEDURE IF EXISTS patch_qnaire;
DELIMITER //
CREATE PROCEDURE patch_qnaire()
  BEGIN

    SELECT "Adding new web_version column to qnaire table" AS "";
    
    SELECT COUNT(*) INTO @test
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
    AND table_name = "qnaire"
    AND column_name = "web_version";

    IF @test = 0 THEN
      ALTER TABLE qnaire ADD COLUMN web_version TINYINT(1) NOT NULL DEFAULT 0 AFTER script_id;
    END IF;

  END //
DELIMITER ;

CALL patch_qnaire();
DROP PROCEDURE IF EXISTS patch_qnaire;