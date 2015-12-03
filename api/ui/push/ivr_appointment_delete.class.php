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
    $db_participant = $this->get_record()->get_participant();
    parent::execute();

    // send message to IVR
    $ivr_manager = lib::create( 'business\ivr_manager' );
    $ivr_manager->remove_appointment( $db_participant );

    // if the owner is a participant then update their queue status
    $db_participant->update_queue_status();
  }
}
