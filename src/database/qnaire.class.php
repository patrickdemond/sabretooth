<?php
/**
 * qnaire.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * qnaire: record
 */
class qnaire extends \cenozo\database\has_rank
{
  /**
   * Sets the interview method for a list of identifiers as a single operation
   * @param database\identifier $db_identifier The identifier (NULL if using native UIDs)
   * @param array $identifier_list
   * @param string $method Either "phone" or "web"
   */
  public function mass_set_method( $db_identifier, $identifier_list, $method )
  {
    $pine_qnaire_id = $this->get_script()->pine_qnaire_id;
    if( is_null( $pine_qnaire_id ) )
      throw lib::create( 'exception\runtime', 'Tried to set method for non Pine questionnaire.', __METHOD__ );

    set_time_limit( 900 ); // 15 minutes max

    $participant_class_name = lib::get_class_name( 'database\participant' );
    $interview_class_name = lib::get_class_name( 'database\interview' );
    $cenozo_manager = lib::create( 'business\cenozo_manager', 'pine' );

    if( 'phone' == $method )
    {
      // delete pending emails for all interviews
      $cenozo_manager->post(
        sprintf( 'qnaire/%d/participant', $pine_qnaire_id ),
        array(
          'mode' => 'remove_mail',
          'identifier_id' => is_null( $db_identifier ) ? NULL : $db_identifier->id,
          'identifier_list' => $identifier_list
        )
      );
    }
    else if( 'web' == $method )
    {
      // first make sure that all interviews exist
      $modifier = lib::create( 'database\modifier' );

      if( is_null( $db_identifier ) )
      {
        $modifier->where( 'uid', 'IN', $identifier_list );
      }
      else
      {
        $modifier->join( 'participant_identifier', 'participant.id', 'participant_identifier.participant_id' );
        $modifier->where( 'participant_identifier.identifier_id', '=', $db_identifier->id );
        $modifier->where( 'participant_identifier.value', 'IN', $identifier_list );
      }

      static::db()->execute( sprintf(
        'INSERT INTO interview( qnaire_id, participant_id, method, start_datetime ) '.
        'SELECT %s, participant.id, "web", UTC_TIMESTAMP() '.
        'FROM participant '.
        '%s '.
        'ON DUPLICATE KEY UPDATE method = "web"',
        static::db()->format_string( $this->id ),
        $modifier->get_sql()
      ) );

      // make sure all existing respondents get mail
      $cenozo_manager->post(
        sprintf( 'qnaire/%d/participant', $pine_qnaire_id ),
        array(
          'mode' => 'add_mail',
          'identifier_id' => is_null( $db_identifier ) ? NULL : $db_identifier->id,
          'identifier_list' => $identifier_list
        )
      );

      // and finally, create missing pine respondents
      $cenozo_manager->post(
        sprintf( 'qnaire/%d/participant', $pine_qnaire_id ),
        array(
          'mode' => 'create',
          'identifier_id' => is_null( $db_identifier ) ? NULL : $db_identifier->id,
          'identifier_list' => $identifier_list
        )
      );
    }

    // now set the method column
    $modifier = lib::create( 'database\modifier' );
    $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );

    if( is_null( $db_identifier ) )
    {
      $modifier->where( 'uid', 'IN', $identifier_list );
    }
    else
    {
      $modifier->join( 'participant_identifier', 'participant.id', 'participant_identifier.participant_id' );
      $modifier->where( 'participant_identifier.identifier_id', '=', $db_identifier->id );
      $modifier->where( 'participant_identifier.value', 'IN', $identifier_list );
    }

    static::db()->execute( sprintf(
      "UPDATE interview %s\n".
      "SET method = %s\n".
      'WHERE %s',
      $modifier->get_join(),
      static::db()->format_string( $method ),
      $modifier->get_where()
    ) );
  }

  /**
   * Extend parent method
   */
  public function add_quota( $ids )
  {
    parent::add_quota( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }

  /**
   * Extend parent method
   */
  public function remove_quota( $ids )
  {
    parent::remove_quota( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }

  /**
   * Extend parent method
   */
  public function replace_quota( $ids )
  {
    parent::replace_quota( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }
  /**
   * Extend parent method
   */
  public function add_site( $ids )
  {
    parent::add_site( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }

  /**
   * Extend parent method
   */
  public function remove_site( $ids )
  {
    parent::remove_site( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }

  /**
   * Extend parent method
   */
  public function replace_site( $ids )
  {
    parent::replace_site( $ids );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $queue_class_name::repopulate();
  }
}
