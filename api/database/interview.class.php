<?php
/**
 * interview.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * interview: record
 */
class interview extends \cenozo\database\has_note
{
  /**
   * Get the interview's last (most recent) assignment.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return assignment
   * @access public
   */
  public function get_last_assignment()
  {
    // check the last key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to query interview with no id.' );
      return NULL;
    }
    
    // need custom SQL
    $assignment_id = static::db()->get_one(
      sprintf( 'SELECT assignment_id FROM interview_last_assignment WHERE interview_id = %s',
               static::db()->format_string( $this->id ) ) );
    return $assignment_id ? lib::create( 'database\assignment', $assignment_id ) : NULL;
  }

  /**
   * Returns the time in seconds that it took to complete a particular phase of this interview
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param phase $db_phase Which phase of the interview to get the time of.
   * @param assignment $db_assignment Repeated phases have their times measured for each
   *                      iteration of the phase.  For repeated phases this determines which
   *                      assignment's time to return.  It is ignored for phases which are not
   *                      repeated.
   * @return float
   * @access public
   */
  public function get_interview_time( $db_phase, $db_assignment = NULL )
  {
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to determine interview time for interview with no id.' );
      return 0.0;
    }
    
    if( is_null( $db_phase ) )
    {
      log::warning( 'Tried to determine interview time for null phase.' );
      return 0.0;
    }

    if( $db_phase->repeated && is_null( $db_assignment ) )
    {
      log::warning(
        'Tried to determine interview time for repeating phase without an assignment.' );
      return 0.0;
    }
   
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );
    $timings_class_name = lib::get_class_name( 'database\limesurvey\survey_timings' );

    $old_tokens_sid = $tokens_class_name::get_sid();
    $tokens_class_name::set_sid( $db_phase->sid );
    $old_tokens_sid = $tokens_class_name::get_sid();
    $survey_class_name::set_sid( $db_phase->sid );
    $old_timings_sid = $timings_class_name::get_sid();
    $timings_class_name::set_sid( $db_phase->sid );

    $survey_mod = lib::create( 'database\modifier' );
    $tokens_class_name::where_token( $survey_mod, $this->get_participant(), $db_phase->repeated );
    $survey_list = $survey_class_name::select( $survey_mod );
    if( 0 == count( $survey_list ) ) return 0.0;

    $db_survey = current( $survey_list );

    $timing_mod = lib::create( 'database\modifier' );
    $timing_mod->where( 'id', '=', $db_survey->id );
    $db_timings = current( $timings_class_name::select( $timing_mod ) );
    $time = $db_timings ? (float) $db_timings->interviewtime : 0.0;

    $tokens_class_name::set_sid( $old_tokens_sid );
    $survey_class_name::set_sid( $old_survey_sid );
    $timings_class_name::set_sid( $old_timings_sid );

    return $time;
  }

  /**
   * Performes all necessary steps when completing an interview.
   * 
   * This method encapsulates all processing required when an interview is completed.
   * If you wish to "force" the completion or uncompletion of an interview please use
   * the force_complete() and force_uncomplete() methods intead.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function complete()
  {
    // update the record
    $this->completed = true;
    $this->save();

    // record the event (if one exists)
    $db_event_type = $this->get_qnaire()->get_completed_event_type();
    if( !is_null( $db_event_type ) )
    {
      // make sure the event doesn't already exist
      $event_mod = lib::create( 'database\modifier' );
      $event_mod->where( 'event_type_id', '=', $db_event_type->id );
      if( 0 == $this->get_participant()->get_event_count( $event_mod ) )
      {
        $db_event = lib::create( 'database\event' );
        $db_event->participant_id = $this->participant_id;
        $db_event->event_type_id = $db_event_type->id;
        $db_event->datetime = util::get_datetime_object()->format( 'Y-m-d H:i:s' );
        $db_event->save();
      }
    }
  }

  /**
   * Forces an interview to become completed.
   * 
   * This method will update an interview's status to be complete.  It will also update the
   * correspinding limesurvey data to be set as complete.  This action cannot be undone.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function force_complete()
  {
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to force complete interview with no id.' );
      return;
    }
    
    // do nothing if the interview is already set as completed
    if( $this->completed ) return;
    
    // update all uncomplete tokens and surveys associated with this interview which are
    // associated with phases which are not repeated (tokens should not include assignments)
    $db_participant = $this->get_participant();
    $phase_mod = lib::create( 'database\modifier' );
    $phase_mod->where( 'repeated', '!=', true );
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );
    foreach( $this->get_qnaire()->get_phase_list( $phase_mod ) as $db_phase )
    {
      $old_tokens_sid = $tokens_class_name::get_sid();
      $tokens_class_name::set_sid( $db_phase->sid );
      $old_survey_sid = $survey_class_name::get_sid();
      $survey_class_name::set_sid( $db_phase->sid );

      // update tokens
      $tokens_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $tokens_mod, $db_participant, $db_phase->repeated );
      $tokens_mod->where( 'completed', '=', 'N' );
      foreach( $tokens_class_name::select( $tokens_mod ) as $db_tokens )
      {
        $db_tokens->completed = util::get_datetime_object()->format( 'Y-m-d H:i' );
        $db_tokens->usesleft = 0;
        $db_tokens->save();
      }

      // update surveys
      $survey_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $survey_mod, $db_participant, $db_phase->repeated );
      $survey_mod->where( 'submitdate', '=', NULL );

      // get the last page for this survey
      $lastpage = $survey_class_name::db()->get_one( sprintf(
        'SELECT MAX( lastpage ) FROM %s',
        $survey_class_name::get_table_name() ) );

      foreach( $survey_class_name::select( $survey_mod ) as $db_survey )
      {
        $db_survey->submitdate = util::get_datetime_object()->format( 'Y-m-d H:i:s' );
        if( $lastpage ) $db_survey->lastpage = $lastpage;
        $db_survey->save();
      }

      $tokens_class_name::set_sid( $old_tokens_sid );
      $survey_class_name::set_sid( $old_survey_sid );
    }

    // finally, update the record
    $this->complete();
  }

  /**
   * Forces an interview to become completed.
   * 
   * This method will update an interview's status to be incomplete.  It will also delete the
   * correspinding limesurvey data.  This action cannot be undone.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function force_uncomplete()
  {
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to force uncomplete interview with no id.' );
      return;
    }
    
    // delete all tokens and surveys associated with this interview which are
    // associated with phases which are not repeated (tokens should not include assignments)
    $db_participant = $this->get_participant();
    $phase_mod = lib::create( 'database\modifier' );
    $phase_mod->where( 'repeated', '!=', true );
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );
    foreach( $this->get_qnaire()->get_phase_list( $phase_mod ) as $db_phase )
    {
      $old_tokens_sid = $tokens_class_name::get_sid();
      $tokens_class_name::set_sid( $db_phase->sid );
      $old_tokens_sid = $tokens_class_name::get_sid();
      $survey_class_name::set_sid( $db_phase->sid );

      // delete tokens
      $tokens_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $tokens_mod, $db_participant, $db_phase->repeated );
      foreach( $tokens_class_name::select( $tokens_mod ) as $db_tokens )
      {
        $db_tokens->completed = 'N';
        $db_tokens->usesleft = 1;
        $db_tokens->save();
      }

      // delete surveys
      $survey_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $survey_mod, $db_participant, $db_phase->repeated );

      foreach( $survey_class_name::select( $survey_mod ) as $db_survey )
      {
        $db_survey->submitdate = NULL;
        $db_survey->save();
      }

      $tokens_class_name::set_sid( $old_tokens_sid );
      $survey_class_name::set_sid( $old_survey_sid );
    }

    // finally, update the record
    $this->completed = false;
    $this->save();

    // remove completed events (if any exist)
    $db_event_type = $this->get_qnaire()->get_completed_event_type();
    if( !is_null( $db_event_type ) )
    {
      $event_mod = lib::create( 'database\modifier' );
      $event_mod->where( 'event_type_id', '=', $db_event_type->id );
      foreach( $this->get_participant()->get_event_list( $event_mod ) as $db_event )
        $db_event->delete();
    }
  }

  /**
   * Returns the most recent total number of consecutive failed calls.  A maximum of one
   * failed call per assignment is counted.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return int
   * @access public
   */
  public function get_failed_call_count()
  {
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to get failed call count for interview with no id.' );
      return;
    }
    
    $assignment_mod = lib::create( 'database\modifier' );
    $assignment_mod->order_desc( 'start_datetime' );
    $assignment_mod->where( 'end_datetime', '!=', NULL );
    $failed_calls = 0;
    foreach( $this->get_assignment_list( $assignment_mod ) as $db_assignment )
    {
      // find the most recently completed phone call
      $phone_call_mod = lib::create( 'database\modifier' );
      $phone_call_mod->order_desc( 'start_datetime' );
      $phone_call_mod->where( 'status', '=', 'contacted' );
      $phone_call_mod->where( 'end_datetime', '!=', NULL );
      if( 0 < $db_assignment->get_phone_call_count( $phone_call_mod ) ) break;
      $failed_calls++;
    }

    return $failed_calls;
  }

  public static function get_interview_for_sid( $db_participant, $sid )
  {
    $sql = sprintf(
      'SELECT interview.id '.
      'FROM interview '.
      'JOIN participant on interview.participant_id = participant.id '.
      'JOIN qnaire on interview.qnaire_id = qnaire.id '.
      'JOIN phase on qnaire.id = phase.qnaire_id '.
      'WHERE participant.id = %s '.
      'AND phase.sid = %s',
      static::db()->format_string( $db_participant->id ),
      static::db()->format_string( $sid ) );

    $id = static::db()->get_one( $sql );
    return is_null( $id ) ? NULL : new static( $id );
  }
  
  /**
   * Creates the interview_failed_call_count temporary table needed by all queues.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   * @static
   */
  public static function create_interview_failed_call_count()
  {
    if( static::$interview_failed_call_count_created ) return;
    static::db()->execute( 'SET @next := @series := @nc := @interview_id := 0' );
    $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS interview_failed_call_count '.
           static::$interview_failed_call_count_sql;
    static::db()->execute( $sql );
    static::db()->execute(
      'ALTER TABLE interview_failed_call_count '.
      'ADD INDEX dk_interview_id ( interview_id ), '.
      'ADD INDEX dk_total ( total )' );
    static::$interview_failed_call_count_created = true;
  }
  
  /**
   * Whether the interview_failed_call_count temporary table has been created.
   * @var boolean
   * @static
   */
  protected static $interview_failed_call_count_created = false;

  /**
   * A string containing the SQL used to create the interview_failed_call_count data
   * @var string
   * @static
   */
  protected static $interview_failed_call_count_sql = <<<'SQL'
