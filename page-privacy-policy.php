<?php
/**
 * Template Name: Privacy Policy
 *
 * Legal shell — full-width main, no pre-footer CTA.
 * Hero + article body inside main.
 *
 * Figma nodes: 300:2218 (hero), 300:2222 (content)
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$somvio_legal_hero_args = array(
	'title'      => __( 'Privacy Policy', 'somvio' ),
	'breadcrumb' => __( 'Privacy Policy', 'somvio' ),
	'lead'       => __( 'Last Updated: June 2026', 'somvio' ),
	'aria_label' => __( 'Privacy Policy', 'somvio' ),
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
			 * Privacy Policy page body (extra sections).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_privacy_policy_content' );

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
