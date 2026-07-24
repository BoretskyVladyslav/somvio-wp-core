<?php
/**
 * Template Name: Blog
 *
 * Blog landing. Hero is the first full-width block inside main;
 * Process Steps (How It Works) follows in the page body.
 * No default post loop — keep main clean for custom section templates.
 *
 * Figma nodes: 300:2181 (hero)
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

			get_template_part( 'template-parts/sections/blog', 'hero' );

			/**
			 * Blog page body sections (posts grid, etc.).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_blog_page_content' );

			get_template_part( 'template-parts/sections/how', 'it-works' );

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
