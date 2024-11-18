<div class="row">
    <div class="col-lg-10 col-md-10 col-sm-12 mx-auto my-1">
        <div class="card-toolbar mb-3 row justify-content-between align-items-center">
            <div class="col-lg-2 col-md-2 col-sm-12">
                <a href="<?=base_url('/portal/dashboard') ?>" class="btn btn-flex btn-primary btn-sm" id="add-facility">
                    Back
                </a>
            </div>
        </div>
        <div class="card card-xl-stretch">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-sm-3">
                        <h3>Activity Logs Masterfile</h3>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="input-group mb-5 form-control-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="activitySearch" class="form-control form-control-sm" placeholder="Search" aria-label="Search"/>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="activity-table" class="table table-row-bordered">
                        <thead>
                            <tr class="fs-6">
                                <th class="fw-bold" width="20%">Table Name</th>
                                <th class="fw-bold" width="20%">User</th>
                                <th class="fw-bold" width="15%">Type</th>
                                <th class="fw-bold" width="15%">Log</th>
                                <th class="fw-bold" width="20%">Date Added</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>