<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package edu-center
 */

get_header();
?>


	<?php
	while ( have_posts() ) :
		the_post();
	
	$site_slogan = get_theme_mod( 'site_slogan' );
	if ( $site_slogan ) :
			?>
	<div class="slogan__wrapper">
		<div class="slogan__container container">
			<div class="slogan__txt"><?php echo esc_html( $site_slogan ); ?></div>
		</div><!-- /.slogan__container container -->
	<?php
	endif;

	// Вывод слайдера из ACF
	if ( have_rows( 'slider_repeater' ) ) :
	?>
		<div class="main-slider">
			<div class="main-slider__inner">
				<div class="main-slider__container container">

					<div class="swiper swiper-main-slider">
						<div class="swiper-wrapper swiper-main-wrapper">

							<?php
							while ( have_rows( 'slider_repeater' ) ) :
								the_row();
								$slide_image = get_sub_field( 'slide_image' );
								$slide_title = get_sub_field( 'slide_title' );
								$slide_text  = get_sub_field( 'slide_text' );
								$slide_link  = get_sub_field( 'slide_link' );
								$slide_button_text = get_sub_field( 'slide_button_text' );
								if ( ! $slide_button_text ) {
									$slide_button_text = __( 'Записаться на курс', 'edu-center' );
								}
								?>
							<div class="main-slider__item swiper-slide">
								<div class="main-slider__row">
									<div class="main-slider__col-content">
										<?php if ( $slide_title ) : ?>
										<div class="main-slider__title"><?php echo esc_html( $slide_title ); ?></div>
										<?php endif; ?>
										
										<?php if ( $slide_text ) : ?>
										<div class="main-slider__descr">
											<?php echo wp_kses_post( wpautop( $slide_text ) ); ?>
										</div>
										<?php endif; ?>
										
										<?php if ( $slide_link ) : ?>
										<a class="main-slider__button btn btn-secondary" href="<?php echo esc_url( $slide_link ); ?>" role="button"><?php echo esc_html( $slide_button_text ); ?></a>
										<?php endif; ?>
									</div>
									
									<?php if ( $slide_image ) : ?>
									<div class="main-slider__col-image">
										<img class="main-slider__image" 
											src="<?php echo esc_url( $slide_image['url'] ); ?>" 
											alt="<?php echo esc_attr( $slide_image['alt'] ?: $slide_title ); ?>" 
											width="<?php echo esc_attr( $slide_image['width'] ); ?>" 
											height="<?php echo esc_attr( $slide_image['height'] ); ?>"
											aria-hidden="true">
									</div>
									<?php endif; ?>
								</div>
							</div><!-- /.main-slider__item -->
							<?php endwhile; ?>

						</div><!-- /.swiper-main-wrapper -->
					</div><!-- /.swiper-main-slider -->

					<div class="swiper__button-controls main-slider__button-controls">
						<div class="swiper-button-prev main-slider__prev">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</div>
						<div class="swiper-button-next main-slider__next">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</div>
					</div><!-- /.main-slider__button-controls -->

				</div>
			</div><!-- /.main-slider__inner -->
		</div><!-- /.main-slider -->
	<?php endif; ?>

	<?php
	// Вывод преимуществ из ACF
	if ( have_rows( 'advantages_repeater' ) ) :
		?>
	<div class="advantages">
		<div class="advantages__container container">

			<div class="swiper swiper-advantages">
				<div class="swiper-wrapper advantages__list">

					<?php
					while ( have_rows( 'advantages_repeater' ) ) :
						the_row();
						$advantage_icon  = get_sub_field( 'advantage_icon' );
						$advantage_title = get_sub_field( 'advantage_title' );
						$advantage_text  = get_sub_field( 'advantage_text' );
						?>
					<div class="advantages__item swiper-slide">
						<div class="advantages__item-wrap">
							<?php if ( $advantage_icon ) : ?>
							<div class="advantages__icon">
								<img class="advantages__icon-img" 
									src="<?php echo esc_url( $advantage_icon['url'] ); ?>" 
									alt="<?php echo esc_attr( $advantage_icon['alt'] ?: $advantage_title ); ?>" 
									width="<?php echo esc_attr( $advantage_icon['width'] ); ?>" 
									height="<?php echo esc_attr( $advantage_icon['height'] ); ?>">
							</div>
							<?php endif; ?>
							
							<?php if ( $advantage_title ) : ?>
							<h3 class="advantages__title"><?php echo esc_html( $advantage_title ); ?></h3>
							<?php endif; ?>
							
							<?php if ( $advantage_text ) : ?>
							<div class="advantages__descr"><?php echo wp_kses_post( wpautop( $advantage_text ) ); ?></div>
							<?php endif; ?>
						</div>
					</div><!-- /.advantages__item -->
					<?php endwhile; ?>

				</div><!-- /.advantages__list -->
			</div><!-- /.swiper-advantages -->

			<div class="swiper__button-controls advantages__button-controls">
				<div class="swiper-button-prev advantages__prev">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
							stroke-linejoin="round" />
					</svg>
				</div>
				<div class="swiper-button-next advantages__next">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
							stroke-linejoin="round" />
					</svg>
				</div>
			</div><!-- /.advantages__button-controls -->

		</div><!-- /.advantages__container.container -->
	</div><!-- /.advantages -->
	<?php endif; ?>

	<?php
	// Получаем отзывы (комментарии) с мета-полями
	$comments_args = array(
		'status'  => 'approve',
		'number'  => 100, // Получаем больше, чтобы потом отсортировать по дате
		'orderby' => 'comment_date',
		'order'   => 'DESC',
	);
	
	$all_comments = get_comments( $comments_args );
	$review_comments = array();
	
	// Фильтруем комментарии, у которых есть мета-поле _review_name_position (обязательное поле)
	foreach ( $all_comments as $comment ) {
		$review_name_position = get_comment_meta( $comment->comment_ID, '_review_name_position', true );
		if ( ! empty( $review_name_position ) ) {
			$review_date = get_comment_meta( $comment->comment_ID, '_review_date', true );
			$review_course = get_comment_meta( $comment->comment_ID, '_review_course', true );
			
			// Используем дату из мета-поля, если она есть, иначе дату комментария
			$sort_date = ! empty( $review_date ) ? $review_date : $comment->comment_date;
			
			$review_comments[] = array(
				'comment'           => $comment,
				'name_position'     => $review_name_position,
				'date'              => $review_date,
				'course'            => $review_course,
				'sort_date'         => $sort_date,
			);
		}
	}
	
	// Сортируем по дате из мета-поля по убыванию
	usort(
		$review_comments,
		function( $a, $b ) {
			$date_a = ! empty( $a['sort_date'] ) ? strtotime( $a['sort_date'] ) : 0;
			$date_b = ! empty( $b['sort_date'] ) ? strtotime( $b['sort_date'] ) : 0;
			return $date_b - $date_a; // По убыванию
		}
	);
	
	// Берем 8 самых свежих
	$review_comments = array_slice( $review_comments, 0, 8 );
	
	if ( ! empty( $review_comments ) ) :
		$edu_testimonials_page_url = edu_center_get_testimonials_page_url();
		$edu_testimonials_href     = $edu_testimonials_page_url ? $edu_testimonials_page_url : '#';
		?>
	<section class="section testimonials">
		<div class="testimonials__container container">
			<div class="section__title-wrap">
				<h2 class="section__title"><?php echo esc_html__( 'Что о нас говорят', 'edu-center' ); ?></h2>
				<a href="<?php echo esc_url( $edu_testimonials_href ); ?>" class="testimonials__link-all testimonials__link-all--desktop"><?php echo esc_html__( 'все Отзывы', 'edu-center' ); ?></a>
			</div>
			<div class="testimonials__wrapper">
				<div class="testimonials__inner">

					<div class="swiper swiper-testimonials">

						<div class="swiper-wrapper testimonials__list">

							<?php
							foreach ( $review_comments as $review_data ) :
								$comment = $review_data['comment'];
								$name_position = $review_data['name_position'];
								$review_course = $review_data['course'];
								$comment_content = $comment->comment_content;
								
								// Получаем название курса, если это ID
								$course_name = '';
								if ( ! empty( $review_course ) ) {
									if ( is_numeric( $review_course ) ) {
										$course_post = get_post( $review_course );
										if ( $course_post ) {
											$course_name = $course_post->post_title;
										}
									} else {
										$course_name = $review_course;
									}
								}
								?>
							<div class="testimonials__item swiper-slide">
								<?php if ( $name_position ) : ?>
								<div class="testimonials__autor">
									<?php echo esc_html( $name_position ); ?>
								</div>
								<?php endif; ?>

								<?php if ( $course_name ) : ?>
								<div class="testimonials__course">
									<?php printf( esc_html__( 'Студент курса «%s»', 'edu-center' ), esc_html( $course_name ) ); ?>
								</div>
								<?php endif; ?>

								<?php if ( $comment_content ) : ?>
								<div class="testimonials__txt">
									<?php echo wp_kses_post( wpautop( $comment_content ) ); ?>
								</div>
								<?php endif; ?>
							</div><!-- /.testimonials__item -->
							<?php endforeach; ?>

						</div><!-- /.testimonials__list -->

					</div><!-- /.swiper-testimonials -->

				</div><!-- /.testimonials__inner -->

				<div class="swiper__button-controls testimonials__button-controls">
					<a href="<?php echo esc_url( $edu_testimonials_href ); ?>" class="testimonials__link-all testimonials__link-all--mobile"><?php echo esc_html__( 'все Отзывы', 'edu-center' ); ?></a>
					<div class="swiper-button-prev testimonials__prev">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round"></path>
						</svg>
					</div>
					<div class="swiper-button-next testimonials__next">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round"></path>
						</svg>
					</div>
				</div><!-- /.testimonials__button-controls -->

			</div><!-- /.testimonials__wrapper -->

		</div><!-- /.testimonials__container.container -->

	</section><!-- /.testimonials -->
	<?php endif; ?>

	<?php
	// Получаем термины таксономии course_tag
	$course_tags = get_terms(
		array(
			'taxonomy'   => 'course_specialization',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! is_wp_error( $course_tags ) && ! empty( $course_tags ) ) :
		// Разделяем на основные (первые 7) и дополнительные (остальные)
		$main_tags    = array_slice( $course_tags, 0, 7 );
		$additional_tags = array_slice( $course_tags, 7 );
		?>
	<?php
	// Получаем заголовок блока тегов из ACF
	$tags_block_title = get_field( 'tags_block_title' );
	?>
	<section class="section links">
		<div class="container">
			<?php if ( ! empty( $tags_block_title ) ) : ?>
			<h2 class="section__title"><?php echo esc_html( $tags_block_title ); ?></h2>
			<?php endif; ?>

			<div class="links__list">
				<?php
				// Выводим основные ссылки
				foreach ( $main_tags as $tag ) :
					$tag_link = get_term_link( $tag );
					if ( ! is_wp_error( $tag_link ) ) :
						?>
					<div class="links__item">
						<a class="links__link" href="<?php echo esc_url( $tag_link ); ?>"><?php echo esc_html( $tag->name ); ?></a>
					</div>
					<?php
					endif;
				endforeach;
				?>

				<?php if ( ! empty( $additional_tags ) ) : ?>
				<div class="collapse" id="collapseLinks">
					<div class="links__list">
						<?php
						foreach ( $additional_tags as $tag ) :
							$tag_link = get_term_link( $tag );
							if ( ! is_wp_error( $tag_link ) ) :
								?>
							<div class="links__item">
								<a class="links__link" href="<?php echo esc_url( $tag_link ); ?>"><?php echo esc_html( $tag->name ); ?></a>
							</div>
							<?php
							endif;
						endforeach;
						?>
					</div>
				</div>
				<?php endif; ?>
			</div><!-- /.links__list -->

			<?php if ( ! empty( $additional_tags ) ) : ?>
			<button class="links__btn-more btn btn-primary" type="button" data-bs-toggle="collapse"
				data-bs-target="#collapseLinks" aria-expanded="false" aria-controls="collapseLinks">
				показать больше
			</button>
			<?php endif; ?>

		</div><!-- /.container -->
	</section><!-- /.links -->
	<?php endif; ?>

	<?php
	// Получаем настройки курсов из ACF
	$upcoming_courses_days = get_field( 'upcoming_courses_days' );
	$upcoming_courses_count = get_field( 'upcoming_courses_count' );
	$new_courses_days = get_field( 'new_courses_days' );
	$new_courses_count = get_field( 'new_courses_count' );
	$hot_courses_count = get_field( 'hot_courses_count' );
	$now_timestamp = current_time( 'timestamp' );

	// Функция для получения курсов с событиями
	function edu_get_courses_with_events( $args = array() ) {
		$defaults = array(
			'post_type'      => 'lp_course',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$args = wp_parse_args( $args, $defaults );
		
		$courses_query = new WP_Query( $args );
		$courses_data = array();
		
		if ( $courses_query->have_posts() && class_exists( 'EM_Event' ) ) {
			while ( $courses_query->have_posts() ) {
				$courses_query->the_post();
				$course_id = get_the_ID();
				$event_ids = get_post_meta( $course_id, '_course_event_ids', true );
				
				if ( ! is_array( $event_ids ) || empty( $event_ids ) ) {
					continue;
				}

				$nearest_event = function_exists( 'edu_em_get_nearest_course_group_in_default_scope' )
					? edu_em_get_nearest_course_group_in_default_scope( $course_id )
					: null;
				$nearest_event_date = $nearest_event
					? strtotime( $nearest_event->event_start_date . ' ' . $nearest_event->event_start_time )
					: 0;

				$study_hours = '';
				if ( $nearest_event && ! empty( $nearest_event->post_id ) ) {
					$event_study_hours = get_post_meta( $nearest_event->post_id, '_edu_event_study_hours', true );
					if ( $event_study_hours ) {
						$hours_number = floatval( $event_study_hours );
						if ( $hours_number > 0 && class_exists( 'LP_Datetime' ) ) {
							$study_hours = LP_Datetime::get_string_plural_duration( $hours_number, 'hour' );
						} else {
							$study_hours = (string) $event_study_hours;
						}
					}
				}
				if ( $study_hours === '' ) {
					$study_hours = function_exists( 'learn_press_get_post_translated_duration' )
						? learn_press_get_post_translated_duration( $course_id, '' )
						: '';
				}

				if ( $nearest_event ) {
					$event_post_id         = $nearest_event->post_id;
					$price_legal           = get_post_meta( $event_post_id, '_edu_event_price_legal', true );
					$price_legal_sale      = get_post_meta( $event_post_id, '_edu_event_price_legal_sale', true );
					$price_individual      = get_post_meta( $event_post_id, '_edu_event_price_individual', true );
					$price_individual_sale = get_post_meta( $event_post_id, '_edu_event_price_individual_sale', true );
					$learning_format       = get_post_meta( $event_post_id, '_edu_event_learning_format', true );
					$weekdays              = get_post_meta( $event_post_id, '_edu_event_weekdays', true );
					$start_date            = date_i18n( 'd.m.Y', strtotime( $nearest_event->event_start_date ) );
					$end_date              = date_i18n( 'd.m.Y', strtotime( $nearest_event->event_end_date ) );
					$date_range            = $start_date === $end_date ? $start_date : $start_date . '-' . $end_date;
					$start_time            = $nearest_event->event_start_time;
					if ( strlen( $start_time ) > 5 ) {
						$start_time = substr( $start_time, 0, 5 );
					}
					$end_time = $nearest_event->event_end_time;
					if ( strlen( $end_time ) > 5 ) {
						$end_time = substr( $end_time, 0, 5 );
					}
					$schedule = $weekdays ? $weekdays . ': ' . $start_time . '–' . $end_time : $start_time . '–' . $end_time;
				} else {
					$price_legal           = (string) get_post_meta( $course_id, '_lp_regular_price', true );
					$price_legal_sale      = (string) get_post_meta( $course_id, '_lp_sale_price', true );
					$price_individual      = (string) get_post_meta( $course_id, '_lp_individual_price', true );
					$price_individual_sale = '';
					$learning_format       = '';
					$date_range            = __( 'Дата уточняется', 'edu-center' );
					$schedule              = '';
				}
				
				// Получаем иконку курса
				$course_icon = get_post_thumbnail_id( $course_id );
				$course_icon_url = '';
				$course_icon_alt = get_the_title( $course_id );
				if ( $course_icon ) {
					$course_icon_url = wp_get_attachment_image_url( $course_icon, 'thumbnail' );
					$course_icon_alt = get_post_meta( $course_icon, '_wp_attachment_image_alt', true ) ?: $course_icon_alt;
				}
				
				// Получаем теги курса
				$course_tags = get_the_terms( $course_id, 'course_tag' );
				$tags_array = array();
				if ( ! is_wp_error( $course_tags ) && $course_tags ) {
					foreach ( $course_tags as $tag ) {
						$tags_array[] = $tag->name;
					}
				}
				
				$courses_data[] = array(
					'course_id'            => $course_id,
					'course_title'         => get_the_title( $course_id ),
					'course_link'          => get_permalink( $course_id ),
					'course_icon'           => $course_icon_url,
					'course_icon_alt'      => $course_icon_alt,
					'event_id'             => $nearest_event ? $nearest_event->event_id : 0,
					'event_start_date'     => $nearest_event ? $nearest_event->event_start_date : '',
					'event_start_timestamp' => $nearest_event_date,
					'date_range'            => $date_range,
					'learning_format'       => $learning_format,
					'study_hours'           => $study_hours,
					'schedule'              => $schedule,
					'price_legal'           => $price_legal,
					'price_legal_sale'      => $price_legal_sale,
					'price_individual'      => $price_individual,
					'price_individual_sale' => $price_individual_sale,
					'tags'                  => $tags_array,
					'post_date'             => get_the_date( 'Y-m-d', $course_id ),
				);
			}
			wp_reset_postdata();
		}
		
		return $courses_data;
	}
	
	// Получаем ближайшие курсы
	$upcoming_courses = array();
	if ( ! empty( $upcoming_courses_count ) && ! empty( $upcoming_courses_days ) ) {
		$all_courses = edu_get_courses_with_events();
		$now = current_time( 'timestamp' );
		$max_days = intval( $upcoming_courses_days ) * DAY_IN_SECONDS;
		
		foreach ( $all_courses as $course ) {
			$days_until = $course['event_start_timestamp'] - $now;
			if ( $days_until > 0 && $days_until <= $max_days ) {
				$upcoming_courses[] = $course;
			}
		}
		
		// Сортируем по дате начала
		usort( $upcoming_courses, function( $a, $b ) {
			return $a['event_start_timestamp'] - $b['event_start_timestamp'];
		} );
		
		// Ограничиваем количество
		if ( intval( $upcoming_courses_count ) > 0 ) {
			$upcoming_courses = array_slice( $upcoming_courses, 0, intval( $upcoming_courses_count ) );
		}
	}
	
	// Получаем новые курсы (имеющие тег "Новинка" с ближайшими событиями)
	$new_courses = array();
	if ( ! empty( $new_courses_count ) && class_exists( 'EM_Event' ) ) {
		// Ищем термин "Новинка" в таксономии course_tag
		$novelty_tag = get_term_by( 'name', 'Новинка', 'course_tag' );
		
		if ( $novelty_tag && ! is_wp_error( $novelty_tag ) ) {
			// Получаем все курсы с тегом "Новинка"
			$novelty_courses_query = new WP_Query(
				array(
					'post_type'      => 'lp_course',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'tax_query'      => array(
						array(
							'taxonomy' => 'course_tag',
							'field'    => 'term_id',
							'terms'    => $novelty_tag->term_id,
						),
					),
				)
			);
			
			if ( $novelty_courses_query->have_posts() ) {
				$courses_with_events = array();
				
				while ( $novelty_courses_query->have_posts() ) {
					$novelty_courses_query->the_post();
					$course_id = get_the_ID();
					$event_ids = get_post_meta( $course_id, '_course_event_ids', true );
					
					if ( ! is_array( $event_ids ) || empty( $event_ids ) ) {
						continue;
					}

					$nearest_event = function_exists( 'edu_em_get_nearest_course_group_in_default_scope' )
						? edu_em_get_nearest_course_group_in_default_scope( $course_id )
						: null;
					$nearest_event_date = $nearest_event
						? strtotime( $nearest_event->event_start_date . ' ' . $nearest_event->event_start_time )
						: 0;
					if ( ! $nearest_event || $nearest_event_date <= $now_timestamp ) {
						continue;
					}

					$study_hours = '';
					if ( $nearest_event && ! empty( $nearest_event->post_id ) ) {
						$event_study_hours = get_post_meta( $nearest_event->post_id, '_edu_event_study_hours', true );
						if ( $event_study_hours ) {
							$hours_number = floatval( $event_study_hours );
							if ( $hours_number > 0 && class_exists( 'LP_Datetime' ) ) {
								$study_hours = LP_Datetime::get_string_plural_duration( $hours_number, 'hour' );
							} else {
								$study_hours = (string) $event_study_hours;
							}
						}
					}
					if ( $study_hours === '' ) {
						$study_hours = function_exists( 'learn_press_get_post_translated_duration' )
							? learn_press_get_post_translated_duration( $course_id, '' )
							: '';
					}

					if ( $nearest_event ) {
						$event_post_id         = $nearest_event->post_id;
						$price_legal           = get_post_meta( $event_post_id, '_edu_event_price_legal', true );
						$price_legal_sale      = get_post_meta( $event_post_id, '_edu_event_price_legal_sale', true );
						$price_individual      = get_post_meta( $event_post_id, '_edu_event_price_individual', true );
						$price_individual_sale = get_post_meta( $event_post_id, '_edu_event_price_individual_sale', true );
						$learning_format       = get_post_meta( $event_post_id, '_edu_event_learning_format', true );
						$weekdays              = get_post_meta( $event_post_id, '_edu_event_weekdays', true );
						$start_date            = date_i18n( 'd.m.Y', strtotime( $nearest_event->event_start_date ) );
						$end_date              = date_i18n( 'd.m.Y', strtotime( $nearest_event->event_end_date ) );
						$date_range            = $start_date === $end_date ? $start_date : $start_date . '-' . $end_date;
						$start_time            = $nearest_event->event_start_time;
						if ( strlen( $start_time ) > 5 ) {
							$start_time = substr( $start_time, 0, 5 );
						}
						$end_time = $nearest_event->event_end_time;
						if ( strlen( $end_time ) > 5 ) {
							$end_time = substr( $end_time, 0, 5 );
						}
						$schedule = $weekdays ? $weekdays . ': ' . $start_time . '–' . $end_time : $start_time . '–' . $end_time;
					} else {
						$price_legal           = (string) get_post_meta( $course_id, '_lp_regular_price', true );
						$price_legal_sale      = (string) get_post_meta( $course_id, '_lp_sale_price', true );
						$price_individual      = (string) get_post_meta( $course_id, '_lp_individual_price', true );
						$price_individual_sale = '';
						$learning_format       = '';
						$date_range            = __( 'Дата уточняется', 'edu-center' );
						$schedule              = '';
					}

					$course_icon = get_post_thumbnail_id( $course_id );
					$course_icon_url = '';
					$course_icon_alt = get_the_title( $course_id );
					if ( $course_icon ) {
						$course_icon_url = wp_get_attachment_image_url( $course_icon, 'thumbnail' );
						$course_icon_alt = get_post_meta( $course_icon, '_wp_attachment_image_alt', true ) ?: $course_icon_alt;
					}

					$course_tags = get_the_terms( $course_id, 'course_tag' );
					$tags_array  = array();
					if ( ! is_wp_error( $course_tags ) && $course_tags ) {
						foreach ( $course_tags as $tag ) {
							$tags_array[] = $tag->name;
						}
					}

					$courses_with_events[] = array(
						'course_id'             => $course_id,
						'course_title'          => get_the_title( $course_id ),
						'course_link'           => get_permalink( $course_id ),
						'course_icon'           => $course_icon_url,
						'course_icon_alt'       => $course_icon_alt,
						'event_id'              => $nearest_event ? $nearest_event->event_id : 0,
						'event_start_date'      => $nearest_event ? $nearest_event->event_start_date : '',
						'event_start_timestamp' => $nearest_event_date,
						'date_range'            => $date_range,
						'learning_format'       => $learning_format,
						'study_hours'           => $study_hours,
						'schedule'              => $schedule,
						'price_legal'           => $price_legal,
						'price_legal_sale'      => $price_legal_sale,
						'price_individual'      => $price_individual,
						'price_individual_sale' => $price_individual_sale,
						'tags'                  => $tags_array,
						'post_date'             => get_the_date( 'Y-m-d', $course_id ),
					);
				}
				
				wp_reset_postdata();
				
				// Сортируем по дате ближайшего события (ближайшие первыми)
				usort( $courses_with_events, function( $a, $b ) {
					return $a['event_start_timestamp'] - $b['event_start_timestamp'];
				} );
				
				// Ограничиваем количество
				if ( intval( $new_courses_count ) > 0 ) {
					$new_courses = array_slice( $courses_with_events, 0, intval( $new_courses_count ) );
				} elseif ( intval( $new_courses_count ) == -1 ) {
					// Выводим все
					$new_courses = $courses_with_events;
				}
			}
		}
	}
	
	// Получаем горящие курсы (имеющие тэг "Горящий)
	
	$hot_courses = array();
	if ( ! empty( $hot_courses_count ) && class_exists( 'EM_Event' ) ) {
		// Получаем таксономию тегов событий
		$tag_taxonomy = 'event-tags';
		
		if ( taxonomy_exists( $tag_taxonomy ) ) {
			// Ищем термин "Горящий"
			$hot_tag = get_term_by( 'name', 'Горящий', $tag_taxonomy );
			
			if ( $hot_tag && ! is_wp_error( $hot_tag ) ) {
				// Получаем все события с тегом "Горящий"
				$hot_events_query = new WP_Query(
					array(
						'post_type'      => 'event',
						'posts_per_page' => -1,
						'post_status'    => 'publish',
						'tax_query'      => array(
							array(
								'taxonomy' => $tag_taxonomy,
								'field'    => 'term_id',
								'terms'    => $hot_tag->term_id,
							),
						),
					)
				);
				
				if ( $hot_events_query->have_posts() ) {
					$hot_events_data = array();
					
					while ( $hot_events_query->have_posts() ) {
						$hot_events_query->the_post();
						$event_post_id = get_the_ID();
						
						// Проверяем статус публикации поста
						if ( get_post_status( $event_post_id ) !== 'publish' ) {
							continue;
						}
						
						// Получаем даты из сохраненных мета-полей (если доступны)
						$event_start_date = get_post_meta( $event_post_id, '_event_start_date', true );
						$event_end_date = get_post_meta( $event_post_id, '_event_end_date', true );
						
						// Для получения event_id, времен и проверки статуса нужен объект EM_Event
						// (event_id не хранится в мета-полях WordPress, а времена не сохраняются в наши мета-поля)
						if ( ! class_exists( 'EM_Event' ) ) {
							continue;
						}

						// IMPORTANT: $event_post_id is WP post ID; load EM event by post_id to get correct times.
						$EM_Event = new EM_Event( $event_post_id, 'post_id' );
						
/* 						// Проверяем валидность события
						if ( ! $EM_Event->event_id || $EM_Event->event_status != 1 || $EM_Event->post_status != 'publish' ) {
							continue;
						} */

						$event_id = $EM_Event->event_id;
						
						// Используем даты из объекта, если мета-поля не заполнены
						if ( ! $event_start_date ) {
							$event_start_date = $EM_Event->event_start_date;
						}
						if ( ! $event_end_date ) {
							$event_end_date = $EM_Event->event_end_date;
						}
						
						// Времена получаем только из объекта (не сохраняются в мета-поля)
						$event_start_time = $EM_Event->event_start_time;
						$event_end_time = $EM_Event->event_end_time;
						
						// Получаем связанный курс через event_post_id (мета-поля WordPress хранятся по post_id)
						$course_id = get_post_meta( $event_post_id, '_related_course_id', true );

						if ( ! $course_id || get_post_status( $course_id ) !== 'publish' ) {
							continue;
						}
						
						$event_start_timestamp = strtotime( $event_start_date . ' ' . ( $event_start_time ? $event_start_time : '00:00:00' ) );
						if ( $event_start_timestamp <= $now_timestamp ) {
							continue;
						}
						
						// Получаем данные события через мета-поля
						$price_legal = get_post_meta( $event_post_id, '_edu_event_price_legal', true );
						$price_legal_sale = get_post_meta( $event_post_id, '_edu_event_price_legal_sale', true );
						$price_individual = get_post_meta( $event_post_id, '_edu_event_price_individual', true );
						$price_individual_sale = get_post_meta( $event_post_id, '_edu_event_price_individual_sale', true );
						$learning_format = get_post_meta( $event_post_id, '_edu_event_learning_format', true );
						$study_hours = get_post_meta( $event_post_id, '_edu_event_study_hours', true );
						if ( $study_hours ) {
							$hours_number = floatval( $study_hours );
							if ( $hours_number > 0 && class_exists( 'LP_Datetime' ) ) {
								$study_hours = LP_Datetime::get_string_plural_duration( $hours_number, 'hour' );
							}
						} else {
							$study_hours = function_exists( 'learn_press_get_post_translated_duration' )
								? learn_press_get_post_translated_duration( $course_id, '' )
								: '';
						}
						$weekdays = get_post_meta( $event_post_id, '_edu_event_weekdays', true );
						
						$start_date = date_i18n( 'd.m.Y', strtotime( $event_start_date ) );
						$end_date = date_i18n( 'd.m.Y', strtotime( $event_end_date ) );
						$date_range = $start_date === $end_date ? $start_date : $start_date . '-' . $end_date;
						if ( function_exists( 'edu_em_event_post_in_default_scope' ) && ! edu_em_event_post_in_default_scope( $event_post_id ) ) {
							$date_range = __( 'Дата уточняется', 'edu-center' );
						}

						$start_time = $event_start_time;
						if ( strlen( $start_time ) > 5 ) {
							$start_time = substr( $start_time, 0, 5 );
						}
						$end_time = $event_end_time;
						if ( strlen( $end_time ) > 5 ) {
							$end_time = substr( $end_time, 0, 5 );
						}
						$schedule = $weekdays ? $weekdays . ': ' . $start_time . '–' . $end_time : $start_time . '–' . $end_time;
						
						// Получаем иконку курса
						$course_icon = get_post_thumbnail_id( $course_id );
						$course_icon_url = '';
						$course_icon_alt = get_the_title( $course_id );
						if ( $course_icon ) {
							$course_icon_url = wp_get_attachment_image_url( $course_icon, 'thumbnail' );
							$course_icon_alt = get_post_meta( $course_icon, '_wp_attachment_image_alt', true ) ?: $course_icon_alt;
						}
						
						// Получаем теги курса
						$course_tags = get_the_terms( $course_id, 'course_tag' );
						$tags_array = array();
						if ( ! is_wp_error( $course_tags ) && $course_tags ) {
							foreach ( $course_tags as $tag ) {
								$tags_array[] = $tag->name;
							}
						}
						
						$hot_events_data[] = array(
							'course_id'            => $course_id,
							'course_title'         => get_the_title( $course_id ),
							'course_link'          => get_permalink( $course_id ),
							'course_icon'          => $course_icon_url,
							'course_icon_alt'      => $course_icon_alt,
							'event_id'             => $event_id,
							'event_start_date'     => $event_start_date,
							'event_start_timestamp' => $event_start_timestamp,
							'date_range'           => $date_range,
							'learning_format'      => $learning_format,
							'study_hours'          => $study_hours,
							'schedule'             => $schedule,
							'price_legal'          => $price_legal,
							'price_legal_sale'     => $price_legal_sale,
							'price_individual'     => $price_individual,
							'price_individual_sale' => $price_individual_sale,
							'tags'                 => $tags_array,
							'post_date'            => get_the_date( 'Y-m-d', $course_id ),
						);
					}
					
					wp_reset_postdata();

					// Сортируем по дате начала события (ближайшие первыми)
					usort( $hot_events_data, function( $a, $b ) {
						return $a['event_start_timestamp'] - $b['event_start_timestamp'];
					} );
					
					$hot_courses = $hot_events_data;
					
					// Ограничиваем количество
					if ( intval( $hot_courses_count ) > 0 ) {
						$hot_courses = array_slice( $hot_courses, 0, intval( $hot_courses_count ) );
					} elseif ( intval( $hot_courses_count ) == -1 ) {
						// Выводим все
					}
				}
			}
		}
	}
	
	// Показываем секцию, если есть хотя бы один тип курсов
	if ( ! empty( $upcoming_courses ) || ! empty( $new_courses ) || ! empty( $hot_courses ) ) :
	?>
	<div class="courses">
		<div class="container">

			<!-- TABS Courses -->
			<div class="courses__nav-wrapper">
				<nav class="courses__nav">
					<div class="courses__nav-tabs nav nav-tabs" id="nav-tab" role="tablist">
						<?php if ( ! empty( $upcoming_courses ) ) : ?>
						<button class="nav-link active" id="nearest-courses-tab" data-bs-toggle="tab"
							data-bs-target="#nearest-courses" type="button" role="tab" aria-controls="nearest-courses"
							aria-selected="true"><?php esc_html_e( 'Ближайшие', 'edu-center'); ?></button>
						<?php endif; ?>
						<?php if ( ! empty( $new_courses ) ) : ?>
						<button class="nav-link<?php echo empty( $upcoming_courses ) ? ' active' : ''; ?>" id="new-courses-tab" data-bs-toggle="tab" data-bs-target="#new-courses"
							type="button" role="tab" aria-controls="new-courses" aria-selected="<?php echo empty( $upcoming_courses ) ? 'true' : 'false'; ?>"><?php esc_html_e( 'Новые', 'edu-center'); ?></button>
						<?php endif; ?>
						<?php if ( ! empty( $hot_courses ) ) : ?>
						<button class="nav-link<?php echo empty( $upcoming_courses ) && empty( $new_courses ) ? ' active' : ''; ?>" id="last-minute-courses-tab" data-bs-toggle="tab"
							data-bs-target="#last-minute-courses" type="button" role="tab" aria-controls="last-minute-courses"
							aria-selected="<?php echo empty( $upcoming_courses ) && empty( $new_courses ) ? 'true' : 'false'; ?>"><?php esc_html_e( 'Горящие', 'edu-center'); ?></button>
						<?php endif; ?>
					</div>
				</nav>
			</div><!-- /.courses__nav-wrapper -->

			<div class="courses__tab-content tab-content" id="nav-tabContent">

				<!-- Ближайшие -->
				<?php if ( ! empty( $upcoming_courses ) ) : ?>
				<div class="tab-pane fade show active" id="nearest-courses" role="tabpanel"
					aria-labelledby="nearest-courses-tab" tabindex="0">

					<div class="courses__wrapper">
						<div class="courses__inner">
							<div class="swiper swiper-courses-one">
								<div class="courses__list swiper-wrapper">

									<?php foreach ( $upcoming_courses as $course ) : ?>
										<?php get_template_part( 'template-parts/course-card-carousel', null, array( 'course' => $course ) ); ?>
									<?php endforeach; ?>

								</div><!-- /.courses__list -->
							</div><!-- /.swiper-courses-one -->
						</div>
					</div><!-- /.courses__wrapper -->

					<div class="swiper__button-controls courses__button-controls">
						<div class="swiper-button-prev courses__button-prev-1">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</div>
						<div class="swiper-button-next courses__button-next-1">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</div>
					</div><!-- /.courses__button-controls -->

				</div><!-- /.tab-pane -->
				<?php endif; ?>

				<!-- Новые -->
				<?php if ( ! empty( $new_courses ) ) : ?>
				<div class="tab-pane fade<?php echo empty( $upcoming_courses ) ? ' show active' : ''; ?>" id="new-courses" role="tabpanel" aria-labelledby="new-courses-tab" tabindex="0">

					<div class="courses__wrapper">
						<div class="courses__inner">
							<div class="swiper swiper-courses-two">
								<div class="courses__list swiper-wrapper">

									<?php foreach ( $new_courses as $course ) : ?>
										<?php get_template_part( 'template-parts/course-card-carousel', null, array( 'course' => $course ) ); ?>
									<?php endforeach; ?>

								</div>
							</div>
						</div>

						<div class="swiper__button-controls courses__button-controls">
							<div class="swiper-button-prev courses__button-prev-2">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
							</div>
							<div class="swiper-button-next courses__button-next-2">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
							</div>
						</div><!-- /.courses__button-controls -->

					</div>
				</div><!-- /.tab-pane -->
				<?php endif; ?>

				<!-- Горящие -->
				<?php if ( ! empty( $hot_courses ) ) : ?>
				<div class="tab-pane fade<?php echo empty( $upcoming_courses ) && empty( $new_courses ) ? ' show active' : ''; ?>" id="last-minute-courses" role="tabpanel" aria-labelledby="last-minute-courses-tab"
					tabindex="0">

					<div class="courses__wrapper">
						<div class="courses__inner">
							<div class="swiper swiper-courses-three">
								<div class="courses__list swiper-wrapper">

									<?php foreach ( $hot_courses as $course ) : ?>
										<?php get_template_part( 'template-parts/course-card-carousel', null, array( 'course' => $course ) ); ?>
									<?php endforeach; ?>

								</div><!-- /.courses__list -->
							</div>
						</div><!-- /.courses__inner -->

						<div class="swiper__button-controls courses__button-controls">
							<div class="swiper-button-prev courses__button-prev-3">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
							</div>
							<div class="swiper-button-next courses__button-next-3">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
							</div>
						</div><!-- /.courses__button-controls -->

					</div><!-- /.courses__wrapper -->
				</div><!-- /.tab-pane -->
				<?php endif; ?>

			</div><!-- /.courses__tab-content -->

		</div><!-- /.container -->
	</div><!-- /.courses -->
	<?php endif; ?>

	<?php
	// Получаем галерею клиентов
	$clients_gallery = get_field( 'clients_gallery' );
	if ( $clients_gallery && ! empty( $clients_gallery ) ) :
		$clients_gallery_shuffled = $clients_gallery;
		shuffle( $clients_gallery_shuffled );
		?>
	<section class="section clients">
		<div class="container">
			<h2 class="section__title"><?php echo esc_html__( 'Наши клиенты', 'edu-center' ); ?></h2>
		</div><!-- /.container -->

		<div class="clients__container container">
			<div class="clients__wrapper">
				<div class="clients__inner">
					<div class="swiper swiper-clients">
						<div class="swiper-wrapper">
							<?php foreach ( $clients_gallery_shuffled as $image ) : 
								$image_caption = ! empty( $image['caption'] ) ? $image['caption'] : ( ! empty( $image['description'] ) ? $image['description'] : '' );
								?>
							<div class="swiper-slide clients__item">
								<div class="clients__img-wrap">
									<img class="clients__image" 
										src="<?php echo esc_url( $image['url'] ); ?>"
										width="<?php echo esc_attr( $image['width'] ); ?>"
										height="<?php echo esc_attr( $image['height'] ); ?>"
										alt="<?php echo esc_attr( $image['alt'] ?: $image['title'] ); ?>">
								</div>
								<?php if ( $image_caption ) : ?>
								<div class="clients__caption"><?php echo esc_html( $image_caption ); ?></div>
								<?php endif; ?>
							</div><!-- /.clients__item -->
							<?php endforeach; ?>
						</div><!-- /.swiper-wrapper -->
					</div><!-- /.swiper-clients -->
				</div><!-- /.clients__inner -->

				<div class="swiper__button-controls clients__button-controls">
					<div class="swiper-button-prev clients__prev">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</div>
					<div class="swiper-button-next clients__next">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</div>
				</div><!-- /.clients__button-controls -->
			</div><!-- /.clients__wrapper -->
		</div><!-- /.clients__container.container -->
	</section><!-- /.clients -->
	<?php endif; ?>

	<?php
	// Получаем записи из категории "Новости"
	// Подбираем рубрику по возможным slug (на сайте может быть novosti-uchebnogo-czentra и т.п.)
	$news_category       = false;
	$news_category_slug  = 'news';
	$news_category_slugs = array( 'news', 'novosti', 'novosti-uchebnogo-czentra' );
	foreach ( $news_category_slugs as $slug ) {
		$news_category = get_category_by_slug( $slug );
		if ( $news_category ) {
			$news_category_slug = $slug;
			break;
		}
	}
	
	$news_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'category_name'  => $news_category_slug,
			'posts_per_page' => 4, // Выводим 4 новости
			'orderby'         => 'date',
			'order'           => 'DESC', // Обратный хронологический порядок
		)
	);

	if ( $news_query->have_posts() ) :
		?>
	<section class="section news">
		<div class="news__container container">
			<h2 class="section__title"><?php echo esc_html__( 'Новости учебного центра', 'edu-center' ); ?></h2>

			<div class="news__wrapper">
				<div class="news__inner">
					<div class="swiper swiper-news">

						<div class="swiper-wrapper news__list">

							<?php
							$tabindex_counter = 0;
							while ( $news_query->have_posts() ) :
								$news_query->the_post();
								$post_id = get_the_ID();
								$post_title = get_the_title();
								$post_link = get_permalink();
								// Форматируем дату в русском формате: "14 октября, 2025"
								$post_date = date_i18n( 'd F, Y', get_post_time( 'U', true, $post_id ) );
								$post_image = get_the_post_thumbnail_url( $post_id, 'full' );
								$post_image_id = get_post_thumbnail_id( $post_id );
								$post_image_alt = $post_image_id ? get_post_meta( $post_image_id, '_wp_attachment_image_alt', true ) : $post_title;
								$post_image_meta = $post_image_id ? wp_get_attachment_image_src( $post_image_id, 'full' ) : null;
								?>
							<div class="news__item swiper-slide shine__animate-item">
								<div class="news__item-inner" tabindex="<?php echo esc_attr( $tabindex_counter ); ?>">
									<a class="news__link" href="<?php echo esc_url( $post_link ); ?>">
										<?php if ( $post_image ) : ?>
										<div class="news__image-wrap shine__animate-link">
											<img class="news__image" 
												src="<?php echo esc_url( $post_image ); ?>" 
												alt="<?php echo esc_attr( $post_image_alt ?: $post_title ); ?>"
												<?php if ( $post_image_meta ) : ?>
												width="<?php echo esc_attr( $post_image_meta[1] ); ?>" 
												height="<?php echo esc_attr( $post_image_meta[2] ); ?>"
												<?php endif; ?>
											>
										</div>
										<?php endif; ?>
										<div class="news__item-content">
											<?php if ( $post_date ) : ?>
											<div class="news__date"><?php echo esc_html( $post_date ); ?></div>
											<?php endif; ?>
											<?php if ( $post_title ) : ?>
											<div class="news__item-descr"><?php echo esc_html( $post_title ); ?></div>
											<?php endif; ?>
										</div>
									</a>
								</div><!-- /.news__item-inner-->
							</div><!-- /.news__item -->
							<?php
								$tabindex_counter++;
							endwhile;
							?>

						</div><!-- /.news__list -->

					</div><!-- /.swiper-news -->

				</div><!-- /.news__inner -->

				<div class="swiper__button-controls news__button-controls">
					<?php
					// Получаем ссылку на страницу архива категории "Новости"
					if ( ! isset( $news_category ) ) {
						$news_category = get_category_by_slug( 'news' );
						if ( ! $news_category ) {
							$news_category = get_category_by_slug( 'novosti' );
						}
					}
					$news_archive_link = $news_category ? get_category_link( $news_category->term_id ) : '#';
					?>
					<a href="<?php echo esc_url( $news_archive_link ); ?>" class="news__link-all news__link-all--mobile"><?php echo esc_html__( 'все Новости', 'edu-center' ); ?></a>
					<div class="swiper-button-prev news__prev">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round"></path>
						</svg>
					</div>
					<div class="swiper-button-next news__next">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round"></path>
						</svg>
					</div>
				</div><!-- /.news__button-controls -->

			</div><!-- /.news__wrapper -->

			<a href="<?php echo esc_url( $news_archive_link ); ?>" class="news__link-all news__link-all--desktop"><?php echo esc_html__( 'все Новости', 'edu-center' ); ?></a>

		</div><!-- /.news__container.container -->
	</section><!-- /.news -->
	<?php
		wp_reset_postdata();
	endif;
	?>

	<?php
	// Получаем галерею партнеров
	$partners_gallery = get_field( 'partners_gallery' );
	if ( $partners_gallery && ! empty( $partners_gallery ) ) :
		$partners_gallery_shuffled = $partners_gallery;
		shuffle( $partners_gallery_shuffled );
		?>
	<section class="section partners">
		<div class="container">
			<h3 class="section__title"><?php echo esc_html__( 'Наши партнёры', 'edu-center' ); ?></h3>
		</div><!-- /.container -->

		<div class="partners__container container">
			<div class="partners__wrapper">
				<div class="partners__inner">
					<div class="swiper swiper-partners">
						<div class="swiper-wrapper">
							<?php foreach ( $partners_gallery_shuffled as $image ) : ?>
							<div class="swiper-slide partners__item">
								<img class="partners__image" 
									src="<?php echo esc_url( $image['url'] ); ?>"
									width="<?php echo esc_attr( $image['width'] ); ?>"
									height="<?php echo esc_attr( $image['height'] ); ?>"
									alt="<?php echo esc_attr( $image['alt'] ?: $image['title'] ); ?>">
							</div><!-- /.partners__item -->
							<?php endforeach; ?>
						</div><!-- /.swiper-wrapper -->
					</div><!-- /.swiper-partners -->
				</div><!-- /.partners__inner -->

				<div class="swiper__button-controls partners__button-controls">
					<div class="swiper-button-prev partners__prev">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 8H1M1 8L8 1M1 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</div>
					<div class="swiper-button-next partners__next">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</div>
				</div><!-- /.partners__button-controls -->
			</div><!-- /.partners__wrapper -->
		</div><!-- /.partners__container.container -->
	</section><!-- /.partners -->
	<?php endif; ?>

	</div><!-- /.slogan__wrapper -->

	<?php
	endwhile; // End of the loop.
	?>

<?php
get_footer();
