let tok=localStorage.getItem('token');
let optSelect = document.getElementById('optSelectCountry');
let dataCountry;
let countryName;
let alpha2Country;
let countryId;

$(document).ready(function () {
    //check token if not set redirect to login page
    if(tok == null){
        window.location.href = base_url2+"/login";
    }
    let busId=localStorage.getItem('selBus');
    let role=localStorage.getItem('role');
    if(role !== 'employee'){
        $('#accid').removeAttr('disabled')
        $('#bus-timezone').removeAttr('disabled')
        $('#starOfWeek').removeAttr('disabled')
    }else {
        window.location.href = base_url2+"/404";
    }

    $('#bus-timezone').select2();
    //get business information
    $.ajax({
        url: base_url + '/api/businesses/' + busId,
        method: 'GET',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,

        },
        success: function (r) {
            console.log('sdf',r)
            $('#businessIdEdit').val(r['id']);
            $('#accname').val(r['name']);
            $('#busListing').val(r['address']);
            $('#bus-timezone').val(r['timeZone']).trigger('change');
            $('#formatTime').val(r['timeFormat']);
            $('#maxTimeOff').val(r['maxDaysTimeOff']);
            $('#maxTimeOffHour').val(r['maxHourTimeoffPerDay']);
            $('#pac-input').val(r['address']);
            $('#busLocation').val(r['location']);
            if (r['image'] != null) {
                $("#set-profile").attr("src", r['image']['filePath']);
            }else {
                $("#set-profile").attr("src", 'img/workplaceImg.png');
            }
            
           let locArr= r['location'].split(",");
           $('#latitude').val(locArr[0]);
           $('#langtitude').val(locArr[1]);
            gmap.setCenter(new google.maps.LatLng(locArr[0],locArr[1]));
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locArr[0],locArr[1]),
                map: gmap,
                title: r['name']
            });

            if(r['approveTimeoffEmp'] == true){
                $('#approveEmp').prop("checked", true);
            }

            if(r['setPreferred'] == true){
                $('#setPref').prop("checked", true);
            }

            if(r['seeCoworkerSchedule'] == true){
                $('#viewCoworker').prop("checked", true);
            }

            if(r['seePositionSchedule'] == true){
                $('#seePosition').prop("checked", true);
            }

            if(r['shiftConfirmation'] == true){
                $('#requireShiftConfirm').prop("checked", true);
            }

            if(r['availability'] == true){
                $('#showAvailability').prop("checked", true);
            }




        },
        error: function (e) {

            // console.log(e)
            //expire jwt token
            if (e.status == 401) {
                window.location.href = base_url2 + "/login";
            }
            toastr.error(e['responseJSON']['hydra:description']);
        }
    });

    //get location info google map modal
    $('#save-location').on('click',function () {
        alpha2Country = optSelect.options[optSelect.selectedIndex].value;
        dataCountry.forEach(country => {
            if (country.name === alpha2Country) {
                countryName = country.longName;
                countryId = country['@id'];
            }  
        });
        
        if (localStorage.getItem('googleLocation')) {
            let localtion = JSON.parse(localStorage.getItem('googleLocation'));
            let shortName;
            localtion['address_components'].forEach(item => {
                if (item.short_name.indexOf(alpha2Country) != -1 ) {
                shortName = true;
                document.getElementById('save-location').setAttribute('data-dismiss','modal');
                setTimeout(() => {
                    document.getElementById('save-location').removeAttribute('data-dismiss','modal')
                }, 1000);
                $('#busLocation').val(($('#latitude').val()).toString()+','+($('#langtitude').val()).toString());
                $('#busListing').val($('#pac-input').val())
                toastr.success(`Update location.`);
            }

        })
        setTimeout(() => {
            if (shortName != true || !shortName) {
                toastr.error(`This address does not exist in ${countryName}.`);
            }
        }, 1000);
    }


    });

    $('input[name="approveEmp"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });
    $('input[name="setPref"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });
    $('input[name="viewCoworker"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });
    $('input[name="seePosition"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });
    $('input[name="requireShiftConfirm"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });
    $('input[name="showAvailability"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });


    // timezone list
    $.ajax({
        url: base_url+'/api/get_timezone',
        method: 'GET',
        contentType: "application/json",
        headers: {
            'Authorization': `Bearer ${tok}`,

        },
        success: function(r){

            r.forEach(function (el) {
                // console.log(el)
                $('select.seltimezone').append("<option value="+el+">"+el+"</option>");

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

    //edit business information
    $('#save-busInfo').on('click',function () {

        let maxday=$('#maxTimeOff').val()==''?0:$('#maxTimeOff').val();
        let currency = document.getElementById('currencyOpt');
        let valCurrency = currency.options[currency.selectedIndex].value;
        let businessImgId = localStorage.getItem('businessImgId')
        let busId=$('#businessIdEdit').val();
        let dataEdit={
            address: $('#busListing').val(),
            name: $('#accname').val(),
            timeFormat: $('#formatTime').val(),
            timeZone: $('#_bus-timezone').val(),
            currency: valCurrency,
            maxDaysTimeOff: parseInt(maxday),
            maxHourTimeoffPerDay:$('#maxTimeOffHour').val(),
            setPreferred:  $('#setPref').prop("checked"),
            seePositionSchedule:  $('#seePosition').prop("checked"),
            seeCoworkerSchedule:  $('#viewCoworker').prop("checked"),
            shiftConfirmation:  $('#requireShiftConfirm').prop("checked"),
            approveTimeoffEmp:  $('#approveEmp').prop("checked"),
            availability:  $('#showAvailability').prop("checked"),
            location: $('#busLocation').val(),
            country: countryId,
            image: businessImgId
        };
        console.log('data edit',dataEdit)
        $.ajax({
            method:'PUT',
            url:base_url+'/api/businesses/'+busId,
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            data:JSON.stringify(dataEdit),
            success:(res)=>{
                console.log('res',res);

                toastr.success('Business Information Successfully Updated.');
             localStorage.setItem('setPreferred',res['setPreferred']);
             localStorage.setItem('seeOtherPosition',res['seePositionSchedule']);
             localStorage.setItem('seeCoworkerSchedule',res['seeCoworkerSchedule']);
             localStorage.setItem('shiftConfirmation',res['shiftConfirmation']);
             localStorage.setItem('availability',res['availability']);

            },
            error:(e)=>{
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });
    })
});

async function getData (url) {
    let response = await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': "application/json",
            'Authorization': `Bearer ${tok}`
        },
    });
    let Schedule = await response.json();
    return Schedule;
};
getData(base_url+'/api/currencies')
.then(data => {
    let options = data['hydra:member'];
    let optCurrency = document.getElementById('currencyOpt');
    options.forEach(opt => {
        // console.log(opt);
        let el = document.createElement("option");
        el.textContent = opt.name;
        el.value = opt['@id'];
        optCurrency.appendChild(el);
    })

})
.catch(err => console.log(err))

if (optSelect) {

    async function getAlpha_2Country(url){
        // let response = await fetch(`https://restcountries.eu/rest/v2/all`);
        let response = await fetch(url, {
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${tok}`,
            },
        });
        let data = await response.json();
        return data;
    }

    $('.alpha_2Country').select2();

    getAlpha_2Country(base_url+ '/api/available_countries')
    .then(data => {
        // console.log(data);
        let optGroup = document.createElement('optgroup');
        dataCountry = data['hydra:member'];
        data['hydra:member'].forEach(conuntry => {
            // console.log(data);
            optGroup.label = "alpha 2 code country";
            let opt = document.createElement('option');
            opt.innerHTML = `${conuntry.name} (${conuntry.longName})`;
            opt.value = conuntry.name;
            optGroup.appendChild(opt);
            optSelect.appendChild(optGroup);
        });
    })
    .catch(err => console.log(err))

}

let loadFile = (event) => {
    // console.log(event);
    let postImage = async (url, formData) => {
        let response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        let uploadImage = await response.json();
        return uploadImage;
    }
    
    console.log(event.target.files[0])
    
    let formData = new FormData();
    formData.append("objectable","business");
    formData.append("file", event.target.files[0], 'image.png');
    postImage(`${base_url}/api/media`, formData)
    .then(data => {
        console.log(data)
        $("#set-profile").attr("src", data['filePath']);
        localStorage.setItem('businessImgId', data['@id']); 
        toastr.success('Please click the save button to update your profile.')
    })
    .catch(err => {console.log(err)})
}
