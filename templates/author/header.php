<?php
/**
 * @author  wpWax
 * @since   6.6
 * @version 6.7
 */

use \Directorist\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="directorist-author-profile-area">
	<div class="<?php Helper::directorist_row(); ?>">
		<div class="<?php Helper::directorist_column( 12 ); ?>">
			<div class="directorist-card">
				<div class="directorist-card__body directorist-author-profile-wrap">
					<div class="directorist-author-avatar">
						<?php echo $author->avatar_html(); ?>

						<div class="directorist-author-avatar__info">
							<h2><?php echo esc_html( $author->display_name() ); ?></h2>
							<p><?php echo esc_html( $author->member_since_text() ); ?></p>
						</div>
					</div>
					<div class="directorist-author-meta">
						<ul class="directorist-author-meta__list">
							<?php if ( $author->review_enabled() ): ?>
							<li class="directorist-author-meta__list--item">
								<span class="directorist-listing-rating-meta"><?php echo esc_html( $author->rating_count() ); ?><i class="<?php atbdp_icon_type(true); ?>-star"></i></span>
							</li>
							<li class="directorist-author-meta__list--item">
								<span class="directorist-review-count"><?php echo $author->review_count_html(); ?></span>
							</li>
							<?php endif; ?>
							<li class="directorist-author-meta__list--item">
								<span class="directorist-listing-count"><?php echo $author->listing_count_html(); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<!-- <div class="atbd_author_meta">
				<?php if ( $author->review_enabled() ): ?>
					<div class="atbd_listing_meta">
						<span class="atbd_meta atbd_listing_rating">
							
						</span>
					</div>
					<p class="meta-info"></p>			
				<?php endif; ?>

				<p class="meta-info"></p>
			</div> -->
	</div>
</div>