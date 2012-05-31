<?php
/**
 * assignment_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget assignment list
 * 
 * @package sabretooth\ui
 */
class assignment_list extends site_restricted_list
{
  /**
   * Constructor
   * 
   * Defines all variables required by the assignment list.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'assignment', $args );
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
    
    $this->add_column( 'user.name', 'string', 'Operator', true );
    $this->add_column( 'site.name', 'string', 'Site', true );
    $this->add_column( 'participant', 'string', 'Participant' );
    $this->add_column( 'calls', 'number', 'Calls' );
    $this->add_column( 'start_datetime', 'date', 'Date', true );
    $this->add_column( 'start_time', 'time', 'Start Time' );
    $this->add_column( 'end_time', 'time', 'End Time' );
    $this->add_column( 'status', 'string', 'Status' );
  }
  
  /**
   * Set the rows array needed by the template.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();
    
    foreach( $this->get_record_list() as $record )
    {
      $db_participant = $record->get_interview()->get_participant();
      $participant = sprintf( '%s, %s', $db_participant->last_name, $db_participant->first_name );
      
      // get the status of the last phone call for this assignment
      $modifier = lib::create( 'database\modifier' );
      $modifier->order_desc( 'end_datetime' );
      $modifier->limit( 1 );
      $phone_call_list = $record->get_phone_call_list( $modifier );
      $status = 0 == count( $phone_call_list ) ? 'no calls made' : $phone_call_list[0]->status;
      if( 0 == strlen( $status ) ) $status = 'in progress';

      // assemble the row for this record
      $this->add_row( $record->id,
        array( 'user.name' => $record->get_user()->name,
               'site.name' => $record->get_site()->name,
               'participant' => $participant,
               'calls' => $record->get_phone_call_count(),
               'start_datetime' => $record->start_datetime,
               'start_time' => $record->start_datetime,
               'end_time' => $record->end_datetime,
               'status' => $status,
               // note_count isn't a column, it's used for the note button
               'note_count' => $record->get_note_count() ) );
    }
  }

  /**
   * Overrides the parent class method since the record count depends on the active role
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @access protected
   */
  protected function determine_record_count( $modifier = NULL )
  {
    $session = lib::create( 'business\session' );
    if( 'operator' == $session->get_role()->name )
    {
      if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
      $db_assignment = $session->get_current_assignment();
      $participant_id = is_null( $db_assignment )
                      ? 0
                      : $db_assignment->get_interview()->participant_id;
      $modifier->where( 'interview.participant_id', '=', $participant_id );
      $modifier->where( 'end_datetime', '!=', NULL );
    }

    return parent::determine_record_count( $modifier );
  }

  /**
   * Overrides the parent class method since the record list depends on the active role.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @access protected
   */
  protected function determine_record_list( $modifier = NULL )
  {
    $session = lib::create( 'business\session' );
    if( 'operator' == $session->get_role()->name )
    {
      if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
      $db_assignment = $session->get_current_assignment();
      $participant_id = is_null( $db_assignment )
                      ? 0
                      : $db_assignment->get_interview()->participant_id;
      $modifier->where( 'interview.participant_id', '=', $participant_id );
      $modifier->where( 'end_datetime', '!=', NULL );
    }

    return parent::determine_record_list( $modifier );
  }
}
?>
