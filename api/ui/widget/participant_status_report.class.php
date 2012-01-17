<?php
/**
 * participant_status_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget self status
 * 
 * @package sabretooth\ui
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

    $this->add_restriction( 'qnaire' );
    $this->add_restriction( 'site_or_province' );

    $this->set_variable( 'description',
      'This report provides totals of various status types.  In future, reports for a single '.
      'day or date range may be generated.  Populations are broken down by province or by site '.
      'and various call, participant and consent statuses.' );
  }

  /**
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish()
  {
    parent::finish();
    $this->finish_setting_parameters();
  }
}
?>
