<?php
/**
 * survey_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\business;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * The survey manager is responsible for business-layer survey functionality.
 */
class survey_manager extends \cenozo\singleton
{
  /**
   * Constructor.
   * 
   * Since this class uses the singleton pattern the constructor is never called directly.  Instead
   * use the {@link singleton} method.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function __construct() {}

  /**
   * Gets the current survey URL.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string (or false if the survey is not active)
   * @access public
   */
  public function get_survey_url()
  {
    $session = lib::create( 'business\session' );

    // determine the participant
    $db_participant = NULL;
    if( array_key_exists( 'withdrawing_participant', $_COOKIE ) )
    {
      $db_participant = lib::create( 'database\participant', $_COOKIE['withdrawing_participant'] );
    }
    else if( array_key_exists( 'proxying_participant', $_COOKIE ) )
    {
      $db_participant = lib::create( 'database\participant', $_COOKIE['proxying_participant'] );
    }
    else if( 'operator' == $session->get_role()->name )
    {
      // must have an assignment
      $db_assignment = $session->get_current_assignment();
      if( !is_null( $db_assignment ) )
      {
        // the assignment must have an open call
        $modifier = lib::create( 'database\modifier' );
        $modifier->where( 'end_datetime', '=', NULL );
        $call_list = $db_assignment->get_phone_call_list( $modifier );
        if( 0 != count( $call_list ) )
          $db_participant = $db_assignment->get_interview()->get_participant();
      }
    }

    if( !is_null( $db_participant ) )
    {
      $sid = $this->get_current_sid();
      $token = $this->get_current_token();
      if( false !== $sid && false != $token )
      {
        // determine which language to use
        $db_language = $db_participant->get_language();
        if( is_null( $db_language ) ) $db_language = $session->get_service()->get_language();
        return sprintf( '%s/index.php?sid=%s&lang=%s&token=%s&newtest=Y',
                        LIMESURVEY_URL,
                        $sid,
                        $db_language->code,
                        $token );
      }
    }

    // there is currently no active survey
    return false;
  }

  /**
   * This method returns the current SID, or false if all surveys are complete.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return int
   * @access public
   */
  public function get_current_sid()
  {
    if( is_null( $this->current_sid ) ) $this->determine_current_sid_and_token();
    return $this->current_sid;
  }

