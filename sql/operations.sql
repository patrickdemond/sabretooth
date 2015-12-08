-- -----------------------------------------------------
-- Operations
-- -----------------------------------------------------
SET AUTOCOMMIT=0;

-- appointment
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment", "add", true, "View a form for creating new appointments for a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment", "calendar", true, "Shows appointments in a calendar format." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "appointment", "delete", true, "Removes a participant's appointment from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "appointment", "edit", true, "Edits the details of a participant's appointment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "appointment", "feed", true, "Retrieves a list of appointments for a given time-span." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment", "list", true, "Lists a participant's appointments." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "appointment", "new", true, "Creates new appointment entry for a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "appointment", "report", true, "Download a appointment report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment", "report", true, "Set up a appointment report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment", "view", true, "View the details of a participant's particular appointment." );

-- assignment
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "assignment", "begin", true, "Requests a new assignment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "assignment", "end", true, "Ends the current assignment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "assignment", "list", true, "Lists assignments." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "assignment", "view", true, "View assignment details." );

-- availability
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "availability", "calendar", true, "Shows aggregate availabilities in a calendar format." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "availability", "feed", true, "Retrieves a list of aggregate availabilities for a given time-span." );

-- away_time
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "away_time", "add", true, "View a form for creating a new away time." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "away_time", "delete", true, "Removes an away time from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "away_time", "edit", true, "Edits a away time's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "away_time", "list", true, "List away times in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "away_time", "new", true, "Add a new away time to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "away_time", "view", true, "View a away time's details." );

-- call_history
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "call_history", "report", true, "Download a call history report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "call_history", "report", true, "Set up a call history report." );

-- callback
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "callback", "add", true, "View a form for creating new callbacks for a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "callback", "calendar", true, "Shows callbacks in a calendar format." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "callback", "delete", true, "Removes a participant's callback from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "callback", "edit", true, "Edits the details of a participant's callback." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "callback", "feed", true, "Retrieves a list of callbacks for a given time-span." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "callback", "list", true, "Lists a participant's callbacks." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "callback", "new", true, "Creates new callback entry for a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "callback", "report", true, "Download a callback report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "callback", "report", true, "Set up a callback report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "callback", "view", true, "View the details of a participant's particular callback." );

-- cedar_instance
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "cedar_instance", "add", true, "View a form for creating a new cedar instance." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "cedar_instance", "delete", true, "Removes a cedar instance from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "cedar_instance", "edit", true, "Edits a cedar instance's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "cedar_instance", "list", true, "List cedar instances in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "cedar_instance", "new", true, "Add a new cedar instance to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "cedar_instance", "view", true, "View a cedar instance's details." );

-- consent_form
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "consent_form", "report", true, "Download a consent form report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "consent_form", "report", true, "Set up a consent form report." );

-- consent_form
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "consent_required", "report", true, "Download a consent required report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "consent_required", "report", true, "Set up a consent required report." );

-- interview
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "interview", "edit", true, "Edits the details of an interview." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "interview", "list", true, "Retrieves base information for a list of interviews." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "interview", "list", true, "Lists interviews." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "interview", "view", true, "View interview details." );

-- interview_method
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "interview_method", "list", true, "Lists interviews." );

-- ivr_appointment
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "ivr_appointment", "add", true, "View a form for creating new IVR appointments for a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "ivr_appointment", "calendar", true, "Shows IVR appointments in a calendar format." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "ivr_appointment", "delete", true, "Removes a participant's IVR appointment from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "ivr_appointment", "edit", true, "Edits the details of a participant's IVR appointment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "ivr_appointment", "feed", true, "Retrieves a list of IVR appointments for a given time-span." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "ivr_appointment", "list", true, "Lists a participant's IVR appointments." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "ivr_appointment", "new", true, "Creates new IVR appointment entry for a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "ivr_appointment", "report", true, "Download a IVR appointment report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "ivr_appointment", "report", true, "Set up a IVR appointment report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "ivr_appointment", "view", true, "View the details of a participant's particular IVR appointment." );

