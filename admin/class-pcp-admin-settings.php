<?php

/**
 * Loads Settings Page of PCP Plugin
 *
 * @link       https://sumitpore.in
 * @since      1.0.0
 *
 * @package    PCP
 * @subpackage PCP/admin
 */

/**
 * Loads Settings Page of PCP Plugin
 *
 * @package    PCP
 * @subpackage PCP/settings
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Admin_Settings {

	private $pcp_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	public function add_plugin_page() {
		add_options_page(
			'Primary Cat for Posts', // page_title
			'Primary Cat for Posts', // menu_title
			'manage_options', // capability
			'primary-cat-for-posts', // menu_slug
			array( $this, 'admin_page' )
		);
	}

	public function admin_page() {
		$this->pcp_options = PCP::settings(); 
		PCP_Template_Renderer::render('admin/settings-page.php');		
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
			'primary-cat-for-posts' // page
		);

		add_settings_field(
			'enabled_post_types', // id
			'Enable for Post Types', // title
			array( $this, 'render_enable_for_post_types_setting' ), // callback
			'primary-cat-for-posts', // page
			'primary_cat_for_posts_setting_section' // section
		);
	}

	public function sanitize_input( $input ) {
		$sanitary_values = array();
		if ( isset( $input['enabled_post_types'] ) ) {
			$sanitary_values['enabled_post_types'] = $input['enabled_post_types'];
		}

		return $sanitary_values;
	}

	public function render_enable_for_post_types_setting() {
		$post_types = get_post_types(
			[
				'public' => true,
				'show_ui' => true,
				'publicly_queryable' => true,
			],
			'names',
			'and'
		);

		PCP_Template_Renderer::render('admin/enabled-post-types-setting.php', [
			'post_types' => $post_types, 
			'enabled_post_types' => isset($this->pcp_options['enabled_post_types']) ? $this->pcp_options['enabled_post_types'] : []
			]
		);
	}

}
