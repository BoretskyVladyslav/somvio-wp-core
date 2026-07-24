<?php
/**
 * Template Name: Booking
 *
 * Booking landing. Hero is the first full-width block inside main;
 * booking form + order summary follow (Figma 418:6213). No pre-footer CTA.
 *
 * Figma nodes: 418:6207 (hero), 418:6213 (form)
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

			get_template_part( 'template-parts/sections/booking', 'hero' );

			/**
			 * Booking page body sections (future blocks above form).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_booking_page_content' );

			get_template_part( 'template-parts/sections/booking', 'form' );

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
