DROP PROCEDURE IF EXISTS patch_qnaire;
DELIMITER //
CREATE PROCEDURE patch_qnaire()
  BEGIN

    SELECT "Removing prev_qnaire_id from qnaire table" AS "";

    SET @test = (
      SELECT COUNT(*)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = "qnaire"
      AND COLUMN_NAME = "prev_qnaire_id" );
    IF @test = 0 THEN
      ALTER TABLE qnaire
      DROP FOREIGN KEY fk_qnaire_prev_qnaire_id,
      DROP KEY fk_prev_qnaire_id;

      ALTER TABLE qnaire DROP COLUMN qnaire_id;
    END IF;

  END //
DELIMITER ;

CALL patch_qnaire();
DROP PROCEDURE IF EXISTS patch_qnaire;
