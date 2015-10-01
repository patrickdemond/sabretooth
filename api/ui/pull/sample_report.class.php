<?php
/**
 * sample_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Sample report data.
 * 
 * @abstract
 */
class sample_report extends \cenozo\ui\pull\base_report
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
    parent::__construct( 'sample', $args );
  }

  /**
   * Builds the report.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function build()
  {
    $db = lib::create( 'business\session' )->get_database();
    $participant_class_name = lib::get_class_name( 'database\participant' );

    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );
    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $db_site = $restrict_site_id
             ? lib::create( 'database\site', $restrict_site_id )
             : NULL;
    $restrict_collection_id = $this->get_argument( 'restrict_collection_id', 0 );
    $db_collection = $restrict_collection_id
                   ? lib::create( 'database\collection', $restrict_collection_id )
                   : NULL;
    $service_id = lib::create( 'business\session' )->get_service()->id;
    
    $this->add_title( sprintf( 'For the %s interview', $db_qnaire->name ) ) ;
    $this->add_title( is_null( $db_site ) ?
      'for all sites' : sprintf( 'restricted to the %s site', $db_site->name ) );
    if( !is_null( $db_collection ) )
      $this->add_title( sprintf( 'restricted to the %s collection', $db_collection->name ) );
    
    // create a temporary participant site table
    $temp_site_mod = lib::create( 'database\modifier' );
    $temp_site_mod->where( 'service_id', '=', $service_id );
    $participant_class_name::db()->execute(
      'CREATE TEMPORARY TABLE temp_site '.
      'SELECT * FROM participant_site '.
      $temp_site_mod->get_sql() );
    $participant_class_name::db()->execute(
      'ALTER TABLE temp_site '.
      'ADD INDEX dk_participant_id ( participant_id ), '.
      'ADD INDEX dk_site_id ( site_id )' );

    // create a temporary participant primary address table
    $participant_class_name::db()->execute(
      'CREATE TEMPORARY TABLE temp_primary_address '.
      'SELECT * FROM participant_primary_address' );
    $participant_class_name::db()->execute(
      'ALTER TABLE temp_primary_address '.
      'ADD INDEX dk_participant_id ( participant_id ), '.
      'ADD INDEX dk_address_id ( address_id )' );

    // create a temporary last consent table
    $participant_class_name::db()->execute(
      'CREATE TEMPORARY TABLE temp_last_consent '.
      'SELECT * FROM participant_last_consent' );
    $participant_class_name::db()->execute(
      'ALTER TABLE temp_last_consent '.
      'ADD INDEX dk_participant_id ( participant_id )' );

    $sql =
      'SELECT uid, '.
        'cohort.name AS Cohort, '.
        ( is_null( $db_site ) ? 'site.name AS Site, ' : '' ).
        'participant.date_of_birth AS DOB, '.
        'TIMESTAMPDIFF( YEAR, participant.date_of_birth, CURDATE() ) AS Age, '.
        'participant.gender AS Sex, '.
        'IFNULL( language.code, "" ) AS Language, '.
        'region.abbreviation AS Province, '.
        'IFNULL( state.name, "" ) AS State, '.
        'IFNULL( DATE( require_event.datetime ), "n/a" ) AS "Previous Complete Date", '.
        'IFNULL( DATE( require_event.datetime + '.
                        'INTERVAL qnaire.delay WEEK + '.
                        'INTERVAL 1 DAY ), "n/a" ) AS "Date Available", '.
        'COUNT( phone_call.id ) AS "Number Of Calls", '.
        'IFNULL( DATE( appointment.datetime ), "" ) AS "Appointment", '.
        'IF( participant.email IS NULL, "no", "yes" ) AS "Email" ';

    $sql .= sprintf(
      'FROM qnaire '.
      'CROSS JOIN participant '.
      'JOIN service_has_participant ON participant.id = service_has_participant.participant_id '.
      'AND service_has_participant.datetime IS NOT NULL '.
      'AND service_has_participant.service_id = %s '.
      'JOIN cohort ON participant.cohort_id = cohort.id '.
      'JOIN temp_site ON participant.id = temp_site.participant_id '.
      'JOIN site ON temp_site.site_id = site.id '.
      'LEFT JOIN temp_primary_address ON temp_primary_address.participant_id = participant.id '.
      'LEFT JOIN address ON temp_primary_address.address_id = address.id '.
      'LEFT JOIN region ON address.region_id = region.id '.
      'LEFT JOIN state ON participant.state_id = state.id '.
      'LEFT JOIN event ON participant.id = event.participant_id '.
      'AND event.event_type_id = qnaire.completed_event_type_id '.
      'LEFT JOIN event AS require_event ON participant.id = require_event.participant_id '.
      'AND require_event.event_type_id IN ( '.
        'SELECT event_type_id '.
        'FROM qnaire_has_event_type '.
        'WHERE qnaire_id = qnaire.id '.
      ') '.
      'LEFT JOIN interview ON participant.id = interview.participant_id '.
      'LEFT JOIN assignment ON interview.id = assignment.interview_id '.
      'LEFT JOIN phone_call ON assignment.id = phone_call.assignment_id '.
      'LEFT JOIN appointment ON participant.id = appointment.participant_id '.
      'AND appointment.assignment_id IS NULL '.
      'JOIN temp_last_consent ON temp_last_consent.participant_id = participant.id '.
      'LEFT JOIN language ON participant.language_id = language.id ',
      $db->format_string( $service_id ) );

    if( !is_null( $db_collection ) )
    {
      $sql .= sprintf(
        'JOIN collection_has_participant '.
        'ON participant.id = collection_has_participant.participant_id '.
        'AND collection_has_participant.collection_id = %s',
        $db->format_string( $db_collection->id ) );
    }

    $sql .= sprintf(
      'WHERE participant.active = true '.
      'AND IFNULL( temp_last_consent.accept, true ) != false '.
      'AND IFNULL( interview.completed, 0 ) = 0 '.
      'AND qnaire.id = %s '.
      'AND ( '.
        'require_event.id IS NOT NULL OR ( '.
          'SELECT COUNT(*) '.
          'FROM qnaire_has_event_type '.
          'WHERE qnaire_id = qnaire.id '.
        ') = 0 '.
      ') ',
      $db->format_string( $db_qnaire->id ) );

    if( !is_null( $db_site ) )
      $sql .= sprintf( 'AND site.id = %s ', $db->format_string( $db_site->id ) );

    $sql .= 'GROUP BY participant.uid';

    $rows = $participant_class_name::db()->get_all( $sql );

    $header = array();
    $content = array();
    foreach( $rows as $row )
    {   
      // set up the header
      if( 0 == count( $header ) ) 
        foreach( $row as $column => $value )
          $header[] = ucwords( str_replace( '_', ' ', $column ) );

      $content[] = array_values( $row );
    }   

    $this->add_table( NULL, $header, $content, NULL );

  }
}
