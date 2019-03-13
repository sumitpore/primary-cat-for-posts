<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sumitpore.in
 * @since      1.0.0
 *
 * @package    PCP
 * @subpackage PCP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version. This is an entry point for Frontend 
 * related functionality
 *
 * @package    PCP
 * @subpackage PCP/public
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

}
