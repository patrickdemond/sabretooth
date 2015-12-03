<?php
/**
 * address_delete.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: address delete
 *
 * Create a delete address.
 */
class address_delete extends \cenozo\ui\push\address_delete
{
  /**
   * This method executes the operation's purpose.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function execute()
  {
    $db_participant = $this->get_record()->get_person()->get_participant();
    parent::execute();

    // if the owner is a participant then update their queue status
    if( !is_null( $db_participant ) ) $db_participant->update_queue_status();
  }
}
