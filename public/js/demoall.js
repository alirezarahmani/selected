$(document).ready(function () {

    //check token if not set redirect to login page
    let tok = localStorage.getItem('token');
    let personRole = localStorage.getItem('role');
    let billings = JSON.parse(localStorage.getItem('billing'));
    let sideMenu_left = document.getElementById('sideMenu_left');
    let activeTab = localStorage.getItem('activeTab');


    if (tok == null) {
        window.location.href = base_url2 + "/login";
    }
    // active Tabe menu
    $('ul li a').click(function(e) {
        if (e.target.getAttribute('href')) {
            localStorage.setItem('activeTab', e.target.getAttribute('href'));
        }else if (e.target.parentElement.getAttribute('href')) {
            localStorage.setItem('activeTab', e.target.parentElement.getAttribute('href'));
        }
    });

    if (sideMenu_left) {
        if (activeTab !== '#') {
            $('ul li a[href="' + activeTab + '"]').css("color", "white");
            $('ul li a[href="' + activeTab + '"]').css("background-color", "#007bff");
        }
    }

    if(activeTab){
        if (activeTab !== '#'){
            $('ul li a[href="' + activeTab + '"]').tab('show');
        }
    }
    // active Tabe menu

    let showMySchedule = document.getElementById('showMySchedule');
    let showMySchedule_ = document.getElementById('showMySchedule_2');
    let linkId_MySchedule = document.getElementById('scheduleModal');

    let showMyAvailablity = document.getElementById('showMyAvailablity');
    let showMyAvailablity_ = document.getElementById('showMyAvailablity_2');
    let linkId_MyAvailablity = document.getElementById('availabilityModule');

    let showTimeSheets = document.getElementById('useTimesheets');
    let showTimeSheets_ = document.getElementById('timesheetsOn');
    let linkId_TimeSheets = document.getElementById('timesheetsModal');

    let showAdminTerminal = document.getElementById('adminTerminal');
    let showAdminTerminal_ = document.getElementById('adminTerminal_');
    let linkId_AdminTerminal = document.getElementById('adminTerminalLink');

    let showUseSchedule = document.querySelector('.useScheduler');
    let showUseSchedule_ = document.querySelector('.useScheduler_2');
    let linkId_Schedule = document.getElementById('useSchedulModal');

    let useDashboard = document.querySelector('.useDashboard');
    let useDashboard_ = document.querySelector('.useDashboard_2');
    let linkId_Dashboard = document.getElementById('useDashboardModal');

    let role_account = "account";
    let plan_attendance = 'useAttendance';
    let plan_availablity = 'useAvailability';
    let plan_schdule = 'useScheduler';
    let role_employee = "employee";
    let role_manager = "manager";

    let showJobsite = document.getElementById('showJobsite');
    let showJobsite_ = document.getElementById('showJobsite_2');
    let linkId_jobsite = document.getElementById('jobsiteModal');

    let showMyScheduleAccount = document.getElementById('showMyScheduleAccount');
    let showMyScheduleAccount_ = document.getElementById('showMySchedule_2Account');
    let linkId_showMyScheduleAccount = document.getElementById('scheduleModalAccount');
    let borderTop = document.querySelector('.showMyScheduleAccount');
    if(borderTop){
        if (personRole === role_account || personRole === role_manager) {
            borderTop.style.display = 'block';
        }
    }

    if (billings[`${plan_availablity}`] === false && personRole === role_employee) {
        document.getElementById('myAvailablityBorder').style.display = 'none';
    }


    function UseBilling_Plan(billing, liId, liId_, linkId, role, role_2,oneBoolean, twoBoolean) {
        if (billings[`${billing}`] === oneBoolean) {
            if (personRole === `${role}`) {
                liId.addEventListener('click', e => {
                    e.preventDefault();
                    linkId.setAttribute('data-toggle', 'modal')
                    linkId.setAttribute('data-target', '#modalSellerPlan')
                })
            }else {
                liId.style.display = 'none';
            }
        }else if(billings[`${billing}`] === twoBoolean){
            if (personRole === `${role}` || personRole === `${role_2}`) {
                liId.style.display = 'none';
                liId_.style.display = 'block';
            }else {
                liId.style.display = 'none';
                liId_.style.display = 'none';
            }
        }
    }
    function UseBilling_Plan_Profile(billing, liId, liId_, linkId, role, oneBoolean, twoBoolean) {
        if (billings[`${billing}`] === oneBoolean) {
            if (personRole === `${role}`) {
                liId.addEventListener('click', e => {
                    e.preventDefault();
                    linkId.setAttribute('data-toggle', 'modal')
                    linkId.setAttribute('data-target', '#modalSellerPlan')
                })
            }else {
                liId.style.display = 'none';
            }
        }else if(billings[`${billing}`] === twoBoolean){
            liId.style.display = 'none';
            liId_.style.display = 'block';
        }
    }

    UseBilling_Plan( plan_schdule, showJobsite, showJobsite_, linkId_jobsite, role_account, role_manager, false, true);
    UseBilling_Plan( plan_schdule, showUseSchedule, showUseSchedule_, linkId_Schedule, role_account, role_manager, false, true);
    UseBilling_Plan_Profile( plan_schdule, useDashboard, useDashboard_, linkId_Dashboard, role_account, false, true);
    UseBilling_Plan( plan_schdule, showMySchedule, showMySchedule_, linkId_MySchedule, role_employee,role_manager, false, true);
    UseBilling_Plan( plan_schdule, showMyScheduleAccount, showMyScheduleAccount_, linkId_showMyScheduleAccount, role_account,role_manager, false, true);

    UseBilling_Plan( plan_attendance, showTimeSheets, showTimeSheets_, linkId_TimeSheets, role_account,role_manager, false, true);
    UseBilling_Plan( plan_attendance, showAdminTerminal, showAdminTerminal_, linkId_AdminTerminal, role_account,role_manager, false, true);

    UseBilling_Plan_Profile( plan_availablity, showMyAvailablity, showMyAvailablity_, linkId_MyAvailablity, role_account, false, true);

    if (billings['useAttendance'] === true) {
        if (personRole === role_employee || personRole === role_manager) {
            document.getElementById('loginTerminal').style.display = 'block';
        }else {
            document.getElementById('loginTerminal').style.display = 'none';
        }
    }

    if (billings['useScheduler'] === true) {
        if (personRole === "account" || personRole === role_manager) {
            document.getElementById('shiftRequest').style.display = 'block';
            document.getElementById('shiftRequest_border').style.display = 'block';
        }else {
            document.getElementById('shiftRequest').style.display = 'none';
            document.getElementById('shiftRequest_border').style.display = 'none';
        }
    }


    // این بخش رو درست کن
    if(personRole == 'employee'){
       $('.c-content-emp').show();
       $('.c-content-all').hide();

    }else{
        $('.c-content-emp').hide();
        $('.c-content-all').show();
        if(personRole === 'manager'){
         $('.c-content-setting').show();
        }else{
            $('.c-content-setting').hide();
        }
// ---> bahar
        // if(personRole === 'manager' || personRole === 'account'){
        //     $('.c-content-setting').show();
        //     $('.accountHolder').show();
        // }else{
        //     $('.c-content-setting').hide();
        //     $('.accountHolder').hide();
        // }
// ---> bahar
// ---> saeed
        if(personRole === 'account'){
            $('.accountHolder').show();
        }else{
            $('.accountHolder').hide();
        }
// ---> saeed
        if (personRole === role_manager || personRole === role_account) {
            $('.c-content-setting').show();
        }else {
            $('.c-content-setting').hide();
        }

    }

    let fullname = localStorage.getItem('fullname');
    $('.employeeFullname').text(fullname);
    if (localStorage.getItem('userImg')){
        let userImg = localStorage.getItem('userImg');
        $("#userImgAccounts").attr("src", userImg);
    }else {
        $("#userImgAccounts").attr("src", 'img/workplaceImg.png');
        localStorage.setItem('userImg', 'img/workplaceImg.png');
    }

    //Business Setting
    let seeCoworkers=localStorage.getItem('seeCoworkerSchedule');

    if(seeCoworkers === 'true'){

       $('div.seeCoworkers').show();
    }else {
        $('div.seeCoworkers').hide();
    }


    //start script for time-request modal
    $('#start-time-reqoff').datetimepicker({
        format: 'LT'
    });

    $('#end-time-reqoff').datetimepicker({
        format: 'LT'
    });

    $('#start-date-reqoff , #end-date-reqoff').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1901,
        maxYear: parseInt(moment().format('YYYY'),10),
        locale: {
            format: 'DD/MM/YYYY'
        }
    });

    $('#all-day').click(function() {
        if ($(this).prop("checked") == true) {
            $('.date-range').show();
            $('.time-range').hide();


            if ($("#type-off option:selected").val() == 'unpaid') {
                $('.paid').hide();

            }else{
                $('.paid').show();
            }

        }
        else if ($(this).prop("checked") == false) {
            $('.date-range').hide();
            $('.time-range').show();
            $('.paid').hide();
        }
    });

    $('#type-off-all').on('change',function () {
        if ($('#all-day').prop("checked") == true){
            if ($("#type-off-all option:selected").val() == 'unpaid') {
                $('.paid').hide();

            }else{
                $('.paid').show();
            }
        }
    });

    $('.select2').select2();
    $('.select-emp').select2();
    $('#positionList').select2();
    $('#schedule-list').select2({
        minimumInputLength: 89999
    });

    //empty time off modal
    $('#modal-reqoff').on('hidden.bs.modal', function () {
        $(this)
            .find("input[type=text],textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();
        $('.select2').val([]).trigger('change');
        $('textarea#message').val('');

    });

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
                $('select.select-emp-demoall').append('<option value='+user['@id']+'>'+user['firstName']+' '+user['lastName']+'</option>')

            });

        }
    });

    //get type of request list
    $.ajax({
        method:'GET',
        url:encodeURI(base_url+'/api/time_off_requests/types'),
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:function (res) {
           // console.log(res);

            let typeArray = [];
            res.map((r) => {

                typeArray.push('<option value=' + r + '>' + r + '</option>')

            });
            $('select.type-off').html(typeArray);

        }
    });

    //add time off request from modal
    $('.sendreq').on('click',function () {
        let start,end;



        if($('#all-day').prop("checked") == true){
            let startDate=$('#start-date-reqoff').val();
            let _boxdate = moment((startDate).substring(0, 10), 'DD-MM-YYYY');
            let _dateFormat = _boxdate.format('DD-MM-YYYY')
            let $startTime = (startDate).substring(0, 10) == '' ? '' : _dateFormat;
            start= $startTime.concat(" 00:00:00");

            let endDate=$('#end-date-reqoff').val();
            let boxdate = moment((endDate).substring(0, 10), 'DD-MM-YYYY');
            let dateFormat = boxdate.format('DD-MM-YYYY')
            let $endTime = (endDate).substring(0, 10) == '' ? '' : dateFormat;
            end= $endTime.concat(" 23:59:59");
        }else{
            let startDate=$('#start-date-reqoff').val();
            let startTime=$('#start-time-reqoff').val();
            let endTime=$('#start-time-reqoff').val();
             start= moment(startDate).format('DD-MM-YYYY').concat(' '+$('#timestart-timeoff').val());
             end= moment(endDate).format('DD-MM-YYYY').concat(' '+$('#timeend-timeoff').val());

        }
        let paid=$('#paid-hour').val()===''?0:$('#paid-hour').val()

        let data={
            userID:$('.select-emp-demoall').val(),
            type:$('#type-off-all').val(),
            paidHour:paid.toString(),
            message:$('#message').val(),
            startTime: start,
            endTime: end,

        };
        console.log('data',data)
        $.ajax({
            url: base_url + '/api/time_off_requests',
            method: 'POST',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify(data),
            success: function(user){
                console.log('inserted time off',user)
                if($('#timeoffreq_wrapper').length>0){
                    console.log('true')
                    toastr.success('Time Off Successfully Added.');
                    let logs=user["timeoffLogs"];
                    let by =logs[(logs.length)-1];
                    let color="GoldenRod ";
                    let sta="Pending Approval";
                    if(by['status'] === 'accepted') {
                        color = "green";
                        sta='accepted';
                    }else if (by['status'] === 'denied'){
                        color="red";
                        sta= 'denied';
                    }else if (by['status']=== 'canceled'){
                        color="red";
                        sta= 'canceled';
                    }

                    let sd=new Date(by['date']);
                    let statusDate=(sd.toString()).substring(4,10);

                    let cAt=new Date(user["createdAt"]);
                    let createdAt=(cAt.toString()).substring(0,11)+','+cAt.getFullYear();
                    let createdAtTime=(cAt.toTimeString()).substring(0,5);

                    let more;
                    if(user['userID']['id'] === user['userCreatorId']['id']){
                        more='';
                    }else{
                        more=' (by '+by['creatorId']['firstName']+' '+(by['creatorId']['lastName']).charAt(0)+'.)';
                    }

                    console.log(createdAt,createdAtTime)

                    let detailDate;
                    let st=user['startTime'];
                    let et=user['endTime'];

                    if(st.substring(0,10) === et.substring(0,10)){

                        if(st.substring(11,16) === '00:00' && et.substring(11,16) === '23:59'){
                            detailDate=((new Date(user['startTime'])).toString()).substring(0,10)+','+((new Date(user['startTime'])).toString()).substring(10,15);
                        }else{
                            detailDate=((new Date(user['startTime'])).toString()).substring(0,10)+','+((new Date(user['startTime'])).toString()).substring(10,15)+' from '+ st.substring(11,16)+'-'+et.substring(11,16);

                        }
                    }else{
                        detailDate=((new Date(user['startTime'])).toString()).substring(0,10)+'-'+((new Date(user['endTime'])).toString()).substring(4,15);

                    }


                    tableListTimeOff.row.add( {
                        "Employee":'<img src="../../img/pic4.png" style="width: 20px;height: 20px;"/>' +user["userID"]["firstName"]+' '+user["userID"]["lastName"]+more,
                        "Type":user["type"]+' Time Off',
                        "Status":'<div  style="color:'+color+' ">'+sta+'</div>'+
                        '<span>by '+by['creatorId']['firstName']+' '+by['creatorId']['lastName']+' On '+statusDate+'</span>',
                        "Details":detailDate,
                        "Requested On": createdAt+'@'+createdAtTime+
                        '<button  onclick="detail_timeoffReq(\''+user['id']+'\',\''+user['userID']['id']+'\')" class="btn btn-sm"><i class="fas fa-chevron-right"></i></button>'
                    }).draw( false );

                }else{

                    console.log('false')
                    location.replace(base_url2+'/time-off-requests');
                }


            },
            error:function(e) {

                // console.log(e)
                if(e.status == 400){
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            }
        });
    });
    //end script for time-request modal

    //log out clear local storage
    $('.logouting').on('click',function () {
        localStorage.clear();
        sessionStorage.clear();
    });

});
