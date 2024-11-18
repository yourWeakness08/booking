var eventCalendar, countRecurBtn = 0, editCountRecurBtn = 0;
let getCalendar, calendarFilter = '', tabularFiter = '', filterUser = '', filterFacility = '', filterMeeting = '';
var nowDate = new Date();
var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
var maxLimitDate = new Date(nowDate.getFullYear() + 1, nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

getCalendar = function(){
    var calendarEl = document.getElementById('calendar');
    if(typeof calendarEl != 'undefined' && calendarEl != null){
        eventCalendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            nowIndicator: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            navLinks: true, // can click day/week names to navigate views
            editable: false,
            droppable: false,
            selectable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            eventSources: [
                {
                    url: baseUrl("portal/dashboard/get_event"),
                    method: 'GET',
                    extraParams: {
                        filter: calendarFilter,
                        filterUser: filterUser,
                        filterFacility: filterFacility,
                        filterMeeting: filterMeeting,
                    },
                }
            ],
            height: 630,
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List'
            },
            eventClick: function (calEvent, jsEvent, view) {//On click for day event
                const title = calEvent.event.title;
                const rows = calEvent.event.extendedProps;
                const frq = rows.frequency ? rows.frequency : '';
                $("#kt_modal_archive_event").removeClass('d-none');
                vmData.row = Object.assign({}, rows);
                vmData.title = title;
                vmData.meeting = rows.meeting_type;
    
                $("#daterange-view").daterangepicker({
                    startDate: moment(rows.date_from).format('MM/DD/YYYY HH:mm'),
                    endDate: moment(rows.date_to).format('MM/DD/YYYY HH:mm'),
                    singleDatePicker: false,
                    showDropdowns: true,
                    autoUpdateInput: false,
                    timePicker: true,
                    timePicker24Hour: true,
                    locale: {
                        cancelLabel: 'Clear'
                    },
                    "minDate": today,
                    "maxDate": maxLimitDate,
                    parentEl: window.document.querySelector('#kt_modal_edit_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
                    $("#recur_from-view").val(picker.startDate.format('YYYY-MM-DD HH:mm'));
                    $("#recur_to-view").val(picker.endDate.format('YYYY-MM-DD HH:mm'));
                });
                
                $("#wholedaypicker-view").daterangepicker({
                    startDate: moment(rows.date_from).format('MM/DD/YYYY'),
                    singleDatePicker: true,
                    showDropdowns: true,
                    // timePicker: true,
                    autoUpdateInput: false,
                    "minDate": today,
                    parentEl: window.document.querySelector('#kt_modal_edit_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') );
                });

                $("#halfdaypicker-view").daterangepicker({
                    startDate: moment(rows.startDate).format('MM/DD/YYYY'),
                    singleDatePicker: true,
                    showDropdowns: true,
                    // timePicker: true,
                    autoUpdateInput: false,
                    "minDate": today,
                    parentEl: window.document.querySelector('#kt_modal_edit_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') );
                });
                
                $("#others_from-view").daterangepicker({
                    startDate: moment(rows.start_date).format('MM/DD/YYYY'),
                    singleDatePicker: true,
                    showDropdowns: true,
                    timePicker: true,
                    autoUpdateInput: false,
                    timePicker24Hour: true,
                    locale: {
                        cancelLabel: 'Clear'
                    },
                    "minDate": today,
                    parentEl: window.document.querySelector('#kt_modal_edit_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
                });
                
                $("#others_to-view").daterangepicker({
                    startDate: moment(rows.end_date).format('MM/DD/YYYY'),
                    singleDatePicker: true,
                    showDropdowns: true,
                    timePicker: true,
                    autoUpdateInput: false,
                    timePicker24Hour: true,
                    locale: {
                        cancelLabel: 'Clear'
                    },
                    "minDate": today,
                    parentEl: window.document.querySelector('#kt_modal_edit_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
                });
                $("#kt_modal_edit_event").modal('show');
                
                editSelect2Target(rows.facility_id, rows.facility_name);
    
                $("#edit-facility").select2({
                    placeholder: { id: '-1', text: 'Select an option' },
                    minimumResultsForSearch: Infinity,
                    ajax:{
                        url: baseUrl('portal/facility/get_facility'),
                        method: "GET",
                        delay: 250,
                        processResults: function (data) {
                            return { results: data };
                        }
                    }
                });
    
                if(rows.recur_on == 'Weekly'){
                    $.each(frq, function(index, value){
                        if(value == 'Monday'){
                            $("#kt_modal_edit_event .mon").prop('checked', true);
                        }
                        if(value == 'Tuesday'){
                            $("#kt_modal_edit_event .tue").prop('checked', true);
                        }
                        if(value == 'Wednesday'){
                            $("#kt_modal_edit_event .wed").prop('checked', true);
                        }
                        if(value == 'Thursday'){
                            $("#kt_modal_edit_event .thu").prop('checked', true);
                        }
                        if(value == 'Friday'){
                            $("#kt_modal_edit_event .fri").prop('checked', true);
                        }
                        if(value == 'Saturday'){
                            $("#kt_modal_edit_event .sat").prop('checked', true);
                        }
                    });
                }
    
                var user_id = rows.user_id;
                var now = moment().format('YYYY-MM-DD');
                var eventDate = moment(calEvent.event.startStr).format('YYYY-MM-DD');
                var userSession = user_session;

                if(now > eventDate){
                    $("#kt_modal_archive_event").addClass('d-none');
                    $("#kt_modal_edit_event_button1").addClass('d-none');
                    $('#kt_modal_edit_event_submit1').addClass('d-none');
                }else{
                    if(user_id == userSession || user_role != 2){
                        $("#kt_modal_archive_event").removeClass('d-none');
                        $('#kt_modal_edit_event_submit1').removeClass('d-none');
                        $("#kt_modal_edit_event_button1").removeClass('d-none');
                    }else{
                        $("#kt_modal_archive_event").addClass('d-none');
                        $('#kt_modal_edit_event_submit1').addClass('d-none');
                        $("#kt_modal_edit_event_button1").addClass('d-none');
                    }

                }

                if(rows.recurring == 1){
                    editCountRecurBtn = 1;
                }else{
                    editCountRecurBtn = 0;
                }
            },
            dateClick: function(info){
                var now = moment().format('YYYY-MM-DD');
                var dateClicked = info.dateStr;
                var _today = moment().format('YYYY-MM-DD HH:mm');
                const _minDate = (info.dateStr == moment().format('YYYY-MM-DD')) ? _today : moment(info.dateStr + ' 08:00').format('YYYY-MM-D HH:mm');
                if(dateClicked >= now){
                    $("#kt_modal_add_event").modal('show');
                }

                var _recur = $("#recurring:checked").attr('checked', false);
                
                if(_recur.length == 1){
                    $("#kt_modal_add_event_form").trigger('reset');
                }

                $("#repeat-schedule").addClass('d-none');
                
                $("#hide-on-recurring-schedule").removeClass('d-none');
                $("#meeting-time-type option[value='others']").attr('selected',true);
                $("#for-others").removeClass('d-none');

                $("#others_from").daterangepicker({
                    singleDatePicker: true,
                    startDate: moment(info.dateStr + ' 08:00').format('YYYY-MM-D HH:mm'),
                    showDropdowns: true,
                    timePicker: true,
                    autoUpdateInput: true,
                    timePicker24Hour: true,
                    locale: {
                        cancelLabel: 'Clear',
                        "format": "YYYY-MM-DD HH:mm",
                    },
                    alwaysOpen:true,
                    opens: 'left',
                    "minDate": _minDate,
                    parentEl: window.document.querySelector('#kt_modal_add_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
                });

                $("#others_to").daterangepicker({
                    singleDatePicker: true,
                    startDate: moment(info.dateStr + ' 08:00').format('YYYY-MM-D HH:mm'),
                    showDropdowns: true,
                    timePicker: true,
                    autoUpdateInput: true,
                    timePicker24Hour: true,
                    locale: {
                        cancelLabel: 'Clear',
                        "format": "YYYY-MM-DD HH:mm",
                    },
                    alwaysOpen:true,
                    opens: 'left',
                    "minDate": _minDate,
                    parentEl: window.document.querySelector('#kt_modal_add_event')
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
                });
            }, eventDidMount(info){
                if (info.event.backgroundColor) {
                    info.el.style.backgroundColor=info.event.backgroundColor;
                }
                if (info.event.textColor) {
                    info.el.style.color=info.event.textColor;
                }
                info.el.innerText = moment(info.event.start).format('HH:mm') + " - " + moment(info.event.end).format('HH:mm') + ' ' + info.event.title;
            }
        });
    
        eventCalendar.render();
    }
}
$("#tabular-view").DataTable().destroy();
let tabular = $('#tabular-view').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: baseUrl('portal/dashboard/get_datatable'),
        type: "post",
        dataType: "json",
        data: function(data){
            data.search['value'] = tabularFiter
        }
    },
    order: [[6, 'desc']],
    columns: [
        { data: 'fullname', orderable: false },
        { data: 'description' },
        { data: 'facility_name' },
        { data: 'date_range', orderable: false },
        { data: 'contact_number' },
        { data: 'remarks' },
        { data: 'created_dt' },
        { data: null },
    ],
    columnDefs:[
        {
            targets: 1,
            render: function(data, type, row){
                let action = '';
                if(row.reference_no){
                    action += `<small class="fs-8">Ref No: ${row.reference_no}</small>`;
                }
                action += `<p class="mb-0" style="font-weight: 600">${data}</p>`;

                if(row.meeting_type == 'Recurring'){
                    action += `<p class="mb-0">Recurring Schedule</p><small>${row.recurring}</small>`;
                }else{
                    action += '<p class="mb-0">'+row.meeting_type+'</p>';
                }

                return action;
            }
        },
        {
            targets: 7,
            orderable: false,
            render: function(data, type, row){
                let temp = JSON.stringify(row);
                let action = '';
                
                if(user_session == row.user_id || user_role <= 1){
                    action += `<button type="button" onclick="tabularEdit(${row.event_id})" id="edit-tabular" class="btn btn-warning btn-sm me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="bi bi-pencil"></i></button>`;
                    action += `<button type="button" onclick="archiveEvent(${row.event_id})" id="archive" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive"><i class="bi bi-file-zip"></i></button>`;
                }else{
                    action += `<button type="button" id="edit-tabular" class="btn btn-warning btn-sm me-1 disabled" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="bi bi-pencil"></i></button>`;
                    action += '<button type="button" id="archive" class="btn btn-danger btn-sm disabled" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive"><i class="bi bi-file-zip"></i></button>';
                }

                return action;

            }
        }
    ]
});


