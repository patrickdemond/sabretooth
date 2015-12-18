<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\service\shift_template;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    // restrict by date, if requested
    $min_date = $this->get_argument( 'min_date', NULL );
    $max_date = $this->get_argument( 'max_date', NULL );

    if( !is_null( $min_date ) )
      $modifier->where( sprintf( 'IFNULL( end_date, "%s" )', $min_date ), '>=', $min_date );
    if( !is_null( $max_date ) )
      $modifier->where( 'start_date', '<=', $max_date );

    // only show shift templates for the user's current site
    $modifier->where( 'site_id', '=', lib::create( 'business\session' )->get_site()->id );

    // title is what to label events in the calendar view
    if( $select->has_column( 'title' ) )
      $select->add_column( 'CONCAT( operators, " operators" )', 'title', false );

    if( $select->has_column( 'week' ) )
    {
      // add week column
      $select->add_column(
        'IF( "weekly" = repeat_type, '.
            'CONCAT( IF( monday, "M", "_" ), '.
                    'IF( tuesday, "T", "_" ), '.
                    'IF( wednesday, "W", "_" ), '.
                    'IF( thursday, "T", "_" ), '.
                    'IF( friday, "F", "_" ), '.
                    'IF( saturday, "S", "_" ), '.
                    'IF( sunday, "S", "_" ) ), '.
            '"(n/a)" )',
        'week',
        false );
    }
  }

  /**
   * Extend parent method
   */
  public function pre_write( $record )
  {
    // force the site to the current user's site
    $record->site_id = lib::create( 'business\session' )->get_site()->id;
  }
}
