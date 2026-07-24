<?php
/**
 * Blog featured + categories + posts grid — Figma 300:2187.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a theme blog image URI with filemtime cache-bust.
 *
 * @param string $filename File under assets/images/blog/.
 * @return array{path:string,uri:string,exists:bool}
 */
$somvio_blog_image = static function ( $filename ) {
	$filename = ltrim( (string) $filename, '/' );
	$path     = get_stylesheet_directory() . '/assets/images/blog/' . $filename;
	$uri      = get_stylesheet_directory_uri() . '/assets/images/blog/' . $filename;

	if ( file_exists( $path ) ) {
		$uri .= '?v=' . rawurlencode( (string) filemtime( $path ) );
		return array(
			'path'   => $path,
			'uri'    => $uri,
			'exists' => true,
		);
	}

	return array(
		'path'   => $path,
		'uri'    => $uri,
		'exists' => false,
	);
};

$somvio_post_title   = __( 'How Often Should You Schedule Professional Cleaning?', 'somvio' );
$somvio_post_excerpt = __( 'Regular professional cleaning helps maintain a healthier living environment and extends the life of your furniture and flooring.', 'somvio' );
$somvio_post_date    = __( 'December 16, 2024', 'somvio' );
$somvio_read_now     = __( 'Read now', 'somvio' );

/**
 * Resolve a real published post permalink for demo card links (single.php).
 * Prefer hello-world, else the latest published post.
 */
$somvio_demo_post = get_page_by_path( 'hello-world', OBJECT, 'post' );

if ( ! $somvio_demo_post instanceof WP_Post ) {
	$somvio_demo_posts = get_posts(
		array(
			'numberposts'      => 1,
			'post_status'      => 'publish',
			'post_type'        => 'post',
			'suppress_filters' => true,
		)
	);
	$somvio_demo_post = ! empty( $somvio_demo_posts[0] ) ? $somvio_demo_posts[0] : null;
}

$somvio_post_url = ( $somvio_demo_post instanceof WP_Post )
	? (string) get_permalink( $somvio_demo_post )
	: home_url( '/hello-world/' );

$somvio_featured = array(
	array(
		'image'   => 'featured-1.png',
		'layout'  => 'featured',
		'date'    => $somvio_post_date,
		'title'   => $somvio_post_title,
		'excerpt' => $somvio_post_excerpt,
		'url'     => $somvio_post_url,
	),
	array(
		'image'   => 'featured-2.png',
		'layout'  => 'horizontal',
		'date'    => $somvio_post_date,
		'title'   => $somvio_post_title,
		'excerpt' => $somvio_post_excerpt,
		'url'     => $somvio_post_url,
	),
	array(
		'image'   => 'featured-3.png',
		'layout'  => 'horizontal',
		'date'    => $somvio_post_date,
		'title'   => $somvio_post_title,
		'excerpt' => $somvio_post_excerpt,
		'url'     => $somvio_post_url,
	),
);

$somvio_categories = array(
	array(
		'id'      => 'all',
		'label'   => __( 'All Posts', 'somvio' ),
		'active'  => true,
	),
	array(
		'id'      => 'cleaning-tips',
		'label'   => __( 'Cleaning Tips', 'somvio' ),
		'active'  => false,
	),
	array(
		'id'      => 'home-care',
		'label'   => __( 'Home Care', 'somvio' ),
		'active'  => false,
	),
	array(
		'id'      => 'eco-living',
		'label'   => __( 'Eco Living', 'somvio' ),
		'active'  => false,
	),
);

