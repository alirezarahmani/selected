$(document).ready(function () {
    //check token if not set redirect to login page
    let tok = localStorage.getItem('token');
    if (tok == null) {
        window.location.href = base_url2 + "/login";
    }

    //get coworker list
    $.ajax({
        method:'get',
        url:base_url+'/api/users',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,
        },
        success:(res)=>{
            // console.log('new list',res['hydra:member']);
            let users=res['hydra:member'] ;
            let userArray=[];
            users.map( (user)=> {
                console.log('user',user);
                if(user['privacy'] === false){

                    userArray.push(' <div class="col-sm-4 border rounded mt-2 p-2" style="background-color: white;">\n' +
                        '                                    <div class="c-item"><img src="../../img/pic4.png" style="height: 50px;width: 50px;"></div>\n' +
                        '                                    <div class="c-item-content ml-1">\n' +
                        '                                        <div class="font-weight-bold">'+user['firstName']+' '+user['lastName']+'</div>\n' +
                        '                                        <div class="font-weight-bold">'+user['email']+'</div>\n' +
                        '                                    </div>\n' +
                        '                                </div>');
                }else{

                    userArray.push(' <div class="col-sm-4 border rounded mt-2 p-2" style="background-color: white;">\n' +
                        '                                    <div class="c-item"><img src="../../img/pic4.png" style="height: 50px;width: 50px;"></div>\n' +
                        '                                    <div class="c-item-content ml-1">\n' +
                        '                                        <div class="font-weight-bold">'+user['firstName']+' '+user['lastName']+'</div>\n' +
                        '                                    </div>\n' +
                        '                                </div>');
                }

            });

            $('.coworker-list').html(userArray);

        }
    });

});
