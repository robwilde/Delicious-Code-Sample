    var geocoder;
    var map;
    var markers = [];

    var src ;

    function initialize() {
        var qldmap = new google.maps.LatLng(-27.4997864, 153.2120531);
        var mapOptions = {
            zoom: 14,
            center: qldmap
        };
        map = new google.maps.Map(document.getElementById('map'), mapOptions);

        var myParser = new geoXML3.parser({map: map});
//		console.log(myParser);
        myParser.parse(src);

//		google.maps.event.addDomListener(window, 'resize', resizingMap());
    }

    function loadScript() {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp' +
        '&callback=initialize';
        document.body.appendChild(script);
    }

    window.onload = loadScript();
//		google.maps.event.addDomListener(window, 'load', loadScript());

    function codeAddress() {

        removeMarkers();
        var address = document.getElementById('address').value;
        address += ', QLD AU';
        console.log(address);
        geocoder.geocode({'address': address}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location
                });
                markers.push(marker);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }

    function removeMarkers() {
        for (i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
    }

    function resizeMap() {
        console.log('resizeMap');
        console.log(typeof map);
        if (typeof map == "undefined") return;
        setTimeout(function () {
            resizingMap();
        }, 200);
    }

    function resizingMap() {
        console.log('resizingMap');
        if (typeof map == "undefined") return;
        var center = map.getCenter();
        console.log(center);
        google.maps.event.trigger(map, "resize");
        map.setCenter(center);
        map.setZoom(11);
    }
