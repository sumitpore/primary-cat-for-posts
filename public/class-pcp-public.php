<?php //Added Docblock after below guard condition. // @codingStandardsIgnoreLine.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version. This is an entry point for Frontend
 * related functionality
 *
 * @package    PCP
 * @subpackage PCP/public
 * @since      1.0.0
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Public {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->public_posts_listing = new PCP_Public_Posts_Listing();
	}

}
