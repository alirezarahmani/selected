let billings = JSON.parse(localStorage.getItem('billing'));
let personRoles = localStorage.getItem('role');

if (billings['useScheduler'] === false || personRoles === "employee") {
    window.location.href = base_url+'/profile'
}else {

    
    $(document).ready(function () {
        //check token if not set redirect to login page
        let tok = localStorage.getItem('token');
        if (tok == null) {
            window.location.href = base_url2 + "/login";
        }
        
        setTimeout(() => {
            document.getElementById('setLoading').style.display = 'none'
        }, 5000);

        $('.select2').select2();
        $('.viewShift').select2();
        $('select.viewShift option[value="shift"]').attr("selected", true);

        //modal add shift elements
        $('input[name="chbox"]').on('click', function () {
            $(this).val(this.checked ? true : false);

        });
        $('input[name="repeatShitf"]').on('click', function () {
            $(this).val(this.checked ? true : false);
            this.checked? $('.repeated').show(): $('.repeated').hide();

        });
        $('input[name="saveAstemp"]').on('click', function () {
            $(this).val(this.checked ? true : false);

        });
        $('input[name="allDay"]').on('click', function () {
            $(this).val(this.checked ? true : false);

        });


        $('#repeatShitf').on('click', function () {
            if ($('#repeatShitf').val() == true) {
                $('#repeatShitf').attr("checked");
            } else {
                $('#repeatShitf').removeAttr("checked");
            }
        });

        $('#allDay').on('click', function () {
            $(this).val(this.checked ? true : false);

            if ($(this).is(':checked')) {
                $(".time-range").hide();
                $(".date-range").show();

                if ($("#type-off option:selected").val() == 'Unpaid') {
                    $('.paid').hide();

                } else {
                    $('.paid').show();
                }

            } else {
                $(".time-range").show();
                $(".date-range").hide();
                $('.paid').hide()
            }

        });


        //start js for employee modal ===============================================================================================================
        $('#fixed').click(function () {

            if( $(this).is(':checked')) {

                $(".contractCondition").css('display','block');
                $('#fixed').val('fixed');

            } else {

                $(".contractCondition").css('display','none');


            }
        });

        $('#zero').click(function () {

            if( $(this).is(':checked')) {

                $(".contractCondition").css('display','block');
                $('#zero').val('zero');
                $(".contractCondition").css('display','none');

            } else {

                $(".contractCondition").css('display','block');

            }
        });

        $('.useTimezone').on('click',function () {
            if($('#useTimezone').is(':checked' )){
                $('.use-timezone').prop('disabled',false);
            }else{
                $('.use-timezone').prop('disabled',true);
            }

        });

        $('#modal-addemp').on('hidden.bs.modal', function (e) {
            console.log('closed modal')
            $(this)
                .find("input[type=text],textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();


            $("#vert-detail-tab").addClass("active");
            $("#vert-schedule-tab").removeClass("active");
            $("#vert-payroll-tab").removeClass("active");
            $("#vert-note-tab").removeClass("active");
            $("#vert-advance-tab").removeClass("active");

            $("#schedule-tab").removeClass("active show");
            $("#payroll-tab").removeClass("active show");
            $("#log-tab").removeClass("active show");
            $("#advance-tab").removeClass("active show");
            $("#detail-tab").addClass("active show");

            $('.contractCondition').css('display','none');
            $('#email').removeAttr("disabled");

            $('.timeoffBtn').show();
            $('.notifyBtn').hide();
            $('.historyBtn').hide();



        });

        // role List in modal
        $.ajax({
            url: base_url+'/api/get_avail_roles',
            method: 'GET',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,
            },
            success: function(r){


                r.forEach(function (el) {

                    $('select.role-list').append("<option value="+el+">"+el+"</option>");

                });

            },
            error:function(e) {

                // console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });

        //timezone list in modal
        $.ajax({
            url: base_url+'/api/get_timezone',
            method: 'GET',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            success: function(r){

                r.forEach(function (el) {
                    // console.log(el)
                    $('select.seltimezone').append("<option value="+el+">"+el+"</option>");

                });

            },
            error:function(e) {

                // console.log(e)
                //expire jwt token
                if(e.status == 401){
                    window.location.href = base_url2+"/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });

        //==============================================================================================================================================

        //color picker with addon
        $('.colorShifts').colorpicker();

        $('.colorShifts').on('colorpickerChange', function (event) {

            $('.colorShifts .fa-square').css('color', event.color.toString());
        });

        $('#timeStart ,#timeEnd,#timeStartoff,#timeEndoff').datetimepicker({
            format: 'LT',
            locale:  moment.locale('en', {
                week: { dow: 1 }
            })
        });

        $('#ending-shift,#start-date-timeoff,#end-date-timeoff').datetimepicker({
            format: 'L',
            minYear: 1901,
            locale:  moment.locale('en', {
                week: { dow: 1 }
            })
        });

        $('#start-date-annot,#end-date-annot,#start-date-notify,#end-date-notify').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            maxYear: parseInt(moment().format('YYYY'), 10),
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        $('.timeoffBtn').on('click', function () {
            $('.content-addshift').hide();
            $('.content-timeoff').show();

        });

        $('.specificClose').on('click', function () {

            $('.content-qualified').show();
            $('.content-addshift').hide();
            $('.content-history').hide();
            $('.content-timeoff').hide();
            $('.content-eligible').hide();
            $('.change-content').show();
            $('.content-replace').hide();
            $('.howmanyOS').hide();
            $('.backToShiftBtn').hide();
            $('.forOpenShift').removeClass('col-8');
            $('.forOpenShift').addClass('col-10');
            $('.qualified').empty();
            $('#modal-addEvents')
                .find("input,textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();

            $('#add-shift').show();
            $('#add-shift-publish').show();
            $('#save-shift-unpublish').hide();
            $('#delete-shift').hide();
            $('#replace-shift').hide();
            $('#view-req').hide();
            $('.eligibleEmp').hide();


            $('.timeoffBtn').show();
            $('.notifyBtn').hide();
            $('.historyBtn').hide();
            $('ul.timelineShift').html('');
            $('.colorpicker-guide').css({'top':'122px','left':'2px'});
            $('.fas.fa-square').css('color','rgb(202, 204, 213)');



        });

        $('#modal-addEvents').on('hidden.bs.modal', function () {
            $('.content-qualified').show();
            $('.content-addshift').hide();
            $('.content-history').hide();
            $('.content-timeoff').hide();
            $('.content-eligible').hide();
            $('.change-content').show();
            $('.content-replace').hide();
            $('.howmanyOS').hide();
            $('.backToShiftBtn').hide();
            $('.forOpenShift').removeClass('col-8');
            $('.forOpenShift').addClass('col-10');
            $('.qualified').empty();
            $(this)
                .find("input[type=text],textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();

            $('#add-shift').show();
            $('#add-shift-publish').show();
            $('#save-shift-unpublish').hide();
            $('#delete-shift').hide();
            $('#replace-shift').hide();
            $('#view-req').hide();
            $('.eligibleEmp').hide();
            $('ul.timelineShift').html('');
            $('.colorpicker-guide').css({'top':'122px','left':'2px'});
            $('.fas.fa-square').css('color','rgb(202, 204, 213)');
            $('#positionList').val(null).trigger('change');
            $('#jobsiteList').val(null).trigger('change');

        });

        $('#modal-addAnnot').on('hidden.bs.modal', function () {
            $(this)
                .find("input[type=text],textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();
            $('.select2').val([]).trigger('change');

            $('.fa-square').css('color','rgb(205, 218, 227)');
            $('.colorpicker-guide').css({'top':'122px','left':'2px'});
            $('#annotEditId').val('');
            $('.finaldeleteAnnot').hide();
            $('.cancelDel').hide();
            $('.deleteAnnot').hide();


        });

        $('#modal-deleteShift').on('hidden.bs.modal', function () {
            $(this)
                .find("input[type=text],textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();

            $('ul.timelineDelete').html('');



        });

        $('#type-off').on('change', function () {
            if ($('#allDay').prop("checked") == true) {
                if ($("#type-off option:selected").val() == 'Unpaid') {
                    $('.paid').hide();

                } else {
                    $('.paid').show();
                }
            }
        });

        $('.historyBtn').click(() => {
            $('.content-history').show();
            $('.content-addshift').hide();

            let shiftIdHistory = $('#shiftEditID').val();
        });

        $('.backBtn').click(() => {
            $('.content-history').hide();
            $('.content-addshift').show();
        });

        $('.backToShiftBtn').on('click', function () {
            $('.change-content').show();
            $('.content-eligible').hide();
            $('.backToShiftBtn').hide();
        });

        $('.createTemp').on('click', function () {

            $('.content-addshift').show();
            $('.content-qualified').hide();

            $('#add-shift').show();
            $('#add-shift-publish').show();
            $('#save-shift-unpublish').hide();
            $('#delete-shift').hide();
            $('#replace-shift').hide();
            $('#view-req').hide();
            $('.eligibleEmp').hide();
            $('ul.timelineShift').html('');
            $('.colorpicker-guide').css({'top':'122px','left':'2px'});

        });

        //changing value of other select employee
        $('#employeeList').on('change', function (e) {
            $('#empList').val($('#employeeList').children("option:selected").val()).trigger('change');
            $('#selemptimeoff').val($('#employeeList').children("option:selected").val()).trigger('change');
        });


        //show eligible employee List
        $('.eligibleEmpList').on('click', function () {
            $('.change-content').hide();
            $('.content-eligible').show();
            $('.backToShiftBtn').show();

            //get eligible List
            $.ajax({
                method: 'POST',
                url: base_url + '/api/users/get_eligible',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify({
                    positionId: $('#positionList').val(),
                    scheduleId: '/api/schedules/' + $('#schedule-ls').val(),
                    startTime: $('#timeStartInput').val(),
                    endTime: $('#timeEndInput').val()
                }),
                success: (r) => {
                    console.log('eligible', r['hydra:member']);
                    let eligibles = r['hydra:member'];
                    let elArray = [];
                    eligibles.map((elig) => {
                        // console.log('eligs',elig)

                        elArray.push(' <div class="col-5 border rounded m-2 p-2" style="background-color: white">\n' +
                            '                            <div class="d-inline"><img src="../../img/pic4.png" style="height: 30px;width: 30px;"></div>\n' +
                            '                            <div class="d-inline">\n' +
                            '                                <label class="mr-1">' + elig['firstName'] + ' ' + elig['lastName'] + '</label>\n' +
                            '                                <input class="eligChbox" type="checkbox" value="' + elig['@id'] + '" checked="true">\n' +
                            '\n' +
                            '                            </div>\n' +
                            '                        </div>');
                    });
                    $('.eligibles').html(elArray);


                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });

        });


        $('.backDelBtn').on('click', function () {

            $('.content-delete').hide();
            $('.content-delete-history').show();
        });


        $('#replace-shift').on('click', function () {
            let srtIdShift = '/api/shifts/' + $('#shiftEditID').val();
            $('#strIdShift').val(srtIdShift);
            $('.content-addshift').hide();
            $('.content-replace').show();

            let eligDataRep = {
                positionId: $('#positionList').val(),
                scheduleId: '/api/schedules/' + $('#schedule-ls').val(),
                startTime: ($('#selectedDate').val()) + ($('#timeStartInput').val()),
                endTime: (($('#selectedDate').val()) + $('#timeEndInput').val())
            };
            // console.log('eligible data replace',eligDataRep)

            //get eligible List for replace
            $.ajax({
                method: 'POST',
                url: base_url + '/api/users/get_eligible',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(eligDataRep),
                success: (r) => {
                    console.log('eligible', r['hydra:member']);
                    let eligibles = r['hydra:member'];
                    let elArray = [];
                    eligibles.map((elig) => {
                        console.log('eligs', elig)

                        elArray.push(' <div class="col-6 pt-2 pb-2 border rounded" style="background-color: white">\n' +
                            '                            <div class="d-inline"><img src="../../img/pic4.png" style="height: 30px;width: 30px;"></div>\n' +
                            '                            <div class="d-inline">\n' +
                            '                                <label class="mr-1">' + elig['firstName'] + ' ' + elig['lastName'] + '</label>\n' +
                            '                                <input class="eligChbox" type="checkbox" value="' + elig['@id'] + '" checked="true">\n' +
                            '\n' +
                            '                            </div>\n' +
                            '                        </div>');
                    });
                    $('.eligReplace').html(elArray);


                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                }
            });
        });

        $('#offer-shift').on('click', function () {

            let swapsData = [];

            $('.eligChbox').each(function () {
                let objRep = {};

                if (this.checked == true) {

                    objRep['user'] = $(this).val();
                    swapsData.push(objRep);

                }
            });
            console.log('offer shifts', swapsData)


            let repData = {
                type: 'replace',
                requesterShift: $('#strIdShift').val(),
                message: $('#replace-note').val(),
                swaps: swapsData
            };
            console.log('replace data', repData);

            //send shift request for offer shift
            $.ajax({
                method: 'POST',
                url: base_url + '/api/shift_requests',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(repData),
                success: (r) => {
                    //console.log(r)
                    toastr.success('Shift Request Successfully Sent.');
                    $('.content-replace').hide();


                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });


        });

        //view Request
        $('#view-req').on('click', function () {

            let reqid = $(this).data('reqid');
            localStorage.setItem('shRreId', reqid);
            location.replace(base_url2 + '/shiftrequest-detail');

        });

        //show delete button for delete annotation
        $('.deleteAnnot').on('click', function () {
            $('.deleteAnnot').hide();
            $('.cancelDel').show();
            $('.finaldeleteAnnot').show();

        });

        $('.cancelDel').on('click', function () {
            $('.deleteAnnot').show();
            $('.cancelDel').hide();
            $('.finaldeleteAnnot').hide();
        });


        //button in modal delete for delete shift
        $('.historyToBtn').click(() => {

            $('.historyToBtn').hide();
            $('.backtoBtn').show();
            $('.content-history-delete').show();
            $('.content-no-history').hide();

        });

        $('.backtoBtn').click(() => {

            $('.backtoBtn').hide();
            $('.historyToBtn').show();
            $('.content-history-delete').hide();
            $('.content-no-history').show();
        });

        $('#end-repeated-date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            maxYear: parseInt(moment().format('YYYY'),10),
            locale: {
                format: 'DD/MM/YYYY'
            }
        });


    });
}

let setColors = (e) => {
    console.log(e, 'changeColorForBG')
    document.getElementById('changeColorForBG').style.backgroundColor = e.srcElement.style.backgroundColor;
    document.getElementById('changeColorForBG2').style.backgroundColor = e.srcElement.style.backgroundColor;
}
