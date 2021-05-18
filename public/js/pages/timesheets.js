
let billings = JSON.parse(localStorage.getItem('billing'));
let userInfo = JSON.parse(localStorage.getItem('userInfo'));
let personRole = localStorage.getItem('role');
let roleEmail = localStorage.getItem('email');
let steffList = document.querySelector('.c-staff-list');
let scheduleBox = document.getElementById('boxAllSchedule')
let tok = localStorage.getItem('token');

if (tok == null) {
    window.location.href = base_url2 + "/login";
}
console.log(billings);
if (billings['useAttendance'] === false || personRole === "employee") {
    window.location.href = "404.html"
}else {

    if (billings['useScheduler'] === true && billings['useAttendance'] === true) {
        document.getElementById('close-period').style.display = 'block';
    }else {
        document.getElementById('close-period_modal').style.display = 'block';
    }

    let empID;
    let table;
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000
    });
    localStorage.setItem('users-list',JSON.stringify([]));

    businessID = userInfo.userBusinessRoles['0']['business'];

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
    getData(base_url+'/api/currencies')
    .then(data => {
        let options = data['hydra:member'];
        let symbolLaborHeader = document.querySelector('.symbolLaborHeader');
        let symbolLaborFooter = document.querySelector('.symbolLaborFooter');
        options.forEach(opt => {
            opt.businesses.forEach(Id => {
                if (Id === businessID) {
                    symbolLaborHeader.innerHTML = `${opt['symbol']} LABOR`
                    symbolLaborFooter.innerHTML = `${opt['symbol']} LABOR`
                }
            })
        })
    
    })
    .catch(err => console.log(err))
    
    let loadShiftAttModal=(date)=>{
        // console.log('loadShiftAttModal');
        let start=moment(date).format('YYYY-MM-DD 00:00');
        let end=moment(date).format('YYYY-MM-DD 23:59');
    
        $.ajax({
            method: 'get',
            url: base_url2 + '/api/shifts?ownerId='+ window.selected['id']+'&startTime=' + start + '&endTime=' + end,
            headers: {'Authorization': `Bearer ${tok}`},
            success:(res)=>{
                $('.loader-modal').addClass('d-none');
                let shifts = res['hydra:member'];
                let opt_arr= shifts.map(obj=>{
                    return ' <option value="'+obj['@id']+'"><span style="background: '+obj['color']+'">'+
                        moment(obj['startTime']).format(' H:mm') + '-' + moment(obj['endTime']).format('H:mm') + ' </span></option>'})
                    .join(' ');
                $('#shiftAt').html('<option value="null">no shift</option> ');
                $('#shiftAt').html(opt_arr);
    
            }
        })
    };
    $(document).ready(function() {
        window.selected='test';
        window.edit=true;
    
        if (localStorage.getItem('role') === 'account') {
            $('#add-period').show();
            $('#update-period').show();
        }
        //free-shift checkbox in modal
        $('#free-shift').on('change',function () {
            if (this.checked){
                $('#shiftAt').prop('disabled', true);
    
                $('#shiftAt').prop('disabled', false);
            }
        });
        //init datepicker in timesheet modal~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $('#clock-in').datetimepicker({
            format: 'HH:mm'
    
        });
    
        $('#clock-in-add').datetimepicker({
            format: 'HH:mm'
        });
        $('#clock-out').datetimepicker({
            format: 'HH:mm',
        });
    
        $('#clock-out-add').datetimepicker({
            format: 'HH:mm',
    
        });
        //back-btn attendance~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $('#back-result').on('click',(e)=>{
            let closed_date = $(e.target).find(':selected').data('closed');
            load_table(true);
            let idPeriod =  $("#timesheets-period option:selected").val();
            load_staff_period_result(idPeriod)
        });
        //data-table~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        let load_child_row=(rows)=>{
            return '<table  cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; background: blue">'+rows+'</table>';


        };
        table = $('#timesheets').DataTable({
            "data":[],
            "lengthMenu": [[-1], [ "All"]],
            "scrollX": true,
            "sScrollXInner": "100%",
            "ordering": false,
            "columns": [
                { name: 'STAFF' ,"orderable":false,"className":"text-nowrap"},
                { name: 'DAY' ,"width":"15%","orderable":false,"className":"text-nowrap"},
                { name: 'IN' ,"width":"15%","orderable":false,"className":"text-nowrap"},
                { name: 'OUT' ,"width":"15%","orderable":false,"className":"text-nowrap"},
                { name: 'HOLIDAY' ,"orderable":false,"className":"text-nowrap"},
                { name: 'SICK' ,"orderable":false,"className":"text-nowrap"},
                { name: 'SHIFT' ,"orderable":false,"className":"text-nowrap"},
                { name: 'SCHEDULED',"orderable":false,"className":"text-nowrap"},
                { name: 'ATTENDED' ,"orderable":false,"className":"text-nowrap"},
                { name: 'DETAIL' ,"orderable":false,"className":"text-nowrap"},
                { name: 'LOGS' ,"orderable":false,"className":"text-nowrap"},
                { name: 'WORKED' ,"orderable":false,"className":"text-nowrap"},
                { name: 'DIFFERENCE' ,"orderable":false,"className":"text-nowrap"},
                { name: 'TOTAL' ,"orderable":false,"className":"text-nowrap"},
                { name: 'LABOR' ,"orderable":false,"className":"text-nowrap"},
                { name: 'AUTODEDUCTED' ,"orderable":false,"className":"text-nowrap"},
            ],
            "scrollY": "320px",
            "select": {
                toggleable: false
            },
            "paging":   false,
            "info":     false,
            "searching": false,
            "dom": 'Bfrtip',
            "buttons": [
                {
                    "extend": 'print',
                    "exportOptions": {
                        "columns": ':visible',
                        "modifier": {
                            selected: null
                        }
                    },
                    "text": '',
                    "tag": 'span',
                    "className": 'fas fa-print fa-sm border rounded p-2 mb-2',
                    customize: function ( win ) {
                        $(win.document.body)
                            .css( 'font-size', '10pt' );
                        $(win.document.body).find( 'table' )
                            .addClass( 'compact' )
                            .css( 'font-size', 'inherit' );
                    }
                },
                {
                    "extend": 'excel',
                    "text": 'Excel',
                    "className": 'fa-sm border rounded mb-2 font-weight-bold',
                    "exportOptions": {
                        "columns": ':visible',
                        "modifier": {
                            selected: null
                        }
                    },

                }


            ]

        });

    
        $('#timesheets tbody').on('click','tr', function () {
            if (table.column( 0 ).visible() !== true ){//check staff period result is visible
                var tr = $(this);
                var row = table.row( tr );
                //  console.log(row);
    
                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    let date=$(tr).data('date');
                    let start=moment(date).format('YYYY-MM-DD 00:00');
    
                    let end=moment(date).format('YYYY-MM-DD 23:59');
                    //  console.log(start,end);
                    //request to load shift
                    let  $shift_array=[];
                    $.ajax({
                        method: 'get',
                        url: base_url2 + '/api/shifts?ownerId='+ window.selected['id']+'&startTime=' + start + '&endTime=' + end,
                        headers: {'Authorization': `Bearer ${tok}`},
                        success:(res)=>{
                            let shifts = res['hydra:member'];
                            shifts.map(sh=>{
                                let $s=(moment(sh['startTime'] )).format('YYYY-MM-DD H:mm');
                                let $e=(moment(sh['endTime'])).format('YYYY-MM-DD H:mm');
                                let $scheduled=sh['scheduled'];
                                let $p='no-position';
                                if (sh['positionId'] !== null) {
                                    $p=sh['positionId']['name']
                                }
                                let a= '<tr style="background: '+sh['color']+'">'+
                                    '<th>'+$s+'</th>'+
                                    '<th>'+$e+'</th>'+
                                    '<th>'+$scheduled+' min </th>'+
                                    '<th>'+$p+'</th>'+
                                    '</tr>';
                                $shift_array.push(a);
                                // Open this row
                                row.child( load_child_row($shift_array.join(' '))).show();
                                tr.addClass('shown');
    
                            });
    
                        }
                    });
    
                }
            }
    
        } );
    
        let load_table = (closed) => {
            table.clear().draw();
            // console.log(closed);
            //user cannot change anything when period is closed
            if (closed  === 'true' || closed ) {
                table.columns([
                    'STAFF:name' ,
                    'HOLIDAY:name' ,
                    'SICK:name' ,
                    'SCHEDULED:name' ,
                    'ATTENDED:name' ,
                    'TOTAL:name' ,
                    'LABOR:name' ,
                    'AUTODEDUCTED:name' ,
                ]).visible(true,true);
                table.columns([
                    'DAY:name' ,
                    'IN:name' ,
                    'OUT:name' ,
                    'SHIFT:name' ,
                    'DETAIL:name' ,
                    'LOGS:name' ,
                    'WORKED:name' ,
                    'DIFFERENCE:name' ,
                ]).visible(false,true);
            } else  {
                // console.log('this 2');
                table.columns([
                    'STAFF:name' ,
                    'HOLIDAY:name' ,
                    'SICK:name' ,
                    'SCHEDULED:name' ,
                    'ATTENDED:name' ,
                    'TOTAL:name' ,
                    'LABOR:name' ,
                    'AUTODEDUCTED:name' ,
                ]).visible(false,true);
                table.columns([
                    'DAY:name' ,
                    'IN:name' ,
                    'OUT:name' ,
                    'SHIFT:name' ,
                    'DETAIL:name' ,
                    'LOGS:name' ,
                    'WORKED:name' ,
                    'DIFFERENCE:name' ,
                ]).visible(true,true);
    
            }
    
        };
    
    
        //load scheduled staff in periods
        //md-view
        let maker_staff = (state) => {
            if (!state.id) {
                return state.text;
            }
            let $state = $('<span><img class="img-circle img-bordered-sm" src="' + assetDir + '/picsmall.png" alt="user image" width="10px"/> '
                + state["firstName"] + ' ' + state["lastName"]+'</span>');
    
    
            // console.log($state);
            return $state
    
        };
        $('#staffs-md').select2({
            templateResult: maker_staff
        });
    
        //greater than md
        let onselect_staff=function(empID,user_name_family){
            //set edit time modal header
            $('#emp-name-fam').html(user_name_family);
            let selected=$('#timesheets-period').find(':selected');
            let periodStart =$(selected).data('start');
            let periodEnd =$(selected).data('end');

            load_table(false);
            load_dates(periodStart,periodEnd);
            load_staff_clocks(periodStart,periodEnd,empID)
        };
        let load_staff_period =async (start, end) => {
            return  new Promise((resolve,fail)=>{
                $.ajax({
                    method: 'get',
                    url: base_url2 + '/api/users',
                    dataType: 'json',
                    contentType: 'application/json',
                    headers: {
                        'Authorization': `Bearer ${tok}`,
    
                    },
                    success:(resemp) => {

                        setTimeout(() => {
                            document.getElementById('setLoading').style.display = 'none';
                        }, 500);

                        console.log(resemp);
                        getData(`${base_url}/api/schedules`)
                            .then(data => {
                                console.log(data, 'all schedule')
                                let schedules = data['hydra:member']

                                schedules.forEach(scheduleName => {
                                    let scheduleOption = document.createElement('option');
                                    scheduleOption.innerHTML = scheduleName['name'];
                                    scheduleBox.appendChild(scheduleOption);
                                })

                                let scheduleOption = document.createElement('option');
                                scheduleOption.innerHTML = '+ Add Schedule';
                                scheduleBox.appendChild(scheduleOption)

                            })
                            .catch(err => {
                                console.log(err)
                            })


                            $('.c-staff-list').html('');
                        $('#staff-list .card-body ul').html('');
                        if (resemp.length == 0) {
                            $('#staff-list .card-body ul')
                                .html('<div class="p-3">In this Period , No Employee is Scheduled.</div>');
                        } else {
                            localStorage.setItem('users-list',JSON.stringify(resemp));
                            resemp.map((user,index) => {
                                if (index === 0){
                                    window.selected =user
                                }

                                scheduleBox.addEventListener('change', goToSchedulePage);
                                function goToSchedulePage() {
                                    if (scheduleBox.options[scheduleBox.selectedIndex].text == '+ Add Schedule') {
                                        window.location = `${base_url}/schedule`
                                        localStorage.setItem('activeTab', '/schedule')
                                    }
                                    user['userHasSchedule'].map((userNameSchedule) => {
                                        if (userNameSchedule['name'] === scheduleBox.options[scheduleBox.selectedIndex].text) {
                                            filterUser();
                                        }
                                    })

                                    if (scheduleBox.options[scheduleBox.selectedIndex].text === 'All Schedule') {
                                        repeatUser();
                                    }

                                }

                                function filterUser(){
                                    steffList.innerHTML = '';
                                    let li = document.createElement('li');

                                    setTimeout(()=> {
                                        li.innerHTML += `
                                            <img class="img-circle img-bordered-sm float-left my-2 ml-3" alt="user image" src="${assetDir}/picsmall.png" width="30px">
                                            <a class="user-name-family nav-link float-left px-2 mt-1" href="#" data-userId="${user["id"]}">${user["firstName"]} ${user["lastName"]}</a>
                                            <i onclick="edit_employee(${user['id']})" class="fas fa-pencil-alt float-right mr-4 mt-3 pointer" data-toggle="modal" data-target="#modal-addemp"></i>
                                        `
                                        steffList.appendChild(li);
                                    },200)
                                }
                                function repeatUser(){
                                    steffList.innerHTML = ''
                                    setTimeout(() => {
                                        if(localStorage.getItem('role')==='employee' && user["id"]=== localStorage.getItem('id')){

                                            let li = document.createElement('li');
                                            li.innerHTML += `
                                        <img class="img-circle img-bordered-sm float-left my-2 ml-3" alt="user image" src="${assetDir}/picsmall.png" width="30px">
                                        <a class="user-name-family nav-link float-left px-2 mt-1" href="#" data-userId="${user["id"]}">${user["firstName"]} ${user["lastName"]}</a>
                                        <i onclick="edit_employee(${user['id']})" class="fas fa-pencil-alt float-right mr-4 mt-3 pointer" data-toggle="modal" data-target="#modal-addemp"></i>
                                    `
                                            steffList.appendChild(li);
                                        }else if (localStorage.getItem('role')!=='employee'){

                                            let li = document.createElement('li');
                                            li.innerHTML += `
                                        <img class="img-circle img-bordered-sm float-left my-2 ml-3" alt="user image" src="${assetDir}/picsmall.png" width="30px">
                                        <a class="user-name-family nav-link float-left px-2 mt-1" href="#" data-userId="${user["id"]}">${user["firstName"]} ${user["lastName"]}</a>
                                        <i onclick="edit_employee(${user['id']})" class="fas fa-pencil-alt float-right mr-4 mt-3 pointer" data-toggle="modal" data-target="#modal-addemp"></i>
                                    `
                                            steffList.appendChild(li);
                                        }
                                    },200)
                                }

                                if (document.getElementById('all_schedule').innerHTML === 'All Schedule') {
                                    if(localStorage.getItem('role')==='employee' && user["id"]=== localStorage.getItem('id')){

                                        let li = document.createElement('li');
                                        li.innerHTML += `
                                        <img class="img-circle img-bordered-sm float-left my-2 ml-3" alt="user image" src="${assetDir}/picsmall.png" width="30px">
                                        <a class="user-name-family nav-link float-left px-2 mt-1" href="#" data-userId="${user["id"]}">${user["firstName"]} ${user["lastName"]}</a>
                                        <i onclick="edit_employee(${user['id']})" class="fas fa-pencil-alt float-right mr-4 mt-3 pointer" data-toggle="modal" data-target="#modal-addemp"></i>
                                    `
                                        steffList.appendChild(li);
                                    }else if (localStorage.getItem('role')!=='employee'){

                                        let li = document.createElement('li');
                                        li.innerHTML += `
                                        <img class="img-circle img-bordered-sm float-left my-2 ml-3" alt="user image" src="${assetDir}/picsmall.png" width="30px">
                                        <a class="user-name-family nav-link float-left px-2 mt-1" href="#" data-userId="${user["id"]}">${user["firstName"]} ${user["lastName"]}</a>
                                        <i onclick="edit_employee(${user['id']})" class="fas fa-pencil-alt float-right mr-4 mt-3 pointer" data-toggle="modal" data-target="#modal-addemp"></i>
                                    `
                                        steffList.appendChild(li);
                                    }
                                }

                            });
                        }

                        setTimeout(() => {
                            let userList = JSON.parse(localStorage.getItem('users-list'));
                            console.log(userList);
                            if (userList[0]) {
                                let empId;
                                let user_name_family;

                                empId = userList[0]['id'];
                                window.selected= userList[0]['id'];
                                user_name_family = `${userList[0]['firstName']} ${userList[0]['lastName']}`
                                setTimeout(() => {
                                    onselect_staff(empId,user_name_family);
                                }, 500);
                            }
                        }, 500);

                        //select specific employee
                        $('ul.c-staff-list li a').on('click', function () {
                            empID = $(this).data('userid');
                            console.log(empID);
                            console.log($(this));
                            JSON.parse(localStorage.getItem('users-list')).map(user=>user['id'] === empID ? window.selected=user:'');
                            let user_name_family=$(this).find('.user-name-family').html();
                            onselect_staff(empID,user_name_family)
                        });


                        // $('#c-staff-select').on('change', ()=>{
                        //     let selected_opt=$(this).find('option:selected');
                        //     let empId=$(selected_opt).data('userid');
                        //     //set selected user for window
                        //     JSON.parse(localStorage.getItem('users-list')).map(user=>user['id']=== empId ? window.selected=user:'');
                        //     let user_name_family=$(selected_opt).html();
                        //     onselect_staff(empId,user_name_family)
                        //
                        // });
                        resolve()
                    }
                })
            });
        };
    
        //load attendance modal~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $.ajax({
            method: 'get',
            url: base_url2 + '/api/schedules',
            headers: {'Authorization': `Bearer ${tok}`, },
            success:(res)=>{
                let $sch_select= $('#c-schedule-list,#c-schedule-add');
                $sch_select.html('');
                let sch_ls=res['hydra:member'];
                sch_ls.map(sch=>{
                    $sch_select.append(new Option(sch['name'],sch['@id']));
                })
            }
        });
        $.ajax({
            method: 'get',
            url: base_url2 + '/api/positions',
            headers: {'Authorization': `Bearer ${tok}`, },
            success:(res)=>{
                let $pos_select= $('#c-position-list,#c-position-add');
                $pos_select.html('');
                let pos_ls=res['hydra:member'];
                pos_ls.map(pos=>{
                    $pos_select.append(new Option(pos['name'],pos['@id']));
                })
            }
        });
    
    
        //load staff clockIn/clockOut~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        let load_staff_clocks =(start,end,staff)=>{

            $('.table-loader').toggleClass('d-none');
            // console.log(staff,start,end);
            //loader true
            $.ajax({
                method: 'get',
                url: base_url2 + '/api/attendance_times?startTime='+start+'&endTime='+end+'&user='+staff,
                headers: {'Authorization': `Bearer ${tok}`, },
                success: (resTimes) => {

                    // console.log('resTimes', resTimes);
                    let attendanceTimes=resTimes['hydra:member'];
                    let worked_totals=0;
                    let difference_totals = [];
                    attendanceTimes.map((attTime)=> {
                        $('.btn-showLocation').on('click', e => {
                            // console.log(attTime);
                            let dayObj =moment(attTime.startTime.split(" ")[0]).format('DD-M-YYYY').split("-")[0];
                            let daylocation = moment(e.target.parentElement.parentElement.getAttribute("data-date")).format('DD-M-YYYY').split("-")[0];
                            let yearObj =moment(attTime.startTime.split(" ")[0]).format('YYYY-M-DD').split("-")[0];
                            let yearlocation = moment(e.target.parentElement.parentElement.getAttribute("data-date")).format('YYYY-M-DD').split("-")[0];

                            if (dayObj == daylocation && yearObj != yearlocation) {
                                console.log(attTime);
                                inClockLat = attTime.clockInLocation != null ? attTime.clockInLocation.split(",")[0] : '';
                                inClocklang = attTime.clockInLocation != null ? attTime.clockInLocation.split(",")[1] : '';
                                outClockLat = attTime.clockOutLocation != null ? attTime.clockOutLocation.split(",")[0] : '';
                                outClocklang = attTime.clockOutLocation != null ? attTime.clockOutLocation.split(",")[1] : '';
                                console.log(inClockLat, inClocklang, outClockLat, outClocklang, attTime);

                                if (attTime.clockInLocation != null){
                                        let options = {
                                            zoom: 8,
                                            center: {
                                                lat: parseFloat(inClockLat),
                                                lng: parseFloat(inClocklang)
                                            }
                                        }

                                        let map = new google.maps.Map(
                                            document.getElementById('clockInMap'), options
                                        );


                                        let marker = new google.maps.Marker({
                                            position: {
                                                lat: parseFloat(inClockLat),
                                                lng: parseFloat(inClocklang)
                                            },
                                            map: map
                                        });

                                }else {
                                    toastr.error('Not exist clock in location');
                                    document.getElementById('clockInMap').innerHTML = '';
                                }

                                if (attTime.clockOutLocation != null){
                                    let options = {
                                        zoom: 8,
                                        center: {
                                            lat: parseFloat(outClockLat),
                                            lng: parseFloat(outClocklang)
                                        }
                                    }
                                    let map = new google.maps.Map(
                                        document.getElementById('clockOutMap'), options
                                    );


                                    let marker = new google.maps.Marker({
                                        position: {
                                            lat: parseFloat(outClockLat),
                                            lng: parseFloat(outClocklang)
                                        },
                                        map: map
                                    });

                                }else {
                                    toastr.error('Not exist clock out location')
                                    document.getElementById('clockOutMap').innerHTML = '';
                                }
                            }else {
                                document.getElementById('clockOutMap').innerHTML = '';
                                document.getElementById('clockInMap').innerHTML = '';
                            }
                        })
                        let  worked_total=attTime["worked"];
                        let temp=worked_total.split(':');
                        let $minutes=parseFloat(temp[1])/parseFloat(60);
                        worked_totals+=parseFloat(temp[0])+$minutes;
    
                        let class_tr;
    
                        if(attTime['startTime']!==null){
                            class_tr=moment(attTime['startTime']).format('YMD');
                        }else{
                            class_tr=moment(attTime['absentDate']).format('YMD');
                        }
                        // console.log(class_tr,'see class tr to load staff clock');
                        let tr_node=table.rows($('.'+class_tr)).nodes();
    
                        let cells=$(tr_node).find('td').map( (index,el)=>{
                            let cell_node=$(el);
                            let parent_items_count=parseInt($(el).parent('tr').data('items'));
                            if(cell_node.hasClass('in')){
                                let $in_last=table.cell(cell_node).data();
                                let $in_hr= attTime["startTime"].substring(11,16) == null? " " : attTime["startTime"].substring(11,16);
                                table.cell($(el))
                                    .data($in_last+'<div class="td-cell item-'+parent_items_count+'">'+$in_hr+'</div>').draw()
                            }
                            if(cell_node.hasClass('out')){
                                let $out_last=table.cell(cell_node).data();
                                if (attTime["endTime"] != null) {
                                    let $ext= attTime["endTime"].substring(11,16) == null? " " : attTime["endTime"].substring(11,16);
                                    table.cell($(el))
                                        .data($out_last+'<div class="td-cell item-'+parent_items_count+'">'+$ext+'</div>').draw();
                                }
                            }
                            // detail
                            if(cell_node.hasClass('detail')){
    
                                let sche_btn='';
                                let pos_btn='';
                                let break_btn='';
                                if (attTime['schedule'] !==null) {
                                    sche_btn =attTime['schedule']['name'];
                                }
                                if (attTime['position']!==null){
                                    pos_btn = '<div class="bg-info badge table-item-font">' + attTime['position']['name'] + '</div>';
                                }
                                if (attTime['break']!==null){
                                    break_btn = '<div class="badge-danger badge table-item-font"><i class="fa fa-clock"></i> ' + attTime['break'] + '</div>';

                                }
    
                                // console.log(pos_btn,sche_btn);
                                let $det_last=table.cell($(el)).data();
    
                                table.cell($(el)).data($det_last+
                                    '<div class="td-cell item-'+parent_items_count+'">'+
                                    sche_btn+' '+pos_btn+' '+break_btn+
                                    '</div>').draw()
    
                            }
    
                            if(cell_node.hasClass('day') && checkAdmin() && window.edit){
                                if (parent_items_count === 0){
                                    let $act_last=table.cell($(el)).data();
                                    table.cell($(el)).data($act_last+
                                        
                                        '<div class="text-right mb-1 item-'+parent_items_count+'" style="position: relative; right:25px ;top: -23px !important; margin: 0 !important;">'+
                                        '<div class="btn-group btn-group-sm">'+
                                        '<button data-att="'+attTime['id']+'" class="edit-row-at btn btn-info mr-1" style="height: 18px" data-date="'+moment(attTime['startTime']).format('Y-M-D')+'"  data-toggle="modal" data-target="#edit-times" >' +
                                        '<i class="fas fa-edit" style="position: absolute;left: 3px;top: 4px; font-size: 9px"></i>' +
                                        '</button>'+
                                        '<button data-att="'+attTime['id']+'" class="delete-row-at btn btn-danger" style="height: 18px" data-date="'+moment(attTime['startTime']).format('Y-M-D')+'">' +
                                        '<i class="fas fa-trash" style="position: absolute;left: 3px;top: 4px; font-size: 9px"></i>' +
                                        '</button>'+
                                        '</div>'+
                                        '</div>'
                                    ).draw();
                                }else{
                                    let $act_last=table.cell($(el)).data();
                                    table.cell($(el)).data($act_last+
                                        
                                        '<div class="text-right mb-1 item-'+parent_items_count+'" style="position: relative; right: 25px; top: -23px !important; margin: 0 !important;">'+
                                        '<div class="btn-group btn-group-sm">'+
                                        '<button data-att="'+attTime['id']+'" class="edit-row-at btn btn-info mr-1" style="height: 18px" data-date="'+moment(attTime['startTime']).format('Y-M-D')+'"  data-toggle="modal" data-target="#edit-times" >' +
                                        '<i class="fas fa-edit" style="position: absolute;left: 3px;top: 4px; font-size: 9px"></i>' +
                                        '</button>'+
                                        '<button data-att="'+attTime['id']+'" class="delete-row-at btn btn-danger" style="height: 18px" data-date="'+moment(attTime['startTime']).format('Y-M-D')+'">' +
                                        '<i class="fas fa-trash" style="position: absolute;left: 3px;top: 4px; font-size: 9px"></i>' +
                                        '</button>'+
                                        '</div>'+
                                        '</div>'
                                    ).draw();
                                }
    
                            }
    
                            if (cell_node.hasClass('shift')){
    
                                let shift_last=table.cell(cell_node).data();
                                if (attTime['shift'] !==null){
                                    table.cell($(el))
                                        .data(shift_last+'<div class="td-cell item-'+parent_items_count+'"><span style="background: '+attTime['shift']['color']+'">'+attTime["shift"]['@id']+'</span></div>').draw()
    
                                }
                            }
                            if(cell_node.hasClass('worked')){


                                let $worked_last=table.cell(cell_node).data();
                                table.cell($(el))
                                    .data($worked_last+'<div class="td-cell item-'+parent_items_count+'">'+attTime["worked"]+'</div>').draw()
                            }
                            console.log(attTime, 'attTime');
                            if (attTime['shift'] !== null && attTime['endTime'] !== null) {
                                let shiftStart = moment(attTime['shift']["startTime"].substring(11,16), 'HH:mm a');
                                let shiftEnd = moment(attTime['shift']["endTime"].substring(11,16), 'HH:mm a');
                                let attStart = moment(attTime["startTime"].substring(11,16), 'HH:mm a');
                                let attEnd = moment(attTime["endTime"].substring(11,16), 'HH:mm a');
                                let shiftDuration = moment.duration(shiftEnd.diff(shiftStart));
                                let attDuration = moment.duration(attEnd.diff(attStart));
                                let $hours1 = parseInt(shiftDuration.asHours());
                                let $hours2 = parseInt(attDuration.asHours());
                                // let $hours = $hours2 - $hours1;
                                let $minutes1 = parseInt(shiftDuration.asMinutes())-$hours1*60
                                let $minutes2 = parseInt( attDuration.asMinutes())-$hours2*60
                                // let $minutes = ($minutes2 - $minutes1)
                                let resultAtt = moment(`${$hours2}:${$minutes2}`, 'HH:mm');
                                let resultShift = moment(`${$hours1}:${$minutes1}`, 'HH:mm');
                                let resultDuration = moment.duration(resultAtt.diff(resultShift));
                                let resultHours = parseInt(resultDuration.asHours());
                                let resultMinutes = parseInt(resultDuration.asMinutes())-resultHours*60;
                                console.log(`${resultHours}:${resultMinutes}`);
                                let resultDifference = `${resultHours}:${resultMinutes}`;
                                difference_totals.push(resultDifference);
                                // let differenceTime = (attEnd - attStart) - (shiftEnd - shiftStart);
                                // console.log(attEnd - attStart,shiftEnd - shiftStart)
                                // console.log(differenceTime,'difference')
                                if(cell_node.hasClass('difference')){

                                    let $difference_last=table.cell(cell_node).data();
                                    table.cell($(el))
                                        .data($difference_last+'<div class="td-cell item-'+parent_items_count+'">'+resultDifference+'</div>').draw()
                                }
                            }
                        });
    
                        console.log(cells)
                    });

                    console.log(difference_totals);
                    let $minusH = [];
                    let $positiveH = [];
                    let $minusM = [];
                    let $positiveM = [];
                    let $Hours = [];
                    let $Minute = [];
                    difference_totals.forEach(diffTime => {
                        let $hours = diffTime.split(":")[0];
                        let $minute = diffTime.split(":")[1];
                        if ($hours.indexOf('-') != -1) {
                            $minusH.push(parseInt($hours))
                        }else {
                            $positiveH.push(parseInt($hours))
                        }
                        if ($minute.indexOf('-') != -1) {
                            $minusM.push(parseInt($minute))
                        }else {
                            $positiveM.push(parseInt($minute))
                        }
                    })
                    let $sumMinusH = $minusH.reduce((a,b) => {
                        return a + b;
                    },0)
                    let $sumPosH = $positiveH.reduce((a,b) => {
                        return a + b;
                    },0);
                    let $sumMinusM = $minusM.reduce((a,b) => {
                        return a + b;
                    },0)
                    let $sumPosM = $positiveM.reduce((a,b) => {
                        return a + b;
                    },0);
                    $Hours.push($sumMinusH);
                    $Hours.push($sumPosH);
                    $Minute.push($sumMinusM);
                    $Minute.push($sumPosM);
                    let $resultHours = $Hours.reduce((a, b) => {
                        return a + b
                    });
                    let $resultMinute = $Minute.reduce((a, b) => {
                        return a + b
                    })
                    console.log($minusH, $positiveH, $resultHours)
                    console.log($minusM, $positiveM, $resultMinute)
                    let $totalDifference;
                    if ($resultMinute > 60) {
                        let hoursArray = []
                        let convertMinToHouers = ($resultMinute/60).toFixed(2)
                        let split_Houers = parseInt(convertMinToHouers.split('.')[0]);
                        let convertMin = parseInt(convertMinToHouers.split('.')[1]);

                        hoursArray.push(split_Houers)
                        hoursArray.push($resultHours)

                        let fullTime = hoursArray.reduce((a, b) => {
                            return a + b
                        });
                        $totalDifference = `${fullTime}:${convertMin}`;
                        console.log($totalDifference, '$totalDifference')
                    }else {
                        $totalDifference = `${$resultHours}:${$resultMinute}`;
                    }


                    $(table.column(11).footer()).html(worked_totals.toFixed(2));
                    $(table.column(1).footer()).html('Total');
                    $(table.column(12).footer()).html($totalDifference);
                    $('.table-loader').toggleClass('d-none');
                    $('.btn-break ,.btn-warn').popover({html:true});
    
                    //set edit button functionality
                    $('.edit-row-at').on('click',function(){
                        let id=($(this).data('att'));
                        let attendance;
                        $.ajax({
                            method: 'get',
                            url: base_url2 + `/api/attendance_times/${id}`,
                            headers: {'Authorization': `Bearer ${tok}`, },
                            success:(res)=>{
                                attendance=res;
                                $('#attendance-id').val(id);
                                if (attendance['schedule']!==null){
                                    $(`#c-schedule-list option[value=${attendance['schedule']['id']}]`).prop('selected', true) ;
                                }
    
                                if (attendance['position']!==null){
                                    $(`#c-position-list option[value=${attendance['position']['id']}]`).prop('selected', true) ;
                                }
    
                                if (attendance['break']!==null){
                                    $('#user-break').val(attendance['break'])
                                }
                                attendance['startTime']!==null?$("#clock-in input").val(moment(attendance['startTime']).format('HH:mm')):'';
                                attendance['endTime']!==null? $("#clock-out input").val(moment(attendance['endTime']).format('HH:mm')):'';
    
                            }
                        })
                    });
                    //set delete button functionality
                    $('.delete-row-at').on('click',function (e) {
                        let id=$(e.target).closest('.delete-row-at').data('att');
                        $.ajax({
                            method: 'DELETE',
                            url:"/api/attendance_times/"+id,
                            headers: {'Authorization': `Bearer ${tok}`, },
                            success:(res)=>{
                                console.log(res, 'data')
                                let selected=$('#timesheets-period').find(':selected');
                                let periodStart =$(selected).data('start');
                                let periodEnd =$(selected).data('end');
                                load_table(false);
                                // let selected_opt= $('#c-staff-select').find('option:selected');
                                // let empID=$(selected_opt).data('userid');
                                load_dates(periodStart,periodEnd);
                                load_staff_clocks(periodStart,periodEnd,window.selected['id'])
                            },error: (e) => {
                                Toast.fire({title: 'From your location with this ip clock in is not possible',type:'error'});                               
                            }
                                
                        });
                    })
    
                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                }
    
            });
        };
    
        //save changes ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $('#update-attendance').on('click',()=>{
            let date_selectd=$('#timesheets tr.selected').data('date');
            let id=$('#edit-times #attendance-id').val();
            $.ajax({
                method: 'PUT',
                url: base_url2 + '/api/attendance_times/'+id,
                dataType: 'json',
                contentType: 'application/json',
                data:JSON.stringify({
                    endTime:date_selectd+' '+$('#clock-out input').val(),
                    startTime:date_selectd+' '+$('#clock-in input').val(),
                    schedule: $('#c-schedule-list option:selected').val() ,
                    position: $('#c-position-list option:selected').val() ,
                    break: $('#user-break').val() ,
                }),
                headers: {'Authorization': `Bearer ${tok}`, },
                success:(res)=>{
                    $('edit-times').modal('hide');
                    onselect_staff(window.selected['id'],window.selected['firstName']+' '+window.selected['lastName']);

                }
            })
        });
    
        //load staff period result closed~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        let load_staff_period_result=(period_id)=>{
            $.ajax({
                method: 'get',
                url: base_url2 +'/api/period_staff_results?attendancePeriod='+period_id,
                headers: {'Authorization': `Bearer ${tok}`, },
                success:(e)=>{
                    let staff_period_results=e['hydra:member'];
                    // console.log(staff_period_results,'staff period result');
                    let holiday_total=0;
                    let total_labor=0;
                    let sick_total=0;
                    let ot_total=0;
                    let tot_total=0;
                    let scheduled_total=0;
                    let autoDeduct_total=0;
                    staff_period_results.map((period_result) => {
                        holiday_total+=parseFloat(period_result["holdiay"]);
                        total_labor+=parseFloat(period_result['labor']);
                        sick_total+=parseFloat(period_result["sick"]);
                        scheduled_total+=parseFloat(period_result["totalScheduled"]);
                        ot_total+=parseFloat(period_result["ot"]);
                        tot_total+=parseFloat(period_result["total"])
                        autoDeduct_total+=period_result["autoDeducted"]!==null? parseFloat(period_result["autoDeducted"]):0;
                        let $ot_class=period_result['ot']>0?"#b1ceb1":"#bf18183d";
                        console.log($ot_class,'overtime tracker');
                        table.row.add(
                            $('<tr role="row" data-items="0">' +
                                '<td class="staff">'+period_result["user"]['firstName']+''+period_result["user"]['lastName']+'</td>' +
                                '<td class="day"></td>' +
                                '<td class="in"> </td>' +
                                '<td class="out"> </td>' +
                                '<td class="holiday">'+moment.utc().startOf('day').add({ minutes: parseInt(period_result["holiday"]) }).format('H:mm')+' </td>' +
                                '<td class="sick">'+moment.utc().startOf('day').add({ minutes: parseInt(period_result["sick"]) }).format('H:mm')+'</td>' +
                                '<td class="shift"> </td>' +
                                '<td class="scheduled">'+parseFloat(period_result["totalScheduled"]/60).toFixed(2)+' </td>' +
                                '<td class="ot" style="background:'+$ot_class+' ">'+parseFloat(period_result["ot"]/60).toFixed(2)+' </td>'+
                                '<td class="detail"> </td>' +
                                '<td class="logs"></td>' +
                                '<td class="worked">'+moment.utc().startOf('day').add({ minutes: parseInt(period_result["worked"]) }).format('H:mm')+'</td>' +
                                '<td class="defference">'+moment.utc().startOf('day').add({ minutes: parseInt(period_result["worked"]) }).format('H:mm')+'</td>' +
                                '<td class="total">'+parseFloat(period_result["total"]/60).toFixed(2)+' </td>' +
                                '<td class="labor">'+period_result["labor"]+' </td>' +
                                '<td class="autodeducted">'+moment.utc().startOf('day').add({ minutes: parseInt(period_result["autoDeducted"]) }).format('H:mm')+'</td>'+
                                // '<td class="actions text-left"> </td>' +
                                '</tr>'
                            )).draw();
                    });
                    $(table.column(4).footer()).html(moment.utc().startOf('day').add({ minutes: holiday_total.toFixed(0) }).format('H:mm')+' HOLIDAY');
                    $(table.column(5).footer()).html(moment.utc().startOf('day').add({ minutes: sick_total.toFixed(2) }).format('H:mm')+' SICK');
                    $(table.column(7).footer()).html(moment.utc().startOf('day').add({ minutes: scheduled_total.toFixed(2) }).format('H:mm')+' SCHEDULED');
                    $(table.column(8).footer()).html(moment.utc().startOf('day').add({ minutes: ot_total.toFixed(2) }).format('H:mm')+' ATTENDED');
                    $(table.column(11).footer()).html(moment.utc().startOf('day').add({ minutes: tot_total.toFixed(2) }).format('H:mm')+' TOTAL');
                    $(table.column(12).footer()).html(total_labor.toFixed(2)+' LABOR');
                    $(table.column(13).footer()).html(moment.utc().startOf('day').add({ minutes: autoDeduct_total.toFixed(2) }).format('H:mm')+' AUTO DEDUCT');
                }
            });
    
        };
        //option maker~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        let maker_option = (state) => {
            if (!state.id) {
                return state.text;
            }
            let $state = $(state.element).data('closed') ? $('<span class="c-period-option"><i class="fas fa-lock mr-1"></i>' + state.text + '</span>') :
                $('<span><i class="fas fa-lock-open mr-1"></i> ' + state.text + '</span>');
    
    
            // console.log($state);
            return $state
    
        };
        $('#timesheets-period').select2({
            templateResult: maker_option
        });
        //load periods~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        let period_loader = () => {
            $('#timesheets-period').html(null).trigger('change');//empty datepicker option
            $('#edit-periods .c-tb-period tbody').html();//empty periods table in modal
            $.ajax({
                method: 'get',
                url: base_url2 + '/api/attendance_periods',
                headers: {
                    'Authorization': `Bearer ${tok}`,
    
                },
                success: (res) => {
                    // console.log('periodPayss',res);
                    let periods = res['hydra:member'];
                    if(periods.length !== 0){
    
                        periods.map((period, $key) => {
                            //add option to select~~~~~~~~~~~~
                            //add option to select~~~~~~~~~~~~
                            let data = {};
                            data['id'] = period.id;
                            data['text'] = moment(period.startTime).format('MMM DD ') + '-' + moment(period.endTime).format('MMM DD , YYYY');
                            var start = moment(period.startTime).format('MM/DD/YYYY');
                            var end = moment(period.endTime).format('MM/DD/YYYY');
                            var newOption = new Option(data.text, data.id, false, false);
                            newOption.setAttribute('data-closed', period.closed);
                            newOption.setAttribute('data-last', 'false');
                            newOption.setAttribute('data-start', start);
                            newOption.setAttribute('data-end', end);
    
                            if (periods.length - 1 === $key){
                                newOption.setAttribute('data-last', 'true');
                                window.edit=!period.closed
                            }
    
    
                            $('#timesheets-period').append(newOption).trigger('change');
    
                            //add item to edit modal~~~~~~~~~~~
                            if (periods.length - 1 === $key) {
                                $('#edit-periods .c-tb-period tbody').prepend(
                                    '<tr>' +
                                    '<td><input disabled class="form-control startPeriod" style="width: 70%;border:none;background-color: none;" value=' + start + '></td>' +
                                    '<td><input disabled class="form-control endPeriod" style="width: 70%;border:none;background-color: none;" value=' + end + '></td>' +
                                    '<td >' +
                                    '<button class="badge bg-danger c-btn-delete-period" data-dismiss="modal" data-item=' + period.id + '>delete</button>' +
                                    '<button class="badge bg-warning c-btn-save-period" data-dismiss="modal" style="display: none;" data-item=' + period.id + '>save</button>' +
                                    ' ' +
                                    '<button class="badge bg-warning c-btn-edit-period" data-item=' + period.id + '>edit</button>' +
                                    '</td>' +
                                    '</tr>')
                            } else {
                                $('#edit-periods .c-tb-period tbody').prepend(
                                    '<tr>' +
                                    '<td class="pl-4">' + start + '</td>' +
                                    '<td class="pl-4">' + end + '</td>' +
                                    '<td>' +
                                    '' +
                                    '</td>' +
                                    '</tr>')
                            }
                            $("#timesheets-period option:last").attr("selected", "selected");
                            $('.endPeriod').daterangepicker({
                                singleDatePicker: true,
                                showDropdowns: true,
                                minYear: 1901,
                                maxYear: parseInt(moment().format('YYYY'), 10),
                                locale: {
                                    format: 'DD/MM/YYYY'
                                }
                            });
    
                            //edit last period
                            $('.c-btn-edit-period').on('click', function () {
                                $('.endPeriod').css('color', '#495057');
                                $('.endPeriod').css('background-color', '#fff');
                                $('.endPeriod').css('border', '1px solid #ced4da');
                                $('.endPeriod').removeAttr('disabled');
                                $('.c-btn-save-period').show();
                                $('.c-btn-edit-period').hide();
                            });
                            //delete last period
                            $('.c-btn-delete-period').on('click', function () {
                                let iddelitem = $(this).data('item');
    
                                $.ajax({
                                    method: 'DELETE',
                                    url: base_url + '/api/attendance_periods/' + iddelitem,
                                    contentType: "application/json",
                                    headers: {
                                        'Authorization': `Bearer ${tok}`,
                                    },
                                    success: (resDel) => {
                                        // console.log(resDel);
                                        // location.reload();
                                        period_loader();
                                        $('#modal-periods').modal('hide');
    
                                    },
                                    error: (e) => {
                                        // console.log(e)
                                        //expire jwt token
                                        if (e.status == 401) {
                                            window.location.href = base_url2 + "/login";
                                        }
                                    }
    
                                });
    
                            });
                            //update last period
                            $('.c-btn-save-period').on('click', function () {
    
                                let idedititem = $(this).data('item');
                                $.ajax({
                                    method: 'PUT',
                                    url: base_url + '/api/attendance_periods/' + idedititem,
                                    contentType: "application/json",
                                    headers: {
                                        'Authorization': `Bearer ${tok}`,
                                    },
                                    data: JSON.stringify({
                                        endTime: $('.endPeriod').data('daterangepicker').endDate.format('YYYY-MM-DD') + ' 23:59'
                                    }),
                                    success: (resEnd) => {
                                        // console.log('resEnd',resEnd)
                                        // location.reload();
                                        period_loader();
                                        $('#modal-periods').modal('hide');
    
                                    },
                                    error: (e) => {
                                        // console.log(e)
                                        //expire jwt token
                                        if (e.status == 401) {
                                            window.location.href = base_url2 + "/login";
                                        }
                                    }
    
                                });
                            });

                        });
    
                        let defaultPeriod = $('#timesheets-period').find(':selected');
    
                        load_staff_period();
                        //it should be called after  because window.selected should be filled
                        if (defaultPeriod.length>0){
                            // console.log(defaultPeriod[0].attributes[1].value,'~~~~~~~~~~~~~~~check-closed');
                            load_table(defaultPeriod[0].attributes[1].value)
                        }
                    }else{
                        Toast.fire({title:'First Select Pay Period Duration In Attendance Setting',type:'error'});
                    }
                }

            });
        };
        period_loader();
    
        //load table dates~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
        let load_dates=(periodStart,periodEnd)=>{
            //load table content
            let datesArray = [];
            let currDate = moment(periodStart).startOf('day');
            let lastDate = moment(periodEnd).startOf('day');
            datesArray.push(moment(periodStart).format('ddd,MMM D YYYY'));
            while (currDate.add(1, 'days').diff(lastDate) < 0) {
                //console.log(currDate.toDate());
                datesArray.push(moment(currDate.clone().toDate()).format('ddd,MMM D YYYY'));
            }
            // console.log(datesArray,'~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
            datesArray.push(moment(periodEnd).format('ddd,MMM D YYYY'));
            console.log(datesArray, 'datesArray');
    
            datesArray.map((days) => {
                let day=(days.toString()).substring(0,10);
                // console.log(table,(days.toString()).substring(0,10));
                let btn_grp='';
                if(checkAdmin() && window.edit){
                    btn_grp='<div class="btn-group btn-group-sm float-right ml-1" style="height:18px; margin-top: 0px">'+
                        '<a href="#" data-toggle="modal" data-target="#add-times" class="btn btn-dark add-row-at"><i class="fas fa-plus" style="margin-left:1px ;position: absolute;left: 3px;top: 3px; font-size: 9px"></i></a>'+
                        '</div>'
                }
    
                let tr_node=table.row.add(
                    $('<tr role="row" data-items="0">' +
                        '<td class="staff"> </td>' +
                        '<td class="day">'+day+' '+btn_grp+' </td>' +
    
                        '<td class="in"> </td>' +
                        '<td class="out"> </td>' +
                        '<td class="holiday"> </td>' +
                        '<td class="sick"> </td>' +
                        '<td class="shift"> </td>' +
                        '<td class="scheduled"> </td>' +
                        '<td class="Attended"> </td>' +
                        '<td class="detail"> </td>' +
                        '<td class="logs"> ' +
                        '<button class="btn btn-sm btn-primary btn-modal" data-toggle="modal" data-target="#daylog"  data-date="'+moment(days).format('Y-M-D')+'"><i class="fas fa-info mr-2"></i> show logs</button>' +
                        '<button class="btn btn-sm btn-primary ml-2 btn-showLocation" data-toggle="modal" data-target="#showLocation"><i class="fas fa-map-marker-alt mr-2"></i> location</button>' +
                        '</td>' +
                        '<td class="worked"> </td>' +
                        '<td class="difference"></td>' +
                        '<td class="total"> </td>' +
                        '<td class="labor"> </td>' +
                        '<td class="autodeducted"> </td>' +
    
                        '</tr>')).draw();
                tr_node.nodes().to$().addClass(moment(days).format('YMD'));
                tr_node.nodes().to$().attr('data-date',moment(days).format('Y-M-D'));
            });
    
            //btn show logs on click event
            $('.btn-modal').on('click',function () {
                let date_log=$(this).data('date');
                $.ajax({
                    method: 'get',
                    url: base_url2 + '/api/attendance_times_logs?time='+date_log,
                    dataType: 'json',
                    contentType: 'application/json',
                    headers: {
                        'Authorization': `Bearer ${tok}`,
                    },
                    success:(att_logs)=>{
                        let headItem = [];
                        let bodyItem = [];
                        let bodyItems = [];
                        let color
                        att_logs.forEach((log, i) => {
                            color = log[`type`] ===`warning`? `#f3f3cd`: `#d8e5e8`;
                            if (i === 0) {
                                Object.keys(log).forEach(item => headItem.push(item) );
                                Object.keys(log).forEach(item => bodyItem.push(log[item]) );                                          
                            }else {
                                Object.keys(log).forEach(item => bodyItems.push(log[item]) );
                            }
                            setTimeout(() => {
                                if (headItem[0]) {
                                    headItem = [];
                                    bodyItem = [];
                                    bodyItems = [];
                                }
                            }, 2000);
                        })
                        if (headItem[0]) {
                            setTimeout(() => {
                                console.log(headItem);
                                document.getElementById('daylog').querySelector('.modal-body').innerHTML = `
                                    <table class="table table-responsive">
                                        <tbody>
                                            <tr class="text-capitalize">
                                                <th> ${headItem[0]} </th>
                                                <th> ${headItem[1]} </th>
                                                <th> ${headItem[2]} </th>
                                                <th> ${headItem[3]} </th>
                                                <th> ${headItem[4]} </th>
                                                <th> ${headItem[5]} </th>
                                            </tr>
                                            <tr style="background: ${color}">
                                                <td class="text-capitalize"> ${bodyItem[0]} </td>
                                                <td class="text-capitalize"> ${bodyItem[1]} </td>
                                                <td class="text-capitalize"> ${bodyItem[2]} </td>
                                                <td class="text-capitalize"> ${bodyItem[3]} </td>
                                                <td class="text-capitalize"> ${bodyItem[4]} </td>
                                                <td class="text-capitalize"> ${bodyItem[5]} </td>
                                            </tr>
                                            <tr style="background: ${color}; display: ${bodyItems[0] == undefined ? 'none' : ''}" >
                                                <td class="text-capitalize"> ${bodyItems[0]} </td>
                                                <td class="text-capitalize"> ${bodyItems[1]} </td>
                                                <td class="text-capitalize"> ${bodyItems[2]} </td>
                                                <td class="text-capitalize"> ${bodyItems[3]} </td>
                                                <td class="text-capitalize"> ${bodyItems[4]} </td>
                                                <td class="text-capitalize"> ${bodyItems[5]} </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                `;
                            }, 1000);
                        }else{
                            document.getElementById('daylog').querySelector('.modal-body').innerHTML = ''
                        }
                    }
                })
            });
        };
        //change selected period~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $('#timesheets-period').on('select2:select', async(e) => {
            // console.log('selected period', e);

            let closed_date = $(e.target).find(':selected').data('closed');
            let periodStart = $(e.target).find(':selected').data('start');
            let periodEnd = $(e.target).find(':selected').data('end');
            let idPeriod = $(e.target).find(':selected').val();
            window.edit=!closed_date;
    
            // console.log(closed_date, periodStart, periodEnd);
            load_table(closed_date);//if true period staff result should be shown else clocks
            await load_staff_period();
            if (closed_date) {//this is an close date period
    
                load_staff_period_result(idPeriod)
    
            } else {//this is in open date-period
    
                load_dates(periodStart,periodEnd);//inside this table became empty
                let staff=window.selected;
                load_staff_clocks(periodStart,periodEnd,staff['id'])
            }
    
    
        });
    
        // periods modal~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //date range picker initialize in
        $('#period-picker').daterangepicker();
        //save periods
        $('#add-periodDate').on('click touch', () => {
            $.ajax({
                method: 'post',
                url: base_url2 + '/api/attendance_periods',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    startTime: $('#period-picker').data('daterangepicker').startDate.format('YYYY-MM-DD') + ' 00:00',
                    endTime: $('#period-picker').data('daterangepicker').endDate.format('YYYY-MM-DD') + ' 23:59'
                }),
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                success: (e) => {
                    // console.log(e);
                    period_loader();
                    $('#modal-periods').modal('hide');
                    // location.reload();
                },
                error: (e) => {
                    Toast.fire({title:'Your selected period has gap or has conflict,Try again!',type:'error'});
                }
            });
        });

        //export period
        $('#close-period').on('click',()=>{
            if (confirm('Are you sure close pay period ?')) {
                let id= $('#timesheets-period').find(':selected').val();
                $.ajax({
                    method: 'put',
                    url: base_url2 + '/api/attendance_periods/'+id,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        closed:true
                    }),
                    headers: {
                        'Authorization': `Bearer ${tok}`,
        
                    },
                    success: (e) => {
                        // console.log(e);
                        period_loader();
                        $('#edit-times').modal('hide');
                        load_table(true);
                        load_staff_period_result(id);
        
                        document.getElementById('export-period').style.display = 'block';
                        document.getElementById('close-period').style.display = 'none';
        
                    },
                    error: (e) => {
                        Toast.fire({title:e['responseJSON']['detail'],type:'error'});
                    }
                });
            }
     
        });
    
    
        //add-attendance-row-as-supervisor~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $('#add-times').on('show.bs.modal', function (e) {
            // console.log('click btn add row');
            let date_selectd=$('#timesheets tr.selected').data('date');
            loadShiftAttModal(date_selectd);
        });
        //show day log modal~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $('#daylog').on('shown.bs.modal',function(e){
            let selected=$('#timesheets tr.selected').data('date');
            $('.log-modal').removeClass('d-none');
            // console.log(selected,'selected date to load logs');
            //get attendance log
            $.ajax({
                method: 'GET',
                url: base_url2 + '/api/attendance_times_logs?time='+selected,
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Authorization': `Bearer ${tok}`,

                },
                success: (e) => {
                    // console.log(e);
                    $('.log-modal').addClass('d-none');
                    let $lists=e.map(log=>'<li>'+log['text']+'</li>').join(' ');
                    $('.log-ls').html($lists);
                },
                error: (e) => {
                    // console.log(e);
                    Toast.fire({title:'Your selected period has gap or has conflict,Try again!',type:'error'});
                }
            });
    
        });
        $('#add-new-attendance').on('click',(e)=>{
            let date_selectd=$('#timesheets tr.selected').data('date');
            // console.log(date_selectd);
            console.log(window.selected)
            $.ajax({
                    method: 'POST',
                    url: base_url2 + '/api/attendance_times',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        endTime:date_selectd+' '+$("#clock-out-add input").val(),
                        startTime: date_selectd+' '+$("#clock-in-add input").val(),
                        schedule: $('#c-schedule-add option:selected').val() ,
                        position: $('#c-position-add option:selected').val() ,
                        shift: $( "#shiftAt option:selected" ).val() ,
                        break: $('#user-break-add').val() ,
                        freeShift: $('#user-break-add').val() ,
                        user: window.selected['id'] == undefined ? '/api/users/'+window.selected : '/api/users/'+window.selected['id']
                    }),
                    headers: {
                        'Authorization': `Bearer ${tok}`,

                    },
                    success: (e) => {
                        // console.log(e);
                        $('#add-times').modal('hide');
                        onselect_staff(window.selected['id'],window.selected['firstName']+' '+window.selected['lastName']);
                    },
                    error: (e) => {
                        Toast.fire({title:'Your selected period has gap or has conflict,Try again!',type:'error'});
                    }
                });

        });
    });
    
    function checkAdmin() {
        return localStorage.getItem('role')==='account' ||
            localStorage.getItem('role')==='manager' ||
            localStorage.getItem('role')==='supervisor'
    }
}

