<?php
/**
 * Модальное окно «Записаться на курс» (Contact Form 7).
 * Выводится на странице курса и на странице списка событий (events).
 *
 * @package edu-center
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- Модальное окно "Записаться на курс" -->
<div class="modal fade" id="course-enroll-modal" tabindex="-1" aria-labelledby="course-enroll-modal-label" aria-hidden="true" data-bs-config='<?php echo esc_attr( wp_json_encode( array( 'focus' => false ) ) ); ?>'>
  <div class="modal-dialog modal-dialog--course-enroll-modal modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Закрыть', 'edu-center' ); ?>"></button>
      </div>
      <div class="modal-body">
        <h2 class="modal-title" id="course-enroll-modal-label"><?php esc_html_e( 'Записаться на курс', 'edu-center' ); ?></h2>
        <?php
        $edu_cf7_enroll_shortcode = edu_center_get_cf7_enroll_form_shortcode();
        if ( $edu_cf7_enroll_shortcode !== '' && function_exists( 'wpcf7' ) ) {
            echo do_shortcode( $edu_cf7_enroll_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        ?>
      </div>
    </div>
  </div>
</div>
