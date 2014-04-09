<?php
/**
 * call_history.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget call history report
 */
class call_history_report extends base_report
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'call_history', $args );
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

    $this->add_parameter( 'interview_completed', 'enum', 'Interview Complete',
      'Whether to restrict the report to interviews that are complete or incomplete.' );
    $this->add_restriction( 'site' );
    $this->add_restriction( 'source' );
    $this->add_restriction( 'dates' );

    $this->set_variable( 'description',
      'This report chronologically lists assignment call attempts.  The report includes the '.
      "participant's UID, operator's name, date of the assignment, result, start and end time ".
      'of each call.' );
  }

  /**
   * Sets up the operation with any pre-execution instructions that may be necessary.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();

    $complete_list = array( 'Either', 'Yes', 'No' );
    $complete_list = array_combine( $complete_list, $complete_list );

    $this->set_parameter( 'interview_completed', key( $complete_list ), true, $complete_list );
  }
}
