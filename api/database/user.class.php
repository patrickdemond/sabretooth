<?php
/**
 * user.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * user: record
 */
class user extends \cenozo\database\user
{
  /**
   * Returns the total number of phone calls made by the user.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier
   * @return int
   * @access public
   */
  public function get_phone_call_count( $db_qnaire, $modifier = NULL )
  {
    // check the primary key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to query user with no id.' );
      return 0;
    }

    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'phone_call.assignment_id', '=', 'assignment.id', false );
    $modifier->where( 'assignment.interview_id', '=', 'interview.id', false );
    $modifier->where( 'interview.qnaire_id', '=', $db_qnaire->id );
    $modifier->where( 'assignment.user_id', '=', $this->id );

    // custom SQL is required for this method
    return static::db()->get_one( sprintf(
      'SELECT COUNT(*) FROM phone_call, assignment, interview %s',
      $modifier->get_sql() ) );
  }

  /**
   * Returns the total number of interviews who's last assignment belong to this user, as well
   * as the sum of all time spent on those interviews, in seconds.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\qnaire $db_qnaire
   * @param database\site $db_site
   * @param database\modifier $modifier
   * @return array( int, float )
   * @access public
   */
  public function get_interview_count_and_time( $db_qnaire, $db_site, $modifier = NULL )
  {
    // check the primary key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to query user with no id.' );
      return 0;
    }

    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );

    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'assignment.user_id', '=', $this->id );
    $modifier->where( 'assignment.site_id', '=', $db_site->id );
    $modifier->where( 'interview.qnaire_id', '=', $db_qnaire->id );

    // get the list of all interviews related to this user
    $token_list = static::db()->get_col(
      'SELECT uid '.
      'FROM participant '.
      'JOIN interview ON participant.id = interview.participant_id '.
      'JOIN interview_last_assignment ON interview.id = interview_last_assignment.interview_id '.
      'JOIN assignment ON interview_last_assignment.assignment_id = assignment.id '.
      $modifier->get_sql() );

    // determine the time for all interviews in the list
    $time = 0;
    if( 0 < count( $token_list ) )
    {
      // get the times for all interviews
      $phase_mod = lib::create( 'database\modifier' );
      $phase_mod->where( 'repeated', '=', false );
      foreach( $db_qnaire->get_phase_list( $phase_mod ) as $db_phase )
      {
        // first try the phase's default sid
        $survey_mod = lib::create( 'database\modifier' );
        $survey_mod->where( 'token', 'IN', $token_list );
        $survey_class_name::set_sid( $db_phase->sid );
        $time += $survey_class_name::get_total_time( $survey_mod );

        // then go through each source-specifc sid
        foreach( $db_phase->get_source_survey_list() as $db_source_survey )
        {
          $survey_mod = lib::create( 'database\modifier' );
          $survey_mod->where( 'token', 'IN', $token_list );
          $survey_class_name::set_sid( $db_source_survey->sid );
          $time += $survey_class_name::get_total_time( $survey_mod );
        }
      }
    }

    return array( 'count' => count( $interview_id_list ), 'time' => $time );
  }
}
