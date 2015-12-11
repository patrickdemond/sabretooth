<?php
/**
 * progress_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Progress report data.
 * 
 * @abstract
 */
class progress_report extends \cenozo\ui\pull\base_report
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
    parent::__construct( 'progress', $args );
  }

  /**
   * Builds the report.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function build()
  {
    $site_class_name = lib::get_class_name( 'database\site' );
    $session = lib::create( 'business\session' );
    $db = $session->get_database();
    $db_service = $session->get_service();
    $limesurvey_database_name = lib::create( 'business\setting_manager' )->get_setting( 'survey_db', 'database' );

    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $site_mod = lib::create( 'database\modifier' );
    if( $restrict_site_id ) $site_mod->where( 'id', '=', $restrict_site_id );
    
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

    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );

    $this->add_title( sprintf( 'Participant progress for '.  'the %s interview', $db_qnaire->name ) ) ;
    
    // we define the min and max datetime objects here, they get set in the next foreach loop, then
    // used in the for loop below
    $min_datetime_obj = NULL;
    $max_datetime_obj = NULL;
          
    $header = array( 'UID' );
    $totals = array( 'Total' );

    // get the rank and sid of each phase
    $survey_list = array();
    $phase_mod = lib::create( 'database\modifier' );
    $phase_mod->where( 'repeated', '=', false );
    $phase_mod->order( 'rank' );
    $phase_list = $db_qnaire->get_phase_list( $phase_mod );

    $base_mod = lib::create( 'database\modifier' );
    $base_mod->order( 'uid' );
    $phase_selects = array();
    $phase_joins = array();
    foreach( $phase_list as $db_phase )
    {
      $phase_selects[] = sprintf( 'survey_%s.submitdate AS part_%d', $db_phase->sid, $db_phase->rank );
      $base_mod->left_join(
        $limesurvey_database_name.'.survey_'.$db_phase->sid,
        'participant.uid',
        'survey_'.$db_phase->sid.'.token' );
      $header[] = sprintf( 'Part %d', $db_phase->rank );
      $totals[] = 0;
    }

    // now create a table for every site included in the report
    foreach( $site_class_name::select( $site_mod ) as $db_site )
    {
      $site_totals = $totals;
      $modifier = clone $base_mod;
      $modifier->where( 'participant_site.site_id', '=', $db_site->id );
      $sql = sprintf(
        'SELECT uid, %s '.
        'FROM participant '.
        'JOIN participant_site ON participant.id = participant_site.participant_id '.
        'AND participant_site.service_id = %s '.
        '%s',
        implode( ', ', $phase_selects ),
        $db_service->id,
        $modifier->get_sql() );

      $contents = array();
      foreach( $db->get_all( $sql ) as $row )
      {
        if( $restrict_site_id ) $contents[] = array_values( $row );
        $index = 0;
        foreach( $row as $cell )
        {
          if( 0 < $index && $cell ) $site_totals[$index]++;
          $index++;
        }
      }
      array_unshift( $contents, $site_totals );

      $title = 0 == $restrict_site_id ? $db_site->name : NULL;
      $this->add_table( $title, $header, $contents );
    }
  }
}
