<?php
/**
 * appointment_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget appointment list
 */
class appointment_list extends \cenozo\ui\widget\site_restricted_list
{
  /**
   * Constructor
   * 
   * Defines all variables required by the appointment list.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'appointment', $args );
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
    $this->add_column( 'participant.uid', 'string', 'UID', true );
    $this->add_column( 'qnaire.name', 'string', 'Questionnaire', true );
    $this->add_column( 'phone', 'string', 'Phone number', false );
    $this->add_column( 'datetime', 'datetime', 'Date', true );
    $this->add_column( 'state', 'string', 'State', false );

    $this->extended_site_selection = true;
  }

  /**
   * Set the rows array needed by the template.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    // don't add appointments if this list isn't parented
    if( is_null( $this->parent ) ) $this->set_addable( false );
    else
    {
      $appointment_class_name = lib::get_class_name( 'database\appointment' );
      $subject = $this->parent->get_subject();
      if( 'interview' == $subject )
      {
        $db_interview = $this->parent->get_record();
        $db_participant = $db_interview->get_participant();
      }
      else if( 'participant' == $subject )
      {
        $db_participant = $this->parent->get_record();
        $db_interview = $db_participant->get_effective_interview();
      }

      // don't add appointments if the parent already has an unassigned appointment
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'interview_id', '=', is_null( $db_interview ) ? NULL : $db_interview->id );
      $modifier->where( 'assignment_id', '=', NULL );
      $addable = 0 == $appointment_class_name::count( $modifier );

      // don't add appointments if the participant's interview method is ivr
      $db_interview_method = $db_participant->get_effective_interview_method();
      if( $addable && !is_null( $db_interview_method ) && 'ivr' == $db_interview_method->name )
        $addable = false;

      // don't add appointments if the participant has no effective qnaire
      if( $addable && is_null( $db_participant->get_effective_qnaire() ) )
        $addable = false;

      $this->set_addable( $addable );
    }

    parent::setup();

    foreach( $this->get_record_list() as $record )
    {
      $db_phone = $record->get_phone();
      $phone = is_null( $db_phone )
             ? 'not specified'
             : sprintf( '(%d) %s: %s',
                        $db_phone->rank,
                        $db_phone->type,
                        $db_phone->number );
      $this->add_row( $record->id,
        array( 'participant.uid' => $record->get_interview()->get_participant()->uid,
               'qnaire.name' => $record->get_interview()->get_qnaire()->name,
               'phone' => $phone,
               'datetime' => $record->datetime,
               'state' => $record->get_state() ) );
    }
  }

  /**
   * Overrides the parent class method to restrict by interview id, if necessary
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @access protected
   */
  public function determine_record_count( $modifier = NULL )
  {
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->join( 'interview', 'appointment.interview_id', 'interview.id' );
    $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
    $modifier->join( 'qnaire', 'interview.qnaire_id', 'qnaire.id' );
    return parent::determine_record_count( $modifier );
  }

  /**
   * Overrides the parent class method to restrict by interview id, if necessary
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @access protected
   */
  public function determine_record_list( $modifier = NULL )
  {
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->join( 'interview', 'appointment.interview_id', 'interview.id' );
    $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
    $modifier->join( 'qnaire', 'interview.qnaire_id', 'qnaire.id' );
    return parent::determine_record_list( $modifier );
  }
}
