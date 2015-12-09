-- Patch to upgrade database to version 1.3.5

SET AUTOCOMMIT=0;

SOURCE operation.sql
SOURCE role_has_operation.sql
SOURCE queue_state.sql
SOURCE setting_value.sql
SOURCE setting.sql
SOURCE qnaire.sql
SOURCE recording.sql

SOURCE update_version_number.sql

SELECT "NOTE: Make sure to run limesurvey.php" AS "";

COMMIT;
