<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package edu-center
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
  	<header class="header">
		<div class="container">
			<div class="header__inner">

				<!-- Для Главной страницы -->
				<div class="header__logo">
					<?php
					$custom_logo_id = get_theme_mod( 'custom_logo' );
					if ( $custom_logo_id ) {
						$logo_img = wp_get_attachment_image(
							$custom_logo_id,
							array( 223, 60 ),
							false,
							array(
								'class'    => 'header__logo-image logo__image',
								'itemprop' => 'logo',
								'alt'      => get_bloginfo( 'name' ),
							)
						);
						if ( is_front_page() ) {
							echo $logo_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							?>
							<a class="header__logo-link logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'На Главную', 'edu-center' ); ?>" title="<?php esc_attr_e( 'На Главную', 'edu-center' ); ?>">
								<?php echo $logo_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
							<?php
						}
					}
					?>
				</div><!-- /.header__logo -->

				<button class="search-btn btn" type="button" aria-label="<?php esc_attr_e( 'Показать поле поиска', 'edu-center' ); ?>" data-bs-toggle="modal"
					data-bs-target="#searchModal">
					<svg class="search-btn__icon" width="44" height="31" viewBox="0 0 44 31" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<rect width="44" height="30.3468" rx="5" fill="#000482" />
						<ellipse cx="22.0542" cy="14.0541" rx="6.05421" ry="6.05413" stroke="white" stroke-width="1.5"
							stroke-linecap="round" stroke-linejoin="round" />
						<path d="M26.2158 18.5629L29.9997 22.3468" stroke="white" stroke-width="1.5" stroke-linecap="round"
							stroke-linejoin="round" />
					</svg>
				</button><!-- /.search-btn -->

				<!-- Форма поиска для десктопов -->
				<form class="header__search-form search-form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div class="search-form__wrap">
						<input type="search" class="search-form__form-control form-control" placeholder="<?php esc_attr_e( 'Поиск', 'edu-center' ); ?>" aria-label="<?php esc_attr_e( 'Поиск', 'edu-center' ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
						<button class="search-form__btn btn" type="submit">
							<svg class="search__icon" width="22" height="23" viewBox="0 0 22 23" fill="none"
								xmlns="http://www.w3.org/2000/svg">
								<circle cx="9.5" cy="11.021" r="8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
									stroke-linejoin="round" />
								<path d="M15 16.9789L20 21.979" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</button>
					</div><!-- /.search-form__wrap -->
				</form><!-- /.search-form -->

				<!-- Форма поиска для мобильных по клику на кнопку -->
				<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-fullscreen modal__search">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'edu-center' ); ?>"></button>
							</div>

							<div class="modal-body">
								<form class="search-form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
									<div class="search-form__wrap">
										<input type="search" class="search-form__form-control form-control" placeholder="<?php esc_attr_e( 'Поиск', 'edu-center' ); ?>"
											aria-label="<?php esc_attr_e( 'Поиск', 'edu-center' ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
										<button class="search-form__btn btn" type="submit">
											<svg class="search__icon" width="22" height="23" viewBox="0 0 22 23" fill="none"
												xmlns="http://www.w3.org/2000/svg">
												<circle cx="9.5" cy="11.021" r="8" stroke="currentColor" stroke-width="1.5"
													stroke-linecap="round" stroke-linejoin="round" />
												<path d="M15 16.9789L20 21.979" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
													stroke-linejoin="round" />
											</svg>
										</button>
									</div><!-- /.search-form__wrap -->
								</form><!-- /.search-form -->
							</div><!-- /.modal-body -->
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->

				<nav class="header__menu nav-menu">

					<div class="close-nav-menu">
						<button type="button" class="btn-close btn-close--white" aria-label="<?php esc_attr_e( 'Закрыть меню', 'edu-center' ); ?>"></button>
					</div>

					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
							'menu_class'     => 'header__menu-list',
							'container'      => false,
							'fallback_cb'    => false,
							'depth'          => 2,
						)
					);
					?>

					<div class="nav-menu__secondary">
						<div class="nav-menu-secondary__list">
						<?php
						$header_phone = (string) get_theme_mod( 'footer_phone', '' );
						$header_email = (string) get_theme_mod( 'footer_email', '' );
						$phone_tel      = edu_center_normalize_tel_uri( $header_phone );

						if ( $header_phone !== '' && $phone_tel !== '' ) {
							?>
							<a class="phone-link-mobile" href="tel:<?php echo esc_attr( $phone_tel ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Телефон для связи: %s', 'edu-center' ), $header_phone ) ); ?>"><?php echo esc_html( $header_phone ); ?></a>
						<?php } ?>
						<?php if ( $header_email !== '' ) { ?>
							<a class="email-link-mobile" href="mailto:<?php echo esc_attr( $header_email ); ?>"><?php echo esc_html( $header_email ); ?></a>
						<?php } ?>
						</div>
					</div><!-- /.nav-menu__secondary -->
				</nav><!-- /.nav-menu -->

				<div class="header__wrap">
				<?php
				if ( $header_phone !== '' && $phone_tel !== '' ) {
					?>
					<a class="header__phone-link" href="tel:<?php echo esc_attr( $phone_tel ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Телефон для связи: %s', 'edu-center' ), $header_phone ) ); ?>"><?php echo esc_html( $header_phone ); ?></a>
				<?php }
				if ( $header_email !== '' ) {
					?>
					<a class="header__email-link" href="mailto:<?php echo esc_attr( $header_email ); ?>"><?php echo esc_html( $header_email ); ?></a>
				<?php } ?>
				</div><!-- /.header__wrap -->

				<div class="open-nav-menu">
					<svg class="open-nav-menu__icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path d="M3 6H21M3 12H21M3 18H21" stroke="#2F2D47" stroke-width="2.5" stroke-linecap="round"
							stroke-linejoin="round" />
					</svg>
				</div><!-- /.open-nav-menu -->
				<div class="menu-overlay"></div>
			</div>
		</div>

  </header>

  <main class="main">
	<?php // Если это не front-page и не страница курса — выводим хлебные крошки.
	if ( ! is_front_page() && ! is_singular( 'lp_course' ) ) {
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			?>
		<div class="breadcrumbs__container container">
        	<div class="breadcrumbs">
				<?php yoast_breadcrumb( '<p id="breadcrumbs">','</p>' ); ?>
			</div><!-- /.breadcrumbs -->
		</div><!-- /.breadcrumbs__container container -->
		<?php
		}
	}