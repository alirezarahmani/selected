//check token if not set redirect to login page
let tok=localStorage.getItem('token');
if(tok == null){
    window.location.href = base_url2+"/login";
}
$(document).ready(function () {

    //load shift templates list in datatable
    let tableList=$("#shiftTemplates").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[0,5]  }
        ],
        "scrollY":        "300px",
        "scrollCollapse": true,
        "paging":   false,
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        "columns": [
            { data: 'Color',className:'Color' },
            { data: 'Shift Templates',className:'ShiftTemp' },
            { data: 'Unpaid Break',className:'UnpaidBreak' },
            { data: 'Position',className:'Position'},
            { data: 'Schedule',className:'Schedule'},
            { data: 'Actions',className:'Actions' }
        ],
        "ajax":{
            'type':'get',
            'url':base_url+'/api/shift_templates',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
                console.log(json);
                let data=json.map(obj=>{

                    let temps={};
                    let PosID;
                    if(obj['positionId'] == null){
                        PosID="-";

                    }else{
                        PosID=obj['positionId']['name'];
                    }
                    temps['Color']='<div style="background-color: '+obj['color']+'" class="circle-color"></div>';
                    temps['Shift Templates']=obj['startTime']+'-'+obj['endTime'];
                    temps['Unpaid Break']=obj['unpaidBreak']+' min(s)';
                    temps['Position']=PosID;
                    temps['Schedule']=obj['scheduleId']['name'];
                    temps['Actions']=
                        " <div class='btn-group'>" +
                        ' <button onclick="delete_temps('+obj['id']+')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delshtmp">'+
                        "<i class='far fa-trash-alt'></i>" +
                        " </button>"+
                        " <button onclick='edit_temps("+obj['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addshift'>" +
                        "<i class='fas fa-pencil-alt'></i>" +
                        " </button>"+
                        "</div>";

                    return temps;
                });
                return data;
            }
        },
    });

    //onclick table add class selected
    $('#shiftTemplates tbody').on( 'click', 'tr', function () {
        $('#shiftTemplates tbody tr').removeClass('selected');
        $(this).addClass('selected');
    } );


    //appending top buttons
    $('#shiftTemplates_wrapper .row .col-md-6:first-child').prepend('<div class="ml-2 mt-2"><button type="button" class="btn btn-custbl btn-sm addshift ml-3" data-toggle="modal" data-target="#modal-addshift">\n' +
        '                                Add Shift Template\n' +
        '                            </button>\n' +
        '\n' +
        '                            </div>');

    //color picker with addon
    $('.select-color').colorpicker();
    $('.select-color').on('colorpickerChange', function(event) {
        $('.select-color .fa-square').css('color', event.color.toString());
    });
