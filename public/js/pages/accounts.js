let useScheduler;
let tok =localStorage.getItem('token');
let pricingAddress = localStorage.getItem('pricing');
let selectedTimePay = JSON.parse(localStorage.getItem('selectedTimePay'));
if (!tok){
    checkToken();
}else {
    getToken();
}
function getToken() {
    setTimeout(async()=>{
        await localStorage.getItem('token');
        tok = localStorage.getItem('token');
        if (!localStorage.getItem('token')){
            checkToken();
        }else {
            let clicker=function (tok) {
                let loading = document.getElementById('loading');
                loading.style.display = 'block';
                let pointetr = document.querySelector('.alreadybus');
                pointetr.style.pointerEvents = "none";

                let url= base_url+'/api/business/select';
                let selectedId=$(this).data('id');
                localStorage.setItem('selBus',selectedId);

                $.ajax({
                    url:url,
                    crossDomain: true,
                    contentType: 'application/json',
                    method: "POST",
                    dataType: 'json',
                    headers: {
                        'Authorization': `Bearer ${tok.data['tok']}`,
                    },
                    data: JSON.stringify({
                        id_business: selectedId

                    }),
                    success: function(resBus){
                        console.log('resBus',resBus);
                        //get current business info
                        localStorage.setItem('setPreferred', resBus['setPreferred']);
                        localStorage.setItem('seeOtherPosition', resBus['seePositionSchedule']);
                        localStorage.setItem('seeCoworkerSchedule', resBus['seeCoworkerSchedule']);
                        localStorage.setItem('shiftConfirmation', resBus['shiftConfirmation']);
                        localStorage.setItem('availability', resBus['availability']);
                        localStorage.setItem('billing', JSON.stringify(resBus['billing']));
                        useScheduler = resBus['billing'].useScheduler;

                        //get current employee info
                        $.ajax({
                            url: base_url+'/api/users/info',
                            method: 'GET',
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok.data['tok']}`,

                            },
                            success: function(r){

                                console.log('info',r)
                                let key=Object.keys(r['userBusinessRoles'])[0];
                                let employeeRole=r['userBusinessRoles'][key]['role'];
                                localStorage.setItem('userInfo', JSON.stringify(r));
                                localStorage.setItem('schedules',JSON.stringify(r['userHasSchedule']));
                                localStorage.setItem('positions',JSON.stringify(r['positions']));

                                localStorage.setItem('role', employeeRole);
                                localStorage.setItem('email', r['email']);
                                localStorage.setItem('id', r['id']);
                                localStorage.setItem('fullname', r['firstName']+' '+r['lastName']);
                                localStorage.setItem('userImg', r['image'] != null ? r['image']['filePath'] : '');

                                    if (pricingAddress && selectedTimePay === "ok" && employeeRole !== "employee"){
                                        async function goCard(url) {
											let response = await fetch(url,{
												method: 'GET',
												headers : {
													'Content-Type': "application/json",
													'Authorization': `Bearer ${tok.data['tok']}`,
												},
											});
											let goBank = await response.json();
											return goBank;
										}

                                        goCard(base_url+'/api/business_banks/set_bank')
                                            .then(data => {
                                                localStorage.removeItem('pricing');
                                                localStorage.removeItem('selectedTimePay');
                                                localStorage.setItem('redirect_flow_id', JSON.stringify(data.ID));
                                                console.log(resBus['billing']);
                                                setTimeout(() => {
                                                    if (resBus['billing']['name'] === "default") {
                                                        window.location.href = data.url
                                                    }else {
                                                        window.location.href = `${base_url}/success_customer`;
                                                    }
                                                }, 1500);
                                            })
                                            .catch(err => console.log(err))
                                    }else {
                                        if (employeeRole === "employee"){
                                            location.replace(base_url+'/myschedule')
                                        }else {
                                            location.replace(base_url+'/dashboard')
                                        }
                                    }

                            },
                            error:function(e) {
                                loading.style.display = 'none';
                                pointetr.style.cursor = 'pointer';
                                // console.log(e)
                                //expire jwt token
                                if(e.status == 401){
                                    window.location.href = base_url2+"/login";
                                }
                                toastr.error(e['responseJSON']['hydra:description']);
                            }
                        });

                    },
                    error:function(e){
                        console.log(e);
                        loading.style.display = 'none';
                        pointetr.style.cursor = 'pointer';
                        //expire jwt token
                        if(e.status == 401){
                            window.location.href = base_url2+"/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                })
            };
            $(document).ready(async function () {

                //start accounts.js
                await $.ajax({
                    url: base_url+'/api/businesses/self',
                    method:"GET",
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`
                    },
                    success:function (r) {

                        console.log('this',r);
                        if (r.length > 2) {
                            let element = document.getElementById('_workplaceScroll');
                            let elements = document.getElementById('_workplaceScroll_ul');
                            element.classList.add('showAll-workplace')
                            elements.classList.add('showAll-workplace-ul')
                        }else {
                            let element = document.getElementById('_workplaceScroll');
                            let elements = document.getElementById('_workplaceScroll_ul');
                            element.classList.remove('showAll-workplace')
                            elements.classList.remove('showAll-workplace-ul')
                        }
                        localStorage.setItem('businessInfo', JSON.stringify(r));
                        if(r.length == 0){
                            $('.searchtxt').show();
                            document.getElementById('befor_show_workplace_loading').style.display = 'none';
                        }
                        // let business=r["hydra:member"];

                        r.forEach(function(el) {
                            //  console.log(el)
                            document.getElementById('befor_show_workplace_loading').style.display = 'none';
                            let name=el['business']['name'];
                            let busId=el['business']['id'];
                            let role=el['role'];

                            document.querySelector('.alreadybus ul').innerHTML += `
                                <li class="list-items mt-3 mr-3" data-id='${busId}'>
                                    <div class="row">
                                        <div class="col-2 px-0">
                                            <img class="img-fluid img-circle" src='${el['business']['image'] == null ? 'img/workplaceImg.png' : el['business']['image']['filePath']}' >
                                        </div>
                                        <div class="col-9 text-left pt-2">
                                            <strong class="text-capitalize float-left pl-3 font-size-16">${name}</strong>
                                        </div>
                                    </div>
                                </li>
                            `

                            $('.alreadybus ul li.list-items').click({tok:tok},clicker);
                        });

                    },
                    error:function (e) {
                        console.log(e);
                        //expire jwt token
                        if(e.status == 401){
                            window.location.href = base_url2+"/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });

                //show pending business requests
                $.ajax({
                    url: base_url+'/api/business_request/self',
                    method:"GET",
                    contentType: "application/json",
                    headers: {
                        'Authorization': `Bearer ${tok}`
                    },
                    success:function (r) {

                        console.log('show business requested pending',r);
                        let requests=r["hydra:member"];
                        if(requests.length !== 0) {
                            $('.pending-requests').show();
                            requests.forEach(function (el) {
                                console.log(el)
                                let name = el['business']['name'];
                                let add = el['business']['address'];
                                let idcanceled = el['id'];


                                $('.pending-requests ul').append('<div class="row border-bottom p-3">\n' +
                                    '                 <div class="col-2 pt-2 pl-2">\n' +
                                    '               <img src="../../img/pic4.png" class="img-circle elevation-2" alt="User Image" style="width: 34px;height: 34px;">\n' +
                                    '              </div>\n' +
                                    '              <div class="col-8 mt-1">\n' +
                                    '               <h6>' + name + '</h6>\n' +
                                    '               <div class="text-secondary"><small>Employee (Pending)</small></div>\n' +
                                    '               </div>\n' +
                                    '                  <div class="col-2 p-2 text-right"><button type="button" class="btn btn-sm btn-light cancelRequested" data-toggle="modal" data-target="#modal-leave" style="border:none;" data-id="' + idcanceled + '">X</button></div>\n' +
                                    '              </div>');

                            });
                        }

                        //cancel business request
                        $('.cancelRequested').on('click',function () {
                            let idcancel=$(this).data('id');
                            console.log(idcancel);
                            $('#leaveRequestId').val(idcancel);

                        });

                    },
                    error:function (e) {
                        console.log(e);
                        //expire jwt token
                        if(e.status == 401){
                            window.location.href = base_url2+"/login";
                        }
                        toastr.error(e['responseJSON']['hydra:description']);
                    }
                });

                $('.findworkplace').on('click',function (e) {
                    e.preventDefault();
                    window.location.href = base_url2+"/search";
                });

                $('.leaveWp').on('click',function () {
                    let idcan=$('#leaveRequestId').val();

                    $.ajax({
                        url: base_url+'/api/business_requests/'+idcan,
                        method: 'DELETE',
                        contentType: "application/json",
                        headers: {
                            'Authorization': `Bearer ${tok}`,

                        },
                        success: function(r){
                            // console.log(r)
                            location.reload();
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
                //end accounts.js

                //log out clear storage
                $('.logouting').on('click',function () {
                    localStorage.clear();
                    sessionStorage.clear();
                });
            });
        }
    },1000)
}

function checkToken() {
    setTimeout(async()=>{
        if (!localStorage.getItem('token')){
            await localStorage.getItem('token');
            tok = localStorage.getItem('token');
            getToken()
        }
    },1000)
}


