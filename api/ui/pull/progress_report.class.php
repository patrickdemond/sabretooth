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
    $participant_class_name = lib::get_class_name( 'database\participant' );
    $site_class_name = lib::get_class_name( 'database\site' );
    $session = lib::create( 'business\session' );
    $db = $session->get_database();
    $db_service = $session->get_service();
    $limesurvey_database_name = lib::create( 'business\setting_manager' )->get_setting( 'survey_db', 'database' );

    $include_withdrawn = $this->get_argument( 'include_withdrawn' );
    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $site_mod = lib::create( 'database\modifier' );
    if( $restrict_site_id ) $site_mod->where( 'id', '=', $restrict_site_id );
    
    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );

    $this->add_title( sprintf( 'Participant progress for '.  'the %s interview', $db_qnaire->name ) ) ;
    
    // create a temporary last consent table
    if( !$include_withdrawn )
    {
      $participant_class_name::db()->execute(
        'CREATE TEMPORARY TABLE temp_last_consent '.
        'SELECT * FROM participant_last_consent WHERE IFNULL( accept, true ) = true' );
      $participant_class_name::db()->execute(
        'ALTER TABLE temp_last_consent '.
        'ADD INDEX dk_participant_id ( participant_id )' );
    }

    // we define the min and max datetime objects here, they get set in the next foreach loop, then
    // used in the for loop below
    $min_datetime_obj = NULL;
    $max_datetime_obj = NULL;
          
    $header = array( 'UID' );
    $totals = array( 'Total' );

    // add the state column
    if( $restrict_site_id )
    {
      $header[] = 'Condition';
      $totals[] = '';
    }

    // get the rank and sid of each phase
    $survey_list = array();
    $phase_mod = lib::create( 'database\modifier' );
    $phase_mod->where( 'repeated', '=', false );
    $phase_mod->order( 'rank' );
    $phase_list = $db_qnaire->get_phase_list( $phase_mod );

    $phase_selects = array();
    $phase_joins = array();

    $base_mod = lib::create( 'database\modifier' );
    $base_mod->order( 'uid' );
    if( $restrict_site_id ) $base_mod->left_join( 'state', 'participant.state_id', 'state.id' );
    if( !$include_withdrawn )
      $base_mod->join( 'temp_last_consent', 'participant.id', 'temp_last_consent.participant_id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'participant.id', '=', 'service_has_participant.participant_id', false );
    $join_mod->where( 'service_has_participant.service_id', '=', $db_service->id );
    $base_mod->join_modifier( 'service_has_participant', $join_mod );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'participant.id', '=', 'participant_site.participant_id', false );
    $join_mod->where( 'participant_site.service_id', '=', $db_service->id );
    $base_mod->join_modifier( 'participant_site', $join_mod );
    $base_mod->where( 'service_has_participant.datetime', '!=', NULL );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'participant.id', '=', 'appointment.participant_id', false );
    $join_mod->where( 'appointment.assignment_id', '=', NULL );
    $base_mod->join_modifier( 'appointment', $join_mod, 'left' );

    foreach( $phase_list as $db_phase )
    {
      $phase_selects[] = sprintf( 'survey_%s.submitdate AS part_%d', $db_phase->sid, $db_phase->rank );
      $base_mod->left_join(
        $limesurvey_database_name.'.survey_'.$db_phase->sid,
        'participant.uid',
        'survey_'.$db_phase->sid.'.token' );
        
      $header[] = $db_phase->get_survey()->get_title();
      $totals[] = 0;
    }

    $header[] = 'Appointment';
    $totals[] = 0;

    // now create a table for every site included in the report
    foreach( $site_class_name::select( $site_mod ) as $db_site )
    {
      $site_totals = $totals;
      $modifier = clone $base_mod;
      $modifier->where( 'participant_site.site_id', '=', $db_site->id );
      $sql = sprintf(
        'SELECT uid, %s%s, appointment.datetime '.
        'FROM participant '.
        '%s',
        $restrict_site_id ? 'IFNULL( state.name, "" ), ' : '',
        implode( ', ', $phase_selects ),
        $modifier->get_sql() );

      $contents = array();
      foreach( $db->get_all( $sql ) as $row )
      {
        if( $restrict_site_id ) $contents[] = array_values( $row );
        $index = 0;
        foreach( $row as $cell )
        {
          if( 1 < $index && $cell ) $site_totals[$index]++;
          $index++;
        }
      }
      array_unshift( $contents, $site_totals );

      $title = 0 == $restrict_site_id ? $db_site->name : NULL;
      $this->add_table( $title, $header, $contents );
    }
  }
}
