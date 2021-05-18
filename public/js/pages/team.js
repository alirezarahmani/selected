$(document).ready(function () {
    //check token if not set redirect to login page
    let tok = localStorage.getItem('token');
    if (tok == null) {
        window.location.href = base_url2 + "/login";
    }


    $('.select2').select2();

    $('.viewShift').select2();
    $('select.viewShift option[value="shift"]').attr("selected", true);


    //take openshift
    $(document).on('mouseenter', '.op-item', function () {
        //$("this.takeshift").show();
        $(this).find("button.takeshift").show();

    }).on('mouseleave', '.op-item', function () {
        //$(".takeshift").hide();
        $(this).find("button.takeshift").hide();
    });




    //add time off request from modal
    $('.sendTimeOffReq').on('click', function () {
        let start, end;

        if ($('#allDay').prop("checked") == true) {
            let startDate = $('#start-date-timeoff').val();
            start = startDate.concat("00:00:00");
            let endDate = $('#end-date-timeoff').val();
            end = endDate.concat("23:59:59");
        } else {
            let startDate = $('#start-date-timeoff').val();
            let startTime = $('#timeStartoff').val();
            let endTime = $('#timeEndoff').val();
            start = startDate.concat(startTime);
            end = startDate.concat(endTime);

        }

        let data = {
            userID: $('.select-emp').val(),
            type: $('#type-off').val(),
            paidHour: $('#paid-hour').val(),
            message: $('#message').val(),
            startTime: start,
            endTime: end,

        };
        console.log('data', data)
        $.ajax({
            url: base_url + '/api/time_off_requests',
            method: 'POST',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            data: JSON.stringify(data),
            success: function (r) {

                // console.log(r)
                $('#modal-addEvents').modal('hide');
                toastr.success('Time Off Request Successfully Added');
                //show it as background Event

            },
            error: function (e) {

                // console.log(e)
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

                    elArray.push(' <div class="col-5 border rounded m-2 p-2" style="background-color: white">\n' +
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


    //add annotation
    $('.saveAnnot').on('click', function () {
        $('.annotType:checked').each(function () {
            console.log(this.value);
        });
        let annotTypes = [];
        $.each($("input[name='annotType']:checked"), function () {
            annotTypes.push($(this).val());
        });


        if ($('#annotEditId').val() !== '') {
            let edIdAnnot = $('#annotEditId').val();
            let dataEdit = {
                title: $('#title').val(),
                message: $('#message_annot').val(),
                type: annotTypes.toString(),
                scheduleId: '/api/schedules/' + $('.select-schedule-annot').children("option:selected").val(),
                startDate: $('#start-date-annot').val() + ' 00:00',
                endDate: $('#end-date-annot').val() + ' 23:59'
            };

            $.ajax({
                method: 'PUT',
                url: base_url + '/api/annotations/' + edIdAnnot,
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataEdit),
                success: (r) => {
                    console.log(r);
                    let sts = (new Date(r['startDate'])).toString()
                    toastr.success('Annotation Successfully Updated.');
                    $('.annotation-content .selected .txtRibbon').html('<i class="far fa-calendar-alt mr-1"></i>' + sts.substring(8, 10) + ' ' + sts.substring(4, 7));
                    $('.annotation-content .selected .titleAnnot').html(r["title"] + '<button type="button" data-id="' + r['id'] + '" class="btn btn-sm d-inline editAnnotation" data-toggle="modal" data-target="#modal-addAnnot" title="more info"><i class="fas fa-arrow-circle-right" ></i></button>');
                    $('.annotation-content .selected .msgAnnot').text(r['message']);


                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });

        } else {

            let dataAnnot = {
                title: $('#title').val(),
                message: $('#message_annot').val(),
                type: annotTypes.toString(),
                color: "blue",
                scheduleId: '/api/schedules/' + $('.select-schedule-annot').children("option:selected").val(),
                startDate: $('#start-date-annot').val() + ' 00:00',
                endDate: $('#end-date-annot').val() + ' 23:59'
            }
            console.log('annot data', dataAnnot)
            $.ajax({
                method: 'POST',
                url: base_url + '/api/annotations',
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${tok}`,
                },
                data: JSON.stringify(dataAnnot),
                success: (r) => {
                    console.log(r);
                    toastr.success('Annotation Successfully Added.');
                    //show it in the annotation list automatically

                },
                error: (e) => {
                    //expire jwt token
                    if (e.status == 401) {
                        window.location.href = base_url2 + "/login";
                    }
                    toastr.error(e['responseJSON']['hydra:description']);
                }
            });
        }

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

    //delete annotation
    $('.finaldeleteAnnot').on('click', function () {

        let delAnnotId = $('#annotEditId').val();

        $.ajax({
            url: base_url + '/api/annotations/' + delAnnotId,
            method: 'DELETE',
            contentType: "application/json",
            headers: {
                'Authorization': `Bearer ${tok}`,

            },
            success: function (r) {
                // console.log(r)
                //it should be deleted from the open list
                $('.annotation-content .detailAnnots.selected').hide();

            },
            error: function (e) {

                // console.log(e)
                //expire jwt token
                if (e.status == 401) {
                    window.location.href = base_url2 + "/login";
                }
                toastr.error(e['responseJSON']['hydra:description']);
            }
        });
    });


});


