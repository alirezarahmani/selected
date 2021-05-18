let token = localStorage.getItem('token');
let authClockLogin = JSON.parse(localStorage.getItem('AuthClockLogin'));
let formNameClock = JSON.parse(localStorage.getItem('formNameClock'));
console.log(formNameClock);
let Role = localStorage.getItem("role");
let att = JSON.parse(localStorage.getItem('attendance_time_clock'));
let userDetail = JSON.parse(localStorage.getItem('userDetailClockIn'));
let userInfo = JSON.parse(localStorage.getItem('userInfo'));
let businessInfo = JSON.parse(localStorage.getItem('businessInfo'));

let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let snapshot = document.getElementById('snapshot');
let popoverFull = document.querySelector('.popoverClockBreak');
let popoverFull2 = document.querySelector('.popoverClockBreak2');
let popoverBreak = document.querySelector('.popoverBreak');
let popoverBreak2 = document.querySelector('.popoverBreak2');

let showBreak = document.getElementById('showBreak');
let notSchedule = document.getElementById('notSchedule');

let fullnameUsers = document.getElementById('fullnameUsers');
let showScheduleUser = document.getElementById('showScheduleUser')
let jobSiteUsers = document.getElementById('jobSiteUsers');
let businessName = document.getElementById('businessName');
let selectSchedule2 = document.querySelector('.selectSchedule2');

let msgClockIn = document.getElementById('msgClockIn');
let msgClockOut = document.getElementById('msgClockOut');
let msgOptions = document.getElementById('msgOptions');
let currentSchedule = document.getElementById('currentSchedule');

let clockInBTN = document.querySelector('.clockInAction');
let clockOutBTN = document.querySelector('.clockOutAction');
let breakBTN = document.querySelector('.breakAction');

let msgTake = document.getElementById('msgTakeBreak');
let msgFinish = document.getElementById('msgFinishBreak');
let photo_IRI_ID;
// console.log('userDetails',userDetail);
// console.log('userInfo',userInfo);
// console.log('businessInfo',businessInfo);
// console.log('authClock', authClockLogin);
// console.log('att', att);

let billings = JSON.parse(localStorage.getItem('billing'));

