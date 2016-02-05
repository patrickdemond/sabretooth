<?php
/**
 * base_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\ui\widget;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Base class for all report widgets
 * 
 * @abstract
 */
abstract class base_report extends \cenozo\ui\widget\base_report
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The subject being viewed.
   * @param string $name The name of the operation.
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $subject, $args )
  {
    parent::__construct( $subject, 'report', $args );
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

    $this->restrictions['qnaire'] = false;
    $this->restrictions['phase'] = false;
  }

  /**
   * Extending the parent setup method with extra restrictions.
   * @author Dean Inglis <inglisd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();

    if( $this->restrictions[ 'qnaire' ] )
    {
      $qnaire_mod = lib::create( 'database\modifier' );
      $qnaire_mod->order( 'rank' );
      $qnaire_list = array();
      $class_name = lib::get_class_name( 'database\qnaire' );
      foreach( $class_name::select( $qnaire_mod ) as $db_qnaire )
        $qnaire_list[ $db_qnaire->id ] = $db_qnaire->name;

      $this->set_parameter( 'restrict_qnaire_id', key( $qnaire_list ), true, $qnaire_list );
    }
    else if( $this->restrictions[ 'phase' ] )
    {
      $phase_mod = lib::create( 'database\modifier' );
      $phase_mod->order( 'rank' );
      $phase_list = array();
      $class_name = lib::get_class_name( 'database\phase' );
      foreach( $class_name::select( $phase_mod ) as $db_phase )
        $phase_list[ $db_phase->id ] = $db_phase->get_survey()->get_title();

      $this->set_parameter( 'restrict_phase_id', key( $phase_list ), true, $phase_list );
    }
  }

  /**
   * Adds more restrictions to reports.
   * @author Dean Inglis <inglisd@mcmaster.ca>
   * @param string $restriction_type The type of restriction requested.
   * @throws exception\argument
   * @access protected
   */
  protected function add_restriction( $restriction_type )
  {
    parent::add_restriction( $restriction_type );

    if( 'qnaire' == $restriction_type )
    {
      $this->restrictions[ 'qnaire' ] = true;
      $this->add_parameter( 'restrict_qnaire_id', 'enum', 'Questionnaire' );
    }
    else if( 'phase' == $restriction_type )
    {
      $this->restrictions[ 'phase' ] = true;
      $this->add_parameter( 'restrict_phase_id', 'enum', 'Phase' );
    }
  }
}
