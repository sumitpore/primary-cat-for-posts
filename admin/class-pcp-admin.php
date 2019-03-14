<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sumitpore.in
 * @since      1.0.0
 *
 * @package    PCP
 * @subpackage PCP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version. This is an entry point for Admin related
 * functionality
 *
 * @package    PCP
 * @subpackage PCP/admin
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->convert_tax_selection_to_radio_btns();
		$this->admin_settings = new PCP_Admin_Settings();

		// launch each taxonomy class when tax is registered
		// add_action( 'registered_taxonomy', array( $this, 'convert_tax_selection_to_radio_btns' ), 10, 1 );
	}

	public function convert_tax_selection_to_radio_btns(){

		$plugin_settings = PCP::settings();
		if( ! isset( $plugin_settings['enabled_taxonomies'] ) ) {
			return;
		}

	/**
	 * Get all taxonomies - for plugin options checklist
	 * 
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public static function get_all_taxonomies() {

		$args = array (
			'public'   => true,
			'show_ui'  => true,
			'_builtin' => true
		);

		$defaults = get_taxonomies( $args, 'objects' );

		// Remove Post tag from default taxonomies list
		if( isset( $defaults['post_tag'] ) ){
			unset( $defaults['post_tag'] );
		}

		$args['_builtin'] = false;
		$custom = get_taxonomies( $args, 'objects' );

		//Remove tag like custom taxonomies
		$filtered_custom_taxonomies = array_filter(
			$custom, 
			function($taxonomy){
				return strpos($taxonomy, '_tag') === false;
			}, 
			ARRAY_FILTER_USE_KEY
		);

		$taxonomies = array_merge( $defaults, $filtered_custom_taxonomies );
		ksort( $taxonomies );
		return $taxonomies;
	}

}
