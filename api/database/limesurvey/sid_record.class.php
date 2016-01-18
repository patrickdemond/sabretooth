<?php
/**
 * sid_record.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database\limesurvey;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Access to limesurvey's *_SID tables.
 * 
 * Since limesurvey's database structure for some tables is dynamic this class overrides
 * much of the functionality in record class as is appropriate.
 */
abstract class sid_record extends record
{
  /**
   * Returns the current SID for all records of this class type.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return int
   * @access public
   * @static
   */
  public static function get_sid()
  {
    $class_index = lib::get_class_name( get_called_class(), true );
    return array_key_exists( $class_index, self::$table_sid_list )
         ? self::$table_sid_list[$class_index] : NULL;
  }

  /**
   * Sets the current SID for all records of this class type.
   * Make sure to call this method BEFORE using any normal or static methods.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param int $sid
   * @access public
   * @static
   */
  public static function set_sid( $sid )
  {
    $class_index = lib::get_class_name( get_called_class(), true );
    if( is_null( $sid ) ) unset( self::$table_sid_list[$class_index] );
    else self::$table_sid_list[$class_index] = $sid;
  }

  /**
   * Returns the name of the table associated with this record.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   * @static
   */
  public static function get_table_name()
  {
    if( is_null( static::get_sid() ) )
    {
      throw lib::create( 'exception\runtime',
        'The survey id (table_sid) must be set before using this class.', __METHOD__ );
    }

    return sprintf( '%s_%s', parent::get_table_name(), static::get_sid() );
  }
  
  /**
   * The table's current sid.  This is an array since every class must track its own sid
   * separately.
   * @var array(int)
   * @access private
   */
  private static $table_sid_list = array();
}
