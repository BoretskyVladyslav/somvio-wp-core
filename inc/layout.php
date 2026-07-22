<?php
/**
 * GeneratePress layout overrides (sidebars, footer widgets, canvas).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force no-sidebar layout globally.
 *
 * @return string
 */
function somvio_force_no_sidebar() {
	return 'no-sidebar';
}
add_filter( 'generate_sidebar_layout', 'somvio_force_no_sidebar' );

/**
 * Disable GeneratePress footer widget columns.
 *
 * @return string
 */
function somvio_disable_footer_widgets() {
	return '0';
}
add_filter( 'generate_footer_widgets', 'somvio_disable_footer_widgets' );

/**
 * Prefer one continuous content container over separate white boxes.
 *
 * @param array $settings GeneratePress settings.
 * @return array
 */
function somvio_force_one_container( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	$settings['content_layout_setting'] = 'one-container';
	$settings['layout_setting']         = 'no-sidebar';
	$settings['blog_layout_setting']    = 'no-sidebar';
	$settings['single_layout_setting']  = 'no-sidebar';
	$settings['footer_widget_setting']  = '0';

	return $settings;
}
add_filter( 'option_generate_settings', 'somvio_force_one_container' );

/**
 * Hide default GeneratePress page titles (section H2s own the headings).
 */
add_filter( 'generate_show_title', '__return_false' );
