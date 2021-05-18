//check token if not set redirect to login page
let tok=localStorage.getItem('token');
if(tok == null){
    window.location.href = base_url2+"/login";
}

$(document).ready(function () {

    //load job sites list in datatable
    let tableList=$("#jobsites").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[0,4]  }
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
        dom: 'Bfrtip',
        buttons: [
            {
                text:      'Add Job Site',
                action: function ( e, dt, node, config ) {
                    $('#modal-addjsite').modal('show');

                },
                attr:  {
                    class: 'btn btn-custbl btn-sm ml-3 mt-2 addjobsite'
                }
            },
            {
                extend: 'excel',
                text: 'Export',
                attr:  {
                    class: 'btn btn-sm btn-outline-secondary mt-2'
                }
            }

        ],
        "columns": [
            { data: 'Color',className:"Color" },
            { data: 'Job Sites',className:"Name" },
            { data: 'Address',className:"Address" },
            { data: 'Schedules',className:"Schedules" },
            { data: 'Actions',className:'Actions' }
        ],
        "ajax":{
            'type':'get',
            'url':base_url+'/api/job_sites',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
                console.log(json);
                let data=json.map(obj=>{
                    let jsite={};
                    jsite['Color']= ' <div style="background-color: '+obj['color']+'" class="circle-color"></div>' ;
                    jsite['Job Sites']=obj["name"];
                    jsite['Address']=obj["address"];
                    jsite['Schedules']=obj['schedules'].map(sch=>sch['name']).join(',');
                    jsite['Actions']=
                        " <div class='btn-group'>" +
                        ' <button onclick="delete_jobsite(\''+obj['id']+'\',\''+obj['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-deljob">'+
                        "<i class='far fa-trash-alt'></i>" +
                        " </button>"+
                        " <button onclick='edit_jobsite("+obj['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addjsite'>" +
                        "<i class='fas fa-pencil-alt'></i>" +
                        " </button>"+
                        "</div>";

                    return jsite;
                });
                return data;
            }
        }


    });

    //class for search box
    $('input[type=search]').addClass('form-control form-control-sm');

    //onclick table add class selected
    $('#jobsites tbody').on( 'click', 'tr', function () {
        $('#jobsites tbody tr').removeClass('selected');
        $(this).addClass('selected');
    } );

    //color picker with addon
    $('.select-color').colorpicker();
    $('.select-color').on('colorpickerChange', function(event) {
        $('.select-color .fa-square').css('color', event.color.toString());
    });

    //enable add button if input is not empty
    $('#name').keyup(function() {

        var empty = false;
        $('#name').each(function() {
            if ($(this).val().length == 0) {
                empty = true;
            }
        });

        if (empty) {
            $('.addjsite').attr('disabled', 'disabled');
        } else {
            $('.addjsite').removeAttr('disabled');
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
                // console.log(el['@id']);
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

    //final delete job site
    $('.deljob').click(function () {

        let idjob=$('#job-id').val();

        $.ajax({
            method:'DELETE',
            url:base_url+'/api/job_sites/'+idjob,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=>{
                // console.log(res)
                toastr.success('Job Site Successfully Deleted.');
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

    //add job site
    $('#add-jobsite').click(function () {
        let data={
            name:$('#name').val(),
            address:$('#pac-input').val(),
            lat:$("#latitude").val(),
            lang:$("#langtitude").val(),
            color: document.getElementById('changeColorForBG').style.backgroundColor,
            note:$("#description").val(),
            schedules:$(".sch-list").val(),
        };
        $.ajax({
            method:'post',
            url:base_url+'/api/job_sites',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                //console.log(res)
                toastr.success('Job Site Successfully Added.');
                tableList.row.add( {
                    "Color":' <div style="background-color: '+res['color']+'" class="circle-color"></div>',
                    "Job Sites":res["name"],
                    "Address":res["address"],
                    "Schedules":res['schedules'].map(sch=>sch['name']).join(','),
                    "Actions": " <div class='btn-group'>" +
                        ' <button onclick="delete_jobsite(\''+res['id']+'\',\''+res['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-deljob">'+
                        "<i class='far fa-trash-alt'></i>" +
                        " </button>"+
                        " <button onclick='edit_jobsite("+res['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addjsite'>" +
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

    //final edit jobsite
    $('.editjobsite').click(function () {

        let edID= $('#editIdjob').val();
        let data={
            name:$('#name').val(),
            address:$('#pac-input').val(),
            lat:$("#latitude").val(),
            lang:$("#langtitude").val(),
            // color:$("#color").val(),
            color: document.getElementById('changeColorForBG').style.backgroundColor,
            note:$("#description").val(),
            schedules:$(".sch-list").val(),
        };
        console.log(data);
        $.ajax({
            method:'PUT',
            url:base_url+'/api/job_sites/'+edID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                console.log(res);
                toastr.success('Job Site Successfully Edited.');
                setTimeout(()=> {
                    tableList.cell('.selected .Color').data(' <div style="background-color: '+res['color']+'" class="circle-color"></div>');
                    tableList.cell('.selected .Name').data(res['name']);
                    tableList.cell('.selected .Address').data(res['address']);
                    tableList.cell('.selected .Schedules').data(res['schedules'].map(sch=>sch['name']).join(','));
                    tableList.draw();
                    $('#schList').val(null).trigger('change');
                    $('#editIdjob').val('');
                },1000)


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

    $('#modal-addjsite').on('hidden.bs.modal', function(){
        $('.select-color .fa-square').css('color', 'rgb(202, 204, 213)');
        $('#edit-jobsite').hide();
        $('#add-jobsite').show();
        $(this).find('form')[0].reset();
        $('#schList').val(null).trigger('change');
        $('.colorpicker-guide').css({'top':'122px','left':'2px'});
        gmap = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -33.8688, lng: 151.2195},
            zoom: 13
        });
    });

});


let edit_jobsite= (id)=>{

    $('#add-jobsite').hide();
    $('#edit-jobsite').show();
    $.ajax({
        method:'GET',
        url:base_url+'/api/job_sites/'+id,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log(res)
            $('#name').val(res['name']);
            // $('#color').val(res['color']);
            document.getElementById('changeColorForBG').style.backgroundColor = res['color'];
            $('.fa-square').css('color',res['color']);
            $('#editIdjob').val(res['id']);
            $('#langtitude').val(res['lang']);
            $('#latitude').val(res['lat']);
            $('#description').val(res['note']);
            $('#pac-input').val(res['address']);

            let arr=[];
            res['schedules'].map(sch=>{
                // console.log(sch)
                arr.push(sch["@id"]);

            });
            console.log('array',arr)
            $('#schList').val(arr).trigger('change');

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

let delete_jobsite= (id,name) =>{

    let delId=id;
    let jobName=name;
    $('#job-id').val(delId);
    $('.titleForDel').text('delete '+jobName+' job site ?');

};

let setColors = (e) => {
    document.getElementById('changeColorForBG').style.backgroundColor = e.srcElement.style.backgroundColor;
}