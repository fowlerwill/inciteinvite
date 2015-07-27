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
            _bindCreateEventButton($eventButton);
        })

        $('.iievent_day').hover(
            //mouseover
            function(event) {

                var button = $('<button class="button btn-xs btn-primary iievent_button iievent_create_button" ' +
                    'data-modal="iievent_modal_new"><span class="glyphicon glyphicon-plus"></span></button>');
                var date = $(event.currentTarget).attr('data-date');
                _bindCreateEventButton(button, date);

                $(event.currentTarget).children('.panel-heading')
                    .prepend(button);
            },
            //mouseout
            function(event) {
                $(event.currentTarget).find('button.iievent_create_button').remove();
            })
    })

    /**
     * Bind the create event button to the appropriate modal.
     * @param elem
     * @private
     */
    function _bindCreateEventButton($elem, date) {

        $elem.on('click', function(event) {
            if(date) {
                $('#' + $elem.attr('data-modal') +' #iievent_date').attr('value', date);
            }
            $( '#' + $(this).attr('data-modal')).modal();
        })
    }

})( jQuery );
