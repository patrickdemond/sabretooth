<?php
/**
 * tokens.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database\limesurvey;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * Access to limesurvey's tokens_SID tables.
 */
class tokens extends sid_record
{
  const TOKEN_POSTFIX_LENGTH = 7;

  /** 
   * TODO: document
   */
  public static function where_token( $modifier, $db_participant, $repeated = false )
  {
    if( !is_null( $modifier ) && !is_a( $modifier, lib::get_class_name( 'database\modifier' ) ) ) 
      throw lib::create( 'exception\argument', 'modifier', $modifier, __METHOD__ );
    if( !is_null( $db_participant ) && !is_a( $db_participant, lib::get_class_name( 'database\participant' ) ) ) 
      throw lib::create( 'exception\argument', 'db_participant', $db_participant, __METHOD__ );

    if( $repeated )
    {   
      $like = sprintf( '%s.%s', $db_participant->uid, str_repeat( '_', static::TOKEN_POSTFIX_LENGTH ) );
      $modifier->where( 'token', 'LIKE', $like );
    }   
    else $modifier->where( 'token', '=', $db_participant->uid );
  }

  /** 
   * Returns the token name based on the participant and whether the script is repeated
   * 
   * If the script is not repeated then the token string is simply the participant's UID.
   * If the script is repeated then a counter is postfixed to the UID.  The largest pre-existing postfix
   * will be found and incremented, or if this is the participant's first token then a postfix of 1 will
   * be added. Postfixes are delimited by a period (.)
   * Note: postfixes are always padded with zeros (0)
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @param boolean $repeated
   * @static
   * @access public
   */
  public static function determine_token_string( $db_participant, $repeated )
  {
    $token = $db_participant->uid;
    if( $repeated )
    {
      // create an counter as a postfix
      $modifier = lib::create( 'database\modifier' );
      static::where_token( $modifier, $db_participant->uid, true );
      $sub_select = sprintf(
        '( SELECT MAX( tid ) AS max_tid FROM %s %s )', 
        static::get_table_name(),
        $modifier->get_sql() );

      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'tid', '=', $sub_select, false );
      $last_token = static::db()->get_one( sprintf(
        'SELECT token FROM %s %s',
        static::get_table_name(),
        $modifier->get_sql() ) );

      $postfix = $last_token
               ? substr( $last_token, -static::TOKEN_POSTFIX_LENGTH )
               : str_repeat( '0', static::TOKEN_POSTFIX_LENGTH );
      $postfix++;

      $token .= '.'.$postfix;
    }

    return $token;
  }

  /**
   * The name of the table's primary key column.
   * @var string
   * @access protected
   */
  protected static $primary_key_name = 'tid';
}
