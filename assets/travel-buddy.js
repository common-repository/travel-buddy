jQuery(document).ready(function($) {
    $('#travel-buddy-form').submit(function(event) {
        event.preventDefault();
         $('#travel-buddy-result').html("");
        var country = $('#country-select').val();
        var destination = $('#destination-select').val();
        var wpnonc = $('#wpnonc').val();
        
        if (country != destination) {
            $.ajax({
                type: 'POST',
                url: travelbuddyAjax.ajaxurl,
                data: {
                    action: 'trbdai_action',
                    passport: country,
                    destination: destination,
                    wpnonc: wpnonc
                },
                success: function(response) {
                    $('#travel-buddy-result').html(response);
                },
                error: function(xhr,status, error){
                    //alert('Request Status: ' + error + ' Status Text: ' + xhr.statusText + ' ' + xhr.responseText + ' url: ' + travelbuddyAjax.ajaxurl);
                 
                }
            });
        }
    });
});
