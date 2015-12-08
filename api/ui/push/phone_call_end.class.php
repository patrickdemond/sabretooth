<?php
/**
 * phone_call_end.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\push;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * push: phone_call end
 *
 * Assigns a participant to an phone_call.
 */
class phone_call_end extends \cenozo\ui\push
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'phone_call', 'end', $args );
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

    $session = lib::create( 'business\session' );
    $is_operator = 'operator' == $session->get_role()->name;

    // disconnect voip
    $voip_call = lib::create( 'business\voip_manager' )->get_call();
    if( !is_null( $voip_call ) ) $voip_call->hang_up();

    // if this is an operator then process the call result
    if( $is_operator )
    { // set the end time and status of the call
      $db_phone_call = $session->get_current_phone_call();
      if( !is_null( $db_phone_call ) )
      {
        $date_obj = util::get_datetime_object();
        $db_phone_call->end_datetime = $date_obj->format( 'Y-m-d H:i:s' );
        $db_phone_call->status = $this->get_argument( 'status' );
        $db_phone_call->save();

        // if the participant has never had a contact attempt made then mark the event
        $db_interview = $db_phone_call->get_assignment()->get_interview();
        $db_participant = $db_interview->get_participant();
        $db_event_type = $db_interview->get_qnaire()->get_first_attempt_event_type();
        if( !is_null( $db_event_type ) )
        {
          $event_mod = lib::create( 'database\modifier' );
          $event_mod->where( 'event_type_id', '=', $db_event_type->id );
          if( 0 == $db_participant->get_event_count( $event_mod ) )
          {
            $db_event = lib::create( 'database\event' );
            $db_event->participant_id = $db_participant->id;
            $db_event->event_type_id = $db_event_type->id;
            $db_event->datetime = util::get_datetime_object()->format( 'Y-m-d H:i:s' );
            $db_event->save();
          }
        }

        // if the status is "disconnected" or "wrong number" deactivate the phone and make a note
        // that the number has been disconnected
        if( 'disconnected' == $db_phone_call->status ||
            'wrong number' == $db_phone_call->status )
        {
          $db_phone = lib::create( 'database\phone', $db_phone_call->phone_id );
          if( !is_null( $db_phone ) )
          {
            $note = sprintf( 'This phone number has been disabled because a call was made to it '.
                             'on %s at %s '.
                             'by operator id %d (%s) '.
                             'with the result of "%s".',
                             util::get_formatted_date( $db_phone_call->end_datetime ),
                             util::get_formatted_time( $db_phone_call->end_datetime ),
                             $session->get_user()->id,
                             $session->get_user()->name,
                             $db_phone_call->status );

            // keep the old note if there is one
            $note = is_null( $db_phone->note ) ? $note : $db_phone->note."\n\n".$note;

            // apply the change using an operation (so that Mastodon is also updated)
            $args = array(
              'id' => $db_phone->id,
              'columns' => array(
                'active' => false,
                'note' => $note ) );
            $operation = lib::create( 'ui\push\phone_edit', $args );
            $operation->process();
          }
        }
        else if( 'contacted' == $db_phone_call->status )
        { // if the participant has never been reached then add this event
          $db_interview = $db_phone_call->get_assignment()->get_interview();
          $db_participant = $db_interview->get_participant();
          if( !is_null( $db_event_type ) )
          {
            $event_mod = lib::create( 'database\modifier' );
            $event_mod->where( 'event_type_id', '=', $db_event_type->id );
            if( 0 == $db_participant->get_event_count( $event_mod ) )
            {
              $db_event = lib::create( 'database\event' );
              $db_event->participant_id = $db_participant->id;
              $db_event->event_type_id = $db_event_type->id;
              $db_event->datetime = util::get_datetime_object()->format( 'Y-m-d H:i:s' );
              $db_event->save();
            }
          }
        }
      }
    }
  }
}
