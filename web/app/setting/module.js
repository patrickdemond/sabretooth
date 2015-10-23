define( cenozo.getDependencyList( 'setting' ), function() {
  'use strict';

  var module = cenozoApp.module( 'setting' );
  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'site',
        column: 'site_id',
        friendly: 'site'
      }
    },
    name: {
      singular: 'setting',
      plural: 'settings',
      possessive: 'setting\'s',
      pluralPossessive: 'settings\''
    },
    inputList: {
      site: {
        column: 'site.name',
        title: 'Site',
        type: 'string',
        constant: true
      },
      survey_without_sip: {
        title: 'Allow No-Call Interviewing',
        type: 'boolean',
        help: 'Allow operators to interview participants without being in an active VoIP call'
      },
      calling_start_time: {
        title: 'Earliest Call Time',
        type: 'time',
        help: 'The earliest time to assign participants (in their local time)'
      },
      calling_end_time: {
        title: 'Latest Call Time',
        type: 'time',
        help: 'The latest time to assign participants (in their local time)'
      },
      short_appointment: {
        title: 'Short Appointment Length',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'The length of time, in minutes, of a short appointment'
      },
      long_appointment: {
        title: 'Long Appointment Length',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'The length of time, in minutes, of a long appointment'
      },
      pre_call_window: {
        title: 'Pre-Appointment Window',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes before an appointment or callback that a participant can be assigned'
      },
      post_call_window: {
        title: 'Post-Appointment Window',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after an appointment before it is considered missed'
      },
      contacted_wait: {
        title: 'Contacted Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "contacted" call result to allow a participant to be called'
      },
      busy_wait: {
        title: 'Busy Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "busy" call result to allow a participant to be called'
      },
      fax_wait: {
        title: 'Fax Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "fax" call result to allow a participant to be called'
      },
      no_answer_wait: {
        title: 'No Answer Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "no answer" call result to allow a participant to be called'
      },
      not_reached_wait: {
        title: 'Not Reached Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "not reached" call result to allow a participant to be called'
      },
      hang_up_wait: {
        title: 'Hang Up Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "hang up" call result to allow a participant to be called'
      },
      soft_refusal_wait: {
        title: 'Soft Refusal Wait',
        type: 'string',
        format: 'integer',
        minValue: 0,
        help: 'How many minutes after a "soft refusal" call result to allow a participant to be called'
      }
    },
    columnList: {
      site: {
        column: 'site.name',
        title: 'Site'
      },
      survey_without_sip: {
        title: 'No-Call',
        type: 'boolean'
      },
      calling_start_time: {
        title: 'Start Call',
        type: 'time'
      },
      calling_end_time: {
        title: 'End Call',
        type: 'time'
      },
      short_appointment: {
        title: 'Short Ap.',
        type: 'number'
      },
      long_appointment: {
        title: 'Long Ap.',
        type: 'number'
      },
      pre_call_window: {
        title: 'Pre-Call',
        type: 'number'
      },
      post_call_window: {
        title: 'Post-Call',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'site',
      reverse: false
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.controller( 'SettingListCtrl', [
    '$scope', 'CnSettingModelFactory', 'CnSession',
    function( $scope, CnSettingModelFactory, CnSession ) {
      $scope.model = CnSettingModelFactory.root;
      $scope.model.listModel.onList( true ).then( function() {
        $scope.model.setupBreadcrumbTrail( 'list' );
      } ).catch( CnSession.errorHandler );
    }
  ] );

  cenozo.providers.controller( 'SettingViewCtrl', [
    '$scope', 'CnSettingModelFactory', 'CnSession',
    function( $scope, CnSettingModelFactory, CnSession ) {
      $scope.model = CnSettingModelFactory.root;
      $scope.model.viewModel.onView().then( function() {
        $scope.model.setupBreadcrumbTrail( 'view' );
      } ).catch( CnSession.errorHandler );
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnSettingView', function () {
    return {
      templateUrl: 'app/setting/view.tpl.html',
      restrict: 'E'
    };
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnSettingListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnSettingViewFactory',
    cenozo.getViewModelInjectionList( 'setting' ).concat( function() {
      var args = arguments;
      var CnBaseViewFactory = args[0];
      var object = function( parentModel ) { CnBaseViewFactory.construct( this, parentModel, args ); }
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    } )
  );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnSettingModelFactory', [
    '$state', 'CnBaseModelFactory', 'CnSettingListFactory', 'CnSettingViewFactory',
    function( $state, CnBaseModelFactory, CnSettingListFactory, CnSettingViewFactory ) {
      var object = function() {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnSettingListFactory.instance( this );
        this.viewModel = CnSettingViewFactory.instance( this );
      };

      return {
        root: new object(),
        instance: function() { return new object(); }
      };
    }
  ] );

} );
