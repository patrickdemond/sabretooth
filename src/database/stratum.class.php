<?php
/**
 * stratum.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * stratum: record
 */
class stratum extends \cenozo\database\stratum
{
  /**
   * Extend parent method
   */
  public function add_qnaire( $ids )
  {
    parent::add_qnaire( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }

  /**
   * Extend parent method
   */
  public function remove_qnaire( $ids )
  {
    parent::remove_qnaire( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }

  /**
   * Extend parent method
   */
  public function replace_qnaire( $ids )
  {
    parent::replace_qnaire( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }
}
