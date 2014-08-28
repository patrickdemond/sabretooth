<?php
/**
 * participant_status_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget self status
 */
class participant_status_report extends base_report
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
    parent::__construct( 'participant_status', $args );
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

    $this->add_restriction( 'collection' );
    $this->add_restriction( 'cohort' );
    $this->add_restriction( 'qnaire' );
    $this->add_restriction( 'province' );
    $this->add_restriction( 'source' );
    $this->add_parameter( 'breakdown', 'enum', 'Breakdown by' );

    $this->set_variable( 'description',
      'This report provides an overview of participant status throughout the interview process. '.
      'Populations can be broken down by site, province or quota.' );
  }

  /**
   * Sets up the operation with any pre-execution instructions that may be necessary.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();

    $breakdowns = array( 'Site', 'Province', 'Quota' );
    $breakdowns = array_combine( $breakdowns, $breakdowns );
    $this->set_parameter( 'breakdown', 'site', true, $breakdowns );
  }
}
