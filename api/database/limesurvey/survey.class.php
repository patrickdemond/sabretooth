<?php
/**
 * survey.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database\limesurvey;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Access to limesurvey's survey_SID tables.
 */
class survey extends sid_record
{
  /**
   * Returns a participant's response to a particular question.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $question_code
   * @return string
   * @access public
   */
  public function get_response( $question_code )
  {
    // the questions table has more than one column in its primary key so custom sql is needed
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'sid', '=', static::get_sid() );
    $modifier->where( 'title', '=', $question_code );
    $modifier->group( 'sid' );
    $modifier->group( 'gid' );
    $modifier->group( 'qid' );
    $sql = sprintf( 'SELECT gid, qid, parent_qid FROM %s %s',
                    static::db()->get_prefix().'questions',
                    $modifier->get_sql() );
    
    $row = static::db()->get_row( $sql );
    if( 0 == count( $row ) )
      throw lib::create( 'exception\runtime', 'Question code not found in survey.', __METHOD__ );

    $column_name = $row['parent_qid']
                 ? sprintf( '%sX%sX%s%s', static::get_sid(), $row['gid'], $row['parent_qid'], $question_code )
                 : sprintf( '%sX%sX%s', static::get_sid(), $row['gid'], $row['qid'] );
    return $this->$column_name;
  }

  /**
   * Returns the total time in seconds spent on this survey (by all participants)
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier
   * @return double
   * @static
   */
  public static function get_total_time( $modifier = NULL )
  {
    $table_name = static::get_table_name();
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( $table_name.'.id', '=', $table_name.'_timings.id', false );

    return static::db()->get_one( sprintf(
      'SELECT SUM( IFNULL( interviewtime, 0 ) ) FROM %s, %s %s',
      $table_name,
      $table_name.'_timings',
      $modifier->get_sql() ) );
  }

  /**
   * The name of the table's primary key column.
   * @var string
   * @access protected
   */
  protected static $primary_key_name = 'id';
}
