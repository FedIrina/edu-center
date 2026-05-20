<?php
/**
 * Contact Form 7: тег [calendar] и даты записи на курс (модалка, AJAX).
 *
 * @package edu-center
 */

defined( 'ABSPATH' ) || exit;

/** Флаг: в форме на странице использовано поле [calendar]. */
$GLOBALS['edu_cf7_calendar_used'] = false;

/**
 * Регистрация тега формы CF7 [calendar] и [calendar*].
 */
function edu_cf7_register_calendar_tag(): void {
	if ( ! function_exists( 'wpcf7_add_form_tag' ) ) {
		return;
	}
	wpcf7_add_form_tag(
		array( 'calendar', 'calendar*' ),
		'edu_cf7_calendar_tag_handler',
		array( 'name-attr' => true )
	);
}
add_action( 'wpcf7_init', 'edu_cf7_register_calendar_tag' );

/**
 * Добавляет тег "calendar" в список типов полей в редакторе CF7.
 *
 * @param array $types Типы тегов.
 * @return array
 */
function edu_cf7_calendar_form_tag_types( array $types ): array {
	$types['calendar'] = __( 'Календарь (дата)', 'edu-center' );
	return $types;
}
add_filter( 'wpcf7_form_tag_types', 'edu_cf7_calendar_form_tag_types' );

/**
 * Обработчик тега [calendar]: выводит поле ввода с классом для datepicker.
 *
 * @param WPCF7_FormTag $tag Объект тега.
 * @return string HTML поля.
 */
function edu_cf7_calendar_tag_handler( $tag ): string {
	if ( ! $tag->name ) {
		return '';
	}

	$GLOBALS['edu_cf7_calendar_used'] = true;
	edu_cf7_calendar_maybe_add_footer_script();

	$class = 'wpcf7-form-control wpcf7-calendar edu-cf7-datepicker';
	if ( $tag->is_required() ) {
		$class .= ' wpcf7-validates-as-required';
	}
	$value = (string) reset( $tag->values );

	/* Как у поля [text] CF7: флаг placeholder + текст в кавычках идёт в values, не в get_option( 'placeholder' ). */
	$placeholder = '';
	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$placeholder = $value;
		$value       = '';
	} else {
		$opt_ph = $tag->get_option( 'placeholder', '', true );
		if ( is_string( $opt_ph ) && '' !== $opt_ph ) {
			$placeholder = $opt_ph;
		}
	}
	if ( '' === $placeholder ) {
		$placeholder = __( 'Дата начала', 'edu-center' );
	}
	$atts  = array(
		'type'         => 'text',
		'name'         => $tag->name,
		'value'        => $value,
		'class'        => $class,
		'readonly'     => 'readonly',
		'autocomplete' => 'off',
		'placeholder'  => $placeholder,
	);
	$html = sprintf( '<input %s />', edu_cf7_format_atts( $atts ) );
	return $html;
}

/**
 * Форматирует атрибуты в строку (упрощённо, без экранирования значений).
 *
 * @param array $atts Атрибуты.
 * @return string
 */
function edu_cf7_format_atts( array $atts ): string {
	$pairs = array();
	foreach ( $atts as $k => $v ) {
		$pairs[] = sprintf( '%s="%s"', sanitize_key( $k ), esc_attr( $v ) );
	}
	return implode( ' ', $pairs );
}

/**
 * Один раз вешает хук на wp_footer для вывода скрипта инициализации datepicker.
 */
function edu_cf7_calendar_maybe_add_footer_script(): void {
	static $added = false;
	if ( $added ) {
		return;
	}
	$added = true;
	add_action( 'wp_footer', 'edu_cf7_calendar_footer_script', 20 );
}

/**
 * Подключает datepicker при загрузке скриптов CF7 (страницы с формой).
 * Тема Base — как в админке; стили календаря для фронта — css/cf7-datepicker.css.
 */
function edu_cf7_calendar_enqueue_scripts(): void {
	if ( defined( 'EDU_MODAL_DATEPICKER_ENGINE' ) && EDU_MODAL_DATEPICKER_ENGINE === 'flatpickr' ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-datepicker', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1' );
	wp_enqueue_style( 'edu-cf7-datepicker', get_stylesheet_directory_uri() . '/css/cf7-datepicker.css', array( 'jquery-ui-datepicker' ), _S_VERSION );
}
add_action( 'wpcf7_enqueue_scripts', 'edu_cf7_calendar_enqueue_scripts' );

