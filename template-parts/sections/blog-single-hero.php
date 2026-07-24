<?php
/**
 * Single blog post hero — Figma 300:2415.
 *
 * Structure: breadcrumbs → H1 → date. Featured image preferred;
 * hard-fallback to assets/images/blog-single-hero-bg.jpg.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_post_id = get_queried_object_id();

if ( ! $somvio_post_id || 'post' !== get_post_type( $somvio_post_id ) ) {
	return;
}

$somvio_home_url = esc_url( home_url( '/' ) );
$somvio_blog_id  = (int) get_option( 'page_for_posts' );
$somvio_blog_url = $somvio_blog_id
	? esc_url( (string) get_permalink( $somvio_blog_id ) )
	: esc_url( home_url( '/blog/' ) );
$somvio_title = get_the_title( $somvio_post_id );
$somvio_date  = get_the_date( 'F j, Y', $somvio_post_id );

// Featured image first; hard-fallback to local hero asset.
$somvio_bg_uri  = (string) get_the_post_thumbnail_url( $somvio_post_id, 'full' );
$somvio_bg_path = get_stylesheet_directory() . '/assets/images/blog-single-hero-bg.jpg';

if ( '' === $somvio_bg_uri && file_exists( $somvio_bg_path ) ) {
	$somvio_bg_uri  = get_stylesheet_directory_uri() . '/assets/images/blog-single-hero-bg.jpg';
	$somvio_bg_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bg_path ) );
}

$somvio_section_attrs = '';

if ( $somvio_bg_uri ) {
	$somvio_section_attrs = ' style="--blog-single-hero-bg: url(\'' . esc_url( $somvio_bg_uri ) . '\');"';
}
?>
<section
	class="blog-single-hero"
	aria-label="<?php echo esc_attr( $somvio_title ); ?>"
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- style value uses esc_url().
	echo $somvio_section_attrs;
	?>
>
	<div class="blog-single-hero__media" aria-hidden="true"></div>
	<div class="blog-single-hero__bg" aria-hidden="true"></div>

	<div class="blog-single-hero__inner">
		<nav
			class="blog-single-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="blog-single-hero__breadcrumb-list">
				<li class="blog-single-hero__breadcrumb-item">
					<a class="blog-single-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li class="blog-single-hero__breadcrumb-item">
					<span class="blog-single-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<a class="blog-single-hero__breadcrumb-link" href="<?php echo $somvio_blog_url; ?>">
						<?php esc_html_e( 'Blog', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="blog-single-hero__breadcrumb-item blog-single-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="blog-single-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="blog-single-hero__breadcrumb-current">
						<?php echo esc_html( $somvio_title ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="blog-single-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php echo esc_html( $somvio_title ); ?>
		</h1>

		<?php if ( $somvio_date ) : ?>
			<p class="blog-single-hero__date reveal-on-scroll" style="--reveal-delay: 0.1s;">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $somvio_post_id ) ); ?>">
					<?php echo esc_html( $somvio_date ); ?>
				</time>
			</p>
		<?php endif; ?>
	</div>
</section>