$(document).ready( function(){
    getCalendar();
    select2Target();
    filterSelect2Target();
    filterUserSelect2Target();
    getFacility();

    $('a.nav-link').on('click', function () {
        var anchor = $(this).attr('id')
        if(anchor == 'tabular-tab'){
            $("#tabular-search").removeClass('d-none');
            $("#calendar-search").addClass('d-none');
            $("#for-calendar").addClass('d-none');
        }else{
            $("#tabular-search").addClass('d-none');
            $("#calendar-search").removeClass('d-none');
            $("#for-calendar").removeClass('d-none');
        }
        $("#calendarSearch").val('');
        $("#tabularSearch").val('');
        calendarFilter = '';
        tabularFiter = '';
        filterUser = '';
        filterFacility = '';
        filterMeeting = ''
    });

    $('#calendarSearch').donetyping(function(e){
        calendarFilter = this.value;
        getCalendar();
    }, 400);
    $('#tabularSearch').donetyping(function(e){
        tabularFiter = this.value;
        tabular.ajax.reload();
    }, 400);

    let widgetCount = 0;
    $(".action").on('click', function(){
        if(widgetCount == 0){
            widgetCount = 1;
            $(this).addClass('active');
        }else{
            widgetCount = 0;
            $(this).removeClass('active');
        }
    });

    $("#kt_modal_edit_event_form").submit( function(e){
        e.preventDefault();
        var data = $(this).serialize();
        
        $("#kt_modal_edit_event_submit1").prop('disabled', true);
        $.ajax({
            url : baseUrl('portal/dashboard/edit_event/'),
            type: "POST",
            dataType: "json",
            data: data,
            success: function(response){
                if(response.state){
                    // Swal.fire(response.msg, '', 'success');
                    Swal.fire({
                        icon: 'success',
                        title: "",
                        text: response.msg,
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '#kt_modal_edit_event_form',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    }).then((result) => {
                        if(result.isConfirmed){
                            closeEditModal();
                        }

                        return false;
                    });

                    getCalendar();
                }else{
                    // Swal.fire(response.msg, '', 'warning');
                    Swal.fire({
                        icon: 'warning',
                        title: "",
                        text: response.msg,
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '#kt_modal_edit_event_form'
                    });
                }
            }
        });
    });
});

