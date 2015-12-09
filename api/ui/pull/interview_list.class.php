<?php
/**
 * interview_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\pull;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Class for interview list pull operations.
 * 
 * @abstract
 */
class interview_list extends \cenozo\ui\pull\base_list
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
    parent::__construct( 'interview', $args );
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

    // get the qnaire if one was provided
    $qnaire_rank = $this->get_argument( 'qnaire_rank', NULL );
    $qnaire_name = $this->get_argument( 'qnaire_name', NULL );
    if( !is_null( $qnaire_rank ) )
      $this->db_qnaire = $qnaire_class_name::get_unique_record( 'rank', $qnaire_rank );
    else if( !is_null( $qnaire_name ) )
      $this->db_qnaire = $qnaire_class_name::get_unique_record( 'name', $qnaire_name );
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
    if( is_null( $this->db_qnaire ) )
      throw lib::create( 'exception\argument', 'qnaire_*', NULL, __METHOD__ );

    // make sure the date spans are valid, if provided
    $start_date = $this->get_argument( 'start_date', NULL );
    if( !is_null( $start_date ) )
    {
      try
      {
        $this->start_date_obj = util::get_datetime_object( $start_date );
      }
      catch( \Exception $e )
      {
        throw lib::create( 'exception\argument', 'start_date', $start_date, $e, __METHOD__ );
      }
    }

    $end_date = $this->get_argument( 'end_date', NULL );
    if( !is_null( $end_date ) )
    {
      try
      {
        $this->end_date_obj = util::get_datetime_object( $end_date );
      }
      catch( \Exception $e )
      {
        throw lib::create( 'exception\argument', 'end_date', $end_date, $e, __METHOD__ );
      }
    }

    // check range
    if( !is_null( $this->start_date_obj ) &&
        !is_null( $this->end_date_obj ) &&
        util::get_interval( $this->start_date_obj, $this->end_date_obj )->invert )
      throw lib::create( 'exception\runtime', sprintf(
        'Start date "%s" comes after end date "%s".',
        $this->start_date_obj->format( 'Y-m-d' ),
        $this->end_date_obj->format( 'Y-m-d' ) ),
        __METHOD__ );
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
    $this->modifier->order( 'interview.id' );
    $this->modifier->where( 'qnaire_id', '=', $this->db_qnaire->id );

    // sort out the date spans
    if( !is_null( $this->start_date_obj ) )
    {
      $this->modifier->where(
        'assignment.end_datetime', '>=', $this->start_date_obj->format( 'Y-m-d' ).' 00:00:00' );
    }

    // sort out the date spans
    if( !is_null( $this->end_date_obj ) )
    {
      $this->modifier->where(
        'assignment.end_datetime', '<=', $this->end_date_obj->format( 'Y-m-d' ).' 23:59:59' );
    }
  }

  /**
   * Overrides the parent method in case the "linkage" parameter is set to true.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\record $record
   * @return array
   * @access public
   */
  public function process_record( $record )
  {
    $linkage = $this->get_argument( 'linkage', false );

    if( !$linkage )
    {
      $item = parent::process_record( $record );
    }
    else
    {
      $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );

      // instead of getting the interview's details, get the participant uid and the qnaire's token
      $item = array(
        'uid' => $record->get_participant()->uid,
        'token' => $tokens_class_name::determine_token_string( $record->get_participant(), false ) );
    }

    return $item;
  }

  /**
   * The qnaire to restrict to.
   * @var database\qnaire
   * @access protected
   */
  protected $db_qnaire = NULL;

  /**
   * The start date to restrict the list to.
   * @var \Datetime
   * @access protected
   */
  protected $start_date_obj = NULL;

  /**
   * The end date to restrict the list to.
   * @var \Datetime
   * @access protected
   */
  protected $end_date_obj = NULL;
}
