let selectCountry = document.getElementById('selectCountry');
let countryTimezone = document.getElementById('optSelect');
let optSelect = document.getElementById('optSelectCountry');
let createBusiness = document.getElementById('createBusiness');
let createBusiness1 = document.getElementById('createBusiness1');
let createBusinessPlus = document.getElementById('createBusinessPlus');
let tok = localStorage.getItem('token');
let dataCountry;
let countryName;
let alpha2Country;

$(document).ready(function () {
    //start login.js
    $('#email').on('click', function () {
        if ($('#email').val() !== '') {
            if (isValidEmailAddress($('#email').val())) {
                $('#email').addClass('is-valid');
                $('#email').removeClass('is-invalid');
            }
        }
    })

    $('#email').on('keyup', function () {
        $('#email').removeClass('is-valid');
        $('#email').removeClass('is-invalid');
        var userinput = $('#email').val();
        if (isValidEmailAddress(userinput)) {
            //true
            $('#email').addClass('is-valid');
            if ($('#password').hasClass('is-valid')) {

            }

        } else {
            //false
            $('#email').addClass('is-invalid');
        }

    });

    function isValidEmailAddress(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    }

    $('#password').on('keyup', function () {
        $('#password').removeClass('is-invalid');
        $('#password').removeClass('is-valid');

        if (this.value.length < 8) { // checks the password value length

            $('#password').addClass('is-invalid');
            $(this).focus(); // focuses the current field.
            return false; // stops the execution.
        } else {

            $('#password').addClass('is-valid');

            if ($('#email').hasClass('is-valid')) {
                $('.signin').removeAttr('disabled');

            }
        }
    });

    $('.signin').on('click', function (e) {
        e.preventDefault();
        let loading = document.getElementById('imgLoading');
        loading.style.display = 'block'
        let loginForm = document.querySelector('.signin');
        loginForm.style.display = 'none'

        let url = base_url + '/api/login';

        $.ajax({
            url: url,
            method: 'POST',
            contentType: "application/json",
            data: JSON.stringify({
                email: $("#email").val(),
                password: $("#password").val()
            }),
            success: async function (r) {
                console.log(r);
                await localStorage.setItem('token', r.token);
                if (r.token) {
                    getToken();
                } else {
                    checkToken()
                }

                function getToken() {
                    setTimeout(() => {
                        if (!localStorage.getItem('token')) {
                            return checkToken();
                        } else {
                            if (document.referrer === base_url + '/pricing') {
                                localStorage.setItem('pricing', JSON.stringify(document.referrer))
                                return location.replace(base_url2 + '/accounts');
                            } else {
                                return location.replace(base_url2 + '/accounts');
                            }
                        }
                    }, 2000)
                }

                function checkToken() {
                    setTimeout(() => {
                        if (!localStorage.getItem('token')) {
                            return getToken()
                        } else {
                            if (document.referrer === base_url + '/pricing') {
                                localStorage.setItem('pricing', JSON.stringify(document.referrer))
                                return location.replace(base_url2 + '/accounts');
                            } else {
                                return location.replace(base_url2 + '/accounts');
                            }
                        }
                    }, 2000)
                }
            },
            error: function (e) {

                let loading = document.getElementById('imgLoading');
                loading.style.display = 'none'
                let loginForm = document.querySelector('.signin');
                loginForm.style.display = 'block'

                if (e.status == 401) {
                    toastr.error('Email or Password is wrong');
                    $('#email').removeClass('is-valid');
                    $('#password').removeClass('is-valid');
                    $('#email').addClass('is-invalid');
                    $('#password').addClass('is-invalid');


                } else if (e.status == 400) {
                    toastr.error('Invalid input');
                    $('#email').removeClass('is-valid');
                    $('#password').removeClass('is-valid');
                    $('#email').addClass('is-invalid');
                    $('#password').addClass('is-invalid');
                } else if (e.status == 404) {
                    toastr.error('Resource not found');
                    $('#email').removeClass('is-valid');
                    $('#password').removeClass('is-valid');
                    $('#email').addClass('is-invalid');
                    $('#password').addClass('is-invalid');
                }
            }

        });

    });
    //end login.js

    //start register.js
    $('#emailreg').on('keyup', function () {
        $('.signup').attr('disabled', 'disabled');
        $('#emailreg').removeClass('is-valid');
        $('#emailreg').removeClass('is-invalid');
        let emailreg = $('#emailreg').val();
        if (isValidEmailAddress(emailreg)) {
            //true
            console.log('email true')
            $('#emailreg').addClass('is-valid');

        } else {
            //false
            console.log('email false')
            $('#emailreg').addClass('is-invalid');
        }

    });

    $('#passwordreg').on('keyup', function () {
        $('.signup').attr('disabled', 'disabled');
        $('#passwordreg').removeClass('is-invalid');
        $('#passwordreg').removeClass('is-valid');

        if (this.value.length < 8) { // checks the password value length

            $('#passwordreg').addClass('is-invalid');
            $(this).focus(); // focuses the current field.
            return false; // stops the execution.
        } else {

            $('#passwordreg').addClass('is-valid');
        }
    });

    $('.reginputs').keyup(function () {

        var empty = false;
        $('.reginputs').each(function () {
            if ($(this).val().length == 0) {
                empty = true;
            }
        });

        if (empty) {

            $('.signup').attr('disabled', 'disabled');
        } else {

            if ($('#agree').prop("checked") == true) {
                $('.signup').removeAttr('disabled');
            } else {
                $('.signup').attr('disabled', 'disabled');

            }

        }
    });

    $('#agree').on('click', function () {

        let emp = false;
        $('.reginputs').each(function () {
            if ($(this).val().length == 0) {
                emp = true;
            }
        });

        if (this.checked) {
            if (emp) {
                $('.signup').attr('disabled', 'disabled');
            } else {
                $('.signup').removeAttr('disabled');
            }

        } else {
            $('.signup').attr('disabled', 'disabled');
        }
    });


    //start createplace.js
    if ($('#company-name').val() !== null) {
        $('#createBusiness').prop("disabled", false);
    }

    $('#company-name-plus').on('change', function (e) {
        if ($('#company-name-plus').val() !== null && $('#company-name-plus').val() !== '') {
            $('#createBusinessPlus').prop("disabled", false);
        }
    });

    $('.search1').on('change', function (e) {
        if ($('.search1').val() !== '') {
            $('#createBusiness1').prop("disabled", false);
        }
    });

    $('#createBusiness1').on('click', function (e) {
        let loading = document.getElementById('imgLoading');
        loading.style.display = 'block'
        createBusiness1.style.display = 'none'

        alpha2Country = 'GB';
        let countryId = '/api/available_countries/1'
        let countryName = 'United Kingdom';
        if (localStorage.getItem('googleLocation')) {
            localStorage.setItem('address', $('#pac-input').val());
            localStorage.setItem('location', $('#latitude').val() + ',' + $('#langtitude').val());
            localStorage.setItem('country', countryId);
            let localtion = JSON.parse(localStorage.getItem('googleLocation'));
            let shortName;

            localtion['address_components'].forEach(item => {
                if (item.short_name.indexOf(alpha2Country) != -1) {
                    shortName = true;
                    if (alpha2Country != "Select country" || document.getElementById('pac-input').value != '') {
                        console.log(550);
                        window.location.href = base_url2 + "/createplus";
                        // $.ajax({
                        //     method:'POST',
                        //     url:base_url+'/api/businesses',
                        //     contentType: "application/json",
                        //     headers: {
                        //         'Authorization': `Bearer ${tok}`,
                        //     },
                        //     data:JSON.stringify({
                        //         name:$('#company-name').val(),
                        //         address:$('#pac-input').val(),
                        //         location:$('#latitude').val()+','+$('#langtitude').val(),
                        //         country: countryId
                        //     }),
                        //     success:(res)=>{
                        //         console.log(res);
                        //         localStorage.setItem('createbusId', res['id']);
                        //         toastr.success('Added workplace');
                        //         window.location.href = base_url2+"/create-attendance";
                        //     },
                        //     error:(e)=>{
                        //         // console.log(e)
                        //         let loading = document.getElementById('imgLoading');
                        //         loading.style.display = 'none'
                        //         createBusiness.style.display = 'block'
                        //         toastr.error('Something wrong,Try again!')
                        //     }
                        // });
                    } else {
                        let loading = document.getElementById('imgLoading');
                        loading.style.display = 'none'
                        createBusiness1.style.display = 'block'
                        toastr.error(`Please check the select country and workplace address.`);
                    }
                }
            })

            setTimeout(() => {
                if (shortName != true || !shortName) {
                    toastr.error(`This address does not exist in ${countryName}.`);
                    let loading = document.getElementById('imgLoading');
                    loading.style.display = 'none'
                    createBusiness1.style.display = 'block'
                }
            }, 1300);
        }

    });


    $('#createBusinessPlus').on('click', function (e) {

        let loading = document.getElementById('imgLoading');
        let time = 0;
        loading.style.display = 'block'
        createBusinessPlus.style.display = 'none'

        if (localStorage.getItem('address') &&  localStorage.getItem('address') !== '' && $('#company-name-plus').val() !== null && $('#company-name-plus').val() !== '') {
            $.ajax({
                method: 'POST',
                url: base_url + '/api/businesses',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify({
                    name: $('#company-name-plus').val(),
                    address: localStorage.getItem('address'),
                    location: localStorage.getItem('location'),
                    country: localStorage.getItem('country')
                }),
                success: (res) => {
                    time = 1;
                    localStorage.setItem('createbusId', res['id']);
                    toastr.success('Added workplace');
                    window.location.href = base_url2 + "/create-attendance";
                },
                error: (e) => {
                    // console.log(e)
                    let loading = document.getElementById('imgLoading');
                    loading.style.display = 'none'
                    createBusiness.style.display = 'block'
                    toastr.error('Something wrong,Try again!')
                }
            });
        } else {
            toastr.error(`Please check the select country and workplace address.`);
        }
        setTimeout(() => {
            if (time === 1) {
                let loading = document.getElementById('imgLoading');
                loading.style.display = 'none'
                createBusinessPlus.style.display = 'block'
            }
        }, 1300);
    });


    $('#createBusiness').on('click', function (e) {

        let loading = document.getElementById('imgLoading');
        loading.style.display = 'block'
        createBusiness.style.display = 'none'

        alpha2Country = optSelect.options[optSelect.selectedIndex].value;
        let countryId;
        dataCountry.forEach(country => {
            if (country.name === alpha2Country) {
                countryName = country.longName;
                countryId = country['@id'];
            }
        })

        if (localStorage.getItem('googleLocation')) {
            let localtion = JSON.parse(localStorage.getItem('googleLocation'));
            let shortName;

            localtion['address_components'].forEach(item => {
                if (item.short_name.indexOf(alpha2Country) != -1) {
                    shortName = true;
                    if (alpha2Country != "Select country" || document.getElementById('pac-input').value != '') {
                        $.ajax({
                            method: 'POST',
                            url: base_url + '/api/businesses',
                            contentType: "application/json",
                            headers: {
                                'Authorization': `Bearer ${tok}`,
                            },
                            data: JSON.stringify({
                                name: $('#company-name').val(),
                                address: $('#pac-input').val(),
                                location: $('#latitude').val() + ',' + $('#langtitude').val(),
                                country: countryId
                            }),
                            success: (res) => {
                                console.log(res);
                                localStorage.setItem('createbusId', res['id']);
                                toastr.success('Added workplace');
                                window.location.href = base_url2 + "/create-attendance";
                            },
                            error: (e) => {
                                // console.log(e)
                                let loading = document.getElementById('imgLoading');
                                loading.style.display = 'none'
                                createBusiness.style.display = 'block'
                                toastr.error('Something wrong,Try again!')
                            }
                        });
                    } else {
                        toastr.error(`Please check the select country and workplace address.`);
                    }
                }
            })

            setTimeout(() => {
                if (shortName != true || !shortName) {
                    toastr.error(`This address does not exist in ${countryName}.`);
                    let loading = document.getElementById('imgLoading');
                    loading.style.display = 'none'
                    createBusiness.style.display = 'block'
                }
            }, 1300);
        }

    });
    //end createplace.js

    //start account.js
    $('.placeinputs').keyup(function () {

        var empty = false;
        $('.placeinputs').each(function () {
            if ($(this).val().length == 0) {
                empty = true;
            }
        });

        if (empty) {
            $('.startBtn').attr('disabled', 'disabled');
        } else {
            $('.startBtn').removeAttr('disabled');
        }
    });
    $('.startBtn').on('click', function (e) {
        e.preventDefault();
        window.location.href = base_url2 + "/search";

    });
    //end account.js

//log out clear storage
    $('.logouting').on('click', function () {
        console.log('clear');
        localStorage.clear();
        sessionStorage.clear();
    });

    //start forget.js
    $('.forgetpass').on('click', function () {
        let loading = document.getElementById('imgLoading');
        loading.style.display = 'block'
        let loginForm = document.querySelector('.forgetpass');
        loginForm.style.display = 'none'
        $.ajax({
            method: 'POST',
            url: base_url + '/api/reset_request',
            contentType: "application/json",
            data: JSON.stringify({
                email: $('#forget-email').val()

            }),
            success: (res) => {
                // console.log(res)
                toastr.success('We sent an email to you for changing your password!');
                setTimeout(function () {
                    window.location.href = base_url2 + "/login";
                }, 3000);
            },
            error: (e) => {
                let loading = document.getElementById('imgLoading');
                loading.style.display = 'none';
                let loginForm = document.querySelector('.forgetpass');
                loginForm.style.display = 'block'
                // console.log(e)
                toastr.error('Something wrong,Try again!')
            }


        });
    });
    //end forget.js


});

