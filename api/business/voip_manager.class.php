<?php
/**
 * voip_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\business;
use cenozo\lib, cenozo\log, sabretooth\util;

require_once SHIFT8_PATH.'/library/Shift8.php';

/**
 * Manages VoIP communications.
 */
class voip_manager extends \cenozo\singleton
{
  /**
   * Constructor.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function __construct()
  {
    $setting_manager = lib::create( 'business\setting_manager' );
    $this->enabled = true === $setting_manager->get_setting( 'voip', 'enabled' );
    $this->url = $setting_manager->get_setting( 'voip', 'url' );
    $this->username = $setting_manager->get_setting( 'voip', 'username' );
    $this->password = $setting_manager->get_setting( 'voip', 'password' );
    $this->prefix = $setting_manager->get_setting( 'voip', 'prefix' );
  }
    
  /**
   * Initializes the voip manager.
   * 
   * This method should be called immediately after initial construction of the manager
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\runtime, exception\voip
   * @access public
   */
  public function initialize()
  {
    if( !$this->enabled ) return;

    try
    {
      // create and connect to the shift8 AJAM interface
      $this->manager = new \Shift8( $this->url, $this->username, $this->password );
      if( !$this->manager->login() )
        throw lib::create( 'exception\runtime',
          'Unable to connect to the Asterisk server.', __METHOD__ );

      // get the current SIP info
      $peer = static::get_peer();
      $s8_event = $this->manager->getSipPeer( $peer );
      
      if( !is_null( $s8_event ) &&
          $peer == $s8_event->get( 'objectname' ) &&
          'OK' == substr( $s8_event->get( 'status' ), 0, 2 ) )
      {
        $this->sip_info = array(
          'status' => $s8_event->get( 'status' ),
          'type' => $s8_event->get( 'channeltype' ),
          'agent' => $s8_event->get( 'sip_useragent' ),
          'ip' => $s8_event->get( 'address_ip' ),
          'port' => $s8_event->get( 'address_port' ) );
      }
    }
    catch( \Shift8_Exception $e )
    {
      throw lib::create( 'exception\voip',
        'Failed to initialize Asterisk AJAM interface.', __METHOD__, $e );
    }
  }
  
  /**
   * Reads the list of active calls from the server.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\voip
   * @access public
   */
  public function rebuild_call_list()
  {
    $this->call_list = array();
    $events = $this->manager->getStatus();

    if( is_null( $events ) )
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );

