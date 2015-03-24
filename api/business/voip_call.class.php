<?php
/**
 * voip_call.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\business;
use cenozo\lib, cenozo\log, sabretooth\util;

require_once SHIFT8_PATH.'/library/Shift8.php';

/**
 * The details of a voip call.
 */
class voip_call extends \cenozo\base_object
{
  /**
   * Constructor.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param Shift8_Event $event The event from a Shift8::getStatus() call
   * @access public
   */
  public function __construct( $s8_event, $manager )
  {
    // check that the shift8 event is valid
    if( is_null( $s8_event ) ||
        !is_object( $s8_event ) ||
        'Shift8_Event' != get_class( $s8_event ) )
      throw lib::create( 'exception\argument', 's8_event', $s8_event, __METHOD__ );
    
    $this->manager = $manager;
    $this->channel = $s8_event->get( 'channel' );
    $this->bridge = $s8_event->get( 'bridgedchannel' );
    $this->state = $s8_event->get( 'channelstatedesc' );
    $this->time = intval( $s8_event->get( 'seconds' ) );

    // get the dialed number by striping the dialing prefix from the extension
    if( !is_null( $s8_event->get( 'extension' ) ) )
    {
      $prefix = lib::create( 'business\voip_manager' )->get_prefix();
      $this->number = preg_replace( "/^$prefix/", '', $s8_event->get( 'extension' ) );
    }

    // get the peer from the channel which is in the form: SIP/<peer>-HHHHHHHH
    // (where <peer> is the peer (without < and >) and H is a hexidecimal number)
    $slash = strpos( $this->channel, '/' );
    $dash = strrpos( $this->channel, '-' );
    $this->peer = false === $slash || false === $dash || $slash >= $dash
                ? 'unknown'
                : substr( $this->channel, $slash + 1, $dash - $slash - 1 );
  }
  
