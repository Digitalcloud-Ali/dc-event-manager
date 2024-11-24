(function($) {
    'use strict';

    $(document).ready(function() {
        $('.dc-event-ar-view').on('click', function(e) {
            e.preventDefault();
            var modelUrl = $(this).data('model');
            var eventId = $(this).data('event-id');
            initAR(modelUrl, eventId);
        }).attr('role', 'button').attr('aria-label', dcEventManageri18n.viewInAR);

        $('.dc-export-google-calendar').on('click', function(e) {
            e.preventDefault();
            exportToCalendar('google');
        }).attr('role', 'button').attr('aria-label', dcEventManageri18n.exportToGoogle);

        $('.dc-export-ical').on('click', function(e) {
            e.preventDefault();
            exportToCalendar('ical');
        }).attr('role', 'button').attr('aria-label', dcEventManageri18n.exportToiCal);

        function exportToCalendar(calendarType) {
            var eventId = $(this).closest('.dc-event-details').data('event-id');
            $.ajax({
                url: dc_event_manager.ajax_url,
                type: 'POST',
                data: {
                    action: 'dc_export_to_calendar',
                    event_id: eventId,
                    calendar_type: calendarType,
                    nonce: dc_event_manager.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (calendarType === 'google') {
                            window.open(response.data.url, '_blank');
                        } else {
                            var blob = new Blob([response.data.ical], {type: 'text/calendar;charset=utf-8'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = 'event.ics';
                            link.click();
                        }
                    }
                }
            });
        }
    });

})(jQuery);