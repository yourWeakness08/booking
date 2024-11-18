let archiveTbl, archiveFilter = '';

archiveTbl = $("#archive-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: baseUrl('portal/archives/get_datatable'),
        type: "post",
        dataType: "json",
        data: function(data){
            data.search['value'] = archiveFilter
        }
    },
    order: [[2, 'asc']],
    columns: [
        { data: 'name', },
        { data: 'archived_user', },
        { data: 'date_archived' },
        { data: null }
    ], 
    columnDefs:[
        {
            targets: 0,
            render: function(data, type, row){
                var action = '';

                action += '<small class="text-muted fs-7">' + trimString(row.tblname) + '</small>';
                action += '<p style="font-weight: 600">'+ data +'</p>';
                return action;
            }
        },
        {
            targets: -1,
            orderable: false,
            render: function(data, type, row){
                let action;

                action = `<button type="button" onclick="restoreArchive(${row.id}, '${row.tblname}')" id="archive" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Restore"><i class="bi bi-reply-fill"></i></button>`;

                return action;
            }
        }
    ]
});

$(document).ready( function(){
    $('#archiveSearch').donetyping(function(e){
        archiveFilter = this.value;
        archiveTbl.ajax.reload();
    }, 400);
});

function restoreArchive(id, tbl){
    Swal.fire({
        text: "Restore Item?",
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Restore",
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: "btn btn-primary order-2",
            cancelButton: 'btn btn-danger order-1'
        }
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url: baseUrl('portal/archives/restore'),
                type: "POST",
                data: {
                    id: id,
                    tbl: tbl
                },
                dataType: 'json',
                success: function(response){
                    if(response.state){
                        Swal.fire(response.msg, '', 'success');
                        archiveTbl.ajax.reload();
                    }else{
                        Swal.fire(response.msg, '', 'warning');
                    }
                }
            });
        }
    });
}

function trimString(txt){
    var trimmed =  txt.replace('tbl_', '');
    
    trimmed = trimmed.toLowerCase().replace(/\b[a-z]/g, function(letter) {
        return letter.toUpperCase();
    });

    return trimmed;
}