-- mailout_required
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "mailout_required", "report", true, "Download a new mailout required report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "mailout_required", "report", true, "Set up a new mailout required report." );

-- opal_instance
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "opal_instance", "add", true, "View a form for creating a new opal instance." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "opal_instance", "delete", true, "Removes a opal instance from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "opal_instance", "edit", true, "Edits a opal instance's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "opal_instance", "list", true, "List opal instances in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "opal_instance", "new", true, "Add a new opal instance to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "opal_instance", "view", true, "View a opal instance's details." );

-- operator
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "operator", "assignment", true, "Displays the operator's assignment manager." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "operator", "begin_break", true, "Register the start of a break." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "operator", "end_break", true, "Register the end of a break." );

-- participant
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "add_appointment", true, "A form to create a new appointment to add to a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "add_callback", true, "A form to create a new callback to add to a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "add_ivr_appointment", true, "A form to create a new IVR appointment to add to a participant." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "participant", "delete_appointment", true, "Remove a participant's appointment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "participant", "delete_callback", true, "Remove a participant's callback." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "participant", "delete_ivr_appointment", true, "Remove a participant's IVR appointment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "participant", "tree", true, "Returns the number of participants for every node of the participant tree." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "tree", true, "Displays participants in a tree format, revealing which queue the belong to." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "participant", "withdraw", true, "Withdraws the participant (or cancels the withdraw).  This is meant to be used during an interview if the participant suddenly wishes to withdraw." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "withdraw", true, "Pseudo-assignment to handle participant withdraws." );

-- participant_status
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "participant_status", "report", true, "Download a participant status report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant_status", "report", true, "Set up a participant status report." );

-- participant_tree
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "participant_tree", "report", true, "Download a participant tree report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant_tree", "report", true, "Set up a participant tree report." );

-- phase
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "phase", "add", true, "View a form for creating a new phase." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "phase", "add_source_survey", true, "A form to add a new source-specific survey to the phase." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "phase", "delete", true, "Removes a phase from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "phase", "delete_source_survey", true, "Remove a phase's source-specific survey." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "phase", "edit", true, "Edits a phase's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "phase", "list", true, "Lists a questionnaire's phases." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "phase", "new", true, "Creates a new questionnaire phase." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "phase", "view", true, "View the details of a questionnaire's phases." );

-- phone call
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "phone_call", "begin", true, "Starts a new phone call." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "phone_call", "end", true, "Ends the current phone call." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "phone_call", "list", true, "Lists phone calls." );

-- prerecruit
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "prerecruit", "select", true, "Sets pre-recruit populations by quota." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "prerecruit", "select", true, "Provides a list of quotas for entering pre-recruit populations." );

-- productivity
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "productivity", "report", true, "Download a productivity report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "productivity", "report", true, "Set up a productivity report." );

-- qnaire
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "add", true, "View a form for creating a new questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "add_event_type", true, "A form to add an event type to a qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "add_interview_method", true, "A form to add new interview methods to a qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "add_phase", true, "View surveys to add as a new phase to a questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "add_source_withdraw", true, "A form to add a new source-specific withdraw survey to the questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "delete", true, "Removes a questionnaire from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "delete_event_type", true, "Remove a qnaire's event type." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "delete_interview_method", true, "Remove interview methods from a questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "delete_phase", true, "Remove phases from a questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "delete_source_withdraw", true, "Remove a questionnaire's source-specific withdraw survey." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "edit", true, "Edits a questionnaire's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "list", true, "List questionnaires in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "new", true, "Add a new questionnaire to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "new_event_type", true, "Add an event type to a qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "new_interview_method", true, "Add an interview method to a qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "view", true, "View a questionnaire's details." );

-- queue
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue", "add_queue_state", true, "A form to create a new restrcition to the queue based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "delete_queue_state", true, "Remove a queue's restriction based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "edit", true, "Edits a queue's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue", "list", true, "List queues in the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "repopulate", true, "Repopulate all queue participant lists." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue", "view", true, "View a queue's details and list of participants." );

