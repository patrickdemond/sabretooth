<?php
/**
 * error_codes.inc.php
 * 
 * This file is where all error codes are defined.
 * All error code are named after the class and function they occur in.
 */

/**
 * Error number category defines.
 */
define( 'ARGUMENT_SABRETOOTH_BASE_ERRNO',   150000 );
define( 'DATABASE_SABRETOOTH_BASE_ERRNO',   250000 );
define( 'LDAP_SABRETOOTH_BASE_ERRNO',       350000 );
define( 'NOTICE_SABRETOOTH_BASE_ERRNO',     450000 );
define( 'PERMISSION_SABRETOOTH_BASE_ERRNO', 550000 );
define( 'RUNTIME_SABRETOOTH_BASE_ERRNO',    650000 );
define( 'SYSTEM_SABRETOOTH_BASE_ERRNO',     750000 );
define( 'TEMPLATE_SABRETOOTH_BASE_ERRNO',   850000 );
define( 'VOIP_SABRETOOTH_BASE_ERRNO',       950000 );

/**
 * "argument" error codes
 */
define( 'ARGUMENT__SABRETOOTH_BUSINESS_IVR_MANAGER__SET_APPOINTMENT__ERRNO',
        ARGUMENT_SABRETOOTH_BASE_ERRNO + 1 );
define( 'ARGUMENT__SABRETOOTH_BUSINESS_SETTING_MANAGER____CONSTRUCT__ERRNO',
        ARGUMENT_SABRETOOTH_BASE_ERRNO + 2 );
define( 'ARGUMENT__SABRETOOTH_BUSINESS_VOIP_CALL____CONSTRUCT__ERRNO',
        ARGUMENT_SABRETOOTH_BASE_ERRNO + 3 );
