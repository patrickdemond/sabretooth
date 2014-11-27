<?php
/**
 * ivr_appointment_delete.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: ivr_appointment delete
 */
class ivr_appointment_delete extends \cenozo\ui\push\base_delete
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'ivr_appointment', $args );
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

    $ivr_manager = lib::create( 'business\ivr_manager' );

    // send message to IVR
    $db_interview = $this->get_record()->get_interview();
    $ivr_manager->remove_appointment( $db_interview );

    // update the participant's queue status
    $db_interview->get_participant()->update_queue_status();
  }
}
