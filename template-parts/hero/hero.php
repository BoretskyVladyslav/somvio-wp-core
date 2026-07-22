<?php
/**
 * Homepage hero markup — Figma 310:4965.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_icons_uri = get_stylesheet_directory_uri() . '/assets/icons';
$somvio_book_url  = somvio_get_book_now_url();
$somvio_services  = esc_url( home_url( '/services/' ) );

/**
 * Render the Figma sparkle separator with unique SVG mask/clip IDs.
 *
 * @param string $uid Unique suffix for this instance.
 * @return string
 */
$somvio_render_sparkle = static function ( $uid ) {
	$svg = function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-sparkle' ) : '';

	if ( '' === $svg ) {
		return '';
	}

	$safe_uid = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $uid );

	return preg_replace(
		'/(mask0_300_1340|clip0_300_1340)/',
		'$1_' . $safe_uid,
		$svg
	);
};

/**
 * Output one hero ratings sequence for the marquee track.
 *
 * @param string $icons_uri Theme icons URI (trust logos).
 * @param bool   $duplicate Whether this is the aria-hidden duplicate copy.
 * @return void
 */
$somvio_render_hero_ratings = static function ( $icons_uri, $duplicate = false ) use ( $somvio_render_sparkle ) {
	$class = 'somvio-hero__ratings';
	$prefix = $duplicate ? 'dup' : 'main';

	if ( $duplicate ) {
		$class .= ' somvio-hero__ratings--duplicate';
	}
	?>
	<ul class="<?php echo esc_attr( $class ); ?>"<?php echo $duplicate ? ' aria-hidden="true"' : ''; ?>>
		<li class="somvio-hero__ratings-sep" aria-hidden="true">
			<?php echo $somvio_render_sparkle( $prefix . '-1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted local SVG. ?>
		</li>

		<li class="somvio-hero__rating">
			<img
				class="somvio-hero__rating-logo somvio-hero__rating-logo--google"
				src="<?php echo esc_url( $icons_uri . '/logo-google.svg' ); ?>"
				alt="<?php esc_attr_e( 'Google', 'somvio' ); ?>"
				width="50"
				height="50"
				loading="lazy"
				decoding="async"
			>
			<div class="somvio-hero__rating-meta">
				<span class="somvio-hero__rating-score">5/5</span>
				<span class="somvio-hero__rating-count"><?php esc_html_e( '636+ reviews', 'somvio' ); ?></span>
			</div>
		</li>

		<li class="somvio-hero__ratings-sep" aria-hidden="true">
			<?php echo $somvio_render_sparkle( $prefix . '-2' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted local SVG. ?>
		</li>

		<li class="somvio-hero__rating">
			<img
				class="somvio-hero__rating-logo somvio-hero__rating-logo--yelp"
				src="<?php echo esc_url( $icons_uri . '/logo-yelp.svg' ); ?>"
				alt="<?php esc_attr_e( 'Yelp', 'somvio' ); ?>"
				width="41"
				height="50"
				loading="lazy"
				decoding="async"
			>
			<div class="somvio-hero__rating-meta">
				<span class="somvio-hero__rating-score">4.7/5</span>
				<span class="somvio-hero__rating-count"><?php esc_html_e( '33+ reviews', 'somvio' ); ?></span>
			</div>
		</li>

		<li class="somvio-hero__ratings-sep" aria-hidden="true">
			<?php echo $somvio_render_sparkle( $prefix . '-3' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted local SVG. ?>
		</li>

		<li class="somvio-hero__rating">
			<img
				class="somvio-hero__rating-logo somvio-hero__rating-logo--trustpilot"
				src="<?php echo esc_url( $icons_uri . '/logo-trustpilot.svg' ); ?>"
				alt="<?php esc_attr_e( 'Trustpilot', 'somvio' ); ?>"
				width="52"
				height="50"
				loading="lazy"
				decoding="async"
			>
			<div class="somvio-hero__rating-meta">
				<span class="somvio-hero__rating-score">4.9/5</span>
				<span class="somvio-hero__rating-count"><?php esc_html_e( '76+ reviews', 'somvio' ); ?></span>
			</div>
		</li>
	</ul>
	<?php
};
?>
<section class="somvio-hero" aria-label="<?php esc_attr_e( 'Introduction', 'somvio' ); ?>">
	<div class="somvio-hero__bg" aria-hidden="true"></div>

	<div class="somvio-hero__inner">
		<div class="somvio-hero__grid">
			<div class="somvio-hero__content">
				<h1 class="somvio-hero__title"><?php esc_html_e( 'Professional Cleaning Services', 'somvio' ); ?></h1>

				<p class="somvio-hero__text">
					<?php esc_html_e( 'Clean spaces. Better living. High-quality cleaning services for homes and businesses across the UK.', 'somvio' ); ?>
				</p>

				<div class="somvio-hero__actions">
					<a class="btn btn--primary btn--md" href="<?php echo $somvio_book_url; ?>">
						<span class="btn__label"><?php esc_html_e( 'Get Instant Quote', 'somvio' ); ?></span>
					</a>
					<a class="btn btn--outline btn--md" href="<?php echo $somvio_services; ?>">
						<span class="btn__label"><?php esc_html_e( 'Our Services', 'somvio' ); ?></span>
					</a>
				</div>
			</div>

			<aside class="somvio-hero__quote quote-card" aria-label="<?php esc_attr_e( 'Get Your Instant Quote', 'somvio' ); ?>">
				<h2 class="quote-card__title"><?php esc_html_e( 'Get Your Instant Quote', 'somvio' ); ?></h2>

				<form class="quote-card__form" action="#" method="get" novalidate>
					<div class="quote-card__field quote-card__field--full">
						<label class="quote-card__label" for="somvio-quote-service">
							<?php esc_html_e( 'Service Type', 'somvio' ); ?>
						</label>
						<div class="quote-card__select-wrap">
							<select class="quote-card__select" id="somvio-quote-service" name="service_type" disabled>
								<option selected><?php esc_html_e( 'Regular Cleaning', 'somvio' ); ?></option>
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
						<label class="quote-card__label" for="somvio-quote-property">
							<?php esc_html_e( 'Property Type:', 'somvio' ); ?>
						</label>
						<div class="quote-card__select-wrap">
							<select class="quote-card__select" id="somvio-quote-property" name="property_type" disabled>
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
							<label class="quote-card__label" for="somvio-quote-bedrooms">
								<?php esc_html_e( 'Bedrooms', 'somvio' ); ?>
							</label>
							<div class="quote-card__select-wrap">
								<select class="quote-card__select" id="somvio-quote-bedrooms" name="bedrooms" disabled>
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
							<label class="quote-card__label" for="somvio-quote-bathrooms">
								<?php esc_html_e( 'Bathrooms', 'somvio' ); ?>
							</label>
							<div class="quote-card__select-wrap">
								<select class="quote-card__select" id="somvio-quote-bathrooms" name="bathrooms" disabled>
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

		<div class="somvio-hero__reviews">
			<p class="somvio-hero__reviews-label">
				<span class="somvio-hero__reviews-label-line"><?php esc_html_e( 'Top rated NYC', 'somvio' ); ?></span>
				<span class="somvio-hero__reviews-label-line"><?php esc_html_e( 'Cleaning Services', 'somvio' ); ?></span>
				<span class="somvio-hero__reviews-label-line"><?php esc_html_e( 'All review', 'somvio' ); ?></span>
			</p>

			<div class="somvio-hero__ratings-viewport">
				<div class="somvio-hero__ratings-track">
					<?php $somvio_render_hero_ratings( $somvio_icons_uri ); ?>
					<?php $somvio_render_hero_ratings( $somvio_icons_uri, true ); ?>
				</div>
			</div>
		</div>
	</div>
</section>