/*

    $('#posList').select2({
        placeholder: "No Position",
        allowClear: true
    });
*/


    //enable add button if input is not empty
 /*   $('.datetimepicker-input').blur(function() {

        var empty = false;
        $('.datetimepicker-input').each(function() {
            if ($(this).val().length == 0) {
                empty = true;
            }
        });
        console.log('empty',empty)
        if (empty) {
            $('.addshtmp').attr('disabled', 'disabled');
        } else {
            $('.addshtmp').removeAttr('disabled');            }
    });*/

    $('#posList').select2();
    $('#schList').select2();

    //Timepicker
    $('#start-time').datetimepicker({
        format: 'LT'
    });


    $('#end-time').datetimepicker({
        format: 'LT'
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
        url:  base_url+'/api/schedules',
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

    //empty modal
    $('#modal-addshift').on('hidden.bs.modal', function (e) {
        $('#edit-shtmp').hide();
        $('#add-shtmp').show();
        $(this)
            .find("input[type=text],textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();
        $('.fa-square').css('color','rgb(202, 204, 213)');
        $('.colorpicker-guide').css({'top':'122px','left':'2px'});
        $('#posList').val(null).trigger('change');
        $('#schList').val(null).trigger('change');


    });


    //add shift template
    $('#add-shtmp').on('click',function(){

     let pos= $('#posList').val() === '' ? null : $('#posList').val();
     let unp;
     if($("#unpaid").val()==''){
         unp=0;
     }else{
          unp=$("#unpaid").val();
     }
        let data={
            positionId:pos,
            scheduleId:$("#schList").val(),
            startTime:$("#startTime").val(),
            endTime:$("#endTime").val(),
            notes:$("#note").val(),
            color: document.getElementById('changeColorForBG').style.backgroundColor,
            unpaidBreak:parseInt(unp)
        };
        console.log('data',data)
        $.ajax({
            method:'POST',
            url:base_url+'/api/shift_templates',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                 console.log(res);
                let PosID;
                if(res['positionId'] == null){
                    PosID="-";

                }else{
                    PosID=res['positionId']['name'];
                }
                toastr.success('shift template successfully added.')

                tableList.row.add( {
                    "Color":'<div style="background-color: '+res['color']+'" class="circle-color"></div>',
                    "Shift Templates":res['startTime']+'-'+res['endTime'],
                    "Unpaid Break":res['unpaidBreak']+' min(s)',
                    "Position":PosID,
                    "Schedule":res['scheduleId']['name'],
                    "Actions":   " <div class='btn-group'>" +
                    ' <button onclick="delete_temps('+res['id']+')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delshtmp">'+
                    "<i class='far fa-trash-alt'></i>" +
                    " </button>"+
                    " <button onclick='edit_temps("+res['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addshift'>" +
                    "<i class='fas fa-pencil-alt'></i>" +
                    " </button>"+
                    "</div>"
                }).draw( false );

            },
            error:(e)=>{
                // console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }


        })
    });

    //final delete shift template
    $('#delete-shtmp').on('click', function(){
        let idshtemp=$('#shtemp-id').val();

        $.ajax({
            method:'DELETE',
            url:base_url+'/api/shift_templates/'+idshtemp,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=>{
                // console.log(res)
                toastr.success('Shift Template Successfully Deleted.');
                tableList.rows('.selected').remove().draw();
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

    //final edit shift template
    $('#edit-shtmp').on('click',function(){

        let edID= $('#editIdshtmp').val();
        console.log($('#posList').val());
        let posIDedit;
        if($('#posList').val() == ''){
            posIDedit=null;
        }else{
            posIDedit=$('#posList').val()
        }
        let unp;
        if($("#unpaid").val()==''){
            unp=0;
        }else{
            unp=$("#unpaid").val();
        }
        let data={
                positionId:posIDedit,
                scheduleId:$("#schList").val(),
                startTime:$("#startTime").val(),
                endTime:$("#endTime").val(),
                notes:$("#note").val(),
                color: document.getElementById('changeColorForBG').style.backgroundColor,
                unpaidBreak:parseInt(unp)
            };


        $.ajax({
            method:'PUT',
            url:base_url+'/api/shift_templates/'+edID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
               console.log(res)
                let PosID;
                if(res['positionId'] == null){
                    PosID="-";

                }else{
                    PosID=res['positionId']['name'];
                }
                toastr.success('Shift Template Successfully Edited.');
                tableList.cell('.selected .Color').data(' <div style="background-color: '+res['color']+'" class="circle-color"></div>');
                tableList.cell('.selected .ShiftTemp').data(res['startTime']+'-'+res['endTime']);
                tableList.cell('.selected .UnpaidBreak').data(res['unpaidBreak']+' min(s)');
                tableList.cell('.selected .Position').data(PosID);
                tableList.cell('.selected .Schedule').data(res['scheduleId']['name']);
                tableList.draw();
                $('.fa-square').css('color','rgb(202, 204, 213)');


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

let delete_temps=(id)=>{

    let delId=id;
    $('#shtemp-id').val(delId);
};

let edit_temps=(id)=>{

    $.ajax({
        method:'GET',
        url:base_url+'/api/shift_templates/'+id,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log(res)
            // $('#color').val(res['color']);
            document.getElementById('changeColorForBG').style.backgroundColor = res['color'];
            $('.fa-square').css('color',res['color']);
            $('#editIdshtmp').val(res['id']);
            $('#note').val(res['notes']);
            if(res['positionId'] != null){
                $('#posList').val(res['positionId']['@id']).trigger('change');
            }

            $('#schList').val(res['scheduleId']['@id']).trigger('change');
            $('#startTime').val(res['startTime']);
            $('#endTime').val(res['endTime']);
            $('#unpaid').val(res['unpaidBreak']);

            $("#add-shtmp").hide();
            $("#edit-shtmp").show();



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

let setColors = (e) => {
    document.getElementById('changeColorForBG').style.backgroundColor = e.srcElement.style.backgroundColor;
}
