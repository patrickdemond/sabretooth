<?php
/**
 * productivity_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Productivity report data.
 * 
 * @abstract
 */
class productivity_report extends \cenozo\ui\pull\base_report
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The subject to retrieve the primary information from.
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'productivity', $args );
  }

  /**
   * Builds the report.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function build()
  {
    $role_class_name = lib::get_class_name( 'database\role' );
    $site_class_name = lib::get_class_name( 'database\site' );
    $survey_timings_class_name = lib::get_class_name( 'database\limesurvey\survey_timings' );
    $limesurvey_database_name = lib::create( 'business\setting_manager' )->get_setting( 'survey_db', 'database' );

    $db_role = $role_class_name::get_unique_record( 'name', 'operator' );
    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $site_mod = lib::create( 'database\modifier' );
    if( $restrict_site_id ) 
      $site_mod->where( 'id', '=', $restrict_site_id );
    
    $restrict_start_date = $this->get_argument( 'restrict_start_date' );
    $restrict_end_date = $this->get_argument( 'restrict_end_date' );
    $now_datetime_obj = util::get_datetime_object();
    $start_datetime_obj = NULL;
    $end_datetime_obj = NULL;
    
    if( $restrict_start_date )
    {
      $start_datetime_obj = util::get_datetime_object( $restrict_start_date );
      if( $start_datetime_obj > $now_datetime_obj )
        $start_datetime_obj = clone $now_datetime_obj;
    }
    if( $restrict_end_date )
    {
      $end_datetime_obj = util::get_datetime_object( $restrict_end_date );
      if( $end_datetime_obj > $now_datetime_obj )
        $end_datetime_obj = clone $now_datetime_obj;
    }
    if( $restrict_start_date && $restrict_end_date && $end_datetime_obj < $start_datetime_obj )
    {
      $temp_datetime_obj = clone $start_datetime_obj;
      $start_datetime_obj = clone $end_datetime_obj;
      $end_datetime_obj = clone $temp_datetime_obj;
    }

    $db_phase = lib::create( 'database\phase', $this->get_argument( 'restrict_phase_id' ) );
    $this->add_title( sprintf( 'Operator productivity for %s', $db_phase->get_survey()->get_title() ) ) ;

    // if the phase doesn't have a timing table then do nothing
    if( !$survey_timings_class_name::db()->table_exists( sprintf( 'survey_%d_timings', $db_phase->sid ) ) )
      return;
    
    // we define the min and max datetime objects here, they get set in the next foreach loop, then
    // used in the for loop below
    $min_datetime_obj = NULL;
    $max_datetime_obj = NULL;
          
    // now create a table for every site included in the report
    foreach( $site_class_name::select( $site_mod ) as $db_site )
    {
      $modifier = lib::create( 'database\modifier' );
      $modifier->join( 'assignment', 'user.id', 'assignment.user_id' );
      $modifier->join( 'site', 'assignment.site_id', 'site.id' );
      $modifier->join( 'interview_last_assignment', 'assignment.id', 'interview_last_assignment.assignment_id' );
      $modifier->join( 'interview', 'interview_last_assignment.interview_id', 'interview.id' );
      $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
      $modifier->join(
        sprintf( '%s.survey_%d AS survey', $limesurvey_database_name, $db_phase->sid ),
        'participant.uid', 'survey.token' );
      $modifier->join(
        sprintf( '%s.survey_%d_timings AS timings', $limesurvey_database_name, $db_phase->sid ),
        'survey.id', 'timings.id' );
      $modifier->where( 'interview.completed', '=', true );
      $modifier->where( 'site.id', '=', $db_site->id );
      $modifier->group( 'user.id' );
      $modifier->order( 'user.name' );

      if( !is_null( $start_datetime_obj ) )
      {
        $modifier->where( 'DATE( submitdate )', '>=', $start_datetime_obj->format( 'Y-m-d' ) );
        $modifier->where( 'DATE( submitdate )', '<=', $end_datetime_obj->format( 'Y-m-d' ) );
      }

      $sql = sprintf(
        'SELECT '.
          'user.name AS Operator, '.
          'COUNT(*) AS Completes, '.
          'ROUND( SUM( interviewtime )/60/60, 3 ) AS "Total Time (hours)", '.
          'ROUND( (SUM( interviewtime )/60) / COUNT(*), 3 ) AS "Average Time (minutes)", '.
          'ROUND( COUNT(*) / (SUM( interviewtime )/60/60), 3 ) AS "Completes/Hour" '.
        'FROM user %s',
        $modifier->get_sql() );

      $rows = $site_class_name::db()->get_all( $sql );

      $completes = 0;
      $total_time = 0;
      $header = array();
      $content = array();
      foreach( $rows as $row )
      {   
        // set up the header
        if( 0 == count( $header ) ) 
          foreach( $row as $column => $value )
            $header[] = ucwords( str_replace( '_', ' ', $column ) );

        $content[] = array_values( $row );
        $completes += $row['Completes'];
        $total_time += $row['Total Time (hours)'];
      }

      $footer = array( 'Total', 'sum()', 'sum()',
        round( $total_time*60/$completes, 3 ), round( $completes/$total_time, 3 ) );

      $title = 0 == $restrict_site_id ? $db_site->name : NULL;
      $this->add_table( $title, $header, $content, $footer );
    }
  }
}
