(function($) {
    'use strict';

    $(document).ready(function() {
        var $calendar = $('#dc-event-calendar');
        if ($calendar.length) {
            var events = JSON.parse($calendar.attr('data-events'));
            $calendar.fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: events,
                eventClick: function(event) {
                    window.location.href = event.url;
                }
            });