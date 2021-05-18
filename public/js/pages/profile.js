let img;
$(document).ready(function () {
    //check token if not set redirect to login page
    let tok = localStorage.getItem('token');
    let billings = JSON.parse(localStorage.getItem('billing'));

    if (tok == null) {
        window.location.href = base_url2 + "/login";
    }

    if (billings['useAttendance'] === false) {
        document.getElementById('remindersClock').style.display = 'none';
        document.getElementById('alertOvertime').style.display = 'none';
    }

    //Business Setting
    let setPreferred = localStorage.getItem('setPreferred');
    if(setPreferred === true){
      $('.setPreferred').show();
    }else{
        $('.setPreferred').hide();
    }

    //check role and permissions
    let role = localStorage.getItem('role');
    if(role == 'manager'){
        $('.blockHideMnger').hide();

    }else if(role == 'supervisor'){
        $('.blockHideSupvsr').hide();

    }else if(role == 'employee'){
       $('.blockHideEmp').hide();
    }

    //checkbox
    $('#privacy').prop('checked', true);

    $('input[type="checkbox"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });

    $('#prefer').datetimepicker({
        format: 'LT',
        locale:  moment.locale('en', {
            week: { dow: 1 }
        })
    });
    $('#preferTo').datetimepicker({
        format: 'LT',
        locale:  moment.locale('en', {
            week: { dow: 1 }
        })
    });

    //get Timezones
    let tzoneUrl= base_url+'/api/get_timezone';
    $.ajax({
        url: tzoneUrl,
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

    //get current employee info & Alert Preferences
    $.ajax({
        url: base_url+'/api/users/info',
        method: 'GET',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,

        },
        success: function(r){
            console.log('info',r)

            $('#editIdInfo').val(r['id']);
            $('#firstname').val(r['firstName']);
            $('#lastname').val(r['lastName']);
            $('#emailAdd').val(r['email']);
            $('#mobile').val(r['mobile']);
            $('#preferHours').val(r['preferredHoursWeekly']);
            $('#startPrefer').val(r['sleepPreferredStart']);
            $('#endPrefer').val(r['sleepPreferredEnd']);
            $('#timezone').val(r['timezone']);
            if (r['image'] != null) {
                $("#set-profile").attr("src", r['image']['filePath']);
            }else {
                $("#set-profile").attr("src", 'img/workplaceImg.png');
            }

            if(r['privacy'] == true){
                $('#privacy').prop('checked',true);
            }else{
                $('#privacy').prop('checked',false);
            }


            let key=Object.keys(r['employeeAlerts'])[0];
            let alertsEmp=r['employeeAlerts'][key];
            console.log(r['employeeAlerts']);
            if (alertsEmp) {
                $('#editIdAlertemp').val(alertsEmp['id']);

                if(alertsEmp["timeOff"] == "email"){
                    $('input:radio.timeOff[value="email"]').prop('checked',true);
                }else  if(alertsEmp["timeOff"] == "mobile"){
                    $('input:radio.timeOff[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["swapDropShift"] == "email"){
                    $('input:radio.swapDropShift[value="email"]').prop('checked',true);
                }else  if(alertsEmp["swapDropShift"] == "mobile"){
                    $('input:radio.swapDropShift[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["scheduleUpdate"] == "email"){
                    $('input:radio.scheduleUpdate[value="email"]').prop('checked',true);
                }else  if(alertsEmp["scheduleUpdate"] == "mobile"){
                    $('input:radio.scheduleUpdate[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["newEmployee"] == "email"){
                    $('input:radio.newEmployee[value="email"]').prop('checked',true);
                }else  if(alertsEmp["newEmployee"] == "mobile"){
                    $('input:radio.newEmployee[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["availibilityChange"] == "email"){
                    $('input:radio.availibilityChange[value="email"]').prop('checked',true);
                }else  if(alertsEmp["availibilityChange"] == "mobile"){
                    $('input:radio.availibilityChange[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["overTimeAlert"] == "email"){
                    $('input:radio.overTimeAlert[value="email"]').prop('checked',true);
                }else  if(alertsEmp["overTimeAlert"] == "mobile"){
                    $('input:radio.overTimeAlert[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["payrollReminder"] == "email"){
                    $('input:radio.payrollReminder[value="email"]').prop('checked',true);
                }else  if(alertsEmp["payrollReminder"] == "mobile"){
                    $('input:radio.payrollReminder[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["hireAlert"] == "email"){
                    $('input:radio.hireAlert[value="email"]').prop('checked',true);
                }else  if(alertsEmp["hireAlert"] == "mobile"){
                    $('input:radio.hireAlert[value="mobile"]').prop('checked',true);
                }
                $('#shiftRemiderClock').val(alertsEmp['shiftRemiderClock']);
    
                if(alertsEmp["shiftReminder"] == "email"){
                    $('input:radio.shiftReminder[value="email"]').prop('checked',true);
                }else  if(alertsEmp["shiftReminder"] == "mobile"){
                    $('input:radio.shiftReminder[value="mobile"]').prop('checked',true);
                }
    
                if(alertsEmp["clockReminder"] == "email"){
                    $('input:radio.clockReminder[value="email"]').prop('checked',true);
                }else  if(alertsEmp["clockReminder"] == "mobile"){
                    $('input:radio.clockReminder[value="mobile"]').prop('checked',true);
                }
            }
            //var output = Object.entries(alerts).map(([key, value]) => ({key,value}));

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

    //show password
    $('#showPass').on('click',function () {
        var x = document.getElementById("first");
        var y = document.getElementById("second");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }

        if (y.type === "password") {
            y.type = "text";
        } else {
            y.type = "password";
        }
    });

    //last change passwprd
    $('.reset-password').on('click',function () {
        let data={
            last_password:$('#currentPass').val(),
            new_password: {
                first: $('#first').val(),
                second: $('#second').val()
            }
        };
        $.ajax({
            url: base_url + '/api/change_password',
            method: 'POST',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data: JSON.stringify(data),
            success: function(r){
                console.log(r)
                toastr.success('Password changed');
                localStorage.removeItem('token');
                location.replace(base_url2+'/login');
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
    });

    // save employee
    $('.saveInfo').on('click',function () {
        let phour=$('#preferHours').val() ===""?0:parseInt($('#preferHours').val());
        let editID =$('#editIdInfo').val();
        let data;
        let profileId = localStorage.getItem('profileId');
        localStorage.setItem('userImg', img);
        $("#userImgAccounts").attr("src", img);
        if($('#timezone').val() != ''){
            data={
                email:$('#emailAdd').val(),
                firstName: $('#firstname').val(),
                lastName: $('#lastname').val(),
                mobile: $('#mobile').val(),
                preferredHoursWeekly: phour,
                //sleepPreferredStart: $('#startPrefer').val(),
               // sleepPreferredEnd: $('#endPrefer').val(),
                privacy: $('#privacy').val(),
                timezone: $('#timezone').val(),
                useCustomTimezone:true,
                image: profileId
            };
        }else{
            data={
                email:$('#emailAdd').val(),
                firstName: $('#firstname').val(),
                lastName: $('#lastname').val(),
                mobile: $('#mobile').val(),
                preferredHoursWeekly: phour,
               // sleepPreferredStart: $('#startPrefer').val(),
               // sleepPreferredEnd: $('#endPrefer').val(),
                privacy: $('#privacy').val(),
                timezone: null ,
                useCustomTimezone:false,
                image: profileId
            };
        }
        $.ajax({
            url: base_url + '/api/users/'+ editID,
            method: 'PUT',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data: JSON.stringify(data),
            success: function(res){
               console.log('updated info',res)
                toastr.success('info employee updated');
                localStorage.setItem('fullname',res['firstName']+' '+res['lastName']);
                localStorage.setItem('email',res['email']);
                $("#set-profile").attr("src", res['image']['filePath']);
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

    let alertsModal = document.getElementById('alertsModal');
    let saveAlerts = document.querySelector('.saveAlerts');

    if (role === "account") {
        if (billings.useAttendance === true) {
            saveAlerts.style.display = 'block';
        }else {
            alertsModal.style.display = 'block';
        }
    }else {
        saveAlerts.style.display = 'block';
    }

    $('.saveAlerts').on('click',function () {
        let editID =$('#editIdAlertemp').val();
        console.log(editID);
        let data;
        if (billings.useAttendance === true) {
            data={
                availibilityChange: $('input.availibilityChange:checked').val(),
                clockReminder: $('input.clockReminder:checked').val(),
                hireAlert: $('input.hireAlert:checked').val(),
                newEmployee: $('input.newEmployee:checked').val(),
                overTimeAlert: $('input.overTimeAlert:checked').val(),
                payrollReminder: $('input.payrollReminder:checked').val(),
                scheduleUpdate: $('input.scheduleUpdate:checked').val(),
                shiftRemiderClock: parseInt($('#shiftRemiderClock').val()),
                shiftReminder: $('input.shiftReminder:checked').val(),
                swapDropShift: $('input.swapDropShift:checked').val(),
                timeOff: $('input.timeOff:checked').val()
            };
        }else {
            data={
                availibilityChange: $('input.availibilityChange:checked').val(),
                // clockReminder: $('input.clockReminder:checked').val(),
                hireAlert: $('input.hireAlert:checked').val(),
                newEmployee: $('input.newEmployee:checked').val(),
                // overTimeAlert: $('input.overTimeAlert:checked').val(),
                payrollReminder: $('input.payrollReminder:checked').val(),
                scheduleUpdate: $('input.scheduleUpdate:checked').val(),
                shiftRemiderClock: parseInt($('#shiftRemiderClock').val()),
                shiftReminder: $('input.shiftReminder:checked').val(),
                swapDropShift: $('input.swapDropShift:checked').val(),
                timeOff: $('input.timeOff:checked').val()
            };
        }

        setTimeout(() => {
            console.log(data)
            $.ajax({
            url: base_url + '/api/employee_alerts/'+ editID,
            method: 'PUT',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data: JSON.stringify(data),
            success: function(r){
                console.log(r)
                toastr.success('Employee Alerts updated');
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
        }, 200);
    });

    //close modal empty modal
    $('#modal-changePass').on('hidden.bs.modal', function(){
        $(this)
            .find("input,textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();

        $('#showPass').val(false);

        let x = document.getElementById("first");
        let y = document.getElementById("second");
        x.type = "password";
        y.type = "password";
    });

    //modal change password
    $('#showpass').on('click',function () {
        var x = document.getElementById("first");
        var y = document.getElementById("second");
        if(this.checked){
            x.type="text";
            y.type="text";
        }else{
            x.type="password";
            y.type="password";
        }
    });

    $('#first').on('change', function(){
        $('.reset-password').attr('disabled', 'disabled');
        $('#first').removeClass('is-invalid');
        $('#second').removeClass('is-valid');

        if(this.value.length < 8){ // checks the password value length

            $('#first').addClass('is-invalid');
            $(this).focus(); // focuses the current field.
            return false; // stops the execution.
        }else{

            $('#first').addClass('is-valid');

            if($('#second').hasClass('is-valid')){
                $('.signin').removeAttr('disabled');

            }
        }
    });

    $('#first, #second').on('keyup', function () {
        if ($('#first').val() == $('#second').val()) {

            $('.reset-password').removeAttr('disabled');
            $('#first').addClass('is-valid');
            $('#second').addClass('is-valid');
            $('#first').removeClass('is-invalid');
            $('#second').removeClass('is-invalid');

        } else {
            $('#first').addClass('is-invalid');
            $('#second').addClass('is-invalid');
            $('#first').removeClass('is-valid');
            $('#second').removeClass('is-valid');
        }

    });

});

let loadFile = (event) => {
    // console.log(event);
    let postImage = async (url, formData) => {
        let response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        let uploadImage = await response.json();
        return uploadImage;
    }
    
    console.log(event.target.files[0])
    
    let formData = new FormData();
    formData.append("objectable","users");
    formData.append("file", event.target.files[0], 'image.png');
    postImage(`${base_url}/api/media`, formData)
    .then(data => {
        console.log(data)
        img = data['filePath'];
        $("#set-profile").attr("src", data['filePath']);
        localStorage.setItem('profileId', data['@id']); 
        toastr.success('Please click the save button to update your profile.')
    })
    .catch(err => {console.log(err)})
}
