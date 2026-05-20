<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package edu-center
 */

?>
</main>

<footer class="footer">
  <div class="footer__inner">
    <div class="container">
      <div class="footer__container">

        <div class="footer__wrap-column">

          <div class="footer__column footer__column--flex">
            <?php
            // Получаем второй логотип из кастомайзера
            $second_logo_id = get_theme_mod( 'second_logo' );
            
            if ( $second_logo_id ) {
                // Если второй логотип установлен в кастомайзере, используем его
                $second_logo_img = wp_get_attachment_image( $second_logo_id, array( 151, 35 ), false, array(
                    'class'    => 'footer__logo-image logo__image',
                    'itemprop' => 'logo',
                    'alt'      => get_bloginfo( 'name' ) . ' - ' . esc_attr__( 'логотип', 'edu-center' ),
                ) );
                
                if ( is_front_page() || is_home() ) {
                    // На главной странице - в div (без ссылки)
                    echo '<div class="footer__logo">';
                    echo $second_logo_img;
                    echo '</div>';
                } else {
                    // На остальных страницах - в ссылке
                    echo '<a class="footer__logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr__( 'На Главную', 'edu-center' ) . '" title="' . esc_attr__( 'На Главную', 'edu-center' ) . '">';
                    echo $second_logo_img;
                    echo '</a>';
                }
            } else {
                // Fallback: если второй логотип не установлен, используем SVG
                if ( is_front_page() || is_home() ) {
                    // На главной странице - в div (без ссылки)
                    echo '<div class="footer__logo">';
                    echo '<img class="footer__logo-image logo__image" src="' . esc_url( get_stylesheet_directory_uri() . '/img/logo-white.svg' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="151" height="35">';
                    echo '</div>';
                } else {
                    // На остальных страницах - в ссылке
                    echo '<a class="footer__logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr__( 'На Главную', 'edu-center' ) . '" title="' . esc_attr__( 'На Главную', 'edu-center' ) . '">';
                    echo '<img class="footer__logo-image logo__image" src="' . esc_url( get_stylesheet_directory_uri() . '/img/logo-white.svg' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="151" height="35">';
                    echo '</a>';
                }
            }
            ?>
            <div class="footer__logo-name"><?php echo esc_html__( 'АНО ДПО «Учебный центр РРС»', 'edu-center' ); ?></div>
            <?php
            $privacy_policy_page_id = get_theme_mod( 'footer_privacy_policy' );
            if ( $privacy_policy_page_id ) {
              $privacy_policy_url = get_permalink( $privacy_policy_page_id );
              ?>
              <div><a class="footer__menu-link footer__menu-link--privacy-policy" href="<?php echo esc_url( $privacy_policy_url ); ?>"><?php esc_html_e( 'Политика конфиденциальности', 'edu-center' ); ?></a></div>
              <?php
            }
            ?>
            <div class="footer__copyright">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html__( '«Си Ай Скул»', 'edu-center' ); ?></div>
          </div>

          <div class="footer__column">
            <h3 class="footer__title"><?php echo esc_html__( 'О Центре', 'edu-center' ); ?></h3>
            <?php
            wp_nav_menu(
              array(
                'theme_location' => 'footer-menu-about',
                'menu_id'        => 'footer-menu-about',
                'menu_class'     => 'footer__menu-list',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 1,
              )
            );
            ?>
          </div>

        </div><!-- /.footer__wrap-column -->

        <div class="footer__wrap-column">

          <div class="footer__column">
            <h3 class="footer__title"><?php echo esc_html__( 'Обучение', 'edu-center' ); ?></h3>
            <?php
            wp_nav_menu(
              array(
                'theme_location' => 'footer-menu-education',
                'menu_id'        => 'footer-menu-education',
                'menu_class'     => 'footer__menu-list',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 1,
              )
            );
            ?>
          </div>

          <div class="footer__column footer__column--flex">
            <h3 class="footer__title"><?php echo esc_html__( 'Контакты', 'edu-center' ); ?></h3>
            <?php
            $footer_phone = (string) get_theme_mod( 'footer_phone', '' );
            if ( '' !== $footer_phone ) {
                $phone_tel = edu_center_normalize_tel_uri( $footer_phone );
                if ( '' !== $phone_tel ) {
                    ?>
            <div class="footer__phone">
              <a class="footer__phone-link" href="tel:<?php echo esc_attr( $phone_tel ); ?>"><?php echo esc_html( $footer_phone ); ?></a>
            </div>
            <?php
                }
            }
            ?>
            <?php
            $footer_email = (string) get_theme_mod( 'footer_email', '' );
            if ( '' !== $footer_email ) {
                ?>
            <div class="footer__email">
              <a class="footer__email-link" href="mailto:<?php echo esc_attr( $footer_email ); ?>"><?php echo esc_html( $footer_email ); ?></a>
            </div>
            <?php
            }
            ?>
            <?php
            $footer_address = get_theme_mod( 'footer_address' );
            if ( $footer_address ) {
                ?>
            <div class="footer__address">
              <address class="footer__adress-txt"><?php echo wp_kses_post( nl2br( $footer_address ) ); ?></address>
            </div>
            <?php
            }
            ?>
            <?php
            $footer_telegram = get_theme_mod( 'footer_telegram' );
            if ( $footer_telegram ) {
                ?>
            <div class="footer__soc1als soc1als">
              <ul class="soc1als__list">
                <li class="soc1als__item">
                  <a class="soc1als__link" href="<?php echo esc_url( $footer_telegram ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Telegram', 'edu-center' ); ?>" title="<?php echo esc_attr__( 'Telegram', 'edu-center' ); ?>">
                    <svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M19 0.602225L15.9946 16.2923C15.9946 16.2923 15.5741 17.3801 14.4189 16.8584L7.48458 11.3526L7.45242 11.3364C8.38909 10.4654 15.6524 3.70266 15.9698 3.39612C16.4613 2.92136 16.1562 2.63873 15.5856 2.99736L4.85679 10.053L0.717638 8.61077C0.717638 8.61077 0.0662573 8.37083 0.00359284 7.84911C-0.0598962 7.32653 0.739076 7.0439 0.739076 7.0439L17.6131 0.188948C17.6131 0.188948 19 -0.44207 19 0.602225Z"
                        fill="currentColor" />
                    </svg>
                  </a>
                </li>
              </ul>
            </div>
            <?php
            }
            ?>
          </div>
        </div><!-- /.footer__wrap-column -->

      </div>
    </div><!-- /.container -->
  </div>
  <?php get_template_part( 'template-parts/modal', 'course-enroll' ); ?>
</footer>


<?php wp_footer(); ?>

</body>
</html>
