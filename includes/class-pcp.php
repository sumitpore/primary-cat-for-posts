<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sumitpore.in
 * @since      1.0.0
 *
 * @package    PCP
 * @subpackage PCP/includes
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
 * @since      1.0.0
 * @package    PCP
 * @subpackage PCP/includes
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Main plugin path /wp-content/plugins/<plugin-folder>/.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_path    Main path.
	 */
	private static $plugin_path = null;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'PRIMARY_CAT_FOR_POSTS_VERSION' ) ) {
			$this->version = PRIMARY_CAT_FOR_POSTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'primary-cat-for-posts';

		$this->load_dependencies();
		$this->set_locale();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - PCP_i18n. Defines internationalization functionality.
	 * - PCP_Admin. Defines modules related to Admin functionality
	 * - PCP_Public. Defines modules related to Frontend functionality
	 *
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		spl_autoload_register(array($this, 'autoloader'));
		$this->load_common_dependencies();
		add_action( 'init', array($this, 'load_admin_dependencies') );
		add_action( 'init', array( $this, 'load_public_dependencies') );
	}

	/**
	 * Loads classes present in admin and public directories
	 *
	 * @return void
	 */
	public function autoloader( $class_name ){

		if ( false === strpos( $class_name, 'PCP' ) ) {
			return;
		}

		$sub_dir = 'public';

		if ( false !== strpos( $class_name, 'PCP_Admin' ) ) {
			$sub_dir = 'admin';
		}

		$classes_dir = realpath( plugin_dir_path( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . $sub_dir . DIRECTORY_SEPARATOR;
		
		$class_file = 'class-' . str_replace( '_', '-', strtolower($class_name) ) . '.php';

		require_once $classes_dir . $class_file;
	}
	/**
	 * Returns true if a current page request belongs to WordPress dashboard
	 *
	 * @return boolean
	 */
	private function is_admin_request(){
		return is_admin();
	}

	/**
	 * Returns true if a current page request belongs to frontend
	 *
	 * @return boolean
	 */
	private function is_public_request(){
		return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
	}

	public function load_common_dependencies(){
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pcp-i18n.php';
	}

	public function load_admin_dependencies(){

		if( ! $this->is_admin_request() ){
			return;
		}

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-pcp-admin.php';

		$this->load_admin_module();
	}

	public function load_public_dependencies(){

		if( ! $this->is_public_request() ) {
			return;
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-pcp-public.php';

		$this->load_public_module();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the PCP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new PCP_i18n();

		add_action( 'plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain') );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_admin_module() {

		$this->admin = new PCP_Admin( $this->get_plugin_name(), $this->get_version());

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_public_module() {

		$plugin_public = new PCP_Public( $this->get_plugin_name(), $this->get_version());

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Returns path of the Plugin's directory
	 *
	 * @return void
	 */
	public static function get_plugin_path() {

		if( self::$plugin_path === null ){
			self::$plugin_path = plugin_dir_path( dirname( __FILE__ ) );
		}
		return self::$plugin_path;

	}
}
