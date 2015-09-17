<?php
/**
 * qnaire_add.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget qnaire add
 */
class qnaire_add extends \cenozo\ui\widget\base_view
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'qnaire', 'add', $args );
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
    
    // define all columns defining this record
    $this->add_item( 'name', 'string', 'Name' );
    $this->add_item( 'rank', 'enum', 'Rank' );
    $this->add_item( 'default_interview_method_id', 'enum', 'Default Interview Method' );
    $this->add_item( 'delay', 'number', 'Delay (weeks)',
      'How many weeks after the previous questionnaire or event type is completed '.
      'before this questionnaire may begin.' );
    $this->add_item( 'withdraw_sid', 'enum', 'Withdraw Survey' );
    $this->add_item( 'description', 'text', 'Description' );
  }

  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();

    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $interview_method_class_name = lib::get_class_name( 'database\interview_method' );
    $surveys_class_name = lib::get_class_name( 'database\limesurvey\surveys' );
    
    // create enum arrays
    $num_ranks = $qnaire_class_name::count();
    $ranks = array();
    for( $rank = 1; $rank <= ( $num_ranks + 1 ); $rank++ ) $ranks[] = $rank;
    $ranks = array_combine( $ranks, $ranks );
    end( $ranks );
    $last_rank_key = key( $ranks );
    reset( $ranks );

    $interview_methods = array();
    foreach( $interview_method_class_name::select() as $db_interview_method )
      $interview_methods[$db_interview_method->id] = $db_interview_method->name;

    $surveys = array();
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'active', '=', 'Y' );
    $modifier->where( 'anonymized', '=', 'N' );
    $modifier->where( 'tokenanswerspersistence', '=', 'Y' );
    foreach( $surveys_class_name::select( $modifier ) as $db_survey )
      $surveys[$db_survey->sid] = $db_survey->get_title();

    // set the view's items
    $this->set_item( 'name', '', true );
    $this->set_item( 'rank', $last_rank_key, true, $ranks );
    $this->set_item( 'default_interview_method_id',
      key( $interview_methods ), true, $interview_methods );
    $this->set_item( 'delay', 52, true );
    $this->set_item( 'withdraw_sid', key( $surveys ), true, $surveys );
    $this->set_item( 'description', '' );
  }
}
