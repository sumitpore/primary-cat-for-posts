<?php //Added Docblock after below guard condition. // @codingStandardsIgnoreLine.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'PCP_Admin_WP_Radio_Taxonomy' ) ) :
	/**
	 * Process Single Taxonomy
	 *
	 * This class is responsible for
	 * 1. Show Single taxonomy in radio-button format by creating a custom meta box
	 * 2. Map the selected term with Post
	 *
	 * @package    PCP
	 * @subpackage PCP/admin/wp_radio_taxonomy
	 * @author     Sumit P <sumit.pore@gmail.com>
	 */
	class PCP_Admin_WP_Radio_Taxonomy {

		/**
		 * Taxonomy slug aka Taxonomy name
		 *
		 * @var string - taxonomy name
		 * @since 1.0.0
		 */
		public $taxonomy = null;

		/**
		 * The taxonomy object
		 *
		 * @var object - the taxonomy object
		 * @since 1.0.0
		 */
		public $tax_obj = null;

		/**
		 * Constructor
		 *
		 * @param string $taxonomy Taxonomy Slug.
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct( $taxonomy ) {
			$this->taxonomy = $taxonomy;

			// get the taxonomy object - need to get it after init but before admin_menu.
			$this->tax_obj = get_taxonomy( $taxonomy );

			// Remove old taxonomy meta box.
			add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );

			// Add new taxonomy meta box.
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

			// change checkboxes to radios.
			add_filter( 'wp_terms_checklist_args', array( $this, 'filter_terms_checklist_args' ) );

			// never save more than 1 term.
			add_action( 'save_post', array( $this, 'save_single_term' ) );
			add_action( 'edit_attachment', array( $this, 'save_single_term' ) );
		}


		/**
		 * Remove the default metabox
		 *
		 * @access public
		 * @return  void
		 * @since 1.0.0
		 */
		public function remove_meta_box() {
			if ( ! is_wp_error( $this->tax_obj ) && isset( $this->tax_obj->object_type ) ) {
				foreach ( $this->tax_obj->object_type as $post_type ) :
					$id = ! is_taxonomy_hierarchical( $this->taxonomy ) ? 'tagsdiv-' . $this->taxonomy : $this->taxonomy . 'div';
					remove_meta_box( $id, $post_type, 'side' );
			endforeach;
			};
		}

		/**
		 * Add our new customized metabox
		 *
		 * @access public
		 * @return  void
		 * @since 1.0.0
		 */
		public function add_meta_box() {
			if ( ! is_wp_error( $this->tax_obj ) && isset( $this->tax_obj->object_type ) ) {
				foreach ( $this->tax_obj->object_type as $post_type ) :
					$label = $this->tax_obj->labels->singular_name;
					$id = ! is_taxonomy_hierarchical( $this->taxonomy ) ? 'radio-tagsdiv-' . $this->taxonomy : 'radio-' . $this->taxonomy . 'div';
					add_meta_box( $id, $label, array( $this, 'metabox' ), $post_type, 'side', 'core', array( 'taxonomy' => $this->taxonomy ) );
			endforeach;
			};
		}

		/**
		 * Callback to set up the metabox
		 * Mimics the traditional hierarchical term metabox, but modified with our nonces
		 *
		 * @access public
		 * @param  WP_Post $post Post Object.
		 * @param  array   $box Metabox Arguments.
		 * @return void
		 * @since 1.0.0
		 */
		public function metabox( $post, $box ) {
			$defaults = array( 'taxonomy' => 'category' );
			$args = [];

			if ( isset( $box['args'] ) && is_array( $box['args'] ) ) {
				$args = $box['args'];
			}

			extract( wp_parse_args( $args, $defaults ), EXTR_SKIP ); // @codingStandardsIgnoreLine.

			// get current terms.
			$checked_terms = $post->ID ? get_the_terms( $post->ID, $taxonomy ) : array();

			// get first term object.
			$single_term = ! empty( $checked_terms ) && ! is_wp_error( $checked_terms ) ? array_pop( $checked_terms ) : false;

			// Popular Terms.
			$popular = get_terms(
				$taxonomy, array(
					'orderby' => 'count',
					'order' => 'DESC',
					'number' => 10,
					'hierarchical' => false,
				)
			);

			PCP_Template_Renderer::render(
				'admin/radio-taxonomy-metabox.php', [
					'post'              => $post,
					'taxonomy'          => $taxonomy,
					'radio_input_name'  => 'radio_tax_input[' . $taxonomy . ']',
					'taxonomy_object'   => $this->tax_obj,
					'popular'           => $popular,
					'disabled'          => ! current_user_can( $this->tax_obj->cap->assign_terms ) ? 'disabled="disabled"' : '',
					'single_term_id'    => $single_term ? (int) $single_term->term_id : 0,
				]
			);
		}


		/**
		 * Tell checklist function to use our new Walker
		 *
		 * @access public
		 * @param  array $args Walker Arguments.
		 * @return array
		 * @since 1.0.0
		 */
		public function filter_terms_checklist_args( $args ) {

			// define our custom Walker.
			if ( isset( $args['taxonomy'] ) && $this->taxonomy == $args['taxonomy'] ) {
				$args['walker'] = new PCP_Admin_Walker_Category_Radio();
				$args['checked_ontop'] = false;
			}
			return $args;
		}

		/**
		 * Only ever save a single term
		 *
		 * @param  int $post_id Id of saved post.
		 * @return int
		 * @since 1.0.0
		 */
		public function save_single_term( $post_id ) {
			// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// prevent weirdness with multisite.
			if ( function_exists( 'ms_is_switched' ) && ms_is_switched() ) {
				return;
			}

			// make sure we're on a supported post type.
			if ( is_array( $this->tax_obj->object_type ) && isset( $_REQUEST['post_type'] ) && ! in_array( $_REQUEST['post_type'], $this->tax_obj->object_type ) ) {
				return;
			}

			// verify nonce.
			if ( isset( $_POST[ "_radio_nonce-{$this->taxonomy}" ] ) && ! wp_verify_nonce( $_REQUEST[ "_radio_nonce-{$this->taxonomy}" ], "radio_nonce-{$this->taxonomy}" ) ) {
				return;
			}

			// OK, we must be authenticated by now: we need to find and save the data.
			if ( isset( $_REQUEST['radio_tax_input'][ "{$this->taxonomy}" ] ) ) {
				$term = null;
				// If user has selected the category.
				if ( '-1' != $_REQUEST['radio_tax_input'][ "{$this->taxonomy}" ] ) {
					$term = get_term_by( 'id', intval( $_REQUEST['radio_tax_input'][ "{$this->taxonomy}" ] ), $this->taxonomy );
				}

				// if category and not saving any terms, set post meta.
				if ( empty( $term ) || is_wp_error( $term ) ) {
					$this->update_list_of_unselected_required_taxonomies( $post_id );
					return;
				}

				// set the single terms.
				if ( current_user_can( $this->tax_obj->cap->assign_terms ) ) {
					wp_set_object_terms( $post_id, $term->term_id, $this->taxonomy );
				}
			}

			return;
		}

		/**
		 * Updates a post meta containing information about required taxonomies
		 * not selected by user.
		 *
		 * Note: This list is deleted after an error message is shown.
		 *
		 * @param int $post_id Id of saved post.
		 * @return void
		 * @since 1.0.0
		 */
		private function update_list_of_unselected_required_taxonomies( $post_id ) {
$list_of_unselected_required_taxonomies = $old = get_post_meta( $post_id, 'unselected_required_taxonomies', true ); // @codingStandardsIgnoreLine

			if ( empty( $list_of_unselected_required_taxonomies ) ) {
				$list_of_unselected_required_taxonomies = [];
			}

			if ( ! in_array( $this->taxonomy, $list_of_unselected_required_taxonomies ) ) {
				$list_of_unselected_required_taxonomies[] = $this->taxonomy;
				update_post_meta( $post_id, 'unselected_required_taxonomies', $list_of_unselected_required_taxonomies, $old );
			}
		}


	} //end class - do NOT remove or else
endif;
