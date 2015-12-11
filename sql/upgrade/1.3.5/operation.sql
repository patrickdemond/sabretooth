SELECT "Adding new operations" AS "";

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "add", true,
"View a form for creating new queue restriction based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "delete", true,
"Removes a restriction from a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "edit", true,
"Edits a restriction on a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "list", true,
"List restrictions on queues based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "new", true,
"Add a new restriction to a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "view", true,
"View a restriction on a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue", "add_queue_state", true,
"A form to create a new restrcition to the queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "delete_queue_state", true,
"Remove a queue's restriction based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "edit", true,
"Edits a queue's details." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "proxy", true, "Pseudo-assignment to handle participant proxyies." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "progress", "report", true, "Set up a progress report." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "progress", "report", true, "Download up a progress report." );
