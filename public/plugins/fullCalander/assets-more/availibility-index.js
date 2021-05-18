document.addEventListener('DOMContentLoaded', function() {


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
            $('#submitPrefer').show();
            $('#deleteBtn').hide();
            $('#updatePrefer').hide();
            let employeeID= $('#employee_sch').val();
            $('#selected_employee').val(employeeID);
            $('#modalPrefer').show();
            $('.page-head').hide();
            $('.nav-link').hide();
            let startDate=info.dateStr;
            $('#start_dateP').val(startDate);

            $('#submitPrefer').on('click', function (e) {
                e.preventDefault();
                // console.log(this);

                let url=$(this).data('url2');

                $.ajax({
                    type: "POST",
                    url: url,
                    data :$('#frmsubmitPrefer').serialize(),
                    cache:false,

                    success: function (response){
                       // console.log(response);
                        let res=JSON.parse(response);
                        if(res.status ===1 && res.statusClock ===1){
                            alert(res['id_prefer']);
                            location.reload();


                        }
                        else if(res.status === 0 && res.statusClock ===1)  {
                            alert('not insert');
                        }else if(res.statusClock ===0){
                            alert('time conflict');
                        }

                    }

                });
            });

        },
        eventMouseEnter:function(info){
            console.log('eventMouseEnter=');
            console.log( info);




        },
        eventMouseLeave:function(info){
            $('.custom').css('display','block');
            $('div.fc-content').removeClass('custom');
            //remove tooltip
           // $('.tooltip').hide();

        },
        eventClick: function(info){
            console.log(info);
            alert('hi click');
            let id =info.event.id;
            let url = baseUri+'modules/workplan/classes/action/frm.php';

            $.ajax({
                type: "GET",
                url: url + "?" + $.param({Id_edit_pref: id}),
                cache: false,

                success: function (response) {
                    //console.log(response);
                    let res = JSON.parse(response);
                    console.log(res);
                    if (res.status === 1) {
                       
                        $('#modalPrefer').show();
                        $('.page-head').hide();
                         $('#deleteBtn').show();
                        $('#updatePrefer').show();
                        $('#submitPrefer').hide();


                        var idd=(res.rowPrefer[0])["id"];
                        var emplo=(res.rowPrefer[0])["employee_id"];
                        var sd=(res.rowPrefer[0])["start_date"];
                        var ed=(res.rowPrefer[0])["end_date"];
                        var allDay=(res.rowPrefer[0])["all_day"];
                        var avail=(res.rowPrefer[0])["available"];
                        var daysString=(res.rowPrefer[0])["days"];
                        var et=(res.rowPrefer[0])["end_time"];
                        var every=(res.rowPrefer[0])["every"];
                        var not=(res.rowPrefer[0])["note"];
                        var repeat=(res.rowPrefer[0])["repeats_on"];
                        var st=(res.rowPrefer[0])["start_time"];
                        var status=(res.rowPrefer[0])["status"];



                        console.log(idd);
                        $('#id_prefer').val(idd);
                        $('#selected_employee').val(emplo);
                        $('#start_dateP').val(sd);
                        $('.available').val(allDay);
                        $('#start_timeP').val(st);
                        $('#to_timeP').val(et);

                        $('#ended_dateP').val(ed);
                        $('#note_p').val(not);

                        if(avail == 0){
                            $('#available_n').prop('checked',true);
                            $('#available_y').prop('checked',false);

                        }else if(avail ==1){
                            $('#available_n').prop('checked',false);
                            $('#available_y').prop('checked',true);
                        }

                        if(repeat == 0){

                            $('#repeates').val("0");

                            $('#type_switch_off').prop('checked',true);
                            $('#type_switch_on').prop('checked',false);


                        }else if(repeat==1){
                            $('#repeates').val("1");

                            $('#type_switch_on').prop('checked',true);
                            $('#type_switch_off').prop('checked',false);
                            $('#type_value').val(every);
                            $('.div-repeat').show();
                            if(every == 1){
                                $('.every_days').css('display', 'none');

                            }else if(every !== 1){
                                $('.every_days').css('display', 'block');

                                let dayArray=[];
                                dayArray= daysString.split(',');


                                dayArray.forEach(function(e) {

                                    $('#type_checkbox_'+e).prop('checked',true);
                                });


                            }


                        }
                        

                    }


                }

            });

         /*   $('#submitPrefer').on('click', function (e) {
                e.preventDefault();
                // console.log(this);

                let url=$(this).data('url2');

                $.ajax({
                    type: "POST",
                    url: url,
                    data :$('#frmsubmitPrefer').serialize(),
                    cache:false,

                    success: function (response){
                        // console.log(response);
                        let res=JSON.parse(response);
                        if(res.status ===1 && res.statusClock ===1){
                            alert(res['id_prefer']);
                            location.reload();


                        }
                        else if(res.status === 0 && res.statusClock ===1)  {
                            alert('not insert');
                        }else if(res.statusClock ===0){
                            alert('time conflict');
                        }

                    }

                });
            });*/

            $('#deleteBtn').on('click', function (e) {
                e.preventDefault();
                 console.log(e);
                 let idDel=$('#id_prefer').val();
                let rep=$('#repeates').val();
                let url = $(this).data('url2');

                if(rep =="0") {

                    $.ajax({
                        type: "DELETE",
                        url: url + "?" + $.param({Id_delete_prefer: idDel}),
                        cache: false,

                        success: function (response) {
                            // console.log(response);
                            let res = JSON.parse(response);
                            if (res.status === 1) {
                                alert(res['id_deleted']);
                                //location.reload();
                                $('#modalPrefer').hide();
                                info.el.remove();
                            }

                        }

                    });

                }else if(rep=="1"){

                    $('#modalDelete').show();

                    $('#deleteAll').on('click', function (e) {
                        e.preventDefault();

                            let url = $(this).data('url2');

                            $.ajax({
                                type: "DELETE",
                                url: url + "?" + $.param({Id_delete_All: idDel}),
                                cache: false,

                                success: function (response) {
                                    //console.log(response);
                                    let res = JSON.parse(response);
                                    if (res.status === 1) {
                                        alert(res["id_deleted"]);
                                        $('#modalPrefer').hide();
                                        $('#modalDelete').hide();
                                       location.reload();

                                    }


                                }

                            })

                    });

                    $('#deleteOne').on('click', function (e) {
                        e.preventDefault();

                            let url = $(this).data('url2');

                            $.ajax({
                                type: "DELETE",
                                url: url + "?" + $.param({Id_delete_One: idDel}),
                                cache: false,

                                success: function (response) {
                                    //console.log(response);
                                    let res = JSON.parse(response);
                                    if (res.status === 1) {
                                        alert(res["id_deleted"]);
                                        $('#modalPrefer').hide();
                                        $('#modalDelete').hide();
                                        info.el.remove();

                                    }


                                }

                            })

                    });


                }
            });

            $('#updatePrefer').on('click', function (e) {
                e.preventDefault();
                console.log(e);
                let idDel=$('#id_prefer').val();
                let rep=$('#repeates').val();
                let url = $(this).data('url2');


                if(rep =="0") {

                    $.ajax({
                        type: "POST",
                        url: url,
                        data :$('#frmsubmitPrefer').serialize(),
                        cache:false,

                        success: function (response){
                            // console.log(response);
                            let res=JSON.parse(response);
                            if(res.status ===1 && res.statusClock ===1){
                                alert(res['id_prefer']);
                                location.reload();


                            }
                            else if(res.status === 0 && res.statusClock ===1)  {
                                alert('not update');
                            }else if(res.statusClock ===0){
                                alert('time conflict');
                            }

                        }

                    });

                }else if(rep=="1"){

                    $('#modalUpdate').show();

                    $('#updateOne').on('click', function (e) {
                        //console.log('helooo here')
                        e.preventDefault();
                        $('#updateOneValue').val(1);
                        let url = $(this).data('url2');

                        $.ajax({
                            type: "POST",
                            url: url,
                            data :$('#frmsubmitPrefer').serialize(),
                            cache:false,

                            success: function (response){
                                 console.log(response);
                                let res=JSON.parse(response);
                                if(res.status ===1 && res.statusClock ===1){
                                    alert(res['id_prefer']);
                                   location.reload();


                                }
                                else if(res.status === 0 && res.statusClock ===1)  {
                                    alert('not update');
                                }else if(res.statusClock ===0){
                                    alert('time conflict');
                                }

                            }

                        });


                    });

                    $('#updateAll').on('click', function (e) {
                        e.preventDefault();
                        $('#updateAllValue').val(1);
                        let url = $(this).data('url2');

                        $.ajax({
                            type: "POST",
                            url: url,
                            data :$('#frmsubmitPrefer').serialize(),
                            cache:false,

                            success: function (response){
                                // console.log(response);
                                let res=JSON.parse(response);
                                if(res.status ===1 && res.statusClock ===1){
                                    alert(res['id_prefer']);
                                    location.reload();


                                }
                                else if(res.status === 0 && res.statusClock ===1)  {
                                    alert('not update');
                                }else if(res.statusClock ===0){
                                    alert('time conflict');
                                }

                            }

                        });

                    });




                }
            });



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
            console.dir(info);

            $('#employee_sch').on('change', function () {

                $('#employee_sch').each(function (index) {

                    console.log("hi each:" + index)
                    let id = $('#employee_sch').val();
                    console.log(id)

                     $.ajax({
                         type: "GET",
                         url: baseUri + 'modules/workplan/classes/action/frm.php',
                         data: {
                             selectedEmployeeID: id

                         },
                         cache: false,
                         success: function (response) {
                             // console.log(response);
                             let res = JSON.parse(response);
                             if (res.status === 1) {
                                 alert('hi schedule');
                                 console.log(res['prefers']);

                                 let prefer=[];
                                 for(let i=0;i<res.prefers.length;i++) {
                                     let texttitle;
                                     let reptitle;
                                     var desc=[];
                                     if((res.prefers[i])['all_day'] == 0){

                                         texttitle=(res.prefers[i])['start_time']+'-'+(res.prefers[i])['end_time'];

                                     }else  if((res.prefers[i])['all_day'] == 1){
                                         texttitle="All Day";


                                     }

                                     if((res.prefers[i])['repeats_on'] == 0){

                                         reptitle="";
                                         desc="";

                                     }else  if((res.prefers[i])['repeats_on'] === 1){
                                         reptitle="-"+"(Repeated)";
                                         if((res.prefers[i])['days'] !== null) {
                                             var stringDays = (res.prefers[i])['days'];
                                             console.log(stringDays)
                                             var arrayDays = stringDays.split(",");
                                             var desct;
                                             arrayDays.forEach(function (e) {
                                                 switch (e) {
                                                     case "1":
                                                         desct = "Sunday";
                                                         break;


                                                     case "2":
                                                         desct = "Monday";
                                                         break;


                                                     case "3":
                                                         desct = "Tuesday";
                                                         break;


                                                     case "4":
                                                         desct = "Wednesday";
                                                         break;


                                                     case "5":
                                                         desct = "Thursday";
                                                         break;


                                                     case "6":
                                                         desct = "Friday";
                                                         break;


                                                     case "7":
                                                         desct = "Saturday";
                                                         break;

                                                 }
                                                 desc.push(desct);
                                                 console.log(desc)
                                             });
                                         }
                                     }



                                     if((res.prefers[i])['available'] === 1) {
                                         let valuee = {
                                             id: (res.prefers[i])['id'],
                                             title: texttitle.concat(reptitle) ,
                                             start: (res.prefers[i])['start_date'],
                                             color: 'green',
                                             Description: 'Preferred'+'\n'+reptitle+'\n'+texttitle+'\n'+desc


                                         };
                                         prefer.push(valuee);
                                     }else  if((res.prefers[i])['available'] === 0) {
                                         let valuee = {
                                             id: (res.prefers[i])['id'],
                                             title: texttitle.concat(reptitle),
                                             start: (res.prefers[i])['start_date'],
                                             color: 'red',
                                             Description: 'Unavailable'+'\n'+reptitle+'\n'+texttitle+'\n'+desc



                                         };
                                         prefer.push(valuee);
                                     }


                                 }
                                 console.log("this prefer",prefer)
                                successCallback(prefer);


                             }
                             else
                                 alert('an error occured when get availabilities!');


                         }

                     });
                });



            });

        }

    });



    calendar.render();

    $('#employee_sch').on('change',()=> {


        calendar.destroy();
        calendar = new FullCalendar.Calendar(calendarEl, {
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
                $('#submitPrefer').show();
                $('#deleteBtn').hide();
                $('#updatePrefer').hide();
                let employeeID= $('#employee_sch').val();
                $('#selected_employee').val(employeeID);
                $('#modalPrefer').show();
                $('.page-head').hide();
                $('.nav-link').hide();
                let startDate=info.dateStr;
                $('#start_dateP').val(startDate);

                $('#submitPrefer').on('click', function (e) {
                    e.preventDefault();
                    // console.log(this);

                    let url=$(this).data('url2');

                    $.ajax({
                        type: "POST",
                        url: url,
                        data :$('#frmsubmitPrefer').serialize(),
                        cache:false,

                        success: function (response){
                            // console.log(response);
                            let res=JSON.parse(response);
                            if(res.status ===1 && res.statusClock ===1){
                                alert(res['id_prefer']);
                                location.reload();


                            }
                            else if(res.status === 0 && res.statusClock ===1)  {
                                alert('not insert');
                            }else if(res.statusClock ===0){
                                alert('time conflict');
                            }

                        }

                    });
                });

            },
            eventMouseEnter:function(info){
                console.log('eventMouseEnter=');
                console.log( info);




            },
            eventMouseLeave:function(info){
                $('.custom').css('display','block');
                $('div.fc-content').removeClass('custom');
                //remove tooltip
                // $('.tooltip').hide();

            },
            eventClick: function(info){
                console.log(info);
                alert('hi click');
                let id =info.event.id;
                let url = baseUri+'modules/workplan/classes/action/frm.php';

                $.ajax({
                    type: "GET",
                    url: url + "?" + $.param({Id_edit_pref: id}),
                    cache: false,

                    success: function (response) {
                        //console.log(response);
                        let res = JSON.parse(response);
                        console.log(res);
                        if (res.status === 1) {

                            $('#modalPrefer').show();
                            $('.page-head').hide();
                            $('#deleteBtn').show();
                            $('#updatePrefer').show();
                            $('#submitPrefer').hide();


                            var idd=(res.rowPrefer[0])["id"];
                            var emplo=(res.rowPrefer[0])["employee_id"];
                            var sd=(res.rowPrefer[0])["start_date"];
                            var ed=(res.rowPrefer[0])["end_date"];
                            var allDay=(res.rowPrefer[0])["all_day"];
                            var avail=(res.rowPrefer[0])["available"];
                            var daysString=(res.rowPrefer[0])["days"];
                            var et=(res.rowPrefer[0])["end_time"];
                            var every=(res.rowPrefer[0])["every"];
                            var not=(res.rowPrefer[0])["note"];
                            var repeat=(res.rowPrefer[0])["repeats_on"];
                            var st=(res.rowPrefer[0])["start_time"];
                            var status=(res.rowPrefer[0])["status"];



                            console.log(idd);
                            $('#id_prefer').val(idd);
                            $('#selected_employee').val(emplo);
                            $('#start_dateP').val(sd);
                            $('.available').val(allDay);
                            $('#start_timeP').val(st);
                            $('#to_timeP').val(et);

                            $('#ended_dateP').val(ed);
                            $('#note_p').val(not);

                            if(avail == 0){
                                $('#available_n').prop('checked',true);
                                $('#available_y').prop('checked',false);

                            }else if(avail ==1){
                                $('#available_n').prop('checked',false);
                                $('#available_y').prop('checked',true);
                            }

                            if(repeat == 0){

                                $('#repeates').val("0");

                                $('#type_switch_off').prop('checked',true);
                                $('#type_switch_on').prop('checked',false);


                            }else if(repeat==1){
                                $('#repeates').val("1");

                                $('#type_switch_on').prop('checked',true);
                                $('#type_switch_off').prop('checked',false);
                                $('#type_value').val(every);
                                $('.div-repeat').show();
                                if(every == 1){
                                    $('.every_days').css('display', 'none');

                                }else if(every !== 1){
                                    $('.every_days').css('display', 'block');

                                    let dayArray=[];
                                    dayArray= daysString.split(',');


                                    dayArray.forEach(function(e) {

                                        $('#type_checkbox_'+e).prop('checked',true);
                                    });


                                }


                            }


                        }


                    }

                });

                /*   $('#submitPrefer').on('click', function (e) {
                       e.preventDefault();
                       // console.log(this);

                       let url=$(this).data('url2');

                       $.ajax({
                           type: "POST",
                           url: url,
                           data :$('#frmsubmitPrefer').serialize(),
                           cache:false,

                           success: function (response){
                               // console.log(response);
                               let res=JSON.parse(response);
                               if(res.status ===1 && res.statusClock ===1){
                                   alert(res['id_prefer']);
                                   location.reload();


                               }
                               else if(res.status === 0 && res.statusClock ===1)  {
                                   alert('not insert');
                               }else if(res.statusClock ===0){
                                   alert('time conflict');
                               }

                           }

                       });
                   });*/

                $('#deleteBtn').on('click', function (e) {
                    e.preventDefault();
                    console.log(e);
                    let idDel=$('#id_prefer').val();
                    let rep=$('#repeates').val();
                    let url = $(this).data('url2');

                    if(rep =="0") {

                        $.ajax({
                            type: "DELETE",
                            url: url + "?" + $.param({Id_delete_prefer: idDel}),
                            cache: false,

                            success: function (response) {
                                // console.log(response);
                                let res = JSON.parse(response);
                                if (res.status === 1) {
                                    alert(res['id_deleted']);
                                    //location.reload();
                                    $('#modalPrefer').hide();
                                    info.el.remove();
                                }

                            }

                        });

                    }else if(rep=="1"){

                        $('#modalDelete').show();

                        $('#deleteAll').on('click', function (e) {
                            e.preventDefault();

                            let url = $(this).data('url2');

                            $.ajax({
                                type: "DELETE",
                                url: url + "?" + $.param({Id_delete_All: idDel}),
                                cache: false,

                                success: function (response) {
                                    //console.log(response);
                                    let res = JSON.parse(response);
                                    if (res.status === 1) {
                                        alert(res["id_deleted"]);
                                        $('#modalPrefer').hide();
                                        $('#modalDelete').hide();
                                        location.reload();

                                    }


                                }

                            })

                        });

                        $('#deleteOne').on('click', function (e) {
                            e.preventDefault();

                            let url = $(this).data('url2');

                            $.ajax({
                                type: "DELETE",
                                url: url + "?" + $.param({Id_delete_One: idDel}),
                                cache: false,

                                success: function (response) {
                                    //console.log(response);
                                    let res = JSON.parse(response);
                                    if (res.status === 1) {
                                        alert(res["id_deleted"]);
                                        $('#modalPrefer').hide();
                                        $('#modalDelete').hide();
                                        info.el.remove();

                                    }


                                }

                            })

                        });


                    }
                });

                $('#updatePrefer').on('click', function (e) {
                    e.preventDefault();
                    console.log(e);
                    let idDel=$('#id_prefer').val();
                    let rep=$('#repeates').val();
                    let url = $(this).data('url2');


                    if(rep =="0") {

                        $.ajax({
                            type: "POST",
                            url: url,
                            data :$('#frmsubmitPrefer').serialize(),
                            cache:false,

                            success: function (response){
                                // console.log(response);
                                let res=JSON.parse(response);
                                if(res.status ===1 && res.statusClock ===1){
                                    alert(res['id_prefer']);
                                    location.reload();


                                }
                                else if(res.status === 0 && res.statusClock ===1)  {
                                    alert('not update');
                                }else if(res.statusClock ===0){
                                    alert('time conflict');
                                }

                            }

                        });

                    }else if(rep=="1"){

                        $('#modalUpdate').show();

                        $('#updateOne').on('click', function (e) {
                            //console.log('helooo here')
                            e.preventDefault();
                            $('#updateOneValue').val(1);
                            let url = $(this).data('url2');

                            $.ajax({
                                type: "POST",
                                url: url,
                                data :$('#frmsubmitPrefer').serialize(),
                                cache:false,

                                success: function (response){
                                    console.log(response);
                                    let res=JSON.parse(response);
                                    if(res.status ===1 && res.statusClock ===1){
                                        alert(res['id_prefer']);
                                        location.reload();


                                    }
                                    else if(res.status === 0 && res.statusClock ===1)  {
                                        alert('not update');
                                    }else if(res.statusClock ===0){
                                        alert('time conflict');
                                    }

                                }

                            });


                        });

                        $('#updateAll').on('click', function (e) {
                            e.preventDefault();
                            $('#updateAllValue').val(1);
                            let url = $(this).data('url2');

                            $.ajax({
                                type: "POST",
                                url: url,
                                data :$('#frmsubmitPrefer').serialize(),
                                cache:false,

                                success: function (response){
                                    // console.log(response);
                                    let res=JSON.parse(response);
                                    if(res.status ===1 && res.statusClock ===1){
                                        alert(res['id_prefer']);
                                        location.reload();


                                    }
                                    else if(res.status === 0 && res.statusClock ===1)  {
                                        alert('not update');
                                    }else if(res.statusClock ===0){
                                        alert('time conflict');
                                    }

                                }

                            });

                        });




                    }
                });



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

                // $('#employee_sch').on('change', function () {

                // $('#employee_sch').each(function (index) {

                //  console.log("hi each:" + index)
                let id = $('#employee_sch').val();
                console.log(id)

                $.ajax({
                    type: "GET",
                    url: baseUri + 'modules/workplan/classes/action/frm.php',
                    data: {
                        selectedEmployeeID: id

                    },
                    cache: false,
                    success: function (response) {
                        // console.log(response);
                        let res = JSON.parse(response);
                        if (res.status === 1) {
                            //alert('hi schedule');
                            //console.log(res['prefers']);

                            let prefer=[];
                            for(let i=0;i<res.prefers.length;i++) {
                                let texttitle;
                                let reptitle;
                                var desc=[];
                                if((res.prefers[i])['all_day'] == 0){

                                    texttitle=(res.prefers[i])['start_time']+'-'+(res.prefers[i])['end_time'];

                                }else  if((res.prefers[i])['all_day'] == 1){
                                    texttitle="All Day";


                                }

                                if((res.prefers[i])['repeats_on'] == 0){

                                    reptitle="";
                                    desc="";

                                }else  if((res.prefers[i])['repeats_on'] == 1){
                                    reptitle="-"+"(Repeated)";
                                    if((res.prefers[i])['days'] !== null) {
                                        var stringDays = (res.prefers[i])['days'];
                                        console.log(stringDays)
                                        var arrayDays = stringDays.split(",");
                                        var desct;
                                        arrayDays.forEach(function (e) {
                                            switch (e) {
                                                case "1":
                                                    desct = "Sunday";
                                                    break;


                                                case "2":
                                                    desct = "Monday";
                                                    break;


                                                case "3":
                                                    desct = "Tuesday";
                                                    break;


                                                case "4":
                                                    desct = "Wednesday";
                                                    break;


                                                case "5":
                                                    desct = "Thursday";
                                                    break;


                                                case "6":
                                                    desct = "Friday";
                                                    break;


                                                case "7":
                                                    desct = "Saturday";
                                                    break;

                                            }
                                            desc.push(desct);
                                            console.log(desc)
                                        });
                                    }
                                }



                                if((res.prefers[i])['available'] == 1) {
                                    let valuee = {
                                        id: (res.prefers[i])['id'],
                                        title: texttitle.concat(reptitle) ,
                                        start: (res.prefers[i])['start_date'],
                                        color: 'green',
                                        Description: 'Preferred'+'\n'+reptitle+'\n'+texttitle+'\n'+desc


                                    };
                                    prefer.push(valuee);
                                }else  if((res.prefers[i])['available'] == 0) {
                                    let valuee = {
                                        id: (res.prefers[i])['id'],
                                        title: texttitle.concat(reptitle),
                                        start: (res.prefers[i])['start_date'],
                                        color: 'red',
                                        Description: 'Unavailable'+'\n'+reptitle+'\n'+texttitle+'\n'+desc



                                    };
                                    prefer.push(valuee);
                                }


                            }
                            console.log("this prefer",prefer)
                            successCallback(prefer);


                        }
                        else
                            alert('an error occured when get availabilities!');


                    }

                });
                //  });



                //  });

            }

        });

        calendar.render();

    });

    // readjust sizing after font load
    window.addEventListener('load', function() {
        calendar.updateSize();

    });


});