if (billings['useAttendance'] === false) {
    window.location.href = base_url2 + "/404";
}else {

    loadAllEvent();

    function loadAllEvent() {
        clockInBTN.addEventListener('click', actionClockIn);
        clockOutBTN.addEventListener('click', actionClockOut);
        breakBTN.addEventListener('click', actionBreak);
    }
    let RoleCompany;
    if (Role === "account") {
        RoleCompany = "account"
    }else {
        RoleCompany = "user";
    }
    
    if (RoleCompany === "account"){
        if(userDetail){
            if(userDetail.userHasSchedule.length == 0 || !userDetail.userHasSchedule){
                showBreak.style.display = 'none';
                notSchedule.style.display = 'block';
                // alert('= 0')
            }else {
                showBreak.style.display = 'block';
                notSchedule.style.display = 'none';
                // alert(' ! 0')
            }
        }
    }else {
        showBreak.style.display = 'block';
        notSchedule.style.display = 'none';
    }
    
    function hidePopover() {
        popoverFull.style.display = "none";
    }
    function showPopover() {
        popoverFull.style.display = "block";
    }
    function hidePopover2() {
        popoverFull2.style.display = "none";
    }
    function showPopover2() {
        popoverFull2.style.display = "block";
    }
    function showPopoverBreak(){
        if (RoleCompany === "account"){
            if (userDetail){
                if (userDetail['lastAttendanceTime']['breakoutStart'] === null){
                    popoverBreak.style.display = 'block';
                }else {
                    popoverBreak2.style.display = 'block'
                }
            }
        }else {
            if (att){
                if (att['breakoutStart'] == null){
                    popoverBreak.style.display = 'block';
                } else {
                    popoverBreak2.style.display = 'block'
                }
            }else if (att === null){
                popoverBreak.style.display = 'block';
            }
        }
    }
    function hidePopoverBreak(){
        if (RoleCompany === "account"){
            if (userDetail){
                if (userDetail['lastAttendanceTime']['breakoutStart'] === null){
                    popoverBreak.style.display = 'none';
                }else {
                    popoverBreak2.style.display = 'none'
                }
            }
        }else{
            if (att){
                if (att['breakoutStart'] == null){
                    popoverBreak.style.display = 'none';
                } else {
                    popoverBreak2.style.display = 'none'
                }
            }else if (att === null){
                popoverBreak.style.display = 'none';
            }
        }
    }
    
    function startTime() {
        let today = new Date();
        let h = today.getHours();
        let m = today.getMinutes();
        m = checkTime(m);
        document.getElementById('time').innerHTML = h + ":" + m;
        setTimeout(startTime, 500);
    }
    function checkTime(i) {
        if (i < 10) {i = "0" + i};
        return i;
    }
    
    async function postImage(url, formData) {
        let response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        let uploadImage = await response.json();
        return uploadImage;
    }
    async function loginTerminal(url, _loginTerminal) {
        let response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(_loginTerminal)
        });
        let login = await response.json();
        return login;
    }
    async function attendanceTimes(url, _data) {
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(_data),
        });
        let attData = await response.json();
        return attData;
    }
    async function attendanceTimes_put(url, _data) {
        let response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(_data),
        });
        let attData = await response.json();
        return attData;
    }
    
    if (RoleCompany === "account"){
        if (userDetail){
            if (userDetail['firstName'] && userDetail['lastName']) {    
                fullnameUsers.innerHTML = `${userDetail['firstName']} ${userDetail['lastName']}`;
            }
            if (userDetail['lastAttendanceTime']['schedule']['address']) {
                jobSiteUsers.innerHTML = userDetail['lastAttendanceTime']['schedule']['address'];
            }
            if (userDetail['lastAttendanceTime']['schedule']['name']) {
                showScheduleUser.innerHTML = userDetail['lastAttendanceTime']['schedule']['name'];
            }
            if (userDetail['positions'][0]['name']) {
                businessName.innerHTML = userDetail['positions'][0]['name'];
            }
        }
    }else{
        if (userInfo){
            fullnameUsers.innerHTML = `${userInfo['firstName']} ${userInfo['lastName']}`;
            if (businessInfo){
                jobSiteUsers.innerHTML = businessInfo[0]['business']['address'];
                businessName.innerHTML = businessInfo[0]['business']['name'];
            }
            if (formNameClock.schedule){
                showScheduleUser.innerHTML = formNameClock.schedule;
                businessName.innerHTML = formNameClock.position;
            }
        }
    }
    
    let cameraStream = null;
    if (null == cameraStream) {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia){
            navigator.mediaDevices.getUserMedia({video: true})
                .then(function (stream) {
                    video.srcObject = stream;
                    video.play();
                    cameraStream = stream;
                })
                .catch((err)=> console.log("Please enable access and attach a camera"));
        }
    }
    
    function dataURItoBlob(dataURI) {
        // convert base64/URLEncoded data component to raw binary data held in a string
        let byteString;
        if (dataURI.split(',')[0].indexOf('base64') >= 0)
            byteString = atob(dataURI.split(',')[1]);
        else
            byteString = unescape(dataURI.split(',')[1]);
    
        // separate out the mime component
        let mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    
        // write the bytes of the string to a typed array
        let ia = new Uint8Array(byteString.length);
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
    
        return new Blob([ia], {type:mimeString});
    }
    
    let photoClock;
    function getPhoto() {
        video.style.display = 'none';
    
        if (null != cameraStream) {
            let context = canvas.getContext('2d');
            let img = canvas.toDataURL('image/png', 0.5);
            let file = dataURItoBlob(img);
    
            context.drawImage(video, 0, 0, 533, 400);
            let track = cameraStream.getTracks()[ 0 ];
            track.stop();
            video.load();
            cameraStream = null;
    
            photoClock = file;
    
        }
    }
    
    //////////////  help functions
    if (RoleCompany === "account"){
        if (userDetail){
            console.log(userDetail);
            if (userDetail.userHasSchedule.length > 1){
                // let position = document.querySelector('.selectSchedule2');
                selectSchedule2.style.display = 'block';
    
                userDetail.userHasSchedule.forEach(pos => {
                    let optName = pos.name;
                    let optIriId = pos['@id'];
                    let el = document.createElement("option");
                    el.textContent = optName;
                    el.value = optIriId;
                    // console.log(el);
                    selectSchedule2.appendChild(el);
                })
            }
        }
    }
    
    function showPhoto(){
        let formData = new FormData();
        formData.append("objectable","attendance_times");
        formData.append("file", photoClock, "image.png");
    
        postImage(base_url+'/api/media', formData)
            .then(data => {
                photo_IRI_ID = data['@id'];
                // console.log(photo_IRI_ID);
            })
            .catch(err => console.log(err));
    }

    let responseClockInOut;

    function _clockIn_OutPublic() {
    
        let dataTime = new Date();
        let time = moment(dataTime).format('YYYY-MM-DD HH:mm');
        let emailClockIn = JSON.parse(localStorage.getItem('AuthClockLogin'));
        let tokenTerminal = JSON.parse(localStorage.getItem('tokenTerminal'));
        let valuePosition = selectSchedule2.options[selectSchedule2.selectedIndex].value;
    
            if (userDetail.userHasSchedule.length > 1) {
                // alert('chone position ha bishtar az yeki mibashad bayad az select position estefadeh konid');
                if (valuePosition !== "Select position") {
                    if (userDetail['lastAttendanceTime']['startTime'] !== null) {
                        // check kon ke schedule ba ham ok hastand ya na
                        if (valuePosition === userDetail['lastAttendanceTime']['schedule']['@id']) {
                            setTimeout(()=>{
                                let data = {
                                    media: photo_IRI_ID,
                                    startTime: time,
                                    header: tokenTerminal['token'],
                                    mail: emailClockIn['email'],
                                    position: valuePosition,
                                }
                                loginTerminal(base_url+'/api/login_terminal', data)
                                    .then(data => {
                                        responseClockInOut = data;
                                            setTimeout(()=>{
                                                window.location.href = base_url+"/loginClock"
                                            }, 8000)
                                        }
                                    )
                                    .catch(err => console.log(err))
                            },3000)
    
                        }else{
                            currentSchedule.style.display = 'block';
                            setTimeout(()=>{
                                currentSchedule.style.display = 'none';
                            },3000)
                        }   
                    }else {
                        setTimeout(()=>{
                            let data = {
                                media: photo_IRI_ID,
                                startTime: time,
                                header: tokenTerminal['token'],
                                mail: emailClockIn['email'],
                                position: valuePosition,
                            }
                            loginTerminal(base_url+'/api/login_terminal', data)
                                .then(data => {
                                    responseClockInOut = data;
                                        setTimeout(()=>{
                                            window.location.href = base_url+"/loginClock"
                                        }, 8000)
                                    }
                                )
                                .catch(err => console.log(err))
                            // console.log(data);
                        },3000)
                    }
                }else {
                    msgOptions.style.display = 'block';
                    setTimeout(()=>{
                        msgOptions.style.display = 'none';
                    },3000)
                }
                
            }else {
                setTimeout(()=>{
                    let data = {
                        media: photo_IRI_ID,
                        startTime: time,
                        header: tokenTerminal['token'],
                        mail: emailClockIn['email'],
                        position: userDetail.userHasSchedule[0]['@id'],
                    }
                    loginTerminal(base_url+'/api/login_terminal', data)
                        .then(data => {
                            responseClockInOut = data;
                            console.log(data);
                                setTimeout(()=>{
                                    window.location.href = base_url+"/loginClock"
                                }, 8000)
                            }
                        )
                        .catch(err => console.log(err))
                },3000)
            }
    }
    function _clockIn_Private() {
        let dataTime = new Date();
        let time = moment(dataTime).format('YYYY-MM-DD HH:mm');
    
            setTimeout(() => {
                let data = {
                    schedule: authClockLogin['schedule'],
                    position: authClockLogin['position'],
                    startTime: time,
                    media: photo_IRI_ID
                }
                attendanceTimes(base_url + '/api/attendance_times', data)
                    .then(data => {
                        localStorage.setItem('attendance_time_clock', JSON.stringify(data));
                        if (data['@type'] === "hydra:Error") {
                            toastr.error(`${data['hydra:description']}`);
                        }else {
                            responseClockInOut = data;
                            setTimeout(()=>{
                                window.location.href = base_url+"/loginClock"
                            }, 8000)
                        }
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },3000)
    }
    function clockOut_private() {
        let dataTime = new Date();
        let time = moment(dataTime).format('YYYY-MM-DD HH:mm');
        setTimeout(()=>{
            let data = {
                media: photo_IRI_ID,
                endTime: time
            }
            attendanceTimes_put(base_url +"/api/attendance_times/"+ att['id'], data)
                .then(data => {
                    // msgClockOut.style.display = 'block';
                    if (data['@type'] === "hydra:Error") {
                        toastr.error(`${data['hydra:description']}`);
                    }else {
                        responseClockInOut = data;
                        setTimeout(()=>{
                            window.location.href = base_url+"/loginClock"
                        }, 8000)
                    }
    
                })
                .catch(err => {
                    console.log(err)
                })
        },4000)
    }
    
    function clockBreak_public() {
        let dataTime = new Date();
        let time = moment(dataTime).format('YYYY-MM-DD HH:mm');
        let tokenTerminal = JSON.parse(localStorage.getItem('tokenTerminal'));
        let valuePosition = selectSchedule2.options[selectSchedule2.selectedIndex].value;
    
        if (userDetail.userHasSchedule.length > 1){
            if (valuePosition !== "Select position"){
                setTimeout(()=>{
                    let data;
                    if (userDetail['lastAttendanceTime']['breakoutStart'] === null) {
                        msgTake.style.display = 'block';
                        let dataBreak = {
                            media: photo_IRI_ID,
                            breakoutStart: time,
                            header: tokenTerminal['token'],
                            mail: authClockLogin['email'],
                            position: valuePosition,
                        };
                        data = dataBreak;
                    }else {
                        msgFinish.style.display = 'block';
                        let dataBreak = {
                            media: photo_IRI_ID,
                            breakOutEnd: time,
                            header: tokenTerminal['token'],
                            mail: authClockLogin['email'],
                            position: valuePosition,
                        };
                        data = dataBreak;
                    }
                    // console.log(data);
                    loginTerminal(base_url+'/api/login_terminal', data)
                        .then(data => {
                            console.log(data);
                            if (data['@type'] === "hydra:Error") {
                                toastr.error(`${data['hydra:description']}`);
                            }else{
                                setTimeout(()=>{
                                    window.location.href = base_url+"/loginClock"
                                }, 2000)
                            }
                        })
                        .catch(err => console.log(err))
                },4000)
            }else{
                msgOptions.style.display = 'block';
                setTimeout(()=>{
                    msgOptions.style.display = 'none';
                },3000)
            }
        }else{
            setTimeout(()=>{
                let data;
                if (userDetail['lastAttendanceTime']['breakoutStart'] === null){
                    msgTake.style.display = 'block';
                    // alert('break start')
                     let dataBreak = {
                        media: photo_IRI_ID,
                        breakoutStart: time,
                        header: tokenTerminal['token'],
                        mail: authClockLogin['email'],
                        position: userDetail.userHasSchedule[0]['@id'],
                     };
                    data = dataBreak;
                }else {
                    msgFinish.style.display = 'block';
                    // alert('break finish');
                    let dataBreak = {
                        media: photo_IRI_ID,
                        breakOutEnd: time,
                        header: tokenTerminal['token'],
                        mail: authClockLogin['email'],
                        position: userDetail.userHasSchedule[0]['@id'],
                    };
                    data = dataBreak;
                }
                loginTerminal(base_url+'/api/login_terminal', data)
                    .then(data => {
                        console.log(data);
                        if (data['@type'] === "hydra:Error") {
                            toastr.error(`${data['hydra:description']}`);
                        }else{
                            setTimeout(()=>{
                                window.location.href = base_url+"/loginClock"
                            }, 2000)
                        }
                    })
                    .catch(err => console.log(err))
            },4000)
        }
    }
    
    function clockBreak_private() {
        // console.log('attendance_time_clock', att['breakoutStart']);
        let dataTime = new Date();
        let time = moment(dataTime).format('YYYY-MM-DD HH:mm');
    
        setTimeout(()=>{
            let data;
            msgTake.style.display = 'block';
            if (att['breakoutStart'] === null) {
                // alert('break start')
                let dataBreak = {
                    breakoutStart: time,
                    media: photo_IRI_ID
                }
                data = dataBreak;
            }else {
                // alert('break end')
                msgFinish.style.display = 'block';
                let dataBreak = {
                    breakOutEnd: time,
                    media: photo_IRI_ID
                }
                data = dataBreak;
            }
            // console.log(data);
            attendanceTimes_put(base_url + '/api/attendance_times/' + att['id'], data)
                .then(data => {
                    // console.log(data);
                    if (data['@type'] === "hydra:Error") {
                        toastr.error(`${data['hydra:description']}`);
                    }else{
                        localStorage.setItem('attendance_time_clock', JSON.stringify(data));
                        setTimeout(()=>{
                            window.location.href = base_url+"/loginClock"
                        }, 2000)
                    }
                })
                .catch(err => console.log(err))
    
    
        },4000)
    
    }
    
    ////////////  functions
    
    function clockIn() {
        if (RoleCompany === "account") {
            // alert('ok');
            showPhoto();
            _clockIn_OutPublic();
        }else {
            // alert('clock in private!!!!');
            showPhoto();
            _clockIn_Private();
        }
    }
    function clockOut() {
        if (RoleCompany === "account") {
            // alert('ok');
            showPhoto();
            _clockIn_OutPublic();
        }else {
            // alert('clock out private');
            showPhoto();
            clockOut_private();
        }
    }
    
    function clockBreak() {
        if (RoleCompany === "account") {
            // alert('ok');
            showPhoto();
            clockBreak_public();
        }else {
            // alert('nokey');
            showPhoto();
            clockBreak_private();
        }
    }
    ///////////////////  events
    
    function actionClockIn() {
        getPhoto();
        clockIn();
        
        setTimeout(() => {
            if (responseClockInOut === true) {
                msgClockIn.style.display = 'block';        
            }
        }, 5000);
    }
    
    function actionClockOut() {
        getPhoto();
        clockOut();

        setTimeout(() => {
            if (responseClockInOut === true) {
                msgClockOut.style.display = 'block';
            }
        }, 5000);
    }
    
    function actionBreak(){
        getPhoto();
        clockBreak();
    }
    
}