  /**
   * Play a DTMF tone (ie: dial a number)
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $tone Which tone to play (one of 0123456789abcdgs)
   * @throws exception\voip
   * @access public
   */
  public function dtmf( $tone )
  {
    if( !lib::create( 'business\voip_manager' )->get_enabled() ) return;
    
    // make sure the tone is valid
    if( !preg_match( '/^[0-9a-dgs]$/', $tone ) )
    {
      log::warning( 'Attempting to play an invalid DTMF tone.' );
      return;
    }

    // play the dtmf sound locally as audible feedback
    $this->play_sound( 'custom/dtmf'.$tone, 0, false );
    
    // convert g to # and s to * before sending to asterisk
    if( 'g' == $tone ) $tone = '#';
    else if( 's' == $tone ) $tone = '*';

    // now send the DTMF tone itself (which is not heard locally)
    if( !$this->manager->playDTMF( $tone, $this->get_bridge() ) )
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );
  }
  
  /**
   * Disconnects a call (does nothing if already disconnected).
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function hang_up()
  {
    $voip_manager = lib::create( 'business\voip_manager' );
    if( !$voip_manager->get_enabled() ) return;
    
    // hang up the call, if successful then rebuild the call list
    if( $this->manager->hangup( $this->get_channel() ) )
      $voip_manager->rebuild_call_list();
  }
  
  /**
   * Plays a sound file located in asterisk's sound directory.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $sound The name of the sound file to play, without file extension.  For custom
   *               sounds (those that are not included with asterisk) make sure to specify the
   *               custom directory, ie: custom/dtmf0
   * @param int $volume The volume to play the sound at.  This is an integer which ranges from -4
                to 4, where 0 is the "regular" volume.
   * @param boolean $bridge Whether to play the sound so that both sides of the connection can hear
   *                it.  If this is false then only the caller will hear the sound.
   * @access public
   */
  public function play_sound( $sound, $volume = 0, $bridge = true )
  {
    if( !lib::create( 'business\voip_manager' )->get_enabled() ) return;
    
    // constrain the volume to be between -4 and 4
    $volume = intval( $volume );
    if( -4 > $volume ) $volume = -4;
    else if( 4 < $volume ) $volume = 4;

    // play sound in local channel
    if( !$this->manager->originate(
      'Local/playback@cenozo',   // channel
      'cenozo',                  // context
      'playbackspy',             // extension
      1,                         // priority
      false,                     // application
      false,                     // data
      30000,                     // timeout
      false,                     // callerID
      'ActionID=PlayBack,'.      // variables
      'Sound='.$sound.','.
      'Volume='.$volume.','.
      'ToChannel='.$this->get_channel() ) )
    {
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );
    }
    
    if( $bridge )
    {
      // Sleep for 5 miliseconds to try and fix asterisk bug caused by playing two sounds
      // in quick succession
      time_nanosleep( 0, 500000000 );

      // play sound in bridged channel
      if( !$this->manager->originate(
        'Local/playback@cenozo',   // channel
        'cenozo',                  // context
        'playbackspy',             // extension
        1,                         // priority
        false,                     // application
        false,                     // data
        30000,                     // timeout
        false,                     // callerID
        'ActionID=PlayBack,'.      // variables
        'Sound='.$sound.','.
        'Volume='.$volume.','.
        'ToChannel='.$this->get_bridge() ) )
      {
        throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );
      }
    }
  }
  
  /**
   * Starts recording (monitoring) the call.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $filename The file name the recorded call is to be saved under.
   * @access public
   */
  public function start_monitoring( $filename )
  {
    if( !lib::create( 'business\voip_manager' )->get_enabled() ) return;
 
    $filename = sprintf( '%s/%s', VOIP_MONITOR_PATH, $filename );

    // make sure to not overwrite any existing recordings
    if( file_exists( $filename.'-out.wav' ) )
    {
      $base_filename = str_replace( '-01', '', $filename );
      $index = 1;
      do
      {
        $index++;
        $filename = sprintf( '%s-%s',
                             $base_filename,
                             str_pad( $index, 2, '0', STR_PAD_LEFT ) );
      }
      while( file_exists( $filename.'-out.wav' ) );
    }

    if( false == $this->manager->monitor( $this->get_channel(), $filename, 'wav' ) )
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );
  }
  
  /**
   * Stops recording (monitoring) the call.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function stop_monitoring()
  {
    if( !lib::create( 'business\voip_manager' )->get_enabled() ) return;

    if( false == $this->manager->stopMonitor( $this->get_channel() ) )
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );
  }
  
  /**
   * Get the call's peer
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_peer() { return $this->peer; }

  /**
   * Get the call's channel
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_channel() { return $this->channel; }

  /**
   * Get the call's bridged channel
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_bridge() { return $this->bridge; }

  /**
   * Get the call's state (Up, Ring, etc)
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_state() { return $this->state; }

  /**
   * Get the number called (the extension without dialing prefix)
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_number() { return $this->number; }

  /**
   * Get the call's time in seconds
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return int
   * @access public
   */
  public function get_time() { return $this->time; }

  /**
   * The call's peer (should match system user name)
   * 
   * @var string
   * @access private
   */
  private $peer;

  /**
   * The call's channel.
   * 
   * @var string
   * @access private
   */
  private $channel = NULL;

  /**
   * The call's bridged channel.
   * 
   * @var string
   * @access private
   */
  private $bridge = NULL;

  /**
   * The state of the call (Up, Ring, etc)
   * 
   * @var string
   * @access private
   */
  private $state = NULL;

  /**
   * The number called (the extension without dialing prefix)
   * 
   * @var string
   * @access private
   */
  private $number = NULL;

  /**
   * The length of the call in seconds.
   * 
   * @var int
   * @access private
   */
  private $time = NULL;

  /**
   * The asterisk manager object (reference to the voip_manager's object)
   * @var Shift8 object
   * @access private
   */
  private $manager = NULL;
}
