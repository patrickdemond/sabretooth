<?php
/**
 * recording_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Class for recording list pull operations.
 * 
 * @abstract
 */
class recording_list extends \cenozo\ui\pull\base_list
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'recording', $args );
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

    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $interview_class_name = lib::get_class_name( 'database\interview' );

    // get the qnaire if one was provided
    $qnaire_rank = $this->get_argument( 'qnaire_rank', NULL );
    $qnaire_name = $this->get_argument( 'qnaire_name', NULL );
    if( !is_null( $qnaire_rank ) )
    {
      $db_qnaire = $qnaire_class_name::get_unique_record( 'rank', $qnaire_rank );
      if( is_null( $db_qnaire ) )
        throw lib::create( 'exception\runtime',
          sprintf( 'No qnaire with a rank of %d exists', $qnaire_rank ),
          __METHOD__ );
      $this->db_interview = $interview_class_name::get_unique_record(
        array( 'qnaire_id', 'participant_id' ),
        array( $db_qnaire->id, $this->get_argument( 'participant_id' ) ) );
    }
    else if( !is_null( $qnaire_name ) )
    {
      $db_qnaire = $qnaire_class_name::get_unique_record( 'name', $qnaire_name );
      if( is_null( $db_qnaire ) )
        throw lib::create( 'exception\runtime',
          sprintf( 'No qnaire named "%s" exists', $qnaire_name ),
          __METHOD__ );
      $this->db_interview = $interview_class_name::get_unique_record(
        array( 'qnaire_id', 'participant_id' ),
        array( $db_qnaire->id, $this->get_argument( 'participant_id' ) ) );
    }
  }

  /**
   * Validate the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws excpetion\argument, exception\permission
   * @access protected
   */
  protected function validate()
  {
    parent::validate();

    // make sure we have a qnaire set
    if( is_null( $this->db_interview ) )
      throw lib::create( 'exception\runtime',
        'Cannot find interview matching request', __METHOD__ );
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

    if( is_null( $this->modifier ) ) $this->modifier = lib::create( 'database\modifier' );
    $this->modifier->where( 'interview_id', '=', $this->db_interview->id );
    $this->modifier->order( 'rank' );
  }

  /**
   * The interview to restrict to (based on the qnaire and participant)
   * @var database\interview
   * @access protected
   */
  protected $db_interview = NULL;
}
