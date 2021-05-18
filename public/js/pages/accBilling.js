
let tok=localStorage.getItem('token');
let role = localStorage.getItem('role');
let billingInfo = JSON.parse(localStorage.getItem('billing'));
let businessInfo = JSON.parse(localStorage.getItem('businessInfo'));
let userInfo = JSON.parse(localStorage.getItem('selBus'));
// console.log(userInfo);
// console.log(businessInfo);


if (role !== "account"){
    window.location.href = base_url2 + "/404";
}
if(tok == null){
    window.location.href = base_url2+"/login";
}

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

async function postData (url,data) {
    let response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': "application/json",
            'Authorization': `Bearer ${tok}`
        },
        body: JSON.stringify(data),
    });
    let responseData = await response.json();
    return responseData;
};

getData(`${base_url}/api/businesses/${userInfo}`)
    .then(data => {
        if (data.additionalUsersCount) {
            document.querySelector('.numberOfEmployee').innerHTML =  `${billingInfo['numberOfEmployee']} + ${data.additionalUsersCount} `;
            document.querySelector('.numberOfEmployee2').innerHTML = parseFloat(billingInfo['numberOfEmployee']) + data.additionalUsersCount;
            let employeeSubmited = businessInfo[0]['business']['numberOfEmployee'];
            let employeeBilling = parseFloat(billingInfo['numberOfEmployee']) + data.additionalUsersCount;
            let spotsLeftEmployee = (employeeBilling / employeeSubmited).toFixed(0);
            let submitEmployeePerc = Math.floor(employeeSubmited * 100) / employeeBilling;
            let EmployeePerc = Math.floor(employeeBilling * 100) / employeeBilling
            let percentageUsers = 100 - (parseFloat (EmployeePerc - submitEmployeePerc)).toFixed(0) + "%";
            document.querySelector('.progress-bar').setAttribute('style', 'width:'+percentageUsers);
            document.querySelector('.numberOfEmployeeSubmited').innerHTML = employeeSubmited;
            document.querySelector('.numberOfEmployeeSubmited2').innerHTML = employeeSubmited;
            document.getElementById('numberOfEmployeeLeft').innerHTML = spotsLeftEmployee
        }else {
            document.querySelector('.numberOfEmployee').innerHTML = billingInfo['numberOfEmployee'];
            document.querySelector('.numberOfEmployee2').innerHTML = parseFloat(billingInfo['numberOfEmployee']) ;
            let employeeSubmited = businessInfo[0]['business']['numberOfEmployee'];
            let employeeBilling = parseFloat(billingInfo['numberOfEmployee']) ;
            let spotsLeftEmployee = (employeeBilling / employeeSubmited).toFixed(0);
            let submitEmployeePerc = Math.floor(employeeSubmited * 100) / employeeBilling;
            let EmployeePerc = Math.floor(employeeBilling * 100) / employeeBilling
            let percentageUsers = 100 - (parseFloat (EmployeePerc - submitEmployeePerc)).toFixed(0) + "%";
            document.querySelector('.progress-bar').setAttribute('style', 'width:'+percentageUsers);
            document.querySelector('.numberOfEmployeeSubmited').innerHTML = employeeSubmited;
            document.querySelector('.numberOfEmployeeSubmited2').innerHTML = employeeSubmited;
            document.getElementById('numberOfEmployeeLeft').innerHTML = spotsLeftEmployee
        }
    })
    .catch(err => console.log(err))

let UserPurchesAmount;
let UserPurchesCurrency;
let numberUser = document.getElementById('userValue').innerHTML = 0;
function minusUser() {
    if (numberUser > 0) {
        numberUser -= +5;
    }
    let numberOfUser = document.getElementById('userValue').innerHTML = numberUser;
    let data = {
        userCount: numberOfUser
    }
    // console.log(numberOfUser);
    postData(`${base_url}/api/business_banks/exchange_adduser_cost`, data)
        .then(data => {
            // console.log(data)
            let exchenge = data['hydra:member'];
            UserPurchesAmount = exchenge[0];
            UserPurchesCurrency = exchenge[1];
            document.getElementById('subtotalExchange').innerHTML = `${exchenge[2]} ${exchenge[0]}`;
            document.getElementById('totalExchange').innerHTML = `${exchenge[2]} ${exchenge[0]}`;
            if (data['@type'] === "hydra:Error") {
                toastr.error(data['hydra:description']);
            }
        })
        .catch(err => {
            console.log(err)
        })
    return true
}

function plusUser () {
    numberUser += +5;
    let numberOfUser = document.getElementById('userValue').innerHTML = numberUser;
    let data = {
        userCount: numberOfUser
    }
    // console.log(numberOfUser);
    postData(`${base_url}/api/business_banks/exchange_adduser_cost`, data)
        .then(data => {
            // console.log(data)
            let exchenge = data['hydra:member'];
            UserPurchesAmount = exchenge[0];
            UserPurchesCurrency = exchenge[1];
            document.getElementById('subtotalExchange').innerHTML = `${exchenge[2]} ${exchenge[0]}`;
            document.getElementById('totalExchange').innerHTML = `${exchenge[2]} ${exchenge[0]}`;
            if (data['@type'] === "hydra:Error") {
                toastr.error(data['hydra:description']);
            }
        })
        .catch(err => {
            console.log(err)
        })
    return true
}

