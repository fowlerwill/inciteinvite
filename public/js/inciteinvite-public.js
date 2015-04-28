(function( $ ) {
	'use strict';

    $(document).ready(function($) {
        $('.iievent_date').datetimepicker();
        $('.form-control').each(function(index) {
            if( $(this).val() != '' ) {
                $(this).addClass('form-control-full');
            }
        })
    })

})( jQuery );
