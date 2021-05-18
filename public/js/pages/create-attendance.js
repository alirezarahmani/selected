$(document).ready(function () {
    let tok =localStorage.getItem('token');
    let createbusId =localStorage.getItem('createbusId');

    var $submit = $("#createAttendance"),
        $inputs = $('input[type=text],select');

    function checkEmpty() {
        return $inputs.filter(function() {
            return !$.trim(this.value);
        }).length === 0;
    }

    $inputs.on('blur', function() {
        $submit.prop("disabled", !checkEmpty());
    }).blur();

   $('#createAttendance').on('click',function () {
       console.log('createbusId',createbusId)
       let loading = document.getElementById('imgLoading');
       let disabledBTN = document.getElementById('createAttendance');
       let earlyMin = document.getElementById('earlyMin');
       let payrollLen = document.getElementById('payrollLen');
       let nearDistance = document.getElementById('nearDistance');
       let optionOclock = earlyMin.options[earlyMin.selectedIndex].value;
       let optionPayroll = payrollLen.options[payrollLen.selectedIndex].value;
       let optionDistance = nearDistance.options[nearDistance.selectedIndex].value;


       loading.style.display = 'block';
       disabledBTN.style.display = 'none';
       if (optionOclock != "null" && optionDistance != "null" && optionPayroll != "null"){
           $.ajax({
               method:'POST',
               url:base_url+'/api/attendance_settings',
               contentType: "application/json",
               headers: {
                   'Authorization': `Bearer ${tok}`,
               },
               data:JSON.stringify({
                   "earlyLoginAllowed":parseInt(optionOclock),
                   "nearByLocationDistance":parseInt(optionDistance),
                   "payrollLengthDefault":parseInt(optionPayroll),
                   "business":'/api/businesses/'+createbusId
               }),
               success:(res)=>{
                   console.log('res',res['business']);
                   let arrId=res['business'].split('/');
                   console.log(arrId[3])
                   let idBusiness=arrId[3];
                   localStorage.setItem('selBus', idBusiness);
                   $.ajax({
                       url:base_url+'/api/business/select',
                       crossDomain: true,
                       contentType: 'application/json',
                       method: "POST",
                       dataType: 'json',
                       headers: {
                           'Authorization': `Bearer ${tok}`,
                       },
                       data: JSON.stringify({
                           id_business: idBusiness
                       }),
                       success: function(r){
                           console.log(r);
                            localStorage.setItem('billing', JSON.stringify(r.billing));
                            let pricing = localStorage.getItem('pricing');
                            let selectedTimePay = JSON.parse(localStorage.getItem('selectedTimePay'));
                           //get current employee info
                           $.ajax({
                               url: base_url+'/api/users/info',
                               method: 'GET',
                               contentType: "application/json",
                               headers: {
                                   'Authorization': `Bearer ${tok}`,
                               },
                               success: function(r){
                                   console.log('info',r)
                                   let bussinessRoles = r.userBusinessRoles;
                                   for (let key in bussinessRoles){
                                       let role = bussinessRoles[key];
                                        localStorage.setItem('role', role['role']);
                                   }
                                   localStorage.setItem('email', r['email']);
                                   localStorage.setItem('fullname', r['firstName']+' '+r['lastName']);
                                   if (selectedTimePay === "ok") {
                                        async function goCard(url) {
                                            let response = await fetch(url,{
                                                method: 'GET',
                                                headers : {
                                                    'Content-Type': "application/json",
                                                    'Authorization': `Bearer ${tok}`,
                                                },
                                            });
                                            let goBank = await response.json();
                                            return goBank;
                                        }
                                        goCard(base_url+'/api/business_banks/set_bank')
                                        .then(data => {
                                            localStorage.removeItem('selectedTimePay');
                                            localStorage.setItem('redirect_flow_id', JSON.stringify(data.ID));
                                            setTimeout(() => {
                                                window.location.href = data.url
                                            }, 1000);
                                        })
                                        .catch(err => console.log(err) )
                                   }else {
                                       localStorage.setItem('selBus', idBusiness);
                                       console.log(localStorage.getItem('selBus'), '000');
                                       location.replace(base_url2+'/entry');
                                   }
                                   //***store in session
                                   //redirect to scheduler
                               },
                               error:function(e) {
                                   if(e.status == 401){
                                       window.location.href = base_url2+"/login";
                                   }
                                   toastr.error('Error occurred user info');
                               }
                           });
                       },
                       error:function(e){
                           console.log(e);
                           //expire jwt token
                           loading.style.display = 'none'
                           disabledBTN.style.display = 'block';
                           if(e.status == 401){
                               window.location.href = base_url2+"/login";
                           }
                           toastr.error("Try again");
                       }
                   });
               },
               error:(e)=>{
                   // console.log(e)
                   toastr.error('Something wrong,Try again!');
                   loading.style.display = 'none'
                   disabledBTN.style.display = 'block';
               }
           });
       }else {
           toastr.error('Please enter the information correctly.');
           loading.style.display = 'none';
           disabledBTN.style.display = 'block';
       }

   });
});
