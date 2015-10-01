<?php
/**
 * participant_tree.class.php
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
class participant_tree_report extends \cenozo\ui\pull\base_report
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
    parent::__construct( 'participant_tree', $args );
  }

  /**
   * Builds the report.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function build()
  {
    $db = lib::create( 'business\session' )->get_database();
    $interview_method_class_name = lib::get_class_name( 'database\interview_method' );
    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $site_class_name = lib::get_class_name( 'database\site' );

    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $restrict_source_id = $this->get_argument( 'restrict_source_id', 0 );
    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );
    $restrict_language_id = $this->get_argument( 'restrict_language_id' );
    $db_language = $restrict_language_id
                 ? lib::create( 'database\language', $restrict_language_id )
                 : NULL;

    $site_mod = lib::create( 'database\modifier' );
    if( $restrict_site_id ) $site_mod->where( 'id', '=', $restrict_site_id );

    $this->add_title( 'for the '.$db_qnaire->name.' questionnaire' );
    $this->add_title( is_null( $db_language ) ?
      'for all languages' : sprintf( 'restricted to %s participants', $db_language->name ) );

    $contents = array();

    $db_interview_method = $interview_method_class_name::get_unique_record( 'name', 'ivr' );

    // The following code is very similar to the participant_tree widget
    // We loop through every queue to get the number of participants waiting in it
    $queue_mod = lib::create( 'database\modifier' );
    $queue_mod->order( 'id' );
    if( !$qnaire_class_name::is_interview_method_in_use( $db_interview_method ) )
      $queue_mod->where( 'name', '!=', 'ivr_appointment' ); // remove IVR if not in use
    foreach( $queue_class_name::select( $queue_mod ) as $db_queue )
    {
      $row = array( $db_queue->title );

      foreach( $site_class_name::select( $site_mod ) as $db_site )
      {
        // restrict by site and source, if necessary
        // Note that queue modifiers have to be created for each iteration of the loop since
        // they are modified in the process of getting the participant count
        $participant_mod = lib::create( 'database\modifier' );
        $participant_mod->where( 'qnaire_id', '=', $db_qnaire->id );
        if( 0 < $restrict_source_id )
          $participant_mod->where( 'participant.source_id', '=', $restrict_source_id );

        // restrict by language
        if( !is_null( $db_language ) )
        {
          $column = sprintf( 'IFNULL( participant.language_id, %s )',
                             $db->format_string(
                               lib::create( 'business\session' )->get_service()->language_id ) );
          $participant_mod->where( $column, '=', $db_language->id );
        }

        $db_queue->set_site( $db_site );
        $row[] = $db_queue->get_participant_count( $participant_mod );
      }

      // add the grand total if we are not restricting by site
      if( !$restrict_site_id )
      {
        // restrict by source, if necessary
        // Note that queue modifiers have to be created for each iteration of the loop since
        // they are modified in the process of getting the participant count
        $participant_mod = lib::create( 'database\modifier' );
        if( 0 < $restrict_source_id )
          $participant_mod->where( 'participant.source_id', '=', $restrict_source_id );

        // restrict by language
        if( !is_null( $db_language ) )
        {
          $column = sprintf( 'IFNULL( participant.language_id, %s )',
                             $db->format_string(
                               lib::create( 'business\session' )->get_service()->language_id ) );
          $participant_mod->where( $column, '=', $db_language->id );
        }

        $db_queue->set_site( NULL );
        $row[] = $db_queue->get_participant_count( $participant_mod );
      }

      $contents[] = $row;
    }

    if( $restrict_site_id )
    {
      $header = array( 'Queue', 'Total' );
    }
    else
    {
      $header = array( 'Queue' );
      foreach( $site_class_name::select( $site_mod ) as $db_site ) $header[] = $db_site->name;
      $header[] = 'Total';
    }

    $this->add_table( NULL, $header, $contents, NULL );
  }
}
