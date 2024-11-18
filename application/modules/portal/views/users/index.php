<div class="row">
    <div class="col-lg-10 col-md-10 col-sm-12 mx-auto my-1">
        <div class="card-toolbar mb-3 row justify-content-between align-items-center">
            <div class="col-lg-2 col-md-2 col-sm-12">
                <a href="<?=base_url('/portal/dashboard') ?>" class="btn btn-flex btn-primary btn-sm" id="add-facility">
                    Back
                </a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12" style="text-align: right">
                <button class="btn btn-flex btn-primary btn-sm" id="add-user">
                    <span class="svg-icon svg-icon-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black"></rect>
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black"></rect>
                        </svg>
                    </span>
                    Users
                </button>
            </div>
        </div>
        <div class="card card-xl-stretch">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-sm-3">
                        <h3>Users Masterfile</h3>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="input-group mb-5 form-control-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="generalSearch" class="form-control form-control-sm" placeholder="Search" aria-label="Search"/>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="users-table" class="table table-row-bordered">
                        <thead>
                            <tr class="fs-6">
                                <th class="fw-bold" width="20%">Name</th>
                                <th class="fw-bold" width="15%">Email</th>
                                <th class="fw-bold" width="15%">Username</th>
                                <th class="fw-bold" width="15%">Role</th>
                                <th class="fw-bold" width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="create_user" data-bs-backdrop="static" aria-labelledby="staticBackdrop" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="form-group" id="createUser">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0 justify-content-between">
                    <h3 class="card-title">Add User</h3>
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
                    <div class="row mb-3">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>First Name</label>
                                <input type="text" name="fname" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Last Name</label>
                                <input type="text" name="lname" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Username</label>
                                <input type="text" name="username" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Password</label>
                                <input type="password" name="password" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Email</label>
                                <input type="email" name="email" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Role</label>
                                <select class="form-control" name="role" required>
                                    <option value="">Select an Option</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Guest</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Telegram Chat ID</label>
                                <input type="text" name="telegram" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
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

<div class="modal fade" id="update_user" data-bs-backdrop="static" aria-labelledby="staticBackdrop" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="form-group" id="updateUser">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0 justify-content-between">
                    <h3 class="card-title">Update User</h3>
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
                    <div class="row mb-3">
                        <input type="hidden" name="id" id="userId">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>First Name</label>
                                <input type="text" id="fname" name="fname" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Last Name</label>
                                <input type="text" id="lname" name="lname" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Username</label>
                                <input type="text" id="username" name="username" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Password</label>
                                <div class="group" style="position: relative;">
                                    <input type="password" id="password" name="password" class="form-control form-control-sm" autocomplete="off">
                                    <i class="bi bi-eye" id="togglePassword" style="position: absolute; right: 0px; top: 0px; padding: 11px 20px; cursor: pointer;"></i>
                                </div>
                                <!-- <small><span style="color: #c50f0f">Note:</span> Leave Blank if no changes.</small> -->
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Email</label>
                                <input type="email" id="email" name="email" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Role</label>
                                <select class="form-control" name="role" id="role" required>
                                    <option value="">Select an Option</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Guest</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label class="fs-5 mb-1" style="font-weight: 500" required>Telegram Chat ID</label>
                                <input type="text" id="telegram" name="telegram" class="form-control form-control-sm" autocomplete="off" required>
                            </div>
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
