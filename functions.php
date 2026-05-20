<?php
/**
 * Тема edu-center: только главная страница (front-page.php).
 *
 * @package edu-center
 */

if ( ! defined( '_S_VERSION' ) ) {
	define( '_S_VERSION', '1.0.0-front-fragment' );
}

if ( ! defined( 'EDU_MODAL_DATEPICKER_ENGINE' ) ) {
	define( 'EDU_MODAL_DATEPICKER_ENGINE', 'flatpickr' );
}

/**
 * Преобразует произвольную строку телефона из кастомайзера в значение для атрибута href="tel:…".
 *
 * @param string $phone Текст номера (маска, пробелы и т.д.).
 * @return string Пустая строка, если цифр нет; иначе +7XXXXXXXXXX по тем же правилам, что в шапке/подвале.
 */
function edu_center_normalize_tel_uri( string $phone ): string {
	$digits = preg_replace( '/\D/', '', $phone );
	if ( ! is_string( $digits ) ) {
		return '';
	}

	if ( $digits === '' ) {
		return '';
	}

	if ( str_starts_with( $digits, '7' ) ) {
		return '+' . $digits;
	}

	return '+7' . $digits;
}

/**
 * Настройки темы для шапки, подвала и главной.
 */
function edu_center_setup(): void {
	load_theme_textdomain( 'edu-center', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'edu-157x157', 157, 157, true );
	add_image_size( 'edu-137x137', 137, 137, true );
	add_image_size( 'edu-302x243', 302, 243, true );

	register_nav_menus(
		array(
			'menu-1'               => esc_html__( 'Primary', 'edu-center' ),
			'footer-menu-about'    => esc_html__( 'Footer - О Центре', 'edu-center' ),
			'footer-menu-education' => esc_html__( 'Footer - Обучение', 'edu-center' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'edu_center_setup' );

/**
 * Ширина контента (совместимо с родительской темой).
 */
function edu_center_content_width(): void {
	$GLOBALS['content_width'] = apply_filters( 'edu_center_content_width', 640 );
}
add_action( 'after_setup_theme', 'edu_center_content_width', 0 );

require get_template_directory() . '/inc/dependencies.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/testimonials-helper.php';
require get_template_directory() . '/inc/events-functions.php';
require get_template_directory() . '/inc/cf7-calendar.php';

/**
 * Стили и скрипты, необходимые для front-page.php (и той же шапки/подвала с модалкой на остальных URL).
 * В каталогах css/js/fonts лежит только этот минимальный набор (без расписания, loadmore, фильтра курсов и т.д.).
 */
function edu_center_scripts(): void {
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_style( 'edu-center-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_enqueue_style( 'swiper-bundle', get_stylesheet_directory_uri() . '/css/swiper-bundle.min.css', array(), _S_VERSION );

	if ( EDU_MODAL_DATEPICKER_ENGINE === 'flatpickr' ) {
		wp_enqueue_style(
			'edu-flatpickr',
			plugins_url( 'events-manager/includes/external/flatpickr/flatpickr.min.css' ),
			array(),
			_S_VERSION
		);
	}

	$main_deps = ( EDU_MODAL_DATEPICKER_ENGINE === 'flatpickr' ) ? array( 'edu-flatpickr' ) : array();

	wp_enqueue_style( 'main', get_stylesheet_directory_uri() . '/css/main.css', $main_deps, _S_VERSION );
	wp_enqueue_style(
		'edu-additional',
		get_stylesheet_directory_uri() . '/css/additional.css',
		array( 'main' ),
		_S_VERSION
	);
	wp_enqueue_style(
		'edu-cf7-enroll-form',
		get_stylesheet_directory_uri() . '/css/cf7-enroll-form.css',
		array( 'main' ),
		_S_VERSION
	);

	wp_enqueue_script( 'bootstrap-bundle', get_stylesheet_directory_uri() . '/js/bootstrap.bundle.min.js', array( 'jquery' ), _S_VERSION, true );

	if ( EDU_MODAL_DATEPICKER_ENGINE === 'flatpickr' ) {
		wp_enqueue_script(
			'edu-flatpickr',
			plugins_url( 'events-manager/includes/external/flatpickr/flatpickr.min.js' ),
			array(),
			_S_VERSION,
			true
		);
		wp_enqueue_script(
			'edu-flatpickr-ru',
			plugins_url( 'events-manager/includes/external/flatpickr/l10n/ru.min.js' ),
			array( 'edu-flatpickr' ),
			_S_VERSION,
			true
		);
		wp_enqueue_script(
			'edu-course-enroll-modal',
			get_stylesheet_directory_uri() . '/js/course-enroll-modal-flatpickr.js',
			array( 'edu-flatpickr-ru', 'bootstrap-bundle' ),
			_S_VERSION,
			true
		);
	}

	wp_enqueue_script( 'swiper-bundle', get_stylesheet_directory_uri() . '/js/swiper-bundle.min.js', array( 'jquery' ), _S_VERSION, true );
	wp_enqueue_script(
		'edu-center-script',
		get_stylesheet_directory_uri() . '/js/script.js',
		array( 'jquery', 'bootstrap-bundle', 'swiper-bundle' ),
		_S_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'edu_center_scripts' );

/**
 * CSS-классы пунктов меню: header__menu-item (верхний уровень шапки), footer__menu-item (футер).
 *
 * @param string[] $classes CSS classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    Menu arguments.
 * @return string[]
 */
function edu_center_nav_menu_css_class( $classes, $item, $args ): array {
	if ( isset( $args->theme_location ) ) {
		if ( $args->theme_location === 'menu-1' ) {
			if ( 0 === (int) $item->menu_item_parent ) {
				$classes[] = 'header__menu-item';
			}
		} elseif ( $args->theme_location === 'footer-menu-about' || $args->theme_location === 'footer-menu-education' ) {
			$classes[] = 'footer__menu-item';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'edu_center_nav_menu_css_class', 10, 3 );

/**
 * Атрибуты ссылок меню: классы, data-toggle, title (шапка и футер).
 *
 * @param array<string, string> $atts   Link attributes.
 * @param WP_Post               $item   Menu item.
 * @param stdClass              $args   Menu arguments.
 * @return array<string, string>
 */
function edu_center_nav_menu_link_attributes( $atts, $item, $args ): array {
	if ( isset( $args->theme_location ) ) {
		if ( $args->theme_location === 'menu-1' ) {
			if ( 0 === (int) $item->menu_item_parent ) {
				$atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' header__menu-link nav-link' : 'header__menu-link nav-link';
				if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
					$atts['data-toggle'] = 'sub-menu';
				}
			}
			if ( ! empty( $item->attr_title ) ) {
				$atts['title'] = $item->attr_title;
			} elseif ( ! empty( $item->title ) && 0 === (int) $item->menu_item_parent ) {
				$atts['title'] = $item->title;
			}
		} elseif ( $args->theme_location === 'footer-menu-about' || $args->theme_location === 'footer-menu-education' ) {
			$atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' footer__menu-link' : 'footer__menu-link';
		}
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'edu_center_nav_menu_link_attributes', 10, 3 );

/**
 * Заголовок пункта меню: иконка «плюс» у пунктов с подменю в шапке.
 *
 * @param string   $title Menu item title.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu arguments.
 * @param int      $depth Nesting depth.
 * @return string
 */
function edu_center_nav_menu_item_title( $title, $item, $args, $depth ): string {
	if ( isset( $args->theme_location ) && 'menu-1' === $args->theme_location && 0 === (int) $depth ) {
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$title .= ' <i class="plus"></i>';
		}
	}
	return $title;
}
add_filter( 'nav_menu_item_title', 'edu_center_nav_menu_item_title', 10, 4 );
