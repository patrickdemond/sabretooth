<?php
/**
 * site_feed.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * pull: site feed
 */
class site_feed extends \cenozo\ui\pull\base_feed
{
  /**
   * Constructor
   * 
   * Defines all variables required by the site feed.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'site', $args );
  }
  
  /**
   * This method executes the operation's purpose.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function execute()
  {
    parent::execute();

    $daylight_savings = '1' == util::get_datetime_object()->format( 'I' );
    $site_id = $this->get_argument( 'site_id', false );
    $db_site = $site_id
             ? lib::create( 'database\site', $site_id )
             : lib::create( 'business\session' )->get_site();

    $setting_manager = lib::create( 'business\setting_manager' );
    $full_duration = $setting_manager->get_setting( 'appointment', 'full duration', $db_site );
    $half_duration = $setting_manager->get_setting( 'appointment', 'half duration', $db_site );

    // start by creating an array with one element per day in the time span
    $start_datetime_obj = util::get_datetime_object( $this->start_datetime );
    $end_datetime_obj   = util::get_datetime_object( $this->end_datetime );
    
    $days = array();
    $current_datetime_obj = clone $start_datetime_obj;
    while( !$current_datetime_obj->diff( $end_datetime_obj )->invert )
    {
      $days[ $current_datetime_obj->format( 'Y-m-d' ) ] = array(
        'template' => false,
        'diffs' => array(),
        'times' => array() );
      $current_datetime_obj->add( new \DateInterval( 'P1D' ) );
    }
    
    // fill in the slot differentials for shift templates each day
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'site_id', '=', $db_site->id );
    $modifier->where( 'start_date', '<=', $this->end_datetime );
    $shift_template_class_name = lib::get_class_name( 'database\shift_template' );
    foreach( $shift_template_class_name::select( $modifier ) as $db_shift_template )
    {
      foreach( $days as $date => $day )
      {
        $diffs = &$days[$date]['diffs'];
          
        if( $db_shift_template->match_date( $date ) )
        {
          $days[$date]['template'] = true;

          $start_time_as_int =
            intval( preg_replace( '/[^0-9]/', '',
              substr( $db_shift_template->start_time, 0, -3 ) ) );
          if( $daylight_savings ) $start_time_as_int -= 100; // adjust for daylight savings
          if( !array_key_exists( $start_time_as_int, $diffs ) ) $diffs[ $start_time_as_int ] = 0;
          $diffs[ $start_time_as_int ] += $db_shift_template->operators;

          $end_time_as_int =
            intval( preg_replace( '/[^0-9]/', '',
              substr( $db_shift_template->end_time, 0, -3 ) ) );
          if( 0 == $end_time_as_int ) $end_time_as_int = 2400;
          if( $daylight_savings ) $end_time_as_int -= 100; // adjust for daylight savings
          if( !array_key_exists( $end_time_as_int, $diffs ) ) $diffs[ $end_time_as_int ] = 0;
          $diffs[ $end_time_as_int ] -= $db_shift_template->operators;
        }

        // unset diffs since it is a reference
        unset( $diffs );
      }
    }

    // fill in the shifts (which override shift templates for that day)
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'site_id', '=', $db_site->id );
    $modifier->where( 'start_datetime', '<', $this->end_datetime );
    $modifier->where( 'end_datetime', '>', $this->start_datetime );
    $modifier->order( 'start_datetime' );
    $shift_class_name = lib::get_class_name( 'database\shift' );
    foreach( $shift_class_name::select( $modifier ) as $db_shift )
    {
      $start_datetime_obj = util::get_datetime_object( $db_shift->start_datetime );
      $end_datetime_obj   = util::get_datetime_object( $db_shift->end_datetime );

      // since db_site may not be the same site as the session, convert to the correct timzeone
      $start_datetime_obj->setTimezone( util::get_timezone_object( false, $db_site ) );
      $end_datetime_obj->setTimezone( util::get_timezone_object( false, $db_site ) );

      $date = $start_datetime_obj->format( 'Y-m-d' );
      
      if( $days[$date]['template'] )
      { // remove the shift templates for this day, replace with shift
        $days[$date]['diffs'] = array();
        $days[$date]['template'] = false;
      }

      $diffs = &$days[ $start_datetime_obj->format( 'Y-m-d' ) ]['diffs'];
      
      $start_time_as_int = intval( $start_datetime_obj->format( 'Gi' ) );
      $end_time_as_int   = intval( $end_datetime_obj->format( 'Gi' ) );
      if( 0 == $end_time_as_int ) $end_time_as_int = 2400;
      
      if( !array_key_exists( $start_time_as_int, $diffs ) ) $diffs[ $start_time_as_int ] = 0;
      $diffs[ $start_time_as_int ]++;
      if( !array_key_exists( $end_time_as_int, $diffs ) ) $diffs[ $end_time_as_int ] = 0;
      $diffs[ $end_time_as_int ]--;

      // unset diffs since it is a reference
      unset( $diffs );
    }

    // fill in the appointments which have not been completed
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'participant_site.site_id', '=', $db_site->id );
    $modifier->where( 'reached', '=', NULL );
    $modifier->where( 'datetime', '>=', $this->start_datetime );
    $modifier->where( 'datetime', '<', $this->end_datetime );
    $modifier->order( 'datetime' );
    $appointment_class_name = lib::get_class_name( 'database\appointment' );
    foreach( $appointment_class_name::select( $modifier ) as $db_appointment )
    {
      // determine the appointment interval
      $interval = sprintf(
        'PT%dM', $db_appointment->type == 'full' ? $full_duration : $half_duration );

      $appointment_datetime_obj = util::get_datetime_object( $db_appointment->datetime );

      /** NOTE: removed because it was incorrectly displaying slot times when showing another site's
                calendar (mcmaster viewing dalhousie's site calendar, for instance)
          ALSO SEE BELOW
      // since db_site may not be the same site as the session, convert to the correct timzeone
      $appointment_datetime_obj->setTimezone( util::get_timezone_object( false, $db_site ) );
      */

