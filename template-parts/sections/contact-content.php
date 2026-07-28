<?php
/**
 * Contact Us page body — Figma 461:6140.
 *
 * Layout: contact info + socials | instant-quote form card | dark map.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_phone    = function_exists( 'somvio_get_phone' ) ? somvio_get_phone() : array(
	'display' => '+44 7402 495410',
	'href'    => 'tel:+447402495410',
);
$somvio_email    = function_exists( 'somvio_get_email' ) ? somvio_get_email() : array(
	'display' => 'Info@somvio.co.uk',
	'href'    => 'mailto:Info@somvio.co.uk',
);
$somvio_location = function_exists( 'somvio_get_location' ) ? somvio_get_location() : __( 'Glasgow, United Kingdom', 'somvio' );
$somvio_map_url  = function_exists( 'somvio_get_contact_map_embed_url' ) ? somvio_get_contact_map_embed_url() : '';
$somvio_whatsapp = function_exists( 'somvio_get_whatsapp_url' ) ? somvio_get_whatsapp_url() : 'https://wa.me/447402495410';
$somvio_socials  = function_exists( 'somvio_get_social_links' ) ? somvio_get_social_links() : array();

$somvio_privacy_url = home_url( '/privacy-policy/' );
$somvio_terms_url   = home_url( '/terms-conditions/' );

if ( function_exists( 'somvio_get_privacy_policy_page_id' ) ) {
	$privacy_id = somvio_get_privacy_policy_page_id();
	if ( $privacy_id > 0 ) {
		$permalink = get_permalink( $privacy_id );
		if ( $permalink ) {
			$somvio_privacy_url = (string) $permalink;
		}
	}
}

if ( function_exists( 'somvio_get_terms_conditions_page_id' ) ) {
	$terms_id = somvio_get_terms_conditions_page_id();
	if ( $terms_id > 0 ) {
		$permalink = get_permalink( $terms_id );
		if ( $permalink ) {
			$somvio_terms_url = (string) $permalink;
		}
	} elseif ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
		$terms_id = somvio_get_page_id_by_slug( 'terms-conditions' );
		if ( $terms_id > 0 ) {
			$permalink = get_permalink( $terms_id );
			if ( $permalink ) {
				$somvio_terms_url = (string) $permalink;
			}
		}
	}
}
?>
<section class="contact-page" aria-labelledby="contact-hero-title">
	<div class="contact-page__inner">
		<div class="contact-page__grid reveal-on-scroll">
			<div class="contact-page__info">
				<ul class="contact-page__details">
					<li class="contact-page__detail">
						<span class="contact-page__detail-icon" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<a class="contact-page__detail-value" href="<?php echo esc_url( $somvio_phone['href'] ); ?>">
							<?php echo esc_html( $somvio_phone['display'] ); ?>
						</a>
					</li>
					<li class="contact-page__detail">
						<span class="contact-page__detail-icon" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<a class="contact-page__detail-value" href="<?php echo esc_url( $somvio_email['href'] ); ?>">
							<?php echo esc_html( $somvio_email['display'] ); ?>
						</a>
					</li>
					<li class="contact-page__detail">
						<span class="contact-page__detail-icon" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-location' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span class="contact-page__detail-value"><?php echo esc_html( $somvio_location ); ?></span>
					</li>
				</ul>

				<a
					class="btn btn--outline btn--md contact-page__whatsapp"
					href="<?php echo esc_url( $somvio_whatsapp ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span class="btn__label"><?php esc_html_e( 'WhatsApp Us', 'somvio' ); ?></span>
				</a>

				<?php if ( ! empty( $somvio_socials ) ) : ?>
					<ul class="contact-page__social">
						<?php foreach ( $somvio_socials as $social ) : ?>
							<li class="contact-page__social-item">
								<a
									class="contact-page__social-link"
									href="<?php echo esc_url( $social['url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									aria-label="<?php echo esc_attr( $social['label'] ); ?>"
								>
									<span class="contact-page__social-icon" aria-hidden="true">
										<?php echo somvio_get_icon( $social['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="contact-page__form-card">
				<h2 class="contact-page__form-title"><?php esc_html_e( 'Your Instant Quote', 'somvio' ); ?></h2>

				<form class="contact-form" id="somvio-contact-form" novalidate>
					<div class="contact-form__honeypot" aria-hidden="true">
						<label for="contact-company-website"><?php esc_html_e( 'Company website', 'somvio' ); ?></label>
						<input
							type="text"
							id="contact-company-website"
							name="company_website"
							tabindex="-1"
							autocomplete="off"
						>
					</div>

					<div class="contact-form__success" data-contact-success hidden>
						<p class="contact-form__success-title"><?php esc_html_e( 'Thank You!', 'somvio' ); ?></p>
						<p class="contact-form__success-text"><?php esc_html_e( 'Your message has been sent.', 'somvio' ); ?></p>
					</div>

					<div class="contact-form__body" data-contact-body>
						<div class="contact-form__fields">
							<div class="contact-form__field">
								<label class="contact-form__label" for="contact-name">
									<?php esc_html_e( 'First Name', 'somvio' ); ?>
									<span class="required" aria-hidden="true">*</span>
								</label>
								<input
									class="contact-form__input"
									type="text"
									id="contact-name"
									name="name"
									autocomplete="given-name"
									required
									aria-describedby="contact-name-error"
									placeholder="<?php esc_attr_e( 'Your name', 'somvio' ); ?>"
								>
								<p class="contact-form__field-error" id="contact-name-error" data-contact-error="name" hidden role="alert"></p>
							</div>

							<div class="contact-form__field">
								<label class="contact-form__label" for="contact-email">
									<?php esc_html_e( 'Email', 'somvio' ); ?>
									<span class="required" aria-hidden="true">*</span>
								</label>
								<input
									class="contact-form__input"
									type="email"
									id="contact-email"
									name="email"
									autocomplete="email"
									required
									inputmode="email"
									aria-describedby="contact-email-error"
									placeholder="<?php esc_attr_e( 'you@example.com', 'somvio' ); ?>"
								>
								<p class="contact-form__field-error" id="contact-email-error" data-contact-error="email" hidden role="alert"></p>
							</div>

							<div class="contact-form__field">
								<label class="contact-form__label" for="contact-phone">
									<?php esc_html_e( 'Phone', 'somvio' ); ?>
									<span class="required" aria-hidden="true">*</span>
								</label>
								<input
									class="contact-form__input"
									type="tel"
									id="contact-phone"
									name="phone"
									autocomplete="tel"
									required
									inputmode="tel"
									aria-describedby="contact-phone-error"
									placeholder="<?php esc_attr_e( '+44 …', 'somvio' ); ?>"
								>
								<p class="contact-form__field-error" id="contact-phone-error" data-contact-error="phone" hidden role="alert"></p>
							</div>

							<div class="contact-form__field contact-form__field--comment">
								<label class="contact-form__label" for="contact-message">
									<?php esc_html_e( 'Comment', 'somvio' ); ?>
									<span class="required" aria-hidden="true">*</span>
								</label>
								<textarea
									class="contact-form__textarea"
									id="contact-message"
									name="message"
									rows="5"
									required
									aria-describedby="contact-message-error"
									placeholder="<?php esc_attr_e( 'How can we help?', 'somvio' ); ?>"
								></textarea>
								<p class="contact-form__field-error" id="contact-message-error" data-contact-error="message" hidden role="alert"></p>
							</div>
						</div>

						<label class="contact-form__terms">
							<input
								type="checkbox"
								class="contact-form__terms-input"
								name="terms_accepted"
								value="1"
								required
								aria-describedby="contact-terms-error"
							>
							<span class="contact-form__terms-box" aria-hidden="true"></span>
							<span class="contact-form__terms-text">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: 1: terms URL, 2: privacy URL */
										__( 'I have read and accepted the <a href="%1$s" target="_blank" rel="noopener noreferrer">Terms &amp; Conditions</a> and <a href="%2$s" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.', 'somvio' ),
										esc_url( $somvio_terms_url ),
										esc_url( $somvio_privacy_url )
									),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								);
								?>
							</span>
						</label>
						<p class="contact-form__field-error" id="contact-terms-error" data-contact-error="terms" hidden role="alert"></p>

						<p class="contact-form__status" role="status" aria-live="polite" hidden></p>

						<div class="contact-form__actions">
							<button class="btn btn--primary btn--md contact-form__submit" type="submit">
								<span class="btn__label"><?php esc_html_e( 'Send', 'somvio' ); ?></span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<?php if ( '' !== $somvio_map_url ) : ?>
			<div class="contact-page__map reveal-on-scroll" style="--reveal-delay: 0.08s;">
				<div class="contact-page__map-frame">
					<iframe
						class="contact-page__map-iframe"
						title="<?php esc_attr_e( 'Somvio service area map — Glasgow', 'somvio' ); ?>"
						src="<?php echo esc_url( $somvio_map_url ); ?>"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						allowfullscreen
					></iframe>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