$somvio_posts = array(
	array(
		'image'      => 'post-1.png',
		'category'   => 'cleaning-tips',
		'date'       => $somvio_post_date,
		'title'      => $somvio_post_title,
		'excerpt'    => $somvio_post_excerpt,
		'url'        => $somvio_post_url,
	),
	array(
		'image'      => 'post-2.png',
		'category'   => 'home-care',
		'date'       => $somvio_post_date,
		'title'      => $somvio_post_title,
		'excerpt'    => $somvio_post_excerpt,
		'url'        => $somvio_post_url,
	),
	array(
		'image'      => 'post-3.png',
		'category'   => 'eco-living',
		'date'       => $somvio_post_date,
		'title'      => $somvio_post_title,
		'excerpt'    => $somvio_post_excerpt,
		'url'        => $somvio_post_url,
	),
	array(
		'image'      => 'post-4.png',
		'category'   => 'cleaning-tips',
		'date'       => $somvio_post_date,
		'title'      => $somvio_post_title,
		'excerpt'    => $somvio_post_excerpt,
		'url'        => $somvio_post_url,
	),
	array(
		'image'      => 'post-5.png',
		'category'   => 'home-care',
		'date'       => $somvio_post_date,
		'title'      => $somvio_post_title,
		'excerpt'    => $somvio_post_excerpt,
		'url'        => $somvio_post_url,
	),
	array(
		'image'      => 'post-6.png',
		'category'   => 'eco-living',
		'date'       => $somvio_post_date,
		'title'      => $somvio_post_title,
		'excerpt'    => $somvio_post_excerpt,
		'url'        => $somvio_post_url,
	),
);
?>
<section class="blog-grid" aria-label="<?php esc_attr_e( 'Blog articles', 'somvio' ); ?>">
	<div class="blog-grid__inner">
		<div class="blog-grid__featured" data-blog-featured>
			<?php
			$somvio_featured_main = $somvio_featured[0];
			$somvio_featured_side = array_slice( $somvio_featured, 1 );
			$somvio_main_img      = $somvio_blog_image( $somvio_featured_main['image'] );
			?>
			<article class="blog-card blog-card--featured reveal-on-scroll">
				<?php if ( $somvio_main_img['exists'] ) : ?>
					<a class="blog-card__media" href="<?php echo esc_url( $somvio_featured_main['url'] ); ?>" tabindex="-1" aria-hidden="true">
						<img
							class="blog-card__image"
							src="<?php echo esc_url( $somvio_main_img['uri'] ); ?>"
							alt=""
							width="570"
							height="320"
							loading="eager"
							decoding="async"
						>
					</a>
				<?php endif; ?>
				<div class="blog-card__body">
					<p class="blog-card__date"><?php echo esc_html( $somvio_featured_main['date'] ); ?></p>
					<h2 class="blog-card__title">
						<a href="<?php echo esc_url( $somvio_featured_main['url'] ); ?>">
							<?php echo esc_html( $somvio_featured_main['title'] ); ?>
						</a>
					</h2>
					<p class="blog-card__excerpt"><?php echo esc_html( $somvio_featured_main['excerpt'] ); ?></p>
					<a class="blog-card__link" href="<?php echo esc_url( $somvio_featured_main['url'] ); ?>">
						<?php echo esc_html( $somvio_read_now ); ?>
					</a>
				</div>
			</article>

			<div class="blog-grid__featured-side">
				<?php foreach ( $somvio_featured_side as $somvio_side_index => $somvio_side_post ) : ?>
					<?php $somvio_side_img = $somvio_blog_image( $somvio_side_post['image'] ); ?>
					<article
						class="blog-card blog-card--horizontal reveal-on-scroll"
						style="--reveal-delay: <?php echo esc_attr( (string) ( 0.05 + ( $somvio_side_index * 0.05 ) ) ); ?>s;"
					>
						<?php if ( $somvio_side_img['exists'] ) : ?>
							<a class="blog-card__media" href="<?php echo esc_url( $somvio_side_post['url'] ); ?>" tabindex="-1" aria-hidden="true">
								<img
									class="blog-card__image"
									src="<?php echo esc_url( $somvio_side_img['uri'] ); ?>"
									alt=""
									width="285"
									height="220"
									loading="lazy"
									decoding="async"
								>
							</a>
						<?php endif; ?>
						<div class="blog-card__body">
							<p class="blog-card__date"><?php echo esc_html( $somvio_side_post['date'] ); ?></p>
							<h3 class="blog-card__title">
								<a href="<?php echo esc_url( $somvio_side_post['url'] ); ?>">
									<?php echo esc_html( $somvio_side_post['title'] ); ?>
								</a>
							</h3>
							<p class="blog-card__excerpt"><?php echo esc_html( $somvio_side_post['excerpt'] ); ?></p>
							<a class="blog-card__link" href="<?php echo esc_url( $somvio_side_post['url'] ); ?>">
								<?php echo esc_html( $somvio_read_now ); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="blog-grid__categories reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<h2 class="blog-grid__categories-title"><?php esc_html_e( 'Categories', 'somvio' ); ?></h2>
			<div
				class="blog-grid__filters"
				role="tablist"
				aria-label="<?php esc_attr_e( 'Filter posts by category', 'somvio' ); ?>"
				data-blog-filters
			>
				<?php foreach ( $somvio_categories as $somvio_cat ) : ?>
					<button
						type="button"
						class="blog-grid__filter<?php echo ! empty( $somvio_cat['active'] ) ? ' is-active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo ! empty( $somvio_cat['active'] ) ? 'true' : 'false'; ?>"
						data-blog-filter="<?php echo esc_attr( $somvio_cat['id'] ); ?>"
					>
						<?php echo esc_html( $somvio_cat['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="blog-grid__posts" data-blog-posts>
			<?php foreach ( $somvio_posts as $somvio_post_index => $somvio_post ) : ?>
				<?php $somvio_post_img = $somvio_blog_image( $somvio_post['image'] ); ?>
				<article
					class="blog-card blog-card--grid reveal-on-scroll"
					style="--reveal-delay: <?php echo esc_attr( (string) ( $somvio_post_index * 0.05 ) ); ?>s;"
					data-blog-category="<?php echo esc_attr( $somvio_post['category'] ); ?>"
				>
					<?php if ( $somvio_post_img['exists'] ) : ?>
						<a class="blog-card__media" href="<?php echo esc_url( $somvio_post['url'] ); ?>" tabindex="-1" aria-hidden="true">
							<img
								class="blog-card__image"
								src="<?php echo esc_url( $somvio_post_img['uri'] ); ?>"
								alt=""
								width="370"
								height="320"
								loading="lazy"
								decoding="async"
							>
						</a>
					<?php endif; ?>
					<div class="blog-card__body">
						<p class="blog-card__date"><?php echo esc_html( $somvio_post['date'] ); ?></p>
						<h3 class="blog-card__title">
							<a href="<?php echo esc_url( $somvio_post['url'] ); ?>">
								<?php echo esc_html( $somvio_post['title'] ); ?>
							</a>
						</h3>
						<p class="blog-card__excerpt"><?php echo esc_html( $somvio_post['excerpt'] ); ?></p>
						<a class="blog-card__link" href="<?php echo esc_url( $somvio_post['url'] ); ?>">
							<?php echo esc_html( $somvio_read_now ); ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="blog-grid__actions reveal-on-scroll" style="--reveal-delay: 0.15s;">
			<button type="button" class="btn btn--outline btn--md btn--has-icon blog-grid__load-more" data-blog-load-more>
				<span class="btn__label"><?php esc_html_e( 'Load more', 'somvio' ); ?></span>
				<span class="btn__icon" aria-hidden="true">
					<?php
					echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-refresh' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</span>
			</button>
		</div>
	</div>
</section>
