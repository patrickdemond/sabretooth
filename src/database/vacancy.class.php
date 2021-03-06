<?php
/**
 * vacancy.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * vacancy: record
 */
class vacancy extends \cenozo\database\record
{
  /**
   * Used by the appointment manager to determine the existing and missing vacancies for an appointment
   */
  public static function get_vacancy_lists( $db_site, $start_datetime, $duration, &$existing_list, &$missing_list )
  {
    $vacancy_size = lib::create( 'business\setting_manager' )->get_setting( 'general', 'vacancy_size' );

    // populate the list of vacancies and missing vacancies
    $datetime = clone $start_datetime;
    $end_datetime = clone $start_datetime;
    $end_datetime->add( new \DateInterval( sprintf( 'PT%dM', $duration ) ) );
    while( $datetime < $end_datetime )
    {
      $db_vacancy = static::get_unique_record(
        array( 'site_id', 'datetime' ),
        array( $db_site->id, $datetime->format( 'Y-m-d H:i:s' ) ) 
      );

      if( is_null( $db_vacancy ) ) 
      {
        $db_vacancy = lib::create( 'database\vacancy' );
        $db_vacancy->site_id = $db_site->id;
        $db_vacancy->datetime = $datetime;
        $db_vacancy->operators = 0;

        $missing_list[] = $db_vacancy;
      }
      else $existing_list[] = $db_vacancy;

      $datetime->add( new \DateInterval( sprintf( 'PT%dM', $vacancy_size ) ) );
    }
  }

  /**
   * Removes defunct vacancies
   */
  public static function remove_defunct()
  {
    $sql =
      "DELETE FROM vacancy\n".
      "WHERE operators = 0\n".
      "AND id NOT IN( SELECT DISTINCT vacancy_id FROM appointment_has_vacancy )";
    static::db()->execute( $sql );
  }
}
