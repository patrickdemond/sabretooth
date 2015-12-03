<?php
/**
 * consent_delete.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: consent delete
 *
 * Create a delete consent.
 */
class consent_delete extends \cenozo\ui\push\consent_delete
{
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
    $db_participant->update_queue_status();
  }
}
