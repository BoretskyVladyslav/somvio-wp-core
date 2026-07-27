<?php
/**
 * Template Name: Legal Page
 *
 * Shared legal shell — full-width main, no pre-footer CTA.
 * Hero + article body from post_content.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$somvio_legal_hero_args = function_exists( 'somvio_get_current_legal_hero_args' )
	? somvio_get_current_legal_hero_args()
	: array(
		'title'      => get_the_title(),
		'breadcrumb' => get_the_title(),
		'lead'       => '',
		'aria_label' => get_the_title(),
	);
?>

	<div <?php generate_do_attr( 'content' ); ?>>
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php
			get_template_part( 'template-parts/sections/legal-hero', null, $somvio_legal_hero_args );

			/**
			 * generate_before_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_main_content' );

			/**
			 * Legal page body (extra sections).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_legal_page_content' );

			if ( generate_has_default_loop() ) {
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/sections/legal-content' );
				endwhile;
			}

			/**
			 * generate_after_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_after_main_content' );
			?>
		</main>
	</div>

	<?php
	/**
	 * generate_after_primary_content_area hook.
	 *
	 * @since 2.0
	 */
	do_action( 'generate_after_primary_content_area' );

	generate_construct_sidebars();

	get_footer();
