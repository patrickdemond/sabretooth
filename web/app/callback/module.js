define( function() {
  'use strict';

  try { cenozoApp.module( 'callback', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( cenozoApp.module( 'callback' ), {
    identifier: {
      parent: {
        subject: 'interview',
        column: 'interview_id',
        friendly: 'qnaire'
      }
    },
    name: {
      singular: 'callback',
      plural: 'callbacks',
      possessive: 'callback\'s',
      pluralPossessive: 'callbacks\''
    },
    columnList: {
      datetime: {
        type: 'datetime',
        title: 'Date & Time'
      },
      phone: {
        column: 'phone.name',
        type: 'string',
        title: 'Phone Number'
      },
      assignment_user: {
        column: 'assignment_user.name',
        type: 'string',
        title: 'Assigned to'
      },
      state: {
        type: 'string',
        title: 'State',
        help: 'One of reached, not reached, upcoming, assignable, missed, incomplete, assigned or in progress'
      }
    },
    defaultOrder: {
      column: 'datetime',
      reverse: true
    }
  } );

  cenozoApp.module( 'callback' ).addInputGroup( null, {
    datetime: {
      title: 'Date & Time',
      type: 'datetime',
      min: 'now',
      help: 'Cannot be changed once the callback has passed.'
    },
    participant: {
      column: 'participant.uid',
      title: 'Participant',
      type: 'string',
      constant: true
    },
    qnaire: {
      column: 'script.name',
      title: 'Questionnaire',
      type: 'string',
      constant: true
    },
    phone_id: {
      title: 'Phone Number',
      type: 'enum',
      help: 'Which number should be called for the callback, or leave this field blank if any of the ' +
            'participant\'s phone numbers can be called.'
    },
    assignment_user: {
      column: 'assignment_user.name',
      title: 'Assigned to',
      type: 'string',
      constant: true,
      help: 'This will remain blank until the callback has been assigned. The assigned user can only be ' +
            ' different from the reserved user when the callback was missed.'
    },
    state: {
      title: 'State',
      type: 'string',
      constant: true,
      help: 'One of reached, not reached, upcoming, assignable, missed, incomplete, assigned or in progress'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.controller( 'CallbackAddCtrl', [
    '$scope', 'CnCallbackModelFactory',
    function( $scope, CnCallbackModelFactory ) {
      $scope.model = CnCallbackModelFactory.root;
      $scope.record = {};
      $scope.model.addModel.onNew( $scope.record ).then( function() {
        $scope.model.setupBreadcrumbTrail( 'add' );
      } );
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.controller( 'CallbackListCtrl', [
    '$scope', 'CnCallbackModelFactory',
    function( $scope, CnCallbackModelFactory ) {
      $scope.model = CnCallbackModelFactory.root;
      $scope.model.listModel.onList( true ).then( function() {
        $scope.model.setupBreadcrumbTrail( 'list' );
      } );
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.controller( 'CallbackViewCtrl', [
    '$scope', 'CnCallbackModelFactory',
    function( $scope, CnCallbackModelFactory ) {
      $scope.model = CnCallbackModelFactory.root;
      $scope.model.viewModel.onView().then( function() {
        $scope.model.setupBreadcrumbTrail( 'view' );
      } );
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnCallbackAdd', function() {
    return {
      templateUrl: 'app/callback/add.tpl.html',
      restrict: 'E'
    };
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnCallbackView', function() {
    return {
      templateUrl: 'app/callback/view.tpl.html',
      restrict: 'E'
    };
  } );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCallbackAddFactory', [
    'CnBaseAddFactory',
    function( CnBaseAddFactory ) {
      var object = function( parentModel ) { CnBaseAddFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCallbackListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) {
        CnBaseListFactory.construct( this, parentModel );

        // override onDelete
        this.onDelete = function( record ) {
          return this.$$onDelete( record ).then( function() { self.parentModel.enableAdd( 0 == self.total ); } );
        };
      };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCallbackViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var args = arguments;
      var CnBaseViewFactory = args[0];
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root );

        this.onView = function() {
          return this.$$onView().then( function() {
            var upcoming = moment().isBefore( self.record.datetime );
            parentModel.enableDelete( upcoming );
            parentModel.enableEdit( upcoming );
          } );
        };
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCallbackModelFactory', [
    'CnBaseModelFactory', 'CnCallbackAddFactory', 'CnCallbackListFactory', 'CnCallbackViewFactory',
    'CnHttpFactory',
    function( CnBaseModelFactory, CnCallbackAddFactory, CnCallbackListFactory, CnCallbackViewFactory,
              CnHttpFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, cenozoApp.module( 'callback' ) );
        this.addModel = CnCallbackAddFactory.instance( this );
        this.listModel = CnCallbackListFactory.instance( this );
        this.viewModel = CnCallbackViewFactory.instance( this, root );

        // extend getMetadata
        this.getMetadata = function() {
          this.metadata.loadingCount++;
          var promiseList = [ this.$$getMetadata() ];

          var parent = this.getParentIdentifier();
          if( angular.isDefined( parent.subject ) && angular.isDefined( parent.identifier ) ) {
            promiseList.push(
              CnHttpFactory.instance( {
                path: [ parent.subject, parent.identifier ].join( '/' ),
                data: { select: { column: { column: 'participant_id' } } }
              } ).query().then( function( response ) {
                return CnHttpFactory.instance( {
                  path: ['participant', response.data.participant_id, 'phone' ].join( '/' ),
                  data: {
                    select: { column: [ 'id', 'rank', 'type', 'number' ] },
                    modifier: { order: { rank: false } }
                  }
                } ).query().then( function( response ) {
                  self.metadata.columnList.phone_id.enumList = [];
                  response.data.forEach( function( item ) {
                    self.metadata.columnList.phone_id.enumList.push( {
                      value: item.id,
                      name: '(' + item.rank + ') ' + item.type + ': ' + item.number
                    } );
                  } );
                } )
              } )
            );
          }

          return $q.all( promiseList ).finally( function finished() { self.metadata.loadingCount--; } );
        };
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );