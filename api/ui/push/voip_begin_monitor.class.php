<?php
/**
 * voip_begin_monitor.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: voip begin_monitor
 *
 * Changes the current user's theme.
 * Arguments must include 'theme'.
 */
class voip_begin_monitor extends \cenozo\ui\push
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'voip', 'begin_monitor', $args );
  }
  
  /**
   * This method executes the operation's purpose.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function execute()
  {
    parent::execute();

    // get the filename by creating a temporary recording with the current interview/assignment ids
    $token = lib::create( 'business\survey_manager' )->get_current_token();
    $parts = explode( '_', $token );

    // get the highest ranking recording for this interview
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'interview_id', '=', $parts[0] );
    $max_rank = lib::create( 'business\session' )->get_database()->get_one(
      sprintf( 'SELECT MAX( rank ) FROM recording %s', $modifier->get_sql() ) );

    $rank = is_null( $max_rank ) ? 1 : $max_rank + 1;

    $db_recording = lib::create( 'database\recording' );
    $db_recording->interview_id = $parts[0];
    $db_recording->assignment_id = $parts[1];
    $db_recording->rank = $rank;
    $db_recording->save();

    $filename = $db_recording->get_filename();
    lib::create( 'business\voip_manager' )->get_call()->start_monitoring( $filename );
  }
}
