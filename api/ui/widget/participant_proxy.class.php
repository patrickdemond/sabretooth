<?php
/**
 * participant_proxy.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget participant proxy
 */
class participant_proxy extends \cenozo\ui\widget
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'participant', 'proxy', $args );
  }

  /**
   * Processes arguments, preparing them for the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\notice
   * @access protected
   */
  protected function prepare()
  {
    parent::prepare();

    $this->set_heading( 'Participant Proxy' );
  }

  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();
    
    $session = lib::create( 'business\session' );
    $voip_manager = lib::create( 'business\voip_manager' );

    // fill out the participant's details
    $db_participant = lib::create( 'database\participant', $this->get_argument( 'id' ) );
    $db_language = $db_participant->get_language();
    
    $previous_call_list = array();
    $db_last_assignment = $db_participant->get_last_finished_assignment();
    if( !is_null( $db_last_assignment ) )
    {
      foreach( $db_last_assignment->get_phone_call_list() as $db_phone_call )
      {
        $db_phone = $db_phone_call->get_phone();
        $previous_call_list[] = sprintf( 'Called phone #%d (%s): %s',
          $db_phone->rank,
          $db_phone->type,
          $db_phone_call->status ? $db_phone_call->status : 'unknown' );
      }
    }

    // get the participant phone list
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'active', '=', true );
    $modifier->order( 'rank' );
    $db_phone_list = $db_participant->get_phone_list( $modifier );
    
    if( 0 < count( $db_phone_list ) )
    {
      $phone_list = array();
      foreach( $db_phone_list as $db_phone )
        $phone_list[$db_phone->id] =
          sprintf( '%d. %s (%s)', $db_phone->rank, $db_phone->type, $db_phone->number );
      $this->set_variable( 'phone_list', $phone_list );
    }

    $this->set_variable( 'participant_id', $db_participant->id );
    $this->set_variable( 'participant_note_count', $db_participant->get_note_count() );
    $this->set_variable( 'participant_name', $db_participant->get_full_name() );
    $this->set_variable( 'participant_uid', $db_participant->uid );
    $this->set_variable( 'participant_language',
      is_null( $db_language ) ? 'none' : $db_language->name );
    
    if( !is_null( $db_last_assignment ) )
    {
      $this->set_variable( 'previous_assignment_date',
        util::get_formatted_date( $db_last_assignment->start_datetime ) );
      $this->set_variable( 'previous_assignment_time',
        util::get_formatted_time( $db_last_assignment->start_datetime ) );
    }
    $this->set_variable( 'previous_call_list', $previous_call_list );
    $this->set_variable( 'allow_call', $session->get_allow_call() );
    $this->set_variable( 'sip_enabled', $voip_manager->get_sip_enabled() );
    $this->set_variable( 'on_call', !is_null( $voip_manager->get_call() ) );
    $this->set_variable( 'proxy_complete',
      false === lib::create( 'business\survey_manager' )->get_survey_url() );
  }
}