  /**
   * This method returns the current token, or false if all surveys are complete.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_current_token()
  {
    if( is_null( $this->current_token ) ) $this->determine_current_sid_and_token();
    return $this->current_token;
  }

  /**
   * Determines the current SID and token.
   * 
   * This method will first determine whether the participant needs to complete the withdraw
   * script or a questionnaire.  It then determines whether the appropriate script has been
   * completed or not.
   * Note: This method will create tokens in the limesurvey database as necessary.
   * This is also where interviews are marked as complete once all phases are finished.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function determine_current_sid_and_token()
  {
    $this->current_sid = false;
    $this->current_token = false;

    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $source_survey_class_name = lib::get_class_name( 'database\source_survey' );

    $session = lib::create( 'business\session' );
    $setting_manager = lib::create( 'business\setting_manager' );

    if( array_key_exists( 'withdrawing_participant', $_COOKIE ) &&
             'operator' != $session->get_role()->name )
    {
      // get the participant being withdrawn
      $db_participant = lib::create( 'database\participant', $_COOKIE['withdrawing_participant'] );
      if( is_null( $db_participant ) )
      {
        log::warning( 'Tried to determine survey information for an invalid participant.' );
        return false;
      }

      $this->process_withdraw( $db_participant );
    }
    else if( array_key_exists( 'proxying_participant', $_COOKIE ) )
    {
      // get the participant being proxyied
      $db_participant = lib::create( 'database\participant', $_COOKIE['proxying_participant'] );
      if( is_null( $db_participant ) )
      {
        log::warning( 'Tried to determine survey information for an invalid participant.' );
        return false;
      }

      $this->process_proxy( $db_participant );
    }
    else // we're not running a special interview, so check for an assignment
    {
      $db_assignment = $session->get_current_assignment();
      if( is_null( $db_assignment ) )
      {
        log::warning( 'Tried to determine survey information without an active assignment.' );
        return false;
      }

      // records which we will need
      $db_interview = $db_assignment->get_interview();
      $db_participant = $db_interview->get_participant();
      $db_consent = $db_participant->get_last_consent();

      // the participant's last consent is consent, see if the withdraw script is complete
      if( $db_consent && false == $db_consent->accept )
      {
        // the rest is done in a private method
        $this->process_withdraw( $db_participant );
      }
      else
      { // the participant has not withdrawn, check each phase of the interview
        $db_qnaire = $db_interview->get_qnaire();
        $phase_mod = lib::create( 'database\modifier' );
        $phase_mod->order( 'rank' );
        
        $phase_list = $db_qnaire->get_phase_list( $phase_mod );
        if( 0 == count( $phase_list ) )
        {
          log::emerg( 'Questionnaire with no phases has been assigned.' );
        }
        else
        {
          foreach( $phase_list as $db_phase )
          {
            // let the tokens record class know which SID we are dealing with by checking if
            // there is a source-specific survey for this participant, and if not falling back
            // on the default survey
            $db_source_survey = $source_survey_class_name::get_unique_record(
              array( 'phase_id', 'source_id' ),
              array( $db_phase->id, $db_participant->source_id ) );
            $sid = is_null( $db_source_survey ) ? $db_phase->sid : $db_source_survey->sid;

            $old_sid = $tokens_class_name::get_sid();
            $tokens_class_name::set_sid( $sid );
            $tokens_mod = lib::create( 'database\modifier' );
            $tokens_class_name::where_token( $tokens_mod, $db_participant, $db_phase->repeated );
            $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );
    
            if( false === $db_tokens )
            { // token not found, create it
              $db_tokens = lib::create( 'database\limesurvey\tokens' );
              $db_tokens->token =
                $tokens_class_name::determine_token_string( $db_participant, $db_phase->repeated );
              $db_tokens->firstname = $db_participant->honorific.' '.$db_participant->first_name;
              $db_tokens->lastname = $db_participant->last_name;
              $db_tokens->email = $db_participant->email;

              if( 0 < strlen( $db_participant->other_name ) )
                $db_tokens->firstname .= sprintf( ' (%s)', $db_participant->other_name );

              // fill in the attributes
              $db_surveys = lib::create( 'database\limesurvey\surveys', $sid );
              foreach( $db_surveys->get_token_attribute_names() as $key => $value )
                $db_tokens->$key = static::get_attribute( $db_participant, $value );
              $db_tokens->save();
    
              $this->current_sid = $sid;
              $this->current_token = $db_tokens->token;
              break;
            }
            else if( 'N' == $db_tokens->completed )
            { // we have found the current phase
              $this->current_sid = $sid;
              $this->current_token = $db_tokens->token;
              break;
            }
            // else do not set the current_sid or current_token members!

            // put the old sid back
            $tokens_class_name::set_sid( $old_sid );
          }
        }

        // complete the interview
        if( false === $this->current_sid ) $db_interview->complete();
      }
    }
  }

  /**
   * Internal method to handle the withdraw script
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access private
   */
  private function process_withdraw( $db_participant )
  {
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );

    $withdraw_manager = lib::create( 'business\withdraw_manager' );

    // let the tokens record class know which SID we are dealing with by checking if
    // there is a source-specific survey for the participant, and if not falling back
    // on the default withdraw survey
    $withdraw_sid = $withdraw_manager->get_withdraw_sid( $db_participant );
    if( is_null( $withdraw_sid ) )
      throw lib::create( 'exception\runtime',
        sprintf( 'Trying to withdraw participant %s without a withdraw survey.',
                 $db_participant->uid ),
        __METHOD__ );
    $db_surveys = lib::create( 'database\limesurvey\surveys', $withdraw_sid );

