var calendars={};


document.addEventListener('DOMContentLoaded', function() {
    var selectedEmployee;
    //check token if not set redirect to login page
    let tok=localStorage.getItem('token');
    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    let query_string=(new URL(window.location.href)).search;
    let search_params = new URLSearchParams(query_string);
    let userAvail=search_params.get('userAvail');

    let personRole=localStorage.getItem('role');
    let availability=localStorage.getItem('availability');

        //get employee list
        $.ajax({
            method:'get',
            url:base_url+'/api/users',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=>{
                // console.log('new list',res['hydra:member']);
                let users=res['hydra:member'] ;
                users.map( (user)=> {
                    //console.log('user',user)
                    $('select#employee-list').append('<option value='+user['id']+'>'+user['firstName']+' '+user['lastName']+'</option>');
                });


                if(userAvail !== null) {

                    selectedEmployee = userAvail;
                    $('#employee-list').val(userAvail.toString()).trigger('change');
                }else{
                       // console.log('here',localStorage.getItem('id'))
                        $('#employee-list').val((localStorage.getItem('id')).toString()).trigger('change');

                }

            }
        });


    let avails=[];
    let events=[];
    let create_calendar=(id)=>  {
        var calendarEl = document.getElementById(id);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: '0231832627-fcs-1568628067',
            plugins: [ 'dayGrid', 'timeGrid', 'interaction', 'resourceTimeline'],
            timeZone: 'UTC',
            allDay:false,
            aspectRatio: 1.5,
            firstDay:1,
            header: {
                left: 'prev,next',
                center: 'title',
                right: ''
            },
            defaultView: 'dayGridMonth',
            validRange: {
                start: moment().format('YYYY-MM-DD')
            },
            editable: true,
            eventOverlap:false,
            displayEventTime:true,
            displayEventEnd:true,
            eventSources:[{events:async (fetchInfo,success,fail)=> {
                    let events=[];

                    let start=moment(fetchInfo.startStr).utc(0).format('YYYY-MM-DD HH:mm');
                    let end=moment(fetchInfo.endStr).utc(0).format('YYYY-MM-DD HH:mm');
                    console.log(fetchInfo,start,end,"~~~~~~~~~~~~~~~~~~~~~~~~~~",moment);


                    //get availabilities for specific
                    await new Promise(async (resolve,fail)=> {
                        await $.ajax({
                            url:  base_url+'/api/availabilities?user='+selectedEmployee,
                            method: 'GET',
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,

                            },
                            success: function(r){

                                console.log(r['hydra:member']);
                                avails = r['hydra:member'];
                                avails.map((avail)=>{
                                    console.log(avail)
                                    let allday=false;
                                    if(avail['startTime'].substring(0,10) === avail['endTime'].substring(0,10) && avail['startTime'].substring(11,16) === '00:00' && avail['endTime'].substring(11,16) === '23:59'){
                                        allday=true;
                                    }

                                    let event;
                                    if(avail['available'] === false){
                                        if(allday === true) {
                                            event = {
                                                id: avail['id'],
                                                allDay:true,
                                                title:'Unavailable AllDay',
                                                start: (new Date(avail['startTime'] + " UTC")).toISOString(),
                                                end: (new Date(avail['endTime'] + " UTC")).toISOString(),
                                                color: 'red'
                                            };
                                        }else{
                                            event = {
                                                id: avail['id'],
                                                title:'Unavailable',
                                                start: (new Date(avail['startTime'] + " UTC")).toISOString(),
                                                end: (new Date(avail['endTime'] + " UTC")).toISOString(),
                                                color: 'red'
                                            };
                                        }

                                    }else if(avail['available'] === true){

                                        if(allday === true){

                                            event={
                                                id:avail['id'],
                                                allDay:true,
                                                title:'Available AllDay',
                                                start:(new Date(avail['startTime']+" UTC")).toISOString(),
                                                end:(new Date(avail['endTime']+" UTC")).toISOString(),
                                                color:'green'

                                            };
                                        }else{

                                            event={
                                                id:avail['id'],
                                                title:'Available',
                                                start:(new Date(avail['startTime']+" UTC")).toISOString(),
                                                end:(new Date(avail['endTime']+" UTC")).toISOString(),
                                                color:'green'

                                            };
                                        }

                                    }

                                    events.push(event);

                                });

                            },
                            error:function(e) {

                                // console.log(e)
                                //expire jwt token
                                if(e.status == 401){
                                    window.location.href = base_url2+"/login";
                                }
                                toastr.error(e['responseJSON']['hydra:description']);
                            }
                        });

                        resolve(events)

                    }).then(value=>{

                        success(value)});

                }}]
        });
        calendars[id] = calendar;
        calendar.render();

    };
    create_calendar('calendar');

    let defaultDateObject= calendars['calendar'].getDate();
    let defaultDate=(defaultDateObject.toISOString()).substring(0,10);
    let defaultDateStart=defaultDate+' 00:00';
    let defaultDateEnd=defaultDate+' 23:59';



    $("select#employee-list").change(function(){

        console.log('now',moment().format('YYYY-MM-DD'));
        selectedEmployee = $(this).children("option:selected").val();
        let uri = window.location.toString();
        if (uri.indexOf("?") > 0) {
            let clean_uri = uri.substring(0, uri.indexOf("?"));
            window.history.replaceState({}, document.title, clean_uri);
        }
        calendars['calendar'].refetchEvents()
    });


    calendars['calendar'].on('dateClick', function(info) {
        console.log('dateClick',info.dateStr);
        $('#start-avail-date').val(moment(info.dateStr).format('DD/MM/YYYY'));
        $('.changeStat').text('Add');
        $('#modal-addAvail').modal('show');
    });

    calendars['calendar'].on('eventClick', function(info) {
        console.log('event Click ' + info.event.id);

        $.ajax({
            method:'GET',
            url:base_url+'/api/availabilities/'+ info.event.id,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=> {
                console.log(res);
                $('.changeStat').text('Update');
                $('#modal-addAvail').modal('show');
                $('#delete-avail').show();

                $('#availeditId').val(res['id']);

                let startDate=res['startTime'].substring(0,10);
                $('#start-avail-date').val(moment(startDate,'YYYY-MM-DD').format('DD/MM/YYYY'));
                $('#note').val(res['note']);


                if (res['available'] == true) {

                    $('input:radio[name=available][id=avail]').prop('checked', true);

                } else if (res['available'] == false) {
                    $('input:radio[name=available][id=unavail]').prop('checked', true);

                }
                //all day
                console.log('i am here',res['startTime'].substring(11,16),res['endTime'].substring(11,16))
                if(res['startTime'].substring(11,16) === '00:00' && res['endTime'].substring(11,16) === '23:59'){
                    console.log('i am here')
                    $('#alldayCh').prop("checked",true);
                    $('.timepart').hide();
                }else{
                    $('#timeStartInput').val(res['startTime'].substring(11, 16));
                    $('#timeEndInput').val(res['endTime'].substring(11, 16));
                    $('.timepart').show();
                }



                if (res['repeated'] == true) {
                    //$('#repeated').val(res['repeated']);
                    $('#repeated').val(1);
                    $('#repeats').prop("checked", true);
                    $('#repeats').val(true);
                    $('.repeatpart').show();
                    let endDuration=res['endReapetedTime'];
                    $('#end-repeated-date').val(moment(endDuration,'YYYY-MM-DD').format('DD/MM/YYYY'));
                    let dayArray= res['days'].split(',');
                    $.each(dayArray, function(index,value) {
                        console.log('first index',index,value)
                        $("input[name='days']").each(function () {
                            if($(this).val() === value ){
                                $(this).prop("checked",true);
                            }
                        });
                    });


                }else{
                    $('#repeated').val(0);
                    $('#repeats').prop("checked", false);
                    $('.repeatpart').hide();
                }
            },
            error:(e)=>{
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });
    });


    //add availability
    $('#add-avail').on('click',function () {

        $('.days:checked').each(function() {
            console.log(this.value);
        });
        let dayss = [];
        $.each($("input[name='days']:checked"), function(){
            dayss.push($(this).val());
        });


        let start,end;
        if($('#alldayCh').val() === 'true'){
            start=($('#start-avail-date').val()).substring(0, 10) + ' 00:00';
            end=($('#start-avail-date').val()).substring(0, 10) + ' 23:59';

        }else{
            start=($('#start-avail-date').val()).substring(0, 10) + ' ' + $('#timeStartInput').val();
            end=($('#start-avail-date').val()).substring(0, 10) + ' ' + $('#timeEndInput').val();

        }

        if($('#availeditId').val() !== '' ) {
            console.log('edited');

            if ($('#repeated').val() == 0) {

                let edId = $('#availeditId').val();
                let aveditData ;
                if ($('#repeats').val() === 'true') {
                    let endDuration=($('#end-repeated-date').val()).substring(0, 10) + ' ' + '23:59';
                    aveditData = {
                        user: '/api/users/' + $('#employee-list').children("option:selected").val(),
                        note: $('#note').val(),
                        repeated: $('#repeats').val() === 'true',
                        available:$("input[name='available']:checked").val() === 'true',
                        days: dayss.toString(),
                        startTime:moment(start,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                        endTime: moment(end,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                        endReapetedTime: moment(endDuration,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm')
                    }
                } else {

                    aveditData = {
                        user: '/api/users/' + $('#employee-list').children("option:selected").val(),
                        note: $('#note').val(),
                        repeated: $('#repeats').val() === 'true',
                        available:$("input[name='available']:checked").val() === 'true',
                        days: dayss.toString(),
                        startTime:moment(start,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                        endTime: moment(end,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm')

                    }
                }
                console.log('data for edit',aveditData)

                $.ajax({
                    url: base_url + '/api/availabilities/' + edId,
                    method: 'PUT',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify(aveditData),
                    success: function (avail) {
                        console.log('updte single avail',avail);
                        toastr.success('Availability Successfully Updated.');

                        calendars['calendar'].refetchEvents();


                    },
                    error: function (e) {

                        // console.log(e)
                        //expire jwt token
                        if (e.status == 401) {
                            window.location.href = base_url2 + "/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });

            } else if ($('#repeated').val() == 1) {
                let endDuration=($('#end-repeated-date').val()).substring(0, 10) + ' ' + '23:59';
                console.log('repeated')
                let aveditData = {
                    user: '/api/users/' + $('#employee-list').children("option:selected").val(),
                    note: $('#note').val(),
                    available:$("input[name='available']:checked").val() === 'true',
                    days: dayss.toString(),
                    startTime:moment(start,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                    endTime: moment(end,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                    endReapetedTime: moment(endDuration,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                    chain: false
                };


                $('#modal-updateRepeated').modal('show');
                $('#availIdRepeated').val($('#availeditId').val());

                //update availability
                $('.updateAll').on('click', function () {

                    let edId = $('#availIdRepeated').val();

                    //console.log('data',data)
                    aveditData.chain = true;
                    aveditData.repeated=true;
                    console.log('times',start,end)
                    console.log('times',moment(start,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'))
                    console.log('dateEvent', aveditData)
                    $.ajax({
                        url: base_url + '/api/availabilities/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(aveditData),
                        success: function (avail) {
                            console.log('updte single avail',avail);
                            toastr.success('Availability Successfully Updated.');
                            calendars['calendar'].refetchEvents();
                            $( '.updateAll').unbind( "click" );
                        },
                        error: function (e) {

                            // console.log(e)
                            //expire jwt token
                            if (e.status == 401) {
                                window.location.href = base_url2 + "/login";
                            }
                            toastr.error(e['responseJSON']['hydra:description']);
                        }
                    });
                });
                $('.updateOne').on('click', function () {

                    let edId = $('#availIdRepeated').val();

                    //console.log('data',data)
                    aveditData.chain = false;
                    aveditData.repeated = false;

                    console.log('dateEvent', aveditData)
                    $.ajax({
                        url: base_url + '/api/availabilities/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(aveditData),
                        success: function (avail) {
                            console.log('updte single avail',avail);
                            toastr.success('Availability Successfully Updated.');
                            calendars['calendar'].refetchEvents();
                            $( '.updateOne').unbind( "click" );

                        },
                        error: function (e) {

                            // console.log(e)
                            //expire jwt token
                            if (e.status == 401) {
                                window.location.href = base_url2 + "/login";
                            }
                            toastr.error(e['responseJSON']['hydra:description']);
                        }
                    });
                });
            }
        }else {
            console.log('insert first time',$('#repeats').val())
            let avData;

            if ($('#repeats').val() === 'true') {
                console.log('repeated true')
                let endDuration=($('#end-repeated-date').val()).substring(0, 10) + ' ' + '23:59';

                avData = {
                    user: '/api/users/' + $('#employee-list').children("option:selected").val(),
                    note: $('#note').val(),
                    repeated: $('#repeats').val() === 'true',
                    available: $("input[name='available']:checked").val() === 'true',
                    days: dayss.toString(),
                    startTime:moment(start,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                    endTime: moment(end,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                    endReapetedTime: moment(endDuration,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm')

                }
            } else {
                console.log('repeated false')

                avData = {
                    user: '/api/users/' + $('#employee-list').children("option:selected").val(),
                    note: $('#note').val(),
                    repeated: $('#repeats').val() === 'true',
                    available: $("input[name='available']:checked").val() === 'true',
                    days: dayss.toString(),
                    startTime:moment(start,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm'),
                    endTime: moment(end,'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm')

                }
            }
            console.log(avData)
            $.ajax({
                method: 'POST',
                url: base_url + '/api/availabilities',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(avData),
                success: (avail) => {
                    console.log('res added',avail)
                    toastr.success('Availability Successfully Added.');

                    calendars['calendar'].refetchEvents();

                },
                error: (e) => {
                    // console.log(e)
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    if(e['responseJSON']['hydra:description'] === "Invalid IRI \"/api/users/\"."){
                        toastr.error("Select Employee First");
                    }

                }


            });
        }


    });

    //delete availability
    $('#delete-avail').on('click',function () {

        if($('#repeated').val() == 1){
            console.log('repeated = 1 delete')

            $('#modal-deleteRepeated').modal('show');

            $('#availIdRepeateddel').val($('#availeditId').val());
            let idDeleted =  $('#availIdRepeateddel').val()

            console.log(idDeleted)

            //delete availability
            $('.deleteAll').on('click', function () {

                $.ajax({
                    url: base_url + '/api/availabilities/' + idDeleted,
                    method: 'DELETE',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify({
                        chain:true
                    }),
                    success: function (r) {
                         console.log('dellll',r)
                        // $('#modal-deleteRepeated').modal('hide');
                        calendars['calendar'].refetchEvents()

                    },
                    error: function (e) {

                        // console.log(e)
                        //expire jwt token
                        if (e.status == 401) {
                            window.location.href = base_url2 + "/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });
            });
            $('.deleteOne').on('click', function () {


                $.ajax({
                    url: base_url + '/api/availabilities/' + idDeleted,
                    method: 'DELETE',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify({
                        chain:false
                    }),
                    success: function (r) {
                        //console.log(r)
                        // $('#modal-deleteRepeated').modal('hide');
                      // var event = calendars['calendar'].getEventById(idDeleted);
                      //  event.remove();
                        calendars['calendar'].refetchEvents();


                    },
                    error: function (e) {

                        // console.log(e)
                        //expire jwt token
                        if (e.status == 401) {
                            window.location.href = base_url2 + "/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });
            });


        }else if($('#repeated').val() == 0){
            console.log('repeated = 0 delete')

            let idDeleted=$('#availIdRepeateddel').val();
            $.ajax({
                url: base_url + '/api/availabilities/'+ idDeleted,
                method: 'DELETE',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,

                },
                success: function(r){

                   // console.log(r)
                   // var event = calendars['calendar'].getEventById(idDeleted);
                   // event.remove();
                    calendars['calendar'].refetchEvents()

                },
                error:function(e) {

                    // console.log(e)
                    //expire jwt token
                    if(e.status == 401){
                        window.location.href = base_url2+"/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });
        }
    });


});
//calendars['calendar'].addEventSource();
