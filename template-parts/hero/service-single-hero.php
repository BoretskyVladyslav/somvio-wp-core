<?php
/**
 * Single Service page hero — Figma 362:4968.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url     = esc_url( home_url( '/' ) );
$somvio_services_url = esc_url( home_url( '/services/' ) );
$somvio_book_url     = somvio_get_book_now_url();
$somvio_title        = get_the_title();

if ( ! is_string( $somvio_title ) || '' === $somvio_title ) {
	$somvio_title = __( 'Regular Cleaning', 'somvio' );
}
?>
<section
	class="service-single-hero"
	aria-label="<?php echo esc_attr( $somvio_title ); ?>"
>
	<div class="service-single-hero__bg" aria-hidden="true"></div>

	<div class="service-single-hero__inner">
		<div class="service-single-hero__grid">
			<div class="service-single-hero__content">
				<nav
					class="service-single-hero__breadcrumbs reveal-on-scroll"
					aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
				>
					<ol class="service-single-hero__breadcrumb-list">
						<li class="service-single-hero__breadcrumb-item">
							<a class="service-single-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
								<?php esc_html_e( 'Home', 'somvio' ); ?>
							</a>
						</li>
						<li
							class="service-single-hero__breadcrumb-item service-single-hero__breadcrumb-item--current"
							aria-current="page"
						>
							<span class="service-single-hero__breadcrumb-sep" aria-hidden="true">
								<?php
								// Trusted local theme SVG from assets/icons/.
								echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
							<span class="service-single-hero__breadcrumb-current">
								<?php esc_html_e( 'Services', 'somvio' ); ?>
							</span>
						</li>
					</ol>
				</nav>

				<h1 class="service-single-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
					<?php echo esc_html( $somvio_title ); ?>
				</h1>

				<p class="service-single-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
					<?php
					esc_html_e(
						'Clean spaces. Better living. High-quality cleaning services for homes and businesses across the UK.',
						'somvio'
					);
					?>
				</p>

				<div class="service-single-hero__actions reveal-on-scroll" style="--reveal-delay: 0.15s;">
					<a class="btn btn--primary btn--md" href="<?php echo $somvio_book_url; ?>">
						<span class="btn__label"><?php esc_html_e( 'Get Instant Quote', 'somvio' ); ?></span>
					</a>
					<a class="btn btn--outline btn--md" href="<?php echo $somvio_services_url; ?>">
						<span class="btn__label"><?php esc_html_e( 'Our Services', 'somvio' ); ?></span>
					</a>
				</div>
			</div>

			<aside
				id="somvio-instant-quote"
				class="service-single-hero__quote quote-card reveal-on-scroll"
				style="--reveal-delay: 0.1s;"
				aria-label="<?php esc_attr_e( 'Get Your Instant Quote', 'somvio' ); ?>"
			>
				<h2 class="quote-card__title"><?php esc_html_e( 'Get Your Instant Quote', 'somvio' ); ?></h2>

				<form class="quote-card__form" action="#" method="get" novalidate>
					<div class="quote-card__field quote-card__field--full">
						<label class="quote-card__label" for="somvio-service-quote-service">
							<?php esc_html_e( 'Service Type', 'somvio' ); ?>
						</label>
						<div class="quote-card__select-wrap">
							<select
								class="quote-card__select"
								id="somvio-service-quote-service"
								name="service_type"
								disabled
							>
								<option selected><?php echo esc_html( $somvio_title ); ?></option>
							</select>
							<span class="quote-card__chevron" aria-hidden="true">
								<?php
								// Trusted local theme SVG from assets/icons/.
								echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						</div>
					</div>

					<div class="quote-card__field quote-card__field--full">
						<label class="quote-card__label" for="somvio-service-quote-property">
							<?php esc_html_e( 'Property Type:', 'somvio' ); ?>
						</label>
						<div class="quote-card__select-wrap">
							<select
								class="quote-card__select"
								id="somvio-service-quote-property"
								name="property_type"
								disabled
							>
								<option selected><?php esc_html_e( 'House', 'somvio' ); ?></option>
							</select>
							<span class="quote-card__chevron" aria-hidden="true">
								<?php
								// Trusted local theme SVG from assets/icons/.
								echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						</div>
					</div>

					<div class="quote-card__row">
						<div class="quote-card__field">
							<label class="quote-card__label" for="somvio-service-quote-bedrooms">
								<?php esc_html_e( 'Bedrooms', 'somvio' ); ?>
							</label>
							<div class="quote-card__select-wrap">
								<select
									class="quote-card__select"
									id="somvio-service-quote-bedrooms"
									name="bedrooms"
									disabled
								>
									<option selected>1</option>
								</select>
								<span class="quote-card__chevron" aria-hidden="true">
									<?php
									// Trusted local theme SVG from assets/icons/.
									echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</span>
							</div>
						</div>

						<div class="quote-card__field">
							<label class="quote-card__label" for="somvio-service-quote-bathrooms">
								<?php esc_html_e( 'Bathrooms', 'somvio' ); ?>
							</label>
							<div class="quote-card__select-wrap">
								<select
									class="quote-card__select"
									id="somvio-service-quote-bathrooms"
									name="bathrooms"
									disabled
								>
									<option selected>2</option>
								</select>
								<span class="quote-card__chevron" aria-hidden="true">
									<?php
									// Trusted local theme SVG from assets/icons/.
									echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</span>
							</div>
						</div>
					</div>

					<div class="quote-card__footer">
						<button class="btn btn--primary btn--sm btn--has-icon" type="button">
							<span class="btn__label"><?php esc_html_e( 'Next Step', 'somvio' ); ?></span>
							<span class="btn__icon" aria-hidden="true">
								<?php
								// Trusted local theme SVG from assets/icons/.
								echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						</button>
						<p class="quote-card__step"><?php esc_html_e( 'Step 1 of 5', 'somvio' ); ?></p>
					</div>
				</form>
			</aside>
		</div>
	</div>
</section>