    $old_sid = $tokens_class_name::get_sid();
    $tokens_class_name::set_sid( $withdraw_sid );
    $tokens_mod = lib::create( 'database\modifier' );
    $tokens_class_name::where_token( $tokens_mod, $db_participant, false );
    $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );

    if( false === $db_tokens )
    { // token not found, create it
      $db_tokens = lib::create( 'database\limesurvey\tokens' );
      $db_tokens->token = $tokens_class_name::determine_token_string( $db_participant, false );
      $db_tokens->firstname = $db_participant->first_name;
      $db_tokens->lastname = $db_participant->last_name;
      $db_tokens->email = $db_participant->email;

      if( 0 < strlen( $db_participant->other_name ) )
        $db_tokens->firstname .= sprintf( ' (%s)', $db_participant->other_name );

      // fill in the attributes
      foreach( $db_surveys->get_token_attribute_names() as $key => $value )
        $db_tokens->$key = static::get_attribute( $db_participant, $value );

      $db_tokens->save();

      $this->current_sid = $withdraw_sid;
      $this->current_token = $db_tokens->token;
    }
    else if( 'N' == $db_tokens->completed )
    {
      $this->current_sid = $withdraw_sid;
      $this->current_token = $db_tokens->token;
    }
    else // token is complete, store the survey results
    {
      $withdraw_manager->process( $db_participant );
    }

    $tokens_class_name::set_sid( $old_sid );
  }

  /**
   * Internal method to handle the proxy script
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access private
   */
  private function process_proxy( $db_participant )
  {
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );

    $setting_manager = lib::create( 'business\setting_manager' );

    // let the tokens record class know which SID we are dealing with by checking if
    // there is a source-specific survey for the participant, and if not falling back
    // on the default proxy survey
    $proxy_sid = $setting_manager->get_setting( 'general', 'proxy_survey' );
    if( is_null( $proxy_sid ) )
      throw lib::create( 'exception\runtime',
        sprintf( 'Trying to proxy participant %s without a proxy survey.',
                 $db_participant->uid ),
        __METHOD__ );
    $db_surveys = lib::create( 'database\limesurvey\surveys', $proxy_sid );
    $old_sid = $tokens_class_name::get_sid();
    $tokens_class_name::set_sid( $proxy_sid );
    
    // only create a new token if there isn't already one in cookies
    $token = array_key_exists( 'proxying_token', $_COOKIE )
           ? $_COOKIE['proxying_token']
           : $tokens_class_name::determine_token_string( $db_participant, true );
    setcookie( 'proxying_token', $token, 0, COOKIE_PATH );

    $tokens_mod = lib::create( 'database\modifier' );
    $tokens_mod->where( 'token', '=', $token );
    $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );

    if( false === $db_tokens )
    { // token not found, create it
      $db_tokens = lib::create( 'database\limesurvey\tokens' );
      $db_tokens->token = $token;
      $db_tokens->firstname = $db_participant->first_name;
      $db_tokens->lastname = $db_participant->last_name;
      $db_tokens->email = $db_participant->email;

      if( 0 < strlen( $db_participant->other_name ) )
        $db_tokens->firstname .= sprintf( ' (%s)', $db_participant->other_name );

      // fill in the attributes
      foreach( $db_surveys->get_token_attribute_names() as $key => $value )
        $db_tokens->$key = static::get_attribute( $db_participant, $value );

      $db_tokens->save();

      $this->current_sid = $proxy_sid;
      $this->current_token = $token;
    }
    else if( 'N' == $db_tokens->completed )
    {
      $this->current_sid = $proxy_sid;
      $this->current_token = $token;
    }
    // else do not set the current_sid or current_token members!

    $tokens_class_name::set_sid( $old_sid );
  }

  /**
   * Determines attributes needed at survey time.
   * TODO: this method contains many reference to CLSA-specific features which
   *       should be made generic
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @param string $key The name of the attribute to return.
   * @return mixed
   * @access public
   */
  public static function get_attribute( $db_participant, $key )
  {
    $event_type_class_name = lib::get_class_name( 'database\event_type' );
    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );

    $value = NULL;

    // check for new participant.limesurvey.* keys
    if( 1 == preg_match( '/^participant\.limesurvey\./', $key ) )
    {
      $parts = static::parse_key( $key, true );

      if( 3 != count( $parts ) )
        throw lib::create( 'exception\argument', 'key', $key, __METHOD__ );

      $sid = $parts[1];
      $q_title = $parts[2];

      // get this participant's survey for the given sid
      $old_survey_sid = $survey_class_name::get_sid();
      $survey_class_name::set_sid( $sid );
      $old_tokens_sid = $tokens_class_name::get_sid();
      $tokens_class_name::set_sid( $sid );
      $survey_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $survey_mod, $db_participant, false );
      $survey_mod->order_desc( 'datestamp' );
      $survey_list = $survey_class_name::select( $survey_mod );
      if( 0 < count( $survey_list ) )
      {
        $db_survey = current( $survey_list );
        $value = $db_survey->get_response( $q_title );
      }
      $survey_class_name::set_sid( $old_survey_sid );
      $tokens_class_name::set_sid( $old_tokens_sid );
    }
    // check for new participant.opal.* keys
    else if( 1 == preg_match( '/^participant\.opal\./', $key ) )
    {
      $parts = static::parse_key( $key, true );
      
      if( !( 4 == count( $parts ) || 5 == count( $parts ) ) )
        throw lib::create( 'exception\argument', 'key', $key, __METHOD__ );

      $datasource = $parts[1];
      $table = $parts[2];
      $variable = $parts[3];

      $setting_manager = lib::create( 'business\setting_manager' );
      $opal_url = $setting_manager->get_setting( 'opal', 'server' );
      $opal_manager = lib::create( 'business\opal_manager', $opal_url );

      if( $opal_manager->get_enabled() )
      {
        try
        {
          if( 5 == count( $parts ) )
          {
            if( 'label' == $parts[4] )
            {
              // participant.opal.<datasource>.<table>.<variable>.label (returns label) or
              // opal.<datasource>.<table>.<variable>.label (returns label)
              $value = $opal_manager->get_label(
                $datasource, $table, $variable, $value, $db_participant->get_language() );
            }
            else if( 'cache' == $parts[4] )
            {
              // participant.opal.<datasource>.<table>.<variable>.cache (caches data)
              // opal.<datasource>.<table>.<variable>.cache (caches data)

              $variable_cache_class_name = lib::get_class_name( 'database\variable_cache' );
              $variable_cache_class_name::remove_expired(); // make sure to clean-up before searching

              // get the data from the cache, or if it is missing then cache them
              $variable_cache_mod = lib::create( 'database\modifier' );
              $variable_cache_mod->where( 'variable', '=', $variable );
              $variable_cache_list = $db_participant->get_variable_cache_list( $variable_cache_mod );
              if( 0 == count( $variable_cache_list ) )
              {
                $values = $opal_manager->get_values( $datasource, $table, $db_participant );
                $variable_cache_class_name::overwrite_values( $db_participant, $values );
                $value = $values[$variable];
              }
              else
              {
                // participant.opal.<datasource>.<table>.<variable> (returns value) or
                // opal.<datasource>.<table>.<variable> (returns value)
                $value = $variable_cache_list[0]->value;
              }
            }
            else throw lib::create( 'exception\argument', 'key', $key, __METHOD__ );
          }
          else
          {
            $value = $opal_manager->get_value( $datasource, $table, $db_participant, $variable );
          }
        }
        catch( \cenozo\exception\base_exception $e )
        {
          // ignore argument exceptions (data not found in Opal) and report the rest
          if( 'notice' == $e->get_type() ) throw $e;
          else if( 'argument' != $e->get_type() ) log::warning( $e->get_message() );
        }
      }
    }
    else if( 'informant.count()' == $key )
    {
      $alternate_mod = lib::create( 'database\modifier' );
      $alternate_mod->where( 'informant', '=', true );
      $value = $db_participant->get_alternate_count( $alternate_mod );
    }
    else if( 'proxy.count()' == $key )
    {
      $alternate_mod = lib::create( 'database\modifier' );
      $alternate_mod->where( 'proxy', '=', true );
      $value = $db_participant->get_alternate_count( $alternate_mod );
    }
    else if( 'cohort' == $key )
    {
      $value = $db_participant->get_cohort()->name;
    }
    else if( 1 == preg_match( '/^collection./', $key ) )
    {
      $parts = explode( '.', $key );
      if( 2 != count( $parts ) )
        throw lib::create( 'exception\argument', 'key', $key, __METHOD__ );

      $collection_name = $parts[1];
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'collection.name', '=', $collection_name );
      $value = 0 < $db_participant->get_collection_count( $modifier ) ? 1 : 0;
    }
    else if( 'phone.1.number' == $key )
    {
      $modifier = lib::create( 'database\modifier' );
      $modifier->where( 'rank', '=', 1 );
      $phone_list = $db_participant->get_phone_list( $modifier );
      $db_phone = current( $phone_list );
      $value = is_null( $db_phone ) ? '' : $db_phone->number;
    }
    else if( 'uid' == $key )
    {
      $value = $db_participant->uid;
    }
    else if( 'site' == $key )
    {
      $db_site = $db_participant->get_effective_site();
      $value = is_null( $db_site ) ? 'none' : $db_site->name;
    }
    else if( false !== strpos( $key, 'address' ) )
    {
      $db_address = $db_participant->get_primary_address();
      
      if( 'address street' == $key )
      {
        if( $db_address )
        {
          $value = $db_address->address1;
          if( !is_null( $db_address->address2 ) ) $value .= ' '.$db_address->address2;
        }
        else
        {
          $value = '';
        }
      }
      else if( 'address city' == $key )
      {
        $value = $db_address ? $db_address->city : '';
      }
      else if( 'address province' == $key )
      {
        $value = $db_address ? $db_address->get_region()->name : '';
      }
      else if( 'address postal code' == $key )
      {
        $value = $db_address ? $db_address->postcode : '';
      }
    }
    else if( 'age' == $key )
    {
      $value = strlen( $db_participant->date_of_birth )
                  ? util::get_interval(
                      util::get_datetime_object( $db_participant->date_of_birth ) )->y
                  : '';
    }
    else if( 'written consent received' == $key )
    {
      $consent_mod = lib::create( 'database\modifier' );
      $consent_mod->where( 'written', '=', true );
      $value = 0 < $db_participant->get_consent_count( $consent_mod ) ? '1' : '0';
    }
    else if( 'consented to provide HIN' == $key )
    {
      $db_hin = $db_participant->get_hin();
      if( is_null( $db_hin ) ) $value = -1;
      else $value = 1 == $db_hin->access ? 1 : 0;
    }
    else if( 'provided data' == $key )
    {
      if( 'comprehensive' == $db_participant->get_cohort()->name )
      {
        // comprehensive participants have provided data once their first interview is done
        $event_mod = lib::create( 'database\modifier' );
        $event_mod->where( 'event_type_id', '=',
          $event_type_class_name::get_unique_record( 'name', 'completed (Baseline Home)' )->id );
        
        $event_list = $db_participant->get_event_list( $event_mod );
        $provided_data = 0 < count( $event_list ) ? 'yes' : 'no';
      }
      else
      {
        $provided_data = 'no';

        // start by seeing if the participant has completed the baseline interview
        $event_mod = lib::create( 'database\modifier' );
        $event_mod->where( 'event_type_id', '=',
          $event_type_class_name::get_unique_record( 'name', 'completed (Baseline)' )->id );
        
        $event_list = $db_participant->get_event_list( $event_mod );
        if( 0 < count( $event_list ) ) $provided_data = 'yes';
        else
        { // if the interview was never completed, see if it was partially completed
          $interview_mod = lib::create( 'database\modifier' );
          $interview_mod->order( 'qnaire.rank' );
          $interview_list = $db_participant->get_interview_list( $interview_mod );
          if( 0 < count( $interview_list ) )
          {
            $phase_mod = lib::create( 'database\modifier' );
            $phase_mod->where( 'repeated', '=', 0 );
            $phase_mod->order( 'rank' );
            $db_last_interview = current( $interview_list );
            $phase_list = $db_last_interview->get_qnaire()->get_phase_list( $phase_mod );
            if( 0 < count( $phase_list ) )
            {
              // see if a survey exists for this phase
              // if one does then the participant has provided partial data
              $db_phase = current( $phase_list );
              $old_survey_sid = $survey_class_name::get_sid();
              $survey_class_name::set_sid( $db_phase->sid );
              $old_tokens_sid = $tokens_class_name::get_sid();
              $tokens_class_name::set_sid( $db_phase->sid );
              $survey_mod = lib::create( 'database\modifier' );
              $tokens_class_name::where_token( $survey_mod, $db_participant, false );
              if( 0 < $survey_class_name::count( $survey_mod ) ) $provided_data = 'partial';
              $survey_class_name::set_sid( $old_survey_sid );
              $tokens_class_name::set_sid( $old_tokens_sid );
            }
          }
        }
      }

      $value = $provided_data;
    }
    else if( 'DCS samples' == $key )
    {
      // get data from Opal
      $setting_manager = lib::create( 'business\setting_manager' );
      $opal_url = $setting_manager->get_setting( 'opal', 'server' );
      $opal_manager = lib::create( 'business\opal_manager', $opal_url );
      
      $value = 0;

      if( $opal_manager->get_enabled() && 'comprehensive' == $db_participant->get_cohort()->name )
      {
        try
        {
          $blood = $opal_manager->get_value(
            'clsa-dcs', 'Phlebotomy', $db_participant, 'AGREE_BS' );
          $urine = $opal_manager->get_value(
            'clsa-dcs', 'Phlebotomy', $db_participant, 'AGREE_URINE' );

          $value = 0 == strcasecmp( 'yes', $blood ) ||
                        0 == strcasecmp( 'yes', $urine )
                      ? 1 : 0;
        }
        catch( \cenozo\exception\base_exception $e )
        {
          // ignore argument exceptions (data not found in Opal) and report the rest
          if( 'notice' == $e->get_type() ) throw $e;
          else if( 'argument' != $e->get_type() ) log::warning( $e->get_message() );
        }
      }
    }
    else if( false !== strpos( $key, 'marital status' ) )
    {
      // get data from Opal
      $setting_manager = lib::create( 'business\setting_manager' );
      $opal_url = $setting_manager->get_setting( 'opal', 'server' );
      $opal_manager = lib::create( 'business\opal_manager', $opal_url );
      
      $value = 'MISSING';

      if( $opal_manager->get_enabled() )
      {
        try
        {
          $db_cohort = $db_participant->get_cohort();
          $datasource = 'comprehensive' == $db_cohort->name ? 'clsa-inhome' : 'clsa-cati';
          $table = 'comprehensive' == $db_cohort->name
                 ? 'InHome_1'
                 : 'Tracking Baseline Main Script';
          $variable = 'comprehensive' == $db_cohort->name ? 'SDC_MRTL_COM' : 'SDC_MRTL_TRM';
          $value = $opal_manager->get_value( $datasource, $table, $db_participant, $variable );

          // return the label instead of the value, if requested
          if( 'marital status label' == $key )
            $value = $opal_manager->get_label(
              $datasource, $table, $variable, $value, $db_participant->get_language() );
        }
        catch( \cenozo\exception\base_exception $e )
        {
          // ignore argument exceptions (data not found in Opal) and report the rest
          if( 'notice' == $e->get_type() ) throw $e;
          else if( 'argument' != $e->get_type() ) log::warning( $e->get_message() );
        }
      }
    }
    else if( 'parkinsonism' == $key )
    {
      // get data from Opal
      $setting_manager = lib::create( 'business\setting_manager' );
      $opal_url = $setting_manager->get_setting( 'opal', 'server' );
      $opal_manager = lib::create( 'business\opal_manager', $opal_url );
      
      $value = 'NO';

      if( $opal_manager->get_enabled() )
      {
        try
        {
          $db_cohort = $db_participant->get_cohort();
          $datasource = 'comprehensive' == $db_cohort->name ? 'clsa-dcs' : 'clsa-cati';
          $table = 'comprehensive' == $db_cohort->name
                 ? 'DiseaseSymptoms'
                 : 'Tracking Baseline Main Script';
          $variable = 'comprehensive' == $db_cohort->name ? 'CCC_PARK_DCS' : 'CCT_PARK_TRM';
          $value = $opal_manager->get_value(
            $datasource, $table, $db_participant, $variable );
        }
        catch( \cenozo\exception\base_exception $e )
        {
          // ignore argument exceptions (data not found in Opal) and report the rest
          if( 'notice' == $e->get_type() ) throw $e;
          else if( 'argument' != $e->get_type() ) log::warning( $e->get_message() );
        }
      }
    }
    else if( 'operator first_name' == $key || 'user first_name' == $key )
    {
      $db_user = lib::create( 'business\session' )->get_user();
      $value = $db_user->first_name;
    }
    else if( 'operator last_name' == $key || 'user last_name' == $key )
    {
      $db_user = lib::create( 'business\session' )->get_user();
      $value = $db_user->last_name;
    }
    else if( 'last interview date' == $key || 'last completed interview' == $key )
    {
      $event_mod = lib::create( 'database\modifier' );
      $event_mod->order_desc( 'datetime' );
      $event_mod->where_bracket( true );
      $event_mod->where( 'event_type_id', '=',
        $event_type_class_name::get_unique_record( 'name', 'completed (Baseline)' )->id );
      $event_mod->or_where( 'event_type_id', '=',
        $event_type_class_name::get_unique_record( 'name', 'completed (Baseline Site)' )->id );
      $event_mod->where_bracket( false );
      
      $event_list = $db_participant->get_event_list( $event_mod );
      $db_event = 0 < count( $event_list ) ? current( $event_list ) : NULL;
      $value = is_null( $db_event )
                  ? 'DATE UNKNOWN'
                  : util::get_formatted_date( $db_event->datetime );
    }
    else if( false !== strpos( $key, 'alternate' ) )
    {
      $alternate_list = $db_participant->get_alternate_list();

      $matches = array(); // for pregs below
      if( 'number of alternate contacts' == $key )
      {
        $value = count( $alternate_list );
      }
      else if(
        preg_match( '/alternate([0-9]+) (first_name|last_name|phone)/', $key, $matches ) )
      {
        $alt_number = intval( $matches[1] );
        $aspect = $matches[2];

        if( count( $alternate_list ) < $alt_number )
        {
          $value = '';
        }
        else
        {
          if( 'phone' == $aspect )
          {
            $phone_list = $alternate_list[$alt_number - 1]->get_phone_list();
            $value = is_array( $phone_list ) ? $phone_list[0]->number : '';
          }
          else
          {
            $value = $alternate_list[$alt_number - 1]->$aspect;
          }
        }
      }
    }

    return $value;
  }
  
  /**
   * Parse the key used to identify which data value to return
   * 
   * This is code from Cenozo2 which has been back-ported in order to make the participant.opal. attributes
   * work correctly.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $key The key string defining which data to return
   * @return array
   * @access protected
   */
  protected static function parse_key( $key, $remove_participant = false )
  {
    // two consecutive periods (..) is an escaped .
    $key = str_replace( '..', chr( 37 ), $key );

    // split the key into table/column parts then replace the escaped char back into a .
    $parts = explode( '.', $key );
    if( 2 > count( $parts ) )
      throw lib::create( 'exception\argument', 'key', $key, __METHOD__ );
    foreach( $parts as $index => $part ) $parts[$index] = str_replace( chr( 37 ), '.', $part );

    // All keys used to return participant values may be prepended with "participant."
    // If $remove_participant is true then remove it, but only if there are more than 2 parts
    // to the key
    if( $remove_participant && 'participant' == $parts[0] && 2 < count( $parts ) )
      array_shift( $parts );

    return $parts;
  }

  /**
   * This assignment's current sid
   * @var int
   * @access private
   */
  private $current_sid = NULL;
  
  /**
   * This assignment's current token
   * @var string
   * @access private
   */
  private $current_token = NULL;
}
