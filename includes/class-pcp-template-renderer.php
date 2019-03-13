<?php

class PCP_Template_Renderer {
	/**
	 * Render Templates
	 *
	 * @access public
	 * @param mixed  $template_name
	 * @param array  $args (default: array())
	 * @param string $template_path (default: '')
	 * @param string $default_path (default: '')
	 * @return void
	 */
	public static function render( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$located = self::locate_template( $template_name, $template_path, $default_path );
		if ( false != $located ) {
			do_action( 'pcp_before_template_render', $template_name, $template_path, $located, $args );
			include( $located );
			do_action( 'pcp_after_template_render', $template_name, $template_path, $located, $args );
		}
		return $located;
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *      yourtheme       /   $template_path  /   $template_name
	 *      yourtheme       /   $template_name
	 *      $default_path   /   $template_name
	 *
	 * @access public
	 * @param mixed  $template_name
	 * @param string $template_path (default: '')
	 * @param string $default_path (default: '')
	 * @return string
	 */
	private static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'templates/';
		}
		if ( ! $default_path ) {
			$default_path = PCP::get_plugin_path() . 'templates/';
		}

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		if ( file_exists( $template ) ) {
			// Return what we found
			return apply_filters( 'pcp_locate_template', $template, $template_name, $template_path );
		} else {
			return false;
		}
	}
}