// add
$("#daterange").daterangepicker({
    singleDatePicker: false,
    showDropdowns: true,
    autoUpdateInput: false,
    timePicker: true,
    timePicker24Hour: true,
    locale: {
        cancelLabel: 'Clear'
    },
    "minDate": today,
    "maxDate": maxLimitDate,
    parentEl: window.document.querySelector('#kt_modal_add_event')
}).on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
    $("#recur_from").val(picker.startDate.format('YYYY-MM-DD HH:mm'));
    $("#recur_to").val(picker.endDate.format('YYYY-MM-DD HH:mm'));
});

$("#wholedaypicker").daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    // timePicker: true,
    autoUpdateInput: false,
    "minDate": today,
    parentEl: window.document.querySelector('#kt_modal_add_event')
}).on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') );
});

$("#halfdaypicker").daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    // timePicker: true,
    autoUpdateInput: false,
    "minDate": today,
    parentEl: window.document.querySelector('#kt_modal_add_event')
}).on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') );
});

$("#others_from").daterangepicker({
    singleDatePicker: true,
    startDate: moment().format('YYYY-MM-D HH:mm'),
    showDropdowns: true,
    timePicker: true,
    autoUpdateInput: false,
    timePicker24Hour: true,
    locale: {
        cancelLabel: 'Clear',
        "format": "YYYY-MM-DD HH:mm",
    },
    minDate: moment().format('YYYY-MM-D HH:mm'),
    parentEl: window.document.querySelector('#kt_modal_add_event'),
}).on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
});

$("#others_to").daterangepicker({
    singleDatePicker: true,
    startDate: moment().format('YYYY-MM-DD HH:mm'),
    showDropdowns: true,
    timePicker: true,
    autoUpdateInput: false,
    timePicker24Hour: true,
    locale: {
        cancelLabel: 'Clear',
        "format": "YYYY-MM-DD HH:mm",
    },
    minDate: moment().format('YYYY-MM-DD HH:mm'),
    parentEl: window.document.querySelector('#kt_modal_add_event')
}).on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
});

