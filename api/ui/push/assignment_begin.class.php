<?php
/**
 * assignment_begin.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: assignment begin
 *
 * Assigns a participant to an assignment.
 */
class assignment_begin extends \cenozo\ui\push
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'assignment', 'begin', $args );
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

    if( !is_null( lib::create( 'business\session' )->get_current_assignment() ) )
      throw lib::create( 'exception\notice',
        'Please click the refresh button.  If this message appears more than twice '.
        'consecutively report this error to a superior.', __METHOD__ );
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
    
    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $interview_class_name = lib::get_class_name( 'database\interview' );

    $session = lib::create( 'business\session' );
    $db = $session->get_database();
    $setting_manager = lib::create( 'business\setting_manager' );

    // make sure another thread didn't pick up an assignment while waiting
    if( is_null( lib::create( 'business\session' )->get_current_assignment() ) )
    {
      $db_user = $session->get_user();
      $db_site = $session->get_site();

      // determine which way to order the queues
      $reverse_sort_time = $setting_manager->get_setting( 'queue', 'reverse sort time' );
      $now_datetime_obj = util::get_datetime_object();
      $weekday = 6 > $now_datetime_obj->format( 'N' );
      $age_desc = (bool)
        $now_datetime_obj->diff( util::get_datetime_object( $reverse_sort_time ) )->invert;

      // Search through every queue for a new assignment until one is found.
      // This search has to be done one qnaire at a time
      $db_origin_queue = NULL;
      $db_participant = NULL;

      $qnaire_mod = lib::create( 'database\modifier' );
      $qnaire_mod->order( 'rank' );

      $queue_mod = lib::create( 'database\modifier' );
      $queue_mod->where( 'rank', '!=', NULL );
      $queue_mod->order( 'rank' );

      // prepare a language string to be used in the participant mod
      $language_column = sprintf(
        'IFNULL( participant.language_id, %s )',
        $db->format_string( $session->get_service()->language_id ) );
      $user_language_id_list = $db_user->get_language_idlist();
      
      // make sure only one participant is assigned at a time
      $session->acquire_semaphore();

      foreach( $qnaire_class_name::select( $qnaire_mod ) as $db_qnaire )
      {
        foreach( $queue_class_name::select( $queue_mod ) as $db_queue )
        {
          if( $db_queue->get_enabled( $db_site, $db_qnaire ) ) // TODO: in v2.0 put in modifier instead
          {
            $participant_mod = lib::create( 'database\modifier' );
            $participant_mod->where( 'qnaire_id', '=', $db_qnaire->id );
            if( 0 < count( $user_language_id_list ) )
              $participant_mod->where( $language_column, 'IN', $user_language_id_list );

            // on a weekday sort the queue by age, the order defined by the reverse sort time setting
            if( $weekday ) $participant_mod->order(
              'DATEDIFF( DATE( NOW() ), participant.date_of_birth ) < 65 * 365', $age_desc );
            $participant_mod->order( 'participant.source_id' );
            $participant_mod->limit( 1 );

            $db_queue->set_site( $db_site );
            $participant_list = $db_queue->get_participant_list( $participant_mod );
            if( 1 == count( $participant_list ) )
            {
              $db_origin_qnaire = $db_qnaire;
              $db_origin_queue = $db_queue;
              $db_participant = current( $participant_list );
            }
          }

          // stop looping queues if we found a participant
          if( !is_null( $db_participant ) ) break;
        }

        // stop looping qnaires if we found a participant
        if( !is_null( $db_participant ) ) break;
      }

      // if we didn't find a participant then let the user know none are available
      if( is_null( $db_participant ) )
        throw lib::create( 'exception\notice',
          'There are no participants currently available.', __METHOD__ );
      
      // make sure the qnaire has phases
      if( 0 == $db_origin_qnaire->get_phase_count() )
        throw lib::create( 'exception\notice',
          'This participant\'s next questionnaire is not yet ready. '.
          'Please immediately report this problem to a superior.',
          __METHOD__ );
      
      // get this participant's interview or create a new one if none exists yet
      $db_effective_qnaire = $db_participant->get_effective_qnaire();
      if( is_null( $db_effective_qnaire ) )
        throw lib::create( 'exception\runtime',
          sprintf( 'Trying to assign participant %s who has already completed all qnaires.',
                   $db_participant->uid ),
          __METHOD__ );

      $db_interview = $interview_class_name::get_unique_record(
        array( 'qnaire_id', 'participant_id' ),
        array( $db_effective_qnaire->id, $db_participant->id ) );
      
      if( is_null( $db_interview ) )
      {
        $db_interview = lib::create( 'database\interview' );
        $db_interview->participant_id = $db_participant->id;
        $db_interview->qnaire_id = $db_effective_qnaire->id;
        $db_interview->interview_method_id = $db_effective_qnaire->default_interview_method_id;

        // Even though we have made sure this interview isn't a duplicate, it seems to happen from
        // time to time anyway, so catch it and tell the operator to try requesting the assignment
        // again
        try
        {
          $db_interview->save();
        }
        catch( \cenozo\exception\database $e )
        {
          if( $e->is_duplicate_entry() )
          {
            throw lib::create( 'exception\notice',
              'The server was too busy to assign a new participant, please wait a few seconds then '.
              'try requesting an assignment again.  If this message appears several times in a row '.
              'please report the error code to your superior.',
              __METHOD__ );
          }

          throw $e;
        }
      }

      // create an assignment for this user
      $db_assignment = lib::create( 'database\assignment' );
      $db_assignment->user_id = $db_user->id;
      $db_assignment->site_id = $db_site->id;
      $db_assignment->interview_id = $db_interview->id;
      $db_assignment->queue_id = $db_origin_queue->id;
      $db_assignment->save();

      $session->release_semaphore();

      if( $db_origin_queue->from_appointment() )
      { // if this is an appointment queue, mark the appointment now associated with the assignment
        // (this should always be the appointment with the earliest date)
        $appointment_mod = lib::create( 'database\modifier' );
        $appointment_mod->where( 'assignment_id', '=', NULL );
        $appointment_mod->order( 'datetime' );
        $appointment_mod->limit( 1 );
        $appointment_list = $db_participant->get_appointment_list( $appointment_mod );

        if( 0 == count( $appointment_list ) )
        {
          log::crit(
            'Participant queue is from an appointment but no appointment is found.', __METHOD__ );
        }
        else
        {
          $db_appointment = current( $appointment_list );
          $db_appointment->assignment_id = $db_assignment->id;
          $db_appointment->save();
        }
      }
      else if( $db_origin_queue->from_callback() )
      { // if this is a callback queue, mark the callback now associated with the assignment
        // (this should always be the callback with the earliest date)
        $callback_mod = lib::create( 'database\modifier' );
        $callback_mod->where( 'assignment_id', '=', NULL );
        $callback_mod->order( 'datetime' );
        $callback_mod->limit( 1 );
        $callback_list = $db_participant->get_callback_list( $callback_mod );

        if( 0 == count( $callback_list ) )
        {
          log::crit(
            'Participant queue is from a callback but no callback is found.', __METHOD__ );
        }
        else
        {
          $db_callback = current( $callback_list );
          $db_callback->assignment_id = $db_assignment->id;
          $db_callback->save();
        }
      }

      // update this participant's queue status
      $db_participant->update_queue_status();
    }
  }
}
