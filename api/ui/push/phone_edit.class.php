<?php
/**
 * phone_edit.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: phone edit
 *
 * Create a edit phone.
 */
class phone_edit extends \cenozo\ui\push\phone_edit
{
  /**
   * This method executes the operation's purpose.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function execute()
  {
    parent::execute();

    // if the owner is a participant then update their queue status
    $db_participant = $this->get_record()->get_person()->get_participant();
    if( !is_null( $db_participant ) ) $db_participant->update_queue_status();
  }
}
