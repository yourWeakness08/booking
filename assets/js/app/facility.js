var tblFacility;
let FacilitysearchFilter = null;
$(document).ready( function(){
    tblFacility = $("#facility-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl('portal/facility/get_datatable'),
            type: "post",
            dataType: "json",
            data: function(data){
                data.search['value'] = FacilitysearchFilter
            }
        },
        columns: [
            { data: 'name' },
            { data: 'color' },
            { data: 'status' },
            { data: null }
        ],
        columnDefs: [
            {
                targets : 1,
                orderable: false,
                render: function(data){
                    let action;
                    action = '<div class="row align-items-center"><span id="test" class="col-lg-2 col-md-2 col-sm-12" style="background-color: '+data+'; width: 20px; height: 20px;"></span>' + '<p class="col-lg-10 col-md-10 col-sm-12 mb-0">' + data + '</p>' + '</div>';
                    return action;
                }
            },
            {
                targets: 2,
                render: function(data){
                    let action;
                    if(data == 'Active'){
                        action = '<span class="badge badge-light-success fs-8 fw-bolder">Active</span>';
                    }else{
                        action = '<span class="badge badge-light-danger fs-8 fw-bolder">Inactive</span>';
                    }
                    return action;
                }
            },
            {
                targets: 3,
                orderable: false,
                render: function(data, type, row){
                    const tempData = JSON.stringify(row);
                    let action;
                    action = `<button type="button" onclick="updateFacility(${row.id}, '${row.name}', '${row.color}')" data-row="${tempData}" id="edit" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="bi bi-pencil"></i></button> <button type="button" onclick="updateStatus('${row.id}', '${row.status}')" id="changeStatus" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Status"><i class="bi bi-gear"></i></button> <button type="button" onclick="archiveFacility('${row.id}')" id="archive" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive"><i class="bi bi-file-zip"></i></button>`;

                    return action;
                }
            }
        ]
    });

    $("#updateFacility").submit( function(e){
        e.preventDefault();
        var data = $(this).serialize();
    
        $.ajax({
            url: baseUrl('portal/facility/update_facility'),
            type: "POST",
            dataType: "JSON",
            data: data,
            success: function(response){
                if(response.state){
                    Swal.fire(response.msg, '', 'success');
                    tblFacility.ajax.reload();
                    $("#kt_modal_facility").modal('hide');
                    $("#color").css('background-image: linear-gradient(to right, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 30px, rgba(0, 0, 0, 0) 31px, rgba(0, 0, 0, 0) 100%), url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAQCAYAAAB3AH1ZAAAAAXNSR0IArs4c6QAAAFNJREFUSEtjnDlz5n8GPODs2bP4pBmMjY3xyuPSn5KSwrB169bljKMOGA2B0RAY8BBIS0vDWw6Qm89hhQMu/U5OTgxLlixZzjjqgNEQGA2BgQ4BADx2qkG0ILJiAAAAAElFTkSuQmCC) !important');
                }else{
                    Swal.fire(response.msg, '', 'error');
                }
            }
        });
    });

    $("#createFacility").submit( function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url: baseUrl('portal/facility/create_facility'),
            type: "POST",
            dataType: "JSON",
            data: data,
            success: function(response){
                if(response.state){
                    Swal.fire(response.msg, '', 'success');
                    tblFacility.ajax.reload();
                    $("#createFacility").trigger('reset');
                    $("#create_facility").modal('hide');
                }else{
                    Swal.fire(response.msg, '', 'error');
                }
            }
        });
    });

    $('#generalSearch').donetyping(function(e){
        FacilitysearchFilter = this.value;
        tblFacility.ajax.reload();
    }, 400);
}); 


function updateFacility(id, name, color){
    $("#kt_modal_facility").modal('show');
    $("#facility-id").val(id);
    $("#u-name").val(name);
    if(color && color != ''){
        $("#color").val(color);
        $("#color").attr('data-current-color', color);
    }else{
        $("#color").val('#FFFFFF');
        $("#color").attr('data-current-color', '#FFFFFF');
    }
    
    $("#color").css('background-image: linear-gradient(to right, '+color+' 0%, '+color+' 30px, rgba(0, 0, 0, 0) 31px, rgba(0, 0, 0, 0) 100%), url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAQCAYAAAB3AH1ZAAAAAXNSR0IArs4c6QAAAFNJREFUSEtjnDlz5n8GPODs2bP4pBmMjY3xyuPSn5KSwrB169bljKMOGA2B0RAY8BBIS0vDWw6Qm89hhQMu/U5OTgxLlixZzjjqgNEQGA2BgQ4BADx2qkG0ILJiAAAAAElFTkSuQmCC") !important');
}

function updateStatus(id, status){
    const tempStatus = status == 'Active' ? 'Inactive' : "Active";
    Swal.fire({
        text: "Update `" + status + "` status to `" + tempStatus + "`",
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Update Status",
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: "btn btn-primary order-2",
            cancelButton: 'btn btn-danger order-1'
        }
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url : baseUrl('portal/facility/update_facility_status/') + id,
                type: "POST",
                dataType: "json",
                data: {
                    status: tempStatus
                },
                success: function(response){
                    if(response.state){
                        Swal.fire(response.msg, '', 'success');
                        tblFacility.ajax.reload();
                    }else{
                        Swal.fire(response.msg, '', 'error');
                    }
                }
            });
        }
    });
}

function archiveFacility(id){
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
                url : baseUrl('portal/facility/archive_facility/') + id,
                type: "POST",
                dataType: "json",
                success: function(response){
                    if(response.state){
                        Swal.fire(response.msg, '', 'success');
                        tblFacility.ajax.reload();
                    }else{
                        Swal.fire(response.msg, '', 'error');
                    }
                }
            });
        }
    });
}

function createFacility(){
    $("#create_facility").modal('show');
}