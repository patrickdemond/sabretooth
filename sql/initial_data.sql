-- -----------------------------------------------------
-- Data for table .operation
-- -----------------------------------------------------
SET AUTOCOMMIT=0;

-- generic operations
DELETE FROM operation;
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "login", "halt", true, "Logs out all users (except the user who executes this operation)." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "login", "suspend", true, "Prevents all users from logging in (except the user who executes this operation)." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "voip", "halt", true, "Disconnects all VOIP sessions." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "voip", "suspend", true, "Prevents any new VOIP sessions from connecting." );

-- activity
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "activity", "list", true, "List system activity." );

-- operation
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "operation", "list", true, "List operations in the system." );

-- role
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "role", "delete", true, "Removes a role from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "role", "edit", true, "Edits a role's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "role", "new", true, "Add a new role to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "role", "add", true, "View a form for creating a new role." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "role", "view", true, "View a role's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "role", "list", true, "List roles in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "role", "add_operation", true, "View operations to add to a role." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "role", "new_operation", true, "Add new operations to a role." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "role", "delete_operation", true, "Remove operations from a role." );

-- self
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "self", "home", false, "The current user's home screen." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "self", "settings", false, "The current user's settings manager." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "self", "shortcuts", false, "The current user's shortcut icon set." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "self", "set_site", false, "Change the current user's active site." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "self", "set_role", false, "Change the current user's active role." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "self", "set_theme", false, "Change the current user's web interface theme." );

-- site
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "site", "view", true, "View a site's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "site", "list", true, "List sites in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "site", "new_user", true, "Add new users to a site." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "site", "delete_user", true, "Remove users from a site." );

-- user
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "user", "delete", true, "Removes a user from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "user", "edit", true, "Edits a user's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "user", "new", true, "Add a new user to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "user", "add", true, "View a form for creating a new user." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "user", "view", true, "View a user's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "user", "list", true, "List users in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "user", "new_site", true, "Add new site-roles to a user." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "action", "user", "delete_site", true, "Remove site-roles from a user." );


-- build role permissions
DELETE FROM role;
INSERT INTO role( name ) VALUES( "administrator" );
INSERT INTO role( name ) VALUES( "clerk" );
INSERT INTO role( name ) VALUES( "operator" );
INSERT INTO role( name ) VALUES( "supervisor" );
INSERT INTO role( name ) VALUES( "technician" );
INSERT INTO role( name ) VALUES( "viewer" );

DELETE FROM role_has_operation;
INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id
FROM role, operation
WHERE role.name in( "administrator", "supervisor" )
AND operation.subject in( "activity", "operation", "site", "role", "user" );

INSERT INTO role_has_operation( role_id, operation_id )
SELECT role.id, operation.id
FROM role, operation
WHERE role.name in( "technician" )
AND operation.subject in( "login", "voip" );

COMMIT;
