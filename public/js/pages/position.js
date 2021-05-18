//check token if not set redirect to login page
let tok=localStorage.getItem('token');
if(tok == null){
    window.location.href = base_url2+"/login";
}

$(document).ready(function () {

    //load position list in datatable
    let tableList=$("#positions").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[0,2]  }
        ],
        "scrollY":        "300px",
        "scrollCollapse": true,
        "paging":         false,
        "select": {
            style: 'single'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                text:      'Add Position',
                action: function ( e, dt, node, config ) {
                    $('#modal-addpos').modal('show');

                },
                attr:  {
                    class: 'btn btn-custbl btn-sm ml-3 mt-2 addposition'
                }
            }

        ],
        "columns": [
            { data: 'Color' ,className:'Color'},
            { data: 'Positions',className:'Positions' },
            { data: 'Actions',className:'Actions' }
        ],
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        "ajax":{
            'type':'get',
            'url':base_url+'/api/positions',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
                console.log(json);
                let data=json.map(obj=>{
                    let pos={};
                    if(obj['favorite'] == false){

                        pos['Color']='<div style="background-color: '+obj['color']+'" class="circle-color"></div>';
                        pos['Positions']=obj["name"];
                        pos['Actions']=
                            " <div class='btn-group'>" +
                            ' <button onclick="delete_position(\''+obj['id']+'\',\''+obj['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delpos">'+
                            "<i class='far fa-trash-alt'></i>" +
                            " </button>"+
                            " <button onclick='edit_position("+obj['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addpos'>" +
                            "<i class='fas fa-pencil-alt'></i>" +
                            " </button>"+
                            "<button onclick='favorite_position("+obj['id']+",false)' type='button' data-id="+obj['id']+" title='favorite' class='btn btn-default btn-sm favpos'><i class='far fa-heart'></i></button>"+
                            "</div>";
                    }else if(obj['favorite'] == true){

                        pos['Color']='<div style="background-color: '+obj['color']+'" class="circle-color"></div>';
                        pos['Positions']=obj["name"];
                        pos['Actions']=
                            " <div class='btn-group'>" +
                            ' <button onclick="delete_position(\''+obj['id']+'\',\''+obj['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delpos">'+
                            "<i class='far fa-trash-alt'></i>" +
                            " </button>"+
                            " <button onclick='edit_position("+obj['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addpos'>" +
                            "<i class='fas fa-pencil-alt'></i>" +
                            " </button>"+
                            "<button onclick='favorite_position("+obj['id']+",true)' type='button' data-id="+obj['id']+" title='favorite' class='btn btn-default btn-sm favpos'><i class='fas fa-heart'></i></button>"+
                            "</div>";
                    }

                    return pos;
                });
                return data;
            }
        }
    });

    //class for search box
    $('input[type=search]').addClass('form-control form-control-sm');

    //onclick table add class selected
    $('#positions tbody').on( 'click', 'tr', function () {
        $('#schedules tbody tr').removeClass('selected');
        $(this).addClass('selected');
    } );

    //empty modal
    $('#modal-addpos').on('hidden.bs.modal', function (e) {
        $('#edit-position').hide();
        $('#add-position').show();
        $(this)
            .find("input[type=text],textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();
        $('.fa-square').css('color','rgb(202, 204, 213)');
        $('.colorpicker-guide').css({'top':'122px','left':'2px'});


    });


    //color picker with addon
    $('.my-colorpicker2').colorpicker();

    $('.my-colorpicker2').on('colorpickerChange', function(event) {
        $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
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
            $('.addpos').attr('disabled', 'disabled');
        } else {
            $('.addpos').removeAttr('disabled');
        }
    });

    //add positions
    $('#add-position').click(()=>{
        let data={
            name:$('#name').val(),
            color:document.getElementById('changeColorForBG').style.backgroundColor
        };
        $.ajax({
            method:'post',
            url:base_url+'/api/positions',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(data),
            success:(res)=>{
                console.log(res)
                toastr.success('Position Successfully Added.');

                tableList.row.add( {
                    "Color":'<div style="background-color: '+res['color']+'" class="circle-color"></div>',
                    "Positions":res["name"],
                    "Actions":  " <div class='btn-group'>" +
                    ' <button onclick="delete_position(\''+res['id']+'\',\''+res['name']+'\')" type="button"  title="Delete" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal-delpos">'+
                    "<i class='far fa-trash-alt'></i>" +
                    " </button>"+
                    " <button onclick='edit_position("+res['id']+")' type='button'  title='Edit' class='btn btn-default btn-sm' data-toggle='modal' data-target='#modal-addpos'>" +
                    "<i class='fas fa-pencil-alt'></i>" +
                    " </button>"+
                    "<button onclick='favorite_position("+res['id']+",false)' type='button' data-id="+res['id']+" title='favorite' class='btn btn-default btn-sm favpos'><i class='far fa-heart'></i></button>"+
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

    //final delete position
    $('.delpos').click(function () {

        let idpos=$('#pos-id').val();

        $.ajax({
            method:'DELETE',
            url:base_url+'/api/positions/'+idpos,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success:(res)=>{

                toastr.success('Position Successfully Deleted.');
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

    //final edit position
    $('.editposition').click(function () {

       let edID= $('#editIdpos').val();
        $.ajax({
            method:'PUT',
            url:base_url+'/api/positions/'+edID,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify({
                name:$('#name').val(),
                color: document.getElementById('changeColorForBG').style.backgroundColor
            }),
            success:(res)=>{
                console.log(res)

                toastr.success('Position successfully Edited.');
                tableList.cell('.selected .Color').data(' <div style="background-color: '+res['color']+'" class="circle-color"></div>');
                tableList.cell('.selected .Positions').data(res['name']);
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

let edit_position= (id)=>{

    $.ajax({
        method:'GET',
        url:base_url+'/api/positions/'+id,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            console.log(res)
            $('#name').val(res['name']);
            document.getElementById('changeColorForBG').style.backgroundColor = res['color'];
            // $('#showColor').style.background-color(res['color']);
            $('#editIdpos').val(res['id']);
            $('.fa-square').css('color',res['color']);
            $('#add-position').hide();
            $('#edit-position').show();



        },
        error:(e)=>{
            // console.log(e)
            toastr.error(e['responseJSON']['hydra:description']);
        }


    });

};

let delete_position= (id,name) =>{

    let delId=id;
    let posName=name;
    $('#pos-id').val(delId);
    $('.titleForDel').text('delete '+posName+' position ?');

};

let favorite_position=(id,fav)=>{
    console.log(id,fav)

    let favorite;
    //check icon class name
    if(fav == false){
        console.log('false');
        favorite=true;
    }else{
        console.log('true');
        favorite=false;
    }
    $.ajax({
        method:'PUT',
        url:base_url+'/api/positions/'+id,
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        data:JSON.stringify({
            favorite:favorite
        }),
        success:(res)=>{
            console.log(res)
            let idp=res['id'];
            if(res['favorite'] == true){
                $('.favpos[data-id="'+idp+'"] i').addClass('fas');
                $('.favpos[data-id="'+idp+'"] i').removeClass('far');
            }else{
                $('.favpos[data-id="'+idp+'"] i').addClass('far');
                $('.favpos[data-id="'+idp+'"] i').removeClass('fas');
            }
            // toastr.success('Position successfully deleted.');

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