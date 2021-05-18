var calendars = {};
//check token if not set redirect to login page
let tok = localStorage.getItem('token');
if (localStorage.getItem('businessInfo') == null) {
    let businessName = JSON.parse(localStorage.getItem('businessInfo'));
    document.getElementById('businessName').innerHTML = businessName[0]['business']['name'];
}
let billing = JSON.parse(localStorage.getItem('billing'));
let personRole = localStorage.getItem('role');

if (tok == null) {
    window.location.href = base_url2 + "/login";
}

if (billing.useScheduler === false) {
    window.location.href = base_url2 + "/profile";
}

if (billing.useScheduler === true && personRole !== "employee") {
    document.getElementById('ForecastingBox').style.display = "block";
    document.getElementById('payPeriodBox').style.display = "block";
    document.getElementById('ReviewBox').style.display = "block";
    document.getElementById('scheduleShift').style.display = "block";
    document.getElementById('shiftsNotics').style.display = "block";
}

setTimeout(() => {
    document.getElementById('setLoading').style.display = 'none';
}, 5000);

async function loadUser(users) {//because first view of calendar is set on the users it should be work in positions view
    let uArray = [];
    let Recipients = [];
    uArray.push("<option value='/api/users/0'>OpenShift</option>");
    users.map((user) => {
        // console.log('user',user)
        uArray.push('<option value=' + user['@id'] + '>' + user['firstName'] + ' ' + user['lastName'] + '</option>')
        Recipients.push('<option value=' + user['@id'] + '>' + user['firstName'] + ' ' + user['lastName'] + '</option>')
    });
    $('select.select-emp').html(uArray);
    $('select#select-who').html(Recipients);
}


