<?php
/**
 * Вспомогательные функции для front-page.php.
 *
 * @package edu-center
 */

/**
 * URL страницы «Все отзывы» из Customizer (edu_center_testimonials_page_id).
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
	$page_id    = (int) get_theme_mod( 'edu_center_testimonials_page_id', 0 );

	if ( $page_id > 0 && get_post_status( $page_id ) === 'publish' ) {
		$permalink = get_permalink( $page_id );
		if ( is_string( $permalink ) ) {
			$url = $permalink;
		}
	}

	return $url;
}
