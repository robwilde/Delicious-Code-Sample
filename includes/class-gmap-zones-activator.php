<?php

	/**
	 * Fired during plugin activation
	 *
	 * @link       http://www.wildetech.com.au
	 * @since      0.1.0
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/includes
	 */

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      0.1.0
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/includes
	 * @author     Robert Wilde <webdev@wildetech.com.au>
	 */
	class Gmap_Zones_Activator {

		const KML_FOLDER = 'gmapz_kmls';

		/**
		 * build the required folder.
		 *
		 * Create a folder in the upload directory for saving the KML files to display.
		 *
		 * @since    0.1.0
		 */
		public static function activate() {
			$upload_dir = self::gmz_upload_folder();
			if ( ! is_dir( $upload_dir ) ) {
				mkdir( $upload_dir, 0755 );
			}
			self::gmz_wpdb_install();
		}

		public static function gmz_upload_folder() {
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = $upload_dir . '/' . self::KML_FOLDER . '/';

			return $upload_dir;
		}

		public static function gmz_wpdb_install() {
			global $wpdb;

			$table_name      = $wpdb->prefix . "gmz_kml";
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
		    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
		    zone_slug VARCHAR (100) NOT NULL ,
		    zone_name VARCHAR(100) NOT NULL,
		    line_color VARCHAR(20) NOT NULL,
		    fill_color VARCHAR(20) NOT NULL,
		    postal_codes VARCHAR(5000) NOT NULL);
				CREATE UNIQUE INDEX unique_id ON $table_name (id);
				CREATE UNIQUE INDEX unique_zone_name ON $table_name (zone_name);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

	}
