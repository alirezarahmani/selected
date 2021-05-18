function showChar(a,b){
    var x=document.getElementById(a);
    var c=document.getElementById(b);
    console.log('ccc',x)
    console.log('ddd',c)
    if (x.getAttribute('type') == "password") {
        c.removeAttribute("class");
        c.setAttribute("class","fas fa-eye");
        x.removeAttribute("type");
        x.setAttribute("type","text");
    } else {
        x.removeAttribute("type");
        x.setAttribute('type','password');
        c.removeAttribute("class");
        c.setAttribute("class","fas fa-eye-slash");
    }
}
$('document').ready(function() {

    let query_string=(new URL(window.location.href)).search;
    let search_params = new URLSearchParams(query_string);
    let token=search_params.get('token');


    $('.set-password').on('click',()=>{

        let loading = document.getElementById('imgLoading');
        let loginForm = document.querySelector('.set-password');
        loading.style.display = 'block'
        loginForm.style.display = 'none'

        $.ajax({
            url: base_url2+'/api/set-password',
            method: 'POST',
            contentType: "application/json",
            data: JSON.stringify({
                password: $("#password").val(),
                token: token
            }),
            success: function(r){

                toastr.success('Your Password successfully Changed!');
                setTimeout(function(){
                    window.location.href = base_url2+"/login";
                }, 3000);

            },
            error:function(e){
                loading.style.display = 'none'
                loginForm.style.display = 'block'
            }

        });
    })


});
