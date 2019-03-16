<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PCP_Admin_WP_Radio_Taxonomy' ) ) :

	class PCP_Admin_WP_Radio_Taxonomy {

		/**
		 * @var string - taxonomy name
		 * @since 1.0.0
		 */
		public $taxonomy = null;

		/**
		 * @var object - the taxonomy object
		 * @since 1.0.0
		 */
		public $tax_obj = null;

		/**
		 * @var boolean - whether to filter get_terms() or not
		 * @since 1.0.0
		 */
		private $set = true;

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct( $taxonomy ) {
			$this->taxonomy = $taxonomy;

			// get the taxonomy object - need to get it after init but before admin_menu
			$this->tax_obj = get_taxonomy( $taxonomy );

			// Remove old taxonomy meta box
			add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );

			// Add new taxonomy meta box
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

			// change checkboxes to radios & trigger get_terms() filter
			add_filter( 'wp_terms_checklist_args', array( $this, 'filter_terms_checklist_args' ) );

			// never save more than 1 term
			add_action( 'save_post', array( $this, 'save_single_term' ) );
			add_action( 'edit_attachment', array( $this, 'save_single_term' ) );

			// hack global taxonomy to switch all radio taxonomies to hierarchical on edit screen
			add_action( 'load-edit.php', array( $this, 'make_hierarchical' ) );
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
		 * Mimims the traditional hierarchical term metabox, but modified with our nonces
		 *
		 * @access public
		 * @param  object $post
		 * @param  array  $args
		 * @return  print HTML
		 * @since 1.0.0
		 */
		public function metabox( $post, $box ) {
			$defaults = array( 'taxonomy' => 'category' );
			if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
				$args = array();
			} else {
				$args = $box['args'];
			}
			extract( wp_parse_args( $args, $defaults ), EXTR_SKIP ); // @codingStandardsIgnoreLine.

			// get current terms
			$checked_terms = $post->ID ? get_the_terms( $post->ID, $taxonomy ) : array();

			// get first term object
			$single_term = ! empty( $checked_terms ) && ! is_wp_error( $checked_terms ) ? array_pop( $checked_terms ) : false;
			$single_term_id = $single_term ? (int) $single_term->term_id : 0;
			?>
		<div id="taxonomy-<?php echo esc_attr( $taxonomy ); ?>" class="radio-buttons-for-taxonomies categorydiv form-no-clear">
			<ul id="<?php echo esc_attr( $taxonomy ); ?>-tabs" class="category-tabs">
				<?php // Below hidden element submits cat as -1 if user has not selected any cat ?>
				<input type="hidden" name="<?php echo esc_attr( 'radio_tax_input[' . $taxonomy . ']' ); ?>" value="-1">
				<li class="tabs"><a href="#<?php echo esc_attr( $taxonomy ); ?>-all" tabindex="3"><?php echo esc_html( $this->tax_obj->labels->all_items ); ?></a></li>
				<li class="hide-if-no-js"><a href="#<?php echo esc_attr( $taxonomy ); ?>-pop" tabindex="3"><?php esc_html_e( 'Most Used', PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ); ?></a></li>
			</ul>

			<?php wp_nonce_field( 'radio_nonce-' . $taxonomy, '_radio_nonce-' . $taxonomy ); ?>

			<div id="<?php echo esc_attr( $taxonomy ); ?>-pop" class="tabs-panel" style="display: none;">
				<ul id="<?php echo esc_attr( $taxonomy ); ?>checklist-pop" class="categorychecklist form-no-clear" >
					<?php
					$popular = get_terms(
						$taxonomy, array(
							'orderby' => 'count',
							'order' => 'DESC',
							'number' => 10,
							'hierarchical' => false,
						)
					);

					if ( ! current_user_can( $this->tax_obj->cap->assign_terms ) ) {
						$disabled = 'disabled="disabled"';
					} else {
						$disabled = '';
					}

						$popular_ids = array();
					?>

							<?php
							foreach ( $popular as $term ) {
								$popular_ids[] = $term->term_id;

								$value = is_taxonomy_hierarchical( $taxonomy ) ? $term->term_id : $term->slug;
								$id = 'popular-' . $taxonomy . '-' . $term->term_id;

								echo "<li id='$id'><label class='selectit'>";
								echo "<input type='radio' id='in-" . intval( $id ) . "'" . checked( $single_term_id, $term->term_id, false ) . " value='" . esc_attr( $value ) . "' " . $disabled . ' />&nbsp;' . esc_html( $term->name ) . '<br />';

								echo '</label></li>';
							}
							?>
				</ul>
			</div>

			<div id="<?php echo esc_attr( $taxonomy ); ?>-all" class="tabs-panel">
				<ul id="<?php echo esc_attr( $taxonomy ); ?>checklist" data-wp-lists="list:<?php echo esc_attr( $taxonomy ); ?>" class="categorychecklist form-no-clear">
						<?php
						wp_terms_checklist(
							$post->ID, array(
								'taxonomy' => $taxonomy,
								'popular_cats' => $popular_ids,
							)
						);
						?>
				</ul>
			</div>
		</div>
			<?php
		}


		/**
		 * Tell checklist function to use our new Walker
		 *
		 * @access public
		 * @param  array $args
		 * @return array
		 * @since 1.0.0
		 */
		public function filter_terms_checklist_args( $args ) {

			// define our custom Walker
			if ( isset( $args['taxonomy'] ) && $this->taxonomy == $args['taxonomy'] ) {

				// add a filter to get_terms() but only for radio lists
				$this->set_terms_filter( false );

				add_filter( 'get_terms', array( $this, 'get_terms' ), 10, 3 );

				$args['walker'] = new PCP_Admin_Walker_Category_Radio();
				$args['checked_ontop'] = false;
			}
			return $args;
		}

		/**
		 * Turn on/off the terms filter.
		 *
		 * Only filter get_terms() in the wp_terms_checklist() function
		 *
		 * @access public
		 * @param  bool $_set
		 * @return bool
		 * @since 1.0.0
		 */
		private function set_terms_filter( $_set = true ) {
			$this->set = (bool) $_set;
		}

		/**
		 * Only filter get_terms() in the wp_terms_checklist() function
		 *
		 * @access public
		 * @param  bool $_set
		 * @return bool
		 * @since 1.0.0
		 */
		private function get_terms_filter() {
			// give users a chance to disable the no term feature
			return apply_filters( 'pcp_no_term_' . $this->taxonomy, $this->set );
		}


		/**
		 * Add new 0 or null term in metabox and quickedit
		 * this will allow users to "undo" a term if the taxonomy is not required
		 *
		 * @param  array $terms
		 * @param  array $taxonomies
		 * @param  array $args
		 * @return array
		 * @since 1.0.0
		 */
		function get_terms( $terms, $taxonomies, $args ) {

			// only filter terms for radio taxes (except category) and only in the checkbox - need to check $args b/c get_terms() is called multiple times in wp_terms_checklist()
			if ( in_array( $this->taxonomy, (array) $taxonomies ) && ! in_array( 'category', $taxonomies )
			&& isset( $args['fields'] ) && 'all' == $args['fields'] && $this->get_terms_filter() ) {

				// remove filter after 1st run
				remove_filter( current_filter(), __FUNCTION__, 10, 3 );

				$no_term = sprintf( __( apply_filters( 'pcp_no_term_selected_text', 'No %s' ), PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ), $this->tax_obj->labels->singular_name ); // @codingStandardsIgnoreLine.

				$uncategorized = (object) array(
					'term_id' => '0',
					'slug' => '0',
					'name' => $no_term,
					'parent' => '0',
				);

				array_push( $terms, $uncategorized );
			}

			return $terms;
		}

		/**
		 * Only ever save a single term
		 *
		 * @param  int $post_id
		 * @return int
		 * @since 1.0.0
		 */
		function save_single_term( $post_id ) {
			// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// prevent weirdness with multisite
			if ( function_exists( 'ms_is_switched' ) && ms_is_switched() ) {
				return;
			}

			// make sure we're on a supported post type
			if ( is_array( $this->tax_obj->object_type ) && isset( $_REQUEST['post_type'] ) && ! in_array( $_REQUEST['post_type'], $this->tax_obj->object_type ) ) {
				return;
			}

			// verify nonce
			if ( isset( $_POST[ "_radio_nonce-{$this->taxonomy}" ] ) && ! wp_verify_nonce( $_REQUEST[ "_radio_nonce-{$this->taxonomy}" ], "radio_nonce-{$this->taxonomy}" ) ) {
				return;
			}

			// OK, we must be authenticated by now: we need to find and save the data
			if ( isset( $_REQUEST['radio_tax_input'][ "{$this->taxonomy}" ] ) ) {
				$term = null;
				// If user has selected the category
				if ( '-1' != $_REQUEST['radio_tax_input'][ "{$this->taxonomy}" ] ) {
					$term = get_term_by( 'id', intval( $_REQUEST['radio_tax_input'][ "{$this->taxonomy}" ] ), $this->taxonomy );
				}

				// if category and not saving any terms, set post meta
				if ( empty( $term ) || is_wp_error( $term ) ) {
					$this->update_list_of_unselected_required_taxonomies( $post_id );
					return;
				}

				// set the single terms
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
		 * Note: This list is deleted after an error message is shown
		 *
		 * @param int $post_id
		 * @return void
		 * @since 1.0.0
		 */
		private function update_list_of_unselected_required_taxonomies( $post_id ) {

			$list_of_unselected_required_taxonomies = $old = get_post_meta( $post_id, 'unselected_required_taxonomies', true ); // @codingStandardsIgnoreLine
			$taxonomy_name = get_taxonomy( $this->taxonomy )->labels->singular_name;

			if ( empty( $list_of_unselected_required_taxonomies ) ) {
				$list_of_unselected_required_taxonomies = [];
			}

			if ( ! in_array( $taxonomy_name, $list_of_unselected_required_taxonomies ) ) {
				$list_of_unselected_required_taxonomies[] = $taxonomy_name;
				update_post_meta( $post_id, 'unselected_required_taxonomies', $list_of_unselected_required_taxonomies, $old );
			}
		}

		/**
		 * Use this action to switch all radio taxonomies to hierarchical on edit.php
		 * at the moment, there is no filter, so we have to hack the global variable
		 *
		 * @param  array $columns
		 * @return array
		 * @since 1.0.0
		 */
		public function make_hierarchical() {
			global $wp_taxonomies;
			$wp_taxonomies[ $this->taxonomy ]->hierarchical = true;
		}


	} //end class - do NOT remove or else
endif;
