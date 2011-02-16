<?php
/**
 * error_codes.inc.php
 * 
 * This file is where all error codes are defined.
 * All error code are named after the class and function they occur in.
 * @see util::get_error_number() for more details.
 * @package sabretooth\exception
 * @filesource
 */

namespace sabretooth\exception;

/**
 * Error number category defines.
 */
define( 'ARGUMENT_BASE_ERROR_NUMBER',   100000 );
define( 'DATABASE_BASE_ERROR_NUMBER',   200000 );
define( 'MISSING_BASE_ERROR_NUMBER',    300000 );
define( 'PERMISSION_BASE_ERROR_NUMBER', 400000 );
define( 'RUNTIME_BASE_ERROR_NUMBER',    500000 );
define( 'TEMPLATE_BASE_ERROR_NUMBER',   600000 );
define( 'FATAL_BASE_ERROR_NUMBER',      700000 );
define( 'UNKNOWN_BASE_ERROR_NUMBER',    800000 );

/**
 * "argument" error codes
 */
define( 'ARGUMENT_MODIFIER_WHERE_ERROR_NUMBER',          ARGUMENT_BASE_ERROR_NUMBER + 1 );
define( 'ARGUMENT_MODIFIER_GROUP_ERROR_NUMBER',          ARGUMENT_BASE_ERROR_NUMBER + 2 );
define( 'ARGUMENT_MODIFIER_ORDER_ERROR_NUMBER',          ARGUMENT_BASE_ERROR_NUMBER + 3 );
define( 'ARGUMENT_MODIFIER_LIMIT_ERROR_NUMBER',          ARGUMENT_BASE_ERROR_NUMBER + 4 );
define( 'ARGUMENT_ACTIVE_RECORD___GET_ERROR_NUMBER',     ARGUMENT_BASE_ERROR_NUMBER + 5 );
define( 'ARGUMENT_ACTIVE_RECORD___SET_ERROR_NUMBER',     ARGUMENT_BASE_ERROR_NUMBER + 6 );
define( 'ARGUMENT_ACTIVE_RECORD___CALL_ERROR_NUMBER',    ARGUMENT_BASE_ERROR_NUMBER + 7 );
define( 'ARGUMENT_BASE_DELETE___CONSTRUCT_ERROR_NUMBER', ARGUMENT_BASE_ERROR_NUMBER + 8 );
define( 'ARGUMENT_BASE_EDIT___CONSTRUCT_ERROR_NUMBER',   ARGUMENT_BASE_ERROR_NUMBER + 9 );
define( 'ARGUMENT_BASE_RECORD_SET_MODE_ERROR_NUMBER',    ARGUMENT_BASE_ERROR_NUMBER + 10 );
define( 'ARGUMENT_OPERATION_GET_ARGUMENT_ERROR_NUMBER',  ARGUMENT_BASE_ERROR_NUMBER + 11 );
define( 'ARGUMENT_WIDGET_GET_ARGUMENT_ERROR_NUMBER',     ARGUMENT_BASE_ERROR_NUMBER + 12 );
define( 'ARGUMENT_WIDGET_SCRIPT_ERROR_NUMBER',           ARGUMENT_BASE_ERROR_NUMBER + 13 );
define( 'ARGUMENT_ACTION_SCRIPT_ERROR_NUMBER',           ARGUMENT_BASE_ERROR_NUMBER + 14 );
define( 'ARGUMENT_SESSION___CONSTRUCT_ERROR_NUMBER',     ARGUMENT_BASE_ERROR_NUMBER + 15 );
define( 'ARGUMENT_OPERATION___CONSTRUCT_ERROR_NUMBER',   ARGUMENT_BASE_ERROR_NUMBER + 16 );

/**
 * "database" error codes
 * 
 * Since database errors already have codes this list is likely to stay empty.
 */

/**
 * "missing" error codes
 */
define( 'MISSING_AUTOLOADER_AUTOLOAD_ERROR_NUMBER', MISSING_BASE_ERROR_NUMBER + 1 );

/**
 * "permission" error codes
 */
define( 'PERMISSION_OPERATION___CONSTRUCT_ERROR_NUMBER', PERMISSION_BASE_ERROR_NUMBER + 1 );

/**
 * "runtime" error codes
 */
define( 'RUNTIME_ACTIVE_RECORD___CONSTRUCT_ERROR_NUMBER', RUNTIME_BASE_ERROR_NUMBER + 1 );
define( 'RUNTIME_ACTIVE_RECORD___CALL_ERROR_NUMBER',      RUNTIME_BASE_ERROR_NUMBER + 2 );
define( 'RUNTIME_ACTIVE_RECORD_LOAD_ERROR_NUMBER',        RUNTIME_BASE_ERROR_NUMBER + 3 );
define( 'RUNTIME_SESSION_INITIALIZE_ERROR_NUMBER',        RUNTIME_BASE_ERROR_NUMBER + 4 );
define( 'RUNTIME_SELF_SET_ROLE_EXECUTE_ERROR_NUMBER',     RUNTIME_BASE_ERROR_NUMBER + 5 );
define( 'RUNTIME_SELF_SET_SITE_EXECUTE_ERROR_NUMBER',     RUNTIME_BASE_ERROR_NUMBER + 6 );
define( 'RUNTIME_WIDGET_SCRIPT_ERROR_NUMBER',             RUNTIME_BASE_ERROR_NUMBER + 7 );
define( 'RUNTIME_ACTION_SCRIPT_ERROR_NUMBER',             RUNTIME_BASE_ERROR_NUMBER + 8 );
define( 'RUNTIME_LOG_INITIALIZE_LOGGER_ERROR_NUMBER',     RUNTIME_BASE_ERROR_NUMBER + 9 );

/**
 * "template", "fatal" and "unknown" error codes do not have specific codes
 */
?>
