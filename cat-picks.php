<?php
/**
 * Plugin Name: Cat Picks
 * Plugin URI: https://github.com/shoaibchaudhry/cat-picks
 * Description: Registers a Cat Picks custom post type with a Featured By meta field.
 * Version: 1.0.0
 * Author: Shoaib Chaudhry
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: cat-picks
 *
 * @package CatPicks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CAT_PICKS_POST_TYPE = 'cat_pick';
const CAT_PICKS_FEATURED_BY_META_KEY = '_cat_picks_featured_by';

/**
 * Register the Cat Picks custom post type.
 */
function cat_picks_register_post_type(): void {
	$labels = array(
		'name'                  => _x( 'Cat Picks', 'Post type general name', 'cat-picks' ),
		'singular_name'         => _x( 'Cat Pick', 'Post type singular name', 'cat-picks' ),
		'menu_name'             => _x( 'Cat Picks', 'Admin menu text', 'cat-picks' ),
		'name_admin_bar'        => _x( 'Cat Pick', 'Add new on toolbar', 'cat-picks' ),
		'add_new'               => __( 'Add New', 'cat-picks' ),
		'add_new_item'          => __( 'Add New Cat Pick', 'cat-picks' ),
		'new_item'              => __( 'New Cat Pick', 'cat-picks' ),
		'edit_item'             => __( 'Edit Cat Pick', 'cat-picks' ),
		'view_item'             => __( 'View Cat Pick', 'cat-picks' ),
		'all_items'             => __( 'All Cat Picks', 'cat-picks' ),
		'search_items'          => __( 'Search Cat Picks', 'cat-picks' ),
		'parent_item_colon'     => __( 'Parent Cat Picks:', 'cat-picks' ),
		'not_found'             => __( 'No cat picks found.', 'cat-picks' ),
		'not_found_in_trash'    => __( 'No cat picks found in Trash.', 'cat-picks' ),
		'featured_image'        => __( 'Cat Pick featured image', 'cat-picks' ),
		'set_featured_image'    => __( 'Set featured image', 'cat-picks' ),
		'remove_featured_image' => __( 'Remove featured image', 'cat-picks' ),
		'use_featured_image'    => __( 'Use as featured image', 'cat-picks' ),
	);

	register_post_type(
		CAT_PICKS_POST_TYPE,
		array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => true,
			'menu_icon'          => 'dashicons-star-filled',
			'rewrite'            => array( 'slug' => 'cat-picks' ),
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);
}
add_action( 'init', 'cat_picks_register_post_type' );

/**
 * Register the Featured By post meta for REST and sanitization support.
 */
function cat_picks_register_meta(): void {
	register_post_meta(
		CAT_PICKS_POST_TYPE,
		CAT_PICKS_FEATURED_BY_META_KEY,
		array(
			'type'              => 'string',
			'description'       => __( 'Person who featured this Cat Pick.', 'cat-picks' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => static function (): bool {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'cat_picks_register_meta' );

/**
 * Add the Featured By meta box to Cat Picks in admin.
 */
function cat_picks_add_featured_by_meta_box(): void {
	add_meta_box(
		'cat-picks-featured-by',
		__( 'Featured By', 'cat-picks' ),
		'cat_picks_render_featured_by_meta_box',
		CAT_PICKS_POST_TYPE,
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'cat_picks_add_featured_by_meta_box' );

/**
 * Render the Featured By field.
 *
 * @param WP_Post $post Current post object.
 */
function cat_picks_render_featured_by_meta_box( WP_Post $post ): void {
	$featured_by = get_post_meta( $post->ID, CAT_PICKS_FEATURED_BY_META_KEY, true );

	wp_nonce_field( 'cat_picks_save_featured_by', 'cat_picks_featured_by_nonce' );
	?>
	<label for="cat-picks-featured-by-field">
		<?php esc_html_e( 'Name', 'cat-picks' ); ?>
	</label>
	<input
		type="text"
		id="cat-picks-featured-by-field"
		name="cat_picks_featured_by"
		value="<?php echo esc_attr( $featured_by ); ?>"
		class="widefat"
	/>
	<?php
}

/**
 * Save the Featured By field.
 *
 * @param int $post_id Current post ID.
 */
function cat_picks_save_featured_by( int $post_id ): void {
	if (
		! isset( $_POST['cat_picks_featured_by_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cat_picks_featured_by_nonce'] ) ), 'cat_picks_save_featured_by' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( CAT_PICKS_POST_TYPE !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$featured_by = isset( $_POST['cat_picks_featured_by'] )
		? sanitize_text_field( wp_unslash( $_POST['cat_picks_featured_by'] ) )
		: '';

	if ( '' === $featured_by ) {
		delete_post_meta( $post_id, CAT_PICKS_FEATURED_BY_META_KEY );
		return;
	}

	update_post_meta( $post_id, CAT_PICKS_FEATURED_BY_META_KEY, $featured_by );
}
add_action( 'save_post_' . CAT_PICKS_POST_TYPE, 'cat_picks_save_featured_by' );

/**
 * Append the Featured By value on single Cat Pick views.
 *
 * @param string $content Post content.
 * @return string
 */
function cat_picks_append_featured_by_to_content( string $content ): string {
	if ( ! is_singular( CAT_PICKS_POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$featured_by = get_post_meta( get_the_ID(), CAT_PICKS_FEATURED_BY_META_KEY, true );

	if ( '' === $featured_by ) {
		return $content;
	}

	$featured_by_markup = sprintf(
		'<p class="cat-picks-featured-by"><strong>%s</strong> %s</p>',
		esc_html__( 'Featured By:', 'cat-picks' ),
		esc_html( $featured_by )
	);

	return $content . $featured_by_markup;
}
add_filter( 'the_content', 'cat_picks_append_featured_by_to_content' );

/**
 * Flush rewrite rules when the plugin is activated.
 */
function cat_picks_activate(): void {
	cat_picks_register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cat_picks_activate' );

/**
 * Flush rewrite rules when the plugin is deactivated.
 */
function cat_picks_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cat_picks_deactivate' );
