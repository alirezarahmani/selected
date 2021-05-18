let token = localStorage.getItem('token');
let tokenTerminal = JSON.parse(localStorage.getItem('tokenTerminal'));
let Role = localStorage.getItem("role");
let notToken = document.getElementById('noToken');
let hasToken = document.getElementById('hasToken');
let email = document.getElementById('email');
let fullSchedules;
let selectSchedule = document.getElementById("selectSchedule");
let selectPosition = document.querySelector('.selectPosition');
let loginOwner = document.getElementById('loginClockOwner');
let loginStaff = document.getElementById('loginClockStaff');


let billings = JSON.parse(localStorage.getItem('billing'));

if (billings['useAttendance'] === false) {
    window.location.href = base_url2 + "/404";
}else {
        
    loadAllEvent();
    function loadAllEvent() {
        loginOwner.addEventListener('submit', clockOwner);
        loginStaff.addEventListener('submit', clockStaff);
    };

    let RoleCompany;
    if (Role === "account") {
        RoleCompany = "account"
    }else {
        RoleCompany = "user";
    }

    if (token) {
        async function fullSchedule (url) {
            let response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': "application/json",
                    'Authorization': `Bearer ${token}`
                },
            });
            let Schedule = await response.json();
            return Schedule;
        };
        fullSchedule(base_url + '/api/schedules')
            .then(data => {
                // fullSchedules = data['hydra:member'];
                data['hydra:member'].forEach(schedule => {
                    let optName = schedule.name;
                    let optIriId = schedule['@id'];
                    let el = document.createElement("option");
                    el.textContent = optName;
                    el.value = optIriId;
                    selectSchedule.appendChild(el);
                });
            })
    }
    async function loginAuth(url, data) {
        let response = await fetch(url,{
            method: 'POST',
            body: JSON.stringify(data),
        });
        let valLoginAuth = await response.json();
        return valLoginAuth;
    };

    function checkEmail(inputText) {
        const errEmail = document.querySelector('.validEmail');
        const isValid = isValidEmailAddress(inputText.value);

        if (!isValid){
            errEmail.style.display = 'block';
            document.getElementById('loginStaff').disabled = true;
            setTimeout(() => {
                errEmail.style.display = 'none'
            },4000)
        }else {
            document.getElementById('loginStaff').disabled = false;
        }
    };
    function isValidEmailAddress(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    };

    function startTime() {
            let today = new Date();
            let h = today.getHours();
            let m = today.getMinutes();
            m = checkTime(m);
            document.getElementById('time').innerHTML = h + ":" + m;
            let t = setTimeout(startTime, 500);
        };
    function checkTime(i) {
            if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
            return i;
        };

    if (RoleCompany === "account") {
            notToken.style.display = 'none';
            hasToken.style.display = 'block';
    }else {
            notToken.style.display = 'block';
            hasToken.style.display = 'none';
    }

    if (RoleCompany !== "account") {
        async function getPosition(url) {
            let response = await fetch(url,{
                method: 'GET',
                headers : {
                    'Content-Type': "application/json",
                    'Authorization': `Bearer ${token}`,
                },
            });
            let position = await response.json();
            return position;
        }
        
        getPosition(base_url+'/api/positions')
            .then(data => {
                data['hydra:member'].forEach(pos => {
                    let optName = pos.name;
                    let optIriId = pos['@id'];
                    let el = document.createElement("option");
                    el.textContent = optName;
                    el.value = optIriId;
                    selectPosition.appendChild(el);
                });
            })
            .catch(err => console.log(err))   
    }

    function clockOwner(e) {
        e.preventDefault();
        let valueSchedule = selectSchedule.options[selectSchedule.selectedIndex].value;
        let valueScheduleName = selectSchedule.options[selectSchedule.selectedIndex].innerHTML;
        let valuePosition = selectPosition.options[selectPosition.selectedIndex].value;
        let valuePositionName = selectPosition.options[selectPosition.selectedIndex].innerHTML;
        let validSelectBox = document.querySelector('.validScheduleOrPosition');

        let formData = {
            schedule: valueSchedule,
            position: valuePosition
        };
        let formName = {
            schedule: valueScheduleName,
            position: valuePositionName
        }
        // console.log(formData);
            if (formData['schedule'] === "Select schedule" || formData['position'] === "Select position") {
                validSelectBox.style.display = 'block';
                setTimeout(()=>{
                    validSelectBox.style.display = 'none';
                },3000)
            } else {
                localStorage.setItem("AuthClockLogin", JSON.stringify(formData));
                localStorage.setItem("formNameClock", JSON.stringify(formName));
                window.location.href = base_url+'/clockInOut';
            }
    }

    function clockStaff(e) {
        e.preventDefault();
        let validEmail = document.querySelector('.validEmailStaff');
        let formData = {
            header: tokenTerminal['token'],
            email: document.getElementById('EmailHasToken').value,
        };
        setTimeout(()=>{
            loginAuth(base_url + '/api/login_terminal_auth', formData)
                .then(data => {
                    if (data['hydra:description']){
                        validEmail.style.display = 'block';
                        setTimeout(()=>{
                            validEmail.style.display = 'none';
                        },2000)
                    }else {
                        if (data['hydra:description'] == undefined){
                            console.log(data);
                            localStorage.setItem('userDetailClockIn', JSON.stringify(data));
                            localStorage.setItem("AuthClockLogin", JSON.stringify(formData));
                            setTimeout(() => {
                                window.location.href = base_url+'/clockInOut';
                            }, 1000);
                        }
                    }
                })
                .catch(err => {
                    console.log(err)
                })
        },1000)
    }

}