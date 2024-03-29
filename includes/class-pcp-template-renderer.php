<?php //Added Docblock after below guard condition. // @codingStandardsIgnoreLine.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Renders the templates
 *
 * Allows to render templates created inside `templates` directory
 *
 * @since      1.0.0
 * @package    PCP
 * @subpackage PCP/includes/template_renderer
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Template_Renderer {
	/**
	 * Render Templates
	 *
	 * @access public
	 * @param mixed   $template_name Template file to render.
	 * @param array   $args Variables to make available inside template file.
	 * @param boolean $load Print or return the template output.
	 * @param string  $template_path Directory to search for template.
	 * @param string  $default_path Fallback directory to search for template if not found at $template_path.
	 * @return void
	 */
	public static function render( $template_name, $args = array(), $load = true, $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine.
		}

		$located = self::locate_template( $template_name, $template_path, $default_path );
		if ( false == $located ) {
			return;
		}

		ob_start();
		do_action( 'pcp_before_template_render', $template_name, $template_path, $located, $args );
		include( $located );
		do_action( 'pcp_after_template_render', $template_name, $template_path, $located, $args );
		$output = ob_get_clean();

		if ( ! apply_filters( 'pcp_load_template', $load, $template_name, $args ) ) {
				return $output;
		}

		// Not escaping below line because template code itself should take care of escaping wherever needed.
		echo $output; // @codingStandardsIgnoreLine.
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
	 * @param mixed  $template_name Template file to locate.
	 * @param string $template_path $template_path Directory to search for template.
	 * @param string $default_path Fallback directory to search for template if not found at $template_path.
	 * @return string
	 */
	private static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'pcp-templates/';
		}
		if ( ! $default_path ) {
			$default_path = PCP::get_plugin_path() . 'templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		if ( file_exists( $template ) ) {
			// Return what we found.
			return apply_filters( 'pcp_locate_template', $template, $template_name, $template_path );
		} else {
			return false;
		}
	}
}