$("#kt_modal_add_event_form").submit( function(e){
    e.preventDefault();
    var data = $(this).serialize();
    var check = false;

    var type = $("#meeting-time-type").val();
    var recur = $("#recurring:checked").val();
    var facility = $('#kt-select-facility').val();

    if(facility){
        if(recur && recur.length != 0){
            var range = $("#daterange").val();
            if(range == ''){
                // Swal.fire('Daterange field is empty.', '', 'warning');
                Swal.fire({
                    icon: 'warning',
                    title: "",
                    text: 'Daterange field is empty.',
                    customClass: {
                        container: 'my-swal'
                    },
                    target: '.modal'
                });
            }else{
                var weekly = $("#for-weekly input[type='checkbox']:checked").val();
                var check_frq = $("#kt_modal_add_event_form input[type='radio']:checked").val();
                var check_time = $("#recur_from").val();
                var _time = moment(check_time).format('HH:mm');
    
                if(check_frq == 'Weekly'){
                    if(typeof weekly != 'undefined' && weekly){
                        var date_from = $("#recur_from").val();
                        var now = moment().format('YYYY-MM-DD HH:mm');
    
                        if(now > date_from){
                            // Swal.fire('Please select another date/time option.', '', 'warning');
                            Swal.fire({
                                icon: 'warning',
                                title: "",
                                text: 'Please select another date/time option.',
                                customClass: {
                                    container: 'my-swal'
                                },
                                target: '.modal'
                            });
                        }else{
                            if(_time != '00:00'){
                                check = true;
                            }else{
                                Swal.fire({
                                    icon: 'warning',
                                    title: "",
                                    text: 'Please add specific time for your meeting.',
                                    customClass: {
                                        container: 'my-swal'
                                    },
                                    target: '.modal'
                                });
                            }
                        }
                    }else{
                        // Swal.fire('Please select day to recur schedule', '', 'warning');
                        Swal.fire({
                            icon: 'warning',
                            title: "",
                            text: 'Please select day to recur schedule',
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '.modal'
                        });
                    }
                }else{
                    var date_from = $("#recur_from").val();
                    var now = moment().format('YYYY-MM-DD HH:mm');
    
                    if(now > date_from){
                        // Swal.fire('Please select another date/time option.', '', 'warning');
                        Swal.fire({
                            icon: 'warning',
                            title: "",
                            text: 'Please select another date/time option.',
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '.modal'
                        });
                    }else{
                        if(_time != '00:00'){
                            check = true;
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: "",
                                text: 'Please add specific time for your meeting.',
                                customClass: {
                                    container: 'my-swal'
                                },
                                target: '.modal'
                            });
                        }
                    }
                }
                
            }
        }else{
            if(type == 'whole day'){
                var checkdate = $("#wholedaypicker").val();
                if(checkdate == ''){
                    // Swal.fire('Date field is empty.', '', 'warning');
                    Swal.fire({
                        icon: 'warning',
                        title: "",
                        text: 'Date field is empty.',
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '.modal'
                    });
                }else{
                    var now = moment().format('YYYY-MM-DD HH:mm');
                    var wholeday = checkdate + ' 08:00';
                    if(now > wholeday){
                        // Swal.fire('Please select another date/time option.', '', 'warning');
                        Swal.fire({
                            icon: 'warning',
                            title: "",
                            text: 'Please select another date/time option.',
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '.modal'
                        });

                    }else{
                        check = true;
                    }
                }
            }else if(type == 'others'){
                var checkFrom = $("#others_from").val();
                var checkTo = $("#others_to").val();

                if(checkFrom == '' && checkTo == ''){
                    // Swal.fire('Date From/To field is empty.', '', 'warning');
                    Swal.fire({
                        icon: 'warning',
                        title: "",
                        text: 'Date From/To field is empty.',
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '.modal'
                    });
                }else{
                    var now  = moment().format('YYYY-MM-DD HH:mm');
                    if(now > checkFrom){
                        // Swal.fire('Please select another date/time option.', '', 'warning');
                        Swal.fire({
                            icon: 'warning',
                            title: "",
                            text: 'Please select another date/time option.',
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '.modal'
                        });
                    }else if(checkFrom >= checkTo){
                        Swal.fire({
                            icon: 'warning',
                            title: "",
                            text: 'Invalid Date! "Date from" must not be greater than the "Date to".',
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '.modal'
                        });
                    }else{
                        check = true;
                    }
                }
            }else if(type == 'half day'){
                var checkHalf = $("#halfdaypicker").val();
                if(checkHalf == ''){
                    // Swal.fire('Date field is empty.', '', 'warning');
                    Swal.fire({
                        icon: 'warning',
                        title: "",
                        text: 'Date field is empty.',
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '.modal'
                    });
                }else{
                    var now = moment().format('YYYY-MM-DD HH:mm');
                    var halfdayindicator = $("#halfdayindicator").val();
                    var halfday = halfdayindicator == 'am' ? checkHalf + ' 08:00' : checkHalf + ' 13:00';
    
                    if(now > halfday){
                        // Swal.fire('Please select another date/time option.', '', 'warning');
                        Swal.fire({
                            icon: 'warning',
                            title: "",
                            text: 'Please select another date/time option.',
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '.modal'
                        });

                    }else{
                        check = true;
                    }
                }
            }else{
                // Swal.fire('Type field is empty', '', 'warning');
                Swal.fire({
                    icon: 'warning',
                    title: "",
                    text: 'Type field is empty',
                    customClass: {
                        container: 'my-swal'
                    },
                    target: '.modal'
                });
            }
        }
    }else{
        // Swal.fire('Facility field is empty.', '', 'warning');
        Swal.fire({
            icon: 'warning',
            title: "",
            text: 'Facility field is empty.',
            customClass: {
                container: 'my-swal'
            },
            target: '.modal'
        });
    }

    if(check){
        $("#kt_modal_add_event_submit").prop('disabled', true);
        $.ajax({
            url : baseUrl('portal/dashboard/add_event'),
            type: "post",
            dataType: "json",
            data: data,
            beforeSend: function(){
                $("#kt_modal_add_event_submit").prop('disabled', true);
            },
            success: function(response){
                if(response.state){
                    Swal.fire(response.msg, '', 'success');
                    check = false;
                    $("#kt_modal_add_event").modal('hide');
                    $("#kt_modal_add_event_form").trigger('reset');
                    $("#repeat-schedule").addClass('d-none');
                    $("#for-weekly").addClass('d-none');
                    $("#for-halfday").addClass('d-none');
                    $("#hide-on-recurring-schedule").removeClass('d-none');
                    $("#meeting-time-type-view option[value='']").attr('selected', true);
                    select2Target(true);
                    getCalendar();
                    tabular.ajax.reload();
                    countRecurBtn = 0;
                }else{
                    // Swal.fire(response.msg, '', 'warning');
                    Swal.fire({
                        icon: 'warning',
                        title: "",
                        text: response.msg,
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '.modal'
                    });
                }

                $("#kt_modal_add_event_submit").prop('disabled', false);
            }
        });
    }
});
// add

