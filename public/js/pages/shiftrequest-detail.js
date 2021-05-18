$(document).ready(function () {
    //check token if not set redirect to login page
    let tok=localStorage.getItem('token');
    let shreqId=localStorage.getItem('shRreId');
    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    //get shift request detail
    $.ajax({
        method:'GET',
        url:base_url+'/api/shift_requests/'+ shreqId,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log('result',res);
            $('#shiftRequestId').val(res['@id']);
            if(localStorage.getItem('role') === 'employee'){
                if(localStorage.getItem('email') === res['requesterId']['email']){

                    if(res['status'] === 'pendingAccept'){
                        $('#cancelBtn').show();

                    }else if(res['status'] === 'cancel' || res['status'] === 'accept'){
                        //see no button
                        $('.contentEligs').hide();

                    }


                }else if(localStorage.getItem('email') !== res['requesterId']['email']){
                    //just see decline & accept
                    if(res['status'] === 'approve'){
                        $('#acceptBtn').show();
                        $('#declineBtn').show();
                        $('.eligChbox').hide();

                    }else if(res['status'] === 'cancel'){
                        //see no button
                        $('.contentEligs').hide();

                    }
                }

            }else{
                //manager

                if(localStorage.getItem('email') !== res['requesterId']['email']){

                    if(res['status'] === 'pendingAccept'){

                        $('#cancelBtn').show();
                        $('#approveBtn').show();
                        $('#denyBtn').show();

                    }

                }


            }

            $('#shiftRequestId').val(res['id']);
            let dr;
            if(res['type'] === 'replace'){
                dr='Drop';
                $('.changeTitle').text('Potential Takers');
            }else if(res['type'] === 'swap'){
                dr='Swap';
                $('.changeTitle').text('Potential Swaps');
            }
            $('.title1').text('Shift '+dr+ ' Request');

            $('.reqstatus').text(res['status']);
            let creatorId=res['shiftRequestLogs'];
            let creator =creatorId[(creatorId.length)-1];

            let color;
            if(res['status'] === "approve" || res['status'] === "accept"){
                $('.logStatus').text(' by '+creator['creatorId']['firstName']+' '+ creator['creatorId']['lastName']+' on '+(new Date(creator['requestDate']).toString()).substring(4,10));
                color='green';
            }else if(res['status'] === "denied" || res['status'] === "cancel" || res['status'] === "decline"){
                $('.logStatus').text(' by '+creator['creatorId']['firstName']+' '+ creator['creatorId']['lastName']+' on '+(new Date(creator['requestDate']).toString()).substring(4,10));
                color='red'
            }else {
                color='gray'
            }
            $('.reqstatus').css('color',color);



            let shift1=((new Date(res['requesterShift']['startTime'])).toString()).substring(0,15)+' |'+(res['requesterShift']['startTime']).substring(11,16)+'-'+(res['requesterShift']['endTime']).substring(11,16);
            $('.shiftinfo1').text(shift1);
            let shift2=res['requesterShift']['ownerId']['firstName']+' '+res['requesterShift']['ownerId']['lastName']+' as '+res['requesterShift']['positionId']['name']+' at '+ res['requesterShift']['scheduleId']['name'];
            $('.shiftinfo2').text(shift2);

            //Potential Takers
            let swaps=[];
            let usersswap=[];
            if(res['type'] === 'replace'){

                res['swaps'].map((swap)=>{

                    console.log(swap);
                    usersswap.push(swap['@id']);
                    if(localStorage.getItem('email') === res['requesterId']['email']){


                        $('#cancelBtn').show();
                        $('#denyBtn').hide();

                    }
                    if(localStorage.getItem('email') === swap['user']['email']){


                        if(res['status'] === 'approve'){
                            $('#acceptBtn').show();
                            $('#declineBtn').show();

                        }


                    }


                    swaps.push('<div class="col-5 border rounded ml-2 p-2" style="background-color: white;">\n' +
                        '                                              <div class="c-item"><img src="../../img/pic4.png" style="height: 30px;width: 30px;"></div>\n' +
                        '                                              <div class="c-item ml-1">\n' +
                        '                                                  <label class="">'+swap['user']['firstName']+' '+swap['user']['lastName']+'</label>\n' +
                        '                                <input class="ml-5 eligChbox" type="checkbox" value="'+swap['@id']+'" checked="true" style="display: none;">\n' +                        '                                              </div>\n' +
                        '                                          </div>');
                });

            }
            else if(res['type'] === 'swap'){

                res['swaps'].map((sw)=>{
                    console.log('swap users bahar',sw)
                    if(res['status'] === 'approve'){

                        if(localStorage.getItem('email') === sw['user']['email']){


                            if(res['status'] === 'approve'){
                                $('#acceptBtn').show();
                                $('#declineBtn').show();
                            }

                        }


                    }
                    //console.log(sw)

                    let stt =new Date(sw['shift']['startTime']).toString();
                    let ett =new Date(sw['shift']['endTime']).toString();
                    if(sw['status'] === 'decline') {
                        swaps.push('<div class="col-5 border rounded ml-2 p-2" style="background-color: white;">\n' +
                            '                                  <div class="c-item text-center" style="background-color:lightgray;color:white;padding: 2px 10px;">\n' +
                            '                                      <div>' + stt.substring(0, 3) + '</div>\n' +
                            '                                      <div>' + stt.substring(8, 10) + '</div>\n' +
                            '                                  </div>\n' +
                            '                                  <div class="c-item ml-1" style="line-height: 23px;">\n' +
                            '                                      <div style="font-size: 12px">' + stt.substring(16, 21) + ' - ' + ett.substring(16, 21) + ' @ ' + sw['shift']['scheduleId']['name'] + '</div>\n' +
                            '                                      <div style="font-size: 10px">' + sw['shift']['ownerId']['firstName'] + ' ' + (sw['shift']['ownerId']['lastName']).charAt(0) + '. as ' + sw['shift']['positionId']['name'] + '</div>\n' +
                            '\n' +
                            '\n' +
                            '                                  </div>\n' +
                            '                                 <div class="c-item"><i class="fas fa-ban"></i></div>\n' +
                            '                              </div>');
                    }else {
                        swaps.push('<div class="col-5 border rounded ml-2 p-2" style="background-color: white;">\n' +
                            '                                  <div class="c-item text-center" style="background-color: lightgray;color:white;padding: 2px 10px;">\n' +
                            '                                      <div>' + stt.substring(0, 3) + '</div>\n' +
                            '                                      <div>' + stt.substring(8, 10) + '</div>\n' +
                            '                                  </div>\n' +
                            '                                  <div class="c-item ml-1" style="line-height: 23px;">\n' +
                            '                                      <div style="font-size: 12px">' + stt.substring(16, 21) + ' - ' + ett.substring(16, 21) + ' @ ' + sw['shift']['scheduleId']['name'] + '</div>\n' +
                            '                                      <div style="font-size: 10px">' + sw['shift']['ownerId']['firstName'] + ' ' + (sw['shift']['ownerId']['lastName']).charAt(0) + '. as ' + sw['shift']['positionId']['name'] + '</div>\n' +
                            '\n' +
                            '\n' +
                            '                                  </div>\n' +
                            '                              </div>');
                    }
                });
            }

            $('.eligReplace').html(swaps);

            //shift request logs
            let logs=res["shiftRequestLogs"];
            let createLog=[];
            createLog.push('<div class="border p-2 mb-3" style="margin-left: -28px;">\n' +
                '                                          <div><small>Request Created</small></div>\n' +
                '                                          <div class="font-weight-lighter"><small>by '+res['requesterId']['firstName']+' '+res['requesterId']['lastName']+' on '+((new Date(res['date'])).toString()).substring(0,10)+' at '+res['date'].substring(11,16)+'</small></div>\n' +
                '                                      </div>');
            logs.map((log)=>{
                console.log('log',log)
                let clr;
                if(log['type'] === "approve" || log['type'] === "accept"){
                    clr="green";
                }else if(log['type'] === "denied" || log['type'] === "cancel" || log['type'] === "decline" ){
                    clr="red";
                }else{
                    clr="gray";
                }
                if(log['message'] === "" || log['message'] === null  && log['type'] !== "none"){


                    createLog.push(' <div class="border p-2 mb-3" style="margin-left: -28px;">\n' +
                        '                                           <div style="color: '+clr+';"><small>Request '+log['type']+'</small></div>\n' +
                        '                                           <div class="font-weight-lighter"><small>by '+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' on '+((new Date(res['date'])).toString()).substring(0,10)+' at '+res['date'].substring(11,16)+'</small></div>\n' +
                        '                                       </div>');

                } else if(log['message'] !== "" && log['type'] === "none"){

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
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['requestDate'])).toString()).substring(0,10)+'@'+log['requestDate'].substring(11,16)+'</p>\n' +
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
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['requestDate'])).toString()).substring(0,10)+'@'+log['requestDate'].substring(11,16)+'</p>\n' +
                            '                                          </div>\n' +
                            '\n' +
                            '                                      </div>\n' +
                            '                                   \n' +
                            '                                </div>');
                    }

                }else if(log['message'] !== "" && log['type'] !== "none"){

                    createLog.push(' <div class="border p-2 mb-3" style="margin-left: -28px;">\n' +
                        '                                           <div style="color: '+clr+';"><small>Request '+log['type']+'</small></div>\n' +
                        '                                           <div class="font-weight-lighter"><small>by '+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' on '+((new Date(res['date'])).toString()).substring(0,10)+' at '+res['date'].substring(11,16)+'</small></div>\n' +
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
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['requestDate'])).toString()).substring(0,10)+'@'+log['requestDate'].substring(11,16)+'</p>\n' +
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
                            '                                              <p class="request-by" style="font-size: 10px; color: #757575;padding: 3px 0 0 10px;">'+log['creatorId']['firstName']+' '+log['creatorId']['lastName']+' - '+((new Date(log['requestDate'])).toString()).substring(0,10)+'@'+log['requestDate'].substring(11,16)+'</p>\n' +
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
    $('#msgPost').on('click',function () {

        $.ajax({
            method:'POST',
            url:base_url+'/api/shift_request_logs',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                shiftRequestId:'/api/shift_requests/'+$('#shiftRequestId').val(),
                message:$('#msgBox').val()
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

    //accept shift request
    $('#acceptBtn').on('click',function () {

        let acceptUser=[];
        $('.eligChbox').each(function () {


            if(this.checked == true){

                acceptUser.push($(this).val());
            }
        });

        let shiftreqID = $('#shiftRequestId').val();

        $.ajax({
            method:'PUT',
            url:base_url+'/api/shift_requests/'+ shiftreqID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                status:'accept',
                swaps: acceptUser

            }),
            success:(r)=>{
               // console.log(r)
                 location.reload();

            },
            error:(e)=>{
                // console.log(e)
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });
    });

    //decline swap
    $('#declineBtn').on('click',function () {
        let declineUser=[];
        $('.eligChbox').each(function () {


            if(this.checked == true){

                declineUser.push($(this).val());
            }
        });


        let shiftreqID = $('#shiftRequestId').val();

        $.ajax({
            method:'PUT',
            url:base_url+'/api/shift_requests/'+ shiftreqID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                status:'decline',
                swaps: declineUser

            }),
            success:(r)=>{
                // console.log(r)
                location.reload();

            },
            error:(e)=>{
                // console.log(e)
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });
    });

    //accept shift request
    $('#approveBtn').on('click',function () {
        let shiftreqID = $('#shiftRequestId').val();

        $.ajax({
            method:'PUT',
            url:base_url+'/api/shift_requests/'+ shiftreqID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                status:'approve'

            }),
            success:(r)=>{
              //  console.log(r)
                 location.reload();

            },
            error:(e)=>{
                // console.log(e)
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });
    });

    //deny shift request
    $('#denyBtn').on('click',function () {
        let shiftreqID = $('#shiftRequestId').val();


        $.ajax({
            method:'PUT',
            url:base_url+'/api/shift_requests/'+ shiftreqID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                status:'denied'

            }),
            success:(r)=>{
              //  console.log(r)
                 location.reload();

            },
            error:(e)=>{
                // console.log(e)
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });
    });

    //cancel shift request
    $('#cancelBtn').on('click',function () {
        let shiftreqID = $('#shiftRequestId').val();

        $.ajax({
            method:'PUT',
            url:base_url+'/api/shift_requests/'+ shiftreqID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                status:'cancel'

            }),
            success:(r)=>{
                console.log(r)
                // location.reload();

            },
            error:(e)=>{
                // console.log(e)
                toastr.error(e['responseJSON']['hydra:description']);
            }


        });
    });


});