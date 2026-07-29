<?php
/**
 * Search / empty-results hero — branded dark fallback.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url   = esc_url( home_url( '/' ) );
$somvio_book_url   = function_exists( 'somvio_get_book_now_url' )
	? somvio_get_book_now_url()
	: esc_url( home_url( '/booking/' ) );
$somvio_query      = get_search_query();
$somvio_have_posts = have_posts();
?>
<section class="search-hero" aria-label="<?php esc_attr_e( 'Search results', 'somvio' ); ?>">
	<div class="search-hero__inner">
		<nav
			class="search-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="search-hero__breadcrumb-list">
				<li class="search-hero__breadcrumb-item">
					<a class="search-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="search-hero__breadcrumb-item search-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="search-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-arrow-right' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="search-hero__breadcrumb-current"><?php esc_html_e( 'Search', 'somvio' ); ?></span>
				</li>
			</ol>
		</nav>

		<h1 class="search-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php
			if ( $somvio_have_posts && '' !== $somvio_query ) {
				printf(
					/* translators: %s: search query */
					esc_html__( 'Results for “%s”', 'somvio' ),
					esc_html( $somvio_query )
				);
			} elseif ( '' !== $somvio_query ) {
				printf(
					/* translators: %s: search query */
					esc_html__( 'No results for “%s”', 'somvio' ),
					esc_html( $somvio_query )
				);
			} else {
				esc_html_e( 'Search Somvio', 'somvio' );
			}
			?>
		</h1>

		<p class="search-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			if ( $somvio_have_posts ) {
				esc_html_e( 'Here is what we found. Refine your search below or browse our services.', 'somvio' );
			} else {
				esc_html_e( 'Nothing matched your search. Try different keywords, or head back home to book a clean.', 'somvio' );
			}
			?>
		</p>

		<form
			class="search-hero__form reveal-on-scroll"
			style="--reveal-delay: 0.12s;"
			role="search"
			method="get"
			action="<?php echo esc_url( home_url( '/' ) ); ?>"
		>
			<label class="sr-only" for="somvio-search-field">
				<?php esc_html_e( 'Search for:', 'somvio' ); ?>
			</label>
			<input
				type="search"
				id="somvio-search-field"
				class="search-hero__input"
				name="s"
				value="<?php echo esc_attr( $somvio_query ); ?>"
				placeholder="<?php esc_attr_e( 'Search…', 'somvio' ); ?>"
			>
			<button type="submit" class="btn btn--primary btn--md search-hero__submit">
				<span class="btn__label"><?php esc_html_e( 'Search', 'somvio' ); ?></span>
			</button>
		</form>

		<?php if ( $somvio_have_posts ) : ?>
			<ul class="search-hero__results reveal-on-scroll" style="--reveal-delay: 0.15s;">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="search-hero__result">
						<a class="search-hero__result-link" href="<?php the_permalink(); ?>">
							<span class="search-hero__result-title"><?php the_title(); ?></span>
							<?php if ( has_excerpt() ) : ?>
								<span class="search-hero__result-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<?php
				endwhile;
				?>
			</ul>
		<?php endif; ?>

		<div class="search-hero__actions reveal-on-scroll" style="--reveal-delay: 0.2s;">
			<a class="btn btn--outline btn--md" href="<?php echo $somvio_home_url; ?>">
				<span class="btn__label"><?php esc_html_e( 'Back to Home', 'somvio' ); ?></span>
			</a>
			<a class="btn btn--primary btn--md" href="<?php echo esc_url( $somvio_book_url ); ?>">
				<span class="btn__label"><?php esc_html_e( 'Book a Cleaning', 'somvio' ); ?></span>
			</a>
		</div>
	</div>
</section>