document.getElementById('purchesUser').addEventListener('click', e => {
    e.preventDefault();
    let data = {
        userCount: parseFloat(document.getElementById('userValue').innerHTML),
        amount: UserPurchesAmount,
        currency: UserPurchesCurrency
    }
    // console.log(data);
    postData(`${base_url}/api/business_banks/pay_additional_user`, data)
        .then(data => {
            console.log(data);
            if (data['@type'] === "hydra:Error") {
                toastr.error(data['hydra:description']);
            }else {
                toastr.success('added employee.');
                getData(`${base_url}/api/businesses/${userInfo}`)
                    .then(data => {
                        if (data.additionalUsersCount) {
                            document.querySelector('.numberOfEmployee').innerHTML =  `${billingInfo['numberOfEmployee']} + ${data.additionalUsersCount} `;
                            document.querySelector('.numberOfEmployee2').innerHTML = parseFloat(billingInfo['numberOfEmployee']) + data.additionalUsersCount;
                            let employeeSubmited = businessInfo[0]['business']['numberOfEmployee'];
                            let employeeBilling = parseFloat(billingInfo['numberOfEmployee']) + data.additionalUsersCount;
                            let spotsLeftEmployee = (employeeBilling / employeeSubmited).toFixed(0);
                            let submitEmployeePerc = Math.floor(employeeSubmited * 100) / employeeBilling;
                            let EmployeePerc = Math.floor(employeeBilling * 100) / employeeBilling
                            let percentageUsers = 100 - (parseFloat (EmployeePerc - submitEmployeePerc)).toFixed(0) + "%";
                            document.querySelector('.progress-bar').setAttribute('style', 'width:'+percentageUsers);
                            document.querySelector('.numberOfEmployeeSubmited').innerHTML = employeeSubmited;
                            document.querySelector('.numberOfEmployeeSubmited2').innerHTML = employeeSubmited;
                            document.getElementById('numberOfEmployeeLeft').innerHTML = spotsLeftEmployee
                        }else {
                            document.querySelector('.numberOfEmployee').innerHTML = billingInfo['numberOfEmployee'];
                            document.querySelector('.numberOfEmployee2').innerHTML = parseFloat(billingInfo['numberOfEmployee']) ;
                            let employeeSubmited = businessInfo[0]['business']['numberOfEmployee'];
                            let employeeBilling = parseFloat(billingInfo['numberOfEmployee']) ;
                            let spotsLeftEmployee = (employeeBilling / employeeSubmited).toFixed(0);
                            let submitEmployeePerc = Math.floor(employeeSubmited * 100) / employeeBilling;
                            let EmployeePerc = Math.floor(employeeBilling * 100) / employeeBilling
                            let percentageUsers = 100 - (parseFloat (EmployeePerc - submitEmployeePerc)).toFixed(0) + "%";
                            document.querySelector('.progress-bar').setAttribute('style', 'width:'+percentageUsers);
                            document.querySelector('.numberOfEmployeeSubmited').innerHTML = employeeSubmited;
                            document.querySelector('.numberOfEmployeeSubmited2').innerHTML = employeeSubmited;
                            document.getElementById('numberOfEmployeeLeft').innerHTML = spotsLeftEmployee
                        }
                    })
                    .catch(err => console.log(err))
            }
        })
        .catch(err => {
            console.log(err)
        })
})

let accountTransfer = document.getElementById('accountTransfer');

let fullname=localStorage.getItem('fullname');
if (billingInfo.period === 365) {
    document.getElementById('timeBilling').innerHTML = "One years"
    document.getElementById('numberOfPeriod').value = "One years"
}else if (billingInfo.period === 30) {
    document.getElementById('timeBilling').innerHTML = "One month"
    document.getElementById('numberOfPeriod').value = "One month"
}else if (billingInfo.period === 7) {
    document.getElementById('timeBilling').innerHTML = "One week"
    document.getElementById('numberOfPeriod').value = "One week"
}

billingInfo['isDefault'] == true ? document.getElementById('packageItem').style.display = 'block' : document.getElementById('packageItem').style.display = 'none';
billingInfo['useAttendance'] == true ? document.getElementById('attendanceItem').style.display = 'block' : document.getElementById('attendanceItem').style.display = 'none';
billingInfo['useAvailability'] == true ? document.getElementById('availablityItem').style.display = 'block' : document.getElementById('availablityItem').style.display = 'none';
billingInfo['useHiring'] == true ? document.getElementById('hiringItem').style.display = 'block' : document.getElementById('hiringItem').style.display = 'none';
billingInfo['useScheduler'] == true ? document.getElementById('scheduleingItem').style.display = 'block' : document.getElementById('scheduleingItem').style.display = 'none';
document.getElementById('showPriceItem').innerHTML = billingInfo.price === "free" ? "free" : `${billingInfo['currency']['symbol']} ${billingInfo['price']}`;
document.getElementById('subtitlePrice').innerHTML = billingInfo.price === "free" ? "free /" : `${billingInfo['currency']['symbol']} ${billingInfo['price']} /`;
document.getElementById('subtitleUser').innerHTML = `${billingInfo['numberOfEmployee']} user /`

