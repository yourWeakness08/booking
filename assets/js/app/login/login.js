$(document).ready( function(){
    $("form").submit( function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
            url : baseUrl('auth/processLogin'),
            method: "POST",
            dataType: 'json',
            data: data,
            success: function(response){
                $("#alert").removeClass('d-none');
                if(response.state == 'success'){
                    $('#alert').addClass('alert-success');
                    $('#alert').removeClass('alert-danger');
                    $("#msg").text(response.msg);

                    window.location.href= baseUrl('portal/dashboard');
                }else{
                    $('#alert').removeClass('alert-success');
                    $('#alert').addClass('alert-danger');
                    $("#msg").text(response.msg);
                }
            }
        });
    });
});

function baseUrl(url){
    return window.location.origin + '/booking/' + url;
}