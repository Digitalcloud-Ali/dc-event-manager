(function($) {
    'use strict';

    $(document).ready(function() {
        var map, marker, autocomplete;
        var mapElement = document.getElementById('map');
        var locationInput = document.getElementById('event_location');
        var latitudeInput = document.getElementById('event_latitude');
        var longitudeInput = document.getElementById('event_longitude');

        function initMap() {
            var defaultLocation = {lat: 0, lng: 0};
            if (latitudeInput.value && longitudeInput.value) {
                defaultLocation = {
                    lat: parseFloat(latitudeInput.value),
                    lng: parseFloat(longitudeInput.value)
                };
            }

            map = new google.maps.Map(mapElement, {
                center: defaultLocation,
                zoom: 13
            });

            marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true
            });

            google.maps.event.addListener(marker, 'dragend', function() {
                var position = marker.getPosition();
                latitudeInput.value = position.lat();
                longitudeInput.value = position.lng();
            });

            autocomplete = new google.maps.places.Autocomplete(locationInput);
            autocomplete.bindTo('bounds', map);

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }

                marker.setPosition(place.geometry.location);
                latitudeInput.value = place.geometry.location.lat();
                longitudeInput.value = place.geometry.location.lng();
            });
        }

        if (mapElement) {
            initMap();
        }
    });
})(jQuery);