-- queue_state
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "add", true, "View a form for creating new queue restriction based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "delete", true, "Removes a restriction from a queue based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "edit", true, "Edits a restriction on a queue based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "list", true, "List restrictions on queues based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "new", true, "Add a new restriction to a queue based on site and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "view", true, "View a restriction on a queue based on site and qnaire." );

-- quota
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "quota", "add_qnaire", true, "A form to disable a quota for a particular qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "quota", "delete_qnaire", true, "Re-enable quotas for a particular qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "quota", "new_qnaire", true, "Disable a quota for a particular qnaire." );

-- recording
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "recording", "list", true, "Provides a list of recordings for a particular participant and qnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "recording", "list", true, "Lists recordings." );

-- sample
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "sample", "report", true, "Download a sample report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "sample", "report", true, "Set up a sample report." );

-- self
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "self", "dialing_pad", false, "A telephone dialing pad widget." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "self", "timer", false, "A timer widget used to count time and play sounds." );

-- shift
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "shift", "add", true, "View a form for creating a new shift." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "shift", "calendar", true, "Shows shifts in a calendar format." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "shift", "delete", true, "Removes a shift from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "shift", "edit", true, "Edits a shift's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "shift", "feed", true, "Retrieves a list of shifts for a given time-span." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "shift", "new", true, "Add a new shift to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "shift", "view", true, "View a shift's details." );

-- shift_template
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "shift_template", "add", true, "View a form for creating a new shift template." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "shift_template", "calendar", true, "Shows shift templates in a calendar format." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "shift_template", "delete", true, "Removes a shift template from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "shift_template", "edit", true, "Edits a shift template's details." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "shift_template", "feed", true, "Retrieves a list of shift templates for a given time-span." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "shift_template", "new", true, "Add a new shift template to the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "shift_template", "view", true, "View a shift template's details." );

-- site
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "site", "calendar", true, "A calendar listing the number of operators free for an appointment." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "site", "feed", true, "Retrieves a list of free site appointment times for a given time-span." );

-- source_survey
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "source_survey", "add", true, "View a form for creating new source-specific survey for a phase." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "source_survey", "delete", true, "Removes a phase's source-specific survey from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "source_survey", "edit", true, "Edits the details of a phase's source-specific survey." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "source_survey", "list", true, "Lists a phase's source-specific survey entries." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "source_survey", "new", true, "Creates a new source-specific survey for a phase." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "source_survey", "view", true, "View the details of a phase's particular source-specific survey." );

-- source_withdraw
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "source_withdraw", "add", true, "View a form for creating new source-specific withdraw survey for a questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "source_withdraw", "delete", true, "Removes a questionnaire's source-specific withdraw survey from the system." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "source_withdraw", "edit", true, "Edits the details of a questionnaire's source-specific withdraw survey." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "source_withdraw", "list", true, "Lists a questionnaire's source-specific withdraw survey entries." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "source_withdraw", "new", true, "Creates a new source-specific withdraw survey for a questionnaire." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "source_withdraw", "view", true, "View the details of a questionnaire's particular source-specific withdraw survey." );

-- survey
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "survey", "list", true, "List surveys in the system." );

-- timing
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "pull", "timing", "report", true, "Download a timing report." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "timing", "report", true, "Set up a timing report." );

-- user
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "user", "add_shift", true, "View shift form for adding the user to a new shift." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "user", "delete_shift", true, "Remove shifts from a user." );

-- voip
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "voip", "begin_monitor", true, "Starts monitoring the active call." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "voip", "dtmf", true, "Sends a DTMF tone to the Asterisk server." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "voip", "end_monitor", true, "Stops monitoring the active call." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "voip", "play", true, "Plays a sound over the Asterisk server." );
INSERT INTO operation( type, subject, name, restricted, description )
VALUES( "push", "voip", "spy", true, "Opens a listen-only connection to an existing operator's call." );

COMMIT;
