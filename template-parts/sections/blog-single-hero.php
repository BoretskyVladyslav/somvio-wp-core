<?php
/**
 * Single blog post hero — Figma 300:2415.
 *
 * Local bg: assets/images/blog-single-hero-bg.jpg (do not export from Figma).
 * Falls back to the post featured image when the local file is missing.
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

$somvio_home_url  = esc_url( home_url( '/' ) );
$somvio_blog_id   = (int) get_option( 'page_for_posts' );
$somvio_blog_url  = $somvio_blog_id
	? esc_url( (string) get_permalink( $somvio_blog_id ) )
	: esc_url( home_url( '/blog/' ) );
$somvio_title     = get_the_title( $somvio_post_id );
$somvio_date      = get_the_date( '', $somvio_post_id );
$somvio_author_id = (int) get_post_field( 'post_author', $somvio_post_id );
$somvio_author    = $somvio_author_id ? get_the_author_meta( 'display_name', $somvio_author_id ) : '';

$somvio_categories = get_the_category( $somvio_post_id );
$somvio_category   = ! empty( $somvio_categories[0] ) ? $somvio_categories[0]->name : '';

$somvio_word_count = str_word_count(
	wp_strip_all_tags( (string) get_post_field( 'post_content', $somvio_post_id ) )
);
$somvio_read_mins  = max( 1, (int) ceil( $somvio_word_count / 200 ) );
$somvio_read_label = sprintf(
	/* translators: %d: estimated reading time in minutes */
	_n( '%d min read', '%d min read', $somvio_read_mins, 'somvio' ),
	$somvio_read_mins
);

// Prefer local hero asset; fall back to featured image. Never export from Figma.
$somvio_bg_path = get_stylesheet_directory() . '/assets/images/blog-single-hero-bg.jpg';
$somvio_bg_uri  = '';

if ( file_exists( $somvio_bg_path ) ) {
	$somvio_bg_uri = get_stylesheet_directory_uri() . '/assets/images/blog-single-hero-bg.jpg';
	$somvio_bg_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bg_path ) );
} elseif ( has_post_thumbnail( $somvio_post_id ) ) {
	$somvio_thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $somvio_post_id ), 'full' );
	if ( $somvio_thumb ) {
		$somvio_bg_uri = $somvio_thumb;
	}
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

		<?php if ( '' !== $somvio_category ) : ?>
			<p class="blog-single-hero__badge reveal-on-scroll" style="--reveal-delay: 0.04s;">
				<?php echo esc_html( $somvio_category ); ?>
			</p>
		<?php endif; ?>

		<h1 class="blog-single-hero__title reveal-on-scroll" style="--reveal-delay: 0.08s;">
			<?php echo esc_html( $somvio_title ); ?>
		</h1>

		<div class="blog-single-hero__meta reveal-on-scroll" style="--reveal-delay: 0.12s;">
			<?php if ( $somvio_author_id && '' !== $somvio_author ) : ?>
				<span class="blog-single-hero__author">
					<?php
					echo get_avatar(
						$somvio_author_id,
						40,
						'',
						$somvio_author,
						array(
							'class' => 'blog-single-hero__avatar',
						)
					);
					?>
					<span class="blog-single-hero__author-name"><?php echo esc_html( $somvio_author ); ?></span>
				</span>
				<span class="blog-single-hero__meta-sep" aria-hidden="true"></span>
			<?php endif; ?>

			<?php if ( $somvio_date ) : ?>
				<time
					class="blog-single-hero__date"
					datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $somvio_post_id ) ); ?>"
				>
					<?php echo esc_html( $somvio_date ); ?>
				</time>
				<span class="blog-single-hero__meta-sep" aria-hidden="true"></span>
			<?php endif; ?>

			<span class="blog-single-hero__read-time"><?php echo esc_html( $somvio_read_label ); ?></span>
		</div>
	</div>
</section>
