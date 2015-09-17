<?php
/**
 * qnaire_view.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget qnaire view
 */
class qnaire_view extends \cenozo\ui\widget\base_view
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
    parent::__construct( 'qnaire', 'view', $args );
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

    // create an associative array with everything we want to display about the qnaire
    $this->add_item( 'name', 'string', 'Name' );
    $this->add_item( 'rank', 'enum', 'Rank' );
    $this->add_item( 'default_interview_method_id', 'enum', 'Default Interview method' );
    $this->add_item( 'delay', 'number', 'Delay (weeks)',
      'How many weeks after the previous questionnaire or event is completed '.
      'before this questionnaire may begin.' );
    $this->add_item( 'withdraw_sid', 'enum', 'Withdraw Survey' );
    $this->add_item( 'phases', 'constant', 'Number of phases' );
    $this->add_item( 'description', 'text', 'Description' );

    // create the interview_method sub-list widget
    $this->interview_method_list = lib::create( 'ui\widget\interview_method_list', $this->arguments );
    $this->interview_method_list->set_parent( $this );
    $this->interview_method_list->set_heading( 'Available interview methods' );

    // create the phase sub-list widget
    $this->phase_list = lib::create( 'ui\widget\phase_list', $this->arguments );
    $this->phase_list->set_parent( $this );
    $this->phase_list->set_heading( 'Questionnaire phases' );

    // create the source_withdraw sub-list widget
    $this->source_withdraw_list = lib::create( 'ui\widget\source_withdraw_list', $this->arguments );
    $this->source_withdraw_list->set_parent( $this );
    $this->source_withdraw_list->set_heading( 'Source-specific Withdraw Surveys' );

    // create the event_type sub-list widget
    $this->event_type_list = lib::create( 'ui\widget\event_type_list', $this->arguments );
    $this->event_type_list->set_parent( $this );
    $this->event_type_list->set_heading( 'Events required to begin this Questionnaire' );
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

    $record = $this->get_record();

    // create enum arrays
    $num_ranks = $qnaire_class_name::count();
    $ranks = array();
    for( $rank = 1; $rank <= ( $num_ranks + 1 ); $rank++ ) $ranks[] = $rank;
    $ranks = array_combine( $ranks, $ranks );

    $interview_methods = array();
    $interview_mod = lib::create( 'database\modifier' );
    $interview_mod->order( 'interview_method.name' );
    foreach( $interview_method_class_name::select( $interview_mod ) as $db_interview_method )
      $interview_methods[$db_interview_method->id] = $db_interview_method->name;

    $surveys = array();
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'active', '=', 'Y' );
    $modifier->where( 'anonymized', '=', 'N' );
    $modifier->where( 'tokenanswerspersistence', '=', 'Y' );
    foreach( $surveys_class_name::select( $modifier ) as $db_survey )
      $surveys[$db_survey->sid] = $db_survey->get_title();

    // set the view's items
    $this->set_item( 'name', $record->name, true );
    $this->set_item( 'rank', $record->rank, true, $ranks );
    $this->set_item( 'default_interview_method_id',
      $record->default_interview_method_id, true, $interview_methods );
    $this->set_item( 'delay', $record->delay, true );
    $this->set_item( 'withdraw_sid', $record->withdraw_sid, false, $surveys );
    $this->set_item( 'phases', $record->get_phase_count() );
    $this->set_item( 'description', $record->description );
    $this->set_item( 'phases', $record->get_phase_count() );
    $this->set_item( 'description', $record->description );
    
    try
    {
      $this->interview_method_list->process();
      $this->set_variable( 'interview_method_list', $this->interview_method_list->get_variables() );
    }
    catch( \cenozo\exception\permission $e ) {}
    
    try
    {
      $this->phase_list->process();
      $this->set_variable( 'phase_list', $this->phase_list->get_variables() );
    }
    catch( \cenozo\exception\permission $e ) {}
    
    try
    {
      $this->source_withdraw_list->process();
      $this->set_variable( 'source_withdraw_list', $this->source_withdraw_list->get_variables() );
    }
    catch( \cenozo\exception\permission $e ) {}
    
    try
    {
      $this->event_type_list->process();
      $this->set_variable( 'event_type_list', $this->event_type_list->get_variables() );
    }
    catch( \cenozo\exception\permission $e ) {}
  }
  
  /**
   * The qnaire list widget.
   * @var interview_method_list
   * @access protected
   */
  protected $interview_method_list = NULL;
  
  /**
   * The qnaire list widget.
   * @var phase_list
   * @access protected
   */
  protected $phase_list = NULL;
  
  /**
   * The qnaire list widget.
   * @var source_withdraw_list
   * @access protected
   */
  protected $source_withdraw_list = NULL;
  
  /**
   * The qnaire list widget.
   * @var event_type_list
   * @access protected
   */
  protected $event_type_list = NULL;
}
