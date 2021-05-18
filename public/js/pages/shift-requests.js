//check token if not set redirect to login page
let tok=localStorage.getItem('token');
if(tok == null){
    window.location.href = base_url2+"/login";
}
$(document).ready(function () {

    //get shift requests list
    let tableList=$("#shiftreq").DataTable({
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

        "columns": [
            { data: 'Employee' ,className:'Employee'},
            { data: 'Request Type' ,className:'Type'},
            { data: 'Status' ,className:'Status'},
            { data: 'Shift' ,className:'Shift'},
            { data: 'Requested On' ,className:'RequestedOn'}
        ],
        "ajax":{
            'type':'get',
            'url':base_url+'/api/shift_requests',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
                console.log('json', json);
                let data = json.map(request => {
                    let reqdata = {};

                    let logs = request["shiftRequestLogs"];
                    let by = logs[(logs.length) - 1];
                    console.log('by', by)

                    let shift1 = ((new Date(request['requesterShift']['startTime'])).toString()).substring(0, 15) + ' |' + (request['requesterShift']['startTime']).substring(11, 16) + '-' + (request['requesterShift']['endTime']).substring(11, 16);
                    let shift2 = request['requesterShift']['positionId']['name'] + ' at ' + request['requesterShift']['scheduleId']['name'];

                    let cAt = new Date(request["date"]);
                    let createdAt = (cAt.toString()).substring(0, 11) + ',' + cAt.getFullYear();
                    let createdAtTime = (cAt.toTimeString()).substring(0, 5);

                    let more;
                    if (request["requesterShift"]["ownerId"]["@id"] == request['requesterId']['@id']) {
                        more = '';
                    } else {
                        more = ' (by ' + request['requesterId']['firstName'] + ' ' + (request['requesterId']['lastName']).charAt(0) + '.)';
                    }
                    let type;
                    if (request["type"] == 'replace') {
                        type = 'Drop';
                    } else if (request['type'] == 'swap') {
                        type = 'Swap';
                    }


                    let status1, status2, color;
                    if (new Date(request['requesterShift']['startTime']) < new Date(Date.now())) {
                        status1 = 'EXPIRE';
                        status2 = 'On ' + (new Date(request['date']).toString()).substring(4, 10);
                        color = "red";
                    } else {
                        if (request['status'] === 'approve') {
                            status1 = 'PENDING ACCEPTANCE';
                            status2 = 'from recipients';
                            color = "#666";
                        } else if (request['status'] === 'denied') {
                            status1 = 'ACCEPT';
                            status2 = by['creatorId']['firstName'] + ' ' + by['creatorId']['lastName'] + ' On ' + (new Date(by['requestDate']).toString()).substring(4, 10);
                            color = "green";

                        } else if (request['status'] === 'accept') {
                            status1 = 'ACCEPT';
                            status2 = by['creatorId']['firstName'] + ' ' + by['creatorId']['lastName'] + ' On ' + (new Date(by['requestDate']).toString()).substring(4, 10);
                            color = "green";
                        } else if (request['status'] === 'cancel') {
                            status1 = 'CANCELED';
                            status2 = by['creatorId']['firstName'] + ' ' + by['creatorId']['lastName'] + ' On ' + (new Date(by['requestDate']).toString()).substring(4, 10);
                            color = "red";
                        } else if (request['status'] === 'decline') {
                            status1 = 'DECLINE';
                            status2 = by['creatorId']['firstName'] + ' ' + by['creatorId']['lastName'] + ' On ' + (new Date(by['requestDate']).toString()).substring(4, 10);
                            color = "red";
                        } else if (request['status'] === 'pendingAccept') {
                            status1 = 'PENDING APPROVE';
                            status2 = 'from management';
                            color = "gray";
                        }
                    }

                    reqdata['Employee'] = '<img src="../../img/pic4.png" style="width: 20px;height: 20px;"/>' + request["requesterShift"]["ownerId"]["firstName"]+' '+request["requesterShift"]["ownerId"]["lastName"]+more;
                    reqdata['Request Type'] = type;
                    reqdata['Status'] = '<div  style="color:'+color+' ">'+status1+'</div>' +
                                        '<div>'+status2+'</div>';
                    reqdata['Shift'] = '<div>'+shift1+'</div>'+
                                        '<div>'+shift2+'</div>';
                    reqdata['Requested On'] = createdAt +'@'+ createdAtTime +
                        '<button  onclick="detail_shiftReq(\'' + request['id'] + '\')" class="btn btn-sm"><i class="fas fa-chevron-right"></i></button>';

                    return reqdata;
                });
                return data;
            }
        }

    });

    //class for search box
    $('input[type=search]').addClass('form-control form-control-sm');

});

let detail_shiftReq=(id)=> {
    //redirect to detail page
    localStorage.setItem("shRreId", id);
    location.replace(base_url2 + '/shiftrequest-detail');
}

