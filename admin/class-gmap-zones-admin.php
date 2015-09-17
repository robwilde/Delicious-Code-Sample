<?php

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       http://www.wildetech.com.au
	 * @since      0.1.0
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/admin
	 */

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/admin
	 * @author     Robert Wilde <webdev@wildetech.com.au>
	 */
	class Gmap_Zones_Admin {

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
		private $gmapz_admin_url;

		private $kml_folder;
		private $kml_folderDIR;
		private $kml_folderURL;
		private $kml_settings;

		public $zone_name = '';
		public $zone_slug = '';
		public $line_color = '';
		public $fill_color = '';
		public $post_codes = '';

		private $table_name;
		private $wpdb_table;


		/*-------------------------------------------------------------------------------
		    Initialize the class and set its properties.
		-------------------------------------------------------------------------------*/
		/**
		 * @since      0.1.0
		 *
		 * @param      string $plugin_name The name of this plugin.
		 * @param      string $version The version of this plugin.
		 */
		public function __construct( $plugin_name, $version, $table_name, $wpdb_table ) {
			$this->plugin_name = $plugin_name;
			$this->version     = $version;
			$this->table_name  = $table_name;
			$this->wpdb_table  = $wpdb_table;

			$this->kml_folder      = '/gmapz_kmls/';
			$this->gmapz_admin_url = admin_url( 'options-general.php?page=gmap-zones' );

			$upload_dir          = wp_upload_dir();
			$this->kml_folderURL = $upload_dir['baseurl'] . $this->kml_folder;
		}


		/*-------------------------------------------------------------------------------
		    create the KML file and create row in the db
		-------------------------------------------------------------------------------*/
		/**
		 * @param $form_fields
		 */
		public function create_kml_file( $form_fields ) {
			/** The class responsible for connecting to the postcode DB and creating the KML files  */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-kml-creator.php';

			// Grab the Zone Name, replace space with dash (-) and convert to all lower case, then add the upload folder path
			switch ( $form_fields['post_type'] ) {
				case 'insert':

					$form_fields['zone_slug'] = preg_replace( '/[[:space:]]+/', '-', strtolower( $form_fields['zone_name'] ) );

					$kml_Class = new KML_Creator( $form_fields );
					$kml_Class->buildKML()->asXML( $this->upload_folder() . $form_fields['zone_slug'] . '.kml' );

					$this->insert_kml_settings( $form_fields );
					break;
				case 'update':

					$file = $form_fields['zone_slug'] . '.kml';
					$this->delete_file( $file );

					$form_fields['zone_slug'] = preg_replace( '/[[:space:]]+/', '-', strtolower( $form_fields['zone_name'] ) );

					$kml_Class = new KML_Creator( $form_fields );
					$kml_Class->buildKML()->asXML( $this->upload_folder() . $form_fields['zone_slug'] . '.kml' );

					$this->insert_kml_settings( $form_fields );
					break;
			}

		}


		/*-------------------------------------------------------------------------------
		    inserting the form settings fields into the DB
		-------------------------------------------------------------------------------*/
		/**
		 * @param $form_fields
		 */
		public function insert_kml_settings( $form_fields ) {
			$postcodes     = explode( ' ', $form_fields['post_codes'] );
			$insert_result = $this->wpdb_table->insert(
				array(
					'zone_slug'    => $form_fields['zone_slug'],
					'zone_name'    => $form_fields['zone_name'],
					'line_color'   => $form_fields['line_color'],
					'fill_color'   => $form_fields['fill_color'],
					'postal_codes' => json_encode( $postcodes )
				) );

		}


		/*-------------------------------------------------------------------------------
		    return list of files saved in the uploads folder.
		-------------------------------------------------------------------------------*/
		/**
		 * @return array
		 */
		public function file_list() {
			$kmlFile = scandir( $this->upload_folder() );
			$kmls    = array();

			if ( isset( $_GET['fileName'] ) ) {
				$kmls[]    = "'" . $this->kml_folderURL . $_GET['fileName'] . "'";
				$zone_slug = $this->remove_ext( $_GET['fileName'] );

				$this->kml_settings = $this->wpdb_table->get_by( array( 'zone_slug' => $zone_slug ) );

				if ( ! empty( $this->kml_settings ) ) {
					$this->get_kml_settings( $this->kml_settings[0] );
				}
			} else {
				$kmlFiles = scandir( $this->upload_folder() );

				unset( $kmlFiles[0] );
				unset( $kmlFiles[1] );

				foreach ( $kmlFiles as $kmlFile ) {
					$kmls[] = "'" . $this->kml_folderURL . $kmlFile . "'";
				}
			}

			return $kmls;
		}


		public function update_kml_settings( $form_fields ) {
		}


		/*-------------------------------------------------------------------------------
		    delete file from the uploads folder
		-------------------------------------------------------------------------------*/
		/**
		 * @param $file
		 */
		public function delete_file( $file, $is_update = false ) {
			$folder = $this->upload_folder();
			$delete = apply_filters( 'wp_delete_file', $folder . $file );
			if ( ! empty( $delete ) ) {
				@unlink( $delete );
			}

			// delete the settings from the DB if not an update
			if ( $is_update == false ) {
				$this->delete_kml_settings( $file );
			}
		}


		public function delete_kml_settings( $file ) {
			$zone_slug     = $this->remove_ext( $file );
			$delete_result = $this->wpdb_table->delete( array( 'zone_slug' => $zone_slug ) );

		}


		/*-------------------------------------------------------------------------------
		    retrieve the details from the DB for the KML settings form
		-------------------------------------------------------------------------------*/
		/**
		 * @param $kml_settings
		 */
		public function get_kml_settings( $kml_settings ) {
			$this->zone_name = $kml_settings->zone_name;
			$this->zone_slug = $kml_settings->zone_slug;

			// reverse the hex for the line color and remove the alpha from the begging
			$line_color_array = $this->invert_google_colors( $kml_settings->line_color );
			$this->line_color = '#' . $line_color_array['rgb'];

			// convert the hex value for alpha and convert the BGR to RGB value
			$fill_color_array = $this->invert_google_colors( $kml_settings->fill_color );
			$this->fill_color = $this->hex2rgba( $fill_color_array['rgb'], $fill_color_array['alpha'] );

			$postal_codes_array = json_decode( $kml_settings->postal_codes );
			foreach ( $postal_codes_array as $postal_code ) {
				$this->post_codes = ( empty( $this->post_codes ) )
					? $postal_code
					: $this->post_codes . ' ' . $postal_code;
			}
		}

		/*-------------------------------------------------------------------------------
		    separate the hex value into RGB and remove the # tag
		-------------------------------------------------------------------------------*/
		/**
		 * @param $string
		 * @param $start
		 * @param $end
		 *
		 * @return string
		 */
		public function get_string_between( $string, $start, $end ) {
			$string = " " . $string;
			$ini    = strpos( $string, $start );
			if ( $ini == 0 ) {
				return "";
			}
			$ini += strlen( $start );
			$len = strpos( $string, $end, $ini ) - $ini;

			return substr( $string, $ini, $len );
		}


		/** convert the kml color into rgb or hex */
		public function invert_google_colors( $google_color ) {
			$hex_color          = array();
			$color_array        = str_split( $google_color, 2 );
			$alpha              = hexdec( $color_array[0] );
			$hex_color['alpha'] = round( ( $alpha / 255 ), 2 );

			if ( array_key_exists( 3, $color_array ) ) {
				$hex_color['rgb'] = $color_array[3] . $color_array[2] . $color_array[1];
			}

			return $hex_color;
		}


		/*-------------------------------------------------------------------------------
		    invert the colours from hex to required format for the KML files and prepend the alpha
		-------------------------------------------------------------------------------*/
		/**
		 * @param $color
		 *
		 * @return string
		 */
		public function google_colors( $color ) {
			$google_color = null;
			//grab the first character and see if hex
			if ( substr( $color, 0, 1 ) == "#" ) {
				$color     = ltrim( $color, '#' );
				$firstTwo  = substr( $color, 0, 2 );
				$middleTwo = substr( $color, 2, 2 );
				$endTwo    = substr( $color, 4, 2 );

				// return the inverted hex value and add the opacity
				$google_color = 'ff' . $endTwo . $middleTwo . $firstTwo;
			} elseif ( substr( $color, 0, 4 ) == "rgba" ) {
				$color_values = $this->get_string_between( $color, 'rgba(', ')' );
				$color_array  = explode( ',', $color_values );

				$count = count( $color_array );
				for ( $i = 0; $i < $count; $i ++ ) {
					// convert to hex
					$color_array[ $i ] = ( $i == 3 ) ? dechex( $color_array[ $i ] * 255 ) : dechex( $color_array[ $i ] );
					// add leading 0 if not exist
					if ( strlen( $color_array[ $i ] ) < 2 ) {
						$color_array[ $i ] = '0' . $color_array[ $i ];
					}
				}
				// create the google maps BGR format with leading alpha
				$google_color = $color_array[3] . $color_array[2] . $color_array[1] . $color_array[0];
			}

			return $google_color;
		}

		/*-------------------------------------------------------------------------------
		    convert the hex number to RGBA
		-------------------------------------------------------------------------------*/
		public function hex2rgba( $color, $opacity = false ) {
			$default = 'rgb(0,0,0)';

			//Return default if no color provided
			if ( empty( $color ) ) {
				return $default;
			}

			//Sanitize $color if "#" is provided
			if ( $color[0] == '#' ) {
				$color = substr( $color, 1 );
			}

			//Check if color has 6 or 3 characters and get values
			if ( strlen( $color ) == 6 ) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
			} elseif ( strlen( $color ) == 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
			} else {
				return $default;
			}

			//Convert hexadec to rgb
			$rgb = array_map( 'hexdec', $hex );

			//Check if opacity is set(rgba or rgb)
			if ( $opacity ) {
				if ( abs( $opacity ) > 1 ) {
					$opacity = 1.0;
				}
				$output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
			} else {
				$output = 'rgb(' . implode( ",", $rgb ) . ')';
			}

			//Return rgb(a) color string
			return $output;
		}


		/*-------------------------------------------------------------------------------
		    return the path to the upload folder
		-------------------------------------------------------------------------------*/
		/**
		 * @return string
		 */
		public function upload_folder() {
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = $upload_dir . $this->kml_folder;

			return $upload_dir;
		}


		/*-------------------------------------------------------------------------------
		    remove the extension from file name
		-------------------------------------------------------------------------------*/
		/**
		 *
		 * @param $filename
		 *
		 * @return mixed
		 */
		public function remove_ext( $filename ) {
			$name = pathinfo( $filename )['filename'];

			return $name;
		}


		/*-------------------------------------------------------------------------------
		    Register the stylesheets for the admin area.
		-------------------------------------------------------------------------------*/
		/**
		 * @since    0.1.0
		 */
		public function enqueue_styles( $hook ) {
			if ( 'settings_page_gmap-zones' != $hook ) {
				return;
			}

			wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), '3.3.4', 'all' );
			wp_enqueue_style( 'bootstrap-colorpicker', plugin_dir_url( __FILE__ ) . 'css/bootstrap-colorpicker.css', array(), '2.0', 'all' );

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gmap-zones-admin.css', array(), $this->version, 'all' );
		}


		/*-------------------------------------------------------------------------------
		    Register the JavaScript for the admin area.
		-------------------------------------------------------------------------------*/
		/**
		 *
		 * @since    0.1.0
		 */
		public function enqueue_scripts( $hook ) {
			if ( 'settings_page_gmap-zones' != $hook ) {
				return;
			}

			wp_enqueue_script( 'gmapz_geoxml3_js', plugin_dir_url( __FILE__ ) . 'js/geoxml3.js', array(), '3', false );

			wp_enqueue_script( 'gmapz_bootstrap_js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), '3.4.4', false );
			wp_enqueue_script( 'gmapz_bootstrap_colorpicker_js', plugin_dir_url( __FILE__ ) . 'js/bootstrap-colorpicker.min.js', array( 'jquery' ), '3.4.4', false );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gmap-zones-admin.js', array( 'jquery' ), $this->version, false );
		}


		/*-------------------------------------------------------------------------------
		    Create settings page options
		-------------------------------------------------------------------------------*/
		public function settings_page() {
			add_options_page(
				'Google Map Zones Plugin',
				'gMap Zones',
				'manage_options',
				'gmap-zones',
				array( $this, 'admin_display' )
			);
		}


		/*-------------------------------------------------------------------------------
		    include admin display file
		-------------------------------------------------------------------------------*/
		public function admin_display() {
			require_once( plugin_dir_path( __FILE__ ) . 'partials/gmap-zones-admin-display.php' );
		}
	}
