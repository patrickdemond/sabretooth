<?php
/**
 * participant_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Mailout required report data.
 * 
 * @abstract
 */
class participant_report extends \cenozo\ui\pull\participant_report
{
  /**
   * Processes arguments, preparing them for the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function prepare()
  {
    parent::prepare();

    $db = lib::create( 'business\session' )->get_database();

    // get the report arguments
    $last_call_result = $this->get_argument( 'last_call_result' );
    $qnaire_id = $this->get_argument( 'qnaire_id' );
    $start_date = $this->get_argument( 'qnaire_start_date' );
    $end_date = $this->get_argument( 'qnaire_end_date' );

    if( 'any' != $last_call_result )
    {
      $this->sql_tables .=
        'JOIN participant_last_interview '.
        'ON participant.id = participant_last_interview.participant_id '.
        'JOIN interview_last_assignment '.
        'ON participant_last_interview.interview_id = interview_last_assignment.interview_id '.
        'JOIN assignment_last_phone_call '.
        'ON assignment_last_phone_call.assignment_id = interview_last_assignment.assignment_id '.
        'JOIN phone_call '.
        'ON interview_last_assignment.assignment_id = phone_call.assignment_id ';

      $this->modifier->where(
        'phone_call.status', '=', $last_call_result );
    }

    if( $qnaire_id || $start_date || $end_date )
    {
      $this->sql_tables .=
        'JOIN queue_has_participant '.
        'ON participant.id = queue_has_participant.participant_id '.
        'AND queue_has_participant.start_qnaire_date IS NOT NULL ';

      if( $qnaire_id )
        $this->sql_tables .= sprintf(
          'AND queue_has_participant.qnaire_id = %s ',
          $db->format_string( $qnaire_id ) );

      $this->sql_tables .=
        'JOIN queue ON queue_has_participant.queue_id = queue.id '.
        'AND queue.name = "qnaire" ';

      if( $start_date )
        $this->modifier->where( 'queue_has_participant.start_qnaire_date', '>=', $start_date );
      if( $end_date )
        $this->modifier->where( 'queue_has_participant.start_qnaire_date', '<=', $end_date );
    }
  }
}
