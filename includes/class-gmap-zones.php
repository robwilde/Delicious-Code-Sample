<?php

	/**
	 * The file that defines the core plugin class
	 *
	 * A class definition that includes attributes and functions used across both the
	 * public-facing side of the site and the admin area.
	 *
	 * @link       http://www.wildetech.com.au
	 * @since      0.1.0
	 *
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/includes
	 */

	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      0.1.0
	 * @package    Gmap_Zones
	 * @subpackage Gmap_Zones/includes
	 * @author     Robert Wilde <webdev@wildetech.com.au>
	 */
	class Gmap_Zones {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    0.1.0
		 * @access   protected
		 * @var      Gmap_Zones_Loader $loader Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    0.1.0
		 * @access   protected
		 * @var      string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    0.1.0
		 * @access   protected
		 * @var      string $version The current version of the plugin.
		 */
		protected $version;

		protected $table_name;
		protected $wpdb_table;


		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    0.1.0
		 */
		public function __construct() {
			$this->plugin_name = 'gmap-zones';
			$this->version     = '0.7.7';

			$this->table_name = 'gmz_kml';

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}


		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Gmap_Zones_Loader. Orchestrates the hooks of the plugin.
		 * - Gmap_Zones_i18n. Defines internationalization functionality.
		 * - Gmap_Zones_Admin. Defines all hooks for the admin area.
		 * - Gmap_Zones_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    0.1.0
		 * @access   private
		 */
		private function load_dependencies() {
			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gmap-zones-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gmap-zones-i18n.php';

			/** WP DB CRUD */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database/WP_GMZ_KML.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gmap-zones-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gmap-zones-public.php';

			$this->loader = new Gmap_Zones_Loader();

			global $wpdb;
			$this->wpdb_table = new WP_GMZ_KML(
				$wpdb->prefix . $this->table_name
			);
		}


		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Gmap_Zones_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    0.1.0
		 * @access   private
		 */
		private function set_locale() {
			$plugin_i18n = new Gmap_Zones_i18n();
			$plugin_i18n->set_domain( $this->get_plugin_name() );

			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		}


		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    0.1.0
		 * @access   private
		 */
		private function define_admin_hooks() {
			$plugin_admin = new Gmap_Zones_Admin(
				$this->get_plugin_name(),
				$this->get_version(),
				$this->get_table_name(),
				$this->get_wpdb()
			);

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

			$this->loader->add_action( 'admin_menu', $plugin_admin, 'settings_page' );
		}


		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    0.1.0
		 * @access   private
		 */
		private function define_public_hooks() {
			$plugin_public = new Gmap_Zones_Public(
				$this->get_plugin_name(),
				$this->get_version(),
				$this->get_table_name(),
				$this->get_wpdb()
			);

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

			$this->loader->add_action( 'wp_footer', $plugin_public, 'gmapz_modal' );

			$this->loader->add_action( 'wp_ajax_get_post_code', $plugin_public, 'ajax_get_post_code' );
			$this->loader->add_action( 'wp_ajax_nopriv_get_post_code', $plugin_public, 'ajax_get_post_code' );
		}


		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    0.1.0
		 */
		public function run() {
			$this->loader->run();
		}


		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     0.1.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}


		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     0.1.0
		 * @return    Gmap_Zones_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}


		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     0.1.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}


		public function get_table_name() {
			return $this->table_name;
		}


		public function get_wpdb() {
			return $this->wpdb_table;
		}
	}
