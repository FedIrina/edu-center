# edu-center

Фрагмент шаблона страницы пользовательской темы WordPress.

- Демонстрирует PHP-шаблонизацию, хуки WordPress и кастомную вёрстку
- Вёрстка и стили основаны на **предоставленном стороннем макете** (HTML/CSS); фрагмент адаптирует его под WordPress, а не разрабатывает дизайн с нуля
- Анонимизировано для портфолио (без брендинга заказчика)
- Полная тема в репозиторий не входит — это только пример кода, **не пакет для установки** на сторонних сайтах

В выкладке: главная (`front-page.php`), общие `header.php` / `footer.php`, интеграции с ACF, LearnPress и Events Manager.

| | |
|---|---|
| **Версия** | 1.0.0 (`_S_VERSION`: `1.0.0-front-fragment`) |
| **PHP** | 8.3+ |
| **WordPress** | протестировано до 6.9 |
| **Лицензия** | GPL v2 или позднее |
| **Text domain** | `edu-center` |

## Состав фрагмента

- `front-page.php` — слайдер, преимущества, теги специализаций, карусели курсов (ближайшие / новые / «горящие»), отзывы, клиенты, партнёры
- `header.php` / `footer.php` — меню, логотип, контакты из Customizer
- `inc/events-functions.php` — Events Manager, связь с LearnPress, мета `_edu_event_*`, метабокс и AJAX для групп
- `inc/cf7-calendar.php` — тег CF7 `[calendar]`, AJAX дат для модалки (Flatpickr из Events Manager)
- `inc/dependencies.php` — список ожидаемых плагинов и admin notices (без блокировки сайта)

## Предполагаемая среда

Фрагмент писался под конкретный проект, где уже были активны:

| Плагин | Роль в этом коде |
|--------|------------------|
| Advanced Custom Fields (ACF / ACF Pro) | `get_field`, `have_rows` на главной |
| LearnPress | тип `lp_course`, длительность, карточки |
| Events Manager | события, scope, `_course_event_ids`, `_related_course_id` |
| Contact Form 7 (опционально) | модалка «Записаться на курс», тег `[calendar]` |

Без ACF, LearnPress или Events Manager соответствующие участки `front-page.php` и `inc/*` рассчитаны на fatal-ошибки или пустой вывод. В админке при отсутствии плагинов выводятся notice/error (см. `edu_center_check_dependencies()`), автоматической активации плагинов нет.

### Данные и таксономии (как в исходном проекте)

- ACF на главной: `slider_repeater`, `advantages_repeater`, `upcoming_courses_*`, `new_courses_*`, `hot_courses_count`, галереи клиентов и партнёров
- Меню темы: `menu-1`, `footer-menu-about`, `footer-menu-education`
- Таксономия `course_specialization` — блок тегов на главной (регистрация вне этого репозитория)
- Мета событий: префикс `_edu_event_*`; связь курс ↔ события — `_course_event_ids`, `_related_course_id`
- Модальное окно CF7: ID формы в Customizer → **Модалка «Записаться на курс»** (`edu_center_cf7_enroll_form_id`, опционально `edu_center_cf7_enroll_form_title`); вывод в `template-parts/modal-course-enroll.php`

## Структура каталогов

```
edu-center/
├── style.css
├── functions.php
├── front-page.php
├── header.php / footer.php
├── index.php
├── css/
├── js/
├── fonts/
├── img/
├── inc/
├── template-parts/
└── languages/
```

Каталог `docs/` в git не входит (`.gitignore`).

## Подключение ресурсов

На фронте (не в админке) `edu_center_scripts()` подключает Bootstrap 5, Swiper, `main.css`, `additional.css`, Flatpickr из каталога плагина Events Manager и `course-enroll-modal-flatpickr.js` (`eduCourseDatesAjax`).

```php
define( 'EDU_MODAL_DATEPICKER_ENGINE', 'flatpickr' );
```

## Точки расширения

| Фильтр | Назначение |
|--------|------------|
| `edu_center_plugin_dependencies` | список зависимостей от плагинов |
| `edu_center_content_width` | ширина контента (по умолчанию 640) |

### Префиксы в коде

| Область | Префикс |
|---------|---------|
| PHP | `edu_center_*`, `edu_em_*`, `edu_cf7_*`, `edu_get_*` |
| JS | `eduSwipers`, `eduCourseDatesAjax`, `eduReinitModalDatepicker` |
| мета событий | `_edu_event_*` |

## Ограничения репозитория

- Нет каталога курсов, шаблонов LearnPress single/archive, страницы расписания Events Manager и прочих разделов полной темы
- Интеграционная логика в `inc/events-functions.php` и `inc/cf7-calendar.php` — вырезка под главную и модалку, а не самостоятельный плагин

## Автор

Irina Fedorova — см. `style.css`.

## Лицензия

GNU General Public License v2 or later.
