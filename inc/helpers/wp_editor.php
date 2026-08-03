<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable core block patterns.
 */
add_action( 'after_setup_theme', function () {
	remove_theme_support( 'core-block-patterns' );
} );

/**
 * Disable remote patterns from WordPress.org.
 */
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Disable Block Directory results in inserter search.
 */
add_action( 'plugins_loaded', function () {
	remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
} );

/**
 * Allow only selected Gutenberg blocks.
 */
add_filter( 'allowed_block_types_all', function( $allowed_blocks, $editor_context ) {
	return array(
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/image',
		// 'core/gallery',
		'core/spacer',
		'core/missing',
	);
}, 10, 2 );

/**
 * Hide Patterns tab and block search in editor UI.
 */
add_action( 'admin_head', function () {
	echo '<style>
		.editor-inserter__patterns,
		.block-editor-block-patterns-list,
		.components-tab-panel__tabs button[id*="pattern"],
		.block-editor-inserter__panel-header button[id*="pattern"],
		.block-editor-inserter__search,
		.block-editor-inserter__search-input,
		.components-search-control {
			display: none !important;
		}
	</style>';
} );