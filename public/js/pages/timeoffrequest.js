let tableListTimeOff;
//check token if not set redirect to login page
let tok=localStorage.getItem('token');
if(tok == null){
    window.location.href = base_url2+"/login";
}

let personRole=localStorage.getItem('role');
let userEmail=localStorage.getItem('email');


$(document).ready(function () {


    //get time off requests list
     tableListTimeOff=$("#timeoffreq").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[4]  }
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
                 text:      'Request Time Off',
                 action: function ( e, dt, node, config ) {
                     $('#modal-reqoff').modal('show');

                 },
                 attr:  {
                     class: 'btn btn-outline-secondary btn-sm mt-2 ml-3'
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
            { data: 'Employee' ,className:'Employee'},
            { data: 'Type' ,className:'Type'},
            { data: 'Status' ,className:'Status'},
            { data: 'Details' ,className:'Detail'},
            { data: 'Requested On' ,className:'RequestedOn'}
        ],
        "ajax":{
            'type':'get',
            'url':base_url+'/api/time_off_requests',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
                console.log('json',json);
                let newArray=[];
                if (json[0]){
                    json.map((off)=>{
                        console.log('off',off)
                        if(personRole === 'employee'){
                            if(off['userID']['email'] === userEmail || off['userCreatorId']['email'] === userEmail){
                                newArray.push(off);
                            }
                        }else{
                            newArray.push(off);
                        }
                    });
                }

                let data=newArray.map(user=>{
                    let timeoffdata={};

                    let logs=user["timeoffLogs"];
                    let by =logs[(logs.length)-1];
                    let color="GoldenRod ";
                    let sta="Pending Approval";
                    if(by['status'] === 'accepted') {
                        color = "green";
                        sta='accepted';
                    }else if (by['status'] === 'denied'){
                        color="red";
                        sta= 'denied';
                    }else if (by['status']=== 'canceled'){
                        color="red";
                        sta= 'canceled';
                    }

                    let sd=new Date(by['date']);
                    let statusDate=(sd.toString()).substring(4,10);

                    let cAt=new Date(user["createdAt"]);
                    let createdAt=(cAt.toString()).substring(0,11)+','+cAt.getFullYear();
                    let createdAtTime=(cAt.toTimeString()).substring(0,5);

                    let more;
                    if(user['userID']['id'] === user['userCreatorId']['id']){
                        more='';
                    }else{
                        more=' (by '+by['creatorId']['firstName']+' '+(by['creatorId']['lastName']).charAt(0)+'.)';
                    }

                    //console.log(createdAt,createdAtTime)

                    let detailDate;
                    let st=user['startTime'];
                    let et=user['endTime'];

                    if(st.substring(0,10) === et.substring(0,10)){

                        if(st.substring(11,16) === '00:00' && et.substring(11,16) === '23:59'){
                            detailDate=((new Date(user['startTime'])).toString()).substring(0,10)+','+((new Date(user['startTime'])).toString()).substring(10,15);
                        }else{
                            detailDate=((new Date(user['startTime'])).toString()).substring(0,10)+','+((new Date(user['startTime'])).toString()).substring(10,15)+' from '+ st.substring(11,16)+'-'+et.substring(11,16);

                        }
                    }else{
                        detailDate=((new Date(user['startTime'])).toString()).substring(0,10)+'-'+((new Date(user['endTime'])).toString()).substring(4,15);

                    }


                    timeoffdata['Employee']= '<img src="../../img/pic4.png" style="width: 20px;height: 20px;"/>' +user["userID"]["firstName"]+' '+user["userID"]["lastName"]+more;
                    timeoffdata['Type']=user["type"]+' Time Off';
                    timeoffdata['Status']='<div  style="color:'+color+' ">'+sta+'</div>'+
                                       '<span>by '+by['creatorId']['firstName']+' '+by['creatorId']['lastName']+' On '+statusDate+'</span>';
                    timeoffdata['Details']=detailDate;
                    timeoffdata['Requested On']=createdAt+'@'+createdAtTime+
                        '<button  onclick="detail_timeoffReq(\''+user['id']+'\',\''+user['userID']['id']+'\')" class="btn btn-sm"><i class="fas fa-chevron-right"></i></button>';

                    return timeoffdata;
                });
                return data;
            }
        }

    });


    //class for search box
    $('input[type=search]').addClass('form-control form-control-sm');

});

let detail_timeoffReq=(id,userid)=>{
    //redirect to detail page

    localStorage.setItem("toid", id);
    localStorage.setItem("userIdto", userid);
    location.replace(base_url2+'/timeoff-detail');
};
