/**
 * Main JavaScript file for edu-center theme
 * Optimized version with improved code structure and performance
 */

(function() {
    'use strict';

    // Проверка наличия Swiper
    if (typeof Swiper === 'undefined') {
        console.warn('Swiper library is not loaded');
        return;
    }

    // Утилита для безопасной инициализации Swiper
    function initSwiper(selector, config) {
        const element = document.querySelector(selector);
        if (!element) {
            return null;
        }

        try {
            return new Swiper(selector, config);
        } catch (error) {
            console.error(`Error initializing Swiper for ${selector}:`, error);
            return null;
        }
    }

    // Перемешивание слайдов перед инициализацией слайдера
    function shuffleSwiperSlides(selector) {
        const container = document.querySelector(selector);
        if (!container) {
            return;
        }

        const wrapper = container.querySelector('.swiper-wrapper');
        if (!wrapper) {
            return;
        }

        const slides = Array.from(wrapper.querySelectorAll('.swiper-slide'));
        if (slides.length < 2) {
            return;
        }

        for (let i = slides.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            [slides[i], slides[j]] = [slides[j], slides[i]];
        }

        slides.forEach((slide) => {
            wrapper.appendChild(slide);
        });
    }

    // Утилита для создания Swiper с возможностью переинициализации
    function createResizableSwiper(selector, config) {
        let swiperInstance = null;

        return {
            init() {
                if (swiperInstance) {
                    swiperInstance.destroy(true, true);
                    swiperInstance = null;
                }
                swiperInstance = initSwiper(selector, config);
                return swiperInstance;
            },
            destroy() {
                if (swiperInstance) {
                    swiperInstance.destroy(true, true);
                    swiperInstance = null;
                }
            },
            getInstance() {
                return swiperInstance;
            },
        };
    }

    // Общая конфигурация для слайдеров курсов
    const coursesSwiperConfig = {
        slidesPerView: 1,
        spaceBetween: 20,
        breakpoints: {
            768: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            1280: {
                slidesPerView: 3,
                spaceBetween: 20,
            }
        }
    };

    // Инициализация всех Swiper слайдеров
    function initAllSwipers() {
        // Главный слайдер баннера
        initSwiper('.swiper-main-slider', {
            loop: true,
            spaceBetween: 30,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.main-slider__next',
                prevEl: '.main-slider__prev',
            },
        });

        // Слайдеры курсов (оптимизировано - убрано дублирование)
        const coursesConfigs = [
            { selector: '.swiper-courses-one', next: '.courses__button-next-1', prev: '.courses__button-prev-1' },
            { selector: '.swiper-courses-two', next: '.courses__button-next-2', prev: '.courses__button-prev-2' },
            { selector: '.swiper-courses-three', next: '.courses__button-next-3', prev: '.courses__button-prev-3' }
        ];

        const coursesSwipers = coursesConfigs.map(config => {
            const swiper = createResizableSwiper(config.selector, {
                ...coursesSwiperConfig,
                navigation: {
                    nextEl: config.next,
                    prevEl: config.prev,
                }
            });
            swiper.init();
            return swiper;
        });

        // Слайдер отзывов
        shuffleSwiperSlides('.swiper-testimonials');
        initSwiper('.swiper-testimonials', {
            slidesPerView: 1,
            spaceBetween: 10,
            freeMode: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.testimonials__next',
                prevEl: '.testimonials__prev',
            },
        });

        // Слайдер похожих курсов
        initSwiper('.swiper-featured-courses', {
            slidesPerView: 1,
            spaceBetween: 12,
            freeMode: true,
            navigation: {
                nextEl: '.featured-courses__button-next',
                prevEl: '.featured-courses__button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1280: {
                    slidesPerView: 3,
                },
            }
        });

        // Слайдер клиентов
        initSwiper('.swiper-clients', {
            spaceBetween: 12,
            freeMode: true,
            navigation: {
                nextEl: '.clients__next',
                prevEl: '.clients__prev',
            },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 43,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 43,
                },
                1280: {
                    slidesPerView: 5,
                    spaceBetween: 12,
                },
            }
        });

        // Слайдер новостей
        initSwiper('.swiper-news', {
            slidesPerView: 1,
            spaceBetween: 12,
            freeMode: true,
            navigation: {
                nextEl: '.news__next',
                prevEl: '.news__prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1280: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
            }
        });

        // Слайдер партнеров
        initSwiper('.swiper-partners', {
            spaceBetween: 12,
            freeMode: true,
            navigation: {
                nextEl: '.partners__next',
                prevEl: '.partners__prev',
            },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 43,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 43,
                },
                1280: {
                    slidesPerView: 5,
                    spaceBetween: 12,
                },
            }
        });

        // Возвращаем экземпляры для возможного использования
        return { coursesSwipers };
    }

    // Адаптивный Swiper для преимуществ (только на мобильных)
    function initResizableAdvantagesSwiper() {
        const breakpoint = window.matchMedia('(max-width: 767.98px)');
        let swiperAdvantages = null;

        const enableSwiper = function() {
            if (swiperAdvantages) {
                return;
            }

            swiperAdvantages = initSwiper('.swiper-advantages', {
                spaceBetween: 20,
                slidesPerView: 1,
                freeMode: true,
                navigation: {
                    nextEl: '.advantages__next',
                    prevEl: '.advantages__prev',
                },
                breakpoints: {
                    576: {
                        slidesPerView: 2,
                    }
                },
            });
        };

        const disableSwiper = function() {
            if (swiperAdvantages) {
                swiperAdvantages.destroy(true, true);
                swiperAdvantages = null;
            }
        };

        const checker = function() {
            if (breakpoint.matches) {
                enableSwiper();
            } else {
                disableSwiper();
            }
        };

        // Исправлена ошибка: использовался addEventListener вместо addListener для matchMedia
        if (breakpoint.addEventListener) {
            breakpoint.addEventListener('change', checker);
        } else {
            // Fallback для старых браузеров
            breakpoint.addListener(checker);
        }

        checker();
    }

    // Адаптивный Swiper для расписания курсов (только на мобильных)
    function initResizableScheduleSwiper() {
        const breakpoint = window.matchMedia('(max-width: 767.98px)');
        let swiperSchedule = null;

        const enableSwiper = function() {
            if (swiperSchedule) {
                return;
            }

            swiperSchedule = initSwiper('.swiper-shedule', {
                slidesPerView: 1,
                spaceBetween: 20,
                freeMode: true,
                navigation: {
                    nextEl: '.shedule__button-next',
                    prevEl: '.shedule__button-prev',
                },
            });
        };

        const disableSwiper = function() {
            if (swiperSchedule) {
                swiperSchedule.destroy(true, true);
                swiperSchedule = null;
            }
        };

        const checker = function() {
            if (breakpoint.matches) {
                enableSwiper();
            } else {
                disableSwiper();
            }
        };

        if (breakpoint.addEventListener) {
            breakpoint.addEventListener('change', checker);
        } else {
            // Fallback для старых браузеров
            breakpoint.addListener(checker);
        }

        checker();
    }

    // Каталог курсов: аккордеон + Swiper cards на десктопе
    function initCatalogAccordionSwipers() {
        let swiperInstances = [];

        function manageSwipers() {
            const isDesktop = window.innerWidth >= 768;
            const sliders = document.querySelectorAll('.swiper-catalog');

            swiperInstances.forEach((swiper, index) => {
                if (swiper && !swiper.destroyed) {
                    swiper.destroy(true, true);
                    swiperInstances[index] = null;
                }
            });
            swiperInstances = [];

            if (isDesktop) {
                sliders.forEach((el, index) => {
                    swiperInstances[index] = new Swiper(el, {
                        observer: true,
                        observeParents: true,
                        effect: 'cards',
                        speed: 600,
                        grabCursor: true,
                        initialSlide: 0,
                        followFinger: false,
                        resistanceRatio: 0,
                        cardsEffect: {
                            perSlideRotate: 0,
                            rotate: true,
                            slideShadows: false,
                            perSlideOffset: 14,
                        },
                        mousewheel: {
                            invert: false,
                        },
                        on: {
                            slideChangeTransitionEnd: function() {
                                if (this.isEnd) {
                                    setTimeout(() => {
                                        this.slideTo(0, 600);
                                    }, 100);
                                }
                            }
                        }
                    });
                });
            }
        }

        function manageAccordion() {
            const buttons = document.querySelectorAll('#accordionCatalog .accordion-button');
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                buttons.forEach((btn) => btn.setAttribute('data-bs-toggle', 'collapse'));
            } else {
                buttons.forEach((btn) => btn.removeAttribute('data-bs-toggle'));
                document.querySelectorAll('#accordionCatalog .accordion-collapse').forEach((item) => {
                    item.classList.add('show');
                    item.style.height = 'auto';
                });
            }
        }

        function initAll() {
            manageAccordion();

            if (window.innerWidth >= 768) {
                setTimeout(manageSwipers, 100);
            } else {
                manageSwipers();
            }
        }

        let resizeTimeout;
        function handleResize() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initAll, 200);
        }

        window.addEventListener('resize', handleResize);
        document.addEventListener('shown.bs.collapse', function(event) {
            if (event.target.closest('#accordionCatalog') && window.innerWidth < 768) {
                setTimeout(() => {
                    const swiperEl = event.target.querySelector('.swiper-catalog');
                    if (swiperEl && swiperEl.swiper) {
                        swiperEl.swiper.update();
                    }
                }, 50);
            }
        });

        initAll();
    }

    // Мобильное меню: открытие/закрытие, подменю, сброс при resize
    function initNavMenu() {
        const openNavMenu = document.querySelector('.open-nav-menu');
        const closeNavMenu = document.querySelector('.close-nav-menu');
        const navMenu = document.querySelector('.nav-menu');
        const menuOverlay = document.querySelector('.menu-overlay');
        const mediaSize = 1279.98;

        if (!openNavMenu || !closeNavMenu || !navMenu || !menuOverlay) {
            return;
        }

        function toggleNav() {
            navMenu.classList.toggle('open');
            menuOverlay.classList.toggle('active');
            document.body.classList.toggle('hidden-scrolling');
        }

        function collapseSubMenu() {
            const active = navMenu.querySelector('.menu-item-has-children.active');
            if (active) {
                const subMenu = active.querySelector('.sub-menu');
                if (subMenu) subMenu.removeAttribute('style');
                active.classList.remove('active');
            }
        }

        function resizeFix() {
            if (navMenu.classList.contains('open')) {
                toggleNav();
            }
            if (navMenu.querySelector('.menu-item-has-children.active')) {
                collapseSubMenu();
            }
        }

        openNavMenu.addEventListener('click', toggleNav);
        closeNavMenu.addEventListener('click', toggleNav);
        menuOverlay.addEventListener('click', toggleNav);

        navMenu.addEventListener('click', function(event) {
            if (event.target.hasAttribute('data-toggle') && window.innerWidth <= mediaSize) {
                event.preventDefault();
                const menuItemHasChildren = event.target.parentElement;
                if (menuItemHasChildren.classList.contains('active')) {
                    collapseSubMenu();
                } else {
                    if (navMenu.querySelector('.menu-item-has-children.active')) {
                        collapseSubMenu();
                    }
                    menuItemHasChildren.classList.add('active');
                    const subMenu = menuItemHasChildren.querySelector('.sub-menu');
                    if (subMenu) subMenu.style.maxHeight = subMenu.scrollHeight + 'px';
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > mediaSize) {
                resizeFix();
            }
        });
    }

    // Обработка кнопки "Показать больше/Скрыть"
    function initCollapseLinks() {
        const myCollapsible = document.getElementById('collapseLinks');
        const button = document.querySelector('.links__btn-more');

        if (!myCollapsible || !button) {
            return;
        }

        function fadeText(newText) {
            button.style.opacity = '0';
            button.style.transition = 'opacity 150ms';
            
            setTimeout(() => {
                button.textContent = newText;
                button.style.opacity = '1';
            }, 150);
        }

        myCollapsible.addEventListener('show.bs.collapse', () => {
            fadeText('Скрыть');
        });

        myCollapsible.addEventListener('hide.bs.collapse', () => {
            fadeText('Показать больше');
        });
    }

    // Инициализация при загрузке DOM
    function init() {
        // Проверка наличия Bootstrap (для collapse)
        if (typeof bootstrap === 'undefined' && typeof jQuery === 'undefined') {
            console.warn('Bootstrap or jQuery is not loaded');
        }

        initAllSwipers();
        initResizableAdvantagesSwiper();
        initResizableScheduleSwiper();
        initCatalogAccordionSwipers();
        initNavMenu();
        initCollapseLinks();
    }

    // Запуск инициализации
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM уже загружен
        init();
    }

    // Экспорт для возможного использования извне
    window.eduSwipers = {
        reinit: initAllSwipers
    };

})();
