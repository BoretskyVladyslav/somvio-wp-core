<?php
/**
 * Somvio site header markup.
 *
 * Figma: 300:1531 (desktop), 300:1533 (dropdown), 300:2716 (mobile).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url  = esc_url( home_url( '/' ) );
$somvio_site_name = get_bloginfo( 'name', 'display' );
$somvio_book_url  = somvio_get_book_now_url();
$somvio_logo_src  = esc_url( get_stylesheet_directory_uri() . '/assets/images/logo.svg' );
$somvio_phone     = somvio_get_phone();
?>
<header id="masthead" class="site-header somvio-header" role="banner">
	<div class="inside-header somvio-header__inner">
		<div class="somvio-header__brand">
			<a class="somvio-header__logo" href="<?php echo $somvio_home_url; ?>">
				<img
					class="somvio-logo"
					src="<?php echo $somvio_logo_src; ?>"
					alt="<?php echo esc_attr( $somvio_site_name ); ?>"
					width="222"
					height="65"
					loading="eager"
					decoding="async"
				>
			</a>
		</div>

		<div class="somvio-header__end">
			<nav
				id="somvio-header-nav"
				class="somvio-header__nav"
				aria-label="<?php esc_attr_e( 'Primary', 'somvio' ); ?>"
			>
				<div class="somvio-header__drawer">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'somvio-header__menu',
							'fallback_cb'    => 'somvio_header_menu_fallback',
							'depth'          => 2,
						)
					);
					?>

					<div class="somvio-header__drawer-actions">
						<a
							class="somvio-header__phone"
							href="<?php echo esc_url( $somvio_phone['href'] ); ?>"
						>
							<?php echo esc_html( $somvio_phone['display'] ); ?>
						</a>

						<a class="btn btn--outline btn--sm btn--has-icon somvio-header__cta somvio-header__cta--drawer" href="<?php echo $somvio_book_url; ?>">
							<span class="btn__label"><?php esc_html_e( 'Book Now', 'somvio' ); ?></span>
							<span class="btn__icon" aria-hidden="true">
								<?php
								// Trusted local theme SVG from assets/icons/.
								echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						</a>
					</div>
				</div>
			</nav>

			<div class="somvio-header__actions">
				<a
					class="somvio-header__phone somvio-header__phone--desktop"
					href="<?php echo esc_url( $somvio_phone['href'] ); ?>"
				>
					<?php echo esc_html( $somvio_phone['display'] ); ?>
				</a>

				<a
					class="somvio-header__icon-btn somvio-header__phone-btn"
					href="<?php echo esc_url( $somvio_phone['href'] ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: phone number */ __( 'Call %s', 'somvio' ), $somvio_phone['display'] ) ); ?>"
				>
					<span class="somvio-header__icon-btn-icon" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</a>

				<a class="btn btn--outline btn--sm btn--has-icon somvio-header__cta somvio-header__cta--desktop" href="<?php echo $somvio_book_url; ?>">
					<span class="btn__label"><?php esc_html_e( 'Book Now', 'somvio' ); ?></span>
					<span class="btn__icon" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</a>

				<button
					class="somvio-header__icon-btn somvio-header__toggle"
					type="button"
					aria-controls="somvio-header-nav"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Open menu', 'somvio' ); ?>"
				>
					<span class="somvio-header__icon-btn-icon" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-burger-menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</button>
			</div>
		</div>
	</div>

	<button
		class="somvio-header__backdrop"
		type="button"
		tabindex="-1"
		aria-hidden="true"
		aria-label="<?php esc_attr_e( 'Close menu', 'somvio' ); ?>"
	></button>
</header>
