<?php
/**
 * timing.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Consent form report data.
 * 
 * @abstract
 */
class timing_report extends \cenozo\ui\pull\base_report
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
    parent::__construct( 'timing', $args );
  }

  /**
   * Builds the report.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function build()
  {
    $region_class_name = lib::get_class_name( 'database\region' );
    $groups_class_name = lib::get_class_name( 'database\limesurvey\groups' );
    $survey_timings_class_name = lib::get_class_name( 'database\limesurvey\survey_timings' );

    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );
    $this->add_title( sprintf( 'Module timings for the %s interview (in minutes)',
                               $db_qnaire->name ) );

    $data = array();

    // loop through all canadian provinces
    $region_mod = lib::create( 'database\modifier' );
    $region_mod->where( 'country', '=', 'canada' );
    $region_mod->order( 'abbreviation' );
    foreach( $region_class_name::select( $region_mod ) as $db_region )
    {
      $data[$db_region->abbreviation] = array();

      // go through all of the qnaire's surveys
      $phase_mod = lib::create( 'database\modifier' );
      $phase_mod->order( 'rank' );
      foreach( $db_qnaire->get_phase_list() as $db_phase )
      {
        $old_sid = $survey_timings_class_name::get_sid();
        $survey_timings_class_name::set_sid( $db_phase->sid );
        $averages = $survey_timings_class_name::get_averages( $db_region );
        $survey_timings_class_name::set_sid( $old_sid );

        // skip regions with no data
        if( array_sum( $averages ) )
        {
          $groups_mod = lib::create( 'database\modifier' );
          $groups_mod->where( 'sid', '=', $db_phase->sid );
          $groups_mod->where( 'language', '=',
            lib::create( 'business\session' )->get_service()->get_language()->code );
          $groups_mod->order( 'group_order' );
          foreach( $groups_class_name::get_data( $groups_mod ) as $group_data )
          {
            // sum the times for all questions in this group
            $sub_code = sprintf( '/^%dX%dX/', $db_phase->sid, $group_data['gid'] );
            $average = 0;
            foreach( $averages as $code => $time )
              if( preg_match( $sub_code, $code ) ) $average += $time;
    
            $data[$db_region->abbreviation][$group_data['group_name']] = $average;
          }
        }
      }
    }

    // create the content and header arrays using the data
    $header = array( '' );
    $footer = array( 'Overall length of interview' );
    $content = array();
    foreach( $data as $region => $subdata )
    {
      // don't include regions with no data
      if( array_sum( $subdata ) )
      {
        $header[] = $region;
        $footer[] = 'sum()';
        foreach( $subdata as $group => $value )
        {
          if( !array_key_exists( $group, $content ) ) $content[$group] = array();
          $content[$group][0] = $group;
          $content[$group][$region] = sprintf( $value ? '%0.1f' : '', $value / 60 );
        }
      }
    }

    // now add the average column
    $header[] = 'Average';
    $footer[] = 'sum()';
    foreach( $content as $group => $values )
      $content[$group]['sum'] = sprintf(
        '%0.2f', array_sum( array_slice( $values, 1 ) ) / ( count( $values ) - 1 ) );

    $this->add_table( NULL, $header, $content, $footer );
  }
}
