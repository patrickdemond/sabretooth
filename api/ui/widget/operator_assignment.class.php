<?php
/**
 * operator_assignment.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget operator assignment
 */
class operator_assignment extends \cenozo\ui\widget
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
    parent::__construct( 'operator', 'assignment', $args );
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

    $this->set_heading( 'Current Assignment' );

    // see if this user has an open assignment
    $db_current_assignment = lib::create( 'business\session' )->get_current_assignment();
    if( is_null( $db_current_assignment ) )
    {
      // create the system message show sub-widget
      $this->system_message_show = lib::create( 'ui\widget\system_message_show', $this->arguments );
      $this->system_message_show->set_parent( $this );
      $this->system_message_show->set_heading( 'System Messages' );
    }
  }

  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();
    
    $session = lib::create( 'business\session' );

    // see if this user has an open assignment
    $db_current_assignment = $session->get_current_assignment();
    if( is_null( $db_current_assignment ) )
    {
      // determine whether the operator is on a break
      $away_time_mod = lib::create( 'database\modifier' );
      $away_time_mod->where( 'end_datetime', '=', NULL );
      $this->set_variable( 'on_break',
        0 < $session->get_user()->get_away_time_count( $away_time_mod ) );

      try
      {
        $this->system_message_show->process();
        $this->set_variable( 'system_message_show', $this->system_message_show->get_variables() );
      }
      catch( \cenozo\exception\permission $e ) {}

    }
    else
    { // fill out the participant's details
      $phone_call_class_name = lib::get_class_name( 'database\phone_call' );
      $appointment_class_name = lib::get_class_name( 'database\appointment' );
      $callback_class_name = lib::get_class_name( 'database\callback' );
      $operation_class_name = lib::get_class_name( 'database\operation' );

      $setting_manager = lib::create( 'business\setting_manager' );
      $db_interview = $db_current_assignment->get_interview();
      $db_qnaire = $db_interview->get_qnaire();
      $db_participant = $db_interview->get_participant();
      $db_current_phone_call = $session->get_current_phone_call();
      $current_sid = lib::create( 'business\survey_manager' )->get_current_sid();
      $db_language = $db_participant->get_language();

      $db_last_consent = $db_participant->get_last_consent();
      $withdrawing = !is_null( $db_last_consent ) && false == $db_last_consent->accept;
      $proxying = array_key_exists( 'proxying_participant', $_COOKIE );
      
      $previous_call_list = array();
      $db_last_assignment = $db_participant->get_last_finished_assignment();
      if( !is_null( $db_last_assignment ) )
      {
        foreach( $db_last_assignment->get_phone_call_list() as $db_phone_call )
        {
          $db_phone = $db_phone_call->get_phone();
          $previous_call_list[] = sprintf( 'Called phone #%d (%s): %s',
            $db_phone->rank,
            $db_phone->type,
            $db_phone_call->status ? $db_phone_call->status : 'unknown' );
        }
      }

      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'active', '=', true );
      $modifier->order( 'rank' );
      $db_phone_list = $db_participant->get_phone_list( $modifier );
      
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'end_datetime', '!=', NULL );
      $current_calls = $db_current_assignment->get_phone_call_count( $modifier );
      $on_call = !is_null( $db_current_phone_call );

      if( 0 == count( $db_phone_list ) && 0 == $current_calls )
      {
        log::crit(
          sprintf( 'An operator has been assigned participant %d who has no callable phone numbers',
          $db_participant->id ) );
      }
      else
      {
        $phone_list = array();
        foreach( $db_phone_list as $db_phone )
          $phone_list[$db_phone->id] =
            sprintf( '%d. %s (%s)', $db_phone->rank, $db_phone->type, $db_phone->number );
        $this->set_variable( 'phone_list', $phone_list );
        $this->set_variable( 'status_list', $phone_call_class_name::get_enum_values( 'status' ) );
      }

      if( 0 == $current_calls && !$on_call && $db_interview->completed )
      {
        log::crit(
          sprintf( 'An operator has been assigned participant %d who\'s interview is complete '.
                   'but the operator has not made any calls.',
                   $db_participant->id ) );
      }

      // create the interview method list
      $interview_method_list = array();
      $modifier = lib::create( 'database\modifier' );
      $modifier->order( 'interview_method.name' );
      foreach( $db_qnaire->get_interview_method_list( $modifier ) as $db_interview_method )
        $interview_method_list[$db_interview_method->id] = $db_interview_method->name;

      $this->set_variable( 'assignment_id', $db_current_assignment->id );
      $this->set_variable( 'participant_id', $db_participant->id );
      $this->set_variable( 'interview_id', $db_interview->id );
      $this->set_variable( 'participant_note_count', $db_participant->get_note_count() );
      $this->set_variable( 'participant_name', $db_participant->get_full_name() );
      $this->set_variable( 'participant_uid', $db_participant->uid );
      $this->set_variable( 'interview_method_id', $db_interview->interview_method_id );
      $this->set_variable( 'interview_method_list', $interview_method_list );
      $this->set_variable( 'participant_language',
        is_null( $db_language ) ? 'none' : $db_language->name );
      $this->set_variable( 'current_consent',
        is_null( $db_last_consent ) ? 'none' : $db_last_consent->to_string() );
      $this->set_variable( 'withdrawing', $withdrawing );
      $this->set_variable( 'allow_withdraw', !is_null( $db_qnaire->withdraw_sid ) );
      $this->set_variable( 'proxying', $proxying );
      $this->set_variable( 'allow_proxy', !is_null( $setting_manager->get_setting( 'general', 'proxy_survey' ) ) );
      $this->set_variable( 'survey_complete', !$current_sid );
      
      // determine whether we want to show a warning before ending a call
      $warn_before_ending_call = false;
      if( $setting_manager->get_setting( 'calling', 'end call warning' ) && $current_sid )
      {
        $warn_before_ending_call = true;
        if( !$withdrawing && !$proxying )
        { // if we're not withdrawing or proxying then make sure we're not on a repeating survey
          $phase_mod = lib::create( 'database\modifier' );
          $phase_mod->where( 'sid', '=', $current_sid );
          $phase_mod->order( 'rank' );
          $phase_mod->limit( 1 );
          $db_phase = current( $db_qnaire->get_phase_list( $phase_mod ) );
          if( !$db_phase || $db_phase->repeated ) $warn_before_ending_call = false;
        }
      }
      $this->set_variable( 'warn_before_ending_call', $warn_before_ending_call );
      
      // set the appointment and callback variables
      $this->set_variable( 'appointment', false );
      $this->set_variable( 'callback', false );
      $this->set_variable( 'phone_id', false );

      // get the appointment associated with this assignment, if any
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'assignment_id', '=', $db_current_assignment->id );
      $appointment_list = $appointment_class_name::select( $modifier );
      $db_appointment = 0 == count( $appointment_list ) ? NULL : $appointment_list[0];

      // get the callback associated with this assignment, if any
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'assignment_id', '=', $db_current_assignment->id );
      $callback_list = $callback_class_name::select( $modifier );
      $db_callback = 0 == count( $callback_list ) ? NULL : $callback_list[0];
      
      if( !is_null( $db_appointment ) )
      {
        // Determine whether the appointment was missed by calling get_state( true )
        // The 'true' argument ignores the fact that the appointment is currently assigned to
        // the operator.
        if( 'missed' == $db_appointment->get_state( true ) )
        {
          $this->set_variable( 'appointment_missed', true );
          $this->set_variable( 'appointment',
            util::get_formatted_date( $db_appointment->datetime ).' at '.
            util::get_formatted_time( $db_appointment->datetime, false ) );
        }
        else
        {
          $this->set_variable( 'appointment_missed', false );
          $this->set_variable( 'appointment',
            util::get_formatted_time( $db_appointment->datetime, false ) );
        }

        if( !is_null( $db_appointment->phone_id ) )
        {
          $db_phone = lib::create( 'database\phone', $db_appointment->phone_id );
          $this->set_variable( 'phone_id', $db_appointment->phone_id );
          $this->set_variable( 'phone_at',
            sprintf( '%d. %s (%s)', $db_phone->rank, $db_phone->type, $db_phone->number ) );
        }
        else
        {
          $this->set_variable( 'phone_id', false );
          $this->set_variable( 'phone_at', false );
        }
      }
      else if( !is_null( $db_callback ) )
      {
        $this->set_variable( 'callback',
          util::get_formatted_time( $db_callback->datetime, false ) );

        if( !is_null( $db_callback->phone_id ) )
        {
          $db_phone = lib::create( 'database\phone', $db_callback->phone_id );
          $this->set_variable( 'phone_id', $db_callback->phone_id );
          $this->set_variable( 'phone_at',
            sprintf( '%d. %s (%s)', $db_phone->rank, $db_phone->type, $db_phone->number ) );
        }
        else
        {
          $this->set_variable( 'phone_id', false );
          $this->set_variable( 'phone_at', false );
        }
      }

      if( !is_null( $db_last_assignment ) )
      {
        $this->set_variable( 'previous_assignment_id', $db_last_assignment->id );
        $this->set_variable( 'previous_assignment_date',
          util::get_formatted_date( $db_last_assignment->start_datetime ) );
        $this->set_variable( 'previous_assignment_time',
          util::get_formatted_time( $db_last_assignment->start_datetime ) );
      }
      $this->set_variable( 'previous_call_list', $previous_call_list );
      $this->set_variable( 'interview_completed', $db_interview->completed );
      $this->set_variable( 'allow_call', $session->get_allow_call() );
      $this->set_variable( 'on_call', $on_call );
      if( !is_null( $db_current_phone_call ) )
      {
        $note = $db_current_phone_call->get_phone()->note;
        $this->set_variable( 'phone_note', is_null( $note ) ? false : $note );
      }
      else $this->set_variable( 'phone_note', false );

      // only allow an assignment to be ended if the operator is not in a call and
      // they have made at least one call or the interview is completed
      $this->set_variable( 'allow_end_assignment',
        !$on_call && ( 0 < $current_calls || $db_interview->completed ) );
    }
  }
}
