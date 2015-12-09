<?php
/**
 * recording.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\database;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * recording: record
 */
class recording extends \cenozo\database\record
{
  /**
   * Gets the file associated with this recording
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_filename()
  {
    // make sure the recording has the participant set
    if( is_null( $this->participant_id ) )
    {
      log::warning(
        'Tried to get filename of recording without both participant_id.' );
      return NULL;
    }
    
    $uid = $this->get_participant()->uid;
    $padded_rank = str_pad( is_null( $this->rank ) ? 1 : $this->rank, 2, '0', STR_PAD_LEFT );
    $filename = sprintf( '%s/%s', $uid, $padded_rank );
    
    return $filename;
  }
}
