//check token if not set redirect to login page
let personRole=localStorage.getItem('role');
let tok=localStorage.getItem('token');
if(tok == null){
    window.location.href = base_url2+"/login";
}
$(document).ready(function () {
    //load schedule list in datatable
    let tableList=$("#schedules").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[2]  }
        ],
        "scrollY":        "300px",
        "scrollCollapse": true,
        "paging":         false,
        "select": {
            style: 'single'
        },
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        "columns": [
            { data: 'Schedules' ,className:'Schedules'},
            { data: 'Address' ,className:'Address'},
            { data: 'Actions',className:'Actions' }
        ],
        "ajax":{
            'type':'get',
            'url':base_url+'/api/schedules',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
                console.log(json);
                let data=json.map(obj=>{
                    let sche={};

                    if(personRole === 'supervisor'){

                        sche['Schedules']=obj["name"];
                        sche['Address']=obj["address"];
                        sche['Actions']=
                            " <div class='btn-group'>" +
                            ' <button  type="button"  title="Delete" class="btn btn-default btn-sm" disabled>'+
                            "<i class='far fa-trash-alt'></i>" +
                            " </button>"+
                            " <button  type='button'  title='Edit' class='btn btn-default btn-sm' disabled >" +
                            "<i class='fas fa-pencil-alt'></i>" +
                            " </button>"+
                            "</div>";
                    }else{

                        sche['Schedules']=obj["name"];
                        sche['Address']=obj["address"];
                        sche['Actions']=
                            " <div class='btn-group'>" +
                            ' <button onclick="delete_schedule(\''+obj['id']+'\',\''+obj['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delsch">'+
                            "<i class='far fa-trash-alt'></i>" +
                            " </button>"+
                            " <button onclick='edit_schedule("+obj['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addsch'>" +
                            "<i class='fas fa-pencil-alt'></i>" +
                            " </button>"+
                            "</div>";
                    }


                    return sche;
                });
                return data;
            }
        }

    });

    //onclick table add class selected
    $('#schedules tbody').on( 'click', 'tr', function () {
        $('#schedules tbody tr').removeClass('selected');
        $(this).addClass('selected');
    } );


    //appending top buttons
    if(personRole !== 'supervisor'){
        $('#schedules_wrapper .row .col-md-6:first-child').prepend('<div class="ml-2 mt-2"><button type="button" class="btn btn-custbl btn-sm addschedule ml-3" data-toggle="modal" data-target="#modal-addsch">\n' +
            '                                Add Schedule\n' +
            '                            </button>\n' +
            '\n' +
            '                            </div>');
    }


    //enable add button if input is not empty
    $('#name').keyup(function() {

        var empty = false;
        $('#name').each(function() {
            if ($(this).val().length == 0) {
                empty = true;
            }
        });

        if (empty) {
            $('.addSchedule').attr('disabled', 'disabled');
        } else {
            $('.addSchedule').removeAttr('disabled');
        }
    });

    //add schedule
    $('#add-schedule').click(function () {
        let data={
            name:$('#name').val(),
            address:$('#pac-input').val(),
            lat:$('#latitude').val(),
            lang:$('#langtitude').val(),
            maxHourWeek:$('#hours').val(),
            ipAddress:$('#ip-address').val()
        };
        $.ajax({
            method:'POST',
            url:base_url+'/api/schedules',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                console.log(res)
                toastr.success('schedule successfully added.');
                // location.reload();
                tableList.row.add( {
                    "Schedules":res["name"],
                    "Address":res["address"],
                    "Actions": " <div class='btn-group'>" +
                        ' <button onclick="delete_schedule(\''+res['id']+'\',\''+res['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delsch">'+
                        "<i class='far fa-trash-alt'></i>" +
                        " </button>"+
                        " <button onclick='edit_schedule("+res['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addsch'>" +
                        "<i class='fas fa-pencil-alt'></i>" +
                        " </button>"+
                        "</div>"
                }).draw( false );

            },
            error:(e)=>{
                //console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }

        });
    });

    //final delete schedule
    $('.delsch').click( ()=> {

        let idsch=$('#sch-id').val();

        $.ajax({
            method:'DELETE',
            url:base_url+'/api/schedules/'+idsch,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=>{
                // console.log(res)
                toastr.success('schedule successfully deleted.');
                tableList.rows('.selected').remove().draw()
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

    //final edit schedule~
    $('.editschedule').click(function () {

        let edID= $('#editIdsch').val();

        $.ajax({
            method:'PUT',
            url:base_url+'/api/schedules/'+edID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                name:$('#name').val(),
                address:$('#pac-input').val(),
                lat:$('#latitude').val(),
                lang:$('#langtitude').val(),
                maxHourWeek:$('#hours').val(),
                ipAddress:$('#ip-address').val()
            }),
            success:(res)=>{

                toastr.success('Schedule successfully Edited.');
                tableList.cell('.selected .Schedules').data(res['name']);
                tableList.cell('.selected .Address').data(res['address']);
                tableList.draw();


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

    $('#modal-addsch').on('hidden.bs.modal', function(){
        $('#edit-schedule').hide();
        $('#add-schedule').show();

        $('.colorpicker-guide').css({'top':'122px','left':'2px'});

        $(this).find('form')[0].reset();
        gmap = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -33.8688, lng: 151.2195},
            zoom: 13
        });
    });

});
let edit_schedule= (id)=>{
    $('#add-schedule').hide();
    $('#edit-schedule').show();

    $.ajax({
        method:'GET',
        url:base_url+'/api/schedules/'+id,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log(res)
            $('#name').val(res['name']);
            $('#pac-input').val(res['address']);
            $('#editIdsch').val(res['id']);
            $('#langtitude').val(res['lang']);
            $('#latitude').val(res['lat']);
            $('#hours').val(res['maxHourWeek']);

            gmap.setCenter(new google.maps.LatLng(res['lat'],res['lang']));
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(res['lat'],res['lang']),
                map: gmap,
                title: res['name']
            });





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

let delete_schedule= (id,name) =>{
    //schedule id for delete schedule

    let delId=id;
    let schName=name;
    $('#sch-id').val(delId);
    $('.titleForDel').text('delete '+schName+' schedule ?');

};
