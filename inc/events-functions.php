<?php
/**
 * Events Manager: scope, мета событий, связь с курсами LearnPress, админ-метабокс.
 *
 * @package edu-center
 */

if ( ! defined( 'EM_POST_TYPE_EVENT' ) ) {
	return;
}

/**
 * Имя scope по опции «Default list scope» Events Manager (dbem_events_default_scope).
 *
 * @return string
 */
function edu_em_get_default_scope_name(): string {
	$scope = em_get_option( 'dbem_events_default_scope', 'future' );
	if ( is_array( $scope ) && ! empty( $scope['name'] ) ) {
		return (string) $scope['name'];
	}
	return is_string( $scope ) ? $scope : 'future';
}

/**
 * WordPress post_id опубликованных одобренных событий из меты курса (_course_event_ids — EM event_id).
 *
 * @param int[]|mixed $event_em_ids
 * @return int[]
 */
function edu_em_collect_event_post_ids_from_course_meta( mixed $event_em_ids ): array {
	$post_ids = array();
	if ( empty( $event_em_ids ) || ! is_array( $event_em_ids ) || ! class_exists( 'EM_Event' ) ) {
		return $post_ids;
	}
	foreach ( $event_em_ids as $eid ) {
		$eid = (int) $eid;
		if ( ! $eid ) {
			continue;
		}
		$em = em_get_event( $eid, 'event_id' );
		if ( ! $em || empty( $em->event_id ) || $em->post_status !== 'publish' || (int) $em->event_status !== 1 || empty( $em->post_id ) ) {
			continue;
		}
		$post_ids[] = (int) $em->post_id;
	}
	return array_values( array_unique( $post_ids ) );
}

/**
 * События курса в том же scope, что публичные списки EM (dbem_events_default_scope, orderby/order по умолчанию).
 *
 * @param int[]|mixed $event_em_ids
 * @return EM_Event[]
 */
function edu_em_get_course_events_in_default_scope( mixed $event_em_ids ): array {
	if ( ! class_exists( 'EM_Events' ) ) {
		return array();
	}
	$post_ids = edu_em_collect_event_post_ids_from_course_meta( $event_em_ids );
	if ( empty( $post_ids ) ) {
		return array();
	}
	$scope = em_get_option( 'dbem_events_default_scope', 'future' );
	$events = EM_Events::get(
		array(
			'post_id' => $post_ids,
			'scope'   => $scope,
			'status'  => 1,
			'blog'    => get_current_blog_id(),
		)
	);
	return is_array( $events ) ? array_values( $events ) : array();
}

/**
 * Группа для блока «Ближайшая»: среди событий в scope — ближайшая к «сейчас» (past — самая поздняя дата старта; иначе — самая ранняя).
 *
 * @param EM_Event[] $scoped_events
 * @return EM_Event|null
 */
function edu_em_pick_nearest_group_in_scope( array $scoped_events ): ?EM_Event {
	$scope_name = edu_em_get_default_scope_name();
	$pick       = null;
	$pick_ts    = null;
	foreach ( $scoped_events as $em ) {
		if ( ! $em instanceof EM_Event || empty( $em->event_id ) ) {
			continue;
		}
		$ts = strtotime( trim( $em->event_start_date . ' ' . $em->event_start_time ) );
		if ( ! $ts ) {
			continue;
		}
		if ( $pick === null ) {
			$pick    = $em;
			$pick_ts = $ts;
			continue;
		}
		if ( $scope_name === 'past' ) {
			if ( $ts > $pick_ts ) {
				$pick    = $em;
				$pick_ts = $ts;
			}
		} elseif ( $ts < $pick_ts ) {
			$pick    = $em;
			$pick_ts = $ts;
		}
	}
	return $pick;
}

/**
 * Ближайшая группа курса в том же scope, что списки EM (_course_event_ids).
 *
 * @param int $course_id ID поста lp_course.
 * @return EM_Event|null
 */
function edu_em_get_nearest_course_group_in_default_scope( int $course_id ): ?EM_Event {
	if ( $course_id <= 0 ) {
		return null;
	}
	$event_ids = get_post_meta( $course_id, '_course_event_ids', true );
	if ( empty( $event_ids ) || ! is_array( $event_ids ) ) {
		return null;
	}
	$scoped = edu_em_get_course_events_in_default_scope( $event_ids );
	return edu_em_pick_nearest_group_in_scope( $scoped );
}

/**
 * Даты старта для календаря в модалке записи: те же события, что в scope списков EM (как блок расписания в single-course).
 *
 * @param int $course_id ID lp_course.
 * @return string[] Уникальные Y-m-d, сегодня и будущие, по возрастанию.
 */
function edu_get_lp_course_enroll_modal_allowed_dates_from_scope( int $course_id ): array {
	if ( $course_id <= 0 || get_post_type( $course_id ) !== 'lp_course' ) {
		return array();
	}
	$event_ids = get_post_meta( $course_id, '_course_event_ids', true );
	if ( empty( $event_ids ) || ! is_array( $event_ids ) ) {
		return array();
	}
	$course_scoped_events = edu_em_get_course_events_in_default_scope( $event_ids );
	if ( empty( $course_scoped_events ) ) {
		return array();
	}
	$today = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
	$dates = array_values(
		array_unique(
			array_map(
				function ( $e ) {
					return $e->event_start_date;
				},
				$course_scoped_events
			)
		)
	);
	$dates = array_values(
		array_filter(
			$dates,
			function ( $d ) use ( $today ) {
				return $d >= $today;
			}
		)
	);
	sort( $dates );
	return $dates;
}

/**
 * Пост события (EM) попадает в текущий default scope списков.
 *
 * @param int $event_post_id post_id типа event.
 * @return bool
 */
function edu_em_event_post_in_default_scope( int $event_post_id ): bool {
	if ( $event_post_id <= 0 || ! class_exists( 'EM_Events' ) ) {
		return false;
	}
	$scope = em_get_option( 'dbem_events_default_scope', 'future' );
	$found = EM_Events::get(
		array(
			'post_id' => array( $event_post_id ),
			'scope'   => $scope,
			'status'  => 1,
			'blog'    => get_current_blog_id(),
		)
	);
	return ! empty( $found );
}

/**
 * Метабокс настроек события (вкладки «Общие» / «Цены»).
 */
class Edu_Center_Event_Settings_Meta_Box {

