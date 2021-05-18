let token = localStorage.getItem('token');
let redirectFlowId = JSON.parse(localStorage.getItem('redirect_flow_id'));
console.log(redirectFlowId);

let billingPlanCurrent = JSON.parse(localStorage.getItem('billingPlanCurrent'));
let fullName = localStorage.getItem('fullname');
let getCurrency = document.querySelector('.getCurrency');
let customerName = document.getElementById('customerName');
customerName.innerHTML = fullName;
let Billprice;
let BillID;

if (!token) {
    window.location.href = base_url + '/login';
}

async function postApi(url, data){
    let response = await fetch(url, {
        method: 'POST',
        headers : {
            'Content-Type': "application/json",
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(data),
    })
    let postResponse = await response.json();
    return postResponse;
}

async function getApi(url){
    let response = await fetch(url, {
        method: 'GET',
        headers : {
            'Content-Type': "application/json",
            'Authorization': `Bearer ${token}`,
        },
    })
    let getBillings = await response.json();
    return getBillings;
}

getApi(`${base_url}/api/billings`)
.then(data => {
    console.log(data);
    let allBillings = data['hydra:member'];
    allBillings.forEach(billing => {
        if (billingPlanCurrent === billing.name) {
            setTimeout(() => {

            }, 10000);



        }
    });

})
.catch(err => {console.log(err)})

let data = {
    redirect_flow_id: redirectFlowId
}

if (data.redirect_flow_id) {
    postApi(base_url+ `/api/business_banks/compelete`, data)
    .then(data => {
        console.log(data);
        console.log(data.redirect_flow_id);

        setTimeout(() => {

            let iriBill = {
                billing: billingPlanCurrent['@id']
            }
            console.log(data);

            postApi(base_url + '/api/business_banks/exchnage_billing_cost', iriBill)
            .then(data => {
                exchangData = data['hydra:member'];
                console.log(exchangData);

                let opt = document.createElement('option');
                opt.value = exchangData[1];
                opt.innerHTML = exchangData[1];
                getCurrency.appendChild(opt);
                Billprice = exchangData[0];
                document.getElementById('billingPrice').innerHTML = exchangData[0];
                document.getElementById('loadPay').style.display = 'none';
                document.getElementById('payBillings').style.display = 'block';
            })
            .then(err => console.log(err))

            BillID = billingPlanCurrent['@id'];;
            document.getElementById('billingName').innerHTML = billingPlanCurrent.name;
        }, 2000);

        setTimeout(() => {
            localStorage.removeItem('redirect_flow_id');
        }, 3000);
    })
    .catch(data => console.log(data))
}


document.getElementById('payBillings').addEventListener('click', e => {
    let optionVal = getCurrency.options[getCurrency.selectedIndex].value;
    document.getElementById('loadPay').style.display = 'block';
    document.getElementById('payBillings').style.display = 'none';

    e.preventDefault();
    let data = {
        amount: Billprice,
        billing: BillID,
        currency: optionVal
    }
    if (data.currency === "Select currency") {
        toastr.error('Please selected currency');
        document.getElementById('loadPay').style.display = 'none';
        document.getElementById('payBillings').style.display = 'block';
    } else {
        postApi(`${base_url}/api/business_banks/pay`, data)
        .then(data => {
            document.getElementById('loadPay').style.display = 'block';
            console.log(data);
            if (data['@type'] === "hydra:Error") {
                document.getElementById('loadPay').style.display = 'none';
                document.getElementById('payBillings').style.display = 'block';
                toastr.error(`${data['hydra:description']}`);
            }else{
                localStorage.removeItem('billingPlanCurrent');
                window.location.href = base_url + '/accounts'
            }
        })
        .catch(err => {
            console.log(err);
            document.getElementById('loadPay').style.display = 'none';
            document.getElementById('payBillings').style.display = 'block';
        })
    }


})
