-- -----------------------------------------------------
-- Roles
-- -----------------------------------------------------
SET AUTOCOMMIT=0;

-- make sure all roles exist
INSERT IGNORE INTO cenozo.role( name, tier, all_sites ) VALUES
( "administrator", 3, true ),
( "cedar", 1, true ),
( "curator", 2, true ),
( "helpline", 2, true ),
( "opal", 1, true ),
( "operator", 1, false ),
( "supervisor", 2, false );

-- add states to roles
INSERT IGNORE INTO cenozo.role_has_state( role_id, state_id )
SELECT role.id, state.id
FROM role, state
WHERE state.name NOT IN ( "unreachable", "consent unavailable" );

INSERT IGNORE INTO cenozo.role_has_state( role_id, state_id )
SELECT role.id, state.id
FROM role, state
WHERE state.name = "unreachable"
AND role.name IN ( "administrator", "curator" );

INSERT IGNORE INTO cenozo.role_has_state( role_id, state_id )
SELECT role.id, state.id
FROM role, state
WHERE state.name = "consent unavailable"
AND role.name IN ( "administrator", "curator" );

-- access

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "access" AND operation.name = "delete"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "access" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

-- activity

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "activity" AND operation.name = "chart"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "activity" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

-- address

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "address" AND operation.name = "add"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "address" AND operation.name = "delete"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "address" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "address" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "address" AND operation.name = "new"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "address" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- alternate

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "alternate" AND operation.name = "add"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "alternate" AND operation.name = "add_address"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "alternate" AND operation.name = "add_phone"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "alternate" AND operation.name = "delete"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "alternate" AND operation.name = "delete_address"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "alternate" AND operation.name = "delete_phone"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "alternate" AND operation.name = "edit"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "alternate" AND operation.name = "list"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "alternate" AND operation.name = "new"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT IGNORE INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "alternate" AND operation.name = "view"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

