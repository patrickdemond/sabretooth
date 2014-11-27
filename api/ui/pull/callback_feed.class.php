<?php
/**
 * callback_feed.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * pull: callback feed
 */
class callback_feed extends \cenozo\ui\pull\base_feed
{
  /**
   * Constructor
   * 
   * Defines all variables required by the callback feed.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'callback', $args );
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

    // create a list of callbacks between the feed's start and end time
    $modifier = lib::create( 'database\modifier' );
    $callback_mod->join(
      'participant_site', 'callback.participant_id', 'participant_site.participant_id' );
    $modifier->where(
      'participant_site.site_id', '=', lib::create( 'business\session' )->get_site()->id );
    $modifier->where( 'datetime', '>=', $this->start_datetime );
    $modifier->where( 'datetime', '<', $this->end_datetime );

    $this->data = array();
    $callback_class_name = lib::get_class_name( 'database\callback' );
    $setting_manager = lib::create( 'business\setting_manager');
    foreach( $callback_class_name::select( $modifier ) as $db_callback )
    {
      $datetime_obj = util::get_datetime_object( $db_callback->datetime );

      $db_participant = $db_callback->get_participant();
      $this->data[] = array(
        'id' => $db_callback->id,
        'title' => is_null( $db_participant->uid ) || 0 == strlen( $db_participant->uid ) ?
          $db_participant->first_name.' '.$db_participant->last_name :
          $db_participant->uid,
        'allDay' => false,
        'start' => $datetime_obj->format( \DateTime::ISO8601 ) );
    }
  }
}
