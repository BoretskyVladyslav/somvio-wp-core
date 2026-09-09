<?php
/**
 * Front page.
 *
 * Hero and homepage sections are injected via GeneratePress hooks
 * (`generate_after_header` in inc/hero.php and related inc/* files).
 * This template skips the default page-title loop so the hero keeps a single H1.
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
			 * Optional extra homepage blocks inside main (hero stays on generate_after_header).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_front_page_content' );

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
