let activityTbl, activityFilter = '';

activityTbl = $("#activity-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: baseUrl('portal/activity_logs/get_datatable'),
        type: "post",
        dataType: "json",
        data: function(data){
            data.search['value'] = activityFilter
        }
    },
    order: [[4, 'desc']],
    columns: [
        { data: 'tbl_name', },
        { data: 'name', orderable : false },
        { data: 'type' },
        { data: 'message' },
        { data: 'added_dt' }
    ],
});

$(document).ready( function(){
    $('#activitySearch').donetyping(function(e){
        activityFilter = this.value;
        activityTbl.ajax.reload();
    }, 400);
});