$("#menu-filter").submit( function(e){
    e.preventDefault();
    var user = $("#filter_user").val();
    var facility = $("#filter_facility").val();
    var meeting = $("#filter_meeting").val();
    
    filterUser = user;
    filterFacility = facility;
    filterMeeting = meeting;
    getCalendar();
});

const $contactAdd = document.querySelector("#contact");
if(typeof $contactAdd != 'undefined' && $contactAdd){
    const allowedInAdd = /[0-9\/]+/;
    $contactAdd.addEventListener("keypress", event => {
        if (!allowedInAdd.test(event.key)) {
            event.preventDefault();
        }
    });
}

const $contactEdit = document.querySelector("#view-contact");
if(typeof $contactEdit != 'undefined' && $contactEdit){
    const allowedInEdit = /[0-9\/]+/;
    $contactEdit.addEventListener("keypress", event => {
        if (!allowedInEdit.test(event.key)) {
            event.preventDefault();
        }
    });
}

function getType(){
    if(countRecurBtn == 1){
        countRecurBtn = 0;
        $("#repeat-schedule").addClass('d-none');
        $("#hide-on-recurring-schedule").removeClass('d-none');
        $("#recur_from").val();
        $("#recur_to").val();

        var type = $("#meeting-time-type").val();
        if(type == 'whole day'){
            $("#for-whole-day").removeClass('d-none');
            $("#for-others").addClass('d-none');
            $("#for-half-day").addClass('d-none');
        }else if(type == 'half day'){
            $("#for-half-day").removeClass('d-none');
            $("#for-whole-day").addClass('d-none');
            $("#for-others").addClass('d-none');
        }else{
            $("#for-whole-day").addClass('d-none');
            $("#for-others").removeClass('d-none');
            $("#for-half-day").addClass('d-none');
        }
    }else{
        $("#repeat-schedule").removeClass('d-none');
        countRecurBtn = 1;
        $("#for-half-day").addClass('d-none');
        $("#hide-on-recurring-schedule").addClass('d-none');
        $("#for-whole-day").addClass('d-none');
        $("#for-others").addClass('d-none');
    }
}
function EditgetType(){
    if(editCountRecurBtn == 1){
        editCountRecurBtn = 0;
        $("#repeat-schedule-view").addClass('d-none');
        $("#recur_sched-view").val(false);
        $("#hide-on-recurring-schedule-view").removeClass('d-none');
        $("#recur_from").val();
        $("#recur_to").val();

        var type = $("#meeting-time-type-view").val();
        if(type == 'whole day'){
            $("#for-whole-day-view").val('').removeClass('d-none');
            $("#for-others-view").val('').addClass('d-none');
            $("#for-half-day-view").val('').addClass('d-none');
        }else if(type == 'half day'){
            $("#for-half-day-view").val('').removeClass('d-none');
            $("#for-whole-day-view").val('').addClass('d-none');
            $("#for-others-view").val('').addClass('d-none');
        }else{
            $("#for-half-day-view").val('').addClass('d-none');
            $("#for-whole-day-view").val('').addClass('d-none');
            $("#for-others-view").val('').removeClass('d-none');
        }
    }else{
        $("#repeat-schedule-view").removeClass('d-none');
        editCountRecurBtn = 1;
        $("#recur_sched-view").val(true);
        $("#hide-on-recurring-schedule-view").addClass('d-none');
        $("#for-whole-day-view").addClass('d-none');
        $("#for-others-view").addClass('d-none');
    }
}
function changeTimeType(){
    var type = $("#meeting-time-type").val();
    if(type == 'whole day'){
        $("#for-whole-day").removeClass('d-none');
        $("#for-others").addClass('d-none');
        $("#for-half-day").addClass('d-none');
        $("#others_from").val('');
        $("#others_to").val('');
    }else if(type == 'half day'){
        $("#for-half-day").removeClass('d-none');
        $("#for-whole-day").addClass('d-none');
        $("#for-others").addClass('d-none');
        $("#others_from").val('');
        $("#others_to").val('');
    }else{
        $("#for-whole-day").addClass('d-none');
        $("#for-others").removeClass('d-none');
        $("#for-half-day").addClass('d-none');
        
        $("#others_from").val(moment().format('YYYY-MM-DD HH:mm'));
        $("#others_to").val(moment().format('YYYY-MM-DD HH:mm'));
    }
}
function editChangeTimeType(){
    var type = $("#meeting-time-type-view").val();
    if(type == 'whole day'){
        $("#for-whole-day-view").val('').removeClass('d-none');
        $("#for-others-view").val('').addClass('d-none');
        $("#for-half-day-view").val('').addClass('d-none');
    }else if(type == 'half day'){
        $("#for-half-day-view").val('').removeClass('d-none');
        $("#for-whole-day-view").val('').addClass('d-none');
        $("#for-others-view").val('').addClass('d-none');
    }else{
        $("#for-half-day-view").val('').addClass('d-none');
        $("#for-whole-day-view").val('').addClass('d-none');
        $("#for-others-view").val('').removeClass('d-none');
    }
}
function addReservation(){
    $("#kt_modal_add_event").modal('show');
}
function changeFrequency(){
    var frq = $("#kt_modal_add_event_form input[type='radio']:checked").val();
    if(frq == 'Daily'){
        $("#for-weekly").addClass('d-none');
    }else{
        $("#for-weekly").removeClass('d-none');
    }
}
function editChangeFrequency(){
    var frq = $("#kt_modal_edit_event_form input[type='radio']:checked").val();
    if(frq == 'Daily'){
        $("#for-weekly-view").addClass('d-none');
    }else{
        $("#for-weekly-view").removeClass('d-none');
    }
}
function select2Target(destroy = false){
    if(destroy){
        $("#kt-select-facility").select2('destroy').select2();
    }

    $("#kt-select-facility").select2({
        placeholder: { id: '-1', text: 'Select an option' },
        minimumResultsForSearch: Infinity,
        ajax:{
            url: baseUrl('portal/facility/get_facility'),
            method: "GET",
            delay: 250,
            processResults: function (data) {
                return { results: data };
            }
        }
    });
}
function editSelect2Target(id, name, destroy = false){
    if(destroy){
        $("#edit-facility").select2('destroy').select2();
    }

    if(typeof id !== "undefined" && id){
        const select2Option = new Option(name, id, false, true);
        $("#edit-facility").html(select2Option);
    }

    $("#edit-facility").select2({
        placeholder: { id: '-1', text: 'Select an option' },
        minimumResultsForSearch: Infinity,
        ajax:{
            url: baseUrl('portal/facility/get_facility'),
            method: "GET",
            delay: 250,
            processResults: function (data) {
                return { results: data };
            }
        }
    });
}
function closeModal(){
    $("#kt_modal_add_event_form").trigger('reset');
    $("#meeting-time-type").val('');
    $("#kt-select-facility").val('').trigger('change');
    $("#for-whole-day").addClass('d-none');
    $("#for-half-day").addClass('d-none');
    $("#for-others").addClass('d-none');
    $("#hide-on-recurring-schedule").removeClass('d-none');
    $("#repeat-schedule").addClass('d-none');
    countRecurBtn = 0;
}
function closeEditModal(){
    $("#kt_modal_edit_event_form").trigger('reset');
    $("#kt_modal_edit_event").modal('hide');

    $("#kt_modal_edit_event_form input").attr('disabled', true);
    $("#kt_modal_edit_event_form select").attr('disabled', true);
    $("#kt_modal_edit_event_form textarea").attr('disabled', true);
    $("#kt_modal_edit_event_submit1").attr('disabled', true);
    editCountRecurBtn = 0;
}
function updateEvent(){
    $("#kt_modal_edit_event_form input").attr('disabled', false);
    $("#kt_modal_edit_event_form select").attr('disabled', false);
    $("#kt_modal_edit_event_form textarea").attr('disabled', false);
    $("#kt_modal_edit_event_submit1").attr('disabled', false);
}
function filterSelect2Target(destroy = false){
    if(destroy){
        $("#filter_facility").select2('destroy').select2();
    }

    $("#filter_facility").select2({
        allowClear: true,
        placeholder: { id: '-1', text: 'Select an option' },
        minimumResultsForSearch: Infinity,
        ajax:{
            url: baseUrl('portal/facility/get_facility'),
            method: "GET",
            delay: 250,
            processResults: function (data) {
                return { results: data };
            }
        }
    });
}
function filterUserSelect2Target(destroy = false){
    if(destroy){
        $("#filter_user").select2('destroy').select2();
    }

    $("#filter_user").select2({
        allowClear: true,
        placeholder: { id: '-1', text: 'Select an option' },
        ajax:{
            url: baseUrl('portal/users/get_users'),
            method: "GET",
            delay: 250,
            processResults: function (data) {
                return { results: data };
            }
        },
    }).on("select2:select", function(e){
        const data = e.params.data;
    });
}
function resetFilter(){
    $("#menu-filter").trigger('reset');

    filterSelect2Target(true);
    filterUserSelect2Target(true);
    filterUser = '';
    filterFacility = '';
    filterMeeting = '';

    getCalendar();
}
function tabularEdit(id){
    $("#kt_modal_edit_event").modal('show');
    $("#kt_modal_archive_event").addClass('d-none');
    $("#kt_modal_edit_event_button1").addClass('d-none');
    $('#kt_modal_edit_event_submit1').addClass('d-none');

    $.ajax({
        url : baseUrl('portal/dashboard/get_events/') + id,
        type: "GET",
        dataType: "json",
        success: function(response){
            var userSession = user_session;
            var user_id = response.user_id;
            var now = moment().format('YYYY-MM-DD');
            var eventDate = moment(response.startDate).format('YYYY-MM-DD');
            if(now > eventDate){
                console.log(userSession, now, eventDate);
                $("#kt_modal_edit_event_button1").addClass('d-none');
                $('#kt_modal_edit_event_submit1').addClass('d-none');
            }else{
                if(user_id == userSession || user_role != 2){
                    $('#kt_modal_edit_event_submit1').removeClass('d-none');
                    $("#kt_modal_edit_event_button1").removeClass('d-none');
                }else{
                    $('#kt_modal_edit_event_submit1').addClass('d-none');
                    $("#kt_modal_edit_event_button1").addClass('d-none');
                }

            }

            vmData.row = Object.assign({}, response);
            vmData.title = response.title;
            vmData.meeting = response.meeting_type;

            const frq = response.frequency ? response.frequency : '';

            if(response.recur_on == 'Weekly'){
                $.each(frq, function(index, value){
                    if(value == 'Monday'){
                        $("#kt_modal_edit_event .mon").prop('checked', true);
                    }
                    if(value == 'Tuesday'){
                        $("#kt_modal_edit_event .tue").prop('checked', true);
                    }
                    if(value == 'Wednesday'){
                        $("#kt_modal_edit_event .wed").prop('checked', true);
                    }
                    if(value == 'Thursday'){
                        $("#kt_modal_edit_event .thu").prop('checked', true);
                    }
                    if(value == 'Friday'){
                        $("#kt_modal_edit_event .fri").prop('checked', true);
                    }
                    if(value == 'Saturday'){
                        $("#kt_modal_edit_event .sat").prop('checked', true);
                    }
                });
            }

            var user_id = response.user_id;
            var userSession = user_session;
            
            if(user_id == userSession || user_role != 2){
                $("#kt_modal_edit_event_button").removeClass('d-none');
            }else{
                $("#kt_modal_edit_event_button").addClass('d-none');
            }

            $("#daterange-view").daterangepicker({
                startDate: moment(response.date_from).format('MM/DD/YYYY HH:mm'),
                endDate: moment(response.date_to).format('MM/DD/YYYY HH:mm'),
                singleDatePicker: false,
                showDropdowns: true,
                autoUpdateInput: false,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    cancelLabel: 'Clear'
                },
                "minDate": today,
                "maxDate": maxLimitDate,
                parentEl: window.document.querySelector('#kt_modal_edit_event')
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
                $("#recur_from-view").val(picker.startDate.format('YYYY-MM-DD HH:mm'));
                $("#recur_to-view").val(picker.endDate.format('YYYY-MM-DD HH:mm'));
            });
            
            $("#wholedaypicker-view").daterangepicker({
                startDate: moment(response.date_from).format('MM/DD/YYYY HH:mm'),
                singleDatePicker: true,
                showDropdowns: true,
                // timePicker: true,
                autoUpdateInput: false,
                "minDate": today,
                parentEl: window.document.querySelector('#kt_modal_edit_event')
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') );
            });

            $("#halfdaypicker-view").daterangepicker({
                startDate: moment(response.date_from).format('MM/DD/YYYY HH:mm'),
                singleDatePicker: true,
                showDropdowns: true,
                // timePicker: true,
                autoUpdateInput: false,
                "minDate": today,
                parentEl: window.document.querySelector('#kt_modal_edit_event')
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') );
            });
            
            $("#others_from-view").daterangepicker({
                startDate: moment(response.date_from).format('MM/DD/YYYY HH:mm'),
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                autoUpdateInput: false,
                timePicker24Hour: true,
                locale: {
                    cancelLabel: 'Clear'
                },
                "minDate": today,
                parentEl: window.document.querySelector('#kt_modal_edit_event')
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
            });
            
            $("#others_to-view").daterangepicker({
                startDate: moment(response.date_from).format('MM/DD/YYYY HH:mm'),
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                autoUpdateInput: false,
                timePicker24Hour: true,
                locale: {
                    cancelLabel: 'Clear'
                },
                "minDate": today,
                parentEl: window.document.querySelector('#kt_modal_edit_event')
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') );
            });
            $("#kt_modal_edit_event").modal('show');
            
            editSelect2Target(response.facility_id, response.facility_name);

            $("#edit-facility").select2({
                placeholder: { id: '-1', text: 'Select an option' },
                minimumResultsForSearch: Infinity,
                ajax:{
                    url: baseUrl('portal/facility/get_facility'),
                    method: "GET",
                    delay: 250,
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });

            $("#kt_modal_edit_event_form").submit( function(e){
                e.preventDefault();
                var data = $(this).serialize();
                
                $.ajax({
                    url : baseUrl('portal/dashboard/edit_event/') + response.event_id,
                    type: "POST",
                    dataType: "json",
                    data: data,
                    success: function(response){
                        if(response.state){
                            // Swal.fire(response.msg, '', 'success');
                            Swal.fire({
                                icon: 'success',
                                title: "",
                                text: response.msg,
                                customClass: {
                                    container: 'my-swal'
                                },
                                target: '#kt_modal_edit_event'
                            });    

                            setTimeout( function(){
                                closeEditModal();
                            }, 3000);
                            getCalendar();
                            tabular.ajax.reload();
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: "",
                                text: response.msg,
                                customClass: {
                                    container: 'my-swal'
                                },
                                target: '#kt_modal_edit_event'
                            });         
                        }
                    }
                })
            });
        }
    });
}
function archiveEvent(id){
    Swal.fire({
        text: "Are you sure to archive this?",
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Archive",
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: 'btn btn-danger'
        }
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url : baseUrl('portal/dashboard/archive_event/') + id,
                type: "POST",
                dataType: "json",
                success: function(response){
                    if(response.state){
                        Swal.fire(response.msg, '', 'success');
                        tabular.ajax.reload();
                        getCalendar();
                    }else{
                        Swal.fire(response.msg, '', 'warning');
                    }
                }
            });
        }
    });
}
function calendar(){
    // getCalendar();
    //$("table.fc-scrollgrid-sync-table").css('height: 530px !important');
}