let monthValue = `${billingInfo['period']}` / 30;
let monthItem = monthValue.toFixed(0)
document.getElementById('subtitleMonth').innerHTML = `${monthItem} month`;    
                
let picPlan = document.getElementById('picPlan');

if (billingInfo.image != null) {
    picPlan.style.backgroundImage = `url(${billingInfo.image})`;
    picPlan.style.backgroundSize = '185px 320px';
}else {
    picPlan.style.backgroundImage = "url('img/image100.png')";
    picPlan.style.backgroundSize = '185px 320px';
}

document.getElementById('billingName').value = billingInfo.name;
document.getElementById('billingPrice').value = billingInfo.price == "free" ? "free" : `${billingInfo.price} ${billingInfo['currency']['symbol']}`;
document.getElementById('numberOfUser').value = billingInfo.numberOfEmployee;
document.getElementById('billingSchdule').value = billingInfo.useScheduler === true ? "Active" : "Deactive";
document.getElementById('billingAttendace').value = billingInfo.useAttendance === true ? "Active" : "Deactive";
document.getElementById('billingAvailabil').value = billingInfo.useAvailability === true ? "Active" : "Deactive";
document.getElementById('billHiring').value = billingInfo.useHiring === true ? "Active" : "Deactive";
document.getElementById('freeBilling').value = billingInfo.isDefault === true ? "Active" : "Deactive";
document.getElementById('currencyName').value = billingInfo['currency']['name'];
document.getElementById('perUsers').innerHTML = `${billingInfo['currency']['symbol']} 1`;
document.getElementById('subtotalSymbol').innerHTML = `${billingInfo['currency']['symbol']}`;
document.getElementById('taxSymbol').innerHTML = `${billingInfo['currency']['symbol']}`;
document.getElementById('taxSymbol').innerHTML = `${billingInfo['currency']['symbol']}`;
document.getElementById('totalSymbol').innerHTML = `${billingInfo['currency']['symbol']}`;

$(document).ready(function () {

    $('.example-popover').popover({
        container: 'body'
    })
    // console.log(fullname)
    $('.fullname h6').text(fullname);

    let table=$("#billing").DataTable({
        "columnDefs": [
            { "orderable": false, "targets":[0,3]  }
        ],
        "scrollY": "300px",
        "scrollCollapse": true,
        "paging":   false,
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdf',
                text: "<i class='fas fa-print'></i>",
                attr:  {
                    class: 'btn btn-custbl btn-sm ml-3 mt-2 mb-1'
                }
            }
        ],
        "select": true,
        "columns": [
            { data: 'ID',className:'ids'},
            { data: 'DESCRIPTION',className:'decsript' },
            { data: 'DATE',className:'date-bill' },
            { data: 'RECEIPT',className:'receipt-bill' }
        ],
        "ajax":{
            'type':'get',
            'url':base_url+'/api/payment_histories',
            'contentType': "application/json",
            'headers': {
                'Authorization': `Bearer ${tok}`,
            },
            "dataSrc": (json)=> {
 
                let data=json.map(obj=>{
                    let pay={};
                    let dateTime = moment(obj['createdAt']).format('YYYY/D/MM')

                    pay['ID']=obj['id'];
                    pay['DESCRIPTION']=obj['description'];
                    pay['DATE']= dateTime;
                    pay['RECEIPT']= " <button type='button'  title='Edit' class='btn btn-default btn-sm'>" +
                            "<i class='fas fa-print'></i>" +
                            " </button>";
                    return pay;
                });
                return data;
                
            }
        }
    });
});
let refreshBank = document.getElementById('refresh_bank');
refreshBank.setAttribute('checked', true);
let userTransfer = document.getElementById('selectUserTransfer');
getData(`${base_url2}/api/users`)
.then(data=> {
    // console.log(data['hydra:member']);
    let fullUsers = data['hydra:member'];
    fullUsers.forEach(user => {
        let userOPT = document.createElement('option');
        userOPT.innerHTML = `${user.firstName} ${user.lastName}`;
        userOPT.value = user['@id']
        userTransfer.appendChild(userOPT)
    });
})
.catch(err => {
    console.log(err)
})

accountTransfer.addEventListener('click', e => {
    e.preventDefault();
    let user = userTransfer.options[userTransfer.selectedIndex].value;
    let dataUser = {
        user: user,
        refresh_bank: refreshBank.checked ? true : false,
    }

    // console.log(dataUser);

    setTimeout(() => {        
        postData(base_url2 + '/api/business/transfer_ownership', dataUser)
        .then(data => {
            // console.log(data);
            setTimeout(() => {
                window.location.href = base_url2 + "/login";
            }, 2000);
        })
        .catch(err => {
            console.log(err);
        })
    }, 700);
})
