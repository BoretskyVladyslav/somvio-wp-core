<?php
/**
 * Template Name: About Us
 *
 * About Us landing. Hero is the first full-width block inside main;
 * Our Story and Social Proof (Testimonials) follow in the page body.
 *
 * Figma nodes: 384:5980 (hero), 300:2032 (story)
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

	<div <?php generate_do_attr( 'content' ); ?>>
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php
			/**
			 * generate_before_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_main_content' );

			get_template_part( 'template-parts/sections/about', 'hero' );

			get_template_part( 'template-parts/sections/about', 'story' );

			/**
			 * About Us page body sections (future blocks above Social Proof).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_about_page_content' );

			get_template_part( 'template-parts/sections/testimonials' );

			if ( generate_has_default_loop() ) {
				while ( have_posts() ) :
					the_post();
					generate_do_template_part( 'page' );
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
