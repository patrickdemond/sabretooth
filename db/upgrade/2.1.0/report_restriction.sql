DROP PROCEDURE IF EXISTS patch_report_restriction;
DELIMITER //
CREATE PROCEDURE patch_report_restriction()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_access_site_id" );

    SELECT "Making changes to report_restriction data for appointment and phone_call reports" AS "";

    SET @sql = CONCAT(
      "UPDATE ", @cenozo, ".report_restriction ",
      "JOIN ", @cenozo, ".report_type ON report_restriction.report_type_id = report_type.id ",
      "SET null_allowed = 0, mandatory = 0 ",
      "WHERE report_type.name = 'appointment' ",
      "AND report_restriction.name = 'qnaire'" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "UPDATE ", @cenozo, ".report_restriction ",
      "JOIN ", @cenozo, ".report_type ON report_restriction.report_type_id = report_type.id ",
      "SET report_restriction.description = 'Restrict to a particilar appointment outcome (use \"empty\" to restrict to open appointments).' ",
      "WHERE report_type.name = 'appointment' ",
      "AND report_restriction.name = 'outcome'" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "UPDATE ", @cenozo, ".report_restriction ",
      "SET subject = 'vacancy.datetime' ",
      "WHERE subject = 'DATE( vacancy.datetime )'" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "UPDATE ", @cenozo, ".report_restriction ",
      "SET subject = 'phone_call.start_datetime' ",
      "WHERE subject = 'DATE( phone_call.start_datetime )'" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_report_restriction();
DROP PROCEDURE IF EXISTS patch_report_restriction;
