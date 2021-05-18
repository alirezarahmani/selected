//google map
var gmap;
var marker = [];
function initAutocomplete() {
    const haightAshbury = {lat: 51.5074, lng: 0.1278};
     gmap = new google.maps.Map(document.getElementById('map'), {
        center: haightAshbury,
        zoom: 13,
        zoomControl: false,
           draggable: false,
   scaleControl: false,
            fullscreenControl: false,
   scrollwheel: false,
   navigationControl: false,
   mapTypeControl: false,
   streetViewControl: false
    });

    gmap.addListener('click', e => {
        console.log(e);
        let address_locations = JSON.parse(e['nb'] ? e['nb']['view']['localStorage']['googleLocation'] : e['ub']['view']['localStorage']['googleLocation']);
        localStorage.setItem('googleLocation', JSON.stringify(address_locations));
        console.log('location',address_locations);
        deleteMarkers();
        addMarker(e.latLng);
    });

    var card = document.getElementById('pac-card');
    var input = document.getElementById('pac-input');
    var types = document.getElementById('type-selector');
    var strictBounds = document.getElementById('strict-bounds-selector');

    gmap.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);

    var autocomplete = new google.maps.places.Autocomplete(input);

    // Bind the map's bounds (viewport) property to the autocomplete object,
    // so that the autocomplete requests use the current map bounds for the
    // bounds option in the request.
    autocomplete.bindTo('bounds', gmap);

    // Set the data fields to return when the user selects a place.
    autocomplete.setFields(
        ['address_components', 'geometry', 'icon', 'name']);
    var marker = new google.maps.Marker({
        map: gmap,
        anchorPoint: new google.maps.Point(0, -29)
    });

    autocomplete.addListener('place_changed', function() {
        
        marker.setVisible(false);
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            // User entered the name of a Place that was not suggested and
            // pressed the Enter key, or the Place Details request failed.
            window.alert("No details available for input: '" + place.name + "'");
            return;
        }

        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
            gmap.fitBounds(place.geometry.viewport);
        } else {
            gmap.setCenter(place.geometry.location);
            gmap.setZoom(17);  // Why 17? Because it looks good.
        }

        marker.setPosition(place.geometry.location);

        console.log('google map',place.geometry.location.lat());
        console.log('google map',place.geometry.location.lng());
        console.log('google map',place);
        deleteMarkers();
        localStorage.setItem('googleLocation', JSON.stringify(place))
        $('#latitude').val(place.geometry.location.lat());
        $('#langtitude').val(place.geometry.location.lng());
        var infowindow = new google.maps.InfoWindow();
        // var infowindowContent = document.getElementById('infowindow-content');
        // console.log(infowindowContent)
        infowindow.setContent(place['name']);
        infowindow.close();

        marker.setVisible(true);

        var address = '';
        if (place.address_components) {
            address = [
                (place.address_components[0] && place.address_components[0].short_name || ''),
                (place.address_components[1] && place.address_components[1].short_name || ''),
                (place.address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
        }

        //  infowindowContent.children['place-icon'].src = place.icon;
        // infowindowContent.children['place-name'].textContent = place.name;
        // infowindowContent.children['place-address'].textContent = address;
        infowindow.open(gmap, marker);
    });

    // Sets a listener on a radio button to change the filter type on Places
    // Autocomplete.
    function setupClickListener(id, types) {
        var radioButton = document.getElementById(id);
        if (radioButton) {           
            radioButton.addEventListener('click', function() {
                autocomplete.setTypes(types);
            });
        }
    }

    setupClickListener('changetype-all', []);
    setupClickListener('changetype-address', ['address']);
    setupClickListener('changetype-establishment', ['establishment']);
    setupClickListener('changetype-geocode', ['geocode']);

    let userStrict = document.getElementById('use-strict-bounds');
    if (userStrict) {
        userStrict.addEventListener('click', function() {
            console.log('Checkbox clicked! New state=' + this.checked);
            autocomplete.setOptions({strictBounds: this.checked});
        });
    }

}

function addMarker(location) {
    const newMarker = new google.maps.Marker({
        position: location,
        map: gmap
    });
    marker.push(newMarker);
}

function setMapOnAll(map) {
    for (let i = 0; i < marker.length; i++) {
        marker[i].setMap(map);
    }
}

function deleteMarkers(map) {
    setMapOnAll(null);
    marker = [];
}
