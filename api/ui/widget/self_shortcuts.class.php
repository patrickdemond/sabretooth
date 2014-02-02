<?php
/**
 * self_shortcuts.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget self shortcuts
 */
class self_shortcuts extends \cenozo\ui\widget\self_shortcuts
{
  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();
    
    $setting_manager = lib::create( 'business\setting_manager' );
    $survey_manager = lib::create( 'business\survey_manager' );
    $voip_manager = lib::create( 'business\voip_manager' );
    $session = lib::create( 'business\session' );
    $db_site = $session->get_site();

    $voip_enabled = $setting_manager->get_setting( 'voip', 'enabled' );
    $is_operator = 'operator' == $session->get_role()->name;
    
    // get the xor key and make sure it is at least as long as the password
    $xor_key = $setting_manager->get_setting( 'voip', 'xor_key' );
    $password = $_SERVER['PHP_AUTH_PW'];

    // avoid infinite loops by using a counter
    $counter = 0;
    while( strlen( $xor_key ) < strlen( $password ) )
    {
      $xor_key .= $xor_key;
      if( 1000 < $counter++ ) break;
    }
    
    $this->set_variable( 'webphone_parameters', sprintf(
      'username=%s&password=%s',
      $_SERVER['PHP_AUTH_USER'],
      base64_encode( $password ^ $xor_key ) ) );
    $this->set_variable( 'webphone',
      $voip_enabled && !$voip_manager->get_sip_enabled() );
    $this->set_variable( 'dialpad', !is_null( $voip_manager->get_call() ) );

    $this->set_variable( 'participant_id', 0 );
    $this->set_variable( 'prerecruit', false );
    $this->set_variable( 'timer', false );

    if( $is_operator )
    {
      $db_assignment = $session->get_current_assignment();
      $this->set_variable( 'participant_id',
        is_null( $db_assignment ) ? 0 : $db_assignment->get_interview()->get_participant()->id );

      if( !is_null( $session->get_current_phone_call() ) )
      {
        $this->set_variable(
          'prerecruit', $setting_manager->get_setting( 'prerecruit', 'enabled' ) );
        $this->set_variable( 'timer', true );
      }
    }
    else
    {
      $this->set_variable( 'timer', $survey_manager->get_survey_url() );
    }
      
    $this->set_variable( 'calculator', true );
    $this->set_variable( 'timezone_calculator', true );
    $this->set_variable( 'navigation',
      !$is_operator &&
      !$survey_manager->get_survey_url() );
  }
}
