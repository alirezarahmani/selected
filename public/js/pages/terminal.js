
let token = localStorage.getItem('token');
let selectJobSite = document.getElementById("selectJobSites");
let selectSchedule = document.getElementById("selectSchedule");
let submitForm = document.getElementById('formSubmited');
let role = localStorage.getItem('role');
let fullJobSites;

let billings = JSON.parse(localStorage.getItem('billing'));

if (token == null) {
    window.location.href = base_url2 + "/login";
}

if (billings['useAttendance'] === false || role === "employee"){
    window.location.href = base_url2 + "/404";
}else {
    loadAllEvent();
    function loadAllEvent(){
        submitForm.addEventListener('submit', sendForm);
    }

    if (token) {
        async function workPlace (url) {
            let response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': "application/json",
                    'Authorization': `Bearer ${token}`
                },
            });
            let jobSite = await response.json();
            return jobSite;
        };
        workPlace(base_url + "/api/job_sites")
            .then(data => {
                data['hydra:member'].forEach(jobSites => {
                    let optName = jobSites.name;
                    let optIriId = jobSites['@id'];
                    let el = document.createElement("option");
                    el.textContent = optName;
                    el.value = optIriId;
                    selectJobSite.appendChild(el);
                });
                // console.log(data['hydra:member'])
            })

        async function schedules (url) {
            let response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': "application/json",
                    'Authorization': `Bearer ${token}`
                },
            });
            let showSchedule = await response.json();
            return showSchedule;
        };
        schedules(base_url + "/api/schedules")
            .then(data => {
                data['hydra:member'].forEach( schedule => {
                    let optName = schedule.name;
                    let optIriId = schedule['@id'];
                    let el = document.createElement("option");
                    el.textContent = optName;
                    el.value = optIriId;
                    selectSchedule.appendChild(el);
                });
                // console.log(data['hydra:member'])
            })
    }

    async function postTerminalToken (url, _getToken) {
        let response = await fetch(url, {
            method: 'POST',
            headers : {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(_getToken)
        });
        let getToken = await response.json();
        return getToken;
    }

    function sendForm(e) {
        e.preventDefault();
        let valueJobSite = selectJobSite.options[selectJobSite.selectedIndex].value;
        let valueSchedule = selectSchedule.options[selectSchedule.selectedIndex].value;
        let validSelectBox = document.querySelector('.validScheduleOrPosition');

        let data = {
            schedule: valueSchedule,
            jobsite: valueJobSite,
        }

        console.log(data);
        if (data['jobsite'] === "Select workplace" || data['schedule'] === "Select Schedule"){
            validSelectBox.style.display = 'block';
            setTimeout(()=>{
                validSelectBox.style.display = 'none';
            },4000)
        }else{
            postTerminalToken(base_url + '/api/getTerminalToken', data)
                .then(data => {
                    console.log(data);
                    localStorage.setItem("tokenTerminal", JSON.stringify(data));
                    setTimeout(()=>{
                        window.location.href = base_url+"/loginClock";
                    },1000)
                    localStorage.removeItem('token');
                })
                .catch(err => {
                        if (err == 401) {
                            window.location.href = base_url + "/login";
                        }
                    }
                );

        }

    }
}



