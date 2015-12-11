#!/usr/bin/php
<?php
/**
 * This is a special script used when upgrading to version 1.3.5
 * This script should be run once either before or after running patch_database.sql
 * It converts all tokens from interview-based to uid-based
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

ini_set( 'display_errors', '1' );
error_reporting( E_ALL | E_STRICT );
ini_set( 'date.timezone', 'US/Eastern' );

// utility functions
function out( $msg ) { printf( '%s: %s'."\n", date( 'Y-m-d H:i:s' ), $msg ); }
function error( $msg ) { out( sprintf( 'ERROR! %s', $msg ) ); }

class patch
{
  public function add_settings( $settings, $replace = false )
  {
    if( $replace )
    {
      $this->settings = $settings;
    }
    else
    {
      foreach( $settings as $category => $setting )
      {
        if( !array_key_exists( $category, $this->settings ) )
        {
          $this->settings[$category] = $setting;
        }
        else
        {
          foreach( $setting as $key => $value )
            if( !array_key_exists( $key, $this->settings[$category] ) )
              $this->settings[$category][$key] = $value;
        }
      }
    }
  }

  public function execute()
  {
    $error_count = 0;
    $file_count = 0;

    out( 'Reading configuration parameters' );
    // fake server parameters
    $_SERVER['HTTPS'] = false;
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '';

    require_once '../../../web/settings.ini.php';
    require_once '../../../web/settings.local.ini.php';

    // include the application's initialization settings
    global $SETTINGS;
    $this->add_settings( $SETTINGS, true );
    unset( $SETTINGS );

    // include the framework's initialization settings
    require_once $this->settings['path']['CENOZO'].'/app/settings.local.ini.php';
    $this->add_settings( $settings );
    require_once $this->settings['path']['CENOZO'].'/app/settings.ini.php';
    $this->add_settings( $settings );

    if( !array_key_exists( 'general', $this->settings ) ||
        !array_key_exists( 'application_name', $this->settings['general'] ) )
      die( 'Error, application name not set!' );

    define( 'APPNAME', $this->settings['general']['application_name'] );
    define( 'SERVICENAME', $this->settings['general']['service_name'] );
    $this->settings['path']['CENOZO_API'] = $this->settings['path']['CENOZO'].'/api';
    $this->settings['path']['CENOZO_TPL'] = $this->settings['path']['CENOZO'].'/tpl';

    $this->settings['path']['API'] = $this->settings['path']['APPLICATION'].'/api';
    $this->settings['path']['DOC'] = $this->settings['path']['APPLICATION'].'/doc';
    $this->settings['path']['TPL'] = $this->settings['path']['APPLICATION'].'/tpl';

    // the web directory cannot be extended
    $this->settings['path']['WEB'] = $this->settings['path']['CENOZO'].'/web';

    foreach( $this->settings['path'] as $path_name => $path_value )
      define( $path_name.'_PATH', $path_value );
    foreach( $this->settings['url'] as $path_name => $path_value )
      define( $path_name.'_URL', $path_value );

    // get the survey database settings from the limesurvey config file
    $file = LIMESURVEY_PATH.'/config.php';
    if( file_exists( $file ) )
    {
      include $file;
      $this->settings['survey_db'] =
        array( 'driver' => $databasetype,
               'server' => $databaselocation,
               'username' => $databaseuser,
               'password' => $databasepass,
               'database' => $databasename,
               'prefix' => $dbprefix );
    }
    else // no version 1.92 of the config file, try version 2.0
    {
      $file = LIMESURVEY_PATH.'/application/config/config.php';

      if( file_exists( $file ) )
      {
        define( 'BASEPATH', '' ); // needed to read the config file
        $config = require( $file );
        $db = explode( ';', $config['components']['db']['connectionString'] );

        $parts = explode( ':', $db[0], 2 );
        $driver = current( $parts );
        $parts = explode( '=', $db[0], 2 );
        $server = next( $parts );
        $parts = explode( '=', $db[2], 2 );
        $database = next( $parts );

        $this->settings['survey_db'] =
          array( 'driver' => $driver,
                 'server' => $server,
                 'username' => $config['components']['db']['username'],
                 'password' => $config['components']['db']['password'],
                 'database' => $database,
                 'prefix' => $config['components']['db']['tablePrefix'] );
      }
      else throw lib::create( 'exception\runtime',
        'Cannot find limesurvey config.php file.', __METHOD__ );
    }

    // determine the database names for the framework, application and limesurvey
    $cenozo_database_name = sprintf(
      '%s%s',
      $this->settings['db']['database_prefix'],
      $this->settings['general']['framework_name'] );
    $sabretooth_database_name = sprintf(
      '%s%s',
      $this->settings['db']['database_prefix'],
      $this->settings['general']['service_name'] );
    $limesurvey_database_name = $this->settings['survey_db']['database'];

    // open connection to the database
    out( 'Connecting to database' );
    require_once $this->settings['path']['ADODB'].'/adodb.inc.php';
    $db = ADONewConnection( $this->settings['db']['driver'] );
    $db->SetFetchMode( ADODB_FETCH_ASSOC );

    $result = $db->Connect( $this->settings['db']['server'],
                            $this->settings['db']['username'],
                            $this->settings['db']['password'],
                            $sabretooth_database_name );
    if( false === $result )
    {
      error( 'Unable to connect, quitting' );
      die();
    }

    // get a list of all SIDs used by this instance
    $result = $db->GetAll(
      'SELECT DISTINCT sid, repeated FROM ( '.
        'SELECT DISTINCT sid, repeated FROM phase UNION '.
        'SELECT DISTINCT source_survey.sid, repeated FROM source_survey '.
        'JOIN phase ON source_survey.phase_id = phase.id '.
      ') AS sids' );
    if( false === $result )
    {
      error( 'Unable to get list of surveys in use, quitting' );
      die();
    }

    foreach( $result as $row )
    {
      out( sprintf( 'Creating token index in survey_%s', $row['sid'] ) );

      $sql = sprintf(
        'SELECT COUNT(*) '.
        'FROM information_schema.statistics '.
        'WHERE table_schema = "%s" '.
        'AND table_name = "survey_%s" '.
        'AND column_name = "token"',
        $limesurvey_database_name,
        $row['sid'] );

      $count = $db->GetOne( $sql );
      if( false === $count )
      {
        error( 'Problem testing for token index, quitting' );
        die();
      }

      if( 0 == $count )
      {
        $sql = sprintf(
          'ALTER TABLE %s.survey_%s ADD KEY tokens( token )',
          $limesurvey_database_name,
          $row['sid'] );

        if( false === $db->Execute( $sql ) )
        {
          error( 'Problem creating token index, quitting' );
          die();
        }
      }
        
      if( $row['repeated'] )
      {
        out( sprintf( 'Creating temporary tables for repeating script %s', $row['sid'] ) );

        $db->Execute( 'DROP TABLE IF EXISTS temp' );
        $sql =
          'CREATE TEMPORARY TABLE temp( '.
             'token varchar(36) NOT NULL, '.
             'interview_id int(11) NOT NULL, '.
             'assignment_id int(11) NOT NULL, '.
             'counter char(7) NOT NULL, '.
             'INDEX dk_token ( token DESC ), '.
             'INDEX dk_interview_id_assignment_id ( interview_id DESC, assignment_id DESC ) '.
           ')';

        if( false === $db->Execute( $sql ) )
        {
          error( 'There was a problem creating the first temporary table, quitting' );
          die();
        }

        $sql = sprintf(
          'INSERT INTO temp( token, interview_id, assignment_id, counter ) '.
          'SELECT token, '.
                 'SUBSTRING( token, 1, LOCATE( "_", token )-1 ), '.
                 'SUBSTRING( token, locate( "_", token )+1 ), '.
                 '"0000000" '.
          'FROM %s.tokens_%s '.
          'WHERE token REGEXP "^[0-9]+_[1-9][0-9]*$"',
          $limesurvey_database_name,
          $row['sid'] );

        if( false === $db->Execute( $sql ) )
        {
          error( 'There was a problem populating the first temporary table, quitting' );
          error( $sql );
          die();
        }

        $db->Execute( 'DROP TABLE IF EXISTS counter' );
        $sql =
          'CREATE TEMPORARY TABLE counter '.
          'SELECT token, @n := IF( @last != interview_id, 1, @n + 1 ) c, @last := interview_id, assignment_id '.
          'FROM ( SELECT @n:=0, @last:=0 ) vars, temp '.
          'ORDER BY interview_id, assignment_id';

        if( false === $db->Execute( $sql ) )
        {
          error( 'There was a problem creating the second temporary table, quitting' );
          die();
        }

        $sql = 'UPDATE temp JOIN counter USING( token ) SET temp.counter = LPAD( counter.c, 7, "0" )';

        if( false === $db->Execute( $sql ) )
        {
          error( 'There was a problem updating the first temporary table, quitting' );
          die();
        }

        out( sprintf( 'Converting tokens in tokens_%s (repeating)', $row['sid'] ) );

        $sql = sprintf(
          'UPDATE %s.tokens_%s tokens '.
          'JOIN temp ON tokens.token = temp.token '.
          'JOIN %s.interview ON temp.interview_id = interview.id '.
          'JOIN %s.participant ON interview.participant_id = participant.id '.
          'SET tokens.token = CONCAT( participant.uid, "_", temp.counter )',
          $limesurvey_database_name,
          $row['sid'],
          $sabretooth_database_name,
          $cenozo_database_name );

        if( false === $db->Execute( $sql ) )
        {
          error( 'Problem updating tokens, quitting' );
          die();
        }

        out( sprintf( 'Finished converting tokens_%s, %s rows affected', $row['sid'], $db->Affected_Rows() ) );
        out( sprintf( 'Converting tokens in survey_%s (repeating)', $row['sid'] ) );

        $sql = sprintf(
          'UPDATE %s.survey_%s tokens '.
          'JOIN temp ON tokens.token = temp.token '.
          'JOIN %s.interview ON temp.interview_id = interview.id '.
          'JOIN %s.participant ON interview.participant_id = participant.id '.
          'SET tokens.token = CONCAT( participant.uid, "_", temp.counter )',
          $limesurvey_database_name,
          $row['sid'],
          $sabretooth_database_name,
          $cenozo_database_name );

        if( false === $db->Execute( $sql ) )
        {
          error( 'Problem updating tokens, quitting' );
          die();
        }

        out( sprintf( 'Finished converting survey_%s, %s rows affected', $row['sid'], $db->Affected_Rows() ) );
      }
      else
      {
        out( sprintf( 'Converting tokens in tokens_%s (non-repeating)', $row['sid'] ) );

        $sql = sprintf(
          'UPDATE %s.tokens_%s '.
          'JOIN %s.interview ON SUBSTRING( token, 1, LOCATE( "_", token )-1 ) = interview.id '.
          'JOIN %s.participant ON interview.participant_id = participant.id '.
          'SET token = participant.uid '.
          'WHERE token REGEXP "^[0-9]+_0$"',
          $limesurvey_database_name,
          $row['sid'],
          $sabretooth_database_name,
          $cenozo_database_name );

        if( false === $db->Execute( $sql ) )
        {
          error( 'Problem updating tokens, quitting' );
          die();
        }

        out( sprintf( 'Finished converting tokens_%s, %s rows affected', $row['sid'], $db->Affected_Rows() ) );
        out( sprintf( 'Converting tokens in survey_%s (non-repeating)', $row['sid'] ) );

        $sql = sprintf(
          'UPDATE %s.survey_%s '.
          'JOIN %s.interview ON SUBSTRING( token, 1, LOCATE( "_", token )-1 ) = interview.id '.
          'JOIN %s.participant ON interview.participant_id = participant.id '.
          'SET token = participant.uid '.
          'WHERE token REGEXP "^[0-9]+_0$"',
          $limesurvey_database_name,
          $row['sid'],
          $sabretooth_database_name,
          $cenozo_database_name );

        if( false === $db->Execute( $sql ) )
        {
          error( 'Problem updating tokens, quitting' );
          die();
        }

        out( sprintf( 'Finished converting surveys_%s, %s rows affected', $row['sid'], $db->Affected_Rows() ) );
      }
    }
  }
}

$patch = new patch();
$patch->execute();
