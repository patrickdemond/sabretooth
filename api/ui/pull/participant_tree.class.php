<?php
/**
 * participant_tree.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * pull participant tree
 */
class participant_tree extends \cenozo\ui\pull
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the pull
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'participant', 'tree', $args );
  }

  /**
   * This method executes the operation's purpose.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function execute()
  {
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );

    parent::execute();

    $session = lib::create( 'business\session' );
    $db = $session->get_database();
    $all_sites = $session->get_role()->all_sites;
    
    if( $all_sites )
    {
      $site_id = $this->get_argument( "site_id", 0 );
      $db_site = $site_id ? lib::create( 'database\site', $site_id ) : NULL;
    }

    $restrict_language_id = $this->get_argument( 'restrict_language_id', 'any' );

    $current_date = util::get_datetime_object()->format( 'Y-m-d' );
    $viewing_date = $this->get_argument( 'viewing_date', 'current' );
    if( $current_date == $viewing_date ) $viewing_date = 'current';

    // set the viewing date if it is not "current"
    if( 'current' != $viewing_date ) $queue_class_name::set_viewing_date( $viewing_date );

    // get the participant count for every node in the tree
    $this->data = array();
    foreach( $queue_class_name::select() as $db_queue )
    {
      // restrict by language
      // Note: a new queue mod needs to be created for every iteration of the loop
      $queue_mod = lib::create( 'database\modifier' );
      if( 'any' != $restrict_language_id )
      {
        $column = sprintf( 'IFNULL( participant.language_id, %s )',
                           $db->format_string(
                             $session->get_service()->language_id ) );
        $queue_mod->where( $column, '=', $restrict_language_id );
      }

      // restrict queue based on user's role
      if( !$all_sites ) $db_queue->set_site( $session->get_site() );
      else if( !is_null( $db_site ) ) $db_queue->set_site( $db_site );
      
      // handle queues which are not qnaire specific
      if( !$db_queue->qnaire_specific )
      {
        $index = sprintf( '%d_%d', 0, $db_queue->id );
        $this->data[$index] = $db_queue->get_participant_count( $queue_mod );
      }
      else // handle queues which are qnaire specific
      {
        foreach( $qnaire_class_name::select() as $db_qnaire )
        {
          $modifier = clone $queue_mod;
          $modifier->where( 'qnaire_id', '=', $db_qnaire->id );
          $index = sprintf( '%d_%d', $db_qnaire->id, $db_queue->id );
          $this->data[$index] = $db_queue->get_participant_count( $modifier );
        }
      }
    }
  }

  /**
   * Sets the data type.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_data_type() { return "json"; }
}
