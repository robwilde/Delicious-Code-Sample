<a class = "popup-modal" href = "#gmapz_modal"></a>
<a class = "clear_cookie" href = "#">CLEAR COOKIE</a>
<div id = "gmapz_modal" class = "white-popup-block mfp-hide">
	<div id = "modal_body">
		<h3>Delivery Zones</h3>

		<div class = "row">
			<div class = "span15">
				<div class = "postcode_notice">The Contented Chef has limited meal delivery zones.
				                               Please confirm your delivery zone by enter your postcode
				</div>
				<script>
					jQuery(document).ready(function ($) {
						$postcode = $.cookie('chef_poa_cookie');
						if( $postcode !== undefined ){
							$('#address').val($postcode);
						}
					});
				</script>
				<div class = "row clearfix modal-info">
					<div class = "span5">
						<form method = "post" action = "" id = "geocoding_form" name = "postcode">
							<label for = "address">Postcode: </label>

							<div class = "input">
								<input id = "address" type = "textbox" value = "">
								<input type = "button" value = "PostCode" onclick = "codeAddress()">
							</div>
						</form>

					</div>
					<div id = "spinner" class = "span10">
						<img src = "<?= plugin_dir_url( __FILE__ ) . 'spiffygif.gif' ?>" alt = "Loading..." />
					</div>
					<div class = "span10 status_parent">
						<div id = "delivery_status"></div>
						<div id = "deliveryNote"></div>
					</div>
				</div>
				<div class = "modalfooter_parent">
					<div id = "modal_btn" class = "modalfooter">
						<a class = "popup-modal-dismiss" href = "#">
							<button type = "button">Continue Shopping</button>
						</a>
						<a href = "/store/cart/" class = "checkout wc-forward">
							<button type = "button">View Cart</button>
						</a>
					</div>
				</div>
				<div class = "popin">
					<div id = "map">
					</div>
				</div>
			</div>

		</div>
	</div>
	<script>
		jQuery(document).ready(function ($) {

			$('#delivery_status').hide();
			$('#deliveryNote').hide();
			$('#modal_btn').hide();

			$('.clear_cookie').on('click', function (e) {
				e.preventDefault();
				$.removeCookie('chef_poa_cookie', {path: '/'});
				$.removeCookie('chef_zone_cookie', {path: '/'});
				$.removeCookie('chef_deliver_id', {path: '/'});
				$.removeCookie('chef_info_cookie', {path: '/'});
			});

		});
	</script>
</div>