/**
 * Даты начала опубликованных событий курса (Y-m-d), только сегодня и будущие — как на single-course.
 *
 * @param int $course_id ID поста lp_course.
 * @return string[] Уникальные даты по возрастанию.
 */
function edu_normalize_event_date_ymd( mixed $raw ): string {
	if ( $raw === null || $raw === '' ) {
		return '';
	}
	$raw = (string) $raw;
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $raw, $m ) ) {
		return substr( $raw, 0, 10 );
	}
	$t = strtotime( $raw );
	return $t ? gmdate( 'Y-m-d', (int) $t ) : '';
}

function edu_get_course_allowed_start_dates( int $course_id ): array {
	if ( $course_id <= 0 || get_post_type( $course_id ) !== 'lp_course' ) {
		return array();
	}

	$event_ids  = get_post_meta( $course_id, '_course_event_ids', true );
	$all_events = array();
	if ( ! empty( $event_ids ) && is_array( $event_ids ) && class_exists( 'EM_Event' ) ) {
		foreach ( $event_ids as $event_id ) {
			$EM_Event = new EM_Event( $event_id );
			if ( $EM_Event->event_id && (int) $EM_Event->event_status === 1 && $EM_Event->post_status === 'publish' ) {
				$all_events[] = $EM_Event;
			}
		}
	}

	/* Если _course_event_ids на курсе пуст — события по мете _related_course_id. */
	if ( empty( $all_events ) && function_exists( 'em_get_event' ) && defined( 'EM_POST_TYPE_EVENT' ) ) {
		$related_post_ids = get_posts(
			array(
				'post_type'              => EM_POST_TYPE_EVENT,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'meta_query'             => array(
					array(
						'key'   => '_related_course_id',
						'value' => (string) $course_id,
					),
				),
			)
		);
		foreach ( $related_post_ids as $post_id ) {
			$EM_Event = em_get_event( $post_id, 'post_id' );
			if ( $EM_Event && $EM_Event->event_id && (int) $EM_Event->event_status === 1 && $EM_Event->post_status === 'publish' ) {
				$all_events[] = $EM_Event;
			}
		}
	}

	if ( empty( $all_events ) ) {
		return array();
	}

	usort(
		$all_events,
		function ( $a, $b ) {
			return strtotime( $a->event_start_date ) - strtotime( $b->event_start_date );
		}
	);

	$today = current_time( 'Y-m-d' );
	$dates = array_values(
		array_unique(
			array_map(
				function ( $e ) {
					return edu_normalize_event_date_ymd( $e->event_start_date );
				},
				$all_events
			)
		)
	);
	$dates = array_values( array_filter( $dates ) );
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
 * AJAX: даты старта событий для курса (для datepicker в модалке записи).
 */
function edu_ajax_course_allowed_dates(): void {
	check_ajax_referer( 'edu_course_allowed_dates', 'nonce' );
	$course_id = isset( $_POST['course_id'] ) ? absint( wp_unslash( $_POST['course_id'] ) ) : 0;
	$dates     = edu_get_course_allowed_start_dates( $course_id );
	wp_send_json_success( array( 'dates' => $dates ) );
}
add_action( 'wp_ajax_edu_course_allowed_dates', 'edu_ajax_course_allowed_dates' );
add_action( 'wp_ajax_nopriv_edu_course_allowed_dates', 'edu_ajax_course_allowed_dates' );

/**
 * Параметры AJAX для js/course-enroll-modal.js.
 */
function edu_cf7_course_dates_localize_modal_script(): void {
	if ( ! wp_script_is( 'edu-course-enroll-modal', 'enqueued' ) ) {
		return;
	}
	wp_localize_script(
		'edu-course-enroll-modal',
		'eduCourseDatesAjax',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'edu_course_allowed_dates' ),
			'action'  => 'edu_course_allowed_dates',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'edu_cf7_course_dates_localize_modal_script', 25 );

/**
 * Даты для календаря в модалке на странице курса (после разметки футера, до остального wp_footer).
 */
function edu_cf7_print_lp_course_allowed_dates_for_enroll_modal(): void {
	if ( ! is_singular( 'lp_course' ) || ! function_exists( 'edu_get_lp_course_enroll_modal_allowed_dates_from_scope' ) ) {
		return;
	}
	$dates = edu_get_lp_course_enroll_modal_allowed_dates_from_scope( (int) get_queried_object_id() );
	if ( empty( $dates ) ) {
		return;
	}
	echo '<script>window.eduCourseAllowedDates = ' . wp_json_encode( $dates ) . ';</script>' . "\n";
}
add_action( 'wp_footer', 'edu_cf7_print_lp_course_allowed_dates_for_enroll_modal', 5 );

/**
 * Выводит в футере скрипт инициализации datepicker (одна дата, русская локаль).
 */
function edu_cf7_calendar_footer_script(): void {
	if ( empty( $GLOBALS['edu_cf7_calendar_used'] ) ) {
		return;
	}

	if ( defined( 'EDU_MODAL_DATEPICKER_ENGINE' ) && EDU_MODAL_DATEPICKER_ENGINE === 'flatpickr' ) {
		?>
		<script>
		(function () {
			// Мост для flatpickr: course-enroll-modal-flatpickr.js вызывает eduReinitModalDatepicker.
			window.eduReinitModalDatepicker = function (modalAllowedDates) {
				if (typeof window.eduReinitModalDatepickerFlatpickr === 'function') {
					window.eduReinitModalDatepickerFlatpickr(modalAllowedDates);
				}
			};
		})();
		</script>
		<?php
		return;
	}

	$ru_locale = array(
		'closeText'   => __( 'Закрыть', 'edu-center' ),
		'prevText'    => __( 'Пред', 'edu-center' ),
		'nextText'    => __( 'След', 'edu-center' ),
		'currentText' => __( 'Сегодня', 'edu-center' ),
		'monthNames'  => array(
			__( 'Январь', 'edu-center' ),
			__( 'Февраль', 'edu-center' ),
			__( 'Март', 'edu-center' ),
			__( 'Апрель', 'edu-center' ),
			__( 'Май', 'edu-center' ),
			__( 'Июнь', 'edu-center' ),
			__( 'Июль', 'edu-center' ),
			__( 'Август', 'edu-center' ),
			__( 'Сентябрь', 'edu-center' ),
			__( 'Октябрь', 'edu-center' ),
			__( 'Ноябрь', 'edu-center' ),
			__( 'Декабрь', 'edu-center' ),
		),
		'monthNamesShort' => array( 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек' ),
		'dayNames'        => array( 'воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота' ),
		'dayNamesShort'   => array( 'вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт' ),
		'dayNamesMin'     => array( 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ),
		'weekHeader'      => 'Нед',
		'dateFormat'      => 'yy-mm-dd',
		'firstDay'        => 1,
		'isRTL'            => false,
		'showMonthAfterYear' => false,
		'yearSuffix'       => '',
	);

	?>
	<script>
	(function() {
		if ( typeof jQuery === 'undefined' || ! jQuery.fn.datepicker ) return;
		jQuery(function($) {
			var ru = <?php echo wp_json_encode( $ru_locale ); ?>;
			if ( $.datepicker.regional.ru === undefined ) {
				$.datepicker.regional.ru = ru;
			}
			$.datepicker.setDefaults( $.datepicker.regional.ru );
			var allowedDates = ( typeof window.eduCourseAllowedDates !== 'undefined' && Array.isArray( window.eduCourseAllowedDates ) ) ? window.eduCourseAllowedDates : null;
			var opts = {
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true
			};
			/* Bootstrap 5 focus trap в модалке: не даём перехватывать фокус виджету jQuery UI Datepicker (в т.ч. когда #ui-datepicker-div в body). */
			document.addEventListener( 'focusin', function( e ) {
				if ( e.target && e.target.closest && e.target.closest( '.ui-datepicker' ) ) {
					e.stopImmediatePropagation();
				}
			}, true );
			/**
			 * Всплывающий календарь: absolute внутри .form-row.row__form-control, над полем input.wpcf7-calendar
			 * (едет вместе с полем при прокрутке формы).
			 */
			function eduPlaceDatepickerInFormRow( inst ) {
				if ( ! inst || ! inst.input || ! inst.input.length || ! inst.dpDiv || ! inst.dpDiv.length ) {
					return false;
				}
				var $inp = inst.input;
				var $wrap = $inp.closest( '.form-row.row__form-control' );
				if ( ! $wrap.length ) {
					return false;
				}
				$wrap.css( { position: 'relative', overflow: 'visible' } );
				inst.dpDiv.detach().appendTo( $wrap );
				var gap = 4;
				var left = $inp.offset().left - $wrap.offset().left + $wrap.scrollLeft();
				var inpTopRel = $inp.offset().top - $wrap.offset().top + $wrap.scrollTop();
				var dpH = inst.dpDiv.outerHeight() || 220;
				var top = inpTopRel - dpH - gap;
				var dpW = inst.dpDiv.outerWidth() || 280;
				var innerW = $wrap[0].clientWidth || 0;
				if ( innerW > 0 && left + dpW > innerW - 8 ) {
					left = Math.max( 8, innerW - dpW - 8 );
				}
				inst.dpDiv.css( {
					position: 'absolute',
					left: left + 'px',
					top: top + 'px',
					zIndex: 999999
				} );
				return true;
			}
			/*
			 * Внутри Bootstrap .modal родитель с position:fixed → jQuery UI ставит dpDiv в fixed и даёт неверные координаты
			 * при appendTo .modal-body (календарь оказывается в 0,0). Если есть .form-row.row__form-control — позиционируем там;
			 * иначе — absolute относительно .modal-body под полем.
			 */
			function eduFixEnrollModalDpPosition( inst ) {
				if ( ! inst || ! inst.input || ! inst.input.length || ! inst.dpDiv || ! inst.dpDiv.length ) {
					return;
				}
				var $inp = inst.input;
				var $inModal = $inp.closest( '#course-enroll-modal' );
				if ( ! $inModal.length ) {
					return;
				}
				if ( eduPlaceDatepickerInFormRow( inst ) ) {
					return;
				}
				var $body = $inModal.find( '.modal-body' ).first();
				if ( ! $body.length ) {
					return;
				}
				inst.dpDiv.detach().appendTo( $body );
				var newLeft = $inp.offset().left - $body.offset().left + $body.scrollLeft();
				var newTop = $inp.offset().top - $body.offset().top + $body.scrollTop() + $inp.outerHeight() + 4;
				var dpW = inst.dpDiv.outerWidth() || 280;
				var innerW = $body[0].clientWidth || 0;
				if ( innerW > 0 && newLeft + dpW > innerW - 8 ) {
					newLeft = Math.max( 8, innerW - dpW - 8 );
				}
				inst.dpDiv.css( {
					position: 'absolute',
					left: newLeft + 'px',
					top: newTop + 'px',
					zIndex: 999999
				} );
			}
			if ( ! $.datepicker._eduEnrollModalShowPatched ) {
				$.datepicker._eduEnrollModalShowPatched = true;
				var _eduOrigShowDatepicker = $.datepicker._showDatepicker;
				$.datepicker._showDatepicker = function( input ) {
					var el = input.target || input;
					if ( el.nodeName && el.nodeName.toLowerCase() !== 'input' ) {
						el = $( 'input', el.parentNode )[0];
					}
					var inModal = el && $( el ).closest( '#course-enroll-modal' ).length;
					var ret = _eduOrigShowDatepicker.apply( this, arguments );
					if ( inModal && el ) {
						setTimeout( function() {
							var inst = $.datepicker._getInst( el );
							eduFixEnrollModalDpPosition( inst );
						}, 0 );
					}
					return ret;
				};
			}
			/*
			 * Клик по полю = переключение: открыт → закрыть; закрыт и поле в фокусе → открыть (иначе focus не повторяется).
			 * touchstart — для мобильных.
			 */
			function eduToggleCalendarOnInput( e ) {
				if ( e.type === 'mousedown' && e.button !== 0 ) {
					return;
				}
				var el = e.target;
				if ( ! el || el.nodeName.toLowerCase() !== 'input' || ! el.classList.contains( 'wpcf7-calendar' ) ) {
					return;
				}
				var $inp = $( el );
				var visible;
				try {
					visible = $inp.datepicker( 'widget' ).is( ':visible' );
				} catch ( err ) {
					return;
				}
				var inst = $.datepicker._getInst( el );
				if ( ! inst || ! inst.input || inst.input[0] !== el ) {
					return;
				}
				if ( visible ) {
					$inp.datepicker( 'hide' );
					e.preventDefault();
					e.stopImmediatePropagation();
					return;
				}
				if ( document.activeElement === el ) {
					$inp.datepicker( 'show' );
					setTimeout( function() {
						var inst2 = $.datepicker._getInst( el );
						if ( ! inst2 ) {
							return;
						}
						if ( $( el ).closest( '#course-enroll-modal' ).length ) {
							eduFixEnrollModalDpPosition( inst2 );
						} else {
							eduPlaceDatepickerInFormRow( inst2 );
						}
					}, 0 );
					e.preventDefault();
					e.stopImmediatePropagation();
					return;
				}
			}
			document.addEventListener( 'mousedown', eduToggleCalendarOnInput, true );
			document.addEventListener( 'touchstart', eduToggleCalendarOnInput, { capture: true, passive: false } );
			if ( allowedDates && allowedDates.length > 0 ) {
				opts.beforeShowDay = function( date ) {
					var dateStr = $.datepicker.formatDate( 'yy-mm-dd', date );
					var ok = allowedDates.indexOf( dateStr ) !== -1;
					return [ ok, '', ok ? '' : '' ];
				};
			}
			opts.beforeShow = function( input, inst ) {
				setTimeout( function() {
					eduPlaceDatepickerInFormRow( inst );
				}, 0 );
			};
			opts.onChangeMonthYear = function( year, month, inst ) {
				setTimeout( function() {
					eduPlaceDatepickerInFormRow( inst );
				}, 10 );
			};
			/* Поле в модалке записи инициализируем только после открытия (см. eduReinitModalDatepicker), иначе до AJAX доступны все даты. */
			$( '.edu-cf7-datepicker' ).not( '#course-enroll-modal .edu-cf7-datepicker' ).datepicker( opts );
			/**
			 * Переинициализация datepicker только внутри модалки записи (после AJAX по course_id).
			 *
			 * @param {string[]|null|string} modalAllowedDates null — любые даты; '__loading__' — ждём AJAX; [] — нет доступных дат; ['Y-m-d',…] — только эти.
			 */
			window.eduReinitModalDatepicker = function( modalAllowedDates ) {
				var $modal = $( '#course-enroll-modal' );
				var $modalBody = $modal.find( '.modal-body' ).first();
				if ( ! $modalBody.length ) {
					$modalBody = $modal;
				}
				var $inputs = $modal.find( '.edu-cf7-datepicker' );
				if ( ! $inputs.length ) {
					return;
				}
				$inputs.each( function() {
					try {
						$( this ).datepicker( 'destroy' );
					} catch ( err ) {}
				} );
				/* appendTo + патч _showDatepicker (absolute под полем, прокрутка с .modal-body). */
				var modalOpts = {
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true,
					appendTo: $modalBody,
					onChangeMonthYear: function( year, month, inst ) {
						setTimeout( function() {
							eduFixEnrollModalDpPosition( inst );
						}, 10 );
					}
				};
				if ( modalAllowedDates === '__loading__' ) {
					modalOpts.beforeShowDay = function() {
						return [ false, '', '' ];
					};
				} else if ( modalAllowedDates === null || typeof modalAllowedDates === 'undefined' ) {
					/* без ограничения */
				} else if ( Array.isArray( modalAllowedDates ) ) {
					if ( modalAllowedDates.length === 0 ) {
						modalOpts.beforeShowDay = function() {
							return [ false, '', '' ];
						};
					} else {
						modalOpts.beforeShowDay = function( date ) {
							var dateStr = $.datepicker.formatDate( 'yy-mm-dd', date );
							var ok = modalAllowedDates.indexOf( dateStr ) !== -1;
							return [ ok, '', ok ? '' : '' ];
						};
					}
				}
				$inputs.datepicker( modalOpts );
			};
		});
	})();
	</script>
	<?php
}

/**
 * Порядок обхода полей по Tab: после крестика модалки идут radio customer-type, затем остальные поля.
 * Реализовано через tabindex в HTML, без JavaScript.
 *
 * @param string $content HTML формы CF7.
 * @return string
 */
function edu_cf7_enroll_form_tabindex( string $content ): string {
	if ( ! str_contains( $content, 'name="customer-type"' ) ) {
		return $content;
	}

	$n = 1;
	$content = preg_replace_callback(
		'/<input\s[^>]*name="customer-type"[^>]*>/i',
		function ( $m ) use ( &$n ) {
			$tag = $m[0];
			if ( str_contains( $tag, 'tabindex=' ) ) {
				return $tag;
			}
			return rtrim( $tag, '>' ) . ' tabindex="' . $n++ . '">';
		},
		$content
	);

	$n = 100;
	$content = preg_replace_callback(
		'/<(input|select|textarea|button)\s[^>]*>/i',
		function ( $m ) use ( &$n ) {
			$tag = $m[0];
			if ( str_contains( $tag, 'tabindex=' ) ) {
				return $tag;
			}
			return rtrim( $tag, '>' ) . ' tabindex="' . $n++ . '">';
		},
		$content
	);

	return $content;
}
// add_filter( 'wpcf7_form_elements', 'edu_cf7_enroll_form_tabindex' );
