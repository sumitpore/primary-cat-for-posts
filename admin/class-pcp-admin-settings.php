<?php
/**
 * Loads Settings Page of PCP Plugin
 *
 * @package    PCP
 * @subpackage PCP/settings
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Admin_Settings {

	private $pcp_options;
	private $page_slug = 'primary-cat-for-posts';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Add plugin's options page
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function add_plugin_page() {
		add_options_page(
			'Primary Cat for Posts', // page_title
			'Primary Cat for Posts', // menu_title
			'manage_options', // capability
			$this->page_slug, // menu_slug
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Render the settings page
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function admin_page() {
		$this->pcp_options = PCP::settings();
		PCP_Template_Renderer::render( 'admin/settings-page.php' );
	}

	public function init() {
		register_setting(
			'pcp_options_group', // option_group
			'pcp_options', // option_name
			array( $this, 'sanitize_input' ) // sanitize_callback
		);

		add_settings_section(
			'primary_cat_for_posts_setting_section', // id
			'General', // title
			'__return_false', // callback
			$this->page_slug // page
		);

		add_settings_field(
			'enabled_taxonomies', // id
			'Enable Taxonomies', // title
			array( $this, 'render_enable_taxonomies_setting' ), // callback
			$this->page_slug, // page
			'primary_cat_for_posts_setting_section' // section
		);
	}

	public function sanitize_input( $input ) {
		$sanitary_values = array();
		if ( isset( $input['enabled_taxonomies'] ) ) {
			$sanitary_values['enabled_taxonomies'] = $input['enabled_taxonomies'];
		}

		return $sanitary_values;
	}

	/**
	 * Show all taxonomies
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public function render_enable_taxonomies_setting() {
		PCP_Template_Renderer::render(
			'admin/enabled-taxonomies-setting.php', [
				'taxonomies' => PCP_Admin::get_all_taxonomies(),
				'enabled_taxonomies' => isset( $this->pcp_options['enabled_taxonomies'] ) ? $this->pcp_options['enabled_taxonomies'] : [],
			]
		);
	}

}
