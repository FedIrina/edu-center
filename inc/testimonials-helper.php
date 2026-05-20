<?php
/**
 * Вспомогательные функции для front-page.php.
 *
 * @package edu-center
 */

/**
 * URL опубликованной страницы с шаблоном «Отзывы».
 *
 * @return string Permalink или пустая строка.
 */
function edu_center_get_testimonials_page_url(): string {
	static $did_lookup = false;
	static $url        = '';

	if ( $did_lookup ) {
		return $url;
	}

	$did_lookup = true;
	$page_ids   = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'orderby'                => 'menu_order',
			'order'                  => 'ASC',
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'page-testimonials.php',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! empty( $page_ids ) ) {
		$permalink = get_permalink( $page_ids[0] );
		if ( is_string( $permalink ) ) {
			$url = $permalink;
		}
	}

	return $url;
}
