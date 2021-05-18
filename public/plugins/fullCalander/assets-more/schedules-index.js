
document.addEventListener('DOMContentLoaded', function() {
    console.log('my new var',idEmployee,sch);
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        timeZone: 'UTC',
        plugins: ['dayGrid', 'timeGrid', 'interaction', 'resourceTimeline'],
        resourceAreaWidth: 230,
        firstDay: 1,
        fixedWeekCount: false,
        editable: true,
        selectable: true,
        displayEventTime: true,
        displayEventEnd: true,
        //eventBorderColor: '#e63d90',
        eventTextColor: 'black',
        aspectRatio: 1.5,
        scrollTime: '07:00',
        eventRender:function(info){
            //  info.event.backgroundColor='blue';
            // alert('hi render ');
            console.log('render event')
            console.log(info)



            var tooltip = new Tooltip(info.el, {
                title: info.event.extendedProps.Description,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });



        },
        dateClick: function (info) {
            // alert('hi');
            console.log(info);


        },
        eventMouseEnter:function(info){
            console.log('eventMouseEnter=');
            console.log( info);




        },
        eventMouseLeave:function(info){


        },
        eventClick: function(info){
            console.log(info);


        },
        header: {
            left: '',
            center: 'title',
            right: 'today prev,next'
        },

        defaultView: 'dayGridMonth',
        views: {
            resourceTimelineThreeDays: {
                type: 'resourceTimeline',
                duration: {days: 3},
                buttonText: '3 day',
                eventLimit:1
            }
        },
        events: function (info, successCallback, failureCallback) {

            console.log("hi event" );
            console.dir(info)

            $.ajax({
                type: "GET",
                url: baseUri + 'modules/workplan/classes/action/Form.php',
                data: {
                    schedulesEmployeeID: idEmployee,
                    schID:sch

                },
                cache: false,
                success: function (response) {

                    let res = JSON.parse(response);
                    console.log('res',res);
                    let sch =(res.sch[0])["name"];
                    let schedules=[];
                    let texttitle;
                    for(let i=0;i<res.schedules.length;i++) {
                        let st=(res.schedules[i])['start_time'];
                        let sts=st.slice(0,5);
                        let ft=(res.schedules[i])['finish_time'];
                        let ftf=ft.slice(0,5);
                        texttitle=sts+'-'+ftf;

                        let valuee = {
                            id: (res.schedules[i])['id'],
                            title: texttitle,
                            start: (res.schedules[i])['start_date'],
                            color: 'Bisque',
                            Description:sch


                        };
                        schedules.push(valuee);



                    }
                    console.log("this person schedules",schedules)
                    successCallback(schedules);

                }

            });
        }

    });

    calendar.render();

    // readjust sizing after font load
    window.addEventListener('load', function() {
        calendar.updateSize();

    });


});
