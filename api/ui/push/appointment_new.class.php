<?php
/**
 * appointment_new.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: appointment new
 *
 * Create a new appointment.
 */
class appointment_new extends \cenozo\ui\push\base_new
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
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
    
    $interview_class_name = lib::get_class_name( 'database\interview' );

    // determine the interview method
    $columns = $this->get_argument( 'columns' );
    $db_participant = lib::create( 'database\participant', $columns['participant_id'] );
    $db_qnaire = $db_participant->get_effective_qnaire();
    $db_interview = $interview_class_name::get_unique_record(
      array( 'participant_id', 'qnaire_id' ),
      array( $db_participant->id, $db_qnaire->id ) );
    $this->db_interview_method = $db_interview->get_interview_method();
  }
      
  /**
   * Validate the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\notice
   * @access protected
   */
  protected function validate()
  {
    parent::validate();

    $columns = $this->get_argument( 'columns' );

    // make sure the datetime column isn't blank
    if( !array_key_exists( 'datetime', $columns ) || 0 == strlen( $columns['datetime'] ) )
      throw lib::create( 'exception\notice', 'The date/time cannot be left blank.', __METHOD__ );
    
    $db_participant = lib::create( 'database\participant', $columns['participant_id'] );
    $db_qnaire = $db_participant->get_effective_qnaire();

    // make sure the participant has a qnaire to answer
    if( is_null( $db_qnaire ) )
    {
      throw lib::create( 'exception\notice',
        'Unable to create an appointment because the participant has completed all questionnaires.',
        __METHOD__ );
    }
    else if( !$this->get_argument( 'force', false ) )
    {
      // validate the appointment time if the interview is operator-based
      if( 'operator' == $this->db_interview_method->name )
      {
        $this->get_record()->participant_id = $columns['participant_id'];
        $this->get_record()->datetime = $columns['datetime'];
        $this->get_record()->type = $columns['type'];
        if( !$this->get_record()->validate_date() )
        {
          $db_site = $db_participant->get_effective_site();

          // determine the full and half appointment intervals
          $setting_manager = lib::create( 'business\setting_manager' );
          $half_duration = $setting_manager->get_setting( 'appointment', 'half duration', $db_site );
          $full_duration = $setting_manager->get_setting( 'appointment', 'full duration', $db_site );
          $duration = 'full' == $this->get_record()->type ? $full_duration : $half_duration;

          $start_datetime_obj = util::get_datetime_object( $this->get_record()->datetime );
          $end_datetime_obj = clone $start_datetime_obj;
          $end_datetime_obj->add( new \DateInterval( sprintf( 'PT%dM', $duration ) ) );
          throw lib::create( 'exception\notice',
            sprintf(
              'Unable to create a %s appointment (%d minutes) since there is not '.
              'at least 1 slot available from %s and %s.',
              $this->get_record()->type,
              $duration,
              $start_datetime_obj->format( 'H:i' ),
              $end_datetime_obj->format( 'H:i' ) ),
            __METHOD__ );
        }
      }
    }

    // make sure that appointments for IVR interviews have a phone number
    if( array_key_exists( 'phone_id', $columns ) )
    {
      if( 'ivr' == $this->db_interview_method->name && NULL == $columns['phone_id'] )
        throw lib::create( 'exception\notice',
          'This participant\'s interview uses the IVR system so a phone number '.
          'must be provided for all appointments.',
          __METHOD__ );
    }
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

    // send message to IVR if interview-method is IVR
    if( 'ivr' == $this->db_interview_method->name )
    {
      $record = $this->get_record();
      $ivr_manager = lib::create( 'business\ivr_manager' );
      $ivr_manager->set_appointment(
        $record->get_participant(),
        $record->get_phone(),
        $record->datetime );
    }

    // if the owner is a participant then update their queue status
    $this->get_record()->get_participant()->update_queue_status();
  }

  /**
   * The participant's current interview's interview method (cached)
   * @var database\interview_method
   * @access private
   */
  private $db_interview_method = NULL;
}
