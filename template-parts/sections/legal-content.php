<?php
/**
 * Legal article body — Privacy Policy (300:2222) / Terms of Use (300:2243).
 *
 * Renders the page post_content inside a dark reading column.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section
	class="legal-content"
	aria-label="<?php esc_attr_e( 'Legal content', 'somvio' ); ?>"
>
	<div class="legal-content__inner entry-content reveal-on-scroll">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content applies kses filters.
		the_content();
		?>
	</div>
</section>
