<?php
/**
 * Allows user to fetch posts/cpt by their respective primary category
 *
 * @package    PCP
 * @subpackage PCP/public
 * @since      1.0.0
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class PCP_Public_Posts_Listing {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct() {
		add_shortcode( 'pcp_list_posts', array( $this, 'list_posts' ) );
	}

	/**
	 * Returns list of posts based on Post type, taxonomy and term
	 *
	 * @param string    $post_type
	 * @param string    $taxonomy
	 * @param string|id $term
	 * @param integer   $page_number
	 * @return void
	 */
	public static function get_posts( $post_type, $taxonomy, $term, $page_number = 1 ) {
		$args = array(
			'post_type' => $post_type,
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $term,
				),
			),
			'paged' => intval( $page_number ) ? $page_number : 1,
			'posts_per_page' => apply_filters( 'pcp_number_of_posts_per_page', 5 ),
		);

		return get_posts( $args );
	}

	/**
	 * Callback of list_posts shortcode
	 *
	 * Displays the list of posts based on atts passed to shortcode
	 *
	 * @param array $atts
	 * @return void
	 */
	public function list_posts( $atts ) {
		$post_type = isset( $atts['post_type'] ) ? $atts['post_type'] : '';
		$taxonomy = isset( $atts['taxonomy'] ) ? $atts['taxonomy'] : '';
		$term = isset( $atts['term'] ) ? $atts['term'] : '';

		if ( empty( $post_type ) || empty( $taxonomy ) || empty( $term ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'The shortcode is missing one of the following parameters: post_type, taxonomy & term. `pcp_list_posts` shortcode needs all of them', PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ), PRIMARY_CAT_FOR_POSTS_VERSION );
			return '';
		}

		$page_number = apply_filters( 'pcp_list_posts_shortcode_page_number', 1, $atts );
		$posts = PCP_Public_Posts_Listing::get_posts( $post_type, $taxonomy, $term, $page_number );
		return PCP_Template_Renderer::render( 'public/list-posts.php', [ 'posts' => $posts ], false );
	}

}
