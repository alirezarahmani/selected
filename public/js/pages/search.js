let tok_=localStorage.getItem('token');
$(document).ready(function () {

//start search.js

if(tok_ == null){
    window.location.href = base_url2+"/login";
}

$('.searchBtn').on('click',function (e) {
    e.preventDefault();
    window.location.href = base_url2+"/createplace";
});

//start joinBusiness.js
let businessId=localStorage.getItem('selBus');
let token1=localStorage.getItem('token');

if (businessId) {
    $.ajax({
        url: base_url+'/api/businesses/'+ businessId,
        method: 'GET',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${token1}`,
    
        },
        success: function(r){
    
            console.log(r);
           $('.nameBus').text(r['name']);
           $('.addBus').text(r['address']);
           $('#selectedBusinessId').val(r['@id']);
    
        },
        error:function(e) {
    
            // console.log(e)
            //expire jwt token
            if (e.status == 401) {
                window.location.href = base_url2 + "/login";
            }
        }
    });
}

$('.businessReq').on('click',function () {


    $.ajax({
        url: base_url+'/api/business_requests',
        method: 'POST',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${token1}`,

        },
        data:JSON.stringify({
            business:$('#selectedBusinessId').val()
        }),
        success: function(r){

            console.log('business Request response',r);
            $('.content-join').hide();
            $('.businessReq').hide();
            $('.cancelReq').hide();
            $('.content-requested').show();
            $('.businessReq').text('REQUESTED TO JOIN THIS WORKPLACE');
            $('#busReqId').val(r['id']);
            $('.cancelReq').show();

        },
        error:function(e) {

            // console.log(e)
            //expire jwt token
            if(e.status == 401){
                window.location.href = base_url2+"/login";
            }
            toastr.error('Error occurred get schedules');
        }
    });
});

$('.cancelReq').on('click',function () {

    let cancelId= $('#busReqId').val();

    $.ajax({
        url: base_url+'/api/business_requests/'+ cancelId,
        method: 'DELETE',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${token1}`,

        },
        success: function(r){
            console.log(r);
            window.location.href = base_url2 + "/search";

        },
        error:function(e) {

            // console.log(e)
            //expire jwt token
            if (e.status == 401) {
                window.location.href = base_url2 + "/login";
            }
        }
    });

});

//end joinBusiness.js
//log out clear storage
    $('.logouting').on('click',function () {
        localStorage.clear();
        sessionStorage.clear();
    });
});

async function getData (url) {
    let response = await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': "application/json",
            'Authorization': `Bearer ${tok_}`
        },
    });
    let Schedule = await response.json();
    return Schedule;
};

let search = null;
let value;
let filter_place = document.getElementById('filterWorkplace');
let loadingBox = document.getElementById('loading-box');

if (filter_place) {
    filter_place.addEventListener('keydown', (e) => {
        loadingBox.style.display = 'block'
    
        setTimeout(() => {
            value = e.srcElement.value.toLowerCase();
        }, 1000);
        clearTimeout(search);
        search = setTimeout(fireEvent, 1000);
    
    })
    
    function fireEvent() {
        getData(base_url + '/api/businesses/search?address='+ value)
        .then((data) => {
            loadingBox.style.display = 'none';
            console.log(data['hydra:totalItems']);

            let boxPlace = document.getElementById('showWorkplace');
            if (boxPlace != '') {
                boxPlace.innerHTML = '';
            }

            if(value && data['hydra:member'].length != 0) {
                data['hydra:member'].forEach((place) => {
                    let a = document.createElement('a');

                    a.innerHTML += `
                                          
                        <li class="border-bottom py-2 li-show-box">
                            <div class="px-4">
                                <div class="row">
                                    <div class="col-2">
                                        <img class="img-fluid img-circle mt-1" src='${place['image'] == null ? 'img/workplaceImg.png' : place['image']['filePath']}'>
                                    </div>
                                    <div class="col-10">
                                        <strong class="text-capitalize font-size-16">${place.name}</strong>
                                        <br>
                                        <span class="text-capitalize">owner: ${place.owner}</span>
                                    </div>
                                </div>
    
                            </div>
                        </li>
                        
                    `
                    boxPlace.appendChild(a);
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        localStorage.setItem('selBus', place.id);
                        window.location.href = base_url2+"/joinBusiness";
                    })
                });
            }else if(value && data['hydra:member'].length == 0){
                let a = document.createElement('a');
                let li = document.createElement('li');
                li.classList.add('border-bottom', 'py-2', 'li-show-box')
                li.innerHTML += `      
                        <div class="px-4">
                            <div class="row">
                                <div class="col-2">
                                    <img class="img-fluid img-circle mt-1" src='img/workplaceImg.png'>
                                </div>
                                <div class="col-10">
                                    <strong class="text-capitalize font-size-16">No workplace</strong>
                                    <br>
                                    <span class="text-capitalize">owner: No owner</span>
                                </div>
                            </div>

                        </div>
                `
                boxPlace.appendChild(li);
            }
    
        })
        .catch(err => {
            loadingBox.style.display = 'none';
            console.log(err)
        });
    }
}
