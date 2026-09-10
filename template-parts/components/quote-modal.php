<?php
/**
 * Floating quote calculator modal (header / global CTAs).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	class="quote-modal"
	data-quote-modal
	hidden
	aria-hidden="true"
>
	<div class="quote-modal__backdrop" data-quote-modal-close tabindex="-1"></div>
	<div
		class="quote-modal__dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="quote-modal-title"
		tabindex="-1"
	>
		<button
			type="button"
			class="quote-modal__dismiss"
			data-quote-modal-close
			aria-label="<?php esc_attr_e( 'Close quote form', 'somvio' ); ?>"
		>
			<span aria-hidden="true">&times;</span>
		</button>
		<?php
		get_template_part(
			'template-parts/components/quote',
			'calculator',
			array(
				'variant'  => 'solid',
				'class'    => 'quote-modal__card',
				'title_id' => 'quote-modal-title',
			)
		);
		?>
	</div>
</div>
