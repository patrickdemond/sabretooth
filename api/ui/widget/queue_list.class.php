<?php
/**
 * queue_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget queue list
 */
class queue_list extends \cenozo\ui\widget\base_list
{
  /**
   * Constructor
   * 
   * Defines all variables required by the queue list.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'queue', $args );
  }

  /**
   * Processes arguments, preparing them for the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\notice
   * @access protected
   */
  protected function prepare()
  {
    parent::prepare();

    $session = lib::create( 'business\session' );
    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );

    // make sure to display all queues on the same page
    $this->set_items_per_page( 1000 );

    // if there is only one qnaire then restrict to it automatically
    $restrict_qnaire_id = $this->get_argument( 'restrict_qnaire_id', false );
    if( false === $restrict_qnaire_id && 1 == $qnaire_class_name::count() )
    {
      $widget_name = $this->get_full_name();
      $qnaire_list = $qnaire_class_name::select();
      $db_qnaire = current( $qnaire_list );
      $args = array_key_exists( $widget_name, $this->arguments )
            ? $this->arguments[$widget_name]
            : array();
      $args['restrict_qnaire_id'] = $db_qnaire->id;
      $this->arguments[$widget_name] = $args;
    }

    $this->add_column( 'rank', 'number', 'Rank', true );
    if( ( !$session->get_role()->all_sites ||
          $this->get_argument( 'restrict_site_id', false ) ) &&
        $this->get_argument( 'restrict_qnaire_id', false ) )
      $this->add_column( 'enabled', 'boolean', 'Enabled', false );
    $this->add_column( 'participant_count', 'number', 'Participants', false );
    $this->add_column( 'description', 'text', 'Description', true, true, 'left' );
    if( !$session->get_role()->all_sites )
      $this->set_heading(
        sprintf( '%s %s for %s',
                 $this->get_subject(),
                 $this->get_name(),
                 $session->get_site()->name ) );
  }

  /**
   * Sets up the operation with any pre-execution instructions that may be necessary.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();

    $site_class_name = lib::get_class_name( 'database\site' );
    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $language_class_name = lib::get_class_name( 'database\language' );

    $session = lib::create( 'business\session' );
    $db = $session->get_database();

    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $this->set_variable( 'restrict_site_id', $restrict_site_id );
    $db_restrict_site = $restrict_site_id
                      ? lib::create( 'database\site', $restrict_site_id )
                      : NULL;

    $all_sites = $session->get_role()->all_sites;
    if( $all_sites )
    {
      $sites = array();
      $site_mod = lib::create( 'database\modifier' );
      $site_mod->order( 'name' );
      foreach( $site_class_name::select( $site_mod ) as $db_site )
        $sites[$db_site->id] = $db_site->name;
      $this->set_variable( 'sites', $sites );
    }
    else
    {
      $db_restrict_site = $session->get_site();
    }

    $qnaires = array();
    foreach( $qnaire_class_name::select() as $db_qnaire )
      $qnaires[$db_qnaire->id] = $db_qnaire->name;
    $this->set_variable( 'qnaires', $qnaires );

    $restrict_qnaire_id = $this->get_argument( 'restrict_qnaire_id', 0 );
    $this->set_variable( 'restrict_qnaire_id', $restrict_qnaire_id );
    $db_restrict_qnaire = $restrict_qnaire_id
                        ? lib::create( 'database\qnaire', $restrict_qnaire_id )
                        : NULL;

    $language_mod = lib::create( 'database\modifier' );
    $language_mod->where( 'active', '=', true );
    $languages = array( 'any' => 'any' );
    foreach( $language_class_name::select( $language_mod ) as $db_language )
      $languages[$db_language->id] = $db_language->name;
    $this->set_variable( 'languages', $languages );

    $restrict_language_id = $this->get_argument( 'restrict_language_id', 'any' );
    $this->set_variable( 'restrict_language_id', $restrict_language_id );

    $current_date = util::get_datetime_object()->format( 'Y-m-d' );
    $this->set_variable( 'current_date', $current_date );
    $viewing_date = $this->get_argument( 'viewing_date', 'current' );
    if( $current_date == $viewing_date ) $viewing_date = 'current';
    $this->set_variable( 'viewing_date', $viewing_date );

    // set the viewing date if it is not "current"
    if( 'current' != $viewing_date ) $queue_class_name::set_viewing_date( $viewing_date );

    $setting_manager = lib::create( 'business\setting_manager' );
    foreach( $this->get_record_list() as $record )
    {
      // restrict queue based on user's role
      if( !is_null( $db_restrict_site ) ) $record->set_site( $db_restrict_site );

      // restrict to the current qnaire
      $modifier = lib::create( 'database\modifier' );
      if( !is_null( $db_restrict_qnaire ) )
        $modifier->where( 'qnaire_id', '=', $db_restrict_qnaire->id );

      // restrict by language
      if( 'any' != $restrict_language_id )
      {
        $column = sprintf( 'IFNULL( participant.language_id, %s )',
                           $db->format_string(
                             $session->get_service()->language_id ) );
        $modifier->where( $column, '=', $restrict_language_id );
      }

      $this->add_row( $record->id,
        array( 'rank' => $record->rank,
               'enabled' => is_null( $db_restrict_site ) || is_null( $db_restrict_qnaire )
                          ? false
                          : $record->get_enabled( $db_restrict_site, $db_restrict_qnaire ),
               'participant_count' => $record->get_participant_count( $modifier ),
               // I hate to put html here, but the alternative is to implement code in the
               // parent class for this ONLY instance of where we need this functionality.
               'description' => '<div class="title">'.$record->title.'</div>'.
                                '<div>'.$record->description.'</div>' ) );
    }
  }

  /**
   * Overrides the parent class method to only include ranked queues
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @access protected
   */
  public function determine_record_count( $modifier = NULL )
  {
    if( NULL == $modifier ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'rank', '!=', NULL );
    $modifier->order( 'rank' );

    return parent::determine_record_count( $modifier );
  }

  /**
   * Overrides the parent class method since the record list depends on the active role.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @access protected
   */
  public function determine_record_list( $modifier = NULL )
  {
    if( NULL == $modifier ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'rank', '!=', NULL );
    $modifier->order( 'rank' );

    return parent::determine_record_list( $modifier );
  }
}
