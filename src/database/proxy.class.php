<?php
/**
 * proxy.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * proxy: record
 */
class proxy extends \cenozo\database\proxy
{
  /**
   * Override the parent method
   */
  public function save()
  {
    // if we changed certain columns then update the queue
    $update_queue = $this->has_column_changed( array( 'proxy_type_id', 'datetime' ) );
    parent::save();
    if( $update_queue ) $this->get_participant()->repopulate_queue( true );
  }

  /**
   * Override the parent method
   */
  public function delete()
  {
    $db_participant = $this->get_participant();
    parent::delete();
    $db_participant->repopulate_queue( true );
  }
}
