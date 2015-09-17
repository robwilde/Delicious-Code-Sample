<?php
	/**
	 * Created by WTC.
	 * User: Rob Wilde
	 * Date: 13/04/2015
	 * Time: 12:34 AM
	 */
?>
<!--gmap-zones/public/partials/gmap-zones-map-api.php-->
<script>
	var geocoder;
	var data;
	var map;
	var markers = [];
	var doc = document;
	var src = [<?= implode( ", ", $this->file_list() ) ?>];

	// initialize the google maps
	function initialize() {
		geocoder = new google.maps.Geocoder();
		var qldmap = new google.maps.LatLng(-27.4997864, 153.2120531);
		var mapOptions = {
			zoom: 9,
			center: qldmap
		};
		map = new google.maps.Map(doc.getElementById('map'), mapOptions);

		var myParser = new geoXML3.parser({map: map});
		myParser.parse(src);
	}

	// load the google maps API
	function loadScript() {
		var script = doc.createElement('script');
		script.type = 'text/javascript';
		script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp' +
			'&callback=initialize';
		doc.body.appendChild(script);
	}

	window.onload = loadScript();

	//		check the post code entered, grab the POA details and set the cookie
	function codeAddress() {

		removeMarkers();
		var response, grabResponse, deliveryInfo,
			address = doc.getElementById('address').value;


		var deliveryYes = '<p>Congratulations we deliver to your area.<br />Your Delivery Zone is<br />';
		var deliveryNo = '<p>Please be advised we do not deliver to your area. <br />';
		var pickupDetails = 'You can still place an order and pickup from our factory. <br /> For more information please call 1300 645 665</p>';
		var deliveryIslands = '<p>Please be advised although your postcode is within our delivery area, we do not delivery to Islands. <br />';

		jQuery.cookie('chef_poa_cookie', address, {expires: 364, path: '/'});
		jQuery.removeCookie('chef_info_cookie', {expires: 364, path: '/'});
		jQuery.removeCookie('chef_zone_cookie', {expires: 364, path: '/'});


		jQuery('#spinner').ajaxStart(function () {
			jQuery(this).fadeIn('fast');
		}).ajaxStop(function () {
			jQuery(this).stop().fadeOut('fast', function () {
				jQuery('#delivery_status').html(deliveryInfo).fadeIn('slow');
			});
			jQuery('#modal_btn').slideDown(300);
		});

		jQuery.ajax({
			url: "<?php bloginfo('url'); ?>/wp-admin/admin-ajax.php",
			type: "POST",
			data: {'action': 'get_post_code', 'post_code': address},
			success: function (response) {
				var details = JSON.parse(response);
				if (typeof details.zoneName == "number" && typeof details.islands == "number") {
					// deliveryNO
					deliveryInfo = deliveryNo + pickupDetails;
					// clean up the cookies
					console.info('setting the cookie info deliveryNo');
					jQuery.cookie('chef_info_cookie', 'deliveryNo', {expires: 364, path: '/'});

				} else if (typeof details.zoneName == "string" && typeof details.islands == "object") {
					// deliveryYes
					deliveryInfo = deliveryYes + details.zoneName + '</p>';
					// clean up the cookies
					console.info('setting the cookie info deliveryYes');

					jQuery.cookie('chef_info_cookie', 'deliveryYes', {expires: 364, path: '/'});
					jQuery.cookie('chef_zone_cookie', details.zoneName, {expires: 364, path: '/'});
				} else {
					// deliveryIsland
					deliveryInfo = deliveryIslands + '<span class="islands">' + details.islands + '</span>' + pickupDetails;
				}
			},
			error: function () {
				console.log('Cannot retrieve data.');
			}
		});

		address += ', QLD AU';
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
		if (typeof map == "undefined") return;
		setTimeout(function () {
			resizingMap();
		}, 100);
	}

	function resizingMap() {
		if (typeof map == "undefined") return;
		var center = map.getCenter();
		google.maps.event.trigger(map, "resize");
		map.setCenter(center);
		map.setZoom(9);
	}


	function clearCookie() {
		$.removeCookie('chef_poa_cookie', {path: '/'});
		$.removeCookie('chef_zone_cookie', {path: '/'});
		$.removeCookie('chef_deliver_id', {path: '/'});
		$.removeCookie('chef_info_cookie', {path: '/'});
	}


</script>