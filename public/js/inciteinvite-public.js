(function( $ ) {
	'use strict';

    $(document).ready(function($) {
        $('.iievent_date').datetimepicker();
        $('.form-control').each(function(index) {
            if( $(this).val() != '' ) {
                $(this).addClass('form-control-full');
            }
        })

        $('.iievent_button').each(function(index) {
            var $eventButton = $(this);
            $eventButton.on('click', function(event) {
                console.log('here' + $(this).attr('data-modal'));
                $( '#' + $(this).attr('data-modal')).modal();
            })
        })
    })

})( jQuery );
