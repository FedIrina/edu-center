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

- `front-page.php` — слайдер, преимущества, теги специализаций, карусели курсов (ближайшие / новые / «горящие»), отзывы (комментарии с мета `_review_*`), новости, клиенты, партнёры
- `header.php` / `footer.php` — меню, логотип, контакты и ссылки из Customizer
- `index.php` — минимальный fallback, если главная не назначена статической страницей
- `inc/customizer.php` — секции и поля настроек темы
- `inc/dependencies.php` — ожидаемые плагины и admin notices (без блокировки сайта)
- `inc/events-functions.php` — Events Manager, связь с LearnPress, мета `_edu_event_*`, метабокс и AJAX для групп
- `inc/cf7-calendar.php` — тег CF7 `[calendar]`, AJAX дат для модалки (Flatpickr из Events Manager)
- `inc/testimonials-helper.php` — URL страницы отзывов из Customizer
- `template-parts/course-card-carousel.php` — карточка курса в каруселях
- `template-parts/modal-course-enroll.php` — модалка с формой CF7

## Предполагаемая среда

Фрагмент взят из конкретного проект. Для работы проекта и фрагмента нужны:

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
- Таксономия `course_specialization` — блок ссылок на главной (регистрация вне репозитория)
- Таксономия `course_tag`, термин «Новинка» — блок новых курсов; `event-tags`, термин «Горящий» — блок горящих курсов
- Мета событий: префикс `_edu_event_*`; связь курс ↔ события — `_course_event_ids`, `_related_course_id`

### Настройки в Customizer

Путь в админке: **Внешний вид → Настроить**. Секции темы (собственные, не путать с полями ACF на странице главной):

| Секция | Поля (тип контрола) | Назначение |
|--------|---------------------|------------|
| **Идентичность сайта** | второй логотип (медиа), слоган (текст) | шапка / слоган |
| **Главная страница** | рубрика «Новости» (`dropdown-categories`), страница «Все отзывы» (`dropdown-pages`) | блок новостей, ссылка «все Отзывы» |
| **Футер** | телефон, email, адрес, Telegram, страница политики (`dropdown-pages`) | подвал |
| **Модалка «Записаться на курс»** | форма CF7 (`select` из опубликованных форм), title в shortcode (текст, необязательно) | `template-parts/modal-course-enroll.php` |
| **Страница «Преподаватели»** | страница для блока «Дополнительная информация» (`dropdown-pages`) | вне этого фрагмента шаблонов; настройка от полной темы |

Ключи `theme_mod`: `edu_center_news_category_id`, `edu_center_testimonials_page_id`, `edu_center_cf7_enroll_form_id`, `edu_center_cf7_enroll_form_title`. Список форм CF7 строится в `edu_center_get_cf7_form_choices()` (hash формы, как в shortcode плагина).

## Структура каталогов

```
edu-center/
├── style.css
├── functions.php          # setup, enqueue, хелперы CF7/новостей/телефона
├── front-page.php
├── header.php / footer.php
├── index.php
├── css/
├── js/
├── fonts/
├── img/
├── inc/
│   ├── customizer.php
│   ├── dependencies.php
│   ├── events-functions.php
│   ├── cf7-calendar.php
│   └── testimonials-helper.php
├── template-parts/
│   ├── course-card-carousel.php
│   └── modal-course-enroll.php
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

## Ограничения фрагмента

- Нет каталога курсов, шаблонов LearnPress single/archive, страницы расписания Events Manager и прочих разделов полной темы
- Интеграционная логика в `inc/events-functions.php` и `inc/cf7-calendar.php` — вырезка под главную и модалку, а не самостоятельный плагин
- Блок новостей и ссылки отзывов на главной без выбора в Customizer не выводятся (рубрика/страница не заданы)

## Автор

Irina Fedorova — см. `style.css`.

## Лицензия

GNU General Public License v2 or later.