// role List in modal
$.ajax({
    url: base_url+'/api/get_avail_roles',
    method: 'GET',
    contentType: "application/json",
    headers: {
        'Authorization': `Bearer ${tok}`,
    },
    success: function(r){


        r.forEach(function (el) {

            $('select.role-list').append("<option value="+el+">"+el+"</option>");

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

//position list in modal
$.ajax({
    url: base_url+'/api/positions',
    method: 'GET',
    contentType: "application/json",
    headers: {
        'Authorization': `Bearer ${tok}`,

    },
    success: function(r){

        let poses=r['hydra:member'];
        poses.forEach(function (el) {
            // console.log(el)
            let idp=el['@id'];
            let namep=el['name'];
            $('select.pos-list').append("<option value="+idp+">"+namep+"</option>");

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

//schedule list in modal
$.ajax({
    url: base_url+'/api/schedules',
    method: 'GET',
    contentType: "application/json",
    headers: {
        'Authorization': `Bearer ${tok}`,

    },
    success: function(r){

        //console.log('schedule');
        let sch=r['hydra:member'];
        sch.forEach(function (el) {
            // console.log(el)
            let ids=el['@id'];
            let names=el['name'];
            $('select.sch-list').append("<option value="+ids+">"+names+"</option>");

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

let edit_employee= (id)=>{

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
            roleEmployees = userBus['role'];
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

            if(localStorage.getItem('email') === $('#email').val() && personRole === 'account'){
                $('.roleListEmpModal').hide();
            }else{
                $('.roleListEmpModal').show();
                $('#roleList').val(userBus['role']);
            }


            if(userBus['contract'] === 'zero'){

                $('input:radio[name=contract][id=zero]').prop('checked', true);
                $('#zero').val('zero');

            }else if(userBus['contract'] === 'fixed'){
                console.log('here')

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

};

//final edit employee
$('#edit-employee').click(function () {
    let edID= $('#editIdemp').val();
    let fixedDays;
    if($("input[name='contract']:checked").val() === 'zero'){
        fixedDays=0;
    }else if($("input[name='contract']:checked").val() === 'fixed'){
        fixedDays=$('#countDays').val();
    }

    let data ={
        firstName:$('#firstname').val(),
        lastName:$('#lastname').val(),
        mobile:$('#mobile-num').val(),
        timezone:$('#seltimezone').children("option:selected").val(),
        // image:$('#picurl').val(),
        useCustomTimezone:$('#useTimezone').prop("checked"),
        note:$('#note').val(),
        userHasSchedule:$('#schList').val(),
        positions:$('#posList').val(),
        userBusinessRoles: [{
            baseHourlyRate:$('#base-rate').val(),
            maxHoursWeek: parseInt($('#max-hour').val()),
            calculateOT: $('#exempt').prop("checked"),
            payrollOT: $('#overtimeRate').val(),
            editTimeSheet: $('#allow').prop('checked'),
            hideInScheduler: $('#hide-scheduler').prop("checked"),
            // terminalId:$('#employeeID').val() != "" ? $('#employeeID').val() : null,
            terminalId: $('#employeeID').val(),
            contract: $("input[name='contract']:checked").val(),
            role: roleEmail === $('#email').val() &&  personRole === 'account' ? roleEmployees : $('#roleList').val(),
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
            console.log('resUpdate',res)

            $.ajax({
                method: 'get',
                url: base_url2 + '/api/users',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Authorization': `Bearer ${tok}`,

                },
                success:(resemp) => {
                    console.log(resemp);

                    steffList.innerHTML = '';


                    localStorage.setItem('users-list',JSON.stringify(resemp));
                    resemp.map((user,index) => {
                        setTimeout(() => {
                            let li = document.createElement('li');
                            li.innerHTML += `
                                        <img class="img-circle img-bordered-sm float-left my-2 ml-3" alt="user image" src="${assetDir}/picsmall.png" width="30px">
                                        <a class="user-name-family nav-link float-left px-2 mt-1" href="#" data-userId="${user["id"]}">${user["firstName"]} ${user["lastName"]}</a>
                                        <i onclick="edit_employee(${user['id']})" class="fas fa-pencil-alt float-right mr-4 mt-3 pointer" data-toggle="modal" data-target="#modal-addemp"></i>
                                    `
                            steffList.appendChild(li);
                        },50);


                    });

                }
            });

            toastr.success('employee successfully Updated.');


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