	/**
	 * Post type
	 *
	 * @var string
	 */
	private $post_type = 'event';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add meta box
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'edu-event-settings',
			esc_html__( 'Event Settings', 'edu-center' ),
			array( $this, 'render_meta_box' ),
			$this->post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Render meta box
	 *
	 * @param WP_Post $post Post object
	 */
	public function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'edu_event_settings_meta_box', 'edu_event_settings_meta_box_nonce' );

		$tabs = apply_filters(
			'edu_event_settings_tabs',
			array(
				'general' => array(
					'label'    => esc_html__( 'Общие', 'edu-center' ),
					'target'   => 'general_event_data',
					'icon'     => 'dashicons-admin-tools',
					'priority' => 10,
					'content'  => array( $this, 'general_tab' ),
				),
				'pricing' => array(
					'label'    => esc_html__( 'Цены', 'edu-center' ),
					'target'   => 'pricing_event_data',
					'icon'     => 'dashicons-cart',
					'priority' => 20,
					'content'  => array( $this, 'pricing_tab' ),
				),
			)
		);

		// Sort tabs by priority
		uasort( $tabs, array( $this, 'sort_tabs' ) );

		?>
		<div class="edu-event-settings-meta-box">
			<div class="edu-event-settings-inner">
				<div class="edu-event-settings-tab">
					<ul class="edu-event-settings-tabs-nav">
						<?php
						$first = true;
						foreach ( $tabs as $key => $tab ) {
							$active = $first ? ' active' : '';
							$first  = false;
							?>
							<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab<?php echo esc_attr( $active ); ?>">
								<a href="#<?php echo esc_attr( $tab['target'] ); ?>" data-target="<?php echo esc_attr( $tab['target'] ); ?>">
									<?php if ( ! empty( $tab['icon'] ) ) : ?>
										<i class="<?php echo esc_attr( $tab['icon'] ); ?>"></i>
									<?php endif; ?>
									<span><?php echo esc_html( $tab['label'] ); ?></span>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					<div class="edu-event-settings-tabs-content">
						<?php
						$first = true;
						foreach ( $tabs as $key => $tab ) {
							$active = $first ? ' active' : '';
							$first  = false;
							?>
							<div id="<?php echo esc_attr( $tab['target'] ); ?>" class="edu-event-settings-tab-content<?php echo esc_attr( $active ); ?>">
								<?php
								if ( is_callable( $tab['content'] ) ) {
									// Get content from method (it should return string)
									$content = call_user_func( $tab['content'], $post->ID );
									// Output without filtering to preserve input tags
									echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo wp_kses_post( $tab['content'] );
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * General tab content
	 *
	 * @param int $post_id Post ID
	 * @return string HTML content
	 */
	private function general_tab( int $post_id ): string {
		$event_description = get_post_meta( $post_id, '_edu_event_description', true );
		$event_location    = get_post_meta( $post_id, '_edu_event_location', true );
		$event_organizer   = get_post_meta( $post_id, '_edu_event_organizer', true );
		$event_weekdays    = get_post_meta( $post_id, '_edu_event_weekdays', true );
		$event_learning_format = get_post_meta( $post_id, '_edu_event_learning_format', true );
		$related_course_id     = (int) get_post_meta( $post_id, '_related_course_id', true );
		$related_course_title  = '';
		if ( $related_course_id && get_post_type( $related_course_id ) === 'lp_course' ) {
			$related_course_title = get_the_title( $related_course_id );
		} elseif ( $related_course_id ) {
			$related_course_title = sprintf(
				/* translators: %d: invalid course post ID */
				__( 'Несуществующий или удалённый курс (ID %d)', 'edu-center' ),
				$related_course_id
			);
		}

		ob_start();
		?>
		<div class="edu-event-settings-fields">
			<div class="form-field">
				<label for="edu-related-course-search">
					<?php esc_html_e( 'Связанный курс', 'edu-center' ); ?>
				</label>
				<input type="hidden" name="_related_course_id" id="edu-related-course-id" value="<?php echo $related_course_id ? esc_attr( (string) $related_course_id ) : ''; ?>" />
				<input
					type="text"
					id="edu-related-course-search"
					class="regular-text edu-related-course-search"
					autocomplete="off"
					placeholder="<?php esc_attr_e( 'Начните вводить название курса…', 'edu-center' ); ?>"
					value="<?php echo esc_attr( $related_course_title ); ?>"
				/>
				<?php if ( $related_course_id && get_post_type( $related_course_id ) === 'lp_course' ) : ?>
					<p style="margin: 6px 0 0;">
						<a href="<?php echo esc_url( get_edit_post_link( $related_course_id, 'raw' ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Открыть курс в новой вкладке', 'edu-center' ); ?>
						</a>
					</p>
				<?php endif; ?>
				<p style="margin-top: 8px;">
					<button type="button" class="button button-small edu-related-course-clear">
						<?php esc_html_e( 'Снять привязку', 'edu-center' ); ?>
					</button>
				</p>
				<p class="description">
					<?php esc_html_e( 'Поиск по названию опубликованных и черновиков курсов LearnPress. После выбора сохраните событие.', 'edu-center' ); ?>
				</p>
			</div>
			<div class="form-field">
				<label for="_edu_event_description">
					<?php esc_html_e( 'Описание события', 'edu-center' ); ?>
				</label>
				<textarea 
					name="_edu_event_description" 
					id="_edu_event_description" 
					class="large-text" 
					rows="5"
				><?php echo esc_textarea( $event_description ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Дополнительное описание события', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_location">
					<?php esc_html_e( 'Место проведения', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_location" 
					id="_edu_event_location" 
					class="regular-text" 
					value="<?php echo esc_attr( $event_location ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите место проведения события', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_organizer">
					<?php esc_html_e( 'Организатор', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_organizer" 
					id="_edu_event_organizer" 
					class="regular-text" 
					value="<?php echo esc_attr( $event_organizer ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите организатора события', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_weekdays">
					<?php esc_html_e( 'Дни недели', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_weekdays" 
					id="_edu_event_weekdays" 
					class="regular-text" 
					value="<?php echo esc_attr( $event_weekdays ); ?>"
					placeholder="<?php esc_attr_e( 'Например: Пн/Ср/Пт', 'edu-center' ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите дни недели проведения события, например: Пн/Ср/Пт или Пн, Ср, Пт', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_learning_format">
					<?php esc_html_e( 'Формат обучения', 'edu-center' ); ?>
				</label>
				<select 
					name="_edu_event_learning_format" 
					id="_edu_event_learning_format" 
					class="regular-text"
				>
					<option value=""><?php esc_html_e( 'Выберите формат', 'edu-center' ); ?></option>
					<option value="Очно" <?php selected( $event_learning_format, 'Очно' ); ?>><?php esc_html_e( 'Очно', 'edu-center' ); ?></option>
					<option value="Онлайн" <?php selected( $event_learning_format, 'Онлайн' ); ?>><?php esc_html_e( 'Онлайн', 'edu-center' ); ?></option>
					<option value="Гибридный" <?php selected( $event_learning_format, 'Гибридный' ); ?>><?php esc_html_e( 'Гибридный', 'edu-center' ); ?></option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Выберите формат обучения', 'edu-center' ); ?>
				</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Pricing tab content
	 *
	 * @param int $post_id Post ID
	 * @return string HTML content
	 */
	private function pricing_tab( int $post_id ): string {
		$event_price_legal           = get_post_meta( $post_id, '_edu_event_price_legal', true );
		$event_price_legal_sale      = get_post_meta( $post_id, '_edu_event_price_legal_sale', true );
		$event_price_individual      = get_post_meta( $post_id, '_edu_event_price_individual', true );
		$event_price_individual_sale = get_post_meta( $post_id, '_edu_event_price_individual_sale', true );
		$event_sale_start            = get_post_meta( $post_id, '_edu_event_sale_start', true );
		$event_sale_end              = get_post_meta( $post_id, '_edu_event_sale_end', true );
		$event_price_suffix          = get_post_meta( $post_id, '_edu_event_price_suffix', true );

		ob_start();
		?>
		<div class="edu-event-settings-fields">
			<div class="form-field">
				<label for="_edu_event_price_legal">
					<?php esc_html_e( 'Цена для юр. лица', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_price_legal" 
					id="_edu_event_price_legal" 
					class="regular-text" 
					style="width: 150px;"
					value="<?php echo esc_attr( $event_price_legal ); ?>"
					placeholder="0.00"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите цену для юридических лиц. Оставьте пустым для бесплатного события.', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_price_legal_sale">
					<?php esc_html_e( 'Цена со скидкой для юр. лица', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_price_legal_sale" 
					id="_edu_event_price_legal_sale" 
					class="regular-text" 
					style="width: 150px;"
					value="<?php echo esc_attr( $event_price_legal_sale ); ?>"
					placeholder="0.00"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите цену со скидкой для юридических лиц (если применимо)', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_sale_start">
					<?php esc_html_e( 'Дата начала скидки', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_sale_start" 
					id="_edu_event_sale_start" 
					class="regular-text edu-datepicker" 
					value="<?php echo esc_attr( $event_sale_start ); ?>"
					placeholder="<?php esc_attr_e( 'От...', 'edu-center' ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите дату начала действия скидки', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_sale_end">
					<?php esc_html_e( 'Дата окончания скидки', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_sale_end" 
					id="_edu_event_sale_end" 
					class="regular-text edu-datepicker" 
					value="<?php echo esc_attr( $event_sale_end ); ?>"
					placeholder="<?php esc_attr_e( 'До...', 'edu-center' ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите дату окончания действия скидки', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_price_suffix">
					<?php esc_html_e( 'Суффикс цены', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_price_suffix" 
					id="_edu_event_price_suffix" 
					class="regular-text" 
					value="<?php echo esc_attr( $event_price_suffix ); ?>"
					placeholder="<?php esc_attr_e( 'Например: Включая НДС, За час, (За неделю)...', 'edu-center' ); ?>"
				/>
				<p class="description">
					<?php esc_html_e( 'Дополнительная информация после цены, например: Включая НДС, За час, (За неделю)...', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_price_individual">
					<?php esc_html_e( 'Цена для физ. лица', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_price_individual" 
					id="_edu_event_price_individual" 
					class="regular-text" 
					style="width: 150px;"
					value="<?php echo esc_attr( $event_price_individual ); ?>"
					placeholder="0.00"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите цену для физических лиц', 'edu-center' ); ?>
				</p>
			</div>

			<div class="form-field">
				<label for="_edu_event_price_individual_sale">
					<?php esc_html_e( 'Цена со скидкой для физ. лица', 'edu-center' ); ?>
				</label>
				<input 
					type="text" 
					name="_edu_event_price_individual_sale" 
					id="_edu_event_price_individual_sale" 
					class="regular-text" 
					style="width: 150px;"
					value="<?php echo esc_attr( $event_price_individual_sale ); ?>"
					placeholder="0.00"
				/>
				<p class="description">
					<?php esc_html_e( 'Укажите цену со скидкой для физических лиц (если применимо)', 'edu-center' ); ?>
				</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Save meta box data
	 *
	 * @param int     $post_id Post ID
	 * @param WP_Post $post    Post object
	 */
	public function save_meta_box( int $post_id, WP_Post $post ): void {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if this is the correct post type
		if ( $post->post_type !== $this->post_type ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check nonce
		if ( ! isset( $_POST['edu_event_settings_meta_box_nonce'] ) ||
			! wp_verify_nonce( $_POST['edu_event_settings_meta_box_nonce'], 'edu_event_settings_meta_box' )
		) {
			return;
		}

		// Save general fields
		if ( isset( $_POST['_edu_event_description'] ) ) {
			update_post_meta( $post_id, '_edu_event_description', sanitize_textarea_field( $_POST['_edu_event_description'] ) );
		}

		if ( isset( $_POST['_edu_event_location'] ) ) {
			update_post_meta( $post_id, '_edu_event_location', sanitize_text_field( $_POST['_edu_event_location'] ) );
		}

		if ( isset( $_POST['_edu_event_organizer'] ) ) {
			update_post_meta( $post_id, '_edu_event_organizer', sanitize_text_field( $_POST['_edu_event_organizer'] ) );
		}

		if ( isset( $_POST['_edu_event_weekdays'] ) ) {
			update_post_meta( $post_id, '_edu_event_weekdays', sanitize_text_field( $_POST['_edu_event_weekdays'] ) );
		}

		if ( isset( $_POST['_edu_event_learning_format'] ) ) {
			update_post_meta( $post_id, '_edu_event_learning_format', sanitize_text_field( $_POST['_edu_event_learning_format'] ) );
		}

		// Save pricing fields (сохраняем даже пустые значения для очистки)
		$price_legal = isset( $_POST['_edu_event_price_legal'] ) ? sanitize_text_field( $_POST['_edu_event_price_legal'] ) : '';
		update_post_meta( $post_id, '_edu_event_price_legal', $price_legal );

		$price_legal_sale = isset( $_POST['_edu_event_price_legal_sale'] ) ? sanitize_text_field( $_POST['_edu_event_price_legal_sale'] ) : '';
		update_post_meta( $post_id, '_edu_event_price_legal_sale', $price_legal_sale );

		$price_individual = isset( $_POST['_edu_event_price_individual'] ) ? sanitize_text_field( $_POST['_edu_event_price_individual'] ) : '';
		update_post_meta( $post_id, '_edu_event_price_individual', $price_individual );

		$price_individual_sale = isset( $_POST['_edu_event_price_individual_sale'] ) ? sanitize_text_field( $_POST['_edu_event_price_individual_sale'] ) : '';
		update_post_meta( $post_id, '_edu_event_price_individual_sale', $price_individual_sale );

		// Save sale dates
		if ( isset( $_POST['_edu_event_sale_start'] ) ) {
			update_post_meta( $post_id, '_edu_event_sale_start', sanitize_text_field( $_POST['_edu_event_sale_start'] ) );
		}

		if ( isset( $_POST['_edu_event_sale_end'] ) ) {
			update_post_meta( $post_id, '_edu_event_sale_end', sanitize_text_field( $_POST['_edu_event_sale_end'] ) );
		}

		// Save price suffix
		if ( isset( $_POST['_edu_event_price_suffix'] ) ) {
			update_post_meta( $post_id, '_edu_event_price_suffix', sanitize_text_field( $_POST['_edu_event_price_suffix'] ) );
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_scripts( string $hook ): void {
		global $post_type;

		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		if ( $this->post_type !== $post_type ) {
			return;
		}

		// Enqueue styles
		wp_add_inline_style( 'wp-admin', $this->get_inline_styles() );

		// Enqueue scripts
		wp_add_inline_script( 'jquery', $this->get_inline_scripts() );

		// Enqueue jQuery UI Datepicker for date fields
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-datepicker', 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css', array(), '1.13.2' );

		wp_register_script(
			'edu-event-admin-course-search',
			false,
			array( 'jquery', 'jquery-ui-autocomplete' ),
			false,
			true
		);
		wp_enqueue_script( 'edu-event-admin-course-search' );
		wp_localize_script(
			'edu-event-admin-course-search',
			'eduEventCourseSearch',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'edu_admin_search_lp_courses' ),
			)
		);
		wp_add_inline_script( 'edu-event-admin-course-search', $this->get_course_search_inline_script() );
	}

	/**
	 * Get inline styles
	 *
	 * @return string
	 */
	private function get_inline_styles(): string {
		$event_body_class = 'post-type-' . sanitize_html_class( EM_POST_TYPE_EVENT, 'event' );

		return '
		.edu-event-settings-meta-box {
			margin-top: 20px;
		}
		.edu-event-settings-inner {
			display: flex;
			gap: 20px;
		}
		.edu-event-settings-tab {
			display: flex;
			width: 100%;
			gap: 20px;
		}
		.edu-event-settings-tabs-nav {
			display: flex;
			flex-direction: column;
			width: 200px;
			min-width: 200px;
			margin: 0;
			padding: 0;
			list-style: none;
			border-right: 1px solid #ddd;
		}
		.edu-event-settings-tabs-nav li {
			margin: 0;
			padding: 0;
		}
		.edu-event-settings-tabs-nav li a {
			display: flex;
			align-items: center;
			padding: 12px 15px;
			text-decoration: none;
			color: #555;
			border-right: 3px solid transparent;
			transition: all 0.2s ease;
		}
		.edu-event-settings-tabs-nav li a i {
			margin-right: 8px;
			width: 20px;
			text-align: center;
			font-size: 18px;
			line-height: 1;
		}
		.edu-event-settings-tabs-nav li a i.dashicons {
			display: inline-block;
		}
		.edu-event-settings-tabs-nav li.active a,
		.edu-event-settings-tabs-nav li a:hover {
			color: #2271b1;
			background-color: #f0f6fc;
			border-right-color: #2271b1;
		}
		.edu-event-settings-tabs-content {
			flex: 1;
			min-width: 0;
		}
		.edu-event-settings-tab-content {
			display: none;
		}
		.edu-event-settings-tab-content.active {
			display: block;
		}
		.edu-event-settings-fields .form-field {
			margin-bottom: 20px;
		}
		.edu-event-settings-fields .form-field label {
			display: block;
			font-weight: 600;
			margin-bottom: 5px;
		}
		.edu-event-settings-fields .form-field input[type="text"],
		.edu-event-settings-fields .form-field textarea {
			width: 100%;
			max-width: 600px;
		}
		body.' . $event_body_class . ' .ui-autocomplete {
			z-index: 1000000 !important;
			max-width: 640px;
		}
		';
	}

	/**
	 * Get inline scripts
	 *
	 * @return string
	 */
	private function get_inline_scripts(): string {
		return "
		jQuery(document).ready(function($) {
			// Tab switching
			$('.edu-event-settings-tabs-nav li a').on('click', function(e) {
				e.preventDefault();
				var target = $(this).data('target');
				
				// Remove active class from all tabs
				$('.edu-event-settings-tabs-nav li').removeClass('active');
				$('.edu-event-settings-tab-content').removeClass('active');
				
				// Add active class to clicked tab
				$(this).parent().addClass('active');
				$('#' + target).addClass('active');
			});

			// Initialize datepicker for date fields
			if ($.fn.datepicker) {
				$('.edu-datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true
				});
			}
		});
		";
	}

	/**
	 * Inline script: автодополнение курса для метабокса события.
	 *
	 * @return string
	 */
	private function get_course_search_inline_script(): string {
		return '
		jQuery(function($) {
			var $id = $("#edu-related-course-id");
			var $search = $("#edu-related-course-search");
			if (!$search.length) {
				return;
			}
			$search.autocomplete({
				minLength: 2,
				source: function(request, response) {
					$.getJSON(eduEventCourseSearch.url, {
						action: "edu_admin_search_lp_courses",
						nonce: eduEventCourseSearch.nonce,
						term: request.term
					}).done(response).fail(function() { response([]); });
				},
				select: function(event, ui) {
					$id.val(ui.item.value);
					$search.val(ui.item.label);
					return false;
				},
				focus: function(event) {
					event.preventDefault();
				}
			});
			$(document).on("click", ".edu-related-course-clear", function(e) {
				e.preventDefault();
				$id.val("");
				$search.val("");
			});
		});
		';
	}

	/**
	 * Sort tabs by priority
	 *
	 * @param array $a First tab
	 * @param array $b Second tab
	 * @return int
	 */
	private function sort_tabs( array $a, array $b ): int {
		$a_priority = isset( $a['priority'] ) ? $a['priority'] : 10;
		$b_priority = isset( $b['priority'] ) ? $b['priority'] : 10;

		if ( $a_priority === $b_priority ) {
			return 0;
		}

		return $a_priority < $b_priority ? -1 : 1;
	}
}

/**
 * Обновить массив _course_event_ids на курсах при смене привязки события.
 *
 * @param int $event_post_id ID поста события.
 * @param int $old_course_id Предыдущий lp_course (0 — не было).
 * @param int $new_course_id Новый lp_course (0 — снять привязку).
 */
function edu_sync_event_course_link_lists( int $event_post_id, int $old_course_id, int $new_course_id ): void {
	$event_post_id   = (int) $event_post_id;
	$old_course_id   = (int) $old_course_id;
	$new_course_id   = (int) $new_course_id;
	$old_course_id   = $old_course_id > 0 ? $old_course_id : 0;
	$new_course_id   = $new_course_id > 0 ? $new_course_id : 0;

	if ( ! class_exists( 'EM_Event' ) ) {
		return;
	}

	$em_event = new EM_Event( $event_post_id, 'post_id' );
	$em_eid   = (int) $em_event->event_id;
	if ( ! $em_eid ) {
		return;
	}

	$remove_from_course = static function ( $course_id ) use ( $em_eid ) {
		if ( $course_id <= 0 ) {
			return;
		}
		$ids = get_post_meta( $course_id, '_course_event_ids', true );
		if ( ! is_array( $ids ) ) {
			return;
		}
		$ids = array_values( array_filter( array_map( 'intval', $ids ) ) );
		$ids = array_values( array_diff( $ids, array( $em_eid ) ) );
		update_post_meta( $course_id, '_course_event_ids', $ids );
	};

	$add_to_course = static function ( $course_id ) use ( $em_eid ) {
		if ( $course_id <= 0 || get_post_type( $course_id ) !== 'lp_course' ) {
			return;
		}
		$ids = get_post_meta( $course_id, '_course_event_ids', true );
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}
		if ( ! in_array( $em_eid, $ids, true ) ) {
			$ids[] = $em_eid;
			update_post_meta( $course_id, '_course_event_ids', $ids );
		}
	};

	if ( $old_course_id && $old_course_id !== $new_course_id ) {
		$remove_from_course( $old_course_id );
	}
	if ( $new_course_id ) {
		$add_to_course( $new_course_id );
	}
}

/**
 * AJAX: поиск курсов LearnPress для автодополнения в админке события.
 */
function edu_admin_search_lp_courses_ajax(): void {
	if ( ! check_ajax_referer( 'edu_admin_search_lp_courses', 'nonce', false ) ) {
		wp_send_json( array() );
	}

	$lp_type = get_post_type_object( 'lp_course' );
	$can_search_courses = $lp_type ? current_user_can( $lp_type->cap->edit_posts ) : current_user_can( 'edit_posts' );
	if ( ! $can_search_courses ) {
		wp_send_json( array() );
	}

	$term = isset( $_REQUEST['term'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : '';
	if ( strlen( $term ) < 2 ) {
		wp_send_json( array() );
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'lp_course',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			's'                      => $term,
			'posts_per_page'         => 20,
			'orderby'                => 'relevance',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
		)
	);

	$out = array();
	foreach ( $query->posts as $p ) {
		$out[] = array(
			'label' => $p->post_title . ' (ID: ' . (int) $p->ID . ')',
			'value' => (string) (int) $p->ID,
		);
	}

	wp_send_json( $out );
}
add_action( 'wp_ajax_edu_admin_search_lp_courses', 'edu_admin_search_lp_courses_ajax' );

/**
 * Сохранение привязки события к курсу из метабокса (после того как EM успел выставить event_id).
 */
function edu_event_admin_save_related_course( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}

	if ( ! $post || $post->post_type !== EM_POST_TYPE_EVENT ) {
		return;
	}

	if ( ! isset( $_POST['edu_event_settings_meta_box_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['edu_event_settings_meta_box_nonce'] ) ), 'edu_event_settings_meta_box' ) ) {
		return;
	}

	if ( ! isset( $_POST['_related_course_id'] ) ) {
		return;
	}

	$new_course_id = absint( wp_unslash( $_POST['_related_course_id'] ) );
	if ( $new_course_id > 0 && get_post_type( $new_course_id ) !== 'lp_course' ) {
		return;
	}

	$old_course_id = (int) get_post_meta( $post_id, '_related_course_id', true );

	if ( $new_course_id > 0 ) {
		update_post_meta( $post_id, '_related_course_id', $new_course_id );
	} else {
		delete_post_meta( $post_id, '_related_course_id' );
	}

	edu_sync_event_course_link_lists( (int) $post_id, $old_course_id, $new_course_id );
}
add_action( 'save_post_event', 'edu_event_admin_save_related_course', 12, 3 );

/**
 * Такса тегов событий Events Manager.
 *
 * @return string
 */
function edu_em_get_event_tags_taxonomy(): string {
	return defined( 'EM_TAXONOMY_TAG' ) ? EM_TAXONOMY_TAG : 'event-tags';
}

/**
 * term_id термина «Горящий» (по имени или slug в БД, например goryashhij).
 *
 * @return int
 */
function edu_em_get_hot_tag_term_id(): int {
	$tag_taxonomy = edu_em_get_event_tags_taxonomy();
	if ( ! taxonomy_exists( $tag_taxonomy ) ) {
		return 0;
	}

	$found = term_exists( 'Горящий', $tag_taxonomy );
	if ( ! $found ) {
		$found = term_exists( 'goryashhij', $tag_taxonomy );
	}
	if ( $found ) {
		return is_array( $found ) ? (int) $found['term_id'] : (int) $found;
	}

	$tag_result = wp_insert_term(
		'Горящий',
		$tag_taxonomy,
		array( 'slug' => 'goryashhij' )
	);
	if ( is_wp_error( $tag_result ) ) {
		if ( $tag_result->get_error_code() === 'term_exists' ) {
			$data = $tag_result->get_error_data();
			if ( is_array( $data ) && isset( $data['term_id'] ) ) {
				return (int) $data['term_id'];
			}
			if ( is_numeric( $data ) ) {
				return (int) $data;
			}
		}
		$again = get_term_by( 'slug', 'goryashhij', $tag_taxonomy );
		return ( $again && ! is_wp_error( $again ) ) ? (int) $again->term_id : 0;
	}

	return (int) $tag_result['term_id'];
}

/**
 * HEX цвета тега «Горящий» из настроек EM (поле Color у термина, meta tag-bgcolor).
 *
 * @return string Напр. #e74c3c или пустая строка.
 */
function edu_em_get_hot_tag_color_hex(): string {
	$tid = edu_em_get_hot_tag_term_id();
	if ( ! $tid ) {
		return '';
	}
	$hex = '';
	if ( class_exists( 'EM_Tag' ) ) {
		$em_tag = EM_Tag::get( $tid );
		if ( $em_tag && ! empty( $em_tag->term_id ) && method_exists( $em_tag, 'get_color' ) ) {
			$c = $em_tag->get_color();
			if ( is_string( $c ) && $c !== '' ) {
				$hex = sanitize_hex_color( $c );
				if ( ! $hex && preg_match( '/^[0-9a-fA-F]{3,6}$/', trim( $c ) ) ) {
					$hex = sanitize_hex_color( '#' . trim( $c ) );
				}
			}
		}
	}
	if ( $hex === '' && defined( 'EM_META_TABLE' ) ) {
		global $wpdb;
		$c = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT meta_value FROM ' . EM_META_TABLE . ' WHERE object_id = %d AND meta_key = %s LIMIT 1',
				$tid,
				'tag-bgcolor'
			)
		);
		if ( is_string( $c ) && $c !== '' ) {
			$hex = sanitize_hex_color( $c );
			if ( ! $hex && preg_match( '/^[0-9a-fA-F]{3,6}$/', trim( $c ) ) ) {
				$hex = sanitize_hex_color( '#' . trim( $c ) );
			}
		}
	}
	/**
	 * Filters the CSS color (hex) for the «Горящий» tag label.
	 *
	 * @param string $hex Hex color or empty.
	 * @param int    $tid Term ID of the hot tag.
	 */
	$hex = apply_filters( 'edu_em_hot_tag_color_hex', $hex, $tid );
	return is_string( $hex ) ? $hex : '';
}

/**
 * HTML: подпись «Горящий» с цветом шрифта из EM (поле Color тега).
 *
 * @return string
 */
function edu_em_html_hot_tag_label_span(): string {
	$color = edu_em_get_hot_tag_color_hex();
	$style = $color !== '' ? ' style="color:' . esc_attr( $color ) . ';"' : '';
	return '<span' . $style . '>' . esc_html__( 'Горящий', 'edu-center' ) . '</span>';
}

/**
 * Назначить или снять тег «Горящий» у поста события (остальные теги event-tags сохраняются).
 *
 * @param int  $event_post_id post_id типа event.
 * @param bool $assign        true — добавить тег.
 */
function edu_em_set_hot_tag_on_event_post( int $event_post_id, bool $assign ): void {
	if ( $event_post_id <= 0 ) {
		return;
	}
	$tag_taxonomy = edu_em_get_event_tags_taxonomy();
	if ( ! taxonomy_exists( $tag_taxonomy ) ) {
		return;
	}
	$hot_id = edu_em_get_hot_tag_term_id();
	if ( ! $hot_id ) {
		return;
	}
	if ( $assign ) {
		$existing = wp_get_object_terms( $event_post_id, $tag_taxonomy, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $existing ) ) {
			$existing = array();
		}
		$ids = array_map( 'intval', $existing );
		if ( ! in_array( $hot_id, $ids, true ) ) {
			$ids[] = $hot_id;
		}
		wp_set_object_terms( $event_post_id, $ids, $tag_taxonomy );
	} else {
		wp_remove_object_terms( $event_post_id, $hot_id, $tag_taxonomy );
	}
}

/**
 * Чекбокс «Горящий» в AJAX форме курса.
 *
 * @return bool
 */
function edu_em_request_wants_hot_tag(): bool {
	return isset( $_POST['hot'] ) && '1' === (string) wp_unslash( $_POST['hot'] );
}

/**
 * У поста события (EM) есть тег «Горящий» в таксономии тегов событий.
 * Сверяем фактические термины поста (без has_term — slug в БД может отличаться от ожидаемой строки).
 *
 * @param int $event_post_id post_id типа event.
 * @return bool
 */
function edu_em_event_post_has_hot_tag( int $event_post_id ): bool {
	if ( $event_post_id <= 0 ) {
		return false;
	}
	$tax = edu_em_get_event_tags_taxonomy();
	if ( ! taxonomy_exists( $tax ) ) {
		return false;
	}
	$terms = wp_get_object_terms( $event_post_id, $tax );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return false;
	}

	$hot_id = edu_em_get_hot_tag_term_id();
	foreach ( $terms as $term ) {
		if ( ! isset( $term->term_id ) ) {
			continue;
		}
		if ( $hot_id && (int) $term->term_id === $hot_id ) {
			return true;
		}
		if ( isset( $term->slug ) && $term->slug === 'goryashhij' ) {
			return true;
		}
		if ( isset( $term->name ) && $term->name === 'Горящий' ) {
			return true;
		}
	}

	return false;
}

/**
 * Строка из $_POST для AJAX (события / курс).
 */
function edu_em_ajax_post_text( string $key ): string {
	if ( ! isset( $_POST[ $key ] ) ) {
		return '';
	}
	return sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) );
}

/**
 * Целое из $_POST для AJAX.
 */
function edu_em_ajax_post_int( string $key ): int {
	if ( ! isset( $_POST[ $key ] ) ) {
		return 0;
	}
	return absint( wp_unslash( $_POST[ $key ] ) );
}

/**
 * Время HH:mm → HH:mm:ss для EM.
 */
function edu_em_normalize_ajax_time_hms( string $time ): string {
	if ( $time !== '' && strlen( $time ) === 5 ) {
		return $time . ':00';
	}
	return $time;
}

/**
 * DateTime начала/конца из полей AJAX-формы группы.
 *
 * @return array{0: DateTime, 1: DateTime}|null
 */
function edu_em_build_datetimes_from_ajax_schedule( string $start_date, string $end_date, string $start_time, string $end_time ): ?array {
	if ( $start_date === '' ) {
		return null;
	}

	$start_time = edu_em_normalize_ajax_time_hms( $start_time );
	$end_time   = edu_em_normalize_ajax_time_hms( $end_time );
	$end_date   = $end_date !== '' ? $end_date : $start_date;

	try {
		$start_dt = new DateTime( $start_date . ' ' . ( $start_time !== '' ? $start_time : '00:00:00' ) );
		$end_dt   = new DateTime( $end_date . ' ' . ( $end_time !== '' ? $end_time : '00:00:00' ) );
	} catch ( Exception $e ) {
		return null;
	}

	return array( $start_dt, $end_dt );
}

/**
 * Мета цен/формата/часов события из POST (AJAX форма курса).
 */
function edu_em_save_event_meta_from_ajax_post( int $event_post_id, DateTime $start_datetime, DateTime $end_datetime ): void {
	update_post_meta( $event_post_id, '_event_start_date', $start_datetime->format( 'Y-m-d' ) );
	update_post_meta( $event_post_id, '_event_end_date', $end_datetime->format( 'Y-m-d' ) );
	update_post_meta( $event_post_id, '_edu_event_price_legal', edu_em_ajax_post_text( 'price_legal' ) );
	update_post_meta( $event_post_id, '_edu_event_price_legal_sale', edu_em_ajax_post_text( 'price_legal_sale' ) );
	update_post_meta( $event_post_id, '_edu_event_price_individual', edu_em_ajax_post_text( 'price_individual' ) );
	update_post_meta( $event_post_id, '_edu_event_price_individual_sale', edu_em_ajax_post_text( 'price_individual_sale' ) );
	update_post_meta( $event_post_id, '_edu_event_learning_format', edu_em_ajax_post_text( 'learning_format' ) );
	update_post_meta( $event_post_id, '_edu_event_study_hours', edu_em_ajax_post_int( 'study_hours' ) );
	update_post_meta( $event_post_id, '_edu_event_weekdays', edu_em_ajax_post_text( 'weekdays' ) );
}

/**
 * Категория event-categories из POST (при создании группы с карточки курса).
 */
function edu_em_assign_event_category_from_ajax_post( int $event_post_id ): void {
	$category_name = edu_em_ajax_post_text( 'category_name' );
	$category_slug = edu_em_ajax_post_text( 'category_slug' );

	if ( $category_name === '' || $category_slug === '' || ! taxonomy_exists( 'event-categories' ) ) {
		return;
	}

	$category = get_term_by( 'slug', $category_slug, 'event-categories' );
	if ( ! $category ) {
		$category = get_term_by( 'name', $category_name, 'event-categories' );
	}

	$category_id = null;
	if ( ! $category ) {
		$category_result = wp_insert_term( $category_name, 'event-categories', array( 'slug' => $category_slug ) );
		if ( ! is_wp_error( $category_result ) ) {
			$category_id = (int) $category_result['term_id'];
		} else {
			$category_result = wp_insert_term( $category_name, 'event-categories' );
			if ( ! is_wp_error( $category_result ) ) {
				$category_id = (int) $category_result['term_id'];
			}
		}
	} else {
		$category_id = (int) $category->term_id;
	}

	if ( $category_id ) {
		wp_set_object_terms( $event_post_id, array( $category_id ), 'event-categories' );
	}
}

/**
 * Добавить EM event_id в _course_event_ids курса.
 */
function edu_em_append_em_event_to_course( int $course_id, int $em_event_id ): void {
	if ( $course_id <= 0 || $em_event_id <= 0 ) {
		return;
	}

	$event_ids = get_post_meta( $course_id, '_course_event_ids', true );
	if ( ! is_array( $event_ids ) ) {
		$event_ids = array();
	}
	if ( ! in_array( $em_event_id, $event_ids, true ) ) {
		$event_ids[] = $em_event_id;
		update_post_meta( $course_id, '_course_event_ids', $event_ids );
	}
}

/**
 * Время EM для поля формы (HH:mm).
 */
function edu_em_format_time_hm_for_form( string $time ): string {
	return strlen( $time ) > 5 ? substr( $time, 0, 5 ) : $time;
}

new Edu_Center_Event_Settings_Meta_Box();

/**
 * AJAX: создание группы (события EM) с карточки курса.
 */
function edu_create_event(): void {
	check_ajax_referer( 'edu_create_event_nonce', 'nonce' );

	$course_id   = edu_em_ajax_post_int( 'course_id' );
	$event_title = edu_em_ajax_post_text( 'title' );
	$start_date  = edu_em_ajax_post_text( 'start_date' );
	$end_date    = edu_em_ajax_post_text( 'end_date' );
	$start_time  = edu_em_ajax_post_text( 'start_time' );
	$end_time    = edu_em_ajax_post_text( 'end_time' );

	if ( $course_id <= 0 || ! current_user_can( 'edit_post', $course_id ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	if ( ! class_exists( 'EM_Event' ) ) {
		wp_send_json_error( array( 'message' => 'Failed to create event' ) );
	}

	$datetimes = edu_em_build_datetimes_from_ajax_schedule( $start_date, $end_date, $start_time, $end_time );
	if ( $datetimes === null ) {
		wp_send_json_error( array( 'message' => 'Failed to create event' ) );
	}

	[ $start_datetime, $end_datetime ] = $datetimes;

	$EM_Event                     = new EM_Event();
	$EM_Event->event_name         = $event_title;
	$EM_Event->event_start_date   = $start_datetime->format( 'Y-m-d' );
	$EM_Event->event_start_time   = $start_datetime->format( 'H:i:s' );
	$EM_Event->event_end_date     = $end_datetime->format( 'Y-m-d' );
	$EM_Event->event_end_time     = $end_datetime->format( 'H:i:s' );
	$EM_Event->event_all_day      = 0;
	$EM_Event->event_rsvp         = 0;
	$EM_Event->event_status       = 1;
	$EM_Event->event_type         = 'single';
	$EM_Event->event_archetype    = defined( 'EM_POST_TYPE_EVENT' ) ? EM_POST_TYPE_EVENT : 'event';
	$EM_Event->post_status        = 'publish';

	if ( ! $EM_Event->save() ) {
		wp_send_json_error( array( 'message' => 'Failed to create event' ) );
	}

	$event_post_id = (int) $EM_Event->post_id;

	edu_em_save_event_meta_from_ajax_post( $event_post_id, $start_datetime, $end_datetime );
	edu_em_assign_event_category_from_ajax_post( $event_post_id );
	edu_em_append_em_event_to_course( $course_id, (int) $EM_Event->event_id );
	update_post_meta( $event_post_id, '_related_course_id', $course_id );
	edu_em_set_hot_tag_on_event_post( $event_post_id, edu_em_request_wants_hot_tag() );

	wp_send_json_success(
		array(
			'event_id' => $EM_Event->event_id,
			'message'  => __( 'Группу успешно создана', 'edu-center' ),
		)
	);
}
add_action( 'wp_ajax_edu_create_event', 'edu_create_event' );

/**
 * AJAX: обновление группы (события EM).
 */
function edu_update_event(): void {
	check_ajax_referer( 'edu_update_event_nonce', 'nonce' );

	$event_em_id = edu_em_ajax_post_int( 'event_id' );
	$event_title = edu_em_ajax_post_text( 'title' );
	$start_date  = edu_em_ajax_post_text( 'start_date' );
	$end_date    = edu_em_ajax_post_text( 'end_date' );
	$start_time  = edu_em_ajax_post_text( 'start_time' );
	$end_time    = edu_em_ajax_post_text( 'end_time' );

	if ( $event_em_id <= 0 || ! class_exists( 'EM_Event' ) ) {
		wp_send_json_error( array( 'message' => 'Failed to update event' ) );
	}

	$EM_Event = new EM_Event( $event_em_id );
	if ( empty( $EM_Event->event_id ) || empty( $EM_Event->post_id ) ) {
		wp_send_json_error( array( 'message' => 'Failed to update event' ) );
	}

	$event_post_id = (int) $EM_Event->post_id;
	if ( ! current_user_can( 'edit_post', $event_post_id ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	$datetimes = edu_em_build_datetimes_from_ajax_schedule( $start_date, $end_date, $start_time, $end_time );
	if ( $datetimes === null ) {
		wp_send_json_error( array( 'message' => 'Failed to update event' ) );
	}

	[ $start_datetime, $end_datetime ] = $datetimes;

	$EM_Event->event_name       = $event_title;
	$EM_Event->event_start_date = $start_datetime->format( 'Y-m-d' );
	$EM_Event->event_start_time = $start_datetime->format( 'H:i:s' );
	$EM_Event->event_end_date   = $end_datetime->format( 'Y-m-d' );
	$EM_Event->event_end_time   = $end_datetime->format( 'H:i:s' );
	$EM_Event->event_type       = 'single';
	$EM_Event->event_archetype  = defined( 'EM_POST_TYPE_EVENT' ) ? EM_POST_TYPE_EVENT : 'event';
	$EM_Event->post_status      = 'publish';

	if ( ! $EM_Event->save() ) {
		wp_send_json_error( array( 'message' => 'Failed to update event' ) );
	}

	edu_em_save_event_meta_from_ajax_post( $event_post_id, $start_datetime, $end_datetime );

	$course_id = edu_em_ajax_post_int( 'course_id' );
	if ( $course_id > 0 ) {
		update_post_meta( $event_post_id, '_related_course_id', $course_id );
	}

	edu_em_set_hot_tag_on_event_post( $event_post_id, edu_em_request_wants_hot_tag() );

	wp_send_json_success(
		array(
			'message' => __( 'Группа успешно обновлена', 'edu-center' ),
		)
	);
}
add_action( 'wp_ajax_edu_update_event', 'edu_update_event' );

/**
 * AJAX: данные группы для формы редактирования на карточке курса.
 */
function edu_get_event_data(): void {
	check_ajax_referer( 'edu_get_course_events', 'nonce' );

	$event_em_id = edu_em_ajax_post_int( 'event_id' );
	if ( $event_em_id <= 0 ) {
		wp_send_json_error( array( 'message' => 'Event ID is required' ) );
	}

	if ( ! class_exists( 'EM_Event' ) ) {
		wp_send_json_error( array( 'message' => 'Event not found' ) );
	}

	$EM_Event = new EM_Event( $event_em_id );
	if ( empty( $EM_Event->event_id ) || empty( $EM_Event->post_id ) ) {
		wp_send_json_error( array( 'message' => 'Event not found' ) );
	}

	$event_post_id = (int) $EM_Event->post_id;
	if ( ! current_user_can( 'edit_post', $event_post_id ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	wp_send_json_success(
		array(
			'title'                 => $EM_Event->event_name,
			'start_date'            => $EM_Event->event_start_date,
			'end_date'              => $EM_Event->event_end_date,
			'start_time'            => edu_em_format_time_hm_for_form( (string) $EM_Event->event_start_time ),
			'end_time'              => edu_em_format_time_hm_for_form( (string) $EM_Event->event_end_time ),
			'price_legal'           => get_post_meta( $event_post_id, '_edu_event_price_legal', true ),
			'price_legal_sale'      => get_post_meta( $event_post_id, '_edu_event_price_legal_sale', true ),
			'price_individual'      => get_post_meta( $event_post_id, '_edu_event_price_individual', true ),
			'price_individual_sale' => get_post_meta( $event_post_id, '_edu_event_price_individual_sale', true ),
			'learning_format'       => get_post_meta( $event_post_id, '_edu_event_learning_format', true ),
			'study_hours'           => get_post_meta( $event_post_id, '_edu_event_study_hours', true ),
			'weekdays'              => get_post_meta( $event_post_id, '_edu_event_weekdays', true ),
			'hot'                   => edu_em_event_post_has_hot_tag( $event_post_id ),
		)
	);
}
add_action( 'wp_ajax_edu_get_event_data', 'edu_get_event_data' );

/**
 * Обновляем мета-поля дат при сохранении события через стандартный интерфейс WordPress
 * Это обеспечивает синхронизацию мета-полей даже если событие сохраняется не через AJAX
 */
function edu_update_event_date_meta( int $post_id ): void {
	// Проверяем, что это событие
	if ( get_post_type( $post_id ) !== 'event' ) {
		return;
	}
	
	// Проверяем, что Events Manager активен
	if ( ! class_exists( 'EM_Event' ) ) {
		return;
	}
	
	// Получаем объект события
	$EM_Event = new EM_Event( (int) $post_id, 'post_id' );
	
	if ( $EM_Event->event_id && $EM_Event->event_start_date && $EM_Event->event_end_date ) {
		// Обновляем мета-поля для сортировки
		update_post_meta( $post_id, '_event_start_date', $EM_Event->event_start_date );
		update_post_meta( $post_id, '_event_end_date', $EM_Event->event_end_date );
	}
}
add_action( 'save_post_event', 'edu_update_event_date_meta', 20 );

/**
 * Синхронизирует связь "курс -> события" при сохранении события.
 * Нужна для кейса дублирования события в админке Events.
 */
function edu_sync_course_event_link_on_event_save( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! $post || $post->post_type !== 'event' ) {
		return;
	}

	if ( ! class_exists( 'EM_Event' ) ) {
		return;
	}

	$course_id = (int) get_post_meta( $post_id, '_related_course_id', true );
	if ( $course_id <= 0 || get_post_type( $course_id ) !== 'lp_course' ) {
		return;
	}

	$EM_Event = new EM_Event( (int) $post_id, 'post_id' );
	if ( empty( $EM_Event->event_id ) ) {
		return;
	}

	$event_id  = (int) $EM_Event->event_id;
	$event_ids = get_post_meta( $course_id, '_course_event_ids', true );
	if ( ! is_array( $event_ids ) ) {
		$event_ids = array();
	}

	if ( ! in_array( $event_id, $event_ids, true ) ) {
		$event_ids[] = $event_id;
		update_post_meta( $course_id, '_course_event_ids', $event_ids );
	}
}
add_action( 'save_post_event', 'edu_sync_course_event_link_on_event_save', 25, 3 );

