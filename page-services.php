<?php
/**
 * Services page template (slug: services).
 *
 * Compact inner hero is injected via `generate_after_header`.
 * Global header, CTA banner, and footer come from theme hooks.
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

			/**
			 * Services page body sections (Services List, How It Works, etc.).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_services_page_content' );

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
