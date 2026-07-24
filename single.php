<?php
/**
 * Single blog post.
 *
 * Full-width block shell (matches page-about / page-faq / page-blog) to avoid
 * GeneratePress default flex sidebar layout conflicts. Sections load as stacked
 * blocks inside main: Hero → Article Body → Author / Related.
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

			get_template_part( 'template-parts/sections/blog', 'single-hero' );

			/**
			 * Single post sections (Article Body, Author / Related).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_single_post_content' );

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
