<?php
/**
 * Single blog post content + Instant Quote sidebar — Figma 300:2421.
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

$somvio_blog_url    = (int) get_option( 'page_for_posts' )
	? esc_url( (string) get_permalink( (int) get_option( 'page_for_posts' ) ) )
	: esc_url( home_url( '/blog/' ) );

// Article image: local Figma export, else featured image.
$somvio_img_path = get_stylesheet_directory() . '/assets/images/blog-single-content.png';
$somvio_img_uri  = '';
$somvio_img_alt  = get_the_title( $somvio_post_id );

if ( file_exists( $somvio_img_path ) ) {
	$somvio_img_uri  = get_stylesheet_directory_uri() . '/assets/images/blog-single-content.png';
	$somvio_img_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_img_path ) );
} elseif ( has_post_thumbnail( $somvio_post_id ) ) {
	$somvio_img_uri = (string) get_the_post_thumbnail_url( $somvio_post_id, 'large' );
	$somvio_thumb_id = get_post_thumbnail_id( $somvio_post_id );
	$somvio_alt_meta = $somvio_thumb_id ? get_post_meta( $somvio_thumb_id, '_wp_attachment_image_alt', true ) : '';
	if ( is_string( $somvio_alt_meta ) && '' !== $somvio_alt_meta ) {
		$somvio_img_alt = $somvio_alt_meta;
	}
}

$somvio_related = array(
	__( '7 Benefits of Hiring a Professional Cleaning Service', 'somvio' ),
	__( 'Deep Cleaning vs Regular Cleaning: What\'s the Difference?', 'somvio' ),
	__( 'How to Prepare Your Home Before the Cleaners Arrive', 'somvio' ),
	__( 'The Health Benefits of a Clean Home', 'somvio' ),
);

$somvio_post = get_post( $somvio_post_id );
?>
<section
	class="blog-single-content"
	aria-label="<?php esc_attr_e( 'Article content', 'somvio' ); ?>"
>
	<div class="blog-single-content__inner">
		<?php
		get_template_part(
			'template-parts/components/quote',
			'calculator',
			array(
				'variant' => 'solid',
				'id'      => 'somvio-instant-quote',
				'class'   => 'blog-single-content__quote reveal-on-scroll',
			)
		);
		?>

		<article class="blog-single-content__article reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php if ( $somvio_img_uri ) : ?>
				<figure class="blog-single-content__media">
					<img
						class="blog-single-content__image"
						src="<?php echo esc_url( $somvio_img_uri ); ?>"
						alt="<?php echo esc_attr( $somvio_img_alt ); ?>"
						width="770"
						height="463"
						loading="lazy"
						decoding="async"
					>
				</figure>
			<?php endif; ?>

			<div class="blog-single-content__entry entry-content">
				<?php
				if ( $somvio_post instanceof WP_Post ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content filters apply.
					echo apply_filters( 'the_content', $somvio_post->post_content );
				}
				?>
			</div>

			<nav
				class="blog-single-content__related"
				aria-label="<?php esc_attr_e( 'Related Articles', 'somvio' ); ?>"
			>
				<h2 class="blog-single-content__related-title">
					<?php esc_html_e( 'Related Articles', 'somvio' ); ?>
				</h2>
				<ul class="blog-single-content__related-list">
					<?php foreach ( $somvio_related as $somvio_related_title ) : ?>
						<li class="blog-single-content__related-item">
							<a class="blog-single-content__related-link" href="<?php echo $somvio_blog_url; ?>">
								<?php echo esc_html( $somvio_related_title ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</article>
	</div>
</section>
