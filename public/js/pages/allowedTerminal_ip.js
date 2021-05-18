let tok=localStorage.getItem('token');
let role = localStorage.getItem('role');
let billings = JSON.parse(localStorage.getItem('billing'));

let valueLocation = document.querySelector('.idLocations');
let getlocation = document.getElementById('getIp');
let sendLocation = document.getElementById('sendLocation');
let changed_user = document.getElementById('userChanged');
let showLabel = document.getElementById('showLabel');
let lati;
let longi;
let city;
let country_cap;
let country;
let ipLoc;

if (billings['useScheduler'] === false || role === "employee") {
    window.location.href = "404.html"
}else {
    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    loadAllEvent();

    function loadAllEvent(){
        getlocation.addEventListener('click', showLocations);
        sendLocation.addEventListener('submit', postIP);
        window.onload = onPageLoad();
    }

    function checkInput(input) {
        if (input.value == ''){
            document.getElementById('getIp').disabled = true
        }else {
            document.getElementById('getIp').disabled = false
        }
    }

    function chechLable(input) {
        if (input.value == ''){
            document.getElementById('sendIp').disabled = true
        }else {
            document.getElementById('sendIp').disabled = false
        }
    }

    function onPageLoad() {
        changed_user.checked = true;
    }

    async function getAlpha_2Country(ipLocation){
        let response = await fetch(`http://ip-api.com/json/${ipLocation}`);
        let data = await response.json();
        return data;
    }
    async function allowedTerminal(url, data) {
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${tok}`,
            },
            body: JSON.stringify(data)
        });
        let allowedIp = await response.json();
        return allowedIp;
    }
    function showLocations(e) {
        e.preventDefault();
        getAlpha_2Country(valueLocation.value)
        .then(data => {
            if (data.status === "success") {
                lati = parseFloat(data.lat);
                longi = parseFloat(data.lon);
                city = data.city;
                country = data.country;
                country_cap = data.regionName;
                ipLoc = data.query;
                setTimeout(()=>{
                    initLocationIp();
                    showLabel.style.display = 'block';
                },2000)
            }else {
                toastr.error('The ip address is incorrect.');
            }

        })
        .catch(err => console.log(err))
    }

    function initLocationIp(){
        if (lati && longi){
            let options = {
                zoom: 18,
                center: {
                    lat: parseFloat(lati),
                    lng: parseFloat(longi)
                }
            }
            let map = new google.maps.Map(
                document.getElementById('map'), options
            );

            let marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(lati),
                    lng: parseFloat(longi)
                },
                map: map
            });

            let infoWindow = new google.maps.InfoWindow({
                content: `<h4>${country}, ${country_cap}, ${city}</h4>`
            });

            marker.addListener('click', ()=>{
                infoWindow.open(map, marker);
            })
        }

    }

    function postIP(e) {
        e.preventDefault();
        let activeLocation;
        if (changed_user.checked) {
            activeLocation = true;
        }else {
            activeLocation = false;
        }
        let locations = `${lati},${longi}`;
        let data = {
            label: document.getElementById('label').value,
            active: activeLocation,
            ip: ipLoc,
            location: locations
        }

        // console.log(data);
        allowedTerminal(base_url+ '/api/allowed_terminal_ips', data)
            .then(data => {
                // console.log(data)
                valueLocation.value = '';
                document.getElementById('label').value = '';
                toastr.success('Address added.');
            })
            .catch(err => console.log(err));
    }
}
