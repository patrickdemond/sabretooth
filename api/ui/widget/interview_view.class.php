<?php
/**
 * interview_view.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * widget interview view
 * 
 * @package sabretooth\ui
 */
class interview_view extends \cenozo\ui\widget\base_view
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
    parent::__construct( 'interview', 'view', $args );
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

    // create an associative array with everything we want to display about the interview
    $this->add_item( 'uid', 'constant', 'UID' );
    $this->add_item( 'participant', 'constant', 'Participant' );
    $this->add_item( 'qnaire', 'constant', 'Questionnaire' );
    $this->add_item( 'completed', 'boolean', 'Completed',
      'Warning: force-completing an interview cannot be undone!' );
    $this->add_item( 'rescored', 'constant', 'Rescored' );
    $this->add_item( 'recordings', 'constant', 'Recordings' );

    // create the assignment sub-list widget      
    $this->assignment_list = lib::create( 'ui\widget\assignment_list', $this->arguments );
    $this->assignment_list->set_parent( $this );
    $this->assignment_list->set_heading( 'Assignments associated with this interview' );
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
       
    $db_participant = $this->get_record()->get_participant();
    $participant = sprintf( '%s, %s', $db_participant->last_name, $db_participant->first_name );

    // set the view's items
    $this->set_item( 'uid', $db_participant->uid );
    $this->set_item( 'participant', $participant );
    $this->set_item( 'qnaire', $this->get_record()->get_qnaire()->name );
    $this->set_item( 'completed', $this->get_record()->completed, true );
    $this->set_item( 'rescored', $this->get_record()->rescored );
    $this->set_item( 'recordings', $this->get_record()->get_recording_count() );

    // only allow rescoring if the interview's qnaire has a rescore ID, the user has access
    // to the rescoring operation, the interview is complete and there are recordings available
    $operation_class_name = lib::get_class_name( 'database\operation' );
    $db_operation = $operation_class_name::get_operation( 'widget', 'interview', 'rescore' );
    $allow_rescore =
      // the interview is completed
      $this->get_record()->completed &&
      // the interview is not already rescored
      'No' == $this->get_record()->rescored &&
      // the user is allowed to rescore interviews
      lib::create( 'business\session' )->is_allowed( $db_operation ) &&
      // the qnaire has a rescoring survey
      !is_null( $this->get_record()->get_qnaire()->rescore_sid );
      // the interview has at least 1 recording
      // NOTE: not used to help clarify why interviews can't be rescored
      //0 < $this->get_record()->get_recording_count();
    $this->set_variable( 'allow_rescore', $allow_rescore );
    if( $allow_rescore )
      $this->add_action( 'rescore', 'Rescore', NULL,
        'Listen to the recordings made during the interview for rescoring purposes' );

    // process the child widgets
    try
    {
      $this->assignment_list->process();
      $this->assignment_list->remove_column( 'uid' );
      $this->assignment_list->execute();
      $this->set_variable( 'assignment_list', $this->assignment_list->get_variables() );
    }
    catch( \cenozo\exception\permission $e ) {}
  }
  
  /**
   * Overrides the assignment list widget's method.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @assignment protected
   */
  public function determine_assignment_count( $modifier = NULL )
  {
    if( NULL == $modifier ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'assignment.interview_id', '=', $this->get_record()->id );
    return $this->assignment_list->determine_record_count( $modifier );
  }

  /**
   * Overrides the assignment list widget's method.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @assignment protected
   */
  public function determine_assignment_list( $modifier = NULL )
  {
    if( NULL == $modifier ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'assignment.interview_id', '=', $this->get_record()->id );
    return $this->assignment_list->determine_record_list( $modifier );
  }
  
  /**
   * The interview list widget.
   * @var assignment_list
   * @access protected
   */
  protected $assignment_list = NULL;
}
?>
