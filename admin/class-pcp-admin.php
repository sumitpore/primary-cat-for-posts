<?php
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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->settings = new PCP_Admin_Settings();
		$this->edit_post = new PCP_Admin_Edit_Post();
	}

	/**
	 * Get all taxonomies we are interseted in
	 *
	 * @access public
	 * @return array
	 * @since  1.0.0
	 */
	public static function get_all_taxonomies() {
		$args = array(
			'public'   => true,
			'show_ui'  => true,
			'_builtin' => true,
		);

		$defaults = get_taxonomies( $args, 'objects' );

		// Remove Post tag from default taxonomies list
		if ( isset( $defaults['post_tag'] ) ) {
			unset( $defaults['post_tag'] );
		}

		$args['_builtin'] = false;
		$custom = get_taxonomies( $args, 'objects' );

		// Remove tag like custom taxonomies
		$filtered_custom_taxonomies = array_filter(
			$custom,
			function( $taxonomy ) {
				return strpos( $taxonomy, '_tag' ) === false;
			},
			ARRAY_FILTER_USE_KEY
		);

		$taxonomies = array_merge( $defaults, $filtered_custom_taxonomies );
		ksort( $taxonomies );
		return $taxonomies;
	}

}
