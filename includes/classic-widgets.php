<?php
/**
 * Enables the classic widgets settings screens in Appearance - Widgets and the Customizer. Disables the block editor from managing widgets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if (get_option('wpmt_classic_widgets') == '1') {
  // Disables the block editor from managing widgets in the Gutenberg plugin.
  add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
  // Disables the block editor from managing widgets.
  add_filter( 'use_widgets_block_editor', '__return_false' );
}