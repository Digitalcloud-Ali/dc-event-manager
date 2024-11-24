(function($) {
    'use strict';

    $(document).ready(function() {
        // Date picker initialization
        $('.dc-event-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        // Time picker initialization
        $('.dc-event-timepicker').timepicker({
            timeFormat: 'HH:mm',
            interval: 15,
            minTime: '00:00',
            maxTime: '23:45',
            defaultTime: '09:00',
            startTime: '00:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
        });

        // AR model preview
        $('#dc_event_ar_model').on('change', function() {
            var modelUrl = $(this).val();
            if (modelUrl) {
                $('#ar-model-preview').html('<a-scene embedded arjs="sourceType: webcam;"><a-marker preset="hiro"><a-entity gltf-model="' + modelUrl + '" scale="0.1 0.1 0.1"></a-entity></a-marker><a-entity camera></a-entity></a-scene>');
            } else {
                $('#ar-model-preview').empty();
            }
        });

        // Carbon footprint calculation
        $('#calculate-carbon-footprint').on('click', function(e) {
            e.preventDefault();
            var attendees = $('#dc_event_attendees').val();
            var duration = $('#dc_event_duration').val();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dc_calculate_carbon_footprint',
                    attendees: attendees,
                    duration: duration,
                    nonce: dc_event_manager_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#dc_event_carbon_footprint').val(response.data.footprint);
                    } else {
                        alert('Failed to calculate carbon footprint');
                    }
                }
            });
        });
    });
})(jQuery);