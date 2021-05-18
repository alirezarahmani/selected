$(document).ready(function () {

    //check token if not set redirect to login page
    let tok=localStorage.getItem('token');

    if(tok == null){
        window.location.href = base_url2+"/login";
    }

    $('#modal-addAvail').on('hidden.bs.modal', function() {
        $(this)
            .find("input[type=text],textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();

        $('.timepart').show();
        $('.repeatpart').hide();
        $('#delete-avail').hide();
        $('#availeditId').val('');
        $('#repeated').val('');
        $('input[name="chbox"]').val('false');


    }) ;
    $('#modal-updateRepeated').on('hidden.bs.modal', function() {
        //update repeated modal
        $('#availIdRepeated').val('');
    });
    $('#modal-deleteRepeated').on('hidden.bs.modal', function() {
        //delete modal
        $('#availIdRepeateddel').val('');
    });

    $('.specificClose').on('click', function () {

        $('#modal-addAvail')
            .find("input[type=text],textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();

        $('.timepart').show();
        $('.repeatpart').hide();
        $('#delete-avail').hide();

        $('#availeditId').val('');
        $('#repeated').val('');

        $('input[name="chbox"]').val('false');

        //update repeated modal
        $('#availIdRepeated').val('');
        //delete modal
        $('#availIdRepeateddel').val('');
    });

    $('.select2').select2();
   // $('#employee-list').select2();

    //modal add prefer elements
   $('input[name="chbox"]').on('click', function () {
        $(this).val(this.checked ? true : false);

    });

    $('#alldayCh').on('click',function () {
            $('.timepart').toggle()

    });

    $('#repeats').on('click',function () {
        $('.repeatpart').toggle()

    });

    $('#timeStart ,#timeEnd').datetimepicker({
        format: 'LT'
    });

    $('#starting-avail').datetimepicker({
        format: 'DD/MM/YYYY',
        locale:  moment.locale('en', {
            week: { dow: 1 }
        })
    });

    $('#ending-repeat').datetimepicker({
        format: 'DD/MM/YYYY',
        locale:  moment.locale('en', {
            week: { dow: 1 }
        })

    });




});


