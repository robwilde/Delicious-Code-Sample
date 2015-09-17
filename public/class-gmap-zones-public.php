<?php
	/**
	 * The public-facing functionality of the plugin.
	 *
	 * @link       http://www.wildetech.com.au
	 * @since      0.1.0
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/public
	 */

	/**
	 * The public-facing functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/public
	 * @author     Robert Wilde <webdev@wildetech.com.au>
	 */
	class Gmap_Zones_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    0.1.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    0.1.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;

		/** @var  db table name */
		private $table_name;

		private $kml_folder;
		private $kml_folderDIR;
		private $kml_folderURL;

		public $geo_code;

		public $shipping_cookie;
		public $shipping_poa;

		/** @var  OBJECT wpdb connection */
		private $wpdb_kml;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    0.1.0
		 *
		 * @param      string $plugin_name The name of the plugin.
		 * @param      string $version The version of this plugin.
		 */
		public function __construct( $plugin_name, $version, $table_name, $wpdb_kml ) {

			$this->plugin_name   = $plugin_name;
			$this->version       = $version;
			$this->table_name    = $table_name;
			$this->wpdb_kml      = $wpdb_kml;
			$this->kml_folder    = '/gmapz_kmls/';
			$upload_dir          = wp_upload_dir();
			$this->kml_folderURL = $upload_dir[ 'baseurl' ] . $this->kml_folder;
		}

		public function get_cookie( $cookie_name = 'chef_poa_cookie' ) {

			$this->shipping_cookie = isset( $_COOKIE[ $cookie_name ] )
				? $_COOKIE[ $cookie_name ]
				: 'not set';
		}

		public function delete_cookie( $cookie_name = 'chef_poa_cookie' ) {

			setcookie( $cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}

		/**
		 * return the path to the upload folder
		 * @return string
		 */
		public function upload_folder() {

			$upload     = wp_upload_dir();
			$upload_dir = $upload[ 'basedir' ];
			$upload_dir = $upload_dir . $this->kml_folder;

			return $upload_dir;
		}

		/**
		 * return list of files saved in the uploads folder.
		 * @return array
		 */
		public function file_list() {

			$kmlFile  = scandir( $this->upload_folder() );
			$kmls     = array ();
			$kmlFiles = scandir( $this->upload_folder() );
			unset( $kmlFiles[ 0 ] );
			unset( $kmlFiles[ 1 ] );
			foreach ( $kmlFiles as $kmlFile ) {
				$kmls[] = "'" . $this->kml_folderURL . $kmlFile . "'";
			}

			return $kmls;
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    0.1.0
		 */
		public function enqueue_styles() {

			wp_enqueue_style(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'css/gmap-zones-public.css',
				array (),
				$this->version, 'all' );
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    0.1.0
		 */
		public function enqueue_scripts() {

			wp_enqueue_script( 'jquery-cookie', plugin_dir_url( __FILE__ ) . 'js/jquery.cookie.js', array ( 'jquery' ), '1.4.1', TRUE );
			wp_enqueue_script( 'gmapz_geoxml3_js', plugin_dir_url( __FILE__ ) . 'js/geoxml3.js', array (), '3', FALSE );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gmap-zones-public.js', array ( 'jquery' ), $this->version, FALSE );
			wp_localize_script( 'frontend-ajax', 'frontendajax', array ( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}

		/**
		 * Not really sure
		 */
		public function gmapz_modal() {

			require_once plugin_dir_path( __FILE__ ) . 'partials/gmap-zones-map-api.php';
			require_once plugin_dir_path( __FILE__ ) . 'partials/gmap-zones-public-display.php';
		}

		/**
		 * Code required for the AJAX response in the modal
		 */
		public function ajax_get_post_code() {

			if ( ! empty( $_POST[ 'post_code' ] ) ) {
				$postcode           = $_POST[ 'post_code' ];
				$this->shipping_poa = $postcode;
				$zone_name          = array ( 'zoneName' => 0, 'islands' => 0 );
				$island_names       = array ( 'islands' => 0 );;
				if ( gmapz_restricted_postcodes( $postcode ) != NULL ) {
					$island_names = gmapz_restricted_postcodes( $postcode );
				}
				$kml_files = $this->wpdb_kml->get_all();
				foreach ( $kml_files as $kml_file ) {
					$postcodes = json_decode( $kml_file->postal_codes );
					if ( in_array( $postcode, $postcodes ) ) {
						$zone_name = array (
							'zoneName' => $kml_file->zone_name,
							'islands'  => $island_names
						);
					}
				}
				$this->wc_shipping_calc();
				echo json_encode( $zone_name );
			}
			die();
		}

		/**
		 * will insert the postcode entered into the map, into the billing and shipping fields
		 * and complete the shipping calculator
		 *
		 * @param $postcode
		 */
		public function wc_shipping_calc() {

			$billing  = WC()->customer->get_postcode();
			$shipping = WC()->customer->get_shipping_postcode();
			WC()->customer->set_location( 'AU', 'QLD', $this->shipping_poa );
			WC()->customer->set_shipping_location( 'AU', 'QLD', $this->shipping_poa );
		}
	}
