<?php

/**
 * Template for displaying the contents of a single documentation article.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

require __DIR__ . '/partials/header.php';

$contents = Table_Of_Contents::from_post();

?>

<div class="page_title">
	<small class="page_topic">
		<?php the_generic_topic(); ?>
	</small>
	<h1>
		<?php the_title(); ?>
	</h1>
</div>

<?php if ( $contents->get_headings() ) : ?>
	<div class="page_contents">
		<h2 class="h4">
			<?php esc_html_e( 'On this page', 'quickdocs' ); ?>
		</h2>

		<?php echo wp_kses( $contents->get_headings_list(), 'post' ); ?>
	</div>
<?php endif; ?>

<div class="page_article" data-article>
	<main>
		<?php the_content(); ?>
	</main>

	<footer class="footer">
		<div class="footer-pagination">
			<?php echo wp_kses( get_previous_menu_item_link( null, 'footer-page footer-prev' ), 'post' ); ?>

			<?php echo wp_kses( get_next_menu_item_link( null, 'footer-page footer-next' ), 'post' ); ?>
		</div>

		<hr />

		<div class="footer-details">
			<div class="footer-helpful">
				<?php esc_html_e( 'Was this page useful?', 'quickdocs' ); ?>

				<button type="button" aria-label="<?php esc_attr_e( 'Mark as helpful', 'quickdocs' ); ?>" data-helpful>
					<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000">
						<path d="M0 0h24v24H0V0z" fill="none" />
						<path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z" />
					</svg>
				</button>

				<button type="button" aria-label="<?php esc_attr_e( 'Mark as unhelpful', 'quickdocs' ); ?>" data-unhelpful>
					<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000">
						<path d="M0 0h24v24H0z" fill="none" />
						<path d="M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v2c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z" />
					</svg>
				</button>
			</div>

			<div class="footer-updated">
				<?php

				esc_html_e( 'Last updated: ', 'quickdocs' );
				the_modified_date( 'jS M, Y' );

				?>
			</div>
		</div>
	</footer>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