let signupUser = document.querySelector('.signup');
if (signupUser) {
    $('.timezoneSelect').select2();

    async function getTimezone(url) {
        let response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': "application/json",
            }
        });
        let timezone = await response.json();
        return timezone;
    }

    getTimezone(base_url + '/api/get_timezone')
        .then(data => {
            let optGroup = document.createElement('optgroup');
            for (let i = 0; i < data.length; i++) {
                optGroup.label = "Select Timezone";
                countryTimezone.appendChild(optGroup);
                let opt = document.createElement('option');
                opt.innerHTML = data[i];
                opt.value = data[i];
                optGroup.appendChild(opt);
            }
        })
        .catch(err => console.log(err))

    async function registerUser(url, data) {
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': "application/json",
            },
            body: JSON.stringify(data),
        });
        let singUp = await response.json();
        return singUp;
    }

    signupUser.addEventListener('click', signupForm)

    function signupForm(e) {
        e.preventDefault();

        let loading = document.getElementById('imgLoading');
        loading.style.display = 'block'
        signupUser.style.display = 'none'

        let timezone = document.getElementById('useTimezone');
        let valSelect = countryTimezone.options[countryTimezone.selectedIndex].value;
        let valTimezone;
        if (timezone.checked) {
            valTimezone = true;
        } else {
            valTimezone = false;
        }
        let data = {
            firstName: document.getElementById('firstname').value,
            lastName: document.getElementById('lastname').value,
            email: document.getElementById('emailreg').value,
            password: document.getElementById('passwordreg').value,
            mobile: document.getElementById('mobile').value,
            useCustomTimezone: valTimezone,
            timezone: valSelect
        }

        console.log(data);

        registerUser(base_url + '/api/register', data)
            .then(data => {
                if (data.email === "It looks like your already have an account!") {
                    toastr.error(data.email);
                    let loading = document.getElementById('imgLoading');
                    loading.style.display = 'none'
                    signupUser.style.display = 'block'
                } else if (data.mobile === "the phone number is not valid") {
                    toastr.error(data.mobile);
                    let loading = document.getElementById('imgLoading');
                    loading.style.display = 'none'
                    signupUser.style.display = 'block'
                } else {

                    document.querySelector('.successCallout').style.display = "block";
                    loading.style.display = 'none'
                    signupUser.style.display = 'block'
                    setTimeout(function (){
                        window.location.href = base_url2 + "/accounts";
                    }, 3000)
                }
            })
            .catch(err => {
                let loading = document.getElementById('imgLoading');
                loading.style.display = 'none'
                signupUser.style.display = 'block'
                toastr.error(err['responseJSON']['email']);
            })

    }

    let registerFree = JSON.parse(localStorage.getItem('registerFree'));
    if (registerFree) {
        let name = registerFree.name.split(' ');
        document.getElementById('firstname').value = name[0];
        document.getElementById('lastname').value = name[1];
        document.getElementById('emailreg').value = registerFree.email;
    }


}

if (createBusiness) {

    async function getAlpha_2Country(url) {
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

    getAlpha_2Country(base_url + '/api/available_countries')
        .then(data => {
            dataCountry = data['hydra:member'];
            data['hydra:member'].forEach(conuntry => {
                let opt = document.createElement('option');
                opt.innerHTML = `${conuntry.name} (${conuntry.longName})`;
                opt.value = conuntry.name;
                optSelect.appendChild(opt);
            });
        })
        .catch(err => console.log(err))

}

