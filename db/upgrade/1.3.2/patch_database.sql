-- Patch to upgrade database to version 1.3.2

SET AUTOCOMMIT=0;

SOURCE operation.sql
SOURCE role_has_operation.sql
SOURCE jurisdiction.sql
SOURCE qnaire.sql
SOURCE qnaire_has_interview_method.sql
SOURCE queue.sql
SOURCE queue_has_participant.sql
SOURCE ivr_appointment.sql
SOURCE service.sql
SOURCE qnaire_has_quota.sql
SOURCE quota_state.sql

SOURCE update_version_number.sql

COMMIT;