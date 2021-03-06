<?php
/**
 * appointment_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace sabretooth\business;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * A manager to help process appointment vacancy lists
 */
class appointment_manager extends \cenozo\base_object
{
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->db_site = lib::create( 'business\session' )->get_site();
  }

  /**
   * Sets the datetime and duration of the appointment
   */
  public function set_datetime_and_duration( $datetime, $duration )
  {
    $this->datetime = $datetime;
    $this->duration = $duration;
  }

  /**
   * Set the appointment's site (if not set then the session's site is used)
   */
  public function set_site( $db_site )
  {
    $this->db_site = $db_site;
  }

  /**
   * Sets the appointment to manipulate
   */
  public function set_appointment( $db_appointment )
  {
    $this->db_appointment = $db_appointment;
  }

  /**
   * Determines whether there is missing vacancy for the appointment datetime and duration
   */
  public function has_missing_vacancy()
  {
    // make sure the vacancy list has been determined
    $this->determine_vacancy_lists();

    // get a list of the appointment's existing vacancies
    $existing_vacancy_id_list = array();
    if( !is_null( $this->db_appointment ) )
    {
      $vacancy_sel = lib::create( 'database\select' );
      $vacancy_sel->add_column( 'id' );
      foreach( $this->db_appointment->get_vacancy_list( $vacancy_sel ) as $vacancy )
        $existing_vacancy_id_list[] = $vacancy['id'];
    }

    // check each vacancy to see if it is already in use by this appointment or still has vacancy left
    $non_vacant = function( $db_vacancy ) use( $existing_vacancy_id_list ) {
      return $db_vacancy->operators <= $db_vacancy->appointments &&
             !in_array( $db_vacancy->id, $existing_vacancy_id_list );
    };

    return 0 < count( $this->missing_vacancy_list ) ||
           0 < count( array_filter( $this->vacancy_list, $non_vacant ) );
  }

  /**
   * Adds the appropriate vacancy records to the appointment
   */
  public function apply_vacancy_list()
  {
    $vacancy_class_name = lib::get_class_name( 'database\vacancy' );

    // make sure the vacancy list has been determined
    $this->determine_vacancy_lists();

    // remove any existing vacancy records from the appointment
    $this->db_appointment->remove_vacancy( NULL );

    // create any missing vacancies
    foreach( $this->missing_vacancy_list as $db_vacancy ) $db_vacancy->save();

    // get a list of all vacancy ids and add them to the appointment
    $get_id = function( $db_vacancy ) { return $db_vacancy->id; };
    $this->db_appointment->add_vacancy( array_merge(
      array_map( $get_id, $this->vacancy_list ),
      array_map( $get_id, $this->missing_vacancy_list )
    ) );

    $vacancy_class_name::remove_defunct();
  }

  /**
   * Releases the manager's semaphore
   */
  public function release()
  {
    if( !is_null( $this->semaphore ) ) $this->semaphore->release();
  }

  /**
   * Create a list of all existing and missing vacancies this appointment will have
   */
  public function determine_vacancy_lists()
  {
    $vacancy_class_name = lib::get_class_name( 'database\vacancy' );

    if( is_null( $this->vacancy_list ) && is_null( $this->missing_vacancy_list ) )
    {
      $this->semaphore = lib::create( 'business\semaphore' );
      $this->semaphore->acquire();

      $this->vacancy_list = array();
      $this->missing_vacancy_list = array();
      // create a list of all existing and missing vacancies this appointment will have
      $vacancy_class_name::get_vacancy_lists(
        $this->db_site,
        $this->datetime,
        $this->duration,
        $this->vacancy_list,
        $this->missing_vacancy_list
      );
    }
  }

  /**
   * The appointment's datetime
   */
  protected $datetime = NULL;

  /**
   * The appointment's duration
   */
  protected $duration = NULL;

  /**
   * The appointment to affect
   */
  protected $db_appointment = NULL;

  /**
   * The appointment's site
   */
  protected $db_site = NULL;

  /**
   * The vacancy list belonging to the appointment
   */
  protected $vacancy_list = NULL;

  /**
   * The missing vacancies required for the appointment
   */
  protected $missing_vacancy_list = NULL;

  /**
   * The semaphore used by the manager
   */
  protected $semaphore = NULL;
}