async function getScheduleJobsite(selectedSchedule) {

    $.ajax({
        method: 'get',
        url: base_url + '/api/job_sites?schedules=' + selectedSchedule,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success: (res) => {
            let jobsitesOpt = res['hydra:member'];
            console.log('jobsites----->',jobsitesOpt);

            localStorage.setItem("fullJobSites", JSON.stringify(jobsitesOpt));

            let jArray = [];
            let jcheck_box = [];
            jcheck_box.push('<div class="form-check">' +
                '<input checked class="form-check-input job-check-all job-check" type="checkbox" checked="" value="all">' +
                '<label class="form-check-label mt-1">All job sites</label>' +
                ' </div>');

            jcheck_box.push('<div class="form-check">' +
                '<input checked class="form-check-input job-check" type="checkbox" checked="" value="null">' +
                '<label class="form-check-label mt-1">No job sites</label>' +
                ' </div>');
            jArray.push('<option value="" disabled selected>select...</option>');
            jobsitesOpt.map((site) => {
                jArray.push('<option value=' + site['@id'] + '>' + site['name'] + '</option>')
                jcheck_box.push(
                    '<div class="form-check">' +
                    '<input checked class="form-check-input job-check"  checked="" type="checkbox" value="' + site['@id'] + '">' +
                    ' <label class="form-check-label mt-1">' + site['name'] + '</label>' +
                    '</div>')
            });
            $('select.jobs-list').html(jArray);
            $('.jobs-ls-check').html(jcheck_box.join(' '));
            $('.job-check-all').click(function () {
                console.log('check')
                $(".job-check").prop('checked', $(this).prop('checked'));
            });
            $('.job-check').click(function () {
                if (!($(this).prop('checked'))) {
                    $('.job-check-all').prop('checked', false);
                } else {
                    let x = true;
                    $.each($('.job-check:not(.job-check-all)'), function () {
                        console.log($(this).prop('checked'));
                        if (!($(this).prop('checked'))) {
                            x = false;
                        }
                    });
                    $('.job-check-all').prop('checked', x);
                }

                calendars['calendar'].refetchEvents();
            });


        },
        error: (e) => {
            //expire jwt token
            if (e.status == 401) {
                window.location.href = base_url2 + "/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var selectedSchedule;

    let shifts = [];
    let users = [];
    let scheduledObj={};

    let create_calendar = (id) => {
        var calendarEl = document.getElementById(id);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: '0231832627-fcs-1568628067',
            plugins: ['resourceTimeline'],
            timeZone: 'UTC',
            locale: 'en',
            firstDay: 1,
            allDay: false,
            aspectRatio: 1.5,
            height: 200,
            header: false,
            defaultView: 'resourceTimelineDay',
            displayEventTime:false,
            editable: true,
            eventOverlap: false,
            resourceLabelText: 'STAFF',
            resourceAreaWidth: 175,
            eventSources: [{
                events: (fetchInfo, success, fail) => {
                    selectedSchedule = $('#schedule-ls').children("option:selected").val();
                    let events = [];
                   // let rec_id_type = $('.nav.c-event-source .active ').data('resource');
                   // let typeColor = $('select.viewShift').val();
                    // console.log('color',typeColor)
                    let start = moment(fetchInfo.startStr).utc(0).format('YYYY-MM-DD HH:mm');
                    let end = moment(fetchInfo.endStr).utc(0).format('YYYY-MM-DD HH:mm');

                    console.log(fetchInfo, start, end, "~~~~~~~~~~~~~~~~~~~~~~~~~~", moment);
                    //on each fetch event calculate budget tools

                    //make event base on shifts
                    $.ajax({
                        url: encodeURI(base_url + '/api/shifts?scheduleId=' + selectedSchedule + '&startTime=' + start + '&endTime=' + end),
                        method: 'GET',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,
                        },
                        success: function (r) {

                            // console.log(r['hydra:member']);
                            scheduledObj={};//empty object for calculating scheduled
                            shifts = r['hydra:member']
                            console.log(r,'alllllllllllllll shifts')
                            shifts.map(shift => {
                                console.log('shift1', shift);
                                let keyrole,typeColor;
                                if(shift['ownerId'] == null){

                                    keyrole=null;
                                }else{
                                    keyrole=Object.keys(shift['ownerId']['userBusinessRoles'])[0];
                                }

                                let resources, res_id;
                                resources ='ownerId';
                                if (shift['ownerId'] !== null) {
                                    res_id=shift[resources]['@id'];
                                }

                                //show color
                                let finalColor = shift['color'];

                                let newTitle;
                                if (shift['positionId'] == null) {
                                    newTitle = "No Position";
                                } else {
                                    let checklastname;
                                    if (shift['ownerId'] == null) {
                                        checklastname = 'OpenShift';
                                    } else {
                                        checklastname = shift['ownerId']['lastName'];
                                    }
                                    newTitle = shift['positionId']['name']
                                    //newTitle = shift['positionId']['name'];

                                }
                                let event;
                                 if (shift['publish'] === true) {

                                    event = {
                                        id: shift['id'],
                                        resourceId: res_id,
                                        title: shift['startTime'].substring(11, 16) + '-' + shift['endTime'].substring(11, 16) + ' ' + newTitle,
                                        start: (new Date(shift['startTime'] + " UTC")).toISOString(),
                                        end: (new Date(shift['endTime'] + " UTC")).toISOString(),
                                        color: 'lightblue',
                                        publish: shift['publish'],
                                        rate:keyrole==null? '':shift['ownerId']['userBusinessRoles'][keyrole]['baseHourlyRate'],
                                        shiftID: shift['id'],
                                        className: 'font-weight-bold',
                                        editable: shift['editable'],
                                        shiftColor:shift['color']

                                    };
                                }
                                if (event !== undefined) {
                                    // console.log('undefinde');
                                    events.push(event);
                                }
                            });
                            success(events)
                        },
                        error: function (e) {

                            // console.log(e)
                            //expire jwt token
                            if (e.status == 401) {
                                window.location.href = base_url2 + "/login";
                            }
                            toastr.error('401 bad status error');
                        }
                    });

                }
            }],
            resources: (fetchInfo, successCallback, failureCallback) => {

                let url;
                let rec_array=[];
                //get selected schedule~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                let sch_selected_id = $('#schedule-ls option:selected').val();

                 url = '/api/users?userHasSchedule=' + sch_selected_id;

                // alert('info here' + base_url + url, sch_selected_id);
                //request and get resource~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                $.ajax({
                    method: 'get',
                    url: base_url + url,
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,
                    },
                    success: (responseResource) => {
                        let items = responseResource['hydra:member'];

                            items.map(user => {
                                // console.log('user', user);
                                let finalpos = [];
                                let poses = user['positions'];
                                poses.map(pos => {
                                    finalpos.push(pos['id']);
                                });
                                let keys_businessRole = Object.keys(user['userBusinessRoles']).map((i) => i);

                                let resources = {
                                    id: user['@id'],
                                    title: user['firstName'] + ' ' + user['lastName'],
                                    rate: keys_businessRole.length > 0 ? user['userBusinessRoles'][keys_businessRole[0]]['baseHourlyRate'] : 0,
                                    positionID: finalpos,
                                    prfHours: user['preferredHoursWeekly'],
                                    maxHours: keys_businessRole.length > 0 ? user['userBusinessRoles'][[keys_businessRole[0]]]['maxHoursWeek'] : 0

                                };

                                rec_array.push(resources);

                            });

                        successCallback(rec_array);
                    },
                    error: (e) => {
                        // console.log(e)
                        //expire jwt token
                        if (e.status == 401) {
                            window.location.href = base_url2 + "/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });

            },
            eventMouseEnter:function(eventMouseEnter){
                //event while rendering event
                console.log('eventMouseEnter', eventMouseEnter);
                let tooltipTxt = eventMouseEnter.event.title;
                // if (!eventRenderInfo.isMirror) {
                var tooltip = new Tooltip(eventMouseEnter.el, {
                    title: tooltipTxt,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
                // }

            },
            eventRender: function (eventRenderInfo) {
                $(".popover").remove();//remove all last tooltip
            },
            eventPositioned:function(eventPositionInfo){
                //event after events are positioned in their resource
                console.log('eventPositionInfo',eventPositionInfo.view.currentStart );
                let rate=0;
                let event = eventPositionInfo.event;
                let event_resource = event.getResources();
                console.log(event, event_resource, '~~~~~~~~~~~~~~~~~~~~~~~~eventResourceId');
                // console.log('bahar',event_resource[0]['id'])
                // console.log('bahar',event_resource)

                if (event_resource == []) {
                    if(event_resource[0]['id'] !== '/api/users/0') {

                        let owner = event_resource[0]['id'];
                        rate = parseInt(event.extendedProps.rate);

                        var a = moment(event['start']);
                        var b = moment(event['end']);
                        let difference = parseFloat((b.diff(a, 'hours', true)));
                        let calendar_range=a.isSameOrAfter(eventPositionInfo.view.currentStart) && a.isSameOrBefore(eventPositionInfo.view.currentEnd);

                        if (calendar_range && event.rendering !== "background"){
                            console.log('diff', difference);
                            if (typeof  scheduledObj[owner] === 'undefined') {
                                scheduledObj[owner] = [];
                                scheduledObj[owner]['diff'] = 0;
                                scheduledObj[owner]['diff'] =difference.toFixed(2);
                                scheduledObj[owner]['rate'] = rate ;



                            } else {
                                scheduledObj[owner]['diff'] =(parseFloat(scheduledObj[owner]['diff'])+ difference).toFixed(2);

                            }


                            console.log('in the function', scheduledObj);


                            /*      for (let [key, value] in scheduledObj) {

                                    console.log(value);


                                    $('.scheduledHours[data-us="' + key + '"]').text(value[0]);
                                    $('.budgetRate[data-bud="' + key + '"]').text(value[1] * rate);


                                }*/
                            Object.keys(scheduledObj).map(userid=>{
                                console.log(userid,scheduledObj[userid]['diff']);
                                $('.scheduledHours[data-us="' + userid + '"]').text(scheduledObj[userid]['diff']);
                                $('.budgetRate[data-bud="' + userid + '"]').text(scheduledObj[userid]['diff'] * scheduledObj[userid]['rate']);

                            });
                        }


                    }
                }

            },
            resourceRender: function (renderInfo) {
                console.log('resourceRender', renderInfo)

            },
            eventMouseLeave:function (eventMouseLeaveInfo) {
                console.log('eventMouseLeaveInfo',eventMouseLeaveInfo)
            }

        });
        calendars[id] = calendar;
        calendar.render();
    };
    create_calendar('calendar');


    //schedule list~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $.ajax({
        url: base_url + '/api/schedules',
        method: 'GET',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,

        },
        success: function (r) {
        
      
      
            let sch = r['hydra:member'];

            sch.forEach(function (el, index) {
                let ids = el['id'];
                let idr=el['@id'];
                let names = el['name'];

                $('select.sch-list').append("<option value="+idr+">"+names+"</option>");
                let opt;
                if (index === 0) {
                    opt = new Option(names, ids, true, true);
                    getScheduleJobsite(ids);
                } else {
                    opt = new Option(names, ids);
                }

                $('#schedule-ls').append(opt);
                let optAnnot;
                optAnnot = new Option(names, ids);
                $('#schedule-annot').append(optAnnot);


            });
            calendars['calendar'].refetchResources();
            calendars['calendar'].refetchEvents();

        },
        error: function (e) {

            //expire jwt token
            if (e.status == 401) {
                window.location.href = base_url2 + "/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });

    $("select#schedule-ls").change(function () {
        selectedSchedule = $(this).children("option:selected").val();
        getScheduleJobsite(selectedSchedule);
        calendars['calendar'].refetchResources();
        calendars['calendar'].refetchEvents();


    });

    calendars['calendar'].on('eventClick', function(info) {
        console.log('event Click ' + info.event.extendedProps.shiftColor);

        $('.shiftColor').css('background-color',  info.event.extendedProps.shiftColor);
        let dataForDrop={};
        //get shift info
        $.ajax({
            method:'GET',
            url:base_url+'/api/shifts/'+ info.event.id,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=>{
                console.log('selected event',res);
                //selected shift information
                let st= res['startTime'];
                let et= res['endTime'];
                $('.wDay').text(((new Date(st)).toString()).substring(0,3));
                $('.wd').text(((new Date(st)).toString()).substring(8,10));
                $('.startT').text(((new Date(st)).toString()).substring(16,21));
                $('.endT').text(((new Date(et)).toString()).substring(16,21));
                $('.schName').text(res['scheduleId']['name']);
                $('.posName').text(res['positionId']['name']);


                $('.date-time').text((new Date(st).toString()).substring(0,15)+' at '+((new Date(st)).toString()).substring(16,21)+'-'+((new Date(et)).toString()).substring(16,21)+' at '+res['scheduleId']['name'])
                $('.date-time2').text((new Date(st).toString()).substring(0,15)+' at '+((new Date(st)).toString()).substring(16,21)+'-'+((new Date(et)).toString()).substring(16,21)+' at '+res['scheduleId']['name'])

                //check shift request exist or not for the current shift
                if(res['asRequesterShiftToRequest'].length !== 0){
                    let requests=res['asRequesterShiftToRequest'];
                    console.log(requests)
                    let lastrequest=requests[(requests.length)-1];
                    console.log(lastrequest)
                    if(lastrequest['status'] === 'pendingAccept' || lastrequest['status'] === 'approve'){
                        console.log('dard')
                        $('#drop-shift').hide();
                        $('#swap-shift').hide();
                        $('#view-req').show();
                        console.log(lastrequest['id'])

                        $('.viewRequest').attr('data-reqid', lastrequest['id']);


                    }

                }

                dataForDrop={
                    positionId:res['positionId']['@id'],
                    scheduleId:res['scheduleId']['@id'],
                    startTime:res['startTime'],
                    endTime:res['endTime']
                };

                //check drops
                $.ajax({
                    method:'POST',
                    url:base_url+'/api/users/get_eligible',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,
                    },
                    data:JSON.stringify(dataForDrop),
                    success:(r)=>{
                        console.log('eligible',r['hydra:member']);
                        let eligibles=r['hydra:member'];

                        if(eligibles.length !== 0) {
                            $('#drop-shift').show();
                            //show them in the new modal
                            let elArray = [];
                            eligibles.map((elig) => {
                                console.log('eligs',elig)

                                elArray.push(' <div class="col-5 border rounded ml-2 p-2" style="background-color: white">\n' +
                                    '                            <div class="c-item"><img src="../../img/pic4.png" style="height: 30px;width: 30px;"></div>\n' +
                                    '                            <div class="c-item ml-1">\n' +
                                    '                                <label>' + elig['firstName'] + ' ' + elig['lastName'] + '</label>\n' +
                                    '                                <input class="ml-5 eligChbox" type="checkbox" value="' + elig['@id'] + '" >\n' +
                                    '\n' +
                                    '                            </div>\n' +
                                    '                        </div>');
                            });
                            $('.eligDrop').html(elArray);
                        }else{
                            $('#drop-shift').hide()
                        }


                    },
                    error:(e)=>{
                        //expire jwt token
                        if(e.status == 401){
                            window.location.href = base_url2+"/login";
                        }
                    }
                });


            },
            error:(e)=>{
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });

        if(info.event.extendedProps.ownerId === '/api/users/0'){

            $.ajax({
                method:'GET',
                url:base_url+'/api/shifts/'+ info.event.id,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success:(res)=>{
                    console.log(res,'event');
                    $('.openshift-item').html(' <div class="col-8 mt-2 op-item">\n' +
                        '                        <div class="d-inline-block text-white p-2 text-center rounded" style="color: white;background-color: lightgray;">\n' +
                        '                            <div style="font-size: 12px;">' + (new Date(res['startTime']).toString()).substring(0, 3) + ' , ' + (new Date(res['endTime']).toString()).substring(4, 7) + '</div>\n' +
                        '                            <div>' + (new Date(res['startTime']).toString()).substring(7, 10) + '</div>\n' +
                        '                        </div>\n' +
                        '                        <div class="d-inline-block p-1" style="line-height: 15px;">\n' +
                        '                            <div class="font-weight-bold">'+res['startTime'].substring(11,16)+' - '+res['endTime'].substring(11,16)+'</div>\n' +
                        '                            <div><small>At '+res['scheduleId']['name']+'</small></div>\n' +
                        '                        </div>\n' +
                        '                        <div class="d-inline-block ml-4">\n' +
                        '                        <button type="button" class="takeshift btn btn-sm btn-custbl" data-id="/api/shifts/'+res['id']+'" style="display: none;" data-miss="modal">Take Shift</button>\n' +
                        '                    </div>\n' +
                        '                    </div>\n' +
                        '                  ');

                    $('.takeshift ').on('click',function () {
                        let stringID= $(this).data('id');
                        console.log(stringID);
                        $.ajax({
                            method: 'POST',
                            url: base_url + '/api/shifts/take_open_shifts',
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,
                            },
                            data:JSON.stringify({
                                shift:stringID
                            }),
                            success: (res) => {
                                console.log(res)
                                // location.reload();
                                toastr.success('Shift Successfully Taken.');
                                //should edit this shift and the modal's button will change
                                $('#modal-eventClickOpshift').modal('hide');
                                var event = calendars['calendar'].getEventById(res['id']);
                                event.remove();
                                calendars['calendar'].refetchEvents();
                            },
                            error: (e) => {
                                //expire jwt token
                                if (e.status == 401) {
                                    window.location.href = base_url2 + "/login";
                                }
                                toastr.error(e['responseJSON']['hydra:description']);
                            }
                        });



                    });

                },
                error:(e)=>{
                    //expire jwt token
                    if(e.status == 401){
                        window.location.href = base_url2+"/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });

            $('#modal-eventClickOpshift').modal('show');
        }else{


            if(info.event.extendedProps.confirm === false){
                $('#shifteditedId').val(info.event.id);
                $('#confirm-shift').show();
            }
            $('#strIdShiftsw').val(info.event.id);
            $('#strIdShift').val(info.event.id);

            let replaceArray = [];
            //check swaps
            $.ajax({
                method:'POST',
                url:base_url+'/api/shifts/eligible_swap',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data:JSON.stringify({
                    shift:'/api/shifts/'+info.event.id
                }),
                success:(res)=>{
                    console.log(res);
                    if(res['hydra:member'].length !== 0){

                        let st= res['startTime'];
                        let et= res['endTime'];

                        //fill modal swap
                        let swaps=res['hydra:member'];
                        swaps.map((sw) => {
                            // console.log('eligswaps',sw)

                            let stt=new Date(sw['startTime']).toString();
                            let ett=new Date(sw['endTime']).toString();

                            replaceArray.push('<div class="col-5 border rounded ml-2 p-2" style="background-color: white;">\n' +
                                '                                  <div class="c-item text-center" style="background-color: '+sw['color']+';color:white;padding: 2px 10px;">\n' +
                                '                                      <div>'+stt.substring(0,3)+'</div>\n' +
                                '                                      <div>'+stt.substring(8,10)+'</div>\n' +
                                '                                  </div>\n' +
                                '                                  <div class="c-item ml-1" style="line-height: 23px;">\n' +
                                '                                      <div style="font-size: 12px">'+stt.substring(16,21)+' - '+ett.substring(16,21)+' @ '+sw['scheduleId']['name']+'</div>\n' +
                                '                                      <div style="font-size: 10px">'+sw['ownerId']['firstName']+' '+(sw['ownerId']['lastName']).charAt(0)+'. as '+sw['positionId']['name']+'</div>\n' +
                                '\n' +
                                '\n' +
                                '                                  </div>\n' +
                                '                                 <div class="c-item"><input class="ml-5 swapChbox" type="checkbox" value="/api/shifts/'+sw['id']+'"></div>\n' +
                                '                              </div>');
                        });
                       // $('.eligSwap').html(swArray);
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


            //get shift info
            $.ajax({
                method:'GET',
                url:base_url+'/api/shifts/'+ info.event.id,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success:(res)=>{
                    console.log(res,'event');
                    //selected shift information
                    let st= res['startTime'];
                    let et= res['endTime'];
                    $('.wDay').text(((new Date(st)).toString()).substring(0,3));
                    $('.wd').text(((new Date(st)).toString()).substring(8,10));
                    $('.startT').text(((new Date(st)).toString()).substring(16,21));
                    $('.endT').text(((new Date(et)).toString()).substring(16,21));
                    $('.schName').text(res['scheduleId']['name']);
                    $('.posName').text(res['positionId']['name']);


                    $('.date-time').text((new Date(st).toString()).substring(0,15)+' at '+((new Date(st)).toString()).substring(16,21)+'-'+((new Date(et)).toString()).substring(16,21)+' at '+res['scheduleId']['name'])
                    $('.date-time2').text((new Date(st).toString()).substring(0,15)+' at '+((new Date(st)).toString()).substring(16,21)+'-'+((new Date(et)).toString()).substring(16,21)+' at '+res['scheduleId']['name'])

                    dataForDrop={
                        positionId:res['positionId']['@id'],
                        scheduleId:res['scheduleId']['@id'],
                        startTime:res['startTime'],
                        endTime:res['endTime']
                    };

                    //check drops
                    $.ajax({
                        method:'POST',
                        url:base_url+'/api/users/get_eligible',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,
                        },
                        data:JSON.stringify(dataForDrop),
                        success:(r)=>{
                            console.log('eligible',r['hydra:member']);
                            let eligibles=r['hydra:member'];

                            if(eligibles.length !== 0) {

                                //show them in the new modal

                                eligibles.map((elig) => {
                                    console.log('eligs',elig)

                                    replaceArray.push(' <div class="col-5 border rounded ml-2 p-2" style="background-color: white">\n' +
                                        '                            <div class="c-item"><img src="../../img/pic4.png" style="height: 30px;width: 30px;"></div>\n' +
                                        '                            <div class="c-item ml-1">\n' +
                                        '                                <label>' + elig['firstName'] + ' ' + elig['lastName'] + '</label>\n' +
                                        '                                <input class="ml-5 eligChbox" type="checkbox" value="' + elig['@id'] + '" >\n' +
                                        '\n' +
                                        '                            </div>\n' +
                                        '                        </div>');
                                });

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

                    console.log('replaceArray',replaceArray)
                    if(replaceArray.length !== 0){
                        $('#replacement-shift').show();
                        $('.eligDrop').html(elArray);
                    }else{
                        $('#replacement-shift').hide();
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


            $('#modal-eventClick').modal('show');


        }
    });

    if (personRole !== "employee") {
    //load attendance notice~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $.ajax({
        url:base_url+'/api/shifts/notice',
        method:'get',
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(rs_notices)=>{
            let $att_wrapper=$('#attendance-notice-wrapper');
            $att_wrapper.html('');
            console.log(rs_notices,'ressss periodddds');
           if (true){
               let notices=rs_notices;
               if (notices.length===0){
                   $('.notice-card').hide();
               }
               notices.map(not=>{
                   let sh_notice=JSON.parse(not['shift']);
                   console.log(not);
                   console.log($('#attendance-notice-wrapper'),'wrapper');
                   console.log(sh_notice)
                   let owner;
                   if (sh_notice.ownerId === null) {
                    // owner = sh_notice.ownerId;
                    console.log('null')
                   }else {
                    console.log('notNull')
                    owner = sh_notice.ownerId
                   }
                   let notic_wrapper = document.getElementById('attendance-notice-wrapper');
                   setTimeout(() => {
                       if (owner != undefined) {
                            notic_wrapper.innerHTML = `
                                <div class="col-4 pt-2 pb-2 border rounded" style="background-color: white">
                                    <div class="d-inline">
                                        <img src="../../img/pic4.png" style="height: 30px;width: 30px;">
                                    </div>
                                    <div class="d-inline">
                                        <label class="mr-1">${not['status']}</label>
                                        <label class="mr-1">${owner['firstName']} ${owner['lastName']}</label>
                                    </div>
                                </div>
                            `
                       }

                   }, 2000);

               });
           }
        }
    });

    //LOAD FORCAST~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $.ajax({
        method:'GET',
        url:base_url+'/api/shifts/scheduled_comparison_dashboard',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log('die',res)
                let {actual_wages,
                    last_week_scheduled,
                    last_week_worked,
                    lastweek_scheduled_wages,
                    scheduled,
                    scheduled_wage}=res[0];
                    console.log('scheduled',scheduled,last_week_scheduled);

            actual_wages= actual_wages!==null?parseFloat(actual_wages):0;
            last_week_scheduled= last_week_scheduled!==null?parseFloat(last_week_scheduled/60):0;
            lastweek_scheduled_wages= lastweek_scheduled_wages!==null?parseFloat(lastweek_scheduled_wages):0;
            scheduled= scheduled!==null?parseFloat(scheduled/60):0;
            scheduled_wage= scheduled_wage!==null?parseFloat(scheduled_wage):0;
            last_week_worked!==null?parseFloat(last_week_scheduled/60):0;
            console.log('die',actual_wages,
            lastweek_scheduled_wages,
                scheduled,
                last_week_worked,
            scheduled_wage,scheduled);
            let last_week_fewer_wage=((lastweek_scheduled_wages-scheduled_wage)/lastweek_scheduled_wages)*100;
            let last_week_fewer_labor=((last_week_scheduled-scheduled)/last_week_scheduled)*100;
            let actual_week_fewer_wage=(parseFloat(scheduled_wage-actual_wages)/scheduled_wage)*100;
            let actual_week_fewer_labor=((last_week_worked-scheduled)/last_week_worked)*100;
            $('#fewer-last-wage').html(parseFloat(last_week_fewer_wage).toFixed(2));
            $('#fewer-actual_wage').html(parseFloat(actual_week_fewer_wage).toFixed(2));
            $('#fewer-last-week_labor').html(parseFloat(last_week_fewer_labor).toFixed(2));
            $('#fewer-actual_labor').html(parseFloat(actual_week_fewer_labor).toFixed(2));
            console.log(last_week_fewer_wage,
            last_week_fewer_labor,
            actual_week_fewer_wage,
            actual_week_fewer_labor);


// Build the chart
            Highcharts.chart('container_wage', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Comparison scheduled hours'
                },

                xAxis: {
                    type: 'category',
                    labels: {
                        rotation: -45,
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Wages'
                    }
                },
                legend: {
                    enabled: false
                },

                series: [{
                    data: [
                        ['Actual wages', actual_wages],
                        ['Lastweek scheduled wages', lastweek_scheduled_wages ],
                        ['Scheduled wage', scheduled_wage ]
                    ],
                    dataLabels: {
                        enabled: true,
                        rotation: -90,
                        color: '#FFFFFF',
                        align: 'right',
                        format: '{point.y:.1f}', // one decimal
                        y: 10, // 10 pixels down from the top
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                }]
            });
            Highcharts.chart('container_labor', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Comparison scheduled hours'
                },

                xAxis: {
                    type: 'category',
                    labels: {
                        rotation: -45,
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Hours '
                    }
                },
                legend: {
                    enabled: false
                },

                series: [{
                    name: 'Population',
                    data: [
                        ['Scheduled',scheduled],
                        ['Last week scheduled', last_week_scheduled ],
                        ['Last week worked', last_week_worked ]
                    ],
                    dataLabels: {
                        enabled: true,
                        rotation: -90,
                        color: '#FFFFFF',
                        align: 'right',
                        format: '{point.y:.1f}', // one decimal
                        y: 10, // 10 pixels down from the top
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                }]
            });

        }
    });
    //load pay period completed last~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $.ajax({
        url:base_url+'/api/attendance_periods/getClosed?closed=true&page=1',
        method:'get',
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res_periods)=>{
            console.log(res_periods,'periods=1');
            if (typeof res_periods["hydra:member"]!=='undefined'){
                let periods=res_periods['hydra:member'];
                if (periods.length>0){
                    let $last=periods[periods.length-1];
                    $('#last-period-closed').html(moment($last['startTime']).format('MMMM DD') +' - '+moment($last['endTime']).format('MMMM DD , YYYY'))
                }else {
                    document.getElementById('last-period-closed').innerHTML = '';
                }
            }
        }
    });

    //load last uncompelete period~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $.ajax({
        url:base_url+'/api/attendance_periods/getClosed?closed=false&page=1',
        method:'get',
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res_periods)=>{
            console.log(res_periods,'periods=0');
            console.log(moment(new Date()).format('MMMM-DD-YYYY'));

            $.ajax({
                url:base_url+'/api/budget_tools/forecast',
                method:'POST',
                headers: {
                    'Authorization': `Bearer ${tok}`,
                    'content-type': 'application/json'
                },
                data:JSON.stringify({
                    "startTime": res_periods['hydra:member'][0]['end_time'],
                    "endTime": res_periods['hydra:member'][0]['start_time'],
                    "date": moment().format('M-DD-YYYY')
                }),
                success:(forecast)=>{
                    console.log('data forecast',forecast);
                    let billing = JSON.parse(localStorage.getItem('billing'));
                    let priceBudget = parseFloat(forecast.scheduled).toFixed(2);
                    let remainBudget = parseFloat(forecast.worked).toFixed(2);
                    document.getElementById('budgetPrice').innerHTML =  `${billing.currency['symbol']} ${priceBudget}`;
                    document.getElementById('remainingPrice').innerHTML = `${billing.currency['symbol']} ${remainBudget}`;

                    let {worked,scheduled}=forecast;
                    let percent=parseFloat(worked/scheduled*100).toFixed(2)
                    console.log(percent);
                    $('#wage-progress').css('width',percent+'%')
                }
            })

            if (typeof res_periods["hydra:member"]!=='undefined'){
                let periods=res_periods['hydra:member'];
                let period=null;
                periods.map(per=>{
                    if (moment(per.startTime).isBefore(moment()) && (moment(per.endTime).isAfter(moment()))){
                        period=per
                    }
                });
                if (period!==null){
                    $('#current-pay-period').html(moment(period['startTime']).format('MMMM DD') +' - '+moment(period['endTime']).format('MMMM DD , YYYY'));
                }else {
                    document.getElementById('current-pay-period').innerHTML = '';
                }
            }
        }
    });


   }

});
