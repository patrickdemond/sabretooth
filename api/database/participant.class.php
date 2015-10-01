<?php
/**
 * participant.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * participant: record
 */
class participant extends \cenozo\database\participant
{
  /**
   * Updates the participant's queue status.
   * 
   * The participant's entries in the queue_has_participant table are all removed and
   * re-determined.  This method should be called if any of the participant's details
   * which affect which queue they belong in change (eg: change to appointments, consent
   * status, state, etc).
   * WARNING: this operation is db-intensive so it should only be called after all
   * changes to the participant are complete (never more than once per operation).
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function update_queue_status()
  {
    // check the primary key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to update queue status of participant with no id.' );
      return NULL;
    }

    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate( $this );
  }

  /**
   * Get the participant's most recent, closed assignment.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return assignment
   * @access public
   */
  public function get_last_finished_assignment()
  {
    // check the primary key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to query participant with no id.' );
      return NULL;
    }

    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'interview.participant_id', '=', $this->id );
    $modifier->where( 'end_datetime', '!=', NULL );
    $modifier->order_desc( 'start_datetime' );
    $modifier->limit( 1 );
    $assignment_class_name = lib::get_class_name( 'database\assignment' );
    $assignment_list = $assignment_class_name::select( $modifier );

    return 0 == count( $assignment_list ) ? NULL : current( $assignment_list );
  }

  /**
   * Get the participant's current assignment (or null if none is found)
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return assignment
   * @access public
   */
  public function get_current_assignment()
  {
    // check the primary key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to query participant with no id.' );
      return NULL;
    }

    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'interview.participant_id', '=', $this->id );
    $modifier->where( 'end_datetime', '=', NULL );
    $modifier->order_desc( 'start_datetime' );
    $modifier->limit( 1 );
    $assignment_class_name = lib::get_class_name( 'database\assignment' );
    $assignment_list = $assignment_class_name::select( $modifier );

    return 0 == count( $assignment_list ) ? NULL : current( $assignment_list );
  }

  /**
   * Returns the participant's effective qnaire.
   * 
   * The "effective" qnaire is determined based on the participant's current interview.
   * If they have not yet started an interview then the first qnaire is returned.
   * If they current have an incomplete interview then that interview's qnaire is returned.
   * If their current interview is complete then the next qnaire is returned, and if there
   * is no next qnaire then NULL is returned (ie: the participant has completed all qnaires).
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\qnaire
   * @access public
   */
  public function get_effective_qnaire()
  {
    $this->load_queue_data();
    return is_null( $this->effective_qnaire_id ) ?
      NULL : lib::create( 'database\qnaire', $this->effective_qnaire_id );
  }

  /**
   * Returns whether the participant's quota is enabled or not.
   * 
   * This is determined by cross-referencing the participant's quota and their effective qnaire
   * since quotas can be enabled/disabled by qnaire.  If the participant does not belong to any
   * quota then NULL is returned instead.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return boolean
   * @access public
   */
  public function get_quota_enabled()
  {
    $this->load_queue_data();
    $qnaire_mod = lib::create( 'database\modifier' );
    $qnaire_mod->where( 'qnaire_id', '=', $this->effective_qnaire_id );
    $db_quota = $this->get_quota();
    return is_null( $db_quota ) ? NULL : 0 == $db_quota->get_qnaire_count( $qnaire_mod );
  }

  /**
   * Returns the participant's interview method
   * 
   * The interview is either:
   * 1. if the participant hasn't started an interview then the first qnaire's default interview
   *    method
   * 2. if the current interview is not complete then that interview's interview method
   * 3. if all interviews are complete then the last interview's interview method
   * 4. otherwise, the next qnaire's default interview method
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\interview_method
   * @access public
   */
  public function get_effective_interview_method()
  {
    $this->load_queue_data();
    return is_null( $this->effective_interview_method_id ) ?
      NULL : lib::create( 'database\interview_method', $this->effective_interview_method_id );
  }

  /**
   * Returns the participant's qnaire start date.
   * 
   * The qnaire start date is determined based on the following rules:
   * If they have not yet started an interview then the date is based on the first qnaire's
   * delay and the start event needed to start that qnaire, or the current date if there is
   * no start event for the first qnaire.
   * If their current interview is complete then the date is based on the next qnaire's delay
   * and the greatest of its start event and the completed interview's completion date.
   * If there is no next qnaire or the current interview is not complete then NULL is returned,
   * meaning the qnaire has already started (ie: the start date is in the past).
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return datetime
   * @access public
   */
  public function get_start_qnaire_date()
  {
    $this->load_queue_data();
    return is_null( $this->start_qnaire_date ) ?
      NULL : util::get_datetime_object( $this->start_qnaire_date );
  }

  /**
   * Fills in the effective qnaire id, effective interview method id and start qnaire date
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access private
   */
  private function load_queue_data()
  {
    if( $this->queue_data_loaded ) return;

    // check the primary key value
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to query participant with no id.' );
      return NULL;
    }

    // the qnaire date is cached in the queue_has_participant joining table
    $row = static::db()->get_row( sprintf(
      'SELECT * FROM queue_has_participant '.
      'WHERE participant_id = %s '.
      'ORDER BY queue_id DESC '.
      'LIMIT 1',
      static::db()->format_string( $this->id ) ) );

    if( count( $row ) )
    {
      $this->effective_qnaire_id = $row['qnaire_id'];
      $this->effective_interview_method_id = $row['interview_method_id'];
      $this->start_qnaire_date = $row['start_qnaire_date'];
    }

    $this->queue_data_loaded = true;
  }

  /**
   * Whether the participants queue-specific data has been read from the database
   * @var boolean
   * @access private
   */
  private $queue_data_loaded = false;

  /**
   * The participant's current questionnaire id (from a custom query)
   * @var int
   * @access private
   */
  private $effective_qnaire_id = NULL;

  /**
   * The participant's current interview method id (from a custom query)
   * @var int
   * @access private
   */
  private $effective_interview_method_id = NULL;

  /**
   * The date that the current questionnaire is to begin (from a custom query)
   * @var string
   * @access private
   */
  private $start_qnaire_date = NULL;
}