SELECT interview_id, total FROM
(
  SELECT interview_id, series, max( nc ) AS total
  FROM
  (
    SELECT
      @next := IF( interview_id != COALESCE( @interview_id, 0 ) OR status = "contacted", 1, 0 ) AS next,
      @series := COALESCE( @series, 0 ) + IF( @next, 1, 0 ) AS series,
      @nc := IF( @next, IF( status = "contacted", 0, 1 ), @nc + 1 ) AS nc,
      @interview_id := interview_id AS interview_id,
      status
    FROM
    (
      SELECT interview_id, status
      FROM assignment
      JOIN phone_call on assignment.id = phone_call.assignment_id
      WHERE phone_call.end_datetime is not null
      ORDER by interview_id, phone_call.end_datetime
    ) AS t1
  ) AS t2
  GROUP BY interview_id, series ORDER BY interview_id, series DESC
) AS t3
GROUP BY interview_id
SQL;
}

// define the join to the participant_site table
$participant_site_mod = lib::create( 'database\modifier' );
$participant_site_mod->where(
  'interview.participant_id', '=', 'participant_site.participant_id', false );
interview::customize_join( 'participant_site', $participant_site_mod );

// define the join to the last assignment
$assignment_mod = lib::create( 'database\modifier' );
$assignment_mod->where( 'interview.id', '=', 'interview_last_assignment.interview_id', false );
$assignment_mod->where( 'interview_last_assignment.assignment_id', '=', 'assignment.id', false );
interview::customize_join( 'assignment', $assignment_mod );
