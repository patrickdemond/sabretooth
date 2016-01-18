<?php
/**
 * withdraw_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace sabretooth\business;
use cenozo\lib, cenozo\log, sabretooth\util;

/**
 * withdraw_manager: record
 */
class withdraw_manager extends \cenozo\singleton
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
   * Returns the survey id of the withdraw script used to withdraw this participant
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access public
   */
  public function get_withdraw_sid( $db_participant )
  {
    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $source_withdraw_class_name = lib::get_class_name( 'database\source_withdraw' );

    if( !array_key_exists( $db_participant->id, $this->withdraw_sid_list )  )
    {
      $db_qnaire = $db_participant->get_effective_qnaire();
      if( is_null( $db_qnaire ) ) 
      { // finished all qnaires, find the last one completed
        $db_assignment = $db_participant->get_last_finished_assignment();
        if( !is_null( $db_assignment ) ) 
        {   
          $db_qnaire = $db_assignment->get_interview()->get_qnaire();
        }   
        else
        { // no interview means we'll just use the first qnaire
          $db_qnaire = $qnaire_class_name::get_unique_record( 'rank', 1 );
     
          if( is_null( $db_qnaire ) )
            throw lib::create( 'exception\runtime',
                               'Trying to withdraw participant without a questionnaire.',
                               __METHOD__ );
        }   
      }

      $withdraw_sid = NULL;
      if( !is_null( $db_qnaire ) )
      {
        // check for a source-specific withdraw survey for this participant
        $db_source_withdraw = $source_withdraw_class_name::get_unique_record(
          array( 'qnaire_id', 'source_id' ),
          array( $db_qnaire->id, $db_participant->source_id ) );
        $withdraw_sid = is_null( $db_source_withdraw )
                      ? $db_qnaire->withdraw_sid
                      : $db_source_withdraw->sid;
      }
      $this->withdraw_sid_list[$db_participant->id] = $withdraw_sid;
    }

    return $this->withdraw_sid_list[$db_participant->id];
  }

  /**
   * Removes the participant's withdraw script token and survey
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access public
   */
  public function remove_withdraw( $db_participant )
  {
    $withdraw_sid = $this->get_withdraw_sid( $db_participant );

    if( !is_null( $withdraw_sid ) )
    {   
      $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
      $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );

      $old_tokens_sid = $tokens_class_name::get_sid();
      $tokens_class_name::set_sid( $withdraw_sid );
      $old_survey_sid = $survey_class_name::get_sid();
      $survey_class_name::set_sid( $withdraw_sid );

      // delete the script
      $tokens_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $tokens_mod, $db_participant, false );
      foreach( $tokens_class_name::select( $tokens_mod ) as $db_tokens ) $db_tokens->delete();

      // delete the token
      $survey_mod = lib::create( 'database\modifier' );
      $tokens_class_name::where_token( $survey_mod, $db_participant, false );
      foreach( $survey_class_name::select( $survey_mod ) as $db_survey ) $db_survey->delete();

      $tokens_class_name::set_sid( $old_tokens_sid );
      $survey_class_name::set_sid( $old_survey_sid );
    }   

    $db_participant->withdraw_letter = NULL;
    $db_participant->save();
  }

  /**
   * Processes the withdraw script of a participant who has been fully withdrawn and
   * sets the participant.withdraw_letter column based on their answers.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access public
   */
  public function process( $db_participant )
  {
    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );

    // get the withdraw survey record
    $withdraw_sid = $this->get_withdraw_sid( $db_participant );
    $db_surveys = lib::create( 'database\limesurvey\surveys', $withdraw_sid );

    // set the SID for the the survey and tokens records
    $old_tokens_sid = $tokens_class_name::get_sid();
    $tokens_class_name::set_sid( $withdraw_sid );
    $old_survey_sid = $survey_class_name::get_sid();
    $survey_class_name::set_sid( $withdraw_sid );

    // get the withdraw token
    $tokens_mod = lib::create( 'database\modifier' );
    $tokens_class_name::where_token( $tokens_mod, $db_participant, false );
    $tokens_list = $tokens_class_name::select( $tokens_mod );
    if( 0 == count( $tokens_list ) )
      throw lib::create( 'exception\runtime',
        sprintf( 'Tried to process withdraw for participant %s without a token.',
                 $db_participant->uid ),
        __METHOD__ );
    $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );

    // figure out which token attributes are which
    $attributes = array();
    foreach( $db_surveys->get_token_attribute_names() as $key => $value )
      $attributes[$value] = $db_tokens->$key;

    // participants who did not provide data get an empty string
    if( 'no' == $attributes['provided data'] )
    {
      $letter_type = '0';
    }
    else
    {
      if( 0 == $attributes['written consent received'] )
        $letter_type = 0 < $attributes['consented to provide HIN'] ? 'q' : 'r';
      else // written consent was received, write the letter type to the database
      {   
        if( 'partial' == $attributes['provided data'] )
          $letter_type = 0 < $attributes['consented to provide HIN'] ? 's' : 't';
        else // full data received
        {   
          if( 'comprehensive' == $db_participant->get_cohort()->name &&  
              $attributes['last interview date'] == 'DATE UNKNOWN' ) // in-home only
            $letter_type = 0 < $attributes['consented to provide HIN'] ? 'o' : 'p';
          else // not in-home only
          {   
            // from here we need to know whether default was applied or not
            $survey_mod = lib::create( 'database\modifier' );
            $tokens_class_name::where_token( $survey_mod, $db_participant, false );
            $survey_list = $survey_class_name::select( $survey_mod );
            $db_survey = current( $survey_list );
 
            // get the code for the def and opt responses
            $code = 0 < $attributes['consented to provide HIN'] ? 'HIN' : 'NO_HIN';
            $code .= 0 < $attributes['DCS samples'] ? '_SAMP' : '_NO_SAMP';

            $response = array();
            $response['start'] = $db_survey->get_response( 'WTD_START' );
            $response['def'] = $db_survey->get_response( 'WTD_DEF_'.$code );
            $response['opt'] = $db_survey->get_response( 'WTD_OPT_'.$code );

            // the default option was applied if...
            if( 'REFUSED' == $response['start'] ||
                'YES' == $response['def'] ||
                'REFUSED' == $response['def'] ||
                'REFUSED' == $response['opt'] )
            {   
              if( 1 == $attributes['DCS samples'] )
                $letter_type = 0 < $attributes['consented to provide HIN'] ? 'k' : 'm';
              else
                $letter_type = 0 < $attributes['consented to provide HIN'] ? 'l' : 'n';
            }   
            else
            {
              if( 'OPTION1' == $response['opt'] )
              {
                if( 1 == $attributes['DCS samples'] )
                  $letter_type = 0 < $attributes['consented to provide HIN'] ? 'a' : 'c';
                else
                  $letter_type = 0 < $attributes['consented to provide HIN'] ? 'b' : 'd';
              }
              else if( 'OPTION2' == $response['opt'] )
              {
                if( 1 == $attributes['DCS samples'] )
                  $letter_type = 0 < $attributes['consented to provide HIN'] ? 'e' : 'g';
                else
                  $letter_type = 0 < $attributes['consented to provide HIN'] ? 'f' : 'h';
              }
              else // must be OPTION3
              {
                // NOTE: to get option 3 participants must have provided HIN
                $letter_type = 1 == $attributes['DCS samples'] ? 'i' : 'j';
              }
            }
          }
        }
      }
    }

    // now write the letter type for future reference
    $db_participant->withdraw_letter = $letter_type;
    $db_participant->save();
      
    $tokens_class_name::set_sid( $old_tokens_sid );
    $survey_class_name::set_sid( $old_survey_sid );
  }

  /**
   * A cache of withdraw SIDs by participant
   * @var array( database\qnaire )
   * @access private
   */
  private $withdraw_sid_list = array();
}
