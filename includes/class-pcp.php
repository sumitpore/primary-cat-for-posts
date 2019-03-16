<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
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
final class PCP {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * Holds the instance of main admin class
	 *
	 * @var PCP_Admin
	 * @since 1.0.0
	 */
	private $admin;


	/**
	 * Holds the instance of main public class
	 *
	 * @var PCP_Public
	 * @since 1.0.0
	 */
	private $public;

	/**
	 * Main plugin path /wp-content/plugins/<plugin-folder>/.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_path    Main path.
	 */
	private static $plugin_path = null;

	/**
	 * The single instance of the class.
	 *
	 * @var PCP
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main PCP Instance.
	 *
	 * Ensures only one instance of PCP is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see PCP()
	 * @return PCP - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ), PRIMARY_CAT_FOR_POSTS_VERSION );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ), PRIMARY_CAT_FOR_POSTS_VERSION );
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	private function __construct() {
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
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		spl_autoload_register( array( $this, 'autoloader' ) );
		$this->load_common_dependencies();
		add_action( 'init', array( $this, 'load_admin_dependencies' ) );
		add_action( 'init', array( $this, 'load_public_dependencies' ) );
	}

	/**
	 * Loads classes present in admin and public directories
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return void
	 */
	public function autoloader( $class_name ) {
		if ( false === strpos( $class_name, 'PCP' ) ) {
			return;
		}

		$sub_dir = 'public';

		if ( false !== strpos( $class_name, 'PCP_Admin' ) ) {
			$sub_dir = 'admin';
		}

		$classes_dir = realpath( plugin_dir_path( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . $sub_dir . DIRECTORY_SEPARATOR;

		$class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';

		require_once $classes_dir . $class_file;
	}

	/**
	 * Returns true if a current page request belongs to WordPress dashboard
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return boolean
	 */
	private function is_admin_request() {
		return is_admin();
	}

	/**
	 * Returns true if a current page request belongs to frontend
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return boolean
	 */
	private function is_public_request() {
		return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
	}

	/**
	 * Loads modules which are always required
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return void
	 */
	public function load_common_dependencies() {
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pcp-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pcp-template-renderer.php';
	}

	/**
	 * Loads main admin side class file
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return void
	 */
	public function load_admin_dependencies() {
		if ( ! $this->is_admin_request() ) {
			return;
		}

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-pcp-admin.php';

		$this->load_admin_module();
	}

	/**
	 * Loads main public side class file
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return void
	 */
	public function load_public_dependencies() {
		if ( ! $this->is_public_request() ) {
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
	 * @return   void
	 */
	private function set_locale() {
		$plugin_i18n = new PCP_i18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load main admin module
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_admin_module() {
		$this->admin = new PCP_Admin();
	}

	/**
	 * Load main public module
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_public_module() {
		$this->public = new PCP_Public();
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
	 * Returns path of the Plugin's directory
	 *
	 * @since     1.0.0
	 * @return void
	 */
	public static function get_plugin_path() {
		if ( null === self::$plugin_path ) {
			self::$plugin_path = plugin_dir_path( dirname( __FILE__ ) );
		}
		return self::$plugin_path;
	}

	/**
	 * Returns settings of the plugin
	 *
	 * @since     1.0.0
	 * @return void
	 */
	public static function settings() {
		static $settings = null;
		if ( null === $settings ) {
			$settings = get_option( 'pcp_options' );
		}
		return $settings;
	}
}
