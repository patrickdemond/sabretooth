<?php
/**
 * appointment_add.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget appointment add
 */
class appointment_add extends base_appointment_view
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
    parent::__construct( 'appointment', 'add', $args );
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
    
    // add items to the view
    $this->add_item( 'interview_id', 'hidden' );
    $this->add_item( 'phone_id', 'enum', 'Phone Number',
      'Select a specific phone number to call for the appointment, or leave this field blank if '.
      'any of the participant\'s phone numbers can be called.' );
    $this->add_item( 'type', 'enum', 'Type' );
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
    
    // create enum arrays
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'active', '=', true );
    $modifier->order( 'rank' );
    $phones = array();
    foreach( $this->db_participant->get_phone_list( $modifier ) as $db_phone )
      $phones[$db_phone->id] = $db_phone->rank.". ".$db_phone->number;
    
    $appointment_class_name = lib::get_class_name( 'database\appointment' );
    $types = $appointment_class_name::get_enum_values( 'type' );
    $types = array_combine( $types, $types );
    
    // create the min datetime array
    $datetime_limits = NULL;

    // add one day to the start date to avoid qnaire-wait datetime problems in the queue
    $start_qnaire_date = $this->parent->get_record()->get_start_qnaire_date();
    if( !is_null( $start_qnaire_date ) )
    {
      $start_qnaire_date->add( new \DateInterval( 'P1D' ) );
      $datetime_limits = array( 'min_date' => $start_qnaire_date->format( 'Y-m-d' ) );
    }

    // set the view's items
    $this->set_item( 'interview_id', $this->db_interview->id );
    $this->set_item( 'phone_id', '', false, $phones );
    $this->set_item( 'datetime', '', true, $datetime_limits );
    $this->set_item( 'type', key( $types ), true, $types );

    $this->set_variable(
      'allow_forced_appointment', 1 < lib::create( 'business\session' )->get_role()->tier );
  }
}
