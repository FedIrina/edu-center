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
        <?php echo do_shortcode( '[contact-form-7 id="c41705b" title="Записаться на курс" html_class="course-enroll-form"]' ); ?>
      </div>
    </div>
  </div>
</div>