      $diffs = &$days[ $appointment_datetime_obj->format( 'Y-m-d' ) ]['diffs'];

      $start_time_as_int = intval( $appointment_datetime_obj->format( 'Gi' ) );
      // increment slot one hour later
      $appointment_datetime_obj->add( new \DateInterval( $interval ) );
      $end_time_as_int = intval( $appointment_datetime_obj->format( 'Gi' ) );
      if( 0 == $end_time_as_int ) $end_time_as_int = 2400;

      if( !array_key_exists( $start_time_as_int, $diffs ) ) $diffs[ $start_time_as_int ] = 0;
      $diffs[ $start_time_as_int ]--;
      if( !array_key_exists( $end_time_as_int, $diffs ) ) $diffs[ $end_time_as_int ] = 0;
      $diffs[ $end_time_as_int ]++;

      // unset diffs since it is a reference
      unset( $diffs );
    }
    
    // use the 'diff' arrays to define the 'times' array
    foreach( $days as $date => $day )
    {
      $num_operators = 0;
      $diffs = &$days[$date]['diffs'];
      $times = &$days[$date]['times'];
      
      if( 0 < count( $diffs ) )
      {
        // sort the diff array by key (time) to make the following for-loop nice and simple
        ksort( $diffs );
  
        foreach( $diffs as $time => $diff )
        {
          $num_operators += $diff;
          $times[$time] = $num_operators;
        }
      }

      // unset times since it is a reference
      unset( $times );
    }

    // finally, construct the event list using the 'times' array
    $start_time = false;
    $available = 0;
    $this->data = array();
    $site_offset = $db_site->get_time_diff();
    foreach( $days as $date => $day )
    {
      foreach( $day['times'] as $time => $number )
      {
        if( $number == $available ) continue;

        /** NOTE: removed because it was incorrectly displaying slot times when showing another site's
                  calendar (mcmaster viewing dalhousie's site calendar, for instance)
            ALSO SEE ABOVEyy
        // convert the time to the user's site's timezone
        $time -= ( 100 * $site_offset );
        */

        $minutes = $time % 100;
        $hours = ( $time - $minutes ) / 100;
        $time_string = sprintf( '%02d:%02d', $hours, $minutes );

        if( $start_time )
        {
          $end_time = $time_string;
          
          if( $available )
          {
            $end_time_for_title =
              sprintf( '%s%s%s',
                       $hours > 12 ? $hours - 12 : $hours,
                       $minutes ? ':'.sprintf( '%02d', $minutes ) : '',
                       $hours < 12 || 24 == $hours ? 'a' : 'p' );
            $this->data[] = array(
              'title' => sprintf( ' to %s: %d slots', $end_time_for_title, $available ),
              'allDay' => false,
              'start' => $date.' '.$start_time,
              'end' => $date.' '.$end_time );
          }
        }

        // only use this time as the next start time if the available number is not 0
        $start_time = 0 < $number ? $time_string : false;
        $available = $number;
      }
    }
  }
}
