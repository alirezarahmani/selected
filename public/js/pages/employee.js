//check token if not set redirect to login page
let personRole=localStorage.getItem('role');
let roleEmail = localStorage.getItem('email');
let tok=localStorage.getItem('token');

if(tok == null){
    window.location.href = base_url2+"/login";
}
let table;
let roleEmployees;
$(document).ready(function () {

    table=$("#employees").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[0,4]  }
        ],
        "scrollY":        "300px",
        "scrollCollapse": true,
        "paging":   false,
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        dom: 'Bfrtip',
        buttons: [
            {
                text:      'Add Employee',
                action: function ( e, dt, node, config ) {
                    $('#modal-addemp').modal('show');

                },
                attr:  {
                    class: 'btn btn-custbl btn-sm ml-3 mt-2 mb-1'
                }
            },
            {
                extend: 'excel',
                text: 'Export',
                attr:  {
                    class: 'btn btn-sm btn-outline-secondary mt-2 mb-1'
                }
            }

        ],
        "columns": [
            { data: '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>',className:'Checkes' },
            { data: 'Employees',className:'Employees'},
            { data: 'Positions',className:'Empposition' },
            { data: 'Schedules',className:'Empschedule' },
            { data: 'Actions' }
        ]
    });

    //class for search box
    $('input[type=search]').addClass('form-control form-control-sm');

    //get Suspend Business Requests
    $.ajax({
        method:'get',
        url:base_url+'/api/business_requests',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log(res);
            let requestCurrentBusiness=res['hydra:member'];

            requestCurrentBusiness.map(request=>{
                console.log(request);
                table.rows.add( [ {

                    '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>': '<input type="checkbox" value=`${request["@id"]}` class="ml-2"/>' +
                    '                                            <label for=""></label>' ,
                    "Employees": '<div class="user-block">\n' +
                    '                        <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                    '                        <span class="username pt-2" style="font-size: 10px;">'+ request['userId']["firstName"]+' '+request['userId']["lastName"]+'</span>\n' +
                    '                      </div>',                    "Positions":   "",
                    "Schedules":     "",
                    "Actions":    '            <div class="btn-group">' +
                    '                                            <button type="button" onclick="accept_request(\''+request["id"]+'\')"  title="Accept" class="btn btn-default btn-sm accBusRequest"><i class="fas fa-check"></i></button>' +
                    '                                            <button type="button" onclick="decline_request(\''+request["id"]+'\')" title="Decline" class="btn btn-default btn-sm decBusRequest"><i class="fas fa-times"></i></button>' +
                    '\n' +
                    '                                        </div>'

                }] )
                    .draw();

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

    //get employee list in table
    $.ajax({
        method:'get',
        url:base_url+'/api/users',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log(res);
            let users=res['hydra:member'];

            users.map(user=>{
                console.log(user);
                //check privacy for phone number
                let phoneName;
                if(user['privacy'] === true){
                    phoneName= ' <span class="username" style="font-size: 10px;">'+user["firstName"]+' '+user['lastName']+'</span>\n' +
                        ' <span class="username" style="font-size: 10px;">'+user['mobile']+'</span>\n' ;
                }else{
                    phoneName= ' <span class="username" style="font-size: 10px;">'+user["firstName"]+' '+user['lastName']+'</span>\n' ;

                }
                //check roles for table List
                let actionGroup;

                if (user['userBusinessRoles'].length > 0) {

                    let findKey=Object.keys(user['userBusinessRoles'])[0];

                    if(user['userBusinessRoles'][findKey]['role'] ==='account') {

                        if (personRole === 'account' && user['email'] === localStorage.getItem('email') ) {
                            actionGroup=  '        <div class="btn-group">' +
                                '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                                '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                                '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                                '                                        </div>';
                        }else {
                            actionGroup="";
                        }


                    }else if(user['userBusinessRoles'][findKey]['role'] ==='manager'){

                        if (personRole === 'supervisor') {
                            actionGroup="";
                        }else{

                            if(user['email'] === localStorage.getItem('email')){

                                actionGroup='        <div class="btn-group">' +
                                    '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                                    '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                                    '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                                    '                                        </div>';

                            }else{
                                actionGroup='        <div class="btn-group">' +
                                    '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                                    '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                                    '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                                    '                                            <button type="button" onclick="delete_employee(\''+user["id"]+'\',\''+user['firstName']+'\',\''+user['lastName']+'\')" title="Delete" class="btn btn-default btn-sm delemo" data-toggle="modal" data-target="#modal-delemp"><i class="far fa-trash-alt"></i></button>' +
                                    '                                        </div>';
                            }


                        }



                    }else if(user['userBusinessRoles'][findKey]['role'] ==='supervisor'){
                        if (user['email'] === localStorage.getItem('email')) {

                            actionGroup='        <div class="btn-group">' +
                                '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                                '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                                '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                                '                                        </div>';
                        }else{

                            actionGroup='        <div class="btn-group">' +
                                '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                                '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                                '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                                '                                            <button type="button" onclick="delete_employee(\''+user["id"]+'\',\''+user['firstName']+'\',\''+user['lastName']+'\')" title="Delete" class="btn btn-default btn-sm delemo" data-toggle="modal" data-target="#modal-delemp"><i class="far fa-trash-alt"></i></button>' +
                                '                                        </div>';
                        }
                    }else{
                        actionGroup='        <div class="btn-group">' +
                            '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                            '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                            '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                            '                                            <button type="button" onclick="delete_employee(\''+user["id"]+'\',\''+user['firstName']+'\',\''+user['lastName']+'\')" title="Delete" class="btn btn-default btn-sm delemo" data-toggle="modal" data-target="#modal-delemp"><i class="far fa-trash-alt"></i></button>' +
                            '                                        </div>';
                    }

                    table.rows.add( [ {

                        '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>': '<input type="checkbox" value=`${request["@id"]}` class="ml-2" id="check1"/>' +
                        '                                            <label for="check1"></label>' ,
                        "Employees": '<div class="user-block">\n' +
                        '                        <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                        '                        <span class="description" >\n' +
                        '                          <span class="badge" style="background-color: #e6e7e8;">'+user['userBusinessRoles'][findKey]['role']+'</span>\n' +
                        '                        </span>'+phoneName+'\n' +
                        '                      </div>',                      "Positions":   user['positions'].map(pos=>pos['name']).join(','),
                        "Schedules":   user['userHasSchedule'].map(sch=>sch['name']).join(','),
                        "Actions":     actionGroup

                    }] )
                        .draw();

                    }

            });


        },
        Error:(e)=>{

            //expire jwt token
            if(e.status == 401){
                window.location.href = base_url2+"/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });


    if($('#editIdemp').val() == ''){
        $('#email').removeAttr("disabled");
    }

    //checkbox
    $('input[type="checkbox"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });


    $('#fixed').click(function () {

        if( $(this).is(':checked')) {

            $(".contractCondition").css('display','block');
            $('#fixed').val('fixed');

        } else {

            $(".contractCondition").css('display','none');


        }
    });

    $('#zero').click(function () {

        if( $(this).is(':checked')) {

            $(".contractCondition").css('display','block');
            $('#zero').val('zero');
            $(".contractCondition").css('display','none');

        } else {

            $(".contractCondition").css('display','block');

        }
    });

    //empty modal
    $('#modal-addemp').on('hidden.bs.modal', function (e) {
        console.log('closed modal')
        $(this)
            .find("input[type=text],textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();


        $("#vert-detail-tab").addClass("active");
        $("#vert-schedule-tab").removeClass("active");
        $("#vert-payroll-tab").removeClass("active");
        $("#vert-note-tab").removeClass("active");
        $("#vert-advance-tab").removeClass("active");

        $("#schedule-tab").removeClass("active show");
        $("#payroll-tab").removeClass("active show");
        $("#log-tab").removeClass("active show");
        $("#advance-tab").removeClass("active show");
        $("#detail-tab").addClass("active show");

        $('.contractCondition').css('display','none');
        $('#email').removeAttr("disabled");


    });

    $('#terminateDate').daterangepicker({
        singleDatePicker: true,
        startDate: moment().subtract(6, 'days'),
        locale: {
            format: 'DD/MM/YYYY'
        }
    });

    //Enable check and uncheck all functionality
    $('.checkbox-toggle').click(function () {
        let clicks = $(this).data('clicks');
        if (clicks) {
            //Uncheck all checkboxes
            $("tbody input[type='checkbox']").prop('checked', false);
            $('.checkbox-toggle .far.fa-check-square').removeClass('fa-check-square').addClass('fa-square');
            $('.addemployee').show();
            $('.bulkaction').hide();
        } else {
            //Check all checkboxes
            $("tbody input[type='checkbox']").prop('checked', true);
            $('.checkbox-toggle .far.fa-square').removeClass('fa-square').addClass('fa-check-square');
            $('.addemployee').hide();
            $('.bulkaction').show();
        }
        $(this).data('clicks', !clicks)
    });

    //modal navs for position per hourly rate
    /*  let txt;
      $('.nav .nav-link#vert-payroll-tab').on('click',function(){
          var posarray=$('#posList').val();
          console.log('value multi select',posarray);
          if( posarray !== null){
              $.each(posarray,function(ele){
                  txt= $('.pos-list option[value="'+ele+'"]').text();
                  console.log(txt)
                  $('.append-pos').append(`<div class="input-group"><span class="input-group-append"><button type="button" id="readonly-btn">${txt}</button></span><input type="text" class="form-control col-3 val-pos"/></div>`);
              });
          }

      });
       $('#base-rate').on('keyup',function () {
          $('.val-pos').val($('#base-rate').val());
      });
      */


    //checkbox for use custom timezone
    $('.useTimezone').on('click',function () {
        if($('#useTimezone').is(':checked' )){
            $('.use-timezone').prop('disabled',false);
        }else{
            $('.use-timezone').prop('disabled',true);
        }

    });

    //appending top buttons of table list
    $('#employees_wrapper .dt-buttons').prepend(' <div class="bulkaction m-2 ml-3" style="display: none;">\n' +
        '                                <div class="dropdown d-inline-block">\n' +
        '                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\n' +
        '                                        Assign Positions\n' +
        '                                    </button>\n' +
        '                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">\n' +
        '                                        <a class="dropdown-item" href="#">Action</a>\n' +
        '                                        <a class="dropdown-item" href="#">Another action</a>\n' +
        '                                        <a class="dropdown-item" href="#">Something else here</a>\n' +
        '                                    </div>\n' +
        '                                </div>\n' +
        '                                <div class="dropdown d-inline-block">\n' +
        '                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\n' +
        '                                        Assign Schedules\n' +
        '                                    </button>\n' +
        '                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">\n' +
        '                                        <a class="dropdown-item" href="#">Action</a>\n' +
        '                                        <a class="dropdown-item" href="#">Another action</a>\n' +
        '                                        <a class="dropdown-item" href="#">Something else here</a>\n' +
        '                                    </div>\n' +
        '                                </div>\n' +
        '                                <button type="button" class="btn btn-outline-secondary btn-sm">Delete</button>\n' +
        '\n' +
        '                            </div></div>');


    $('#employees_filter').append(' <div class="dropdown d-inline-block ml-2">\n' +
        '                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\n' +
        '                                    </button>\n' +
        '                                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" aria-labelledby="dropdownMenuButton">\n' +
        '                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal-impEmp">Import Employees</a>\n' +
        '                                          <div class="dropdown-divider"></div>\n'+
        '                                        <a class="dropdown-item" href="#">Print Employee Handout</a>\n' +
        '                                          <div class="dropdown-divider"></div>\n'+
        '                                        <a class="dropdown-item" href="#">Invite Unregistered Employees</a>\n' +
        '                                    </div>\n' +
        '                                </div>\n' +
        '\n' +
        '                            </div>');


    //check role and permissions
    let role = localStorage.getItem('role');
    if(role == 'supervisor'){
        $('.blockHideSupvsr').hide();

    }

    //onclick table add class selected
    $('#employees tbody').on( 'click', 'tr', function () {
        $('#employees tbody tr').removeClass('selected');
        $(this).addClass('selected');
    } );

    //empty modal
    $('#modal-addemp').on('hidden.bs.modal', function (e) {
        $(this)
            .find("input,textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();

        $(".select2").val([]).trigger("change");
        $('.contractCondition').css('display','none');
        $('#edit-employee').hide();
        $('#add-employee').show();

    });

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

    //timezone list in modal
    $.ajax({
        url: base_url+'/api/get_timezone',
        method: 'GET',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,

        },
        success: function(r){

            r.forEach(function (el) {
                // console.log(el)
                $('select.seltimezone').append("<option value="+el+">"+el+"</option>");

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

    //in the modal add Employee
    $('#add-employee').on('click',function () {


        let countday;
        if($('#countDays').val() == ""){
            countday=0;
        }else{
            countday=$('#countDays').val();
        }
        $.ajax({
            url: base_url+'/api/users',
            method: 'POST',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
                'business_id':1
            },
            data: JSON.stringify({
                email:$('#email').val(),
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
                    role: $('#roleList').val(),
                    baseHourlyRate:$('#base-rate').val(),
                    maxHoursWeek: $('#max-hour').val(),
                    calculateOT: $('#exempt').prop("checked"),
                    payrollOT: $('#overtimeRate').val(),
                    editTimeSheet: $('#allow').prop('checked'),
                    hideInScheduler: $('#hide-scheduler').prop("checked"),
                    terminalId:$('#employeeID').val(),
                    contract: $("input[name='contract']:checked").val(),
                    fixedDayesContract:parseInt(countday)
                }]



            }),
            success: function(user){

                console.log('added user',user);
                toastr.success('Employee Successfully Added.');
                let findKey=Object.keys(user['userBusinessRoles'])[0];
                if(user['userBusinessRoles'][findKey]['role'] !=='account') {
                    if (user['email'] === localStorage.getItem('email')) {

                        table.rows.add( [ {

                            '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>': '<input type="checkbox" value=`${request["@id"]}` class="ml-2" id="check1"/>' +
                            '                                            <label for="check1"></label>' ,
                            "Employees": '<div class="user-block">\n' +
                            '                        <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                            '                        <span class="description" >\n' +
                            '                          <span class="badge" style="background-color: #e6e7e8;">'+user['userBusinessRoles'][findKey]['role']+'</span>\n' +
                            '                        </span>\n' +
                            '                        <span class="username" style="font-size: 10px;">'+user["firstName"]+' '+user['lastName']+'</span>\n' +
                            '                      </div>',
                            "Positions":   user['positions'].map(pos=>pos['name']).join(','),
                            "Schedules":   user['userHasSchedule'].map(sch=>sch['name']).join(','),
                            "Actions":     '  <div class="btn-group">\n' +
                            '                 <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                            '                 <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' + '  ' +
                            '                 <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                            '                                        </div>'

                        }] )
                            .draw();
                    } else {

                        table.rows.add( [ {

                            '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>': '<input type="checkbox" value=`${request["@id"]}` class="ml-2" id="check1"/>' +
                            '                                            <label for="check1"></label>' ,
                            "Employees": '<div class="user-block">\n' +
                            '                        <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                            '                        <span class="description" >\n' +
                            '                          <span class="badge" style="background-color: #e6e7e8;">'+user['userBusinessRoles'][findKey]['role']+'</span>\n' +
                            '                        </span>\n' +
                            '                        <span class="username" style="font-size: 10px;">'+user["firstName"]+' '+user['lastName']+'</span>\n' +
                            '                      </div>',
                            "Positions":   user['positions'].map(pos=>pos['name']).join(','),
                            "Schedules":   user['userHasSchedule'].map(sch=>sch['name']).join(','),
                            "Actions":     '        <div class="btn-group">' +
                            '                                            <button type="button" data-id="' + user["id"] + '" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                            '                                            <button type="button" data-id="' + user["id"] + '" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                            '                                            <button type="button" onclick="edit_employee(\''+user["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                            '                                            <button type="button" onclick="delete_employee(\''+user["id"]+'\',\''+user['firstName']+'\',\''+user['lastName']+'\')" title="Delete" class="btn btn-default btn-sm delemo" data-toggle="modal" data-target="#modal-delemp"><i class="far fa-trash-alt"></i></button>' +
                            '                                        </div>'

                        }] )
                            .draw();
                    }
                }else{
                    table.rows.add( [ {

                        '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>': '<input type="checkbox" value=`${request["@id"]}` class="ml-2" id="check1"/>' +
                        '                                            <label for="check1"></label>' ,
                        "Employees": '<div class="user-block">\n' +
                        '                        <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                        '                        <span class="description" >\n' +
                        '                          <span class="badge" style="background-color: #e6e7e8;">'+user['userBusinessRoles'][findKey]['role']+'</span>\n' +
                        '                        </span>\n' +
                        '                        <span class="username" style="font-size: 10px;">'+user["firstName"]+' '+user['lastName']+'</span>\n' +
                        '                      </div>',
                        "Positions":   user['positions'].map(pos=>pos['name']).join(','),
                        "Schedules":   user['userHasSchedule'].map(sch=>sch['name']).join(','),
                        "Actions":     ""

                    }] )
                        .draw();


                }

            },
            error:function(e) {

                console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });


    });

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

                toastr.success('employee successfully Updated.');
                let findKey=Object.keys(res['userBusinessRoles'])[0];
                table.cell('.selected .Checkes').data('<input type="checkbox" value=`${request["@id"]}` class="ml-2"/>\n' +
                    '                           <label for=""></label>');
                table.cell('.selected .Employees').data('<div class="user-block">\n' +
                    '                                              <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                    '                                              <span class="description" >\n' +
                    '                                                 <span class="badge" style="background-color: #e6e7e8;">'+res['userBusinessRoles'][findKey]['role']+'</span>\n' +
                    '                                              </span>\n' +
                    '                                                <span class="username" style="font-size: 10px;">'+res["firstName"]+' '+res['lastName']+'</span>\n' +
                    '                                             </div>');
                table.cell('.selected .Empposition').data(Object.keys(res['positions']).map(i=>res['positions'][i]['name']).join(','));
                table.cell('.selected .Empschedule').data(Object.keys(res['userHasSchedule']).map(j=>res['userHasSchedule'][j]['name']).join(','));
                table.draw();

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

    //final delete employee
    $('#delete-employee').click(function () {

        let idemp=$('#emp-id').val();

        $.ajax({
            url:base_url+'/api/business/fire_employee',
            method:'POST',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                id_employee: idemp
            }),
            success:(res)=>{
                // console.log(res)
                toastr.success('Employee Successfully Removed.');
                table.rows('.selected').remove().draw();
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


});


let accept_request=(id)=>{

    $.ajax({
        url: base_url+'/api/business_requests/'+id,
        method: 'PUT',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        data:JSON.stringify({
            status:"accepted"
        }),
        success: function(user){
            console.log('accepted',user)
            toastr.success('Employee Successfully Added.');
            table.rows('.selected').remove().draw();

            table.rows.add( [ {

                '<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>': '<input type="checkbox" value=`${request["@id"]}` class="ml-2" id="check1"/>' +
                '                                            <label for="check1"></label>' ,
                "Employees": '<div class="user-block">\n' +
                '                        <img class="img-circle img-bordered-sm" src="../../img/pic4.png" >\n' +
                '                        <span class="username pt-2" style="font-size: 10px;">'+user['userId']["firstName"]+' '+user['userId']['lastName']+'</span>\n' +
                '                      </div>',
                "Positions":   'None',
                "Schedules":   'None',
                "Actions":     '        <div class="btn-group">' +
                '                                            <button type="button" onclick="gotoScheduler(\''+user['id']+'\')" title="Schedule" class="btn btn-default btn-sm gotoSch"><i class="far fa-clock"></i></button>' +
                '                                            <button type="button" onclick="gotoAvail(\''+user['id']+'\')" title="Availability" class="btn btn-default btn-sm gotoAvail"><i class="far fa-calendar-alt"></i></button>' +
                '                                            <button type="button" onclick="edit_employee(\''+user['userId']["id"]+'\')" title="Edit" class="btn btn-default btn-sm editemp" data-toggle="modal" data-target="#modal-addemp"><i class="fas fa-pencil-alt"></i></button>' +
                '                                            <button type="button" onclick="delete_employee(\''+user['userId']["id"]+'\',\''+user['userId']['firstName']+'\',\''+user['userId']['lastName']+'\')" title="Delete" class="btn btn-default btn-sm delemo" data-toggle="modal" data-target="#modal-delemp"><i class="far fa-trash-alt"></i></button>' +
                '                                        </div>'

            }] )
                .draw();


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
};

let decline_request=(id)=>{


    $.ajax({
        url: base_url+'/api/business_requests/'+id,
        method: 'PUT',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        data:JSON.stringify({
            status:"denied"
        }),
        success: function(r){
            // console.log('denied',r)
            toastr.error('Employee Successfully Declined.');
            table.rows('.selected').remove().draw();

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
};

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

let delete_employee= (id,fname,lname) =>{
    //schedule id for delete schedule

    $('#emp-id').val(id);
    $('.fullname').text(fname+' '+lname);

    let idForCount='/api/users/'+id;
    console.log('here',idForCount);

    $.ajax({
        url: base_url+'/api/users/shift_count',
        method: 'POST',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        data:JSON.stringify({
            user:idForCount
        }),
        success: function(r){
            console.log(r)
            $('span.upcoming-shifts').text(r['count']);

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
};

let gotoAvail=(id)=>{
    //goto availability page

    let params = { 'userAvail': id };
    let new_url = base_url2+"/availability?" + jQuery.param(params);
    window.location.href = new_url;
};

let gotoScheduler=(id)=>{
    //goto scheduler page
    let schId=$(this).data('id');
    //localStorage.setItem('userAvail', availId);
    window.location.href = base_url2+"/scheduler";
};





