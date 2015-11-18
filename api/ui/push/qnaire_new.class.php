<?php
/**
 * qnaire_new.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: qnaire new
 *
 * Create a new qnaire.
 */
class qnaire_new extends \cenozo\ui\push\base_new
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'qnaire', $args );
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

    // make sure the name column isn't blank
    $columns = $this->get_argument( 'columns' );
    if( !array_key_exists( 'name', $columns ) || 0 == strlen( $columns['name'] ) )
      throw lib::create( 'exception\notice',
        'The questionnaire\'s name cannot be left blank.', __METHOD__ );
  }

  protected function setup()
  {
    parent::setup();

    $columns = $this->get_argument( 'columns', array() );
    $record = $this->get_record();

    $db_first_attempt_event_type = lib::create( 'database\event_type' );
    $db_first_attempt_event_type->name = sprintf( 'first attempt (%s)', $columns['name'] );
    $db_first_attempt_event_type->description =
      sprintf( 'First attempt to contact (for the %s interview).', $columns['name'] );
    $db_first_attempt_event_type->save();

    $db_reached_event_type = lib::create( 'database\event_type' );
    $db_reached_event_type->name = sprintf( 'reached (%s)', $columns['name'] );
    $db_reached_event_type->description =
      sprintf( 'The participant was first reached (for the %s interview).', $columns['name'] );
    $db_reached_event_type->save();

    $db_completed_event_type = lib::create( 'database\event_type' );
    $db_completed_event_type->name = sprintf( 'completed (%s)', $columns['name'] );
    $db_completed_event_type->description =
      sprintf( 'Interview completed (for the %s interview).', $columns['name'] );
    $db_completed_event_type->save();

    $record->first_attempt_event_type_id = $db_first_attempt_event_type->id;
    $record->reached_event_type_id = $db_reached_event_type->id;
    $record->completed_event_type_id = $db_completed_event_type->id;
  }

  /**
   * Finishes the operation with any post-execution instructions that may be necessary.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function finish()
  {
    parent::finish();

    // add default interview method to the qnaire
    $this->get_record()->add_interview_method(
      array( $this->get_record()->default_interview_method_id ) );
  }
}
