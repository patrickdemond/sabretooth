define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'qnaire', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: { column: 'rank' },
    name: {
      singular: 'questionnaire',
      plural: 'questionnaires',
      possessive: 'questionnaire\'s'
    },
    columnList: {
      name: {
        column: 'script.name',
        title: 'Name'
      },
      rank: {
        title: 'Rank',
        type: 'rank'
      },
      allow_missing_consent: {
        title: 'Missing Consent',
        type: 'boolean'
      },
      web_version: {
        title: 'Web Version',
        type: 'boolean'
      },
      delay: {
        title: 'Delay',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'rank',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    rank: {
      column: 'qnaire.rank',
      title: 'Rank',
      type: 'rank'
    },
    script_id: {
      title: 'Script',
      type: 'enum',
      isConstant: 'view',
      help: 'Only scripts which are marked as non-repeatable may be used as a questionnaire.'
    },
    allow_missing_consent: {
      title: 'Allow Missing Consent',
      type: 'boolean',
      help: 'This field determines whether or not a participant should be allowed to proceed with the questionnaire when they are missing the extra consent record specified by the study.'
    },
    web_version: {
      title: 'Web Version',
      type: 'boolean',
      isConstant: function( $state, model ) {
        // don't allow non-Pine scripts to have a web version
        return 'add' != model.getActionFromState() && null == model.viewModel.record.pine_qnaire_id;
      },
      help: 'Defines whether this questionnaire has a web-version.'
    },
    delay: {
      title: 'Delay (weeks)',
      type: 'string',
      format: 'integer',
      minValue: 0
    },
    pine_qnaire_id: {
      column: 'script.pine_qnaire_id',
      type: 'hidden'
    }
  } );

  module.addExtraOperation( 'view', {
    title: 'Mass Interview Method',
    operation: function( $state, model ) {
      $state.go( 'qnaire.mass_method', { identifier: model.viewModel.record.getIdentifier() } );
    },
    isIncluded: function( $state, model ) { return model.getEditEnabled() && null != model.viewModel.record.pine_qnaire_id; }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnQnaireAdd', [
    'CnQnaireModelFactory',
    function( CnQnaireModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'add.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnQnaireModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnQnaireList', [
    'CnQnaireModelFactory',
    function( CnQnaireModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnQnaireModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnQnaireMassMethod', [
    'CnQnaireMassMethodFactory', 'CnSession', '$state',
    function( CnQnaireMassMethodFactory, CnSession, $state ) {
      return {
        templateUrl: module.getFileUrl( 'mass_method.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnQnaireMassMethodFactory.instance();

          $scope.model.onLoad().then( function() {
            CnSession.setBreadcrumbTrail( [ {
              title: 'Questionnaires',
              go: function() { return $state.go( 'qnaire.list' ); }
            }, {
              title: $scope.model.qnaireName,
              go: function() { return $state.go( 'qnaire.view', { identifier: $scope.model.qnaireId } ); }
            }, {
              title: 'Mass Interview Method'
            } ] );
          } );
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnQnaireView', [
    'CnQnaireModelFactory',
    function( CnQnaireModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnQnaireModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnQnaireAddFactory', [
    'CnBaseAddFactory',
    function( CnBaseAddFactory ) {
      var object = function( parentModel ) { CnBaseAddFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnQnaireListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnQnaireMassMethodFactory', [
    'CnSession', 'CnHttpFactory', 'CnModalMessageFactory', 'CnParticipantSelectionFactory', '$state',
    function( CnSession, CnHttpFactory, CnModalMessageFactory, CnParticipantSelectionFactory, $state ) {
      var object = function() {
        var self = this;
        angular.extend( this, {
          method: 'phone',
          working: false,
          qnaireId: $state.params.identifier,
          qnaireName: null,
          participantSelection: CnParticipantSelectionFactory.instance( {
            path: ['qnaire', $state.params.identifier, 'participant'].join( '/' ),
            data: { mode: 'confirm', method: 'phone' }
          } ),
          onLoad: function() {
            // reset data
            return CnHttpFactory.instance( {
              path: 'qnaire/' + this.qnaireId,
              data: { select: { column: 'name' } }
            } ).get().then( function( response ) {
              self.qnaireName = response.data.name;
              self.participantSelection.reset();
            } );
          },

          inputsChanged: function() {
            this.participantSelection.data.method = this.method;
            this.participantSelection.reset();
          },

          proceed: function() {
            this.working = true;
            if( !this.participantSelection.confirmInProgress && 0 < this.participantSelection.confirmedCount ) {
              CnHttpFactory.instance( {
                path: ['qnaire', this.qnaireId, 'participant'].join( '/' ),
                data: {
                  mode: 'update',
                  identifier_id: this.participantSelection.identifierId,
                  identifier_list: this.participantSelection.getIdentifierList(),
                  method: this.method
                },
                onError: function( response ) {
                  CnModalMessageFactory.httpError( response ).then( function() { self.onLoad(); } );
                }
              } ).post().then( function( response ) {
                CnModalMessageFactory.instance( {
                  title: 'Interview Methods Updated',
                  message: 'You have successfully changed ' + self.participantSelection.confirmedCount +
                           ' "' + self.qnaireName + '" questionnaires ' +
                           'to using the ' + self.method + ' interviewing method.'
                } ).show().then( function() { self.onLoad(); } );
              } ).finally( function() { self.working = false; } );
            }
          }

        } );
      }
      return { instance: function() { return new object(); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnQnaireViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root );

        this.deferred.promise.then( function() {
          if( angular.isDefined( self.collectionModel ) ) self.collectionModel.listModel.heading = 'Disabled Collection List';
          if( angular.isDefined( self.holdTypeModel ) ) self.holdTypeModel.listModel.heading = 'Overridden Hold Type List';
          if( angular.isDefined( self.siteModel ) ) self.siteModel.listModel.heading = 'Disabled Site List';
          if( angular.isDefined( self.stratumModel ) ) self.stratumModel.listModel.heading = 'Disabled Stratum List';
        } );
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnQnaireModelFactory', [
    'CnBaseModelFactory', 'CnQnaireAddFactory', 'CnQnaireListFactory', 'CnQnaireViewFactory', 'CnHttpFactory',
    function( CnBaseModelFactory, CnQnaireAddFactory, CnQnaireListFactory, CnQnaireViewFactory, CnHttpFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.addModel = CnQnaireAddFactory.instance( this );
        this.listModel = CnQnaireListFactory.instance( this );
        this.viewModel = CnQnaireViewFactory.instance( this, root );

        // extend getMetadata
        this.getMetadata = function() {
          return this.$$getMetadata().then( function() {
            return CnHttpFactory.instance( {
              path: 'application/0/script',
              data: {
                select: { column: [ 'id', 'name' ] },
                modifier: {
                  where: [ { column: 'repeated', operator: '=', value: false } ],
                  order: 'name'
                }
              }
            } ).query().then( function( response ) {
              self.metadata.columnList.script_id.enumList = [];
              response.data.forEach( function( item ) {
                self.metadata.columnList.script_id.enumList.push( { value: item.id, name: item.name } );
              } );
            } );
          } );
        };
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