-- appointment

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "appointment" AND operation.name = "add"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "appointment" AND operation.name = "calendar"
AND role.name IN ( "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "appointment" AND operation.name = "delete"
AND role.name IN( "administrator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "appointment" AND operation.name = "edit"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "appointment" AND operation.name = "feed"
AND role.name IN ( "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "appointment" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "appointment" AND operation.name = "new"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "appointment" AND operation.name = "report"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "appointment" AND operation.name = "report"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "appointment" AND operation.name = "view"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

-- assignment

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "assignment" AND operation.name = "begin"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "assignment" AND operation.name = "end"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "assignment" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "assignment" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- availability

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "availability" AND operation.name = "add"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "availability" AND operation.name = "calendar"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "availability" AND operation.name = "delete"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "availability" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "availability" AND operation.name = "feed"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "availability" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "availability" AND operation.name = "new"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "availability" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- away_time

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "away_time" AND operation.name = "add"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "away_time" AND operation.name = "delete"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "away_time" AND operation.name = "edit"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "away_time" AND operation.name = "list"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "away_time" AND operation.name = "new"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "away_time" AND operation.name = "view"
AND role.name IN ( "supervisor" );

-- callback

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "callback" AND operation.name = "add"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "callback" AND operation.name = "calendar"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "callback" AND operation.name = "delete"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "callback" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "callback" AND operation.name = "feed"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "callback" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "callback" AND operation.name = "new"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "callback" AND operation.name = "report"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "callback" AND operation.name = "report"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "callback" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- call_attempts

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "call_attempts" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "call_attempts" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- call_history

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "call_history" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "call_history" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- cedar_instance

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "cedar_instance" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "cedar_instance" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "cedar_instance" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "cedar_instance" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "cedar_instance" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "cedar_instance" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- collection

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "collection" AND operation.name = "add"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "collection" AND operation.name = "add_participant"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "collection" AND operation.name = "add_user"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "delete"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "delete_participant"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "delete_user"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "edit"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "collection" AND operation.name = "list"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "new"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "new_participant"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "collection" AND operation.name = "new_user"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "collection" AND operation.name = "view"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

-- consent

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "consent" AND operation.name = "add"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "consent" AND operation.name = "delete"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "consent" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "consent" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "consent" AND operation.name = "new"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "consent" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- consent_form

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "consent_form" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "consent_form" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- email

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "email" AND operation.name = "report"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "email" AND operation.name = "report"
AND role.name IN ( "administrator", "curator" );

-- event

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "event" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "event" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "event" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "event" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "event" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "event" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- event_type

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "event_type" AND operation.name = "list"
AND role.name IN( "administrator" );

-- interview

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "interview" AND operation.name = "edit"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "interview" AND operation.name = "list"
AND role.name IN ( "opal" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "interview" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "supervisor", "typist" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "interview" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "supervisor", "typist" );

-- interview_method

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "interview_method" AND operation.name = "list"
AND role.name IN( "administrator" );

-- ivr_appointment

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "ivr_appointment" AND operation.name = "add"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "ivr_appointment" AND operation.name = "calendar"
AND role.name IN ( "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "ivr_appointment" AND operation.name = "delete"
AND role.name IN( "administrator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "ivr_appointment" AND operation.name = "edit"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "ivr_appointment" AND operation.name = "feed"
AND role.name IN ( "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "ivr_appointment" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "ivr_appointment" AND operation.name = "new"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "ivr_appointment" AND operation.name = "view"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

-- jurisdiction

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "jurisdiction" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "jurisdiction" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "jurisdiction" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "jurisdiction" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "jurisdiction" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "jurisdiction" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- language

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "language" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "language" AND operation.name = "list"
AND role.name IN ( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "language" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- mailout_required

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "mailout_required" AND operation.name = "report"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "mailout_required" AND operation.name = "report"
AND role.name IN ( "administrator" );

-- note

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "note" AND operation.name = "delete"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "note" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

-- opal_instance

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "opal_instance" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "opal_instance" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "opal_instance" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "opal_instance" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "opal_instance" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "opal_instance" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- operator

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "operator" AND operation.name = "assignment"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "operator" AND operation.name = "begin_break"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "operator" AND operation.name = "end_break"
AND role.name IN ( "operator" );

-- participant

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add"
AND role.name IN ( NULL );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_address"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_alternate"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_appointment"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_availability"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_callback"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_consent"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_event"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_ivr_appointment"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "add_phone"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete"
AND role.name IN ( NULL );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_address"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_alternate"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_appointment"
AND role.name IN( "administrator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_availability"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_callback"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_consent"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_event"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_ivr_appointment"
AND role.name IN( "administrator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "delete_phone"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "hin"
AND role.name IN( "administrator", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant" AND operation.name = "multinote"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "multinote"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "multinote"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "new"
AND role.name IN ( NULL );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant" AND operation.name = "report"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "report"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "reverse_withdraw"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "search"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant" AND operation.name = "site_reassign"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "site_reassign"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "site_reassign"
AND role.name IN ( "administrator", "curator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant" AND operation.name = "tree"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "tree"
AND role.name IN( "administrator", "curator", "helpline", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "participant" AND operation.name = "withdraw"
AND role.name IN ( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "withdraw" AND subject = "participant" AND operation.name = "withdraw"
AND role.name IN ( "administrator", "curator", "helpline", "supervisor" );

-- participant_status

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant_status" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant_status" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- participant_tree

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "participant_tree" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "participant_tree" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- phase

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phase" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phase" AND operation.name = "add_source_survey"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phase" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phase" AND operation.name = "delete_source_survey"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phase" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phase" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phase" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phase" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- phone

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phone" AND operation.name = "add"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phone" AND operation.name = "delete"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phone" AND operation.name = "edit"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phone" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phone" AND operation.name = "new"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phone" AND operation.name = "view"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- phone_call

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phone_call" AND operation.name = "begin"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "phone_call" AND operation.name = "end"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "phone_call" AND operation.name = "list"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

-- productivity

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "productivity" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "productivity" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- qnaire

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "add_event_type"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "add_interview_method"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "add_phase"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "add_source_withdraw"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "delete_event_type"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "delete_interview_method"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "delete_phase"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "delete_source_withdraw"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "new_event_type"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "qnaire" AND operation.name = "new_interview_method"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "qnaire" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- queue

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue" AND operation.name = "add_queue_state"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue" AND operation.name = "delete_queue_state"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "queue" AND operation.name = "edit"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "queue" AND operation.name = "repopulate"
AND role.name IN( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue" AND operation.name = "view"
AND role.name IN( "administrator", "supervisor" );

-- queue_state

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue_state" AND operation.name = "add"
AND role.name IN ( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "queue_state" AND operation.name = "delete"
AND role.name IN ( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "queue_state" AND operation.name = "edit"
AND role.name IN ( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue_state" AND operation.name = "list"
AND role.name IN ( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "queue_state" AND operation.name = "new"
AND role.name IN ( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "queue_state" AND operation.name = "view"
AND role.name IN ( "administrator", "supervisor" );

-- quota

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "quota" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "quota" AND operation.name = "add_qnaire"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "quota" AND operation.name = "chart"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "quota" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "quota" AND operation.name = "delete_qnaire"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "quota" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "quota" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "quota" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "quota" AND operation.name = "new_qnaire"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "quota" AND operation.name = "view"
AND role.name IN( "administrator", "supervisor" );

-- recording

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "recording" AND operation.name = "list"
AND role.name IN( "cedar" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "recording" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor", "typist" );

-- region_site

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "region_site" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "region_site" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "region_site" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "region_site" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "region_site" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "region_site" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- role

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "role" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

-- sample

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "sample" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "sample" AND operation.name = "report"
AND role.name IN( "administrator", "supervisor" );

-- setting

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "setting" AND operation.name = "edit"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "setting" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "setting" AND operation.name = "view"
AND role.name IN( "administrator", "supervisor" );

-- shift

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "shift" AND operation.name = "add"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "shift" AND operation.name = "calendar"
AND role.name IN( "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "shift" AND operation.name = "delete"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "shift" AND operation.name = "edit"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "shift" AND operation.name = "feed"
AND role.name IN( "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "shift" AND operation.name = "new"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "shift" AND operation.name = "view"
AND role.name IN ( "supervisor" );

-- shift_template

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "shift_template" AND operation.name = "add"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "shift_template" AND operation.name = "calendar"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "shift_template" AND operation.name = "delete"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "shift_template" AND operation.name = "edit"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "shift_template" AND operation.name = "feed"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "shift_template" AND operation.name = "new"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "shift_template" AND operation.name = "view"
AND role.name IN ( "supervisor" );

-- site

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "site" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "site" AND operation.name = "add_access"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "site" AND operation.name = "calendar"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "site" AND operation.name = "delete_access"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "site" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "site" AND operation.name = "feed"
AND role.name IN( "administrator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "site" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "site" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "site" AND operation.name = "new_access"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "site" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- source_survey

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "source_survey" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "source_survey" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "source_survey" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "source_survey" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "source_survey" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "source_survey" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- source_withdraw

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "source_withdraw" AND operation.name = "add"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "source_withdraw" AND operation.name = "delete"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "source_withdraw" AND operation.name = "edit"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "source_withdraw" AND operation.name = "list"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "source_withdraw" AND operation.name = "new"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "source_withdraw" AND operation.name = "view"
AND role.name IN ( "administrator" );

-- survey

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "survey" AND operation.name = "list"
AND role.name IN ( "administrator" );

-- system_message

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "system_message" AND operation.name = "add"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "system_message" AND operation.name = "delete"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "system_message" AND operation.name = "edit"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "system_message" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "system_message" AND operation.name = "new"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "system_message" AND operation.name = "view"
AND role.name IN( "administrator", "supervisor" );

-- timing

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "timing" AND operation.name = "report"
AND role.name IN ( "administrator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "timing" AND operation.name = "report"
AND role.name IN ( "administrator" );

-- user

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "user" AND operation.name = "add"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "user" AND operation.name = "add_access"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "user" AND operation.name = "add_language"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "user" AND operation.name = "add_shift"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "delete"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "delete_access"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "delete_language"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "delete_shift"
AND role.name IN ( "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "edit"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "pull" AND subject = "user" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "user" AND operation.name = "list"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "new"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "new_access"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "new_language"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "reset_password"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "user" AND operation.name = "set_password"
AND role.name IN( "administrator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "widget" AND subject = "user" AND operation.name = "view"
AND role.name IN( "administrator", "supervisor" );

-- voip

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "voip" AND operation.name = "begin_monitor"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "voip" AND operation.name = "dtmf"
AND role.name IN( "administrator", "curator", "helpline", "operator", "supervisor" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "voip" AND operation.name = "end_monitor"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "voip" AND operation.name = "play"
AND role.name IN ( "operator" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id FROM cenozo.role, operation
WHERE type = "push" AND subject = "voip" AND operation.name = "spy"
AND role.name IN ( "supervisor" );

COMMIT;
