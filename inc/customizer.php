<?php
/**
 * edu-center Theme Customizer
 *
 * @package edu-center
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function edu_center_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'edu_center_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'edu_center_customize_partial_blogdescription',
			)
		);
	}

	// Add second logo setting in Site Identity section
	$wp_customize->add_setting(
		'second_logo',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'second_logo',
			array(
				'label'       => esc_html__( 'Второй логотип', 'edu-center' ),
				'description' => esc_html__( 'Выберите изображение для второго логотипа', 'edu-center' ),
				'section'     => 'title_tagline',
				'mime_type'   => 'image',
				'priority'    => 9,
			)
		)
	);

	// Add Slogan setting in Site Identity section (after Tagline)
	$wp_customize->add_setting(
		'site_slogan',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'site_slogan',
		array(
			'label'       => esc_html__( 'Слоган', 'edu-center' ),
			'description' => esc_html__( 'Введите слоган сайта', 'edu-center' ),
			'section'     => 'title_tagline',
			'type'        => 'text',
			'priority'    => 35,
		)
	);

	// Add Footer section
	$wp_customize->add_section(
		'footer_section',
		array(
			'title'    => esc_html__( 'Футер', 'edu-center' ),
			'priority' => 30,
		)
	);

	// Footer Phone setting
	$wp_customize->add_setting(
		'footer_phone',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'footer_phone',
		array(
			'label'       => esc_html__( 'Телефон', 'edu-center' ),
			'description' => esc_html__( 'Введите номер телефона в формате +7(XXX)XXX-XX-XX', 'edu-center' ),
			'section'     => 'footer_section',
			'type'        => 'text',
			'input_attrs' => array(
				'placeholder' => '+7(XXX)XXX-XX-XX',
				'class'       => 'footer-phone-input',
			),
		)
	);

	// Footer Email setting
	$wp_customize->add_setting(
		'footer_email',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'footer_email',
		array(
			'label'       => esc_html__( 'Email', 'edu-center' ),
			'description' => esc_html__( 'Введите email адрес', 'edu-center' ),
			'section'     => 'footer_section',
			'type'        => 'email',
		)
	);

	// Footer Address setting
	$wp_customize->add_setting(
		'footer_address',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'footer_address',
		array(
			'label'       => esc_html__( 'Адрес', 'edu-center' ),
			'description' => esc_html__( 'Введите адрес', 'edu-center' ),
			'section'     => 'footer_section',
			'type'        => 'textarea',
		)
	);

	// Footer Telegram setting
	$wp_customize->add_setting(
		'footer_telegram',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'footer_telegram',
		array(
			'label'       => esc_html__( 'Ссылка на Telegram', 'edu-center' ),
			'description' => esc_html__( 'Введите ссылку на группу или канал в Telegram', 'edu-center' ),
			'section'     => 'footer_section',
			'type'        => 'url',
			'input_attrs' => array(
				'placeholder' => 'https://t.me/your_group',
			),
		)
	);

	// Footer Privacy Policy setting
	$wp_customize->add_setting(
		'footer_privacy_policy',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'footer_privacy_policy',
		array(
			'label'       => esc_html__( 'Страница политики конфиденциальности', 'edu-center' ),
			'description' => esc_html__( 'Выберите страницу с политикой конфиденциальности', 'edu-center' ),
			'section'     => 'footer_section',
			'type'        => 'dropdown-pages',
		)
	);

	$wp_customize->add_section(
		'edu_center_front_page_section',
		array(
			'title'    => esc_html__( 'Главная страница', 'edu-center' ),
			'priority' => 28,
		)
	);

	$wp_customize->add_setting(
		'edu_center_news_category_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'edu_center_news_category_id',
		array(
			'label'       => esc_html__( 'Рубрика «Новости»', 'edu-center' ),
			'description' => esc_html__( 'Записи этой рубрики выводятся в блоке новостей на главной.', 'edu-center' ),
			'section'     => 'edu_center_front_page_section',
			'type'        => 'dropdown-categories',
		)
	);

	$wp_customize->add_setting(
		'edu_center_testimonials_page_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'edu_center_testimonials_page_id',
		array(
			'label'       => esc_html__( 'Страница «Все отзывы»', 'edu-center' ),
			'description' => esc_html__( 'Ссылка «все Отзывы» в блоке отзывов на главной.', 'edu-center' ),
			'section'     => 'edu_center_front_page_section',
			'type'        => 'dropdown-pages',
		)
	);

	$wp_customize->add_section(
		'edu_center_enroll_modal_section',
		array(
			'title'       => esc_html__( 'Модалка «Записаться на курс»', 'edu-center' ),
			'description' => esc_html__( 'Форма Contact Form 7 в модальном окне записи на курс.', 'edu-center' ),
			'priority'    => 32,
		)
	);

	$wp_customize->add_setting(
		'edu_center_cf7_enroll_form_id',
		array(
			'default'           => '',
			'sanitize_callback' => 'edu_center_sanitize_cf7_enroll_form_choice',
			'transport'         => 'refresh',
		)
	);

	$cf7_form_choices = edu_center_get_cf7_form_choices();

	$wp_customize->add_control(
		'edu_center_cf7_enroll_form_id',
		array(
			'label'       => esc_html__( 'Форма CF7', 'edu-center' ),
			'description' => count( $cf7_form_choices ) > 1
				? esc_html__( 'Опубликованные формы Contact Form 7 (id как в шорткоде плагина).', 'edu-center' )
				: esc_html__( 'Нет опубликованных форм Contact Form 7 — список пуст.', 'edu-center' ),
			'section'     => 'edu_center_enroll_modal_section',
			'type'        => 'select',
			'choices'     => $cf7_form_choices,
		)
	);

	$wp_customize->add_setting(
		'edu_center_cf7_enroll_form_title',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'edu_center_cf7_enroll_form_title',
		array(
			'label'       => esc_html__( 'Title в шорткоде CF7', 'edu-center' ),
			'description' => esc_html__( 'Необязательно: атрибут title в [contact-form-7]. Если пусто — «Записаться на курс».', 'edu-center' ),
			'section'     => 'edu_center_enroll_modal_section',
			'type'        => 'text',
		)
	);

	// Section: Teachers archive page (content for "Дополнительная информация" block)
	$wp_customize->add_section(
		'teachers_archive_section',
		array(
			'title'    => esc_html__( 'Страница «Преподаватели»', 'edu-center' ),
			'priority' => 35,
		)
	);

	$wp_customize->add_setting(
		'teachers_archive_info_page_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'teachers_archive_info_page_id',
		array(
			'label'       => esc_html__( 'Страница для блока «Дополнительная информация»', 'edu-center' ),
			'description' => esc_html__( 'Выберите страницу: её контент будет выведен под списком преподавателей в блоке «Дополнительная информация».', 'edu-center' ),
			'section'     => 'teachers_archive_section',
			'type'        => 'dropdown-pages',
		)
	);
}
add_action( 'customize_register', 'edu_center_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function edu_center_customize_partial_blogname(): void {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function edu_center_customize_partial_blogdescription(): void {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function edu_center_customize_preview_js(): void {
	wp_enqueue_script( 'edu-center-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'edu_center_customize_preview_js' );

/**
 * Enqueue scripts for Customizer controls (phone mask)
 */
function edu_center_customize_controls_js(): void {
	wp_enqueue_script(
		'edu-center-customizer-controls',
		get_template_directory_uri() . '/js/customizer-controls.js',
		array( 'jquery', 'customize-controls' ),
		_S_VERSION,
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'edu_center_customize_controls_js' );