    foreach( $events as $s8_event )
      if( 'Status' == $s8_event->get( 'event' ) )
        $this->call_list[] = lib::create( 'business\voip_call', $s8_event, $this->manager );
  }
  
  /**
   * Gets a user's active call.  If the user isn't currently on a call then null is returned.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\user $db_user Which user's call to retrieve.  If this parameter is null then
   *        the current user's call is returned.
   * @return voip_call
   * @access public
   */
  public function get_call( $db_user = NULL )
  {
    if( !$this->enabled ) return NULL;
    if( is_null( $this->call_list ) ) $this->rebuild_call_list();

    $peer = static::get_peer( $db_user );

    // build the call list
    $calls = array();
    foreach( $this->call_list as $voip_call )
      if( $peer == $voip_call->get_peer() ) return $voip_call;

    return NULL;
  }
  
  /**
   * Attempts to connect to a phone.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param mixed $phone May be a database phone record or an explicit number
   * @return voip_call
   * @access public
   * @throws exception\argument, exception\runtime, exception\notice, exception\voip
   */
  public function call( $phone )
  {
    if( !$this->enabled ) return NULL;

    // validate the input
    if( !is_object( $phone ) )
    {
      $number = $phone;
    }
    else
    {
      $db_phone = $phone;
      if( !is_object( $db_phone ) )
        throw lib::create( 'exception\argument', 'db_phone', $db_phone, __METHOD__ );

      $number = $db_phone->number;
    }

    // check that the phone number has exactly 10 digits
    $digits = preg_replace( '/[^0-9]/', '', $number );
    if( 10 != strlen( $digits ) )
      throw lib::create( 'exception\runtime',
        'Tried to connect to phone number which does not have exactly 10 digits.', __METHOD__ );

    // make sure the user isn't already in a call
    if( !is_null( $this->get_call() ) )
      throw lib::create( 'exception\notice',
        'Unable to connect call since you already appear to be in a call.', __METHOD__ );

    // originate call (careful, the online API has the arguments in the wrong order)
    $peer = static::get_peer();
    $channel = 'SIP/'.$peer;
    $context = 'from-internal';
    $extension = $this->prefix.$digits;
    $priority = 1;
    if( !$this->manager->originate( $channel, $context, $extension, $priority ) )
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );

    // rebuild the call list and return (what should be) the peer's only call
    $this->rebuild_call_list();
    return $this->get_call();
  }

  /**
   * Opens a listen-only connection to an existing call
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param voip_call $voip_call The call to spy on
   * @access public
   */
  public function spy( $voip_call )
  {
    $peer = static::get_peer();
    $channel = 'SIP/'.$peer;
    // play sound in local channel
    if( !$this->manager->originate(
      $channel,             // channel
      'cenozo',             // context
      'chanspy',            // extension
      1,                    // priority
      false,                // application
      false,                // data
      30000,                // timeout
      false,                // callerID
      'ActionID=Spy,'.      // variables
      'ToChannel='.$voip_call->get_channel() ) )
    {
      throw lib::create( 'exception\voip', $this->manager->getLastError(), __METHOD__ );
    }

    // rebuild the call list and return (what should be) the peer's only call
    $this->rebuild_call_list();
    return $this->get_call();
  }

  /**
   * Flushes a user's details using a sip prune command.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\user $db_user Which user to flush.
   * @access public
   */
  public function sip_prune( $db_user )
  {
    if( !$this->enabled || is_null( $db_user ) ) return;
    
    // there is no way to send a sip prune command to asterisk using AMI so we need to use the CLI
    $peer = static::get_peer( $db_user );
    $output = array();
    $return_value = 0;
    exec( sprintf( 'asterisk -rx "sip prune realtime peer %s"', $peer ), $output, $return_value );
    if( 0 != $return_value ) log::err( $output[0] );
  }

  /**
   * Determines whether a SIP connection is detected with the client
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return boolean
   * @access public
   */
  public function get_sip_enabled()
  {
    return $this->enabled && is_array( $this->sip_info ) && 0 < count( $this->sip_info );
  }
  
  /**
   * Whether VOIP is enabled.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return boolean
   * @access public
   */
  public function get_enabled() { return $this->enabled; }

  /**
   * Gets the dialing prefix to use when placing external calls
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_prefix() { return $this->prefix; }

  public static function get_peer( $db_user = NULL )
  {
    return 10000000 + ( is_null( $db_user ) ?
      lib::create( 'business\session' )->get_user()->id : $db_user->id );
  }

  /**
   * The asterisk manager object
   * @var Shift8 object
   * @access private
   */
  private $manager = NULL;

  /**
   * The current SIP information (empty array if there is no connection found)
   * @var array
   * @access private
   */
  private $sip_info = NULL;

  /**
   * An array of all currently active calls.
   * 
   * @var array( voip_call )
   * @access private
   */
  private $call_list = NULL;

  /**
   * Whether VOIP is enabled.
   * @var string
   * @access private
   */
  private $enabled = false;
  
  /**
   * The url that asterisk's AJAM is running on
   * @var string
   * @access private
   */
  private $url = '';
  
  /**
   * Which username to use when connecting to the manager
   * @var string
   * @access private
   */
  private $username = '';
  
  /**
   * Which password to use when connecting to the manager
   * @var string
   * @access private
   */
  private $password = '';
  
  /**
   * The dialing prefix to use when making external calls.
   * @var string
   * @access private
   */
  private $prefix = '';
}
