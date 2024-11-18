<div class="row">
    <div class="col-lg-10 col-md-10 col-sm-12 mx-auto my-1">
        <div class="card-toolbar row justify-content-end align-items-center mb-3 mx-1">
            <div class="d-col col-2" style="text-align:right">
                <button class="btn btn-flex btn-primary btn-sm" onclick="addReservation()">
                    <span class="svg-icon svg-icon-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black"></rect>
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black"></rect>
                        </svg>
                    </span>
                    Reservation
                </button>
            </div>
            <div class="d-col col-2">
                <div id="calendar-search" class="input-group form-control-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="calendarSearch" class="form-control form-control-sm" placeholder="Search" aria-label="Search" autocomplete="off"/>
                </div>
                <div id="tabular-search" class="input-group form-control-sm d-none">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="tabularSearch" class="form-control form-control-sm" placeholder="Search" aria-label="Search" autocomplete="off"/>
                </div>
            </div>
            <div id="for-calendar" class="d-col col-3">
                <a href="#" class="btn btn-sm btn-flex bg-body btn-color-gray-700 btn-active-color-primary fw-bold menu-dropdown" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <span class="svg-icon svg-icon-6 svg-icon-muted me-1"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="currentColor"></path>
                    </svg>
                    </span>
                    Filter
                </a>
                <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_filter" style="z-index: 107; position: fixed; inset: 0px 0px auto auto; margin: 0px; transform: translate(-273px, 128px);" data-popper-placement="bottom-end">
                    <form class="form" id="menu-filter">
                        <div class="fs-5 text-dark my-5 mx-5" style="font-weight: 600">Filter Options</div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="form-group mb-4">
                                <label class="form-label mb-0" style="font-weight: 600">User</label>
                                <select class="form-control" data-dropdown-parent="#kt_menu_filter" id="filter_user" name="user">
                                    <option value="">Select an Option</option>
                                </select>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label mb-0" style="font-weight: 600">Facility</label>
                                <select class="form-control" data-dropdown-parent="#kt_menu_filter" id="filter_facility" name="facility">
                                    <option value="">Select an Option</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label mb-0" style="font-weight: 600">Meeting Type</label>
                                <select class="form-control" id="filter_meeting" name="meeting">
                                    <option value="">Select an Option</option>
                                    <option value="whole day">Whole Day</option>
                                    <option value="daily">Daily</option>
                                    <option value="recurring">Recurring Schedule</option>
                                </select>
                            </div>
                        </div>
                        <div class="separator border-gray-200"></div>
                        <div class="d-flex justify-content-end my-3 mx-5">
                            <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2" onclick="resetFilter()" data-kt-menu-dismiss="false">Reset</button>
                            <button type="submit" class="btn btn-sm btn-primary" data-kt-menu-dismiss="false">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card card-xl-stretch">
            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-3">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" id="calendar-tab" onclick="calendar()" href="#kt_tab_pane_1">Calendar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" id="tabular-tab" href="#kt_tab_pane_2">Tabular</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                        <div id="calendar"></div>
                    </div>
                    <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                        <div class="table-responsive">
                            <table id="tabular-view" class="table table-row-bordered" width="100%">
                                <thead>
                                    <th class="reserve fs-5" style="font-weight: 600">Reservation From</th>
                                    <th class="title fs-5" style="font-weight: 600">Title</th>
                                    <th class="facility fs-5" style="font-weight: 600">Facility</th>
                                    <th class="date fs-5" style="font-weight: 600">Date</th>
                                    <th class="contact fs-5" style="font-weight: 600">Contact Number</th>
                                    <th class="remarks fs-5" style="font-weight: 600">Remarks</th>
                                    <th class="created_dt fs-5" style="font-weight: 600">Date Created</th>
                                    <th class="actions fs-5" style="font-weight: 600">Action</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="kt_modal_add_event" data-bs-backdrop="static" aria-labelledby="staticBackdrop" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form" id="kt_modal_add_event_form">
                <div class="modal-header py-3">
                    <h3 class="fw-bolder" data-kt-calendar="title">Add Reservation</h3>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" id="kt_modal_add_event_close" onclick="closeModal()">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Reservation Name</label>
                            <input type="text" name="description" class="form-control form-control-sm" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-between align-items-center">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Facility</label>
                            <select name="facility" data-dropdown-parent="#kt_modal_add_event" data-hide-search="true" id="kt-select-facility" class="form-control" required>
                                <option value="">Select an Option</option>
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-check form-switch form-check-custom form-check-solid" data-bs-toggle="tooltip" data-bs-placement="top" title="Repeat Schedule">
                                <input class="form-check-input h-20px w-30px" type="checkbox" name="recur_sched" id="recurring" value="true" id="flexSwitchDefault" onchange="getType()" />
                                <label class="form-check-label fs-5" for="flexSwitchDefault">
                                    Recurring Schedule
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-none"  id="repeat-schedule">
                        <div class="row mb-3">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <label class="fs-5 mb-1" style="font-weight: 500">Frequency</label>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-12">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm fs-6">
                                            <input class="form-check-input" type="radio" name="frequency" value="Daily" checked id="flexCheckboxLg" onclick="changeFrequency()" />
                                            <label class="form-check-label" for="flexCheckboxLg">
                                                Daily
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2 col-sm-12">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm fs-6">
                                            <input class="form-check-input" type="radio" name="frequency" value="Weekly" id="flexCheckboxLg1"  onclick="changeFrequency()" />
                                            <label class="form-check-label" class="fs-5" for="flexCheckboxLg1">
                                                Weekly
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="for-weekly" class="row mb-3 d-none">
                            <div class="btn-group" role="group" aria-label="Basic checkbox toggle button group">
                                <input type="checkbox" name="weekly[]" value="Monday" class="btn-check" id="btncheck1" autocomplete="off">
                                <label class="btn btn-primary btn-sm" for="btncheck1">Mon</label>

                                <input type="checkbox" name="weekly[]" value="Tuesday" class="btn-check" id="btncheck2" autocomplete="off">
                                <label class="btn btn-primary btn-sm" for="btncheck2">Tue</label>

                                <input type="checkbox" name="weekly[]" value="Wednesday" class="btn-check" id="btncheck3" autocomplete="off">
                                <label class="btn btn-primary btn-sm" for="btncheck3">Wed</label>

                                <input type="checkbox" name="weekly[]" value="Thursday" class="btn-check" id="btncheck4" autocomplete="off">
                                <label class="btn btn-primary btn-sm" for="btncheck4">Thu</label>

                                <input type="checkbox" name="weekly[]" value="Friday" class="btn-check" id="btncheck5" autocomplete="off">
                                <label class="btn btn-primary btn-sm" for="btncheck5">Fri</label>

                                <input type="checkbox" name="weekly[]" value="Saturday" class="btn-check" id="btncheck6" autocomplete="off">
                                <label class="btn btn-primary btn-sm" for="btncheck6">Sat</label>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <label class="fs-5 mb-1" style="font-weight: 500">Recur Schedule on</label>
                                <input type="text" id="daterange" name="daterange" placeholder="From - To" class="form-control form-control-sm" autocomplete="off">
                                <input type="hidden" name="recur_from" id="recur_from">
                                <input type="hidden" name="recur_to" id="recur_to">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3" id="hide-on-recurring-schedule">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Type</label>
                            <select id="meeting-time-type" name="meeting_time_type" class="form-control" onchange="changeTimeType()">
                                <option value="">Select an Option</option>
                                <option value="whole day">Whole Day</option>
                                <option value="half day">Half Day</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="row d-none mb-3" id="for-whole-day">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Date/Time Options</label>
                            <input type="text"  id="wholedaypicker" data-dropdown-parent="#kt_modal_add_event" value="" name="wholeday" placeholder="YYYY-MM-DD" class="form-control form-control-sm" autocomplete="off">
                        </div>
                    </div>
                    <div class="row d-none mb-3" id="for-half-day">
                        <label class="fs-5 mb-1" style="font-weight: 500">Date/Time Options</label>
                        <div class="col-lg-8 col-md-8 col-sm-12">
                            <input type="text" id="halfdaypicker" data-dropdown-parent="#kt_modal_add_event" value="" name="halfday" placeholder="YYYY-MM-DD" class="form-control form-control-sm" autocomplete="off">
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <select name="halfdayindicator" id="halfdayindicator" class="form-control form-control-sm">
                                <option value="am">AM</option>
                                <option value="pm">PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="row d-none mb-3" id="for-others">
                        <label class="fs-5 mb-1" style="font-weight: 500">Date/Time Options</label>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <input type="text" id="others_from" name="date_from" placeholder="From" class="form-control form-control-sm" autocomplete="off">
                            <small><span class="text-danger">Note:</span> Time is 24 Hour format</small>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <input type="text" id="others_to" name="date_to" placeholder="To" class="form-control form-control-sm" autocomplete="off">
                            <small><span class="text-danger">Note:</span> Time is 24 Hour format</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Contact Number</label>
                            <input type="text" name="contact" id="contact" maxlength="11" size="11" class="form-control form-control-sm" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Remarks</label>
                            <textarea class="form-control form-control-sm" rows="3" name="remarks"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <span class="text-danger">Note: </span> If urgent, Please choose type "<b>Others</b>".
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="kt_modal_add_event_cancel" class="btn btn-light-danger btn-sm me-3" data-bs-dismiss="modal" onclick="closeModal()">Cancel</button>
                    <button type="submit" id="kt_modal_add_event_submit" class="btn btn-primary btn-sm">
                        <span class="indicator-label">Submit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="kt_modal_edit_event" data-bs-backdrop="static" aria-labelledby="staticBackdrop" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form" method="POST" id="kt_modal_edit_event_form">
                <input type="hidden" name="id" :value="row.event_id">
                <div class="modal-header py-3">
                    <h3 class="fw-bolder" data-kt-calendar="title">View Reservation</h3>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" onclick="closeEditModal()">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Reservation Name</label>
                            <input type="text" id="view-desc" name="title" :value="title" class="form-control form-control-sm" autocomplete="off" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-between align-items-center">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Facility</label>
                            <select name="facility" :value="row.facility_id" data-dropdown-parent="#kt_modal_edit_event" data-hide-search="true" id="edit-facility" class="form-control" required disabled>
                                <option>Select an Option</option>
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-check form-switch form-check-custom form-check-solid" data-bs-toggle="tooltip" data-bs-placement="top" title="Repeat Schedule">
                                <input class="form-check-input h-20px w-30px" type="checkbox" name="recur_sched_view" id="recurring" value="true" id="flexSwitchDefault" :checked="(row.recurring == 1) ? 'checked' : ''" onchange="EditgetType()" disabled />
                                <label class="form-check-label fs-5" for="flexSwitchDefault">
                                    Recurring Schedule
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="" :class="row.recurring == 1 ? '' : 'd-none'" id="repeat-schedule-view">
                        <div class="row mb-3">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <label class="fs-5 mb-1" style="font-weight: 500">Frequency</label>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-12">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm fs-6">
                                            <input class="form-check-input" type="radio" name="frequency" value="Daily" :checked="row.recur_on == 'Daily' ? 'Checked' : ''" id="flexCheckboxLg2" onclick="editChangeFrequency()" disabled />
                                            <label class="form-check-label" for="flexCheckboxLg2">
                                                Daily
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2 col-sm-12">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm fs-6">
                                            <input class="form-check-input" type="radio" name="frequency" value="Weekly" id="flexCheckboxLg3" :checked="row.recur_on == 'Weekly' ? 'Checked' : ''"  onclick="editChangeFrequency()" disabled />
                                            <label class="form-check-label" class="fs-5" for="flexCheckboxLg3">
                                                Weekly
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="for-weekly-view" :class="row.recur_on == 'Weekly' ? '' : 'd-none'" class="row mb-3">
                            <div class="btn-group" role="group" aria-label="Basic checkbox toggle button group">
                                <input type="checkbox" name="weekly_view[]" value="Monday" class="btn-check mon" id="b1" autocomplete="off" disabled>
                                <label class="btn btn-primary btn-sm" for="b1">Mon</label>

                                <input type="checkbox" name="weekly_view[]" value="Tuesday" class="btn-check tue" id="b3" autocomplete="off" disabled>
                                <label class="btn btn-primary btn-sm" for="b3">Tue</label>

                                <input type="checkbox" name="weekly_view[]" value="Wednesday" class="btn-check wed" id="b4" autocomplete="off" disabled>
                                <label class="btn btn-primary btn-sm" for="b4">Wed</label>

                                <input type="checkbox" name="weekly_view[]" value="Thursday" class="btn-check thu" id="b5" autocomplete="off" disabled>
                                <label class="btn btn-primary btn-sm" for="b5">Thu</label>

                                <input type="checkbox" name="weekly_view[]" value="Friday" class="btn-check fri" id="b6" autocomplete="off" disabled>
                                <label class="btn btn-primary btn-sm" for="b6">Fri</label>

                                <input type="checkbox" name="weekly_view[]" value="Saturday" class="btn-check sat" id="b7" autocomplete="off" disabled>
                                <label class="btn btn-primary btn-sm" for="b7">Sat</label>
                            </div>
                            <small><span class="text-danger">Note:</span> Leave if no changes.</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <label class="fs-5 mb-1" style="font-weight: 500">Recur Schedule on</label>
                                <input type="text" id="daterange-view" name="daterange" placeholder="From - To" :value="row.date_range" class="form-control form-control-sm" autocomplete="off" disabled>
                                <input type="hidden" name="recur_from_view" :value="row.date_from" id="recur_from-view">
                                <input type="hidden" name="recur_to_view" :value="row.date_to" id="recur_to-view">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3" :class="row.recurring == 0 ? '' : 'd-none'" id="hide-on-recurring-schedule-view">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Type</label>
                            <select id="meeting-time-type-view" name="meeting_time_type" class="form-control" onchange="editChangeTimeType()" disabled>
                                <option value="">Select an Option</option>
                                <template v-if="row.meeting_type == 'whole day'">
                                    <option value="whole day" selected>Whole Day</option>
                                    <option value="half day">Half Day</option>
                                    <option value="others">Others</option>
                                </template>
                                <template v-else-if="row.meeting_type == 'half day'">
                                    <option value="whole day">Whole Day</option>
                                    <option value="half day" selected>Half Day</option>
                                    <option value="others">Others</option>
                                </template>
                                <template v-else>
                                    <option value="whole day">Whole Day</option>
                                    <option value="half day">Half Day</option>
                                    <option value="others" selected>Others</option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3" :class="row.meeting_type == 'whole day' ? '' : 'd-none'" id="for-whole-day-view">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Date/Time Options</label>
                            <input type="text"  id="wholedaypicker-view" data-dropdown-parent="#kt_modal_edit_event" :value="row.startDate" name="wholeday" placeholder="YYYY-MM-DD" class="form-control form-control-sm" autocomplete="off" disabled>
                        </div>
                    </div>
                    <div class="row mb-3" :class="row.meeting_type == 'half day' ? '' : 'd-none'" id="for-half-day-view">
                        <label class="fs-5 mb-1" style="font-weight: 500">Date/Time Options</label>
                        <div class="col-lg-8 col-md-8 col-sm-12">
                            <input type="text" id="halfdaypicker-view" data-dropdown-parent="#kt_modal_edit_event" :value="row.startDate" name="halfday" placeholder="YYYY-MM-DD" class="form-control form-control-sm" autocomplete="off" disabled>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <select name="halfdayindicator" id="" class="form-control form-control-sm" disabled>
                                <template v-if="row.meridiem == 'am'">
                                    <option value="am" selected>AM</option>
                                    <option value="pm">PM</option>
                                </template>
                                <template v-else>
                                    <option value="am">AM</option>
                                    <option value="pm" selected>PM</option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3" :class="row.meeting_type == 'others' ? '' : 'd-none'" id="for-others-view">
                        <label class="fs-5 mb-1" style="font-weight: 500">Date/Time Options</label>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <input type="text" id="others_from-view" name="date_from" placeholder="From" :value="row.start_date" class="form-control form-control-sm" autocomplete="off" disabled>
                            <small><span class="text-danger">Note:</span> Time is 24 Hour format</small>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <input type="text" id="others_to-view" name="date_to" placeholder="To" :value="row.end_date" class="form-control form-control-sm" autocomplete="off" disabled>
                            <small><span class="text-danger">Note:</span> Time is 24 Hour format</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Contact Number</label>
                            <input type="text" id="view-contact" name="contact" :value="row.contact_number" maxlength="11" size="11" class="form-control form-control-sm" required autocomplete="off" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500">Remarks</label>
                            <textarea id="view-remarks" class="form-control form-control-sm" name="remarks" rows="3" disabled>{{ row.description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                <button type="button" id="kt_modal_edit_event_close" class="btn btn-light-danger btn-sm me-3" onclick="closeEditModal()">Close</button>
                <button type="button" id="kt_modal_archive_event" class="btn btn-danger btn-sm me-3" @click="archiveEvent(row.event_id)"><i class="bi bi-file-zip"></i> Archive</button>
                        <button type="button" id="kt_modal_edit_event_button1" class="btn btn-warning btn-sm me-3 d-none" onclick="updateEvent()">Edit</button>
                        <button type="submit" id="kt_modal_edit_event_submit1" class="btn btn-primary btn-sm" disabled>
                            <span class="indicator-label">Submit</span>
                        </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="legend" class="action">
    <button>
    <i class="bi bi-question-circle-fill fs-1"></i>
    </button>
    <ul>
        <li><b>Meeting Rooms Legend</b></li>
        <template v-if="count > 0">
            <li v-for="rows in row">
                <div class="row">
                   <div class="col-8">
                        {{ rows.name }}
                   </div>
                   <div class="col-2">
                       <span style="width: 15px; height: 15px; display: block; border-radius: 50%" :style="{ background: rows.color }"></span>
                   </div>
                </div>
            </li>
        </template>
        <template v-else>
            No Facility Available
        </template>
        <li><span class="text-danger">Note:</span> Time is in 24-Hour Format</li>
    </ul>
</div>

