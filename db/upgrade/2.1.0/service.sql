SELECT "Adding new services" AS "";

-- rebuild the service list
DELETE FROM service
WHERE subject IN ( 'shift', 'shift_template' );

INSERT IGNORE INTO service ( subject, method, resource, restricted ) VALUES
( 'failed_login', 'GET', 0, 1 ),
( 'vacancy', 'DELETE', 1, 1 ),
( 'vacancy', 'GET', 0, 0 ),
( 'vacancy', 'GET', 1, 1 ),
( 'vacancy', 'PATCH', 1, 1 ),
( 'vacancy', 'POST', 0, 1 );
