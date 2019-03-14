<?php
/**
 * Handles Selection of Primary category on Edit Post page
 * 
 * @package    PCP
 * @subpackage PCP/edit_post
 * @author     Sumit P <sumit.pore@gmail.com>
 */
 class PCP_Admin_Edit_Post {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'save_post', array( $this, 'revert_to_draft_if_required_cats_are_not_selected' ), 999 );
        add_action( 'edit_form_top', array( $this, 'show_error_if_primary_cat_not_selected') );
        
        $this->initiate_radio_buttons_conversion();
    }

    /**
	 * Loads required scripts on Add new or Edit Post screen
	 *
	 * @access public
	 * @param string $hook
	 * @return void
	 * @since  1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		if( !in_array( $hook, array( 'post.php', 'post-new.php' ) ) ){
			return;
		}

		wp_enqueue_script( 'pcp-admin-edit-post', plugin_dir_url( __FILE__ ) . 'js/pcp-admin-edit-post.js', array( 'jquery' ), PRIMARY_CAT_FOR_POSTS_VERSION, true );
	}
    
    /**
	 * Display an error message if primary category is not selected
	 *
	 * @param WP_Post The current post object.
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	function show_error_if_primary_cat_not_selected( $post ) {

		if ( 'auto-draft' === get_post_status( $post ) ) {
            return;
        }

        $list_of_all_unselected_required_taxonomies = get_post_meta($post->ID, 'unselected_required_taxonomies', true);

        if( empty( $list_of_all_unselected_required_taxonomies ) ){
            return;
        }

        printf(
            '<div class="error below-h2"><p><b>%s %s</b></p></div>',
            esc_html__( 'Please set following required Categories: ', PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ),
            esc_html__( implode(', ', $list_of_all_unselected_required_taxonomies) )
        );

        // Delete the post meta after message is shown
        delete_post_meta($post->ID, 'unselected_required_taxonomies');
		
    }
    
    /**
	 * Initiates the process of converting selected taxonomies to radio buttons
	 * 
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function initiate_radio_buttons_conversion(){
		global $pagenow;
		
		// We want to load this only on Add New CPT/Edit CPT page
		if( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ){
			return;
		}

		$plugin_settings = PCP::settings();
		if( ! isset( $plugin_settings['enabled_taxonomies'] ) ) {
			return;
		}

		$taxonomies = array_keys( PCP_Admin::get_all_taxonomies() );
		if(! $taxonomies) {
			return;
		}
		
		foreach( $taxonomies as $taxonomy_name ){
			if ( ! in_array( $taxonomy_name, (array) $plugin_settings['enabled_taxonomies'] ) ) {
				continue;
			}
			new PCP_Admin_WP_Radio_Taxonomy( $taxonomy_name );
		} 

    }
    
    /**
     * Reverts post status to draft if all required cats are not selected
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function revert_to_draft_if_required_cats_are_not_selected( $post_id ){

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        $list_of_all_unselected_required_taxonomies = get_post_meta($post_id, 'unselected_required_taxonomies', true);

        // All required taxonomies are set
        if( empty( $list_of_all_unselected_required_taxonomies ) ){
            return;
        }

        remove_action( 'save_post', array($this, 'revert_to_draft_if_required_cats_are_not_selected'), 999 );

        $postdata = array(
            'ID'          => $post_id,
            'post_status' => 'draft',
        );

        wp_update_post( $postdata );

		//Remove category set by WP automatically when user does not select the category
		if( get_post_type($post_id) === 'post' ){
			$default_category = (int)get_option('default_category');
			wp_remove_object_terms($post_id, $default_category, 'category');
		}

    }
 }