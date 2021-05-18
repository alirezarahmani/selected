$(document).ready(function () {
    //check token if not set redirect to login page
    let tok=localStorage.getItem('token');
    let timeoffId=localStorage.getItem('toid');
    let userIdto=localStorage.getItem('userIdto');
    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    //get time off request detail
    $.ajax({
        method:'GET',
        url:base_url+'/api/time_off_requests/'+ timeoffId,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log('result',res);

            $('.nameTotal').text(res['userID']['firstName']);
            $('#timeoffRequestId').val(res['id']);
            $('.title1').text(res['type']+ ' Time Off Request');


            let color,statusTxt;
            if(res['Status'] === "accepted"){
                statusTxt='ACCEPTED';
                color='green';
            }else if(res['Status'] === "denied"){
                statusTxt='DENIED';
                color='red'
            }else if(res['Status'] === "canceled"){
                statusTxt='CANCELED';
                color='red';
            }else if(res['Status'] === "created"){
                statusTxt='PENDING APPROVAL';
                color='yellow';
            }
            $('.reqstatus').css('color',color);
            $('.reqstatus').text(statusTxt);

            if(localStorage.getItem('role') === 'employee'){
                if(res['Status']!== 'canceled'){
                    $('#cancel-btn').show();
                }

            }else {
                //manager
                if(res['Status'] === "created"){
                    $('#accept-btn').show();
                    $('#deny-btn').show();
                }else if(res['Status'] === "accepted"){
                    $('#deny-btn').show();

                }else if(res['Status'] === "denied"){
                    $('#accept-btn').show();

                }
            }

            if(res['paidHour'] !== ''){
                $('#unpaidh').text(res['paidHour']+' Paid Hour');
                $('#unpaidh').show();
            }




            let logs=res["timeoffLogs"];
            //last log
            let by =logs[(logs.length)-1];
            console.log(by)
            if(res['Status'] === 'created'){
                $('.logStatus').text('from management');
            }else{
                $('.logStatus').text(' by '+by['creatorId']['firstName']+' '+by['creatorId']['lastName']+' On '+ ((new Date(by['date'])).toString()).substring(4,10));

            }

            if(res['userID']['id'] === res['userCreatorId']['id']){
                $('.creator').text(res['userID']['firstName']+' '+res['userID']['lastName']+' requested '+res['type']+' time off.')
            }else{
                $('.creator').text(res['userCreatorId']['firstName']+' '+res['userCreatorId']['lastName']+' added '+res['type']+' time off for '+res['userID']['firstName']+' '+res['userID']['lastName']);
            }

            let st=res['startTime'];
            let et=res['endTime'];

            if(st.substring(0,10) === et.substring(0,10)){

                if(st.substring(11,16) === '00:00' && et.substring(11,16) === '23:59'){
                    $('.timeoffDate').text(((new Date(res['startTime'])).toString()).substring(0,10)+','+((new Date(res['startTime'])).toString()).substring(10,15));
                }else{
                    $('.timeoffDate').text(((new Date(res['startTime'])).toString()).substring(0,10)+','+((new Date(res['startTime'])).toString()).substring(10,15)+' from '+ st.substring(11,16)+'-'+et.substring(11,16));

                }
            }else{
                $('.timeoffDate').text(((new Date(res['startTime'])).toString()).substring(0,10)+'-'+((new Date(res['endTime'])).toString()).substring(4,15));

            }

            //time off request logs

            let createLog=[];
            createLog.push('<div class="border p-2 mb-3" style="margin-left: -28px;">\n' +
                '                                          <div><small>Request Created</small></div>\n' +
                '                                          <div class="font-weight-lighter"><small>by '+res['userCreatorId']['firstName']+' '+res['userCreatorId']['lastName']+' on '+((new Date(res['createdAt'])).toString()).substring(0,16)+'</small></div>\n' +
                '                                      </div>');
            logs.map((log)=>{
                console.log('log',log)
                let clr;
                if(log['status'] === "accepted"){
                    clr="green";
                }else if(log['status'] === "denied" || log['status'] === "canceled" ){
                    clr="red";
                }else{
                    clr="yellow";
                }
                if(log['message'] ==="" && log['status'] !== "non_status"){

                    createLog.push(' <div class="border p-2 mb-3" style="margin-left: -28px;">\n' +
                        '                                           <div style="color: '+clr+';"><small>Request '+log['status']+'</small></div>\n' +
                        '                                           <div class="font-weight-lighter"><small>by '+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' on '+((new Date(log['date'])).toString()).substring(0,10)+' at '+log['date'].substring(11,16)+'</small></div>\n' +
                        '                                       </div>');

                }else if(log['message'] !=="" && log['status'] === "non_status"){

                    //check right or left
                    if(log['creatorId']['email'] === localStorage.getItem('email')){

                        createLog.push(' <div class="row mr-2">\n' +
                            '                                      <div class="col-sm-2 order-last">\n' +
                            '\n' +
                            '                                          <div style="margin-left: 10px;padding: 10px 0;display: inline-block;">\n' +
                            '                                              <img src="../../img/pic4.png" class="img-circle" style="width: 34px;height: 34px;">\n' +
                            '                                          </div>\n' +
                            '                                      </div>\n' +
                            '                                    <div class="col-sm-10" style="text-align: right">\n' +
                            '                                          <div class="" style="margin-left: 10px;padding: 10px 0;display: inline;">\n' +
                            '                                              <div style="background-color: #f5f5f5; padding: 10px;position: relative;display: inline-block;border-radius: 4px;">\n' +
                            '                                                  <i class="fa fa-caret-right" style="right: -8px;color: #f5f5f5; font-size: 28px; position: absolute; top: 3px;"></i>\n' +
                            '                                                  <strong>'+log['message']+'</strong>\n' +
                            '                                              </div>\n' +
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['date'])).toString()).substring(0,10)+'@'+log['date'].substring(11,16)+'</p>\n' +
                            '                                          </div>\n' +
                            '\n' +
                            '                                      </div>\n' +
                            '\n' +
                            '                                </div>');
                    }else {

                        createLog.push('<div class="row" style="margin-left: -49px;">\n' +
                            '                                      <div class="col-sm-2 order-first">\n' +
                            '\n' +
                            '                                          <div  style="margin-left: 10px;padding: 10px 0;display: inline-block;">\n' +
                            '                                              <img src="../../img/pic4.png" class="img-circle" style="width: 34px;height: 34px;">\n' +
                            '                                          </div>\n' +
                            '                                      </div>\n' +
                            '                                    <div class="col-sm-10">\n' +
                            '                                          <div class="" style="margin-left: 10px;padding: 10px 0;display: inline;">\n' +
                            '                                              <div style="background-color: #f5f5f5; padding: 10px;position: relative;display: inline-block;border-radius: 4px;">\n' +
                            '                                                  <i class="fa fa-caret-left" style="left: -8px;    color: #f5f5f5; font-size: 28px; position: absolute; top: 3px;"></i>\n' +
                            '                                                  <strong>'+log['message']+'</strong>\n' +
                            '                                              </div>\n' +
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['date'])).toString()).substring(0,10)+'@'+log['date'].substring(11,16)+'</p>\n' +
                            '                                          </div>\n' +
                            '\n' +
                            '                                      </div>\n' +
                            '                                   \n' +
                            '                                </div>');
                    }

                }else if(log['message'] !=="" && log['status'] !== "non_status"){

                    createLog.push(' <div class="border p-2 mb-3" style="margin-left: -28px;">\n' +
                        '                                           <div style="color: '+clr+';"><small>Request '+log['status']+'</small></div>\n' +
                        '                                           <div class="font-weight-lighter"><small>by '+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' on '+((new Date(res['createdAt'])).toString()).substring(0,10)+' at '+((new Date(log['createdAt'])).toString()).substring(11,16)+'</small></div>\n' +
                        '                                       </div>');

                    //check right or left
                    if(log['creatorId']['email'] === localStorage.getItem('email')){
                        createLog.push(' <div class="row mr-2">\n' +
                            '                                      <div class="col-sm-2 order-last">\n' +
                            '\n' +
                            '                                          <div  style="margin-left: 10px;padding: 10px 0;display: inline-block;">\n' +
                            '                                              <img src="../../img/pic4.png" class="img-circle" style="width: 34px;height: 34px;">\n' +
                            '                                          </div>\n' +
                            '                                      </div>\n' +
                            '                                    <div class="col-sm-10" style="text-align: right">\n' +
                            '                                          <div class="" style="margin-left: 10px;padding: 10px 0;display: inline;">\n' +
                            '                                              <div style="background-color: #f5f5f5; padding: 10px;position: relative;display: inline-block;border-radius: 4px;">\n' +
                            '                                                  <i class="fa fa-caret-right" style="right: -8px;color: #f5f5f5; font-size: 28px; position: absolute; top: 3px;"></i>\n' +
                            '                                                  <strong>'+log['message']+'</strong>\n' +
                            '                                              </div>\n' +
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['date'])).toString()).substring(0,10)+'@'+log['date'].substring(11,16)+'</p>\n' +
                            '                                          </div>\n' +
                            '\n' +
                            '                                      </div>\n' +
                            '\n' +
                            '                                </div>');
                    }else {
                        createLog.push('<div class="row" style="margin-left: -49px;">\n' +
                            '                                      <div class="col-sm-2 order-first">\n' +
                            '\n' +
                            '                                          <div  style="margin-left: 10px;padding: 10px 0;display: inline-block;">\n' +
                            '                                              <img src="../../img/pic4.png" class="img-circle" style="width: 34px;height: 34px;">\n' +
                            '                                          </div>\n' +
                            '                                      </div>\n' +
                            '                                    <div class="col-sm-10">\n' +
                            '                                          <div class="" style="margin-left: 10px;padding: 10px 0;display: inline;">\n' +
                            '                                              <div style="background-color: #f5f5f5; padding: 10px;position: relative;display: inline-block;border-radius: 4px;">\n' +
                            '                                                  <i class="fa fa-caret-left" style="left: -8px;    color: #f5f5f5; font-size: 28px; position: absolute; top: 3px;"></i>\n' +
                            '                                                  <strong>'+log['message']+'</strong>\n' +
                            '                                              </div>\n' +
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['date'])).toString()).substring(0,10)+'@'+log['date'].substring(11,16)+'</p>\n' +
                            '                                          </div>\n' +
                            '\n' +
                            '                                      </div>\n' +
                            '                                   \n' +
                            '                                </div>');
                    }
                }

            });
            $('div.logList').html(createLog);

        },
        error:(e)=>{
            //expire jwt token
            if(e.status == 401){
                window.location.href = base_url2+"/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });

    //post message
    $('#msgtoffPost').on('click',function () {

        $.ajax({
            method:'POST',
            url:base_url+'/api/timeoff_logs',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                timeOffRequstId: '/api/time_off_requests/'+$('#timeoffRequestId').val(),
                message:$('#msgTimeoff').val()
            }),
            success:(r)=>{
                //console.log(r)
                location.reload();

            },
            error:(e)=>{
                // console.log(e)
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });


    });

    //Total time off
    $.ajax({
        method:'GET',
        url:base_url+'/api/time_off_totals?user='+ userIdto,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(r)=>{
          console.log('timeofftotal',r)
          let totals=r['hydra:member'];
          let key=Object.keys(totals)[0];
          console.log('key',key)

            //$('.sick').text(totals[key]['totalSick']);
            //$('.holiday').text(totals[key]['totalHoliday']);
            let timeoffLeft=parseFloat(totals[key]['deservedHoliday']);
            $('.deserved').text(timeoffLeft.toFixed(2));
            let totalUsed=parseFloat(totals[key]['totalHoliday']) + parseFloat(totals[key]['deservedHoliday']);
            $('.useded').text(totalUsed.toFixed(2));





        },
        error:(e)=>{
            //expire jwt token
            if(e.status == 401){
                window.location.href = base_url2+"/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });


});