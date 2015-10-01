<?php
/**
 * shift.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * shift: record
 */
class shift extends \cenozo\database\record
{
  /**
   * Overrides the parent class to prevent doubling shift times.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\runtime
   * @access public
   */
  public function save()
  {
    // warn if we are in read-only mode
    if( $this->read_only )
    {
      log::warning( 'Tried to save read-only record.' );
      return;
    }

    $db_user = lib::create( 'database\user', $this->user_id );
    $db_site = lib::create( 'database\site', $this->site_id );
    $class_name = lib::get_class_name( 'database\role' );
    $db_role = $class_name::get_unique_record( 'name', 'operator' );
    
    // Make sure the user has the operator role at the site
    if( !$db_user->has_access( $db_site, $db_role ) )
    {
      throw lib::create( 'exception\runtime',
        sprintf( 'Cannot assign shift to "%s", user does not have operator access to %s',
                 $db_user->name,
                 $db_site->name ), __METHOD__ );
    }

    // See if the user already has a shift at this time
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'id', '!=', $this->id );
    $modifier->where( 'user_id', '=', $this->user_id );
    
    // convert the start and end times to server time
    $start_datetime = util::to_server_datetime( $this->start_datetime );
    $end_datetime = util::to_server_datetime( $this->end_datetime );

    // (need to use custom SQL)
    $overlap_ids = static::db()->get_col( 
      sprintf( 'SELECT id FROM %s %s '.
               'AND NOT ( ( start_datetime <= %s AND end_datetime <= %s ) OR '.
                         '( start_datetime >= %s AND end_datetime >= %s ) )',
               static::get_table_name(),
               $modifier->get_where(),
               static::db()->format_string( $start_datetime ),
               static::db()->format_string( $start_datetime ),
               static::db()->format_string( $end_datetime ),
               static::db()->format_string( $end_datetime ) ) );
    
    if( 0 < count( $overlap_ids ) )
    {
      $overlap_id = current( $overlap_ids );
      $db_overlap = new static( $overlap_id );
      throw lib::create( 'exception\notice',
        sprintf( 'There is already a shift which exists for this operator during the requested '.
                 'time (%s to %s).  Please adjust the shift times so that there is no overlap.',
                 substr( $db_overlap->start_datetime, 0, -3 ),
                 substr( $db_overlap->end_datetime, 11, -3 ) ),
        __METHOD__ );
    }
    
    // all is well, continue with the parent's save method
    parent::save();
  }
}
