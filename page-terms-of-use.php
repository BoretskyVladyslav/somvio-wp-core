<?php
/**
 * Template Name: Terms of Use
 *
 * Legal shell — full-width main, no pre-footer CTA.
 * Hero + article body inside main.
 *
 * Figma nodes: 300:2239 (hero), 300:2243 (content)
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$somvio_legal_hero_args = array(
	'title'      => __( 'Terms & Conditions', 'somvio' ),
	'breadcrumb' => __( 'Terms of Use', 'somvio' ),
	'lead'       => __( 'Please read these Terms carefully before booking our services.', 'somvio' ),
	'aria_label' => __( 'Terms of Use', 'somvio' ),
);
?>

	<div <?php generate_do_attr( 'content' ); ?>>
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php
			// Hero first — Figma 300:2239.
			get_template_part( 'template-parts/sections/legal-hero', null, $somvio_legal_hero_args );

			/**
			 * generate_before_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_main_content' );

			/**
			 * Terms of Use page body (extra sections).
			 *
			 * @since 1.0.0
			 */
			do_action( 'somvio_terms_of_use_content' );

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
