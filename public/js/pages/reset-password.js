
$('document').ready(function() {
    $('#showpass').on('click',function () {
        var x = document.getElementById("first");
        var y = document.getElementById("second");
        if(this.checked){
            x.type="text";
            y.type="text";
        }else{
            x.type="password";
            y.type="password";
        }
    });

    $('#first').on('change', function(){
        $('.reset-password').attr('disabled', 'disabled');
        $('#first').removeClass('is-invalid');
        $('#second').removeClass('is-valid');

        if(this.value.length < 8){ // checks the password value length

            $('#first').addClass('is-invalid');
            $(this).focus(); // focuses the current field.
            return false; // stops the execution.
        }else{

            $('#first').addClass('is-valid');

            if($('#second').hasClass('is-valid')){
                $('.signin').removeAttr('disabled');

            }
        }
    });

    $('#first, #second').on('keyup', function () {
        if ($('#first').val() == $('#second').val()) {

            $('.reset-password').removeAttr('disabled');
            $('#first').addClass('is-valid');
            $('#second').addClass('is-valid');
            $('#first').removeClass('is-invalid');
            $('#second').removeClass('is-invalid');

        } else {
            $('#first').addClass('is-invalid');
            $('#second').addClass('is-invalid');
            $('#first').removeClass('is-valid');
            $('#second').removeClass('is-valid');
        }

    });

    let query_string=(new URL(window.location.href)).search;
    let search_params = new URLSearchParams(query_string);
    let token=search_params.get('token');

    $('.reset-password').on('click',()=>{
        let loading = document.getElementById('imgLoading');
        let loginForm = document.querySelector('.reset-password');
        loading.style.display = 'block'
        loginForm.style.display = 'none'
        $.ajax({
            url: base_url2+'/api/reset_action',
            method: 'POST',
            contentType: "application/json",
            data: JSON.stringify({
                token: token,
                plain_password: {
                    first: $('#first').val(),
                    second:  $('#second').val()
                }
            }),
            success: function(r){
                // window.location.href=base_url2+'/login'
            },
            error:function(e){
                loading.style.display = 'none'
                loginForm.style.display = 'block'
            }

        });
    })


});
