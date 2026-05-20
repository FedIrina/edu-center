<?php
/**
 * Минимальный fallback (не главная страница).
 * Основная вёрстка темы рассчитана на отображение через front-page.php.
 *
 * @package edu-center
 */

get_header();
?>
	<p class="container" style="padding: 2rem 0;">
		<?php esc_html_e( 'Эта сборка темы предназначена для главной страницы сайта.', 'edu-center' ); ?>
	</p>
<?php
get_footer();
