<?php
/**
 * Зависимости темы от плагинов: проверка и уведомления в админке.
 *
 * Тему не блокируем и плагины не активируем автоматически.
 *
 * @package edu-center
 */

defined( 'ABSPATH' ) || exit;

/**
 * Список плагинов, от которых зависит front-page и интеграции темы.
 *
 * required — без плагина главная или модалка ломаются (fatal / пустые блоки).
 * plugin_files — возможные пути в wp-content/plugins (для ссылки «Плагины»).
 *
 * @return array<string, array{
 *     label: string,
 *     required: bool,
 *     check: callable,
 *     plugin_files?: string[],
 *     description?: string
 * }>
 */
function edu_center_get_plugin_dependencies(): array {
	$deps = array(
		'acf' => array(
			'label'        => 'Advanced Custom Fields',
			'required'     => true,
			'check'        => static function (): bool {
				return function_exists( 'get_field' ) && function_exists( 'have_rows' );
			},
			'plugin_files' => array(
				'advanced-custom-fields-pro/acf.php',
				'advanced-custom-fields/acf.php',
			),
			'description'  => __( 'Слайдер, преимущества, настройки блоков курсов и галереи на главной.', 'edu-center' ),
		),
		'learnpress' => array(
			'label'        => 'LearnPress',
			'required'     => true,
			'check'        => static function (): bool {
				return post_type_exists( 'lp_course' )
					&& ( class_exists( 'LearnPress' ) || defined( 'LEARNPRESS_VERSION' ) );
			},
			'plugin_files' => array( 'learnpress/learnpress.php' ),
			'description'  => __( 'Курсы, карточки и длительность на главной.', 'edu-center' ),
		),
		'events-manager' => array(
			'label'        => 'Events Manager',
			'required'     => true,
			'check'        => static function (): bool {
				return defined( 'EM_POST_TYPE_EVENT' ) && class_exists( 'EM_Event' );
			},
			'plugin_files' => array( 'events-manager/events-manager.php' ),
			'description'  => __( 'Группы, расписание, цены событий и модалка записи.', 'edu-center' ),
		),
		'contact-form-7' => array(
			'label'        => 'Contact Form 7',
			'required'     => false,
			'check'        => static function (): bool {
				return function_exists( 'wpcf7' ) || defined( 'WPCF7_VERSION' );
			},
			'plugin_files' => array( 'contact-form-7/wp-contact-form-7.php' ),
			'description'  => __( 'Форма «Записаться на курс» в модальном окне.', 'edu-center' ),
		),
	);

	/**
	 * @param array<string, array<string, mixed>> $deps
	 */
	return apply_filters( 'edu_center_plugin_dependencies', $deps );
}

/**
 * Проверяет, активен ли один из файлов плагина из списка.
 *
 * @param string[] $plugin_files Относительные пути в wp-content/plugins.
 */
function edu_center_is_any_plugin_file_active( array $plugin_files ): bool {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	foreach ( $plugin_files as $file ) {
		if ( is_plugin_active( $file ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Результат проверки зависимостей.
 *
 * @return array{
 *     ok: bool,
 *     missing_required: array<int, array{ id: string, label: string, description: string, manage_url: string }>,
 *     missing_recommended: array<int, array{ id: string, label: string, description: string, manage_url: string }>
 * }
 */
function edu_center_check_dependencies(): array {
	$missing_required    = array();
	$missing_recommended = array();

	foreach ( edu_center_get_plugin_dependencies() as $id => $dep ) {
		$check = $dep['check'] ?? null;
		if ( ! is_callable( $check ) || (bool) $check() ) {
			continue;
		}

		$plugin_files = $dep['plugin_files'] ?? array();
		$manage_url   = admin_url( 'plugins.php' );
		if ( ! empty( $plugin_files ) && ! edu_center_is_any_plugin_file_active( $plugin_files ) ) {
			$manage_url = admin_url(
				'plugin-install.php?s=' . rawurlencode( $dep['label'] ) . '&tab=search&type=term'
			);
		}

		$item = array(
			'id'          => (string) $id,
			'label'       => (string) $dep['label'],
			'description' => (string) ( $dep['description'] ?? '' ),
			'manage_url'  => $manage_url,
		);

		if ( ! empty( $dep['required'] ) ) {
			$missing_required[] = $item;
		} else {
			$missing_recommended[] = $item;
		}
	}

	return array(
		'ok'                  => empty( $missing_required ),
		'missing_required'    => $missing_required,
		'missing_recommended' => $missing_recommended,
	);
}

/**
 * Все обязательные плагины на месте.
 */
function edu_center_dependencies_ok(): bool {
	return edu_center_check_dependencies()['ok'];
}

/**
 * Уведомления в админке для пользователей с правом activate_plugins.
 */
function edu_center_admin_dependency_notices(): void {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$result = edu_center_check_dependencies();
	if ( $result['ok'] && empty( $result['missing_recommended'] ) ) {
		return;
	}

	if ( ! empty( $result['missing_required'] ) ) {
		echo '<div class="notice notice-error"><p><strong>';
		esc_html_e( 'Тема edu-center: не установлены или не активны обязательные плагины:', 'edu-center' );
		echo '</strong></p><ul style="list-style:disc;margin-left:1.5em;">';
		foreach ( $result['missing_required'] as $item ) {
			echo '<li>';
			echo esc_html( $item['label'] );
			if ( $item['description'] !== '' ) {
				echo ' — <span class="description">' . esc_html( $item['description'] ) . '</span>';
			}
			echo ' <a href="' . esc_url( $item['manage_url'] ) . '">';
			esc_html_e( 'Установить или включить', 'edu-center' );
			echo '</a></li>';
		}
		echo '</ul></div>';
	}

	if ( ! empty( $result['missing_recommended'] ) ) {
		echo '<div class="notice notice-warning"><p><strong>';
		esc_html_e( 'Тема edu-center: рекомендуемые плагины не активны:', 'edu-center' );
		echo '</strong></p><ul style="list-style:disc;margin-left:1.5em;">';
		foreach ( $result['missing_recommended'] as $item ) {
			echo '<li>';
			echo esc_html( $item['label'] );
			if ( $item['description'] !== '' ) {
				echo ' — <span class="description">' . esc_html( $item['description'] ) . '</span>';
			}
			echo ' <a href="' . esc_url( $item['manage_url'] ) . '">';
			esc_html_e( 'Установить или включить', 'edu-center' );
			echo '</a></li>';
		}
		echo '</ul></div>';
	}
}
add_action( 'admin_notices', 'edu_center_admin_dependency_notices' );
