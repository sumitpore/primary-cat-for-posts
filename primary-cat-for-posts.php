<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sumitpore.in
 * @since             1.0.0
 * @package           PCP
 *
 * @wordpress-plugin
 * Plugin Name:       Primary Category for Posts and Custom Post Types
 * Description:       Makes it mandatory for post editor to set Primary category for the post being edited
 * Version:           1.0.0
 * Author:            Sumit P
 * Author URI:        https://sumitpore.in
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       primary-cat-for-posts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRIMARY_CAT_FOR_POSTS_VERSION', '1.0.0' );
define( 'PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN', 'primary-cat-for-posts' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pcp.php';

/**
 * Main instance of PCP class.
 *
 * Returns the main instance of PCP to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PCP
 */
function PCP() { // @codingStandardsIgnoreLine
	return PCP::instance();
}

$GLOBALS['pcp'] = PCP();
