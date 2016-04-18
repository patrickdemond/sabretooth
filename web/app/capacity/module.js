define( [ 'appointment', 'availability', 'shift', 'shift_template', 'site' ].reduce( function( list, name ) {
  return list.concat( cenozoApp.module( name ).getRequiredFiles() );
}, [] ), function() {
  'use strict';

  try { var module = cenozoApp.module( 'capacity', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'capacity',
      plural: 'capacities',
      possessive: 'capacity\'s',
      pluralPossessive: 'capacities\''
    }
  } );

  function getSlotsFromEvents( appointmentEvents, shiftEvents, shiftTemplateEvents ) {
    var slots = [];

    // create an object grouping all events for each day
    var events = {};
    appointmentEvents.forEach( function( item ) {
      var date = item.start.format( 'YYYY-MM-DD' );
      if( angular.isUndefined( events[date] ) )
        events[date] = { appointments: [], shifts: [], templates: [] };
      events[date].appointments.push( item );
    } );
    shiftEvents.forEach( function( item ) {
      var date = item.start.format( 'YYYY-MM-DD' );
      if( angular.isUndefined( events[date] ) )
        events[date] = { appointments: [], shifts: [], templates: [] };
      events[date].shifts.push( item );
    } );
    shiftTemplateEvents.forEach( function( item ) {
      var date = item.start.format( 'YYYY-MM-DD' );
      if( angular.isUndefined( events[date] ) )
        events[date] = { appointments: [], shifts: [], templates: [] };
      events[date].templates.push( item );
    } );

    // now go through each day and determine the open slots
    for( var date in events ) {
      // determine where the number of slots changes
      var diffs = {};
      if( 0 < events[date].shifts.length ) {
        // process shifts
        events[date].shifts.forEach( function( shift ) {
          var time = shift.start.format( 'HH:mm' );
          if( angular.isUndefined( diffs[time] ) ) diffs[time] = 0;
          diffs[time]++;
          var time = shift.end.format( 'HH:mm' );
          if( angular.isUndefined( diffs[time] ) ) diffs[time] = 0;
          diffs[time]--;
        } );
      } else {
        // process shift templates if there are no shifts
        events[date].templates.forEach( function( shiftTemplate ) {
          var time = moment( shiftTemplate.start ).format( 'HH:mm' );
          if( angular.isUndefined( diffs[time] ) ) diffs[time] = 0;
          diffs[time] += parseInt( shiftTemplate.title );
          var time = moment( shiftTemplate.end ).format( 'HH:mm' );
          if( angular.isUndefined( diffs[time] ) ) diffs[time] = 0;
          diffs[time] -= parseInt( shiftTemplate.title );
        } );
      }

      // remove slots taken up by non-overridden appointments
      events[date].appointments.filter( function( appointment ) {
        return !appointment.override;
      } ).forEach( function( appointment ) {
        var time = appointment.start.format( 'HH:mm' );
        if( angular.isUndefined( diffs[time] ) ) diffs[time] = 0;
        diffs[time]--;
        var time = appointment.end.format( 'HH:mm' );
        if( angular.isUndefined( diffs[time] ) ) diffs[time] = 0;
        diffs[time]++;
      } );

      // get an ordered list of all keys in the diffs array
      var times = [];
      for( var time in diffs ) if( diffs.hasOwnProperty( time ) ) times.push( time );
      times.sort();

      // now go through all diffs to determine the slots
      var lastTime = null;
      var lastNumber = 0;
      var number = 0;
      for( var i = 0; i < times.length; i++ ) {
        var time = times[i];
        number += diffs[time];
        if( 0 < lastNumber ) {
          var colon = time.indexOf( ':' );
          var lastColon = lastTime.indexOf( ':' );
          var tempDate = moment( date );
          slots.push( {
            title: lastNumber + ' slot' + ( 1 < lastNumber ? 's' : '' ),
            start: moment().year( tempDate.year() )
                           .month( tempDate.month() )
                           .date( tempDate.date() )
                           .hour( lastTime.substring( 0, lastColon ) )
                           .minute( lastTime.substring( lastColon + 1 ) )
                           .second( 0 ),
            end: moment().year( tempDate.year() )
                         .month( tempDate.month() )
                         .date( tempDate.date() )
                         .hour( time.substring( 0, colon ) )
                         .minute( time.substring( colon + 1 ) )
                         .second( 0 )
          } );
        }
        lastTime = time;
        lastNumber = number;
      }
    }

    return slots;
  }

  // add an extra operation for each of the appointment-based calendars the user has access to
  [ 'appointment', 'availability', 'capacity', 'shift', 'shift_template' ].forEach( function( name ) {
    var calendarModule = cenozoApp.module( name );
    if( angular.isDefined( calendarModule.actions.calendar ) ) {
      module.addExtraOperation( 'calendar', {
        title: calendarModule.subject.snake.replace( "_", " " ).ucWords(),
        operation: function( $state, model ) {
          $state.go( name + '.calendar', { identifier: model.site.getIdentifier() } );
        },
        classes: 'capacity' == name ? 'btn-warning' : undefined // highlight current model
      } );
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnCapacityCalendar', [
    'CnCapacityModelFactory',
    'CnAppointmentModelFactory', 'CnAvailabilityModelFactory',
    'CnShiftModelFactory', 'CnShiftTemplateModelFactory',
    function( CnCapacityModelFactory,
              CnAppointmentModelFactory, CnAvailabilityModelFactory,
              CnShiftModelFactory, CnShiftTemplateModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'calendar.tpl.html' ),
        restrict: 'E',
        scope: {
          model: '=?',
          preventSiteChange: '@'
        },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnCapacityModelFactory.instance();
          $scope.model.calendarModel.heading = $scope.model.site.name.ucWords() + ' Capacity Calendar';
        },
        link: function( scope ) {
          // factory name -> object map used below
          var factoryList = {
            appointment: CnAppointmentModelFactory,
            availability: CnAvailabilityModelFactory,
            capacity: CnCapacityModelFactory,
            shift: CnShiftModelFactory,
            shift_template: CnShiftTemplateModelFactory
          };

          // synchronize appointment/shift-based calendars
          scope.$watch( 'model.calendarModel.currentDate', function( date ) {
            Object.keys( factoryList ).filter( function( name ) {
              return angular.isDefined( cenozoApp.moduleList[name].actions.calendar );
            } ).forEach( function( name ) {
               var calendarModel = factoryList[name].forSite( scope.model.site ).calendarModel;
               if( !calendarModel.currentDate.isSame( date, 'day' ) ) calendarModel.currentDate = date;
            } );
          } );
          scope.$watch( 'model.calendarModel.currentView', function( view ) {
            Object.keys( factoryList ).filter( function( name ) {
              return angular.isDefined( cenozoApp.moduleList[name].actions.calendar );
            } ).forEach( function( name ) {
               var calendarModel = factoryList[name].forSite( scope.model.site ).calendarModel;
               if( calendarModel.currentView != view ) calendarModel.currentView = view;
            } );
          } );
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCapacityCalendarFactory', [
    'CnBaseCalendarFactory',
    'CnAppointmentModelFactory', 'CnShiftModelFactory', 'CnShiftTemplateModelFactory', '$q',
    function( CnBaseCalendarFactory,
              CnAppointmentModelFactory, CnShiftModelFactory, CnShiftTemplateModelFactory, $q ) {
      var object = function( parentModel, site ) {
        var self = this;
        CnBaseCalendarFactory.construct( this, parentModel );

        // remove day and event click callbacks
        delete this.settings.dayClick;
        delete this.settings.eventClick;

        // extend onCalendar to transform templates into events
        this.onCalendar = function( replace, minDate, maxDate, ignoreParent ) {
          // always replace, otherwise the calendar won't update when new appointments/shifts/etc are made
          replace = true;

          // unlike other calendars we don't cache events
          var appointmentCalendarModel = CnAppointmentModelFactory.forSite( parentModel.site ).calendarModel;
          var shiftCalendarModel = CnShiftModelFactory.forSite( parentModel.site ).calendarModel;
          var shiftTemplateCalendarModel = CnShiftTemplateModelFactory.forSite( parentModel.site ).calendarModel;

          // instead of calling $$onCalendar we determine events from the events in other calendars
          return $q.all( [
            appointmentCalendarModel.onCalendar( replace, minDate, maxDate, true ),
            shiftCalendarModel.onCalendar( replace, minDate, maxDate, true ),
            shiftTemplateCalendarModel.onCalendar( replace, minDate, maxDate, true )
          ] ).then( function() {
            self.cache = getSlotsFromEvents(
              // get all appointments inside the load date span
              appointmentCalendarModel.cache.filter( function( item ) {
                return !item.start.isBefore( minDate, 'day' ) && !item.end.isAfter( maxDate, 'day' );
              } ),
              // get all shift events inside the load date span
              shiftCalendarModel.cache.filter( function( item ) {
                return !item.start.isBefore( minDate, 'day' ) && !item.end.isAfter( maxDate, 'day' );
              } ),
              // get all shift template events inside the load date span
              shiftTemplateCalendarModel.cache.filter( function( item ) {
                return !item.start.isBefore( minDate, 'day' ) && !item.end.isAfter( maxDate, 'day' );
              } )
            );
          } );
        };
      };

      return { instance: function( parentModel, site ) { return new object( parentModel, site ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnCapacityModelFactory', [
    'CnBaseModelFactory', 'CnCapacityCalendarFactory', 'CnSession', '$state',
    function( CnBaseModelFactory, CnCapacityCalendarFactory, CnSession, $state ) {
      var object = function( site ) {
        if( !angular.isObject( site ) || angular.isUndefined( site.id ) )
          throw new Error( 'Tried to create CnCapacityModel without specifying the site.' );

        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.calendarModel = CnCapacityCalendarFactory.instance( this, site );
        this.site = site;
      };

      // get the siteColumn to be used by a site's identifier
      var siteModule = cenozoApp.module( 'site' );
      var siteColumn = angular.isDefined( siteModule.identifier.column ) ? siteModule.identifier.column : 'id';

      return {
        siteInstanceList: {},
        forSite: function( site ) {
          if( !angular.isObject( site ) ) {
            $state.go( 'error.404' );
            throw new Error( 'Cannot find site matching identifier "' + site + '", redirecting to 404.' );
          }
          if( angular.isUndefined( this.siteInstanceList[site.id] ) ) {
            if( angular.isUndefined( site.getIdentifier ) )
              site.getIdentifier = function() { return siteColumn + '=' + this[siteColumn]; };
            this.siteInstanceList[site.id] = new object( site );
          }
          return this.siteInstanceList[site.id];
        },
        instance: function() {
          var site = null;
          if( 'calendar' == $state.current.name.split( '.' )[1] ) {
            if( angular.isDefined( $state.params.identifier ) ) {
              var identifier = $state.params.identifier.split( '=' );
              if( 2 == identifier.length )
                site = CnSession.siteList.findByProperty( identifier[0], identifier[1] );
            }
          } else {
            site = CnSession.site;
          }
          return this.forSite( site );
        }
      };
    }
  ] );

} );
