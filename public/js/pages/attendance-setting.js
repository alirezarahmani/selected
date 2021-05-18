$(document).ready(function () {

    let tok=localStorage.getItem('token');
    let role = localStorage.getItem('role');

    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    if (role === "employee"){
        window.location.href = base_url2 + "/404";
    }

    let billings = JSON.parse(localStorage.getItem('billing'));
    let showModalIp = (ip) => {
        ip.setAttribute('data-toggle', 'modal');
        ip.setAttribute('data-target', '#modalSellerPlan');
    }
    //checkbox
    let showIp = document.getElementById('clockIncom');
    if (billings['useScheduler'] === false) {
        showModalIp(showIp);
    }
    showIp.onchange = function () {
        if (billings['useScheduler'] === true) {
            document.getElementById('allowedIp').style.display = this.checked ? 'block' : 'none'
        } else {
            showModalIp(showIp);
        }
    };

    //get attendance setting for current business
    $.ajax({
        method:'GET',
        url:base_url+'/api/attendance_settings',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            //console.log(res)
            let att=res['hydra:member'][0];
            $('#attendanceSettingId').val(att['id']);
            $('#earlyClock').val(att['earlyLoginAllowed']);
            $('#periodLen').val(att['payrollLengthDefault']);
            $('#hoursnotify').val(att['alertClockInMinutes']);

             //checkboxes
             if(att['clockInWithMobile'] === true){
                    $('#clockInmob').prop("checked", true);
                    $('#clockInmob').val( true);
              }
            if(att['clockInByPc'] === true){
                $('#clockIncom').prop("checked", true);
                $('#clockIncom').val(true);
            }
            if(att['alertClockInManager'] === true){
                $('#notifymanager').prop("checked", true);
                $('#notifymanager').val( true);
            }
            if(att['alertClockIn'] === true){
                $('#notifyemp').prop("checked", true);
                $('#notifyemp').val( true);
            }
            if(att['registerBreak'] === true){
                $('#letRecord').prop("checked", true);
                $('#letRecord').val(true);
            }
            if(att['automateCalculateBreak'] === true){
                $('#deductBreaks').prop("checked", true);
                $('#deductBreaks').val(true);
            }




        },
        error:(e)=>{
            console.log(e)
            //expire jwt token
            if(e.status == 401){
                window.location.href = base_url2+"/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }

    });

    //Put attendance setting for current business
    $('.saveAttSetting').on('click',function () {
        let edId=$('#attendanceSettingId').val();

        let data={
            earlyLoginAllowed:parseInt($('#earlyClock').val()),
            clockInWithMobile: $('#clockInmob').val() === "true",
            clockInByPc:$('#clockIncom').val() === "true",
            payrollLengthDefault: parseInt($('#periodLen').val()),
            alertClockIn: $('#notifyemp').val() === "true",
            alertClockInMinutes:parseInt($('#hoursnotify').val()),
            registerBreak: $('#letRecord').val() === "true",
            automateCalculateBreak: $('#deductBreaks').val() === "true",
            alertClockInManager: $('#notifymanager').val()=== "true"
        }
        console.log(data)
        $.ajax({
            method:'PUT',
            url:base_url+'/api/attendance_settings/'+edId,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                console.log(res);
                toastr.success('Setting Successfuly Updated.');

            },
            error:(e)=>{
                console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }

        });
    });

});