define( 'ARGUMENT__SABRETOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        ARGUMENT_SABRETOOTH_BASE_ERRNO + 4 );
define( 'ARGUMENT__SABRETOOTH_DATABASE_QUEUE__GET_QUERY_PARTS__ERRNO',
        ARGUMENT_SABRETOOTH_BASE_ERRNO + 5 );
define( 'ARGUMENT__SABRETOOTH_UI_PULL_INTERVIEW_LIST__VALIDATE__ERRNO',
        ARGUMENT_SABRETOOTH_BASE_ERRNO + 6 );

/**
 * "database" error codes
 * 
 * Since database errors already have codes this list is likely to stay empty.
 */

/**
 * "ldap" error codes
 * 
 * Since ldap errors already have codes this list is likely to stay empty.
 */

/**
 * "notice" error codes
 */
define( 'NOTICE__SABRETOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 1 );
define( 'NOTICE__SABRETOOTH_DATABASE_APPOINTMENT__VALIDATE_DATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 2 );
define( 'NOTICE__SABRETOOTH_DATABASE_SHIFT__SAVE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 3 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_APPOINTMENT_EDIT__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 4 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_APPOINTMENT_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 5 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_ASSIGNMENT_BEGIN__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 6 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_ASSIGNMENT_BEGIN__EXECUTE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 7 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_ASSIGNMENT_END__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 8 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_AWAY_TIME_EDIT__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 9 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_AWAY_TIME_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 10 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_CALLBACK_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 11 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_CEDAR_INSTANCE_DELETE__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 12 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_CEDAR_INSTANCE_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 13 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_INTERVIEW_EDIT__EXECUTE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 14 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_IVR_APPOINTMENT_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 15 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_OPAL_INSTANCE_DELETE__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 16 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_OPAL_INSTANCE_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 17 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_PHONE_CALL_BEGIN__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 18 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_QNAIRE_DELETE_INTERVIEW_METHOD__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 19 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_QNAIRE_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 20 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_QUEUE_RESTRICTION_DELETE__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 21 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_QUEUE_RESTRICTION_EDIT__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 22 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_QUEUE_RESTRICTION_NEW__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 23 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_SHIFT_NEW__PREPARE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 24 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_SHIFT_NEW__EXECUTE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 25 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_VOIP_DTMF__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 26 );
define( 'NOTICE__SABRETOOTH_UI_PUSH_VOIP_SPY__VALIDATE__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 27 );
define( 'NOTICE__SABRETOOTH_UI_WIDGET_PRERECRUIT_SELECT__SETUP__ERRNO',
        NOTICE_SABRETOOTH_BASE_ERRNO + 28 );

/**
 * "permission" error codes
 */

/**
 * "runtime" error codes
 */
define( 'RUNTIME__SABRETOOTH_BUSINESS_IVR_MANAGER__SET_APPOINTMENT__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 1 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_IVR_MANAGER__REMOVE_APPOINTMENT__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 2 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_IVR_MANAGER__GET_STATUS__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 3 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_IVR_MANAGER__SEND__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 4 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_LDAP_MANAGER__SET_USER_PASSWORD__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 5 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_SESSION__GET_CURRENT_ASSIGNMENT__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 6 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_SESSION__GET_CURRENT_PHONE_CALL__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 7 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_SETTING_MANAGER____CONSTRUCT__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 8 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_SURVEY_MANAGER__PROCESS_WITHDRAW__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 9 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_SURVEY_MANAGER__GET_ATTRIBUTE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 10 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_VOIP_MANAGER__INITIALIZE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 11 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 12 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_WITHDRAW_MANAGER__GET_WITHDRAW_SID__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 13 );
define( 'RUNTIME__SABRETOOTH_BUSINESS_WITHDRAW_MANAGER__PROCESS__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 14 );
define( 'RUNTIME__SABRETOOTH_DATABASE_APPOINTMENT__SAVE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 15 );
define( 'RUNTIME__SABRETOOTH_DATABASE_APPOINTMENT__VALIDATE_DATE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 16 );
define( 'RUNTIME__SABRETOOTH_DATABASE_CALLBACK__SAVE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 17 );
define( 'RUNTIME__SABRETOOTH_DATABASE_IVR_APPOINTMENT__SAVE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 18 );
define( 'RUNTIME__SABRETOOTH_DATABASE_LIMESURVEY_RECORD____CALL__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 19 );
define( 'RUNTIME__SABRETOOTH_DATABASE_LIMESURVEY_SID_RECORD__GET_TABLE_NAME__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 20 );
define( 'RUNTIME__SABRETOOTH_DATABASE_LIMESURVEY_SURVEY__GET_RESPONSE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 21 );
define( 'RUNTIME__SABRETOOTH_DATABASE_LIMESURVEY_SURVEY_TIMINGS__GET_TABLE_NAME__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 22 );
define( 'RUNTIME__SABRETOOTH_DATABASE_LIMESURVEY_SURVEYS__GET_TOKEN_ATTRIBUTE_NAMES__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 23 );
define( 'RUNTIME__SABRETOOTH_DATABASE_PHONE_CALL__SAVE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 24 );
define( 'RUNTIME__SABRETOOTH_DATABASE_QUEUE__POPULATE_TIME_SPECIFIC__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 25 );
define( 'RUNTIME__SABRETOOTH_DATABASE_QUEUE__GET_QUERY_PARTS__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 26 );
define( 'RUNTIME__SABRETOOTH_DATABASE_SHIFT__SAVE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 27 );
define( 'RUNTIME__SABRETOOTH_UI_PULL_INTERVIEW_LIST__VALIDATE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 28 );
define( 'RUNTIME__SABRETOOTH_UI_PULL_RECORDING_LIST__PREPARE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 29 );
define( 'RUNTIME__SABRETOOTH_UI_PULL_RECORDING_LIST__VALIDATE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 30 );
define( 'RUNTIME__SABRETOOTH_UI_PUSH_ASSIGNMENT_BEGIN__EXECUTE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 31 );
define( 'RUNTIME__SABRETOOTH_UI_PUSH_PARTICIPANT_REVERSE_WITHDRAW__EXECUTE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 32 );
define( 'RUNTIME__SABRETOOTH_UI_PUSH_PARTICIPANT_WITHDRAW__EXECUTE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 33 );
define( 'RUNTIME__SABRETOOTH_UI_PUSH_PHONE_CALL_BEGIN__VALIDATE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 34 );
define( 'RUNTIME__SABRETOOTH_UI_WIDGET_APPOINTMENT_ADD__PREPARE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 35 );
define( 'RUNTIME__SABRETOOTH_UI_WIDGET_CALLBACK_ADD__SETUP__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 36 );
define( 'RUNTIME__SABRETOOTH_UI_WIDGET_IVR_APPOINTMENT_ADD__PREPARE__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 37 );
define( 'RUNTIME__SABRETOOTH_UI_WIDGET_PHASE_ADD__SETUP__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 38 );
define( 'RUNTIME__SABRETOOTH_UI_WIDGET_SOURCE_SURVEY_ADD__SETUP__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 39 );
define( 'RUNTIME__SABRETOOTH_UI_WIDGET_SOURCE_WITHDRAW_ADD__SETUP__ERRNO',
        RUNTIME_SABRETOOTH_BASE_ERRNO + 40 );

/**
 * "system" error codes
 * 
 * Since system errors already have codes this list is likely to stay empty.
 * Note the following PHP error codes:
 *      1: error,
 *      2: warning,
 *      4: parse,
 *      8: notice,
 *     16: core error,
 *     32: core warning,
 *     64: compile error,
 *    128: compile warning,
 *    256: user error,
 *    512: user warning,
 *   1024: user notice
 */

/**
 * "template" error codes
 * 
 * Since template errors already have codes this list is likely to stay empty.
 */

/**
 * "voip" error codes
 */
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_CALL__DTMF__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 1 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_CALL__PLAY_SOUND__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 2 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_CALL__START_MONITORING__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 3 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_CALL__STOP_MONITORING__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 4 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_MANAGER__INITIALIZE__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 5 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_MANAGER__REBUILD_CALL_LIST__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 6 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 7 );
define( 'VOIP__SABRETOOTH_BUSINESS_VOIP_MANAGER__SPY__ERRNO',
        VOIP_SABRETOOTH_BASE_ERRNO + 8 );

