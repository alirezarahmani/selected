let formSubmit = document.getElementById('registerFree');
let choosePlanScheduling = document.getElementById('chooseScheduling');
let choosePlanPremium = document.getElementById('choosePlanPremium');
let choosePlanAttendance = document.getElementById('choosePlanAttendance');

let token = localStorage.getItem('token');
let roleEmployee = localStorage.getItem('role');
let sellerBilling = JSON.parse(localStorage.getItem('billing'));
let plans = document.getElementById('choosePlanDIV');
let calculator_item = document.getElementById('calculator_item');

if (plans) {
    async function getApi(url){

        let response = await fetch(url, {
            method: 'GET',
            headers : {
                'Content-Type': "application/json",
            },
        })
        let getBillings = await response.json();
        return getBillings;
    }
    async function postData (url,data) {
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': "application/json",
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(data),
        });
        let Schedule = await response.json();
        return Schedule;
    };


    getApi(`${base_url}/api/billings`)
        .then(data => {
            let allPlan = data['hydra:member'];

            allPlan.map(plan => {
                if (plan.name !== "default") {
                    console.log(plan, 'plan');
                    console.log(sellerBilling, 'sellerBilling')
                    let input = document.createElement('div');
                    input.classList.add("column-14", "w-col", "w-col-4")
                    input.innerHTML = `
                    <div class="div-block-49">
                        <div class="div-block-50">
                        <img src="${plan.image ? plan.image['filePath'] : ''}" 
                        sizes="(max-width: 479px) 69vw, (max-width: 767px) 32vw, (max-width: 991px) 22vw, 200px" alt="" class="image-9"></div>
                        <h3 class="heading-8 scheduling">${plan.name}</h3>
                        <h3 class="heading-8-copy">${plan.currency.symbol} ${plan.price}</h3>
                        <p class="paragraph-4">Per user, per month<br>EXCL VAT</p>
                        <div class="div-block-53">
                            <div class="div-block-52">
                                <img src="img/right.jpg" width="15" height="15" alt="">
                                <p class="paragraph-5">7+ Days of ${plan.name}</p>
                            </div>
                        </div>
                        <div class="div-block-53" style="display: ${plan.useAttendance === true ? 'block' : 'none'} ">
                            <div class="div-block-52">
                                <img src="img/right.jpg" width="15" height="15" alt="">
                                <p class="paragraph-5">Attendance</p>
                            </div>
                        </div>
                        <div class="div-block-53" style="display: ${plan.useAvailability === true ? 'block' : 'none'}">
                            <div class="div-block-52">
                                <img src="img/right.jpg" width="15" height="15" alt="">
                                <p class="paragraph-5">Availability</p>
                            </div>
                        </div>
                        <div class="div-block-53" style="display: ${plan.useHiring === true ? 'block' : 'none'}">
                            <div class="div-block-52">
                                <img src="img/right.jpg" width="15" height="15" alt="">
                                <p class="paragraph-5">Hiring</p>
                            </div>
                        </div>
                        <div class="div-block-53" style="display: ${plan.useScheduler === true ? 'block' : 'none'}">
                            <div class="div-block-52">
                                <img src="img/right.jpg" width="15" height="15" alt="">
                                <p class="paragraph-5">Scheduler</p>
                            </div>
                        </div>
                        <div class="div-block-51" style="display: ${roleEmployee && roleEmployee === 'employee' || roleEmployee === 'manager' ? 'none' : 'flex'}">
                            <a id="actionPlan" style="display: none">${plan.id}</a>
                            <span style="cursor: ${sellerBilling && sellerBilling.id == plan.id ? 'none' : 'pointer'}" 
                            class="${sellerBilling && sellerBilling.id == plan.id ? 'tab-link-fill-shifts-copy w-button currentText' : 'tab-link-fill-shifts-copy w-button'}">
                            ${ !token ? 'Choose plan' : (!sellerBilling ? 'Choose plan' : (sellerBilling && sellerBilling.id != plan.id ? 'Upgrade plan' : 'Current plan') ) }
                            </span>
                    </div>
                `

                    document.getElementById('choosePlanDIV').appendChild(input);

                    input.addEventListener("click", e => {
                        if (e.target.previousElementSibling.id || e.target.previousElementSibling.id === "actionPlan"){
                            if (plan.name !== "default") {
                                let id = parseFloat(e.target.previousElementSibling.innerHTML);
                                if (!sellerBilling || sellerBilling.id != id) {

                                    if (id === plan.id) {

                                        function redirectToGoCardLess() {
                                            async function goCard(url) {
                                                let response = await fetch(url,{
                                                    method: 'GET',
                                                    headers : {
                                                        'Content-Type': "application/json",
                                                        'Authorization': `Bearer ${token}`,
                                                    },
                                                });
                                                let goBank = await response.json();
                                                return goBank;
                                            }
                                            goCard(base_url+'/api/business_banks/set_bank')
                                                .then(data => {
                                                    // console.log(data)
                                                    let url = data.url;
                                                    localStorage.setItem('redirect_flow_id', JSON.stringify(data.ID));
                                                    setTimeout(() => {
                                                        window.location.href = url
                                                    }, 1000);
                                                })
                                                .catch(err => console.log(err))
                                        }

                                        if (token) {
                                            redirectToGoCardLess();
                                            localStorage.setItem('billingPlanCurrent', JSON.stringify(plan));
                                            localStorage.setItem('selectedTimePay', JSON.stringify('ok'));
                                        }else {
                                            window.location.href = base_url+'/login';
                                            localStorage.setItem('selectedTimePay', JSON.stringify('ok'));
                                            localStorage.setItem('billingPlanCurrent', JSON.stringify(plan));
                                        }

                                    }

                                }

                            }
                        }
                    })
                }

            })

        })
        .catch(err => {console.log(err)})

    // calculate

    getApi(`${base_url}/api/pricing_questions`)
        .then(data => {
            console.log('---------------------sdfdsfsdfs',data)
            let questions = data['hydra:member'];

            // if (questions.length > 0){
                document.getElementById('_cal_box').style.display = 'block';
                let arr = [];
                let obj = {};
                console.log(data, 'dataCalculate');
                questions.map( (data, index) => {
                    console.log(index, 'index data')

                    // let optCaculat = document.createElement('div');
                    // optCaculat.classList.add("column-17", "w-col", "w-col-4");
                    // optCaculat.innerHTML = `
                    //     <div class="calculator-div">
                    //         <img src="${base_url}${data.media['filePath']}" alt="" class="image-10">
                    //         <h3 class="heading-11">${data.question}</h3>
                    //         <div class="div-block-57" style="border-radius: 5px !important">
                    //             <input type="number" class="answerQuestions" style="width: 100%; height: 100%; border: none; border-radius: 14px; text-align: center; font-size: 25px;">
                    //         </div>
                    //         <div class="div-block-59">
                    //             <p class="paragraph-7">${data.description}</p>
                    //         </div>
                    //     </div>
                    // `;
                    // calculator_item.appendChild(optCaculat);

                    let _box_cal = document.createElement('div');
                    _box_cal.classList.add("column-17", "w-col", "w-col-4");
                    _box_cal.innerHTML = `
                   
                     <div class="calculator-div"><img src="${index == 0 ? 'img/calendar.jpg' : (index == 1 ? 'img/notes.jpg' : (index == 2 ? 'img/users.jpg' : ''))}" alt="" class="image-10">
                        <h3 class="heading-11">${data.question}</h3>
                        <div class="div-block-57" style="border-radius: 5px !important">
                            <input type="number" class="answerQuestions" style="width: 100%; height: 100%; border: none; border-radius: 14px; text-align: center; font-size: 25px;">
                        </div>
                        <div class="div-block-59">
                            <p class="paragraph-7">${data.description}</p>
                        </div>
                    </div>
                 
                `;

                    calculator_item.appendChild(_box_cal);

                    document.getElementById('calculationQuestions').addEventListener('click', e => {
                        let id = data['@id'];
                        let value = `${_box_cal.firstElementChild.firstElementChild.nextElementSibling.nextElementSibling.firstElementChild.value}`;
                        console.log(value, 'valueQuestions');
                        obj = {id, value};
                        arr.push(obj);
                    })
                })

                document.getElementById('calculationQuestions').addEventListener('click', (e,callBacks) => {
                    let currectInput;
                    arr.map(feild => {
                        if (feild.value === ''){
                            currectInput = false;
                        }
                    })
                    let data = {"answers" : {}};

                    arr.map((setVal) => {
                        let keys = setVal.id,
                            value = setVal.value;
                        data.answers[keys] = value
                    })

                    setTimeout(()=>{
                        if (currectInput === false) {
                            toastr.error('Please fill vacancies')
                        }else {
                            postData(`${base_url}/api/pricing_questions/total_all_question`, data)
                                .then(data => {
                                    // console.log(data);
                                    toastr.success('The formula was added.');
                                })
                                .catch(err => {
                                    console.log(err);
                                    toastr.error('The error occur.');
                                })
                        }

                    },1000)

                })
            // }

        })
        .catch(err => {
            console.log(err)
        })
}

let alertRegisterForFree = document.getElementById('alertRegisterForFree');

loadAllEvents();
function loadAllEvents() {
    formSubmit.addEventListener('submit', getFormFree);
}

function getFormFree(e) {
    e.preventDefault();

    let data = {
        name: e.target[0].value,
        email: e.target[1].value
    }
    if (data.name !== "" && data.email !== "") {
        localStorage.setItem('registerFree', JSON.stringify(data));
        window.location.href = base_url+'/register'
    }else {
        alertRegisterForFree.style.display = 'block';
    }
}

