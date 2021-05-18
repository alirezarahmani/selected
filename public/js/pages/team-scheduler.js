var calendars = {};
//check token if not set redirect to login page
let tok = localStorage.getItem('token');

if (tok == null) {
    window.location.href = base_url2 + "/login";
}
let allSchedules = JSON.parse(localStorage.getItem('schedules'));
let seeOtherPosition = localStorage.getItem('seeOtherPosition');
let allPositions = JSON.parse(localStorage.getItem('positions'));
console.log(Object.keys(allSchedules).length)

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
            let jArray = [];
            let jcheck_box = [];
            jcheck_box.push('<div class="form-check">' +
                '<input checked class="form-check-input job-check-all job-check" type="checkbox" checked="" value="all">' +
                '<label class="form-check-label mt-1">All job sites</label>' +
                ' </div>');

            jcheck_box.push('<div class="form-check">' +
                '<input checked class="form-check-input pos-check-all job-check" type="checkbox" checked="" value="null">' +
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
async function getPositions() {
    //get position List
    $.ajax({
        method: 'get',
        url: base_url + '/api/positions',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success: (_pos) => {
            //console.log('new list',res['hydra:member']);
            let positions = _pos['hydra:member'];
            let pArray = [];
            let pcheck_box = [];
            pcheck_box.push('<div class="form-check">' +
                '<input checked class="form-check-input pos-check-all pos-check" type="checkbox"  value="all">' +
                '<label class="form-check-label mt-1">All positions</label>' +
                ' </div>');
            pcheck_box.push('<div class="form-check">' +
                '<input checked class="form-check-input  pos-check" type="checkbox"  value="null" >' +
                '<label class="form-check-label mt-1">No position</label>' +
                ' </div>');

            pArray.push('<option value="" disabled selected>select...</option><option value="">No Position</option>');

            positions.map((pos) => {
                // console.log('pos',pos)
                pArray.push('<option value=' + pos['@id'] + '>' + pos['name'] + '</option>');
                pcheck_box.push('<div class="form-check">' +
                    '<input checked class="form-check-input pos-check" type="checkbox" value="' + pos['@id'] + '">' +
                    '<label class="form-check-label mt-1">' + pos['name'] + '</label>' +
                    ' </div>');
            });

            $('select.pos-list').html(pArray);
            $('.positions-ls-check').html(pcheck_box.join(' '));
            $('.pos-check-all').click(function () {
                console.log('check');
                $(".pos-check").prop('checked', $(this).prop('checked'));
            });
            $('.pos-check').click(function () {
                if (!($(this).prop('checked'))) {
                    $('.pos-check-all').prop('checked', false);
                } else {
                    let x = true;
                    $.each($('.pos-check:not(.pos-check-all)'), function () {
                        console.log($(this).prop('checked'));
                        if (!($(this).prop('checked'))) {
                            x = false;
                        }
                    });
                    $('.pos-check-all').prop('checked', x);
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
    let timeoffs = [];
    let availability = [];
    let users = [];
    let listPositions = [];
    let scheduledObj={};


    let create_calendar = (id) => {
        var calendarEl = document.getElementById(id);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: '0231832627-fcs-1568628067',
            plugins: ['dayGrid', 'timeGrid', 'interaction', 'resourceTimeline'],
            timeZone: 'UTC',
            locale: 'en',
            firstDay: 1,
            datesRender: (info) => {
                console.log('dates render')
                console.log(info.view);
                //$('.annot-content').empty();
                //$('.annotation-wrapper ul.annotation li.c-nav-item .c-nav-link').css('color','white');
                let start = moment(info.view.currentStart).format('YYYY-MM-DD');
                let dayStart = moment(info.view.currentStart).format('dddd DD');
                let end = moment(info.view.currentEnd).format('YYYY-MM-DD');

            },
            allDay: false,
            aspectRatio: 1.5,
            height: 500,
            header: {
                left: 'prev,next',
                center: 'title',
                right: 'resourceTimelineDay,resourceTimelineWeek,dayGridMonth'
            },
            defaultView: 'resourceTimelineDay',
            views: {
                resourceTimelineWeek: {
                    type: 'resourceTimelineWeek',
                    buttonText: 'week',
                    columnHeader: false,
                    slotDuration: {hours: 6},
                    slotLabelInterval: {hours: 24},
                    resourceAreaWidth: 175,
                    slotWidth: 50,
                    slotLabelFormat: [
                        {weekday: 'long', day: 'numeric'}, // top level of text
                    ]
                }
            },
            editable: true,
            eventOverlap: false,
            resourceLabelText: 'Employee',
            resourceAreaWidth: 175,
            eventSources: [{
                events: (fetchInfo, success, fail) => {
                    selectedSchedule = $('#schedule-ls').children("option:selected").val();

                    let events = [];
                    let typeColor = $('select.viewShift').val();
                    let start = moment(fetchInfo.startStr).utc(0).format('YYYY-MM-DD HH:mm');
                    let end = moment(fetchInfo.endStr).utc(0).format('YYYY-MM-DD HH:mm');
                    console.log(fetchInfo, start, end, "~~~~~~~~~~~~~~~~~~~~~~~~~~", moment);
                    //make event base on shifts
                    $.ajax({
                        url: encodeURI(base_url + '/api/shifts?scheduleId=' + selectedSchedule + '&startTime=' + start + '&endTime=' + end),
                        method: 'GET',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,
                        },
                        success: function (r) {

                            scheduledObj={};//empty object for calculating scheduled
                            shifts = r['hydra:member'];
                            console.log('all shifts',shifts);
                            let filtered = [];
                            $.each($(".pos-check:checked"), function () {
                                filtered.push($(this).val());
                            });
                            let job_filtered = [];
                            $.each($(".job-check:checked"), function () {
                                job_filtered.push($(this).val());
                            });
                            console.log(filtered,job_filtered);

                            shifts.filter(shft => {

                                let poId = shft['positionId'] !== null ? shft['positionId']['@id'] : "null";
                                return !filtered.includes("all") && shft['positionId'] !== null ? filtered.includes(poId) : true;
                            }).filter(shft => {

                                let jid = shft['jobSitesId'] !== null ? shft['jobSitesId']['@id'] : "null";
                                return !job_filtered.includes("all") && shft['jobSitesId'] !== null ? job_filtered.includes(jid) : true;
                            }).map(shift => {

                                console.log('shift1', shift);

                                let event,newTitle;
                                if(shift['positionId'] == null){
                                    newTitle='No Position';
                                }else{
                                    newTitle=shift['positionId']['name'];
                                }

                                //show color
                                let finalColor;
                                if (typeColor === "shift") {
                                    finalColor = shift['color'];

                                } else if (typeColor === "position") {
                                    if (shift['positionId'] == null) {
                                        finalColor = '#888888';
                                    } else {
                                        finalColor = shift['positionId']['color'];
                                    }

                                }

                                 if (shift['publish'] === true) {

                                    if(shift['ownerId'] == null ){

                                       shift['eligibleOpenShiftUser'].map((eligs)=>{

                                          if(eligs['email'] === localStorage.getItem('email')){
                                              console.log('marg',eligs)
                                              event = {
                                                  id: shift['id'],
                                                  resourceId: '/api/users/0',
                                                  title: shift['startTime'].substring(11, 16) + '-' + shift['endTime'].substring(11, 16) + ' ' + newTitle,
                                                  start: (new Date(shift['startTime'] + " UTC")).toISOString(),
                                                  end: (new Date(shift['endTime'] + " UTC")).toISOString(),
                                                  color: finalColor,
                                                  publish: shift['publish'],
                                                  shiftID: shift['id'],
                                                  editable: shift['editable']

                                              };
                                              events.push(event);

                                          }
                                       });



                                    }else {
                                        event = {
                                            id: shift['id'],
                                            resourceId: shift['ownerId']['@id'],
                                            title: shift['startTime'].substring(11, 16) + '-' + shift['endTime'].substring(11, 16) + ' ' + newTitle,
                                            start: (new Date(shift['startTime'] + " UTC")).toISOString(),
                                            end: (new Date(shift['endTime'] + " UTC")).toISOString(),
                                            color: finalColor,
                                            publish: shift['publish'],
                                            shiftID: shift['id'],
                                            editable: shift['editable']

                                        };
                                        events.push(event);
                                    }




                                }

                                 console.log('last Events',events)
                            });
                            success(events)
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



                }
            }, {
                events: (fetchInfo, success, fail) => {
                    let start = moment(fetchInfo.startStr).utc(0).format('YYYY-MM-DD HH:mm');
                    let end = moment(fetchInfo.endStr).utc(0).format('YYYY-MM-DD HH:mm');
                    let events=[];
                    $.ajax({
                        url: encodeURI(base_url+'/api/time_off_requests?startTime='+ start +'&endTime='+ end ),
                        method: 'GET',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        success: function(r){
                            //console.log(r['hydra:member']);
                            timeoffs=r['hydra:member'];
                            timeoffs.map(timeoff=>{
                                console.log('timeoff',timeoff);

                                let event;
                                if(timeoff['Status'] === 'accepted' && timeoff['userID']['email'] === localStorage.getItem('email')){

                                    event={
                                        id:timeoff['id'],
                                        resourceId:timeoff['userID']['@id'],
                                        start:(new Date(timeoff['startTime']+" UTC")).toISOString(),
                                        end:(new Date(timeoff['endTime']+" UTC")).toISOString(),
                                        color:'#D3D3D3',
                                        rendering: 'background'
                                    };
                                    events.push(event);
                                }




                            });

                            success(events);
                        },
                        error:function(e) {

                            // console.log(e)
                            //expire jwt token
                            if(e.status == 401){
                                toastr.error(e['responseJSON']['hydra:description']);
                                window.location.href = base_url2+"/login";
                            }

                        }
                    });
                }
            }],
            resources: (fetchInfo, successCallback, failureCallback) => {

                let url, rec_array, firstPart,secPart;

                //get selected schedule~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                let sch_selected_id = $('#schedule-ls option:selected').val();


                  firstPart='/api/users?userHasSchedule=' + sch_selected_id;
                    rec_array = [{
                        id: "/api/users/0",
                        title: 'OpenShifts',
                        eventColor: 'red'
                    }];

                // alert('info here' + base_url + url, sch_selected_id);

                if(seeOtherPosition === 'true'){
                    url = firstPart;
                }else{

                    if(Object.keys(allPositions).length !== 1){
                        let mine;
                        allPositions.forEach(function (el, index) {
                            let idd = el['id'];

                            if (index === 0) {
                                mine='&positions%5B%5D='+idd;
                            } else {
                                mine+= '&positions%5B%5D='+idd
                            }


                        });
                        secPart=mine;

                    }else{

                      let key=Object.keys(allPositions)[0];
                        let idd= allPositions[key]['id'];

                        secPart='&positions%5B%5D='+idd;
                    }


                    url = firstPart + secPart;
                }
                //request and get resource~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                $.ajax({
                    method: 'get',
                    url:encodeURI(base_url + url),
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,
                    },
                    success: (responseResource) => {

                        let items = responseResource['hydra:member'];

                            items.map(user => {
                                console.log('user', user);
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
            eventRender: function (eventRenderInfo) {
                //event while rendering event
                console.log('eventRender info', eventRenderInfo);
                let tooltipTxt = eventRenderInfo.event.title;
                if (!eventRenderInfo.isMirror) {
                    var tooltip = new Tooltip(eventRenderInfo.el, {
                        title: tooltipTxt,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }

            },
            eventPositioned:function(eventPositionInfo){
                //event after events are positioned in their resource
                console.log('eventPositionInfo',eventPositionInfo)
                let rate=0;
                let event = eventPositionInfo.event;
                let event_resource = event.getResources();
                console.log(event, event_resource, '~~~~~~~~~~~~~~~~~~~~~~~~eventResourceId');
                console.log('bahar',event_resource[0]['id'])

                if(event_resource[0]['id'] !== '/api/users/0') {
                    let owner = event_resource[0]['id'];
                    rate = parseInt(event.extendedProps.rate);


                    var a = moment(event['start']);
                    var b = moment(event['end']);
                    let difference = parseFloat((b.diff(a, 'hours', true)).toFixed(2));

                    console.log('diff', difference);
                    if (typeof  scheduledObj[owner] === 'undefined') {
                        scheduledObj[owner] = [];
                        scheduledObj[owner]['diff'] = 0;
                        scheduledObj[owner]['diff'] =0 + difference;
                        scheduledObj[owner]['rate'] = rate ;



                    } else {
                        scheduledObj[owner]['diff'] = parseInt(scheduledObj[owner]['diff'])+ difference;

                    }


                    console.log('in the function', scheduledObj)


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




            },
            resourceRender: function (renderInfo) {
                console.log('resourceRender', renderInfo)
                let startView = moment(renderInfo.view.currentStart).format('YYYY-MM-DD');
                let endView = moment(renderInfo.view.currentEnd).format('YYYY-MM-DD');


                let rec_id_type = $('.nav.c-event-source .active ').data('resource');

                let _userId;
                if (rec_id_type !== 'position') {
                    //employee is resource
                    if(renderInfo['resource']['id']==='/api/users/0'){
                        console.log('OpenShift');
                        let _userId=(renderInfo['resource']['id']).slice(11);
                        let personpic = document.createElement('img');
                        personpic.classList.add('pic-span', 'mr-2', 'float-left');
                        personpic.src = imgAssetDirOpenshift;
                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(personpic);


                        let more = document.createElement('strong');
                        more.classList.add('rem');
                        more.style.position = 'absolute';


                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(more);

                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(personpic);
                        let infoMark = document.createElement('div');
                        infoMark.classList.add('rem');

                    }else{
                        let _userId=(renderInfo['resource']['id']).slice(11);
                        let getname = (renderInfo.resource.title).split(' ');
                        let name = getname[0] + " '";

                        let more = document.createElement('strong');
                        more.classList.add('rem');
                        more.style.position = 'absolute';
                        let personpic = document.createElement('img');
                        personpic.classList.add('pic-span', 'mr-2', 'mt-1', 'float-left');
                        personpic.src = imgAssetDir;


                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(more);

                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(personpic);
                        let infoMark = document.createElement('div');
                        infoMark.classList.add('rem');
                        let icon = document.createElement('i');
                        icon.classList.add("far", "fa-clock", "mr-1");
                        let txtdiv = document.createElement('div');
                        txtdiv.classList.add("d-inline");
                        txtdiv.title = "Preferred Hours / Scheduled Hours / Max Hours";
                        let span1 = document.createElement('span');
                        let span2 = document.createElement('span');
                        span2.setAttribute('data-us', renderInfo['resource']['id']);
                        let span3 = document.createElement('span');

                        let maxHours;
                        if (renderInfo.resource.extendedProps.maxHours == null) {
                            maxHours = 0;
                        } else {
                            maxHours = renderInfo.resource.extendedProps.maxHours;
                        }
                        let prfHours;
                        if (renderInfo.resource.extendedProps.prfHours == null) {
                            prfHours = 0;
                        } else {
                            prfHours = renderInfo.resource.extendedProps.prfHours;
                        }

                        span1.innerText= prfHours+'/';
                        span2.classList.add('scheduledHours');
                        span2.innerText=0;
                        span3.innerText='/'+ maxHours;
                        infoMark.appendChild(icon);
                        infoMark.appendChild(txtdiv);
                        txtdiv.appendChild(span1);
                        txtdiv.appendChild(span2);
                        txtdiv.appendChild(span3);

                        let infoMark2 = document.createElement('div');
                        infoMark2.classList.add('rem2');
                        let icon2 = document.createElement('i');
                        icon2.classList.add("fas", "fa-pound-sign", "mr-1");
                        let txt2 = document.createElement('span');
                        txt2.classList.add("budgetRate");
                        txt2.setAttribute('data-bud', renderInfo['resource']['id']);
                        txt2.innerText = 0;
                        infoMark2.appendChild(icon2);
                        infoMark2.appendChild(txt2);

                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(infoMark);
                        renderInfo.el.querySelector('.fc-cell-text')
                            .appendChild(infoMark2);
                        infoMark2.style.display = 'none';


                    }




                    if ($('#showbudgetemp').is(':checked')) {
                        $('div.rem').hide();
                        $('div.rem2').show();
                    } else {
                        $('div.rem').show();
                        $('div.rem2').hide();
                    }



                } else {

                    //position is resource
                    let posCircle = document.createElement('span');
                    posCircle.classList.add('color-pos', 'mr-2', 'rounded-circle', 'float-left');
                    let _col = renderInfo.resource.extendedProps.color;
                    posCircle.style.backgroundColor = _col;
                    renderInfo.el.querySelector('.fc-cell-text')
                        .appendChild(posCircle);
                }


            }

        });
        calendars[id] = calendar;
        calendar.render();
    };
    create_calendar('calendar');
    //get positions~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    getPositions();
    if(Object.keys(allSchedules).length !== 1){

        //multi schedule
        allSchedules.forEach(function (el, index) {
            let ids = el['id'];
            let names = el['name'];
            let opt;
            if (index === 0) {

                getScheduleJobsite(ids);
                opt = new Option(names, ids, true, true);
            } else {
                opt = new Option(names, ids);
            }


            $('#schedule-ls').append(opt);


        });
        calendars['calendar'].refetchResources();
        calendars['calendar'].refetchEvents();
    }else{

        //one schedule
        let ids = allSchedules[0]['id'];
        let names = allSchedules[0]['name'];
        let opt;
        getScheduleJobsite(ids);
       console.log('all',allSchedules)
        opt = new Option(names, ids, true, true);
        $('#schedule-ls').append(opt);
        calendars['calendar'].refetchResources();
        calendars['calendar'].refetchEvents();

    }
    $("select#schedule-ls").change(function () {
        selectedSchedule = $(this).children("option:selected").val();
        getScheduleJobsite(selectedSchedule);
        calendars['calendar'].refetchResources();
        calendars['calendar'].refetchEvents();


    });


    let defaultDateObject = calendars['calendar'].getDate();
    let defaultDate = (defaultDateObject.toISOString()).substring(0, 10);
    let defaultDateStart = defaultDate + ' 00:00';
    let defaultDateEnd = defaultDate + ' 23:59';

    calendars['calendar'].on('eventClick', function (info) {
        console.log('event Click ' + info.event._def.resourceIds[0]);


        if(info.event._def.resourceIds[0] === '/api/users/0'){

            console.log('stop')
            $.ajax({
                method:'GET',
                url:base_url+'/api/shifts/'+ info.event.id,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success:(res)=>{
                    console.log('click open',res);
                    $('.openshift-item').html(' <div class="col-8 mt-2 op-item">\n' +
                        '                        <div class="d-inline-block text-white p-2 text-center rounded" style="color: white;background-color: lightgray;">\n' +
                        '                            <div style="font-size: 12px;">' + (new Date(res['startTime']).toString()).substring(0, 3) + ' , ' + (new Date(res['endTime']).toString()).substring(4, 7) + '</div>\n' +
                        '                            <div>' + (new Date(res['startTime']).toString()).substring(7, 10) + '</div>\n' +
                        '                        </div>\n' +
                        '                        <div class="d-inline-block p-2" style="line-height: 15px;">\n' +
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
                                   // window.location.href = base_url2 + "/login";
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
        }


    });

    $('#vert-tabs-opt a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href"); // activated tab
        // console.log(target);
        calendars['calendar'].refetchEvents();
        calendars['calendar'].refetchResources();
    });

    $('select.viewShift').on('change', function () {
        calendars['calendar'].refetchEvents();
    });

    //unscheduled employee
    $('#unschemp').change(function () {
        if (this.checked && selectedSchedule) {
            let dataUnscheduled = {
                start_date: defaultDateStart,
                end_date: defaultDateEnd,
                schedule: '/api/schedules/' + selectedSchedule
            };

            console.log('unscheduled employee',dataUnscheduled)
            console.log('unscheduled',selectedSchedule)
            $.ajax({
                method: 'POST',
                url: base_url + '/api/users/unscheduled',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataUnscheduled),
                success: (res) => {
                    console.log('unscheduled employee', res);
                    let userss = res['hydra:member'];
                    userss.map((us) => {
                        let resourceItem = calendars['calendar'].getResourceById(us['@id']);
                        console.log(resourceItem)
                        resourceItem.remove();
                    });


                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                }
            });
        } else {
            //get resource(users)
            $.ajax({
                url:  base_url+'/api/users?userHasSchedule='+selectedSchedule,
                method: 'GET',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,

                },
                success: function (r) {

                    console.log(r['hydra:member']);
                    users = r['hydra:member'];
                    let resources = {
                        id: "/api/users/0",
                        title: 'OpenShifts',
                        eventColor: 'red'

                    };
                    calendars['calendar'].addResource(resources);

                    users.map(user => {
                        console.log('user', user);
                        let finalpos = [];
                        let poses = user['positions'];
                        poses.map(pos => {
                            finalpos.push(pos['id']);
                        });
                        resources = {
                            id: user['@id'],
                            title: user['firstName'] + ' ' + user['lastName'],
                            rate: user['baseHourlyRate'],
                            positionID: finalpos

                        };
                        calendars['calendar'].addResource(resources);

                    });
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
        }

    });


});

