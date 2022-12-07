<?php

/**
 * Template for displaying a collection of documentation articles.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

require __DIR__ . '/partials/header.php';

$query = new \WP_Query(
	array(
		'post_type'      => Quickdocs_Posttype::POST_TYPE,
		'posts_per_page' => 45,
	)
);

?>

<div class="page_title">
	<h1>
		<?php the_archive_title(); ?>
	</h1>
</div>

<?php if ( get_option( 'qds_home_show_topics', true ) ) : ?>
	<div class="page_contents">
		<ul class="topics-list">
			<?php
			wp_list_categories(
				array(
					'title_li'            => __( 'Popular Topics', 'quickdocs' ),
					'hide_title_if_empty' => true,
					'taxonomy'            => Quickdocs_Posttype::TOPICS_TAXONOMY,
					'number'              => 7,
					'orderby'             => 'count',
					'order'               => 'DESC',
					'hide_empty'          => true,
					'hierarchical'        => false,
				)
			)
			?>
		</ul>
	</div>
<?php endif; ?>

<div class="page_article" data-article>
	<main>
		<?php the_archive_description(); ?>

		<div class="article-grid">
			<?php

			while ( $query->have_posts() ) {
				$query->the_post();
				include __DIR__ . '/partials/content-article.php';
			}

			wp_reset_postdata();

			?>
		</div>
	</main>

	<footer class="footer">
		<div class="footer-pagination">
			<?php

			$items = get_sidebar_menu_items();
			echo wp_kses( get_next_menu_item_link( $items[0], 'footer-page footer-next' ), 'post' );

			?>
		</div>

		<hr />
	</footer>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
