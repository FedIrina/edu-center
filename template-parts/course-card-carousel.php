<?php
/**
 * Карточка курса для карусели (Swiper): сейчас — блок «Курсы» с вкладками на главной.
 *
 * Ожидает переменную $course (массив), переданную через
 * get_template_part( 'template-parts/course-card-carousel', null, array( 'course' => $course ) ).
 *
 * @package edu-center
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $course ) || ! is_array( $course ) ) {
	return;
}

$has_sale_legal       = ! empty( $course['price_legal_sale'] ) && $course['price_legal_sale'] != $course['price_legal'];
$has_sale_individual  = ! empty( $course['price_individual_sale'] ) && $course['price_individual_sale'] != $course['price_individual'];
$show_individual      = ! empty( $course['price_individual'] );
$show_legal           = ! empty( $course['price_legal'] );
$legal_base_price     = $show_legal ? floatval( $course['price_legal'] ) : null;
$individual_base_price = $show_individual ? floatval( $course['price_individual'] ) : null;
$legal_discount_price = $has_sale_legal ? floatval( $course['price_legal_sale'] ) : null;
$individual_discount_price = $has_sale_individual ? floatval( $course['price_individual_sale'] ) : null;
$show_split_price_groups = $show_individual && $show_legal && (
	$legal_base_price !== $individual_base_price || $legal_discount_price !== $individual_discount_price
);
$common_base_price = $show_legal ? $legal_base_price : $individual_base_price;
$common_has_sale   = $show_legal ? $has_sale_legal : $has_sale_individual;
$common_sale_price = $show_legal ? $legal_discount_price : $individual_discount_price;
?>
<div class="courses__item swiper-slide">
	<div class="courses__item-inner">
		<div class="courses__tags">
			<?php if ( ! empty( $course['tags'] ) ) : ?>
			<?php foreach ( $course['tags'] as $tag ) : ?>
			<span><?php echo esc_html( $tag ); ?></span>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="courses__content">
			<div class="courses__wrap-title">
				<?php if ( $course['course_icon'] ) : ?>
				<div class="courses__icon">
					<img class="courses__icon-img" src="<?php echo esc_url( $course['course_icon'] ); ?>" width="80" height="80"
						alt="<?php echo esc_attr( $course['course_icon_alt'] ); ?>" aria-hidden="true">
				</div>
				<?php endif; ?>
				<div class="courses__code"><?php echo esc_html( get_post_meta( $course['course_id'], '_course_code', true ) ?: '' ); ?></div>
				<h3 class="courses__title">
					<a href="<?php echo esc_url( $course['course_link'] ); ?>"><?php echo esc_html( $course['course_title'] ); ?></a>
				</h3>
			</div>
			<div class="courses__details-wrap">
				<div class="courses__details">
					<?php if ( $course['date_range'] ) : ?>
					<div class="courses__date"><?php echo esc_html( $course['date_range'] ); ?></div>
					<?php endif; ?>
					<?php if ( $course['learning_format'] ) : ?>
					<div class="courses__type"><?php echo esc_html( $course['learning_format'] ); ?></div>
					<?php endif; ?>
					<?php if ( $course['study_hours'] ) : ?>
					<div class="courses__hours"><?php echo esc_html( $course['study_hours'] ); ?> <?php //echo esc_html__( 'часов', 'edu-center' ); ?></div>
					<?php endif; ?>
					<?php if ( $course['schedule'] ) : ?>
					<div class="courses__schedule"><?php echo esc_html( $course['schedule'] ); ?></div>
					<?php endif; ?>
				</div>

				<a href="<?php echo esc_url( $course['course_link'] ); ?>" class="courses__order btn btn-primary"><?php echo esc_html__( 'Записаться', 'edu-center' ); ?></a>
			</div>

			<div class="courses__prices-wrap">
				<div class="courses__prices__col">
					<?php if ( $show_split_price_groups ) : ?>
					<div class="courses__amount-group courses__amount-group--individual">
						<?php if ( $has_sale_individual ) : ?>
						<div class="courses__amount--no-actual"><del><span class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_individual'] ), 0, ',', ' ' ) ); ?>₽</span></del></div>
						<div class="courses__amount--actual"><span class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_individual_sale'] ), 0, ',', ' ' ) ); ?>₽</span></div>
						<?php else : ?>
						<div class="courses__amount"><span class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_individual'] ), 0, ',', ' ' ) ); ?>₽</span></div>
						<?php endif; ?>
					</div>
					<div class="courses__amount-group courses__amount-group--organizations">
						<?php if ( $has_sale_legal ) : ?>
						<div class="courses__amount--no-actual"><del><span class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_legal'] ), 0, ',', ' ' ) ); ?>₽</span></del></div>
						<div class="courses__amount--actual"><span class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_legal_sale'] ), 0, ',', ' ' ) ); ?>₽</span></div>
						<?php else : ?>
						<div class="courses__amount"><span class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_legal'] ), 0, ',', ' ' ) ); ?>₽</span></div>
						<?php endif; ?>
					</div>
					<?php elseif ( null !== $common_base_price ) : ?>
						<?php if ( $common_has_sale && null !== $common_sale_price ) : ?>
						<div class="courses__amount--no-actual"><del><span class="nowrap"><?php echo esc_html( number_format( $common_base_price, 0, ',', ' ' ) ); ?>₽</span></del></div>
						<div class="courses__amount--actual"><span class="nowrap"><?php echo esc_html( number_format( $common_sale_price, 0, ',', ' ' ) ); ?>₽</span></div>
						<?php else : ?>
						<div class="courses__amount"><span class="nowrap"><?php echo esc_html( number_format( $common_base_price, 0, ',', ' ' ) ); ?>₽</span></div>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<div class="courses__prices__col courses__prices__col--descr">
					<?php if ( $show_individual ) : ?>
					<div class="courses__price courses__price--individual"><?php echo esc_html__( 'Для физ. лиц -', 'edu-center' ); ?> <span
							class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_individual'] ), 0, ',', ' ' ) ); ?>₽</span></div>
					<?php endif; ?>
					<?php if ( $show_legal ) : ?>
					<div class="courses__price courses__price--organizations"><?php echo esc_html__( 'Для организаций -', 'edu-center' ); ?> <span
							class="nowrap"><?php echo esc_html( number_format( floatval( $course['price_legal'] ), 0, ',', ' ' ) ); ?>₽</span></div>
					<?php endif; ?>
				</div>
			</div><!-- /.courses__prices-wrap -->
		</div><!-- /.courses__content -->
	</div>
</div><!-- /.courses__item -->
