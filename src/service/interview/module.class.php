<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\service\interview;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\interview\module
{
  /**
   * Extend parent method
   */
  public function validate()
  {
    parent::validate();

    if( 300 > $this->get_status()->get_code() )
    {
      $operation = $this->get_argument( 'operation', false );
      if( 'force_complete' == $operation )
      {
        $db_interview = $this->get_resource();

        if( 3 > lib::create( 'business\session' )->get_role()->tier )
        {
          $this->get_status()->set_code( 403 );
        }
        // make sure the interview isn't already complete
        else if( !is_null( $db_interview->end_datetime ) )
        {
          $this->set_data( 'The interview has already been marked as complete.' );
          $this->get_status()->set_code( 409 );
        }
        else
        {
          // only force complete if there are no open assignments
          $modifier = lib::create( 'database\modifier' );
          $modifier->where( 'assignment.end_datetime', '=', NULL );
          if( 0 < $db_interview->get_assignment_count( $modifier ) )
          {
            $this->set_data( 'Interviews cannot be force-closed while there is an open assignment.' );
            $this->get_status()->set_code( 409 );
          }
        }
      }
    }
  }

  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    if( $select->has_column( 'last_participation_consent' ) )
    {
      $join_mod = lib::create( 'database\modifier' );
      $join_mod->where( 'interview.participant_id', '=', 'participant_last_consent.participant_id' );
      $modifier->join(
        'participant_last_consent', 'interview.participant_id', 'participant_last_consent.participant_id' );
      $modifier->join( 'consent_type', 'participant_last_consent.consent_type_id', 'consent_type.id' );
      $modifier->where( 'consent_type.name', '=', 'participation' );
      $modifier->left_join( 'consent', 'participant_last_consent.consent_id', 'consent.id' );
      $select->add_column( 'consent.accept', 'last_participation_consent', false, 'boolean' );
    }

    // count how many future, unassigned appointments the interview has
    if( $select->has_column( 'future_appointment' ) )
    {
      $join_sel = lib::create( 'database\select' );
      $join_sel->from( 'appointment' );
      $join_sel->add_column( 'interview_id' );
      $join_sel->add_column( 'COUNT( * ) > 0', 'future_appointment', false );

      $join_mod = lib::create( 'database\modifier' );
      $join_mod->join( 'vacancy', 'appointment.start_vacancy_id', 'vacancy.id' );
      $join_mod->where( 'vacancy.datetime', '>', 'UTC_TIMESTAMP()', false );
      $join_mod->where( 'assignment_id', '=', NULL );
      $join_mod->group( 'interview_id' );

      $modifier->left_join(
        sprintf( '( %s %s ) AS interview_join_appointment', $join_sel->get_sql(), $join_mod->get_sql() ),
        'interview.id',
        'interview_join_appointment.interview_id' );
      $select->add_column( 'IFNULL( future_appointment, false )', 'future_appointment', false, 'boolean' );
    }

    if( $select->has_column( 'missed_appointment' ) )
    {
      $join_sel = lib::create( 'database\select' );
      $join_sel->from( 'appointment' );
      $join_sel->add_column( 'interview_id' );
      $join_sel->add_column( 'COUNT( * ) > 0', 'missed_appointment', false );

      $join_mod = lib::create( 'database\modifier' );
      $join_mod->join( 'vacancy', 'appointment.start_vacancy_id', 'vacancy.id' );
      $join_mod->where( 'vacancy.datetime', '<', 'UTC_TIMESTAMP()', false );
      $join_mod->where( 'assignment_id', '=', NULL );
      $join_mod->where( 'outcome', '=', NULL );
      $join_mod->group( 'interview_id' );

      $modifier->left_join(
        sprintf( '( %s %s ) AS interview_join_appointment', $join_sel->get_sql(), $join_mod->get_sql() ),
        'interview.id',
        'interview_join_appointment.interview_id' );
      $select->add_column( 'IFNULL( missed_appointment, false )', 'missed_appointment', false, 'boolean' );
    }

    $modifier->join( 'qnaire', 'interview.qnaire_id', 'qnaire.id' );
    if( $select->has_table_columns( 'script' ) )
      $modifier->join( 'script', 'qnaire.script_id', 'script.id' );
  }

  /**
   * Extend parent method
   */
  public function post_write( $record )
  {
    parent::post_write( $record );

    if( 'PATCH' == $this->get_method() )
    {
      if( 'force_complete' == $this->get_argument( 'operation', false ) )
      {
        $record->force_complete();
        $record->get_participant()->repopulate_queue( true );
      }
    }
  }
}
