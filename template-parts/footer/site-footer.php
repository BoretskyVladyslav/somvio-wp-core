<?php
/**
 * Global site footer markup — Figma 325:5030.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url  = home_url( '/' );
$somvio_site_name = get_bloginfo( 'name', 'display' );
$somvio_logo_src  = get_stylesheet_directory_uri() . '/assets/images/logo.svg';
$somvio_phone    = function_exists( 'somvio_get_phone' ) ? somvio_get_phone() : array(
	'display' => '+44 7402 495410',
	'href'    => 'tel:+447402495410',
);
$somvio_email    = function_exists( 'somvio_get_email' ) ? somvio_get_email() : array(
	'display' => 'Info@somvio.co.uk',
	'href'    => 'mailto:Info@somvio.co.uk',
);
$somvio_location = function_exists( 'somvio_get_location' ) ? somvio_get_location() : __( 'London, United Kingdom', 'somvio' );
$somvio_whatsapp = function_exists( 'somvio_get_whatsapp_url' ) ? somvio_get_whatsapp_url() : 'https://wa.me/447402495410';
$somvio_socials  = function_exists( 'somvio_get_social_links' ) ? somvio_get_social_links() : array();

$somvio_services = array();

if ( function_exists( 'somvio_get_single_service_pages' ) ) {
	foreach ( somvio_get_single_service_pages() as $slug => $label ) {
		$somvio_services[] = array(
			'label' => $label,
			'url'   => function_exists( 'somvio_get_service_page_url' )
				? somvio_get_service_page_url( $slug )
				: home_url( '/services/' . $slug . '/' ),
		);
	}
} else {
	$somvio_services = array(
		array(
			'label' => __( 'Regular Cleaning', 'somvio' ),
			'url'   => home_url( '/services/regular-cleaning/' ),
		),
		array(
			'label' => __( 'Deep Cleaning', 'somvio' ),
			'url'   => home_url( '/services/deep-cleaning/' ),
		),
		array(
			'label' => __( 'End of Tenancy', 'somvio' ),
			'url'   => home_url( '/services/end-of-tenancy/' ),
		),
		array(
			'label' => __( 'Airbnb Cleaning', 'somvio' ),
			'url'   => home_url( '/services/airbnb-cleaning/' ),
		),
		array(
			'label' => __( 'After Builders', 'somvio' ),
			'url'   => home_url( '/services/after-builders/' ),
		),
	);
}

$somvio_quick_links = array(
	array(
		'label' => __( 'Home', 'somvio' ),
		'url'   => home_url( '/' ),
	),
	array(
		'label' => __( 'Services', 'somvio' ),
		'url'   => home_url( '/services/' ),
	),
	array(
		'label' => __( 'About Us', 'somvio' ),
		'url'   => home_url( '/about-us/' ),
	),
	array(
		'label' => __( 'Reviews', 'somvio' ),
		'url'   => home_url( '/reviews/' ),
	),
	array(
		'label' => __( 'FAQ', 'somvio' ),
		'url'   => home_url( '/faq/' ),
	),
	array(
		'label' => __( 'Contact', 'somvio' ),
		'url'   => home_url( '/contact/' ),
	),
);

$somvio_year = (int) gmdate( 'Y' );
?>
<footer class="site-footer" role="contentinfo">
	<div class="site-footer__inner">
		<div class="site-footer__grid">
			<div class="site-footer__col site-footer__col--brand">
				<a class="site-footer__logo" href="<?php echo esc_url( $somvio_home_url ); ?>">
					<img
						src="<?php echo esc_url( $somvio_logo_src ); ?>"
						alt="<?php echo esc_attr( $somvio_site_name ); ?>"
						width="189"
						height="55"
						loading="lazy"
						decoding="async"
					>
				</a>
				<p class="site-footer__tagline">
					<?php esc_html_e( 'Professional cleaning services you can rely on.', 'somvio' ); ?>
				</p>
				<?php if ( ! empty( $somvio_socials ) ) : ?>
					<ul class="site-footer__social">
						<?php foreach ( $somvio_socials as $social ) : ?>
							<li class="site-footer__social-item">
								<a
									class="site-footer__social-link"
									href="<?php echo esc_url( $social['url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									aria-label="<?php echo esc_attr( $social['label'] ); ?>"
								>
									<span class="site-footer__social-icon" aria-hidden="true">
										<?php
										if ( function_exists( 'somvio_get_icon' ) ) {
											echo somvio_get_icon( $social['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}
										?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<nav class="site-footer__col site-footer__col--services" aria-label="<?php esc_attr_e( 'Services', 'somvio' ); ?>">
				<p class="site-footer__heading"><?php esc_html_e( 'Services', 'somvio' ); ?></p>
				<ul class="site-footer__list">
					<?php foreach ( $somvio_services as $item ) : ?>
						<li class="site-footer__list-item">
							<a class="site-footer__link" href="<?php echo esc_url( $item['url'] ); ?>">
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<nav class="site-footer__col site-footer__col--links" aria-label="<?php esc_attr_e( 'Quick Links', 'somvio' ); ?>">
				<p class="site-footer__heading"><?php esc_html_e( 'Quick Links', 'somvio' ); ?></p>
				<ul class="site-footer__list">
					<?php foreach ( $somvio_quick_links as $item ) : ?>
						<li class="site-footer__list-item">
							<a class="site-footer__link" href="<?php echo esc_url( $item['url'] ); ?>">
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<div class="site-footer__col site-footer__col--contact">
				<p class="site-footer__heading"><?php esc_html_e( 'Contact', 'somvio' ); ?></p>
				<ul class="site-footer__contact">
					<li class="site-footer__contact-item">
						<span class="site-footer__contact-icon" aria-hidden="true">
							<?php
							if ( function_exists( 'somvio_get_icon' ) ) {
								echo somvio_get_icon( 'icon-phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<a class="site-footer__contact-text" href="<?php echo esc_url( $somvio_phone['href'] ); ?>">
							<?php echo esc_html( $somvio_phone['display'] ); ?>
						</a>
					</li>
					<li class="site-footer__contact-item">
						<span class="site-footer__contact-icon" aria-hidden="true">
							<?php
							if ( function_exists( 'somvio_get_icon' ) ) {
								echo somvio_get_icon( 'icon-email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<a class="site-footer__contact-text" href="<?php echo esc_url( $somvio_email['href'] ); ?>">
							<?php echo esc_html( $somvio_email['display'] ); ?>
						</a>
					</li>
					<li class="site-footer__contact-item">
						<span class="site-footer__contact-icon" aria-hidden="true">
							<?php
							if ( function_exists( 'somvio_get_icon' ) ) {
								echo somvio_get_icon( 'icon-location' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<span class="site-footer__contact-text"><?php echo esc_html( $somvio_location ); ?></span>
					</li>
				</ul>
				<a
					class="btn btn--outline btn--sm site-footer__whatsapp"
					href="<?php echo esc_url( $somvio_whatsapp ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span class="btn__label"><?php esc_html_e( 'WhatsApp Us', 'somvio' ); ?></span>
				</a>
				<p class="site-footer__whatsapp-hint">
					<?php esc_html_e( 'Prefer to text us for a quick response.', 'somvio' ); ?>
				</p>
			</div>
		</div>

		<div class="site-footer__bottom">
			<p class="site-footer__copyright">
				<?php
				printf(
					/* translators: %d: current year */
					esc_html__( '© %d Somvio. All rights reserved.', 'somvio' ),
					$somvio_year
				);
				?>
			</p>
			<p class="site-footer__legal">
				<a class="site-footer__legal-link" href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">
					<?php esc_html_e( 'Privacy Policy', 'somvio' ); ?>
				</a>
				<span class="site-footer__legal-sep" aria-hidden="true">|</span>
				<a class="site-footer__legal-link" href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>">
					<?php esc_html_e( 'Terms & Conditions', 'somvio' ); ?>
				</a>
			</p>
		</div>
	</div>
</footer>
