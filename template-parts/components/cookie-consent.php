<?php
/**
 * Cookie consent banner markup (Figma 450:5227).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cookie_policy_url = home_url( '/cookie-policy/' );
?>
<div
	class="cookie-consent"
	data-cookie-consent
	hidden
	aria-hidden="true"
	role="dialog"
	aria-labelledby="somvio-cookie-consent-title"
	aria-describedby="somvio-cookie-consent-desc"
>
	<div class="cookie-consent__panel">
		<p class="cookie-consent__title" id="somvio-cookie-consent-title">
			<?php esc_html_e( 'Cookie preferences', 'somvio' ); ?>
		</p>
		<p class="cookie-consent__text" id="somvio-cookie-consent-desc">
			<?php
			echo esc_html__(
				'We use cookies to improve your browsing experience, analyze website traffic, and personalize content. By clicking "Accept All", you consent to our use of cookies.',
				'somvio'
			);
			?>
			<?php
			printf(
				/* translators: %s: cookie policy URL */
				' <a class="cookie-consent__link" href="%s">%s</a>',
				esc_url( $cookie_policy_url ),
				esc_html__( 'Cookie Policy', 'somvio' )
			);
			?>
		</p>
		<div class="cookie-consent__actions">
			<button
				type="button"
				class="btn btn--primary btn--md cookie-consent__btn"
				data-cookie-consent-accept
			>
				<span class="btn__label"><?php esc_html_e( 'Accept All', 'somvio' ); ?></span>
			</button>
			<button
				type="button"
				class="btn btn--outline btn--md cookie-consent__btn"
				data-cookie-consent-decline
			>
				<span class="btn__label"><?php esc_html_e( 'Reject', 'somvio' ); ?></span>
			</button>
			<a
				class="btn btn--outline btn--md cookie-consent__btn"
				href="<?php echo esc_url( $cookie_policy_url ); ?>"
				data-cookie-consent-settings
			>
				<span class="btn__label"><?php esc_html_e( 'Cookie Settings', 'somvio' ); ?></span>
			</a>
		</div>
	</div>
</div>
