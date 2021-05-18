var calendars = {};
//check token if not set redirect to login page
let tok = localStorage.getItem('token');
let personRole = localStorage.getItem('role');
let roleEmail = localStorage.getItem('email');

if (tok == null) {
    window.location.href = base_url2 + "/login";
}
let scheduleArray;

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

            if (jobsitesOpt.length != 0){

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
                jArray.push('<option value="" disabled selected>Select jobsite</option>');
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

            }else {
                document.getElementById('_btn_addJobSite').style.display = 'block';
                document.getElementById('_btn_addJobSite').href = `${base_url}/jobsite`;
                document.getElementById('_jobSpinner').style.display = 'none';
            }


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
            pArray.push('<option value="" disabled selected>Select position</option>');
            // pArray.push('<option value="">No Position</option>')
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
    let annotationObj={};

    //budget tools entry~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    let table_budget = $('#budget-table')[0];

    $('.total .c-content').on('mouseover', function () {
        $(this).find('.budget-cell').prop({'disabled': false, placeholder: "click and enter total"});
    });

    $('.budget-cell').on('change', (e) => {
        let cell_number = $(e.target).closest('td').index();
        console.log(cell_number);
        let bdg_date=moment().isoWeekday(cell_number+1).format('YYYY-MM-DD');

        let total_cell = table_budget.rows[0].cells[cell_number];
        let labor_cell = table_budget.rows[1].cells[cell_number];
        let labor = $(labor_cell).find('.budget-cell').val();
        let total = $(total_cell).find('.budget-cell').val();
        console.log('labor total fucking',labor,total);
        $.ajax({
            method:'post',
            url: encodeURI(base_url + '/api/budget_tools'),
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data: JSON.stringify({
                date:bdg_date,
                total,
                labor,
                scheduleId:"/api/schedules/"+selectedSchedule
            }),
            success(e){
                console.log(e)
            },
            error:(e)=>{
                console.error(e)
            }

        });
        calculate_percent(cell_number)
    });

    $('.labor .c-content').on('mouseover', function () {
        $(this).find('.budget-cell').prop({'disabled': false, placeholder: "click and enter labor"})
    });


    $('.c-content').on('mouseleave', function () {
        $(this).find('.budget-cell').prop({'disabled': true, placeholder: " "});
    });

    //budge_tool end~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    let calculate_percent=()=>{
        $('.c-content input').val(' ');
        console.log('calculate percent');
        let view=calendars['calendar'].view;
        let date_start=moment(view.currentStart).format('YYYY-MM-DD 00:00');
        let date_end=moment(view.currentEnd).format('YYYY-MM-DD 00:00');

        if (calendars['calendar'].view.type ==='resourceTimelineWeek'){
            $.ajax({
                method:'get',
                url: encodeURI(base_url + '/api/budget_tools?date='+date_start+','+date_end),
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success:(e)=>{
                    let budget=e['hydra:member'];
                    console.log(budget);
                    setTimeout(() => {
                        document.getElementById('tableLoading').style.display = 'none';
                    }, 2000);
                    let labor,total;
                    if (Array.isArray(budget)){
                        budget.map(bdg=>{
                            let cell=moment(bdg['date']).isoWeekday()-1;
                            let shift_start_bg=moment(bdg['date']).format('YYYY-MM-DD 00:00');
                            let shift_end_bg=moment(bdg['date']).format('YYYY-MM-DD 23:59');
                            console.log(bdg,cell,date_start,date_end,'~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
                            labor=bdg['labor'];
                            total=bdg['total'];
                            let percentage = (parseFloat(labor) * parseFloat(total)) / 100;
                            $(table_budget.rows[0].cells[cell]).find(".budget-cell").val(total);
                            $(table_budget.rows[1].cells[cell]).find(".budget-cell").val(labor);
                            console.log(cell);
                            $.ajax({
                                url: encodeURI(base_url + '/api/shifts?scheduleId=' + selectedSchedule + '&startTime=' + shift_start_bg + '&endTime=' + shift_end_bg),
                                method: 'GET',
                                contentType: "application/json",
                                headers: {
                                    'Authorization': `Bearer ${tok}`,
                                },
                                success: function (r) {
                                    let day_shifts=r['hydra:member'];
                                    console.log(day_shifts,'-------------------------------->265');
                                    let $labors=0;
                                    let $total_hour=0;
                                    let $percent_exceed=0;
                                    let $percent_diff=0;
                                    day_shifts.map(shift_obj=>{
                                        console.log(shift_obj);
                                        let $_rate=shift_obj['ownerId']!==null
                                        && shift_obj['ownerId']['userBusinessRoles'][0]['baseHourlyRate']!==null?
                                            shift_obj['ownerId']['userBusinessRoles'][0]['baseHourlyRate']: 0 ;
                                        let $rate=parseFloat($_rate).toFixed(2);
                                        let start_shft=moment(shift_obj['startTime']);
                                        let end_shft=moment(shift_obj['endTime']);
                                        let unpaid_brak=shift_obj['unpaidBreak'];
                                        let diff=parseFloat((end_shft.diff(start_shft,'m')-unpaid_brak)/60);
                                        console.log(diff,'diff');
                                        $labors=parseFloat($labors+(diff*$rate)).toFixed(1);
                                        $total_hour=parseFloat($total_hour+diff);
                                        console.log(diff,$labors)
                                    });

                                    if (total!==0){
                                        console.log(total,$labors,$percent_exceed,percentage);
                                        $percent_exceed=(($labors*100)/total);
                                        console.log($percent_exceed,total,percentage);
                                        $percent_diff=labor-$percent_exceed;
                                    }
                                    console.log('majid nodehi',cell,bdg,$percent_exceed,$percent_diff,$total_hour,$labors)

                                    $(table_budget.rows[2].cells[cell]).find(".budget-cell").val(parseFloat($percent_exceed).toFixed(2));
                                    $(table_budget.rows[3].cells[cell]).find(".budget-cell").val(parseFloat($percent_diff).toFixed(2));
                                    $(table_budget.rows[4].cells[cell]).find(".budget-cell").val($total_hour.toFixed(2));
                                    $(table_budget.rows[5].cells[cell]).find(".budget-cell").val($labors);

                                },
                                error: function (e) {

                                    // console.log(e)
                                    //expire jwt token
                                    if (e.status == 401) {
                                        window.location.href = base_url2 + "/login";
                                    }if(e['responseJSON'] !=='undefined'){
                                        toastr.error('some thing wrong happen');
                                    }

                                }
                            });


                        })
                    }
                }

            });
        }
    };

    //Annotation~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    let get_annotations=(selectedSchedule)=>{
        let infoEl;

        let view=calendars['calendar'].view;
        let date_start=moment(view.currentStart).format('YYYY-MM-DD 00:00');
        let date_end=moment(view.currentEnd).format('YYYY-MM-DD 00:00');

        if (calendars['calendar'].view.type ==='resourceTimelineWeek'){

            infoEl=$('#calendar').find('.fc-view.fc-resourceTimelineWeek-view');

            //get Annotations
            $.ajax({
                method: 'get',
                url: base_url + '/api/annotations?scheduleId=' + selectedSchedule,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success: (_annot) => {
                    console.log('new list',_annot['hydra:member']);
                    let listAnnot=_annot['hydra:member'];

                    selectedSchedule = $('#schedule-ls').children("option:selected").val();
                    // console.log('there',listAnnot);
                    if(listAnnot.length !== 0){
                        // alert('we have annotation')
                        let annotArray=[];
                        let annotDurationArray=[];

                        annotationObj={};

                        listAnnot.map((annot)=>{

                            if(annot['startDate'].substring(0,10) === annot['endDate'].substring(0,10)){
                                let st=annot['startDate'].substring(0,10);
                                annotationObj[st]=[];
                                annotationObj[st]['id']=annot['id'];
                                annotationObj[st]['type']=annot['type'];
                                annotationObj[st]['title']=annot['title'];
                                annotationObj[st]['color']=annot['color'];
                                annotationObj[st]['st']=annot['startDate'].substring(0,10);
                            }else{

                                console.log(annotationObj);

                                let annStart=moment(annot['startDate'].substring(0,10)).format('YYYY-MM-DD');
                                let currrDate=moment(annot['startDate'].substring(0,10)).startOf('day');
                                let endddDate=moment(annot['endDate'].substring(0,10)).startOf('day');

                                annotDurationArray.push(annStart);
                                while (currrDate.add(1,'days').diff(endddDate)<= 0) {
                                    //console.log('currrDate',currrDate)
                                    annotDurationArray.push(currrDate.format('YYYY-MM-DD'));
                                }
                                // console.log('annotDurationArray',annotDurationArray)

                                annotDurationArray.forEach(function(annDur) {
                                    // do something with `item`
                                    annotationObj[annDur]=[];
                                    annotationObj[annDur]['id']=annot['id'];
                                    annotationObj[annDur]['type']=annot['type'];
                                    annotationObj[annDur]['title']=annot['title'];
                                    annotationObj[annDur]['color']=annot['color'];
                                    annotationObj[annDur]['st']=annDur;
                                });

                            }

                        });
                        console.log('annotationObj',annotationObj);

                        $(infoEl).find('table thead.fc-head .fc-time-area.fc-widget-header .fc-content table tbody tr th.fc-widget-header').each(function (i, el) {
                            console.log('element header',el)
                            $(el).find('i').remove();
                            $(el).css('background-color','#fafafa');
                            $(el).css('color','black');
                            let date_old= $(el).attr('data-date');
                            //console.log( 'check existence', $(el).find('span.fc-cell-text'));
                            $(el).find('span.fc-cell-text').text(moment(date_old).format('DD ddd'));


                            // $(el).find(`.fc-widget-header[data-date='${date_old}']`).find('span.fc-cell-text').text(moment(date_old).format('DD ddd'));
                            // console.log('test',date_old,moment(date_old).format('DD ddd'),$(el).find(`.fc-widget-header[data-date='${date_old}']`).find('span.fc-cell-text'))

                            // $(el).find('th.fc-widget-header.headAnnot').removeClass('headAnnot');
                            // $('th.fc-widget-header.headAnnot').removeClass('headAnnot');

                            if($(el).data('date') in annotationObj){
                                //add new style to header of column calendar
                                let annotId= annotationObj[$(el).data('date')]['id'];
                                console.log('come here');
                                $(el).find('.fc-cell-text').append('<i class="ml-3 far fa-comment-dots fa-lg pointer text-secondary add-annotations editAnnot" \n' +
                                    '                                                 data-toggle="modal"\n' +
                                    '                                                data-target="#modal-addAnnot" data-id="'+annotId+'"></i>');
                                let strType=annotationObj[$(el).data('date')]['type'];

                                if(strType.includes('closed') === true){
                                    $(el).find('.fc-cell-text')[0].childNodes[0].nodeValue = 'Closed !';
                                }

                                let colorAnnot = annotationObj[$(el).data('date')]['color'];
                                $(el).find('.fc-cell-content').mouseenter(function(){
                                    new Tooltip(this, {
                                        title: annotationObj[$(el).data('date')]['title'],
                                        placement: 'right',
                                        trigger: 'hover',
                                        container: 'body'
                                    });
                                    console.log('item', annotationObj[$(el).data('date')]['title'])
                                });

                                //$('el.fc-widget-header').addClass('headAnnot');
                                $(el).css('background-color', colorAnnot);
                                $(el).css('color','white');
                                $(el).find('i').removeClass('text-secondary');

                            }else{
                                $(el).find('i').remove();
                                $(el).find('.fc-cell-text').append('<i class="ml-3 fas fa-comment-medical fa-lg text-info pointer add-annotations" \n' +
                                    '                                                title="Add Annotation" data-toggle="modal"\n' +
                                    '                                                data-target="#modal-addAnnot"></i>');
                            }
                        });

                    }else{
                        //this duration doesnt have annotation
                        $(infoEl).find('table thead.fc-head .fc-time-area.fc-widget-header .fc-content table tbody tr th.fc-widget-header').each(function (i, el) {

                            $(el).find('i').remove();
                            $(el).css('background-color','#fafafa');
                            $(el).css('color','black');
                            let date_old= $(el).attr('data-date');
                            //  console.log( 'check existence', $(el).find('span.fc-cell-text'));
                            $(el).find('span.fc-cell-text').text(moment(date_old).format('DD ddd'));

                        });
                        $(infoEl).find('.fc-time-area.fc-widget-header .fc-widget-header .fc-cell-text').append('<i class="ml-3 fas fa-comment-medical fa-lg text-info add-annotations" \n' +
                            '                                                title="Add Annotation" data-toggle="modal"\n' +
                            '                                                data-target="#modal-addAnnot"></i>');
                    }

                    console.log('infoEl--->',infoEl);

                    //show modal annotation
                    $('.add-annotations').on('click',function () {
                        if($(this).hasClass('editAnnot')){
                            let editIdAnnot=$(this).data('id');
                            $('.changeStat').text('Edit');
                            $('#annotEditId').val(editIdAnnot);
                            //get specific Annotation
                            $.ajax({
                                method: 'GET',
                                url: base_url + '/api/annotations/'+editIdAnnot,
                                contentType: "application/json",
                                headers: {
                                    'Authorization': `Bearer ${tok}`,
                                },
                                success: (rAnnot) => {
                                    console.log('specificAnnot',rAnnot);

                                    let startAnnot=rAnnot['startDate'].substring(0,10);
                                    let endAnnot=rAnnot['endDate'].substring(0,10);
                                    $('#end-date-annot').val(moment(endAnnot,'YYYY-MM-DD').format('DD/MM/YYYY'));
                                    $('#start-date-annot').val(moment(startAnnot,'YYYY-MM-DD').format('DD/MM/YYYY'));
                                    $('.select-schedule-annot').val((rAnnot['scheduleId']['id']).toString());
                                    document.getElementById('changeColorForBG2').style.backgroundColor = rAnnot['color'];
                                    $('#schedule-annot').trigger('change');
                                    $('#title').val(rAnnot['title']);
                                    $('#message_annot').val(rAnnot['message']);
                                    $('.deleteAnnot').show();

                                    let typeArray= rAnnot['type'].split(',');
                                    $.each(typeArray, function(index,value) {
                                        $("input[name='annotType']").each(function () {
                                            if($(this).val() ===value ){
                                                $(this).prop("checked",true);
                                            }
                                        });
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
                        }else{
                            $('#schedule-annot').val(selectedSchedule);
                            $('.changeStat').text('Add');
                            let dayAnnot=$(this).closest('th').data('date');
                            $('#start-date-annot').val(moment(dayAnnot,'YYYY-MM-DD').format('DD/MM/YYYY'));
                            $('#end-date-annot').val(moment(dayAnnot,'YYYY-MM-DD').format('DD/MM/YYYY'));
                        }
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

    };

    let create_calendar = (id) => {
        var calendarEl = document.getElementById(id);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: '0231832627-fcs-1568628067',
            plugins: ['dayGrid', 'timeGrid', 'interaction', 'resourceTimeline'],
            timeZone: 'UTC',
            locale: 'en',
            firstDay: 1,
            customButtons: {
                printButton: {
                    text: 'custom',
                    click: function () {
                        // alert('clicked the custom button!');
                        var originalContents = document.body.innerHTML;
                        var printReport = document.getElementById('calendar').innerHTML;
                        document.body.innerHTML = printReport;
                        window.print();
                        document.body.innerHTML = originalContents;

                    }
                }
            },
            viewSkeletonRender: function(info) {
                console.log('viewSkeletonRender',info.view)

                calendarEl.querySelectorAll('.fc-button').forEach((button) => {
                    if (button.innerText === 'custom') {
                        button.classList.add('print-button');
                        $('.print-button').html('<i class="fas fa-print"></i>');
                        $('.print-button').hide();
                    }
                });

            },
            datesRender: (info) => {
                console.log('datesRender---------->',info)

                scheduledObj={};
                //$('.annot-content').empty();
                //$('.annotation-wrapper ul.annotation li.c-nav-item .c-nav-link').css('color','white');
                let start = moment(info.view.currentStart).format('YYYY-MM-DD');
                let dayStart = moment(info.view.currentStart).format('dddd DD');
                let end = moment(info.view.currentEnd).format('YYYY-MM-DD');

                // let endActive=((moment(info.view.currentEnd)).subtract(1, "days")).format('DD/MM/YYYY');
                // $('#end-date-notify').val(endActive);

                let startCurrent=(moment(info.view.currentStart)).format('YYYY-MM-DD');
                let endCurrent=((moment(info.view.currentEnd)).subtract(1, "days")).format('YYYY-MM-DD');
                let defaultDateStart = startCurrent + ' 00:00';
                let defaultDateEnd = endCurrent + ' 23:59';

                calendars['calendar'].refetchResources();

                if ($('#unschemp').is(":checked")) {
                    let dataUnscheduled = {
                        start_date: defaultDateStart,
                        end_date: defaultDateEnd,
                        schedule: '/api/schedules/' + selectedSchedule
                    };
                    // console.log('unscheduled employee',dataUnscheduled)
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
                }
                if (info.view.type === 'resourceTimelineWeek') {
                    console.log('in the if week view');
                    console.log('info.el',info.el)
                    $('.print-button').show();
                    selectedSchedule = $('#schedule-ls').children("option:selected").val();
                    get_annotations(selectedSchedule);
                    let datesArray = ['<th class="invisible"></th>'];
                    let currDate = moment(info.view.currentStart).startOf('day');
                    let lastDate = moment(info.view.currentEnd).startOf('day');
                    datesArray.push('<th class="'+moment(info.view.currentStart).format('YYYY-MM-DD')+'">' + dayStart + '</th>');
                    while (currDate.add(1, 'days').diff(lastDate) < 0) {
                        datesArray.push('<th class="'+currDate.format('YYYY-MM-DD')+'" >' + currDate.format('DD dddd') + '</th>');
                    }


                    // $('#budget-table thead tr').html(datesArray.join(' '));

                    //show tables budget
                    $('#showbudgettable').on('change', (e) => {
                        let checked = $(e.target).is(':checked');
                        if (checked) {

                            if(personRole === 'supervisor'){
                                $('#budget').show();
                                $("#budget-table tr:not(:first-child)").hide();
                            }else{
                                $('#budget').show();
                            }
                            $('div.rem').hide();
                            $('div.rem2').show();
                        } else {
                            $('#budget').hide();
                            $('div.rem').show();
                            $('div.rem2').hide();
                        }

                    });

                    if ($('#showbudgettable').is(':checked')) {
                        $('#budget').show();


                    } else {
                        $('div#budget').hide();
                    }

                } else if (info.view.type === 'resourceTimelineDay') {
                    console.log('this is day view')

                    $('.annotation-wrapper').hide();
                    $('div#budget').hide();
                    $('#showbudgettable').on('change', (e) => {
                        let checked = $(e.target).is(':checked');
                        if (checked) {

                            $('div.rem').hide();
                            $('div.rem2').show();
                            $('div#budget').hide();
                        } else {

                            $('div.rem').show();
                            $('div.rem2').hide();
                        }

                    });

                    if ($('#showbudgettable').is(':checked')) {
                        $('div.rem').hide();
                        $('div.rem2').show();


                    } else {
                        $('div.rem').show();
                        $('div.rem2').hide();
                    }

                } else if (info.view.type === 'dayGridMonth') {

                    console.log('this is month view')
                    $('.annotation-wrapper').hide();
                    $('div#budget').hide();
                    $('#showbudgettable').on('change', (e) => {
                        let checked = $(e.target).is(':checked');
                        if (checked) {

                            $('div.rem').hide();
                            $('div.rem2').show();
                            $('div#budget').hide();
                        } else {

                            $('div.rem').show();
                            $('div.rem2').hide();
                        }

                    });
                }
            },
            allDay: false,
            aspectRatio: 1.5,
            height: 500,
            header: {
                left: 'prev,next today printButton',
                center: 'title',
                right: 'resourceTimelineDay,resourceTimelineWeek,dayGridMonth',
                printButton: "fas fa-print"
            },
            defaultView: 'resourceTimelineWeek',
            views: {
                resourceTimelineWeek: {
                    type: 'resourceTimelineWeek',
                    buttonText: 'week',
                    columnHeader: true,
                    slotDuration: {days: 1},
                    slotLabelInterval: {days: 1},
                    resourceAreaWidth: 175,
                    slotWidth: 60,
                    slotLabelFormat: [
                        {weekday: 'short', day: 'numeric'}, // top level of text
                    ]
                }
            },
            displayEventTime:false,
            editable: true,
            eventOverlap: false,
            resourceLabelText: 'Employee',
            resourceAreaWidth: 175,
            eventSources: [{
                events: (fetchInfo, success, fail) => {
                    selectedSchedule = $('#schedule-ls').children("option:selected").val();
                    let events = [];
                    let rec_id_type = $('.nav.c-event-source .active ').data('resource');
                    let typeColor = $('select.viewShift').val();
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
                            shifts = r['hydra:member'];
                            console.log(shifts,'------------------------->938-->shifts')


                            const toDate = (str) => {
                                const [dateStr, t] = str.split(" ");
                                const [h, m] = t.split(":");
                                const date = new Date(dateStr);
                                date.setHours(h);
                                date.setMinutes(m);
                                return date.getTime();
                            };

                            let overlapIdsShift;
                            function loadShits(allShifts) {
                                let shiftMap = {};
                                let overlapIds = new Set();
                                allShifts.forEach((shift) => {
                                    if (shift.ownerId !== null) {
                                        let dateDay = shift.startTime.split(" ");
                                        let ownId = shift.ownerId['id'];

                                        if (!shiftMap[dateDay[0] && ownId]) {
                                            shiftMap[dateDay[0] && ownId] = [
                                                [toDate(shift.startTime), toDate(shift.endTime)],
                                            ];
                                        }
                                        else {
                                            const sTime = toDate(shift.startTime);
                                            const eTime = toDate(shift.endTime);
                                            const overlap = shiftMap[dateDay[0] && ownId].some(([osTime, oeTime]) => {
                                                return (
                                                    (sTime > osTime && sTime < oeTime) ||
                                                    (eTime > osTime && eTime < oeTime)
                                                );
                                            });

                                            // console.log(shiftMap);
                                            shiftMap[dateDay[0] && ownId].push([sTime, eTime]);

                                            if (overlap){
                                                overlapIdsShift = overlapIds.add(`${dateDay[0]} ${shift.ownerId['id']}`);
                                            }

                                            shift.overlap = overlap
                                        }
                                    }
                                });
                                // console.log("overLapIds",overlapIds);
                                // console.log("overlapIdsShift",overlapIdsShift);
                                // console.log("shiftMap",shiftMap);
                                // asyncOverlap = overlapIds;
                            }
                            loadShits(shifts)


                            let filtered = [];
                            $.each($(".pos-check:checked"), function () {
                                filtered.push($(this).val());
                            });
                            let job_filtered = [];
                            $.each($(".job-check:checked"), function () {
                                job_filtered.push($(this).val());
                            });

                            shifts.filter(shft => {
                                let poId = shft['positionId'] !== null ? shft['positionId']['@id'] : "null";
                                return !filtered.includes("all") && shft['positionId'] !== null ? filtered.includes(poId) : true;
                            }).filter(shft => {
                                let jid = shft['jobSitesId'] !== null ? shft['jobSitesId']['@id'] : "null";
                                return !job_filtered.includes("all") && shft['jobSitesId'] !== null ? job_filtered.includes(jid) : true;
                            }).map(shift => {
                                console.log("All Shifts========>>",shift);

                                let keyrole;
                                let newTitle;
                                let checklastname;
                                let overlapShifts;
                                let dateDay = shift.startTime.split(" ");

                                let checkOwnerShifts = (overShift , overPos) => {
                                    if (shift.ownerId == null) {
                                        keyrole = null;
                                        if (overPos === true) {
                                            checklastname = 'OpenShift';
                                        }
                                    }else {
                                        keyrole = Object.keys(shift['ownerId']['userBusinessRoles'])[0];
                                        if (overShift === true) {
                                            if (overlapIdsShift.has(`${dateDay[0]} ${shift.ownerId['id']}`)) {
                                                console.log(overlapIdsShift);
                                                overlapShifts = "asyncShift"
                                            }else {
                                                overlapShifts = "";
                                            }
                                        }
                                        if (overPos === true) {
                                            checklastname = shift['ownerId']['lastName'];
                                        }
                                    }
                                }

                                if (overlapIdsShift !== undefined) {
                                    checkOwnerShifts(true ,  false);
                                }


                                if (shift['positionId'] == null) {
                                    newTitle = "No Position";
                                } else {
                                    checkOwnerShifts(false ,  true);
                                    newTitle = rec_id_type === 'position' ? checklastname : shift['positionId']['name']
                                    //newTitle = shift['positionId']['name'];
                                }


                                let markShiftUnAvail;
                                if (shift['conflictAvailability'].length > 0){
                                    markShiftUnAvail = "shiftUnAvilibility";
                                }else {
                                    markShiftUnAvail = "";
                                }

                                let resources;
                                resources = rec_id_type === 'position' ? 'positionId' : 'ownerId';
                                let res_id;
                                if (shift[resources] == null) {
                                    res_id = rec_id_type === 'position' ? '/api/positions/0' : '/api/users/0'
                                } else {
                                    res_id = shift[resources]['@id'];
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


                                let event;

                                if (shift['publish'] === false) {

                                    event = {
                                        id: shift['id'],
                                        resourceId: res_id,
                                        title: shift['startTime'].substring(11, 16) + '-' + shift['endTime'].substring(11, 16) + ' ' + newTitle,
                                        start: (new Date(shift['startTime'] + " UTC")).toISOString(),
                                        end: (new Date(shift['endTime'] + " UTC")).toISOString(),
                                        color: finalColor,
                                        publish: shift['publish'],
                                        shiftID: shift['id'],
                                        rate:keyrole==null? '':shift['ownerId']['userBusinessRoles'][keyrole]['baseHourlyRate'],
                                        className: ['unpublish','font-weight-bold','pl-4', overlapShifts, markShiftUnAvail],
                                        editable: shift['editable']
                                    };
                                } else if (shift['publish'] === true) {

                                    event = {
                                        id: shift['id'],
                                        resourceId: res_id,
                                        title: shift['startTime'].substring(11, 16) + '-' + shift['endTime'].substring(11, 16) + ' ' + newTitle,
                                        start: (new Date(shift['startTime'] + " UTC")).toISOString(),
                                        end: (new Date(shift['endTime'] + " UTC")).toISOString(),
                                        color: finalColor,
                                        publish: shift['publish'],
                                        rate:keyrole==null? '':shift['ownerId']['userBusinessRoles'][keyrole]['baseHourlyRate'],
                                        shiftID: shift['id'],
                                        className: ['font-weight-bold', 'pl-4', overlapShifts, markShiftUnAvail],
                                        editable: shift['editable']

                                    };
                                }
                                events.push(event);
                                console.log('shift Events---',events)
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
                            console.log(r['hydra:member']);
                            timeoffs=r['hydra:member'];
                            timeoffs.map(timeoff=>{
                                console.log('timeoff',timeoff);

                                let event;
                                if(timeoff['Status'] === 'accepted'){

                                    event={
                                        id:timeoff['id'],
                                        resourceId:timeoff['userID']['@id'],
                                        start:(new Date(timeoff['startTime']+" UTC")).toISOString(),
                                        end:(new Date(timeoff['endTime']+" UTC")).toISOString(),
                                        color:'#D3D3D3',
                                        rendering: 'background'
                                    };
                                }

                                events.push(event);


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
            },{
                events: (fetchInfo, success, fail) => {
                    selectedSchedule = $('#schedule-ls').children("option:selected").val();

                    let events = [];
                    let rec_id_type = $('.nav.c-event-source .active ').data('resource');
                    let typeColor = $('select.viewShift').val();
                    // console.log('color',typeColor)
                    let start = moment(fetchInfo.startStr).utc(0).format('YYYY-MM-DD HH:mm');
                    let end = moment(fetchInfo.endStr).utc(0).format('YYYY-MM-DD HH:mm');
                    console.log(fetchInfo, start, end, "~~~~~~~~~~~~~~~~~~~~~~~~~~", moment);
                    //make event base on shifts
                    $.ajax({
                        url: encodeURI(base_url + '/api/availabilities?&startTime=' + start + '&endTime=' + end),
                        method: 'GET',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,
                        },
                        success: function (availabilities) {
                            console.log('availablity-----??',availabilities['hydra:member']);
                            let avails=availabilities['hydra:member'];
                            avails.map((avail)=>{

                                let allday = false;

                                if (avail['startTime'].substring(0, 10) === avail['endTime'].substring(0, 10) &&
                                    avail['startTime'].substring(11, 16) === '00:00' &&
                                    avail['endTime'].substring(11, 16) === '23:59') {
                                    allday = true;
                                }

                                let event;

                                if (avail['available'] === false) {

                                    //start---->tamam css ha inja load misheh
                                    if (allday === true) {
                                        event = {
                                            id: avail['id'],
                                            resourceId: avail['user'],
                                            allDay: true,
                                            title: 'UnAvailable AllDay',
                                            start: (new Date(avail['startTime'] + " UTC")).toISOString(),
                                            end: (new Date(avail['endTime'] + " UTC")).toISOString(),
                                            color: '#f3adad',
                                            rendering: 'background'
                                        };
                                    } else {
                                        event = {
                                            id: avail['id'],
                                            resourceId: avail['user'],
                                            title: 'UnAvailable',
                                            start: (new Date(avail['startTime'] + " UTC")).toISOString(),
                                            end: (new Date(avail['endTime'] + " UTC")).toISOString(),
                                            color: '#f3adad',
                                            rendering: 'background'
                                        };
                                    }
                                    //end---->tamam css ha inja load misheh

                                } else if (avail['available'] === true) {

                                    if (allday === true) {

                                        event = {
                                            id: avail['id'],
                                            allDay: true,
                                            resourceId: avail['user'],
                                            title: 'Available AllDay',
                                            start: (new Date(avail['startTime'] + " UTC")).toISOString(),
                                            end: (new Date(avail['endTime'] + " UTC")).toISOString(),
                                            color: '#8fea9b',
                                            rendering: 'background'

                                        };
                                    } else {

                                        event = {
                                            id: avail['id'],
                                            title: 'Available',
                                            resourceId: avail['user'],
                                            start: (new Date(avail['startTime'] + " UTC")).toISOString(),
                                            end: (new Date(avail['endTime'] + " UTC")).toISOString(),
                                            color: '#8fea9b',
                                            rendering: 'background'

                                        };
                                    }

                                }

                                events.push(event);
                                console.log(events,'::::::::::::')

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

                let url, rec_array;
                //get selected schedule~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                let sch_selected_id = $('#schedule-ls option:selected').val();
                //check active tab position or user~~~~~~~~~~~~~~~~~~~~
                let rec_id_type = $('.nav.c-event-source .active ').data('resource');
                if (rec_id_type === 'position') {
                    url = '/api/positions';
                    rec_array = [{
                        id: "/api/positions/0",
                        title: 'No Position',
                        eventColor: 'grey',
                        color: 'grey'
                    }]

                } else {
                    url = '/api/users?userHasSchedule=' + sch_selected_id;
                    rec_array = [{
                        id: "/api/users/0",
                        title: 'OpenShifts',
                        eventColor: 'red'
                    }];

                }
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
                        if (rec_id_type === 'position') {

                            items.map(pos => {
                                // console.log('positions---->', pos);
                                let positionResources = {
                                    id: pos['@id'],
                                    title: pos['name'],
                                    color: pos['color']
                                };

                                rec_array.push(positionResources);
                            });
                        } else {
                            loadUser(items);
                            items.map(user => {
                                if (roleEmail != user.email){
                                    console.log('user', user);
                                    let finalpos = [];
                                    let poses = user['positions'];
                                    poses.map(pos => {
                                        finalpos.push(pos['id']);
                                    });
                                    let keys_businessRole = Object.keys(user['userBusinessRoles']).map((i) => i);
                                    // console.log('sss------->',user['userBusinessRoles'][[keys_businessRole[0]]]['maxHoursWeek'])
                                    let resources = {
                                        id: user['@id'],
                                        title: user['firstName'] + ' ' + user['lastName'],
                                        rate: keys_businessRole.length > 0 ? user['userBusinessRoles'][keys_businessRole[0]]['baseHourlyRate'] : 0,
                                        positionID: finalpos,
                                        prfHours: user['preferredHoursWeekly'],
                                        maxHours: keys_businessRole.length > 0 ? user['userBusinessRoles'][[keys_businessRole[0]]]['maxHoursWeek'] : 0
                                    };
                                    rec_array.push(resources);
                                }
                            });
                        }
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
                // inja css load misheh
                console.log('eventRenderInfo',eventRenderInfo)

                $(".popover").remove();//remove all last tooltip
            },

            eventPositioned:function(eventPositionInfo){
                console.log('eventPositionInfo',eventPositionInfo);
                let rate=0;
                let event = eventPositionInfo.event;
                let event_resource = event.getResources();
                console.log(event_resource, 'event_resource');

                console.log(event, event_resource, '~~~~~~~~~~~~~~~~~~~~~~~~eventResourceId');

                if(eventPositionInfo.view !== 'DayGridView'){
                    if(event_resource[0]['id'] !== '/api/users/0') {

                        let owner = event_resource[0]['id'];
                        rate = parseInt(event.extendedProps.rate);

                        var a = moment(event['start']);
                        var b = moment(event['end']);
                        let difference = parseFloat((b.diff(a, 'hours', true)));
                        let calendar_range= a.isSameOrAfter(eventPositionInfo.view.currentStart) && a.isSameOrBefore(eventPositionInfo.view.currentEnd);

                        if (calendar_range && event.rendering !== "background"){
                            if (typeof  scheduledObj[owner] === 'undefined') {
                                scheduledObj[owner] = [];
                                scheduledObj[owner]['diff'] = 0;
                                scheduledObj[owner]['diff'] =difference.toFixed(2);
                                scheduledObj[owner]['rate'] = rate ;

                            } else {
                                scheduledObj[owner]['diff'] =(parseFloat(scheduledObj[owner]['diff'])+ difference).toFixed(2);
                            }


                            console.log('in the function', scheduledObj);

                            Object.keys(scheduledObj).map(userid=>{
                                console.log(userid,parseFloat(scheduledObj[userid]['diff']), scheduledObj[userid]['rate'], 'bageRate');
                                $('.scheduledHours[data-us="' + userid + '"]').text(scheduledObj[userid]['diff']);
                                $('.budgetRate[data-bud="' + userid + '"]').text(scheduledObj[userid]['diff'] * scheduledObj[userid]['rate']);
                            });
                        }
                    }
                }
            },

            resourceRender: function (renderInfo) {
                console.log('resourceRender', renderInfo)
                let startView = moment(renderInfo.view.currentStart).format('YYYY-MM-DD');
                let endView = moment(renderInfo.view.currentEnd).format('YYYY-MM-DD');

                let rec_id_type = $('.nav.c-event-source .active ').data('resource');

                if (rec_id_type !== 'position') {
                    //employee is resource
                    if(renderInfo['resource']['id']==='/api/users/0'){
                        //console.log('OpenShift');
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

                        $(renderInfo.el.children[0].childNodes[0].lastChild.childNodes[1]).append('<div class="dropdown d-inline-block ml-1">\n' +
                            '  <i class="dropdown-toggle" data-toggle="dropdown"> \n' +
                            '  </i>\n' +
                            '  <ul class="dropdown-menu text-center" style="font-size: 10px;">\n' +
                            '    <li ><a href="#" onclick="publishAll(\''+_userId+'\')">Publish OpenShifts</a></li>\n' +
                            '<div class="dropdown-divider"></div>'+
                            '    <li ><a href="#" onclick="unpublishAll(\''+_userId+'\')">Unpublish OpenShifts</a></li>\n' +
                            '<div class="dropdown-divider"></div>'+
                            '    <li ><a href="#" onclick="deleteAll(\''+_userId+'\')">Delete OpenShifts</a></li>\n' +
                            '  </ul>\n' +
                            '</div>');
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
                        if (renderInfo.resource.extendedProps.maxHours == null || renderInfo.resource.extendedProps.maxHours === '') {
                            maxHours = 0;
                        } else {
                            maxHours = renderInfo.resource.extendedProps.maxHours;
                        }
                        let prfHours;
                        if (renderInfo.resource.extendedProps.prfHours == null || renderInfo.resource.extendedProps.prfHours === '') {
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


                        $(renderInfo.el.children[0].childNodes[0].lastChild.childNodes[1]).append('<div class="dropdown d-inline-block ml-1">\n' +
                            '  <i class="dropdown-toggle" data-toggle="dropdown"> \n' +
                            '  </i>\n' +
                            '  <ul class="dropdown-menu text-center" style="font-size: 10px; transform: 0">\n' +
                            '    <li ><a onclick="unpublishAll(\''+_userId+'\')">Unpublish ' + name + 's Shift</a></li>\n' +
                            '<div class="dropdown-divider"></div>'+
                            '    <li ><a onclick="gotoAvail(\''+_userId+'\')">Edit ' + name + 's Availability</a></li>\n' +
                            '<div class="dropdown-divider"></div>'+
                            '    <li><a onclick="publishAll(\''+_userId+'\')">Publish ' + name + 's Shift</a></li>\n' +
                            '<div class="dropdown-divider"></div>'+
                            '    <li><a onclick="editEmp(\''+_userId+'\')" data-toggle="modal" data-target="#modal-addemp">Edit ' + name + 's Detail</a></li>\n' +
                            '<div class="dropdown-divider"></div>'+
                            '    <li><a onclick="deleteAll(\''+_userId+'\')">Delete ' + name + 's Shift</a></li>\n' +
                            '  </ul>\n' +
                            '</div>');
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
                    console.log('_col')
                    posCircle.style.backgroundColor = _col;
                    renderInfo.el.querySelector('.fc-cell-text')
                        .appendChild(posCircle);
                }

            },

            eventMouseLeave:function (eventMouseLeaveInfo) {
                console.log('eventMouseLeaveInfo',eventMouseLeaveInfo)
            },

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
                                getScheduleJobsite([]);

            console.log(sch,474747);
            localStorage.setItem("fullSchedules", JSON.stringify(sch));
            scheduleArray = sch;
            sch.forEach(function (el, index) {
                let ids = el['id'];
                let idr=el['@id'];
                scheduleId = idr;
                console.log(scheduleId);
                let names = el['name'];

                $('select.sch-list').append("<option value="+idr+">"+names+"</option>");

                let opt;
                if (index === 0) {
                    getScheduleJobsite(ids);
                    get_annotations(ids);
                    opt = new Option(names, ids, true, true);
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
            calculate_percent();
        },
        error: function (e) {

            //expire jwt token
            if (e.status == 401) {
                window.location.href = base_url2 + "/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });

    //get positions~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    getPositions();


    //console.log('fuck',calendars['calendar'].view)
    //let defaultView=calendars['calendar'].view;
    // let startCurrent=(moment(defaultView.currentStart)).format('YYYY-MM-DD');
    // let endCurrent=((moment(defaultView.currentEnd)).subtract(1, "days")).format('YYYY-MM-DD');
    // let defaultDateStart = startCurrent + ' 00:00';
    // let defaultDateEnd = endCurrent + ' 23:59';



    $("select#schedule-ls").change(function () {
        selectedSchedule = $(this).children("option:selected").val();
        getScheduleJobsite(selectedSchedule);
        calendars['calendar'].refetchResources();
        calendars['calendar'].refetchEvents();


    });

    calendars['calendar'].on('dateClick', function (info) {
        console.log('clicked on ' + info);

        var selectedSchedule = $('#schedule-ls').children("option:selected").val();
        let titlemodal = 'Create Shift On ' + ((info.date).toString()).substring(0, 10) + 'th';
        $('.titleTxt').text(titlemodal);
        $('#selectedDate').val(info.dateStr);
        if (info.view.type === "dayGridMonth") {
            $('#employeeList').val('/api/users/0').trigger('change');
            //$('#empList').val($('#employeeList').children("option:selected").val()).trigger('change');

            //get all shift templates
            $.ajax({
                method: 'get',
                url: base_url + '/api/shift_templates?scheduleId=' + selectedSchedule,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success: (res) => {
                    console.log('shift templates', res['hydra:member']);
                    let temps = res['hydra:member'];
                    let tempArray = [];
                    temps.map((tmp) => {
                        console.log('temp', tmp)
                        let id = tmp["id"];
                        let stime = tmp["startTime"];
                        let ftime = tmp["endTime"];
                        let pos = tmp["positionId"]["name"];
                        let color = tmp["color"];
                        let note = tmp["notes"];


                        $('.qualifiedll').append('<div class="form-group custblock" ><div class="time-block col-xs-6" data-id="'+id+'" style="padding: 6px;height: 40px;"><span class="show-time"><small>'+stime+" - "+ftime+'</small></span></div><div class="show-pos"><span class="tag-position">'+pos+'</span></div><div class="show-icon" data-id="'+id+'"><i class="icon-pencil"></i></div></div>');
                        tempArray.push(' <div class="col-5 border rounded " style="background-color: ' + color + ';">\n' +
                            '                             <div class="add-directly-shift d-inline p-2" data-id="'+id+'" data-dismiss="modal"><div class="c-item ">' + stime + '-' + ftime + '</div>\n' +
                            '                                 <div class="c-item rounded tag text-center ml-3">' + pos + '</div>\n' +
                            '                                 <div class="c-item ml-1" title="' + note + '"><i class="far fa-comment"></i></div></div>\n' +
                            '                                 <div class="c-item c-pen pr-1 pl-1 chooseTemp" data-id="' + id + '"><i class="fas fa-pencil-alt"></i></div>\n' +
                            '                         </div>');
                    });

                    console.log('tempArray------>',tempArray);

                    $('.qualified').html(tempArray);

                    $('.chooseTemp').on('click', function () {
                        $('.content-addshift').show();
                        $('.content-qualified').hide();
                        let idtemp = $(this).data('id');
                        // console.log('id temp',idtemp)

                        $.ajax({
                            method: 'get',
                            url: base_url + '/api/shift_templates/' + idtemp,
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,
                            },
                            success: (res) => {
                                //console.log(res)
                                $('#timeStartInput').val(res['startTime']);
                                $('#timeEndInput').val(res['endTime']);
                                $('#note').val(res['notes']);
                                $('#unpaidbreak').val(res['unpaidBreak']);
                                $('#positionList').val(res['positionId']['@id']).trigger('change');
                                $('#colorshift').val(res['color']);
                                $('div.colorShifts  i.fa-square').css('color', res['color']);

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

                    $('.add-directly-shift').on('click', function () {

                        let idtemp = $(this).data('id');
                        // console.log('id temp',idtemp)

                        $.ajax({
                            method: 'get',
                            url: base_url + '/api/shift_templates/' + idtemp,
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,
                            },
                            success: (res) => {
                                //console.log(res)

                                // create shift save click
                                let _data = {
                                    ownerId: null,
                                    positionId: res['positionId']['@id'],
                                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                                    publish: false,
                                    startTime: (info.dateStr).substring(0, 10) + ' ' + res['startTime'],
                                    endTime: (info.dateStr).substring(0, 10) + ' ' + res['endTime'],
                                    unpaidBreak: res['unpaidBreak'],
                                    color: res['color'],
                                    note: res['notes']
                                };
                                console.log('data',_data)
                                $.ajax({
                                    url: base_url + '/api/shifts',
                                    method: 'POST',
                                    contentType: "application/json",
                                    headers: {
                                        'Authorization': `Bearer ${tok}`,

                                    },
                                    data: JSON.stringify(_data),
                                    success: function (shift) {
                                        toastr.success('Shift Successfully Added.');
                                        console.log('inserted shift', shift);
                                        calendars['calendar'].refetchEvents();
                                        calculate_percent();


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
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });
        } else {

            $('#employeeList').val((info.resource.id).toString()).trigger('change');
            $('#empList').val((info.resource.id).toString()).trigger('change');
            $('#selemptimeoff').val(info.resource.id).trigger('change');
            $('.eligibleEmp').hide();
            if (info.resource.id === '/api/users/0') {

                $('.forOpenShift').removeClass('col-10');
                $('.forOpenShift').addClass('col-8');
                $('.howmanyOS').show();
                $('.eligibleEmp').show();

                //get all shift templates for positions
                $.ajax({
                    method: 'get',
                    url: base_url + '/api/shift_templates?scheduleId=' + selectedSchedule,
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,
                    },
                    success: (res) => {
                        console.log('shift templates', res['hydra:member']);
                        let temps = res['hydra:member'];
                        let tempArray = [];
                        temps.map((tmp) => {
                            console.log('temp', tmp)
                            let id = tmp["id"];
                            let stime = tmp["startTime"];
                            let ftime = tmp["endTime"];
                            let pos = tmp["positionId"]["name"];
                            let color = tmp["color"];
                            let note = tmp["notes"];


                            $('.qualifiedll').append('<div class="form-group custblock" ><div class="time-block col-xs-6" data-id="'+id+'" style="padding: 6px;height: 40px;"><span class="show-time"><small>'+stime+" - "+ftime+'</small></span></div><div class="show-pos"><span class="tag-position">'+pos+'</span></div><div class="show-icon" data-id="'+id+'"><i class="icon-pencil"></i></div></div>');
                            tempArray.push(' <div class="border rounded ml-2 mb-2" style="width: 47%; background-color: ' + color + ';">\n' +
                                '                              <div class="add-directly-shift d-inline p-2" data-id="'+id+'" data-dismiss="modal">   <div class="c-item ">' + stime + '-' + ftime + '</div>\n' +
                                '                                 <div class="c-item rounded tag text-center ml-3">' + pos + '</div>\n' +
                                '                                 <div class="c-item ml-1" title="' + note + '"><i class="far fa-comment"></i></div></div>\n' +
                                '                                 <div class="c-item c-pen pr-1 pl-1 chooseTemp" data-id="' + id + '"><i class="fas fa-pencil-alt"></i></div>\n' +
                                '                         </div>');
                        });

                        $('.qualified').html(tempArray);

                        $('.chooseTemp').on('click', function () {
                            $('.content-addshift').show();
                            $('.content-qualified').hide();
                            let idtemp = $(this).data('id');
                            // console.log('id temp',idtemp)

                            $.ajax({
                                method: 'get',
                                url: base_url + '/api/shift_templates/' + idtemp,
                                contentType: "application/json",
                                headers: {
                                    'Authorization': `Bearer ${tok}`,
                                },
                                success: (res) => {
                                    //console.log(res)
                                    $('#timeStartInput').val(res['startTime']);
                                    $('#timeEndInput').val(res['endTime']);
                                    $('#note').val(res['notes']);
                                    $('#unpaidbreak').val(res['unpaidBreak']);
                                    $('#positionList').val(res['positionId']['@id']).trigger('change');
                                    $('#colorshift').val(res['color']);
                                    $('div.colorShifts  i.fa-square').css('color', res['color']);

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

                        $('.add-directly-shift').on('click', function () {

                            let idtemp = $(this).data('id');
                            // console.log('id temp',idtemp)

                            $.ajax({
                                method: 'get',
                                url: base_url + '/api/shift_templates/' + idtemp,
                                contentType: "application/json",
                                headers: {
                                    'Authorization': `Bearer ${tok}`,
                                },
                                success: (res) => {
                                    // console.log(res)

                                    // create shift save click
                                    let _data = {
                                        // ownerId: (info.resource.id).toString(),
                                        positionId: res['positionId']['@id'],
                                        scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                                        publish: false,
                                        startTime: (info.dateStr).substring(0, 10) + ' ' + res['startTime'],
                                        endTime: (info.dateStr).substring(0, 10) + ' ' + res['endTime'],
                                        unpaidBreak: res['unpaidBreak'],
                                        color: res['color'],
                                        note: res['notes'],
                                        chain: true,
                                    };
                                    console.log('data',_data)
                                    $.ajax({
                                        url: base_url + '/api/shifts',
                                        method: 'POST',
                                        contentType: "application/json",
                                        headers: {
                                            'Authorization': `Bearer ${tok}`,

                                        },
                                        data: JSON.stringify(_data),
                                        success: function (shift) {
                                            toastr.success('Shift Successfully Added.');
                                            console.log('inserted shift', shift);
                                            calendars['calendar'].refetchEvents();
                                            calculate_percent();
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
                    error: (e) => {
                        //expire jwt token
                        if (e.status == 401) {
                            window.location.href = base_url2 + "/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });
            } else {

                if ($('.nav.c-event-source .active ').data('resource') !== 'position') {
                    let getResourcePos = info.resource.extendedProps.positionID;
                    getResourcePos.map(po => {
                        //get specific position shift templates
                        $.ajax({
                            method: 'get',
                            url: base_url + '/api/shift_templates?scheduleId=' + selectedSchedule + '&positionId=' + po,
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,
                            },
                            success: (res) => {
                                //  console.log('shift templates',res['hydra:member']);
                                let temps = res['hydra:member'];

                                temps.map((tmp) => {
                                    //console.log('temp',tmp)
                                    let id = tmp["id"];
                                    let stime = tmp["startTime"];
                                    let ftime = tmp["endTime"];
                                    let pos = tmp["positionId"]["name"];
                                    let color = tmp["color"];
                                    let note = tmp["notes"];

                                    $('.qualified').append(' <div class="border rounded ml-2 mb-2" style="width: 47%; background-color: ' + color + ';">\n' +
                                        '                               <div class="add-directly-shift d-inline p-2" data-id="' + id + '" data-dismiss="modal">  <div class="c-item ">' + stime + '-' + ftime + '</div>\n' +
                                        '                                 <div class="c-item rounded tag text-center ml-1">' + pos + '</div>\n' +
                                        '                                 <div class="c-item ml-1" title="' + note + '"><i class="far fa-comment"></i></div></div>\n' +
                                        '                                 <div class="c-item c-pen pr-1 pl-1 chooseTemp" data-id="' + id + '"><i class="fas fa-pencil-alt"></i></div>\n' +
                                        '                         </div>');
                                });

                                $('.chooseTemp').on('click', function () {
                                    $('.content-addshift').show();
                                    $('.content-qualified').hide();
                                    let idtemp = $(this).data('id');
                                    // console.log('id temp',idtemp)

                                    $.ajax({
                                        method: 'get',
                                        url: base_url + '/api/shift_templates/' + idtemp,
                                        contentType: "application/json",
                                        headers: {
                                            'Authorization': `Bearer ${tok}`,
                                        },
                                        success: (res) => {
                                            //console.log(res)
                                            $('#timeStartInput').val(res['startTime']);
                                            $('#timeEndInput').val(res['endTime']);
                                            $('#note').val(res['notes']);
                                            $('#unpaidbreak').val(res['unpaidBreak']);
                                            $('#positionList').val(res['positionId']['@id']).trigger('change');
                                            $('#colorshift').val(res['color']);
                                            $('div.colorShifts  i.fa-square').css('color', res['color']);

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

                                $('.add-directly-shift').on('click', function () {

                                    let idtemp = $(this).data('id');
                                    // console.log('id temp',idtemp)

                                    $.ajax({
                                        method: 'get',
                                        url: base_url + '/api/shift_templates/' + idtemp,
                                        contentType: "application/json",
                                        headers: {
                                            'Authorization': `Bearer ${tok}`,
                                        },
                                        success: (res) => {
                                            // console.log(res)

                                            // create shift save click
                                            let _data = {
                                                ownerId: (info.resource.id).toString(),
                                                positionId: res['positionId']['@id'],
                                                scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                                                publish: false,
                                                startTime: (info.dateStr).substring(0, 10) + ' ' + res['startTime'],
                                                endTime: (info.dateStr).substring(0, 10) + ' ' + res['endTime'],
                                                unpaidBreak: res['unpaidBreak'],
                                                color: res['color'],
                                                note: res['notes']
                                            };
                                            console.log('data',_data)
                                            $.ajax({
                                                url: base_url + '/api/shifts',
                                                method: 'POST',
                                                contentType: "application/json",
                                                headers: {
                                                    'Authorization': `Bearer ${tok}`,

                                                },
                                                data: JSON.stringify(_data),
                                                success: function (shift) {
                                                    toastr.success('Shift Successfully Added.');
                                                    console.log('inserted shift', shift);
                                                    calendars['calendar'].refetchEvents();
                                                    calculate_percent();


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
                            error: (e) => {
                                //expire jwt token
                                if (e.status == 401) {
                                    window.location.href = base_url2 + "/login";
                                }
                                toastr.error(e['responseJSON']['hydra:description']);
                            }
                        });

                    });
                } else {
                    $('.content-qualified ').hide();
                    $('.content-addshift ').show();
                    $('#positionList').val(info.resource.id).trigger('change');
                    $('#empList').val('/api/users/0').trigger('change');
                }
            }

        }
        $("#modal-addEvents").modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    });

    calendars['calendar'].on('eventClick', function (info) {
        console.log('event Click ' + info.event.rendering);

        if(info.event.rendering !== 'background') {
            if (localStorage.getItem('role') === 'employee') {
                $('#cancelBtn').hide();
            }

            if (info.event._def.resourceIds[0] === '/api/users/0') {
                $('.eligibleEmp ').show();
                $('#replace-shift').hide();
            } else {
                $('#replace-shift').show();
                $('.eligibleEmp ').hide();
            }

            $.ajax({
                method: 'GET',
                url: base_url + '/api/shifts/' + info.event.id,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success: (res) => {
                    console.log(res);

                    $("#modal-addEvents").modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                    $('#selectedDate').val(res['startTime'].substring(0, 10));
                    $('#shiftEditID').val(res['id']);
                    if (res['ownerId'] === '' || res['ownerId'] == null) {
                        $('#empList').val('/api/users/0').trigger('change');
                    } else {
                        $('#empList').val(res['ownerId']['@id']).trigger('change');
                    }

                    $('#timeStartInput').val(res['startTime'].substring(11, 16));
                    $('#timeEndInput').val(res['endTime'].substring(11, 16));
                    $('#note').val(res['note']);
                    // $('#colorshift').val(res['color']);
                    document.getElementById('changeColorForBG').style.backgroundColor = res['color'];
                    $('div.colorShifts .fa-square').css('color', res['color']);
                    $('#unpaidbreak').val(res['unpaidBreak']);

                    if (res['publish'] == true) {

                        $('#save-shift-unpublish').show();
                        $('#add-shift-publish').hide();

                    } else if (res['publish'] == false) {

                        $('#save-shift-unpublish').hide();
                        $('#add-shift-publish').show();
                    }

                    if (res['jobSitesId'] !== null) {
                        $('#jobsiteList').val(res['jobSitesId']['@id']).trigger('change');
                    }
                    if (res['positionId'] !== null) {
                        $('#positionList').val(res['positionId']['@id']).trigger('change');
                    }
                    if (res['repeated'] == true) {
                        $('#repeatShitf').val(res['repeated']);
                        $('#repeatShitf').prop("checked", true);
                        $('.repeated').show();
                        $('#repeatedPeriod').val(res['repeatPeriod']).trigger('change');
                        $('#end-repeated-date').val(res['endRepeatTime']);
                        $('#repeated').val(1);

                    } else {
                        $('#repeated').val(0);
                    }


                    let shiftHistories = res['shiftHistories'];
                    let eachDate;
                    shiftHistories.map(history => {
                        // console.log(history)

                        let datetim = (history['date']).split(' ');
                        //console.log(datetim[0])
                        let msg;

                        if (history['type'] === "create") {

                            if (history['changed_property'] == null) {
                                msg = 'created a shift';
                            }
                            let datelable = new Date(datetim[0]);


                            $('ul.timeline').append('<li class="time-label">\n' +
                                '                           <span class="bg-orange eachday">\n' +
                                '                                 ' + (datelable.toString()).substring(0, 15) + '\n' +
                                '                               </span>\n' +
                                '                        </li>');

                            $('ul.timeline').append('<li>\n' +
                                '                            <i class="fa fa-user bg-silver"></i>\n' +
                                '\n' +
                                '                            <div class="timeline-item">\n' +
                                '                                <span class="time"><i class="fa fa-clock-o"></i> ' + datetim[1] + '</span>\n' +
                                '\n' +
                                '                                <h3 class="timeline-header no-border"><span class="text-capitalize" style="font-weight: 600;">' + history['userId']['firstName'] + ' ' + history['userId']['lastName'] + '</span> <span>' + msg + '</span> </h3>\n' +
                                '                            </div>\n' +
                                '                        </li>');

                            eachDate = history['date'];

                        } else if (history['type'] === "update") {

                            // console.log('updated history',eachDate)

                            if (eachDate.substring(0, 10) !== history['date'].substring(0, 10)) {

                                let datelableup = new Date(datetim[0]);

                                $('ul.timeline').append('<li class="time-label">\n' +
                                    '                           <span class="bg-orange eachday">\n' +
                                    '                                 ' + (datelableup.toString()).substring(0, 15) + '\n' +
                                    '                               </span>\n' +
                                    '                        </li>');

                                eachDate = history['date'];
                            }


                            $('ul.timeline').append('<li>\n' +
                                '                            <i class="fa fa-user bg-blue"></i>\n' +
                                '\n' +
                                '                            <div class="timeline-item">\n' +
                                '                                <span class="time"><i class="fa fa-clock-o"></i> ' + datetim[1] + '</span>\n' +
                                '\n' +
                                '                                <h3 class="timeline-header no-border"><span class="text-capitalize" style="font-weight: 600;">' + history['userId']['firstName'] + ' ' + history['userId']['lastName'] + '</span> <span>' + history['type'] + '</span> </h3>\n' +
                                '                            </div>\n' +
                                '                        </li>');

                        }

                    });

                    let owf, owl;
                    if (res['ownerId'] === '' || res['ownerId'] == null) {
                        owf = " Open Shift";
                        owl = " Open Shift";
                    } else {

                        owf = ' ' + res['ownerId']['firstName'];
                        owl = ' ' + res['ownerId']['lastName'];
                    }
                    $('.fname').text(owf);
                    let info1 = ((new Date(res['startTime'])).toString()).substring(0, 15) + ' from ' + res['startTime'].substring(11, 16) + '-' + res['endTime'].substring(11, 16);
                    $('.info1').text(info1);
                    let posInfo;
                    if (res['positionId'] == null) {
                        posInfo = '';
                    } else {
                        posInfo = ' as ' + res['positionId']['name'];
                    }

                    let info2 = owf + ' ' + owl + posInfo + ' at ' + res['scheduleId']['name'];
                    $('.info2').text(info2);

                    //check shift request exist or not for the current shift
                    if (res['asRequesterShiftToRequest'].length !== 0) {
                        let requests = res['asRequesterShiftToRequest'];
                        //console.log(requests)
                        let lastrequest = requests[(requests.length) - 1];
                        //  console.log(lastrequest)
                        if (lastrequest['status'] === 'pendingAccept' || lastrequest['status'] === 'approve') {

                            $('#replace-shift').hide();
                            $('#view-req').show();
                            // console.log(lastrequest['id'])

                            $('.viewRequest').attr('data-reqid', lastrequest['id']);


                        }

                    }
                    $("#modal-addEvents").modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
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

            if (info.event.extendedProps.publish == true) {

                $('.shunpublish').show();
                $('.addshpublish').hide()

            } else if (info.event.extendedProps.publish == false) {

                $('.shunpublish').hide();
                $('.addshpublish').show()
            }

            let titlemodal = 'Edit Shift On ' + ((info.event.start).toString()).substring(0, 10);
            $('.titleTxt').text(titlemodal);

            $('.content-qualified').hide();
            $('.content-timeoff').hide();
            $('.content-addshift').show();
            $('.timeoffBtn').hide();
            $('.historyBtn').show();
            $('.notifyBtn').show();
            $('#delete-shift').show();
            $('.content-eligible').hide();
            $('.change-content').show();
        }


    });

    calendars['calendar'].on('eventDrop', function (eventDropInfo) {

        $('.popover').remove();//tooltip problem
        let dragID = eventDropInfo.event.id;
        let dropStart = eventDropInfo.event.start.toISOString();
        let dropEnd = eventDropInfo.event.end.toISOString();
        let newData = {};
        if (eventDropInfo.newResource == undefined) {
            newData = {
                startTime: dropStart,
                endTime: dropEnd,
                publish: false,
                chain: false
            }
        } else {
            newData = {
                ownerId: eventDropInfo.newResource.id,
                startTime: dropStart,
                endTime: dropEnd,
                publish: false,
                chain: false
            }
        }

        console.log('dropStart~~~~~~~~~~~~~~~~~~~~~>', moment(dropStart).day());
        $.ajax({
            url: base_url + '/api/shifts/' + dragID,
            method: 'PUT',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify(newData),
            success: function (r) {
                console.log('drop', dropStart);
                toastr.success('Shift Successfully Droped and Changed');
                calendars['calendar'].refetchEvents();
                console.log()
            },
            error: function (e) {

                //expire jwt token
                if (e.status == 401) {
                    window.location.href = base_url2 + "/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
                eventDropInfo.revert();
            }
        });


    });

    calendars['calendar'].on('eventResize', function (eventResizeInfo) {
        //  console.log('event Resize ' + eventResizeInfo.event.start);
        let resizeID = eventResizeInfo.event.id;
        let resizeStart = eventResizeInfo.event.start.toISOString();
        let resizeEnd = eventResizeInfo.event.end.toISOString();

        // console.log(resizeID,resizeStart,resizeEnd)
        $.ajax({
            url: base_url + '/api/shifts/' + resizeID,
            method: 'PUT',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify({

                startTime: resizeStart,
                endTime: resizeEnd,
                publish: false,
                chain: false
            }),
            success: function (r) {
                console.log('resize', r);
                toastr.success('Shift Successfully Resized and Changed');
                calendars['calendar'].refetchEvents();

            },
            error: function (e) {

                //expire jwt token
                if (e.status == 401) {
                    window.location.href = base_url2 + "/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
                eventResizeInfo.revert();
            }
        });


    });

    calendars['calendar'].on('datesRender',function(){

        calculate_percent()
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
        let defaultView=calendars['calendar'].view;
        let startCurrent=(moment(defaultView.currentStart)).format('YYYY-MM-DD');
        let endCurrent=((moment(defaultView.currentEnd)).subtract(1, "days")).format('YYYY-MM-DD');
        let defaultDateStart = startCurrent + ' 00:00';
        let defaultDateEnd = endCurrent + ' 23:59';
        if (this.checked) {
            let dataUnscheduled = {
                start_date: defaultDateStart,
                end_date: defaultDateEnd,
                schedule: '/api/schedules/' + selectedSchedule
            };
            // console.log('unscheduled employee',dataUnscheduled)
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

    //unscheduled position
    $('#unschpos').change(function () {
        let defaultView=calendars['calendar'].view;
        let startCurrent=(moment(defaultView.currentStart)).format('YYYY-MM-DD');
        let endCurrent=((moment(defaultView.currentEnd)).subtract(1, "days")).format('YYYY-MM-DD');
        let defaultDateStart = startCurrent + ' 00:00';
        let defaultDateEnd = endCurrent + ' 23:59';
        if (this.checked) {
            console.log('ccccc');
            let dataUnscheduled = {
                start_date: defaultDateStart,
                end_date: defaultDateEnd,
                schedule: '/api/schedules/' + selectedSchedule
            };
            $.ajax({
                method: 'POST',
                url: base_url + '/api/positions/unscheduled',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataUnscheduled),
                success: (res) => {
                    console.log('unscheduled position', res);
                    let posess = res['hydra:member'];
                    posess.map((pos) => {
                        console.log(pos, pos['@id'])
                        let resourceItem = calendars['calendar'].getResourceById(pos['@id']);
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
            $.ajax({
                url: base_url + '/api/positions',
                method: 'GET',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,

                },
                success: function(r){

                    console.log(r['hydra:member'])
                    listPositions = r['hydra:member'];
                    listPositions.map(pos => {
                        console.log('positions', pos)
                        let positionResource = {
                            id: pos['@id'],
                            title: pos['name'],
                            color: pos['color']
                        }

                        calendars['calendar'].addResource(positionResource);

                    });
                },
                error: function (e) {

                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });
        }

    });

    //add shift(event)
    $('#add-shift').unbind('click').on('click', function () {

        let OwnerID = $('#empList').val() !== '/api/users/0' ?  $('#empList').val(): null;

        let selectedPos = $('#positionList').val() === '' ? 'No Position' : $('#positionList').val();

        let unp = $('#unpaidbreak').val() === '' || $('#unpaidbreak').val() == null ? '0' : $('#unpaidbreak').val();


        if ($('#saveAstemp').prop('checked')) {
            // console.log('save as shift template')

            let dataShiftTmp = {
                positionId: selectedPos,
                scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                startTime: $("#timeStartInput").val(),
                endTime: $("#timeEndInput").val(),
                notes: $("#note").val(),
                color: document.getElementById('changeColorForBG').style.backgroundColor,
                unpaidBreak: parseInt(unp)
            };
            // console.log('data shift template',dataShiftTmp)
            $.ajax({
                method: 'post',
                url: base_url + '/api/shift_templates',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataShiftTmp),
                success: (res) => {
                    // console.log(res)
                    toastr.success('Shift Template Successfully Added.');


                },
                error: (e) => {
                    // console.log(e)
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            })
        }

        let eligOpenShift = [];
        $.each($(".eligChbox[type='checkbox']:checked"), function () {
            eligOpenShift.push($(this).val());
        });
        console.log('favorite', eligOpenShift);
        let boxdate = moment(($('#end-repeated-date').val()).substring(0, 10), 'DD-MM-YYYY');
        let dateFormat = boxdate.format('DD-MM-YYYY')
        let $endRepeatTime = ($('#end-repeated-date').val()).substring(0, 10) == '' ? '' : dateFormat;

        if ($('#shiftEditID').val() !== '') {


            if ($('#repeated').val() == 0) {
                console.log('edit exist shift');

                let edId = $('#shiftEditID').val();
                let data = {
                    ownerId: OwnerID,
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                    repeated: $('#repeatShitf').prop("checked"),
                    repeatPeriod: parseInt(document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value) ,
                    endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                    jobSitesId: $('#jobsiteList').val(),
                    startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                    endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                    unpaidBreak: parseInt(unp),
                    color: document.getElementById('changeColorForBG').style.backgroundColor,
                    note: $('#note').val(),
                    eligibleOpenShiftUser: eligOpenShift

                };
                //console.log('data',data)
                $.ajax({
                    url: base_url + '/api/shifts/' + edId,
                    method: 'PUT',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify(data),
                    success: function (shift) {
                        toastr.success('Shift Successfully Updated.');
                        console.log('refetch updated event', shift)
                        calendars['calendar'].refetchEvents();
                        calculate_percent();


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
                let $repeatPeriod = document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value ;

                console.log('edit exist shift')
                console.log($repeatPeriod)
                let dataEvent = {
                    ownerId: OwnerID,
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                    repeated: true,
                    repeatPeriod: parseInt($repeatPeriod) ,
                    endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                    jobSitesId: $('#jobsiteList').val(),
                    startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                    endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                    unpaidBreak: parseInt(unp),
                    color: document.getElementById('changeColorForBG').style.backgroundColor,
                    note: $('#note').val(),
                    eligibleOpenShiftUser: eligOpenShift,
                    chain: true
                };
                $('#modal-updateRepeated').modal('show');
                $('#shiftIdRepeated').val($('#shiftEditID').val());

                //save shift
                $('.applyToAll').on('click', function () {

                    let edId = $('#shiftIdRepeated').val();


                    dataEvent.chain=true;
                    dataEvent.repeated=true;

                    console.log('dateEventall', dataEvent)
                    $.ajax({
                        url: base_url + '/api/shifts/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(dataEvent),
                        success: function (res) {
                            console.log('update all repeated shift', res)
                            toastr.success('Shifts Successfully Updated.');

                            // var event = calendars['calendar'].getEventById(res['id']);
                            // event.remove();
                            calendars['calendar'].refetchEvents();
                            calculate_percent();
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

                $('.applyToOne').on('click', function () {

                    let edId = $('#shiftIdRepeated').val();

                    //console.log('data',data)
                    dataEvent.chain=false;
                    dataEvent.repeated=false;
                    console.log('dateEventone', dataEvent)
                    $.ajax({
                        url: base_url + '/api/shifts/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(dataEvent),
                        success: function (res) {
                            console.log('updated one repeated shift', res)
                            toastr.success('Shift Successfully Updated.');

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
            }
        } else if ($('#shiftEditID').val() === '') {
            console.log('null edit id-1');
            let chainRepeat;
            if ($('#repeatedPeriod').val() === null || $('#repeatedPeriod').val() == '0') {
                chainRepeat = false
            }else {
                chainRepeat = true
            }

            let data = {
                ownerId: OwnerID,
                positionId: $('#positionList').val(),
                scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                publish: false,
                repeated: $('#repeatShitf').prop("checked"),
                repeatPeriod: parseInt($('#repeatedPeriod').val()) ,
                endRepeatTime: $endRepeatTime +' '+ '23:59:59',
                jobSitesId: $('#jobsiteList').val(),
                startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                unpaidBreak: parseInt(unp),
                color: document.getElementById('changeColorForBG').style.backgroundColor,
                note: $('#note').val(),
                eligibleOpenShiftUser: eligOpenShift,
                chain: chainRepeat,
            };
            console.log('data',data)
            $.ajax({
                url: base_url + '/api/shifts',
                method: 'POST',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,

                },
                data: JSON.stringify(data),
                success: function (shift) {
                    toastr.success('Shift Successfully Added.');
                    console.log('inserted shift', shift);
                    calendars['calendar'].refetchEvents();
                    calculate_percent();


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

    //save & publish shift(event)
    $('#add-shift-publish').unbind('click').on('click', function () {

        let OwnerID = $('#empList').val() === '/api/users/0' ? null : $('#empList').val();
        let selectedPos = $('#positionList').val() === '' ? 'No Position' : $('#positionList').val();
        let unp = $('#unpaidbreak').val() === '' || $('#unpaidbreak').val() == null ? '0' : $('#unpaidbreak').val();

        let dateMoment = moment(($('#end-repeated-date').val()).substring(0, 10), 'DD-MM-YYYY');
        let dateFormat = dateMoment.format('YYYY-MM-DD')
        let $endRepeatTime = ($('#end-repeated-date').val()).substring(0, 10) == '' ? '' : dateFormat;
        let eligOpenShift = [];
        $.each($(".eligChbox[type='checkbox']:checked"), function () {
            eligOpenShift.push($(this).val());
        });
        console.log('favorite', eligOpenShift);

        if ($('#saveAstemp').prop('checked')) {
            // console.log('save as shift template')

            let dataShiftTmp = {
                positionId: selectedPos,
                scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                startTime: $("#timeStartInput").val(),
                endTime: $("#timeEndInput").val(),
                notes: $("#note").val(),
                color: document.getElementById('changeColorForBG').style.backgroundColor,
                unpaidBreak: parseInt(unp)
            };
            //console.log('data shift template',dataShiftTmp)
            $.ajax({
                method: 'post',
                url: base_url + '/api/shift_templates',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataShiftTmp),
                success: (res) => {
                    // console.log(res)
                    toastr.success('Shift Template Successfully Added.');
                },
                error: (e) => {
                    // console.log(e)
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }


            })
        }

        if ($('#shiftEditID').val() !== '') {

            if ($('#repeated').val() == 0) {
                console.log('not null')
                let edId = $('#shiftEditID').val();
                let data = {
                    ownerId: OwnerID,
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                    repeated: $('#repeatShitf').prop("checked"),
                    repeatPeriod: parseInt(document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value) ,
                    endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                    jobSitesId: $('#jobsiteList').val(),
                    startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                    endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                    unpaidBreak: parseInt(unp),
                    color: document.getElementById('changeColorForBG').style.backgroundColor,
                    note: $('#note').val(),
                    eligibleOpenShiftUser: eligOpenShift,
                    publish: true,
                };
                //console.log('data',data)
                $.ajax({
                    url: base_url + '/api/shifts/' + edId,
                    method: 'PUT',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify(data),
                    success: function (res) {
                        //console.log(r)
                        // $('#modal-addEvents').modal('hide');
                        toastr.success('Shift Successfully Updated.');
                        // var event = calendars['calendar'].getEventById(res['id']);
                        // event.remove();
                        calendars['calendar'].refetchEvents();
                        calculate_percent();

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
                console.log('repeated')
                let dataEvent = {
                    ownerId: OwnerID,
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                    repeated: $('#repeatShitf').prop("checked"),
                    repeatPeriod: parseInt(document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value) ,
                    endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                    jobSitesId: $('#jobsiteList').val(),
                    startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                    endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                    unpaidBreak: parseInt(unp),
                    color: document.getElementById('changeColorForBG').style.backgroundColor,
                    note: $('#note').val(),
                    chain: true,
                    publish: true,
                    eligibleOpenShiftUser: eligOpenShift
                };

                $('#modal-updateRepeated').modal('show');
                $('#shiftIdRepeated').val($('#shiftEditID').val());

                //save shift
                $('.applyToAll').on('click', function () {

                    let edId = $('#shiftIdRepeated').val();

                    //console.log('data',data)
                    dataEvent.chain = true;
                    dataEvent.repeated=true;
                    console.log('dateEvent', dataEvent)
                    $.ajax({
                        url: base_url + '/api/shifts/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(dataEvent),
                        success: function (res) {
                            // console.log(r)
                            //  $('#modal-updateRepeated').modal('hide');
                            toastr.success('Shifts Successfully Updated.');
                            // var event = calendars['calendar'].getEventById(res['id']);
                            // event.remove();
                            calendars['calendar'].refetchEvents();
                            calculate_percent();

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
                $('.applyToOne').on('click', function () {

                    let edId = $('#shiftIdRepeated').val();

                    //console.log('data',data)
                    dataEvent.chain = false;
                    dataEvent.repeated = false;
                    console.log('dateEvent', dataEvent)
                    $.ajax({
                        url: base_url + '/api/shifts/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(dataEvent),
                        success: function (res) {
                            // console.log(r)
                            //  $('#modal-updateRepeated').modal('hide');
                            toastr.success('Shift Successfully Updated.');
                            // var event = calendars['calendar'].getEventById(res['id']);
                            // event.remove();
                            calendars['calendar'].refetchEvents();
                            calculate_percent();

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
        } else if ($('#shiftEditID').val() == '') {
            console.log('null edit id-2')
            // create shift save & publish click
            // let $repeatPeriod = parseInt(document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value) ;
            let chainRepeat;
            if ($('#repeatedPeriod').val() === null || $('#repeatedPeriod').val() == '0') {
                chainRepeat = false
            }else {
                chainRepeat = true
            }

            let data = {
                ownerId: OwnerID,
                positionId: $('#positionList').val(),
                scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                publish: true,
                repeated: $('#repeatShitf').prop("checked"),
                repeatPeriod: parseInt($('#repeatedPeriod').val()) ,
                endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                jobSitesId: $('#jobsiteList').val(),
                startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                unpaidBreak: parseInt(unp),
                color: document.getElementById('changeColorForBG').style.backgroundColor,
                note: $('#note').val(),
                eligibleOpenShiftUser: eligOpenShift,
                chain: chainRepeat
            };
            console.log('data',data)
            $.ajax({
                url: base_url + '/api/shifts',
                method: 'POST',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(data),
                success: function (shift) {
                    calculate_percent();
                    toastr.success('Shift Successfully Added.');
                    console.log('inserted publish shift', shift)
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
        }


    });

    //save & unpublish shift(event)
    $('#save-shift-unpublish').on('click', function () {
        console.log('unpublish')

        let unp = $('#unpaidbreak').val() === '' || $('#unpaidbreak').val() == null ? '0' : $('#unpaidbreak').val();
        let OwnerID = $('#empList').val() === '/api/users/0' ? null : $('#empList').val();
        let selectedPos = $('#positionList').val() === '' ? 'No Position' : $('#positionList').val();
        let dateMoment = moment(($('#end-repeated-date').val()).substring(0, 10), 'DD-MM-YYYY');
        let dateFormat = dateMoment.format('YYYY-MM-DD');
        let $endRepeatTime = ($('#end-repeated-date').val()).substring(0, 10) == '' ? '' : dateFormat;
        let eligOpenShift = [];
        $.each($(".eligChbox[type='checkbox']:checked"), function () {
            eligOpenShift.push($(this).val());
        });
        console.log('favorite', eligOpenShift);

        if ($('#saveAstemp').prop('checked')) {
            // console.log('save as shift template')

            let dataShiftTmp = {
                positionId: selectedPos,
                scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                startTime: $("#timeStartInput").val(),
                endTime: $("#timeEndInput").val(),
                notes: $("#note").val(),
                color: document.getElementById('changeColorForBG').style.backgroundColor,
                unpaidBreak: parseInt(unp)
            };
            // console.log('data shift template',dataShiftTmp)
            $.ajax({
                method: 'post',
                url: base_url + '/api/shift_templates',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataShiftTmp),
                success: (res) => {
                    // console.log(res)
                    toastr.success('shift template successfully added.');


                },
                error: (e) => {
                    // console.log(e)
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error('Something wrong,Try again!')
                }


            })
        }


        if ($('#shiftEditID').val() !== '') {
            console.log('not null')

            if ($('#repeated').val() == 0) {

                let edId = $('#shiftEditID').val();
                let data = {
                    ownerId: OwnerID,
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                    repeated: $('#repeatShitf').prop("checked"),
                    repeatPeriod: parseInt(document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value) ,
                    endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                    jobSitesId: $('#jobsiteList').val(),
                    startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                    endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                    unpaidBreak: parseInt(unp),
                    color: document.getElementById('changeColorForBG').style.backgroundColor,
                    note: $('#note').val(),
                    publish: false,
                    eligibleOpenShiftUser: eligOpenShift

                };
                //console.log('data',data)
                $.ajax({
                    url: base_url + '/api/shifts/' + edId,
                    method: 'PUT',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify(data),
                    success: function (shift) {

                        toastr.success('Shift Successfully Unpublished.');
                        console.log('refetch unpublished single event', shift)

                        calendars['calendar'].refetchEvents();
                        calculate_percent();


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

                let dateMoment = moment(($('#end-repeated-date').val()).substring(0, 10), 'DD-MM-YYYY');
                let dateFormat = dateMoment.format('YYYY-MM-DD');
                let $endRepeatTime = ($('#end-repeated-date').val()).substring(0, 10) == '' ? '' : dateFormat;
                console.log('repeated')
                let dataEvent = {
                    ownerId: OwnerID,
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + ($('#schedule-ls').val()).toString(),
                    repeated: $('#repeatShitf').prop("checked"),
                    repeatPeriod: parseInt(document.getElementById('repeatedPeriod').options[document.getElementById('repeatedPeriod').selectedIndex].value) ,
                    endRepeatTime: $endRepeatTime + ' ' + '23:59:59',
                    jobSitesId: $('#jobsiteList').val(),
                    startTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeStartInput').val(),
                    endTime: ($('#selectedDate').val()).substring(0, 10) + ' ' + $('#timeEndInput').val(),
                    unpaidBreak: parseInt(unp),
                    color: document.getElementById('changeColorForBG').style.backgroundColor,
                    note: $('#note').val(),
                    chain: true,
                    publish: false,
                    eligibleOpenShiftUser: eligOpenShift

                };

                $('#modal-updateRepeated').modal('show');
                $('#shiftIdRepeated').val($('#shiftEditID').val());

                //save shift
                $('.applyToAll').on('click', function () {

                    let edId = $('#shiftIdRepeated').val();

                    //console.log('data',data)
                    dataEvent.chain = true;
                    console.log('dateEvent', dataEvent)
                    $.ajax({
                        url: base_url + '/api/shifts/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(dataEvent),
                        success: function (shift) {
                            toastr.success('Shift Successfully Unpublished.');
                            console.log('refetch unpublished single event', shift)
                            //  var event = calendars['calendar'].getEventById(shift['id']);
                            //event.remove();
                            calendars['calendar'].refetchEvents();
                            calculate_percent();

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
                $('.applyToOne').on('click', function () {

                    let edId = $('#shiftIdRepeated').val();

                    //console.log('data',data)
                    dataEvent.chain = false;
                    console.log('dateEvent', dataEvent);
                    $.ajax({
                        url: base_url + '/api/shifts/' + edId,
                        method: 'PUT',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        data: JSON.stringify(dataEvent),
                        success: function (shift) {
                            toastr.success('Shift Successfully Unpublished.');
                            console.log('refetch unpublished single event', shift)

                            // var event = calendars['calendar'].getEventById(shift['id']);
                            // event.remove();
                            calendars['calendar'].refetchEvents();
                            calculate_percent();

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
        }
    });

    //delete shift
    $('#delete-shift').on('click', function () {

        if ($('#repeated').val() == 1) {
            console.log('repeated = 1 delete')

            $('#modal-deleteRepeated').modal('show');
            $('#shiftIdRepeated').val($('#shiftEditID').val());

            $('#shiftIdRepeateddel').val($('#shiftEditID').val());
            let idDeleted = $('#shiftIdRepeateddel').val();

            console.log('DELETE shifts+++++++++' ,idDeleted)

            //delete shift
            $('.deleteAll').on('click', function () {

                $.ajax({
                    url: base_url + '/api/shifts/' + idDeleted,
                    method: 'DELETE',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify({
                        chain: true
                    }),
                    success: function (rem) {
                        toastr.success('Shift Successfully Deleted.');
                        console.log('refetch deleted event', rem)

                        var event = calendars['calendar'].getEventById(idDelete);
                        // event.remove();
                        calendars['calendar'].refetchEvents();
                        calculate_percent();

                    },
                    error: function (e) {

                        console.log('error', e)
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });
            });
            $('.deleteOne').on('click', function () {

                $.ajax({
                    url: base_url + '/api/shifts/' + idDeleted,
                    method: 'DELETE',
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    data: JSON.stringify({
                        chain: false
                    }),
                    success: function (rem) {
                        toastr.success('Shift Successfully Deleted.');
                        console.log('refetch deleted event', rem)

                        var event = calendars['calendar'].getEventById(idDelete);
                        //  event.remove();
                        calendars['calendar'].refetchEvents();
                        calculate_percent();


                    },
                    error: function (e) {

                        console.log('error', e)
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });
            });


        } else if ($('#repeated').val() == 0) {
            console.log('repeated = 0 delete')

            $('#modal-deleteShift').modal('show');

            $('#shiftIdSingle').val($('#shiftEditID').val());
        }
    });

    $('#final-delete-shift').on('click',function () {
        let idDelete=$('#shiftIdSingle').val();
        console.log('delte shictsldjflsdjflkjslkj', base_url + '/api/shifts/' + idDelete);

        $.ajax({
            method: 'DELETE',
            url: base_url+'/api/shifts/'+idDelete,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data: JSON.stringify({
                chain: true
            }),
            success: function (rem) {
                toastr.success('Shift Successfully Deleted.');
                console.log('refetch deleted event', rem)

                var event = calendars['calendar'].getEventById(idDelete);
                // event.remove();
                calendars['calendar'].refetchEvents();
                calculate_percent();

            },
            error: function (e) {

                console.log('error', e);
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });
    });

    //


    //final edit employee
    $('#edit-employee').click(function () {
        let edID= $('#editIdemp').val();
        let fixedDays;
        if($("input[name='contract']:checked").val() === 'zero'){
            fixedDays=0;
        }else if($("input[name='contract']:checked").val() === 'fixed'){
            fixedDays=$('#countDays').val();
        }

        let data = {
            firstName:$('#firstname').val(),
            lastName:$('#lastname').val(),
            mobile:$('#mobile-num').val(),
            timezone:$('#seltimezone').children("option:selected").val(),
            image:$('#picurl').val(),
            useCustomTimezone:$('#useTimezone').prop("checked"),
            note:$('#note').val(),
            userHasSchedule:$('#schList').val(),
            positions:$('#posList').val(),
            userBusinessRoles: [{
                role: roleEmail === $('#email').val() && personRole === 'account' ? personRole : $('#roleList').val(),
                baseHourlyRate:$('#base-rate').val(),
                maxHoursWeek: parseInt($('#max-hour').val()),
                calculateOT: $('#exempt').prop("checked"),
                payrollOT: $('#overtimeRate').val(),
                editTimeSheet: $('#allow').prop('checked'),
                hideInScheduler: $('#hide-scheduler').prop("checked"),
                terminalId:$('#employeeID').val(),
                contract: $("input[name='contract']:checked").val(),
                fixedDayesContract:parseInt(fixedDays)
            }]

        };

        console.log(data)
        $.ajax({
            method:'PUT',
            url:base_url+'/api/users/'+edID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                console.log(res)

                toastr.success('employee successfully Updated.');
                calendars['calendar'].refetchResources();
                calendars['calendar'].refetchEvents();
                calculate_percent();

                if(res['email'] === localStorage.getItem('email')){

                    localStorage.setItem('schedules',JSON.stringify(res['userHasSchedule']));
                }


            },
            error:(e)=>{
                // console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });

    });

    //add time off request from modal
    $('.sendTimeOffReq').on('click', function () {
        let start, end;

        if ($('#allDay').prop("checked") == true) {
            let startDate = $('#start-date-timeoff').val();
            start = startDate.concat("00:00:00");
            let endDate = $('#end-date-timeoff').val();
            end = endDate.concat("23:59:59");
        } else {
            let startDate = $('#start-date-timeoff').val();
            let startTime = $('#timeStartoff').val();
            let endTime = $('#timeEndoff').val();
            start = startDate.concat(startTime);
            end = startDate.concat(endTime);

        }

        let data = {
            userID: $('.select-emp').val(),
            type: $('#type-off').val(),
            paidHour: $('#paid-hour').val(),
            message: $('#message').val(),
            startTime: start,
            endTime: end,

        };
        console.log('data', data)
        $.ajax({
            url: base_url + '/api/time_off_requests',
            method: 'POST',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify(data),
            success: function (r) {

                // console.log(r)
                $('#modal-addEvents').modal('hide');
                toastr.success('Time Off Request Successfully Added');
                calendars['calendar'].refetchResources();
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

    //add annotation
    $('.saveAnnot').on('click', function () {
        $('.annotType:checked').each(function () {
            console.log(this.value);
        });
        let annotTypes = [];
        $.each($("input[name='annotType']:checked"), function () {
            annotTypes.push($(this).val());
        });

        let startAnnot = ($('#start-date-annot').val()).substring(0, 10);
        let startDate = moment(startAnnot, 'DD/MM/YYYY').format('YYYY-MM-DD')
        let endAnnot=($('#end-date-annot').val()).substring(0, 10);
        let endDate = moment(endAnnot,'DD/MM/YYYY').format('YYYY-MM-DD');
        console.log('endAnnot',startDate,endDate)

        if ($('#annotEditId').val() !== '') {
            let edIdAnnot = $('#annotEditId').val();
            let dataEdit = {
                title: $('#title').val(),
                message: $('#message_annot').val(),
                type: annotTypes.toString(),
                scheduleId: '/api/schedules/' + $('.select-schedule-annot').children("option:selected").val(),
                color: document.getElementById('changeColorForBG2').style.backgroundColor,
                startDate: startDate + ' ' + '00:00',
                endDate: endDate + ' ' + '23:59',
            };

            $.ajax({
                method: 'PUT',
                url: base_url + '/api/annotations/' + edIdAnnot,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataEdit),
                success: (resAnnot) => {
                    console.log(resAnnot);

                    toastr.success('Annotation Successfully Updated.');
                    get_annotations(selectedSchedule);
                    //  $('.annotation-content .selected .txtRibbon').html('<i class="far fa-calendar-alt mr-1"></i>' + sts.substring(8, 10) + ' ' + sts.substring(4, 7));
                    //  $('.annotation-content .selected .titleAnnot').html(r["title"] + '<button type="button" data-id="' + r['id'] + '" class="btn btn-sm d-inline editAnnotation" data-toggle="modal" data-target="#modal-addAnnot" title="more info"><i class="fas fa-arrow-circle-right" ></i></button>');
                    //  $('.annotation-content .selected .msgAnnot').text(r['message']);


                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });

        } else {

            let dataAnnot = {
                title: $('#title').val(),
                message: $('#message_annot').val(),
                type: annotTypes.toString(),
                color: "blue",
                scheduleId: '/api/schedules/' + $('.select-schedule-annot').children("option:selected").val(),
                color: document.getElementById('changeColorForBG2').style.backgroundColor,
                startDate: startDate + ' ' + '00:00',
                endDate: endDate + ' ' + '23:59',
            };
            console.log('annot data', dataAnnot)
            $.ajax({
                method: 'POST',
                url: base_url + '/api/annotations',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataAnnot),
                success: (r) => {
                    console.log(r);
                    toastr.success('Annotation Successfully Added.');
                    get_annotations(selectedSchedule);
                    //show it in week view in the calendar's column header automatically

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

    });

    //delete annotation
    $('.finaldeleteAnnot').on('click', function () {

        let delAnnotId = $('#annotEditId').val();

        $.ajax({
            url: base_url + '/api/annotations/' + delAnnotId,
            method: 'DELETE',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            success: function (r) {
                // console.log(r)
                toastr.success('Annotation Successfully Deleted.');
                get_annotations(selectedSchedule);
                //$('.annotation-content .detailAnnots.selected').hide();

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


    let statusObj={};
    //select in publish & notify part

    async function postPublish(url, _publishData){
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${tok}`,
            },
            body: JSON.stringify(_publishData)
        });
        let publish = await response.json();
        return publish;
    }
    async function postPoblishAndUnpublish(url, data) {
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${tok}`,
            },
            body: JSON.stringify(data),
        });
        let publish = await response.json();
        return publish;
    }

    let arrUsers = [];

    let resend_notif = document.getElementById('resend-notifications');
    let unpublishShift = document.getElementById('unpublish-shifts');
    let publishShift = document.getElementById('publish-shifts');

    let changed_user = document.getElementById('userChanged');
    let notify_text = document.getElementById('notify_msg');
    let start = document.getElementById('start-date-notify');
    let end = document.getElementById('end-date-notify');
    let scheduleIdr = document.getElementById('schedule-ls');
    let toggleBtn = document.getElementById('toggleBtn');

    loadAllEvent();
    function loadAllEvent(){
        resend_notif.addEventListener('click', resendNotif);
        unpublishShift.addEventListener('click', unpublishShifts);
        publishShift.addEventListener('click', publish);
    }

    function resendNotif(e) {
        e.preventDefault();

        let scheduleValue = scheduleIdr.options[scheduleIdr.selectedIndex].value;
        let scheduleIRI;
        let userChange;

        scheduleArray.forEach(el => {
            if (el['id'] == scheduleValue) {
                scheduleIRI = el['@id']
            }
        });

        if (changed_user.checked) {
            userChange = true;
        }else {
            userChange = false;
        }

        setTimeout(()=>{
            let pubData = {
                users: arrUsers,
                schedule: scheduleIRI,
                start: moment(start.value).format('YYYY-MM-DD'),
                end: moment(end.value).format('YYYY-MM-DD'),
                notify_text: notify_text.value,
                changed_user: userChange
            };

            postPublish(base_url + '/api/shifts/publish_and_notify', pubData)
                .then(data => {
                    notify_text.value = '';
                    changed_user.checked = false;
                    document.querySelector('.select2-selection__rendered').innerHTML = '';
                    toggleBtn.click();
                    toastr.success(" Publish and notify")
                    // console.log('sendData',data)
                })
                .catch(err => {
                        if (err == 401) {
                            window.location.href = base_url+"/login";
                        }
                        toastr.error("An error has occurred. Please try again")
                    }
                );
        },2000)
    }
    function unpublishShifts(e){
        e.preventDefault();
        let data = {
            users: arrUsers,
            shiftStartTime: moment(start.value).format('YYYY-MM-DD'),
            shiftEndTime: moment(end.value).format('YYYY-MM-DD'),
            publish: false
        }
        postPoblishAndUnpublish(base_url + '/api/shifts/publish_and_unpublish', data)
            .then(data =>{
                notify_text.value = '';
                changed_user.checked = false;
                document.querySelector('.select2-selection__rendered').innerHTML = '';
                toggleBtn.click();
                toastr.success(" unpublish and unpublish")

            })
            .catch(err => {
                console.log(err)
                toastr.error("An error has occurred. Please try again")
            })

    }
    function publish(e) {
        e.preventDefault();

        let data = {
            users: arrUsers,
            shiftStartTime: moment(start.value).format('YYYY-MM-DD'),
            shiftEndTime: moment(end.value).format('YYYY-MM-DD'),
            publish: true
        }

        postPoblishAndUnpublish(base_url + '/api/shifts/publish_and_unpublish', data)
            .then(data =>{
                notify_text.value = '';
                changed_user.checked = false;
                document.querySelector('.select2-selection__rendered').innerHTML = '';
                toggleBtn.click();
                toastr.success(" Publish and shifts")
            })
            .catch(err => {
                console.log(err)
                toastr.error("An error has occurred. Please try again")
            })
    }


    $('#select-who').on('select2:select', function (e) {
        var dataSelect = e.params.data;

        // console.log(arrUsers);

        // console.log('selectwho',dataSelect);
        let iriId=dataSelect.id;
        arrUsers.push(iriId);
        // console.log(arrUsers);

        var resourceA = calendars['calendar'].getResourceById(iriId);
        var events = resourceA.getEvents();
        // console.log('getEvents',events)
        var eventStatus = [];

        $.each(events, function( index, value ) {
            // console.log( index + ": " + value );
            if(value.rendering !== 'background'){
                eventStatus.push(value.extendedProps.publish)
            }
        });

        // console.log('eventStatus',eventStatus)
        if(eventStatus.length !== 0) {
            statusObj[iriId] = eventStatus;
            checkForNotify(statusObj);
        }else{
            $('#resend-notifications').show();
            $('#publish-shifts').hide();
            $('#unpublish-shifts').hide();
        }

    });

    //select in publish & notify part
    $('#select-who').on('select2:unselect', function (e) {
        var dataSelect = e.params.data;
        // console.log('selectwho-->deleteItem',dataSelect);
        let iriId=dataSelect.id;
        for (let i = arrUsers.length -1; i >= 0; i--) {
            if (arrUsers[i] == iriId) {
                arrUsers.splice(i, 1);
            }
        }
        // console.log(arrUsers);

        // console.log('statusObj',statusObj,iriId)
        delete statusObj[iriId];
        checkForNotify(statusObj);
    });

    let checkForNotify=(obj)=>{
        console.log('check Function',obj)
        if(Object.keys(obj).length!== 0){
            if(Object.keys(obj).length === 1){
                console.log('one',obj[Object.keys(obj)[0]])
                let stsArr=obj[Object.keys(obj)[0]];
                $.each(stsArr, function( i, val ) {
                    if(val === false){
                        $('#resend-notifications').hide();
                        $('#publish-shifts').show();
                        $('#unpublish-shifts').show();
                        return false;
                    }else{
                        $('#resend-notifications').show();
                        $('#publish-shifts').hide();
                        $('#unpublish-shifts').show();
                    }
                });


            }else{
                console.log('more than 2')
                $.each( obj, function( key, value ) {
                    if(jQuery.inArray(false , value) !== -1){
                        $('#resend-notifications').hide();
                        $('#publish-shifts').show();
                        $('#unpublish-shifts').show();
                        return false;
                    }else{
                        $('#resend-notifications').show();
                        $('#publish-shifts').hide();
                        $('#unpublish-shifts').show();
                    }
                });
            }

        }else{
            $('#resend-notifications').hide();
            $('#publish-shifts').show();
            $('#unpublish-shifts').show();
        }

    }

});
//calendars['calendar'].addEventSource();
//goto availability page
let gotoAvail=(id)=>{
    //goto availability page

    let params = { 'userAvail': id };
    let new_url = base_url2+"/availability?" + jQuery.param(params);
    window.location.href = new_url;
};

//unpublish all shift for selected employee
let unpublishAll=(id)=>{

    let uriId='/api/users/'+id;
    var resourceA = calendars['calendar'].getResourceById(uriId);
    console.log('unpublishAll',resourceA)
    var events = resourceA.getEvents();
    var eventTitles = events.map(function(event) {
        $.ajax({
            url: base_url + '/api/shifts/' + event.id,
            method: 'PUT',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify({
                publish:false
            }),
            success: function (res) {
                // console.log(res)
                calendars['calendar'].refetchEvents();
                // calculate_percent(moment(event.start).day()); i comment by myself publish and unpublish is not important
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
        // return event.id
    });
    toastr.success('Shift Successfully Unpublished.');
    // console.log(eventTitles);
};

//publish all shifts for selected employee
let publishAll=(id)=>{

    let uriId='/api/users/'+id;
    var resourceA = calendars['calendar'].getResourceById(uriId);
    console.log('publishAll',resourceA)
    var events = resourceA.getEvents();
    var eventTitles = events.map(function(event) {
        $.ajax({
            url: base_url + '/api/shifts/' + event.id,
            method: 'PUT',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify({
                publish:true
            }),
            success: function (res) {
                // console.log(res)
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
        // return event.id
    });
    toastr.success('Shift Successfully Published.');
    // console.log(eventTitles);
};

//delete all specific resource's shift
let deleteAll=(id)=>{

    let uriId='/api/users/'+id;
    var resourceA = calendars['calendar'].getResourceById(uriId);
    console.log('publishAll',resourceA)
    var events = resourceA.getEvents();
    var eventTitles = events.map(function(event) {

        var event = calendars['calendar'].getEventById(event.id);
        let start_event=event.start;
        event.remove();
        $.ajax({
            url: base_url + '/api/shifts/' + event.id,
            method: 'DELETE',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            success: (rem)=>{

            },
            error: function (e) {

                console.log('error', e)
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });
        // return event.id
    });
    toastr.success('Shift Successfully Deleted.');
    // console.log(eventTitles);
};

//modal edit for edit employee's detail
let editEmp=(id)=>{

    let currencId = JSON.parse(localStorage.getItem('billing'));
    document.querySelector('.currencySymbol').innerHTML = currencId['currency']['symbol'];
    document.querySelector('.currencySymbols').innerHTML = currencId['currency']['symbol'];

    $.ajax({
        method:'GET',
        url:base_url+'/api/users/'+id,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log('success',res)
            let findKey=Object.keys(res['userBusinessRoles'])[0];
            let userBus=res['userBusinessRoles'][findKey];

            $('#email').val(res['email']);
            $('#firstname').val(res['firstName']);
            $('#lastname').val(res['lastName']);
            $('#mobile-num').val(res['mobile']);
            $('#editIdemp').val(res['id']);

            $('#base-rate').val(userBus['baseHourlyRate']);
            $('#max-hour').val(userBus['maxHoursWeek']);
            $('#employeeID').val(userBus['terminalId']);
            $('#note').val(res['note']);
            $('#overtimeRate').val(userBus['payrollOT']);

            console.log(roleEmail, res.email)

            if(personRole === 'account' && roleEmail == res.email){
                $('.roleListEmpModal').hide();
            }else{
                $('.roleListEmpModal').show();
                $('#roleList').val(userBus['role']);
            }


            if(userBus['contract'] === 'zero'){

                $('input:radio[name=contract][id=zero]').prop('checked', true);

            }else if(userBus['contract'] === 'fixed'){


                $('input:radio[name=contract][id=fixed]').prop('checked', true);
                $('.contractCondition').css('display','block');
                $('#countDays').val(userBus['fixedDayesContract']);
            }




            let arrpos=[];
            (res['positions']).map(pos=>{
                // console.log(sch)
                arrpos.push(pos["@id"]);

            });

            $('#posList').val(arrpos).trigger('change');

            let arrsch=[];
            (res['userHasSchedule']).map(sch=>{
                // console.log(sch)
                arrsch.push(sch["@id"]);

            });

            $('#schList').val(arrsch).trigger('change');

            //check boxes

            if(userBus['calculateOT'] == true){
                $('#exempt').prop("checked", true);
            }

            if(userBus['hideInScheduler'] == true){
                $('#hide-scheduler').prop("checked", true);
            }

            if(userBus['editTimeSheet'] == true){
                $('#allow').prop("checked", true);
            }

            if(res['timezone'] !== ''){
                $('#useTimezone').prop("checked", true);
                $('#seltimezone').removeAttr('disabled');
                $('#seltimezone').val(res['timezone']).trigger("change");
            }

            $('#add-employee').hide();
            $('#edit-employee').show();
            $('#email').prop('disabled',true);


        },
        error:(e)=>{
            // console.log(e)
            //expire jwt token
            if(e.status == 401){
                window.location.href = base_url2+"/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }


    });

}

async function getData (url) {
    let response = await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': "application/json",
            'Authorization': `Bearer ${tok}`
        },
    });
    let Schedule = await response.json();
    return Schedule;
};
getData(base_url + '/api/shifts/count_publish_unpublish')
    .then(data => {
        console.log(data, 'publish and unpublish')

        if (data['unPunlished'] == '0') {
            document.getElementById('publishAndNotifyColor').classList.add('card-custyel');
        }else {
            document.getElementById('unpublishChanges').style.display = 'block'
            document.getElementById('publishAndNotifyColor').classList.add('card-danger');
            document.getElementById('publishAndNotifyColor').classList.remove('card-custyel');
            document.getElementById('unpublishChanges').innerHTML = `${data['unPunlished']} Unpublish Changes`
        }
    })
    .catch(err => {
        console.log(err)
    })






