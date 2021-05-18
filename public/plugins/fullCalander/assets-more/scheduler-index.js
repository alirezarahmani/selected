
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'UTC',
    plugins: [ 'dayGrid', 'timeGrid', 'interaction', 'resourceTimeline' ],
    resourceAreaWidth: 230,
    editable: true,
    aspectRatio: 1.5,
    scrollTime: '07:00',
    header: {
      left: 'promptResource today prev,next',
      center: 'title',
      right: 'resourceTimelineDay,resourceTimelineThreeDays,timeGridWeek,dayGridMonth'
    },
    customButtons: {
      promptResource: {
        text: '+ room',
        click: function() {
          var title = prompt('Room name');
          if (title) {
            calendar.addResource({
              title: title
            });
          }
        }
      }
    },
    defaultView: 'resourceTimelineDay',
    views: {
      resourceTimelineThreeDays: {
        type: 'resourceTimeline',
        duration: { days: 3 },
        buttonText: '3 day'
      }
    },
    resourceLabelText: 'Rooms',
    resources: [
        {
            id: 1,
            title: 'Room A'
        },
        {
            id: 2,
            title: 'Room B'
        }
    ],
    events:  [
        {
            title  : 'event1',
            start  : '2019-08-21',
            resourceId : 1,
        },
        {
            title  : 'event2',
            start  : '2019-08-21',
            end    : '2019-08-21',
            resourceId : 1,
        },
        {
            title  : 'event3',
            start  : '2019-08-21T12:30:00',
            end :'2019-08-21T15:30:00',
            resourceId : 2,
            allDay : false // will make the time show

        }
    ]
  });

  calendar.render();

  // readjust sizing after font load
  window.addEventListener('load', function() {
    calendar.updateSize();
  });

});
