<?php
/**
 * qnaire.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * qnaire: record
 */
class qnaire extends \cenozo\database\has_rank
{
  /**
   * Allow access to default interview method object
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\interview_method
   * @access public
   */
  public function get_default_interview_method()
  {
    return is_null( $this->default_interview_method_id ) ?
      NULL : lib::create( 'database\interview_method', $this->default_interview_method_id );
  }

  /**
   * Returns whether a particular interview method is in use by any qnaire
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\interview_method $db_interview_method
   * @return boolean
   * @access public
   * @static
   */
  public static function is_interview_method_in_use( $db_interview_method )
  {
    $database_class_name = lib::get_class_name( 'database\database' );

    // quick custom sql to determine whether any qnaire is using a particular interview method
    return (bool) static::db()->get_one( sprintf(
      'SELECT 0 < COUNT(*) '.
      'FROM qnaire_has_interview_method '.
      'WHERE interview_method_id = %s',
      $database_class_name::format_string( $db_interview_method->id ) ) );
  }

  /**
   * Returns a special event-type associated with this qnaire
   * 
   * Returns the event-type associated with the first attempt of contacting a participant for this
   * qnaire. If no event-type exists this method will return NULL.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\event_type
   * @access public
   */
  public function get_first_attempt_event_type()
  {
    return is_null( $this->first_attempt_event_type_id ) ?
      NULL : lib::create( 'database\event_type', $this->first_attempt_event_type_id );
  }

  /**
   * Returns a special event-type associated with this qnaire
   * 
   * Returns the event-type associated with the first time a participant is contacted for this
   * qnaire. If no event-type exists this method will return NULL.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\event_type
   * @access public
   */
  public function get_reached_event_type()
  {
    return is_null( $this->reached_event_type_id ) ?
      NULL : lib::create( 'database\event_type', $this->reached_event_type_id );
  }

  /**
   * Returns a special event-type associated with this qnaire
   * 
   * Returns the event-type associated with when this qnaire is completed.  If no event-type exists
   * this method will return NULL.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\event_type
   * @access public
   */
  public function get_completed_event_type()
  {
    return is_null( $this->completed_event_type_id ) ?
      NULL : lib::create( 'database\event_type', $this->completed_event_type_id );
  }
}
