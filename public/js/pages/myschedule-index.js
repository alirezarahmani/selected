var calendars={};


document.addEventListener('DOMContentLoaded', function() {

    //check token if not set redirect to login page
    let tok=localStorage.getItem('token');
    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    function create_calendar(id) {
        var calendarEl = document.getElementById(id);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: '0231832627-fcs-1568628067',
            plugins: [ 'dayGrid', 'timeGrid', 'interaction', 'resourceTimeline'],
            resourceAreaWidth: 230,
            firstDay: 1,
            fixedWeekCount: false,
            selectable: true,
            displayEventTime: true,
            displayEventEnd: true,
            //eventBorderColor: '#e63d90',
            eventTextColor: 'black',
            timeZone: 'UTC',
            defaultView: 'dayGridMonth',
            aspectRatio: 1.5,
            header: {
                left: '',
                center: 'title',
                right: 'today prev,next'
            },
            editable: true,
            eventColor:'#f0f0f0',
            eventSources:[{events:async (fetchInfo,success,fail)=> {
                    let events=[];

                    let res=await new Promise((resolve,fail)=> {
                        $.ajax({
                            url:  base_url+'/api/shifts/self' ,
                            method: 'GET',
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,

                            },
                            success: function(r){

                                console.log(r['hydra:member']);
                                let shifts=r['hydra:member'];
                                shifts.map(shift=>{
                                    console.log('shift1',shift);
                                    let event;
                                    //title:shift['startTime'].substring(11,16)+'-'+shift['endTime'].substring(11,16) +' '+ shift['scheduleId']['name'],
                                    if(shift['publish'] === true){

                                        if(shift['ownerId'] == null){
                                            event={
                                                id:shift['id'],
                                                title:' OPEN SHIFT',
                                                start:(new Date(shift['startTime']+" UTC")).toISOString(),
                                                end:(new Date(shift['endTime']+" UTC")).toISOString(),
                                                ownerId:'/api/users/0'
                                            };
                                        }else{
                                            event={
                                                id:shift['id'],
                                                title:' '+ shift['scheduleId']['name'],
                                                start:(new Date(shift['startTime']+" UTC")).toISOString(),
                                                end:(new Date(shift['endTime']+" UTC")).toISOString(),
                                                ownerId:shift['ownerId']['@id'],
                                                confirm:shift['confirm'],
                                                publish:shift['publish'],
                                                shiftColor:shift['color']
                                            };
                                        }
                                        events.push(event);

                                    }



                                });

                                resolve(events);

                            },
                            error:function(e) {

                                // console.log(e)
                                //expire jwt token
                                if(e.status == 401){
                                    window.location.href = base_url2+"/login";
                                }
                                toastr.error('Error occurred get schedules employee');
                                resolve([])
                            }
                        })
                    }).then(value=>success(value));
                }}]
        });
        calendars[id] = calendar;
        calendar.render();

    }
    create_calendar('calendar');

    calendars['calendar'].on('eventClick', function(info) {
        console.log('event Click ' + info.event.extendedProps.shiftColor);

        $('.shiftColor').css('background-color','gray');
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
                        //show swap button
                        $('#swap-shift').show();

                        let st= res['startTime'];
                        let et= res['endTime'];

                        //fill modal swap
                        let swArray = [];
                        let swaps=res['hydra:member'];
                        swaps.map((sw) => {
                           // console.log('eligswaps',sw)

                            let stt=new Date(sw['startTime']).toString();
                            let ett=new Date(sw['endTime']).toString();

                            swArray.push('<div class="col-5 border rounded ml-2 p-2" style="background-color: white;">\n' +
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
                        $('.eligSwap').html(swArray);
                    }else{
                        $('#swap-shift').hide();
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
                     }

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
                            toastr.error(e['responseJSON']['hydra:description']);
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


            $('#modal-eventClick').modal('show');


        }
    });

});

