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
		add_action( 'admin_menu', array( $this, 'add_plugin_page') );
		add_action( 'admin_init', array( $this, 'init') );
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
		$this->pcp_options = get_option( 'pcp_options' ); ?>

		<div class="wrap">
			<h2>Primary Cat for Posts</h2>
			<p>Some sub text here</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'pcp_options_group' );
					do_settings_sections( 'primary-cat-for-posts-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function init() {
		register_setting(
			'pcp_options_group', // option_group
			'pcp_options', // option_name
			array( $this, 'sanitize_input' ) // sanitize_callback
		);

		add_settings_section(
			'primary_cat_for_posts_setting_section', // id
			'Settings', // title
			array( $this, 'section_info' ), // callback
			'primary-cat-for-posts-admin' // page
		);

		add_settings_field(
			'enable_for_post_types', // id
			'Enable for Post Types', // title
			array( $this, 'render_enable_for_post_types_setting' ), // callback
			'primary-cat-for-posts-admin', // page
			'primary_cat_for_posts_setting_section' // section
		);
	}

	public function sanitize_input($input) {
		$sanitary_values = array();
		if ( isset( $input['enable_for_post_types'] ) ) {
			$sanitary_values['enable_for_post_types'] = $input['enable_for_post_types'];
		}

		return $sanitary_values;
	}

	public function section_info() {
		echo 'SECTION INFO';
	}

	public function render_enable_for_post_types_setting() {
		?> <select name="pcp_options[enable_for_post_types]" id="enable_for_post_types">
			<?php $selected = (isset( $this->pcp_options['enable_for_post_types'] ) && $this->pcp_options['enable_for_post_types'] === 'option-one') ? 'selected' : '' ; ?>
			<option value="option-one" <?php echo $selected; ?>>Option One</option>
		</select> <?php
	}

}
