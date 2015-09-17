<?php

	/**
	 * Provide a admin area view for the plugin
	 *
	 * This file is used to markup the admin-facing aspects of the plugin.
	 *
	 * @link       http://www.wildetech.com.au
	 * @since      0.1.0
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/admin/partials
	 */
	if ( isset( $_REQUEST[ 'deleteFile' ] ) )
		$this->delete_file( $_REQUEST[ 'fileName' ] );

	if ( isset( $_POST[ 'submit' ] ) )
	{
		// test to see if insert new kml file or update old kml file
		$form_fields = array();
		$form_fields['post_type'] = (!empty($_POST['zone_slug']))
			? 'update' : 'insert';

		$form_fields [ 'zone_name' ] = $_POST[ 'zone_name' ];
		$form_fields['zone_slug'] = $_POST['zone_slug'];
		$form_fields[ 'line_color' ] = $this->google_colors( $_POST[ 'line_color' ] );
		$form_fields[ 'fill_color' ] = $this->google_colors( $_POST[ 'fill_color' ] );
		$form_fields[ 'post_codes' ] = $_POST[ 'postal_codes' ];

		$this->create_kml_file( $form_fields );
}

?>
<script>
	//	jQuery(document).ready(function () {
	var map;
	var src = [<?= implode( ", ", $this->file_list() ) ?>];

	function initialize() {
		var chicago = new google.maps.LatLng(-27.4997864, 153.2120531);
		var mapOptions = {
			zoom: 14,
			center: chicago
		};
		var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

		var myParser = new geoXML3.parser({map: map});
//		console.log(myParser);
		myParser.parse(src);
	}

	function loadScript() {
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp' +
		'&callback=initialize';
		document.body.appendChild(script);
	}

	window.onload = loadScript;
	//	});
</script>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div class="bootstrap-wrapper">
		<div class="row">
			<div class="col-md-8 map_container">
				<div id="map-canvas"></div>
			</div>
			<div class="col-md-3">
				<form method="POST"  action=""  role="form">
					<legend>Create Zone</legend>

					<div class="panel panel-default">
						<div class="panel-heading">Zone Settings</div>
						<div class="panel-body">
							<div class="form-group demo2">
								<label for="zone_name">Zone Name</label>
								<input type="text" class="form-control" name="zone_name"
									   id="zone_name" value="<?= $this->zone_name ?>"
									   placeholder="Zone Name">
								<input type="hidden" name="zone_slug" id="zone_slug" class="form-control"
									   value="<?= $this->zone_slug ?>" >

							</div>

							<div class="form-group">
								<label for="line_color">Line Color</label>

								<div class="input-group colorpicker-component demo demo-auto">

									<input type="text" value="<?= $this->line_color ?>" class="form-control" name="line_color"
										   id="line_color" placeholder="click box to select color"/>
									<span class="input-group-addon"><i></i></span>
								</div>
							</div>

							<div class="form-group">
								<label for="fill_color">Fill Color</label>

								<div class="input-group colorpicker-component demo demo-auto"
									 data-color="<?= ( empty($this->fill_color) )? 'rgba(150,216,62,0.55)':$this->fill_color; ?>">
									<input type="text" value="<?= $this->fill_color ?>" class="form-control" name="fill_color"
										   id="fill_color" placeholder="click box to select color"/>
									<span class="input-group-addon"><i></i></span>
								</div>
							</div>
							<div class="form-group">
								<label for="postal_codes">QLD Post Codes
									<small>(add multiple postcodes separated by space)</small>
								</label>
								<input type="text" class="form-control" value="<?= $this->post_codes ?>" name="postal_codes"
									   id="postal_codes" placeholder="enter postcodes separated by space">
							</div>
						</div>
					</div>

					<button name="submit" type="submit"
							class="btn btn-primary">Submit</button>
				</form>

				<div class="panel panel-default gmz-file-list">
					<div class="panel-heading">Zone KML Files (click a file to load)</div>
					<div class="panel-body">
						<?php
							$dir = $this->upload_folder();

							if ( is_dir( $dir ) )
							{
								if ( $dh = opendir( $dir ) )
								{
									while ( ( $file = readdir( $dh ) ) !== FALSE )
									{
										if ( is_file( $dir . $file ) )
										{
											echo '<a href="' . $this->gmapz_admin_url . '&deleteFile=true&fileName=' . $file . '">';
											echo '<img src="' . plugins_url( 'assets/delete.gif', dirname(__FILE__) ) . '"></a>';
											echo '<a href = "' . $this->gmapz_admin_url . '&fileName=' . $file . '">' . $file . '</a></br>';
										}
									}
									closedir( $dh );
								}
							}
						?>
					</div>
				</div>
				<a href="<?= $this->gmapz_admin_url ?>" class="btn btn-primary">Refresh</a>

			</div>
		</div>
	</div>
</div>
