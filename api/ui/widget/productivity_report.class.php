<?php
/**
 * productivity_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget productivity report
 */
class productivity_report extends base_report
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
    parent::__construct( 'productivity', $args );
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

    $this->add_restriction( 'site' );
    $this->add_restriction( 'phase' );
    $this->add_restriction( 'dates' );
    
    $this->set_variable( 'description',
      'This report lists operator productivity.  The report includes the number of completed interviews, '.
      'the total time spent on completed interviews and the number of completes per hour.  Note that '.
      'time spent on incomplete interviews is not included in this report.  If start or end dates are '.
      'provided then it will restrict the report to those interviews which were completed between the '.
      'given dates.' );
  }
}
