<div class="row">
    <div class="col-lg-10 col-md-10 col-sm-12 mx-auto my-1">
        <div class="card-toolbar mb-3 row justify-content-between align-items-center">
            <div class="col-lg-2 col-md-2 col-sm-12">
                <a href="<?=base_url('/portal/dashboard') ?>" class="btn btn-flex btn-primary btn-sm" id="add-facility">
                    Back
                </a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12" style="text-align: right">
                <button class="btn btn-flex btn-primary btn-sm" id="add-facility" onclick="createFacility()">
                    <span class="svg-icon svg-icon-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black"></rect>
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black"></rect>
                        </svg>
                    </span>
                    Facility
                </button>
            </div>
        </div>
        <div class="card card-xl-stretch">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-sm-3">
                        <h3>Facility Masterfile</h3>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="input-group mb-5 form-control-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="generalSearch" class="form-control form-control-sm" placeholder="Search" aria-label="Search"/>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="facility-table" class="table table-row-bordered">
                        <thead>
                            <tr class="fs-6">
                                <th class="fw-bold" width="50%">Facility Name</th>
                                <th class="fw-bold" width="15%">Facility Color</th>
                                <th class="fw-bold" width="15%">Status</th>
                                <th class="fw-bold" width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="create_facility" data-bs-backdrop="static" aria-labelledby="staticBackdrop" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="form-group" id="createFacility">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0 justify-content-between">
                    <h3 class="card-title">Add Facility</h3>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="fs-5 mb-1" style="font-weight: 500" required>Facility Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" autocomplete="off" required>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500" required>Facility Color</label>
                            <input type="text" name="facility_color" class="form-control form-control-sm" autocomplete="off" data-jscolor="{zIndex: 9999}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="kt_modal_facility" data-bs-backdrop="static" aria-labelledby="staticBackdrop" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="form-group" id="updateFacility">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0 justify-content-between">
                    <h3 class="card-title">Edit Facility</h3>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="facility-id" name="id">
                    <div class="form-group mb-3">
                        <label class="fs-5 mb-1" style="font-weight: 500" required>Facility Name</label>
                        <input type="text" id="u-name" name="name" class="form-control form-control-sm" autocomplete="off" required>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="fs-5 mb-1" style="font-weight: 500" required>Facility Color</label>
                            <input type="text" name="facility_color" id="color" class="form-control form-control-sm" autocomplete="off" data-jscolor="{zIndex: 9999}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>