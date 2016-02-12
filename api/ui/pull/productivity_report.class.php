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
    // determine whether or not to round time to 15 minute increments
    $round_times = $this->get_argument( 'round_times', true );

    $role_class_name = lib::get_class_name( 'database\role' );
    $site_class_name = lib::get_class_name( 'database\site' );
    $user_class_name = lib::get_class_name( 'database\user' );
    $activity_class_name = lib::get_class_name( 'database\activity' );
    $user_time_class_name = lib::get_class_name( 'database\user_time' );

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

    // determine whether we are running the report for a single date or not
    $single_date = ( !is_null( $start_datetime_obj ) &&
                     !is_null( $end_datetime_obj ) &&
                     $start_datetime_obj == $end_datetime_obj ) || 
                   ( !is_null( $start_datetime_obj ) &&
                     $start_datetime_obj == $now_datetime_obj );

    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );
    
    $this->add_title( 'Operator productivity' );
    
    // we define the min and max datetime objects here, they get set in the next foreach loop, then
    // used in the for loop below
    $min_datetime_obj = NULL;
    $max_datetime_obj = NULL;
          
    // now create a table for every site included in the report
    foreach( $site_class_name::select( $site_mod ) as $db_site )
    {
      $contents = array();
      // start by determining the table contents
      $grand_total_time = 0;
      $grand_total_completes = array();
      $user_list_mod = lib::create( 'database\modifier' );
      foreach( $user_class_name::select() as $db_user )
      {
        // create modifiers for the activity, interview and user_time queries
        $activity_mod = lib::create( 'database\modifier' );
        $activity_mod->where( 'activity.user_id', '=', $db_user->id );
        $activity_mod->where( 'activity.site_id', '=', $db_site->id );
        $activity_mod->where( 'activity.role_id', '=', $db_role->id );
        $activity_mod->where( 'operation.subject', '!=', 'self' );
        $interview_mod = lib::create( 'database\modifier' );
        
        if( $restrict_start_date && $restrict_end_date )
        {
          $activity_mod->where( 'datetime', '>=',
            $start_datetime_obj->format( 'Y-m-d' ).' 0:00:00' );
          $activity_mod->where( 'datetime', '<=',
            $end_datetime_obj->format( 'Y-m-d' ).' 23:59:59' );
          $interview_mod->where( 'assignment.start_datetime', '>=',
            $start_datetime_obj->format( 'Y-m-d' ).' 0:00:00' );
          $interview_mod->where( 'assignment.end_datetime', '<=',
            $end_datetime_obj->format( 'Y-m-d' ).' 23:59:59' );
        }
        else if( $restrict_start_date && !$restrict_end_date ) 
        {
          $activity_mod->where( 'datetime', '>=',
            $start_datetime_obj->format( 'Y-m-d' ).' 0:00:00' );
          $interview_mod->where( 'assignment.start_datetime', '>=',
            $start_datetime_obj->format( 'Y-m-d' ).' 0:00:00' );
        }
        else if( !$restrict_start_date && $restrict_end_date )
        {
          $activity_mod->where( 'datetime', '<=',
            $end_datetime_obj->format( 'Y-m-d' ).' 23:59:59' );
          $interview_mod->where( 'assignment.start_datetime', '<=',
            $end_datetime_obj->format( 'Y-m-d' ).' 23:59:59' );
        }

        // if there is no activity then skip this user
        if( 0 == $activity_class_name::count( $activity_mod ) ) continue;

        // Determine the number of completed interviews and interview times
        $interview_details = $db_user->get_interview_count_and_time( $db_qnaire, $db_site, $interview_mod );
        $count_list = $interview_details['count'];
        $time_list = $interview_details['time'];

        // Determine the total time spent as an operator over the desired period
        $total_time = $user_time_class_name::get_sum(
          $db_user, $db_site, $db_role, $start_datetime_obj, $end_datetime_obj, $round_times );

        // if there was no time spent then ignore this user
        if( 0 == $total_time ) continue;

        // Now we can use all the information gathered above to fill in the contents of the table.
        ///////////////////////////////////////////////////////////////////////////////////////////
        $content = array( $db_user->name );
        foreach( $count_list as $completes ) $content[] = $completes;

        if( $single_date )
        {
          $day_activity_mod = lib::create( 'database\modifier' );
          $day_activity_mod->where( 'activity.user_id', '=', $db_user->id );
          $day_activity_mod->where( 'activity.site_id', '=', $db_site->id );
          $day_activity_mod->where( 'activity.role_id', '=', $db_role->id );
          $day_activity_mod->where( 'operation.subject', '!=', 'self' );
          $day_activity_mod->where( 'datetime', '>=',
            $start_datetime_obj->format( 'Y-m-d' ).' 0:00:00' );
          $day_activity_mod->where( 'datetime', '<=',
            $start_datetime_obj->format( 'Y-m-d' ).' 23:59:59' );
          
          $min_datetime_obj = $activity_class_name::get_min_datetime( $day_activity_mod );
          $max_datetime_obj = $activity_class_name::get_max_datetime( $day_activity_mod );

          $content[] = is_null( $min_datetime_obj ) ? '??' : $min_datetime_obj->format( 'H:i' );
          $content[] = is_null( $max_datetime_obj ) ? '??' : $max_datetime_obj->format( 'H:i' );
        }

        $content[] = sprintf( '%0.2f', $total_time );
        foreach( $count_list as $completes )
          $content[] = $total_time > 0 ? sprintf( '%0.2f', $completes / $total_time ) : '';
        foreach( $count_list as $title => $completes )
          $content[] = $completes  > 0 ? sprintf( '%0.2f', $time_list[$title] / $completes / 60 ) : '';

        $contents[] = $content;

        foreach( $count_list as $title => $completes )
        {
          if( !array_key_exists( $title, $grand_total_completes ) ) $grand_total_completes[$title] = 0;
          $grand_total_completes[$title] += $count_list[$title];
        }
        $grand_total_time += $total_time;
      }

      $average_compPH = array();
      foreach( $grand_total_completes as $title => $completes )
        $average_compPH[$title] = $grand_total_time[$title] > 0 ? 
          sprintf( '%0.2f', $grand_total_completes / $grand_total_time[$title] ) : 'N/A';

      $header = array( 'Operator' );
      $footer = array( 'Total' );
      foreach( $grand_total_completes as $title => $completes )
      {
        $header[] = $title;
        $footer[] = 'sum()';
      }

      if( $single_date )
      {
        $header[] = 'Start Time';
        $header[] = 'End Time';

        $footer[] = '--';
        $footer[] = '--';
      }

      $header[] = 'Total Time';
      $footer[] = 'sum()';
      foreach( $grand_total_completes as $title => $completes )
      {
        $header[] = $title.' CompPH';
        $footer[] = $average_compPH[$title];
      }
      foreach( $grand_total_completes as $title => $completes )
      {
        $header[] = $title.' Avg. Length';
        $footer[] = 'average()';
      }

      $title = 0 == $restrict_site_id ? $db_site->name : NULL;
      $this->add_table( $title, $header, $contents, $footer );
    }
  }
}
