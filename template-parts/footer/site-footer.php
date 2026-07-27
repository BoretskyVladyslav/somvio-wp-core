<?php
/**
 * Global site footer markup — Figma 325:5030 grid.
 *
 * Columns: Brand | Navigation | Legal & Policy | Contact.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url  = home_url( '/' );
$somvio_site_name = get_bloginfo( 'name', 'display' );
$somvio_logo_src  = get_stylesheet_directory_uri() . '/assets/images/logo.svg';
$somvio_phone     = function_exists( 'somvio_get_phone' ) ? somvio_get_phone() : array(
	'display' => '+44 7402 495410',
	'href'    => 'tel:+447402495410',
);
$somvio_email     = function_exists( 'somvio_get_email' ) ? somvio_get_email() : array(
	'display' => 'Info@somvio.co.uk',
	'href'    => 'mailto:Info@somvio.co.uk',
);
$somvio_location  = function_exists( 'somvio_get_location' ) ? somvio_get_location() : __( 'Glasgow, United Kingdom', 'somvio' );
$somvio_whatsapp  = function_exists( 'somvio_get_whatsapp_url' ) ? somvio_get_whatsapp_url() : 'https://wa.me/447402495410';
$somvio_socials   = function_exists( 'somvio_get_social_links' ) ? somvio_get_social_links() : array();

$somvio_nav_links = array(
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
		'label' => __( 'Blog', 'somvio' ),
		'url'   => home_url( '/blog/' ),
	),
	array(
		'label' => __( 'FAQ', 'somvio' ),
		'url'   => home_url( '/faq/' ),
	),
	array(
		'label' => __( 'Booking', 'somvio' ),
		'url'   => home_url( '/booking/' ),
	),
);

$somvio_legal_links = array(
	array(
		'label' => __( 'Privacy Policy', 'somvio' ),
		'url'   => home_url( '/privacy-policy/' ),
	),
	array(
		'label' => __( 'Terms & Conditions', 'somvio' ),
		'url'   => home_url( '/terms-conditions/' ),
	),
	array(
		'label' => __( 'Cookie Policy', 'somvio' ),
		'url'   => home_url( '/cookie-policy/' ),
	),
	array(
		'label' => __( 'Cancellation Policy', 'somvio' ),
		'url'   => home_url( '/cancellation-policy/' ),
	),
	array(
		'label' => __( 'Master Legal Index', 'somvio' ),
		'url'   => home_url( '/legal/' ),
	),
);

$somvio_bottom_legal = array_slice( $somvio_legal_links, 0, 2 );
$somvio_year         = (int) gmdate( 'Y' );
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

			<nav class="site-footer__col site-footer__col--nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'somvio' ); ?>">
				<p class="site-footer__heading"><?php esc_html_e( 'Navigation', 'somvio' ); ?></p>
				<ul class="site-footer__list">
					<?php foreach ( $somvio_nav_links as $item ) : ?>
						<li class="site-footer__list-item">
							<a class="site-footer__link" href="<?php echo esc_url( $item['url'] ); ?>">
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<nav class="site-footer__col site-footer__col--legal" aria-label="<?php esc_attr_e( 'Legal & Policy', 'somvio' ); ?>">
				<p class="site-footer__heading"><?php esc_html_e( 'Legal & Policy', 'somvio' ); ?></p>
				<ul class="site-footer__list">
					<?php foreach ( $somvio_legal_links as $item ) : ?>
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
								echo somvio_get_icon( 'icon-location' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<span class="site-footer__contact-text"><?php echo esc_html( $somvio_location ); ?></span>
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
								echo somvio_get_icon( 'icon-phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<a class="site-footer__contact-text" href="<?php echo esc_url( $somvio_phone['href'] ); ?>">
							<?php echo esc_html( $somvio_phone['display'] ); ?>
						</a>
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

			<a
				class="somvio-developer-credit"
				href="https://michaelstudioo.com.ua"
				target="_blank"
				rel="noopener noreferrer"
			>
				<span class="somvio-developer-credit__label"><?php esc_html_e( 'Developed by', 'somvio' ); ?></span>
				<span class="somvio-developer-credit__brand">
					<span class="somvio-developer-credit__logo" aria-hidden="true">
						<?php
						$somvio_ms_logo = get_stylesheet_directory() . '/assets/icons/logo-michael-studioo.svg';
						if ( file_exists( $somvio_ms_logo ) ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted local SVG asset.
							echo file_get_contents( $somvio_ms_logo );
						}
						?>
					</span>
					<span class="somvio-developer-credit__name">Michael Studioo</span>
				</span>
			</a>

			<p class="site-footer__legal">
				<?php foreach ( $somvio_bottom_legal as $index => $item ) : ?>
					<?php if ( $index > 0 ) : ?>
						<span class="site-footer__legal-sep" aria-hidden="true">|</span>
					<?php endif; ?>
					<a class="site-footer__legal-link" href="<?php echo esc_url( $item['url'] ); ?>">
						<?php echo esc_html( $item['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</p>
		</div>
	</div>
</footer>
