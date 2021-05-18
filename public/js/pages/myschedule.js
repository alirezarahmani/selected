let billings = JSON.parse(localStorage.getItem('billing'));
let personRole = localStorage.getItem('role');

if (billings['useScheduler'] === false && personRole !== "employee") {
    window.location.href = "404.html" 
}else {
    $(document).ready(function () {

        //check token if not set redirect to login page
        let tok=localStorage.getItem('token');
        if(tok == null){
            window.location.href = base_url2+"/login";
        }
    
        //show annotations on top
        $.ajax({
            url: base_url+'/api/annotations',
            method: 'GET',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
    
            },
            success: function(r){
    
              //  console.log(r);
                let annotations = r['hydra:member'];
                if(annotations.length !== 0) {
                    $('.AnnotationCard').show();
                    let annotArray = [];
                    annotations.map((annot) => {
                        console.log('annotation', annot);
                        annotArray.push('<div class="col-5">\n' +
                            '                               <div class="d-inline-block text-white p-2 text-center rounded" style="color: white;background-color: lightgray;">\n' +
                            '                                   <div style="font-size: 12px;">' + (new Date(annot['startDate']).toString()).substring(0, 3) + ' , ' + (new Date(annot['startDate']).toString()).substring(4, 7) + '</div>\n' +
                            '                                   <div>' + (new Date(annot['startDate']).toString()).substring(7, 10) + '</div>\n' +
                            '                               </div>\n' +
                            '                               <div class="d-inline-block p-1" style="line-height: 15px;">\n' +
                            '                                   <div class="font-weight-bold">' + annot['title'] + '</div>\n' +
                            '                                   <div><small>At ' + annot['scheduleId']['name'] + '</small></div>\n' +
                            '                                   <div><small>By ' + annot['createdBy']['firstName'] + ' ' + annot['createdBy']['lastName'] + '  on ' + ((new Date(annot['createdAt'])).toString()).substring(4, 10) + ' , ' + ((new Date(annot['createdAt'])).toString()).substring(10, 15) + '</small></div>\n' +
                            '                               </div>\n' +
                            '                           </div>');
    
                    });
                    $('.annotation-item').html(annotArray);
                }
    
            },
            error:function(e) {
    
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error('Error occurred get annotation');
            }
        });
    
    
        $(document).on('mouseenter', '.op-item', function () {
            //$("this.takeshift").show();
            $(this).find("button.takeshift").show();
    
        }).on('mouseleave', '.op-item', function () {
            //$(".takeshift").hide();
            $(this).find("button.takeshift").hide();
        });
    
        //Confirm Shift
        $('#confirm-shift').on('click',function () {
    
            let shiftedtiId=$('#shifteditedId').val();
    
            $.ajax({
                url: base_url+'/api/shifts/'+shiftedtiId,
                method: 'PUT',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
    
                },
                data:JSON.stringify({
                    confirm:true
                }),
                success: function(r){
                     console.log(r);
                    toastr.success('Shift Successfully Confirmed.');
                },
                error:function(e) {
    
                    //expire jwt token
                    if(e.status == 401){
                        window.location.href = base_url2+"/login";
                    }
                    toastr.error('Error occurred when confirming shift');
                }
            });
        });
    
        //swap button open swap modal
        $('#swap-shift').on('click',function () {
    
            $('#modal-eventClick').modal('hide');
            $('#modal-eventClick').on('hidden.bs.modal', function () {
    
                $('#modal-showEligibles').modal('show')
            });
            $('.content-swap').show();
    
        });
    
        //drop button open drop modal
        $('#drop-shift').on('click',function () {
    
            $('#modal-eventClick').modal('hide');
            $('#modal-eventClick').on('hidden.bs.modal', function () {
    
                $('#modal-showEligibles').modal('show')
            });
            $('.content-drop').show();
    
        });
    
        //view Request
        $('#view-req').on('click',function () {
    
            let reqid=$(this).data('reqid');
            localStorage.setItem('shRreId',reqid);
            location.replace(base_url2+'/shiftrequest-detail');
    
        });
    
        //specific close
        $('.specificClose').on('click',function () {
            $('.content-drop').hide();
            $('.content-swap').hide();
        });
    
        //initiate drop
        $('#initiate-drop').on('click',function () {
    
            let swapsData=[];
            $('.eligChbox').each(function () {
                let objRep={};
                console.log('here')
                if(this.checked == true){
    
                    objRep['user']=$(this).val()
                    swapsData.push(objRep);
    
                }
            });
    
            let repData={
                type:'replace',
                requesterShift: '/api/shifts/'+$('#strIdShift').val(),
                message:$('#drop-note').val(),
                swaps:swapsData
            }
          //  console.log('drop data',repData);
    
            //send shift request for drop shift
            $.ajax({
                method:'POST',
                url:base_url+'/api/shift_requests',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data:JSON.stringify(repData),
                success:(r)=>{
                     console.log(r);
                    toastr.success('Your Request Has Successfully Created.');
    
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
    
        //initiate swap
        $('#initiate-swap').on('click',function () {
    
            let swapsData=[];
    
            $('.swapChbox').each(function () {
                let objSwap={};
    
                if(this.checked == true){
                    objSwap['shift']=$(this).val()
                    swapsData.push(objSwap);
    
                }
            });
    
    
            let swapReqData={
                type:'swap',
                requesterShift:'/api/shifts/'+$('#strIdShiftsw').val(),
                message:$('#swap-note').val(),
                swaps:swapsData
            };
    
            console.log(swapReqData)
            $.ajax({
                method:'POST',
                url:base_url+'/api/shift_requests',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data:JSON.stringify(swapReqData),
                success:(r)=>{
                    console.log(r);
                    // location.reload();
                    toastr.success('Your Request Has Successfully Created.');
    
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
    
    
    
    });
}


