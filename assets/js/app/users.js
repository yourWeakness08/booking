var tbl;
let searchFilter = null;

$(document).ready( function(){
    tbl = $("#users-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl('portal/users/get_datatable'),
            type: "post",
            dataType: "json",
            data: function(data){
                data.search['value'] = searchFilter
            }
        },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'username' },
            { data: 'role' },
            { data: null },
        ],
        columnDefs: [
            {
                targets: 3,
                orderable: false
            },
            {
                targets: 4,
                orderable: false,
                render: function(data, type, row){
                    const tempData = JSON.stringify(row);
                    let action = '';
                    const suspend = row.is_active == 1 ? '' : 'disabled';
                    const status = row.is_active == 1 ? 'active' : 'inactive';
                    action += `<button type="button" onclick="editUser(${row.id})" id="edit-user" class="btn btn-warning btn-sm me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" ${suspend}><i class="bi bi-pencil"></i></button>`;
                    action += `<button type="button" onclick="archive('${row.id}')" id="archive" class="btn btn-danger btn-sm me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive" ${suspend}><i class="bi bi-file-zip"></i></button>`;
                    action += `<button type="button" onclick="setStatus('${status}', '${row.id}')" id="status" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Suspend"><i class="bi bi-exclamation-triangle"></i></button>`;


                    return action;
                }
            }
        ],
        createdRow: function(tr, row, data, dataIndex ) {
            var _currentRow = $(tr);
            var is_active = row.is_active;

			if(typeof is_active !== 'undefined' && is_active == 0){
				_currentRow.addClass("suspended");
				_currentRow.attr('title', 'Suspended');
			}
        }
    });

    $('#generalSearch').donetyping(function(e){
        searchFilter = this.value;
        tbl.ajax.reload();
    }, 400);

    $("#add-user").click( function(){
        $("#create_user").modal('show');
    });

    $("#createUser").submit( function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url : baseUrl('portal/users/create_user'),
            method: "POST",
            dataType: "json",
            data: data,
            success: function(response){
                if(response.state){
                    Swal.fire(response.msg, '', 'success');
                    tbl.ajax.reload();
                    $("#createUser").trigger('reset');
                    $("#create_user").modal('hide');
                }else{
                    // Swal.fire(response.msg, '', 'error');
                    Swal.fire({
                        icon: 'error',
                        title: "",
                        text: response.msg,
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '.modal'
                    });
                }
            }
        })
    });

    $("#updateUser").submit( function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url: baseUrl('portal/users/update_user'),
            type: "POST",
            dataType: "JSON",
            data: data,
            success: function(response){
                if(response.state){
                    Swal.fire(response.msg, '', 'success');
                    tbl.ajax.reload();
                    $("#kt_modal_facility").modal('hide');
                    $("#update_user").modal('hide');
                }else{
                    // Swal.fire(response.msg, '', 'error');
                    Swal.fire({
                        icon: 'error',
                        title: "",
                        text: response.msg,
                        customClass: {
                            container: 'my-swal'
                        },
                        target: '.modal'
                    });
                }
            }
        })
    });

    $("#togglePassword").click( function(){
        var count = 0;

        if($(this).hasClass('clicked')){
            $(this).removeClass('bi-eye-slash').addClass('bi-eye');
            $(this).removeClass('clicked');
            $("#password").attr('type', 'password');
        }else{
            $(this).removeClass('bi-eye').addClass('bi-eye-slash');
            $(this).addClass('clicked');
            $("#password").attr('type', 'text');
        }
    });
});

function editUser(id){
    $("#update_user").modal('show');

    $.ajax({
        url : baseUrl('portal/users/get_user/') + id,
        type: "GET",
        dataType: "json",
        success: function(response){
            $("#userId").val(response.id);
            $("#fname").val(response.firstname);
            $("#lname").val(response.lastname);
            $("#username").val(response.username);
            $("#password").val(response.original_password);
            $("#email").val(response.email);
            $("#role option[value="+response.role_id+"]").attr('selected',true);
            $("#telegram").val(response.telegram_chat_id);
        }
    })
    
}

function archive(id){
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
                url : baseUrl('portal/users/archive_user/') + id,
                type: "POST",
                dataType: "json",
                success: function(response){
                    if(response.state){
                        Swal.fire(response.msg, '', 'success');
                        tbl.ajax.reload();
                    }else{
                        Swal.fire(response.msg, '', 'error');
                    }
                }
            });
        }
    });
}

function setStatus(type, id){
    var status = type == 'active' ? 'Suspend' : 'Activate';
    Swal.fire({
        text: "Are you sure to `"+status+"` this user?",
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: status,
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: "btn btn-primary order-2",
            cancelButton: 'btn btn-danger order-1'
        }
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url: baseUrl('portal/users/suspend_user'),
                type: "POST",
                data: {
                    id: id,
                    type: type
                },
                dataType: 'json',
                success: function(response){
                    if(response.state){
                        Swal.fire(response.msg, '', 'success');
                        tbl.ajax.reload();
                    }else{
                        Swal.fire(response.msg, '', 'warning');
                    }
                }
            });
        }
    });
}