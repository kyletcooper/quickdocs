<?php

/**
 * Template for displaying a preview of a documentation article.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

?>

<article class="article">
	<a class="article-link" href="<?php the_permalink(); ?>">
		<div class="article-top">
			<h2 class="article-title">
				<?php the_title(); ?>
			</h2>

			<svg class="article-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000">
				<path d="M0 0h24v24H0z" fill="none" />
				<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
			</svg>
		</div>

		<div class="article-excerpt">
			<?php the_excerpt(); ?>
		</div>
	</a>
</article>
