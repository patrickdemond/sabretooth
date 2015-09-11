<?php
/**
 * proxy_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\business;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * proxy_manager: record
 */
class proxy_manager extends \cenozo\singleton
{
  /**
   * Constructor.
   * 
   * Since this class uses the singleton pattern the constructor is never called directly.  Instead
   * use the {@link singleton} method.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function __construct() {}

  /**
   * Returns the survey id of the proxy script used to proxy this participant
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access public
   */
  public function get_proxy_sid( $db_participant )
  {
    return lib::create( 'business\setting_manager' )->get_setting( 'general', 'proxy_survey' );
  }

  public function generate_token( $db_participant )
  {
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $proxy_sid = $this->get_proxy_sid( $db_participant );
    $tokens_class_name::set_sid( $proxy_sid );

    $token = $db_participant->uid;

    // need to add a postfix to the token; try for an open assignment first
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'interview.participant_id', '=', $db_participant->id );
    $modifier->where( 'assignment.end_datetime', '=', NULL );

    $assignment_list = 
      lib::create( 'business\session' )->get_user()->get_assignment_list( $modifier );
    if( 0 < count( $assignment_list ) ) 
    {
      $db_assignment = current( $assignment_list );
      $postfix = '_'.str_pad( $db_assignment->id, 7, '0', STR_PAD_LEFT );
    }   
    else // create an counter as a postfix
    {   
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'token', 'LIKE', $db_participant->uid.'_NA%' );
      $sub_select = sprintf(
        '( SELECT MAX(tid) FROM %s %s )', $tokens_class_name::get_table_name(), $modifier->get_sql() );

      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'tid', '=', $sub_select, false );
      $last_token = $tokens_class_name::db()->get_one( sprintf(
        'SELECT token FROM %s %s', $tokens_class_name::get_table_name(), $modifier->get_sql() ) );

      $postfix = $last_token ? strstr( $last_token, '_' ) : '_NA00000';
      $postfix++;
    }   

    $token .= $postfix;
    return $token;
  }
}