function getFacility(){
    $.ajax({
        url : baseUrl('portal/facility/getLegends'),
        method: "GET",
        dataType: "json",
        success: function(response){
            vmLegend.row = Object.assign({}, response);
            vmLegend.count = response.length;
        }
    })
}

var vmData = new Vue({
    el: "#kt_modal_edit_event",
    data: { row: {}, title: null, meeting: null, frequency: {}, now: null },
    methods: {
      archiveEvent(id){
        Swal.fire({
          text: "Are you sure to archive this?",
          icon: "warning",
          buttonsStyling: false,
          showCancelButton: true,
          confirmButtonText: "Archive",
          cancelButtonText: 'Cancel',
          customClass: {
              confirmButton: "btn btn-primary",
              cancelButton: 'btn btn-danger'
          },
          target: '#kt_modal_edit_event'
      }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url : baseUrl('portal/dashboard/archive_event/') + id,
                type: "POST",
                dataType: "json",
                success: function(response){
                    if(response.state){
                      Swal.fire({
                        icon: 'success',
                        title: response.msg,
                        customClass: {
                          container: 'my-swal'
                        },
                        target: '#kt_modal_edit_event'
                      }).then(() => {
                        $('#kt_modal_edit_event').modal('hide');
                        tabular.ajax.reload();
                        getCalendar();
                      });
                    }else{
                        // Swal.fire(response.msg, '', 'error');
                        Swal.fire({
                            icon: 'error',
                            title: "",
                            text: response.msg,
                            customClass: {
                                container: 'my-swal'
                            },
                            target: '#kt_modal_edit_event'
                        });
                    }
                }
            });
        }
    });
    }},
});

var vmLegend = new Vue({
    el: "#legend",
    data: { row: {}, count : 0 }
});