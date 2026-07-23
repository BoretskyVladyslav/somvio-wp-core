<?php
/**
 * Legacy FAQ section include — delegates to faq-accordion.php.
 *
 * @package Somvio_Child
 * @deprecated Use get_template_part( 'template-parts/sections/faq', 'accordion' ).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/sections/faq', 'accordion' );
