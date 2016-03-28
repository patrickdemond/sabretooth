<?php
/**
 * survey_timings.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database\limesurvey;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Access to limesurvey's survey_SID_timings tables.
 */
class survey_timings extends sid_record
{
  /**
   * Need to override the parent class because this table doesn't follow the
   * <table_name>_<SID> convention.
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

    return sprintf( 'survey_%s_timings', static::get_sid() );
  }

  /**
   * Returns an associative array of the average time for all questions in this survey
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\region $db_region Restrict average to participants from a certain region
   * @return array
   * @access public
   */
  public static function get_averages( $db_region = NULL )
  {
    // we need to get all *X*X*time based column names from the information schema
    // to build custom sql
    $information_mod = lib::create( 'database\modifier' );
    $information_mod->where( 'table_schema', '=', static::db()->get_name() );
    $information_mod->where( 'table_name', '=', static::get_table_name() );
    $information_mod->where( 'column_name', 'LIKE', '%X%X%time' );

    $column_list = static::db()->get_col( sprintf(
      'SELECT column_name '.
      'FROM information_schema.columns %s',
      $information_mod->get_sql() ) );

    $sql = '';
    $first = true;
    foreach( $column_list as $column )
    {
      $sql .= sprintf( '%s AVG( NULLIF(%s,0) ) %s',
                       $first ? 'SELECT' : ',',
                       $column,
                       $column );
      if( $first ) $first = false;
    }

    if( !is_null( $db_region ) )
    {
      $sql .= sprintf( ' '.
        'FROM %s t '.
        'JOIN %s s ON t.id = s.id '.
        'JOIN %s.%sinterview i ON s.token LIKE CONCAT( i.id, "_%%" ) '.
        'JOIN %sparticipant_primary_address p ON i.participant_id = p.participant_id '.
        'JOIN %saddress a ON p.address_id = a.id '.
        'JOIN %sregion r ON a.region_id = r.id AND r.id = %s',
        static::get_table_name(),
        str_replace( '_timings', '', static::get_table_name() ),
        static::db()->get_name(),
        static::db()->get_prefix(),
        static::db()->get_prefix(),
        static::db()->get_prefix(),
        static::db()->get_prefix(),
        static::db()->format_string( $db_region->id ) );
    }
    else
    {
      $sql .= ' FROM '.static::get_table_name();
    }

    return static::db()->get_row( $sql );
  }
  
  /**
   * The name of the table's primary key column.
   * @var string
   * @access protected
   */
  protected static $primary_key_name = 'id';
}
