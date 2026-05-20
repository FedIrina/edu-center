/**
 * Модалка «Записаться на курс»: data-* + даты, Flatpickr (single date).
 * Поведение повторяет legacy-скрипт на jQuery UI Datepicker.
 */
(function () {
	'use strict';

	const modal = document.getElementById('course-enroll-modal');
	if (!modal) {
		return;
	}

	const datesCache = {};
	let lastCourseEnrollOpener = null;

	function findCourseEnrollOpenerEl(el) {
		if (!el || typeof el !== 'object') {
			return null;
		}
		if (el.nodeType !== 1) {
			el = el.parentElement;
		}
		if (!el || !('closest' in el)) {
			return null;
		}
		let node = el;
		let opener = node.closest('[data-bs-target="#course-enroll-modal"]');
		if (opener) {
			return opener;
		}
		opener = node.closest('a[href="#course-enroll-modal"]');
		return opener || null;
	}

	function getPreferredDefaultStartYmd(trigger) {
		if (!trigger || !trigger.getAttribute) {
			return '';
		}
		const attr = trigger.getAttribute('data-edu-default-start-date');
		if (attr && /^\d{4}-\d{2}-\d{2}$/.test(attr.trim())) {
			return attr.trim();
		}
		return '';
	}

	function parseYmdToLocalDate(ymd) {
		if (!/^\d{4}-\d{2}-\d{2}$/.test(ymd || '')) {
			return null;
		}
		const parts = ymd.split('-');
		const y = Number.parseInt(parts[0], 10);
		const m = Number.parseInt(parts[1], 10);
		const d = Number.parseInt(parts[2], 10);
		if (!y || !m || !d) {
			return null;
		}
		return new Date(y, m - 1, d);
	}

	function ymdOfDate(date) {
		const y = date.getFullYear();
		const m = String(date.getMonth() + 1).padStart(2, '0');
		const d = String(date.getDate()).padStart(2, '0');
		return y + '-' + m + '-' + d;
	}

	function makeDisableRule(modalAllowedDates) {
		if (
			modalAllowedDates === '__loading__' ||
			(Array.isArray(modalAllowedDates) && modalAllowedDates.length === 0)
		) {
			return function () {
				return true;
			};
		}
		if (modalAllowedDates === null || typeof modalAllowedDates === 'undefined') {
			return function () {
				return false;
			};
		}
		const allowedMap = {};
		if (Array.isArray(modalAllowedDates)) {
			modalAllowedDates.forEach((ymd) => {
				if (/^\d{4}-\d{2}-\d{2}$/.test(ymd)) {
					allowedMap[ymd] = true;
				}
			});
		}
		return function (date) {
			return !allowedMap[ymdOfDate(date)];
		};
	}

	function destroyModalDatepickers() {
		modal.querySelectorAll('.edu-cf7-datepicker').forEach((input) => {
			if (input._flatpickr) {
				try {
					input._flatpickr.destroy();
				} catch {
					/* ignore */
				}
			}
		});
	}

	function setModalDateInputsDisabled(disabled, placeholderWhenNoDates) {
		const inputs = modal.querySelectorAll('.edu-cf7-datepicker');
		inputs.forEach((input) => {
			if (disabled) {
				if (input.dataset.eduOriginalPlaceholder === undefined) {
					input.dataset.eduOriginalPlaceholder =
						input.getAttribute('placeholder') !== null ? input.getAttribute('placeholder') : '';
				}
				input.setAttribute(
					'placeholder',
					typeof placeholderWhenNoDates === 'string' ? placeholderWhenNoDates : ''
				);
			} else if (input.dataset.eduOriginalPlaceholder !== undefined) {
				const orig = input.dataset.eduOriginalPlaceholder;
				if (orig === '') {
					input.removeAttribute('placeholder');
				} else {
					input.setAttribute('placeholder', orig);
				}
				delete input.dataset.eduOriginalPlaceholder;
			}
			input.disabled = !!disabled;
			if (disabled) {
				input.value = '';
				input.classList.remove('wpcf7-validates-as-required');
			}
		});
	}

	window.eduReinitModalDatepickerFlatpickr = function (modalAllowedDates) {
		if (typeof window.flatpickr === 'undefined') {
			return;
		}
		const inputs = modal.querySelectorAll('.edu-cf7-datepicker');
		if (!inputs.length) {
			return;
		}
		const isLoading = modalAllowedDates === '__loading__';
		const isEmptyAllowed =
			Array.isArray(modalAllowedDates) && modalAllowedDates.length === 0;
		const noDatesAvailable = isLoading || isEmptyAllowed;
		setModalDateInputsDisabled(
			noDatesAvailable,
			isEmptyAllowed ? 'Без даты' : ''
		);
		const disableRule = makeDisableRule(modalAllowedDates);
		const ruLocale =
			window.flatpickr &&
			window.flatpickr.l10ns &&
			window.flatpickr.l10ns.ru
				? window.flatpickr.l10ns.ru
				: undefined;
		inputs.forEach((input) => {
			if (input._flatpickr) {
				try {
					input._flatpickr.destroy();
				} catch {
					/* ignore */
				}
			}
			window.flatpickr(input, {
				locale: ruLocale,
				dateFormat: 'Y-m-d',
				allowInput: false,
				clickOpens: true,
				disableMobile: true,
				disable: [disableRule]
			});
		});
	};

	function applyModalDefaultStartDate(allowedDates, preferredYmd) {
		if (!preferredYmd) {
			return;
		}
		const input = modal.querySelector('input.wpcf7-calendar');
		if (!input || !input._flatpickr) {
			return;
		}
		let ymd = '';
		if (allowedDates === null || typeof allowedDates === 'undefined') {
			ymd = preferredYmd;
		} else if (Array.isArray(allowedDates)) {
			if (allowedDates.length === 0) {
				return;
			}
			if (allowedDates.indexOf(preferredYmd) !== -1) {
				ymd = preferredYmd;
			} else {
				input._flatpickr.clear();
				return;
			}
		}
		if (!ymd) {
			return;
		}
		const parsed = parseYmdToLocalDate(ymd);
		if (!parsed) {
			return;
		}
		input._flatpickr.setDate(parsed, true, 'Y-m-d');
	}

	function applyModalDates(dates, retries) {
		retries = retries || 0;
		if (typeof window.eduReinitModalDatepicker !== 'function') {
			if (retries < 15) {
				setTimeout(() => {
					applyModalDates(dates, retries + 1);
				}, 50);
			}
			return;
		}
		if (dates === '__loading__') {
			window.eduReinitModalDatepicker('__loading__');
			return;
		}
		window.eduReinitModalDatepicker(dates);
		const preferredYmd = modal.dataset.eduPendingDefaultStartDate || '';
		if (!preferredYmd) {
			return;
		}
		setTimeout(() => {
			applyModalDefaultStartDate(dates, preferredYmd);
			delete modal.dataset.eduPendingDefaultStartDate;
		}, 0);
	}

	function fetchAllowedDates(courseId) {
		if (
			!courseId ||
			typeof window.eduCourseDatesAjax === 'undefined' ||
			!window.eduCourseDatesAjax.ajaxUrl
		) {
			return Promise.resolve(null);
		}
		const payload = new URLSearchParams();
		payload.set('action', window.eduCourseDatesAjax.action);
		payload.set('nonce', window.eduCourseDatesAjax.nonce);
		payload.set('course_id', courseId);
		return fetch(window.eduCourseDatesAjax.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: payload.toString(),
			credentials: 'same-origin'
		})
			.then((response) => response.json())
			.then((res) => {
				if (!res || !res.success || !res.data) {
					return null;
				}
				return Array.isArray(res.data.dates) ? res.data.dates : [];
			})
			.catch(() => null);
	}

	document.addEventListener(
		'click',
		(e) => {
			const opener = findCourseEnrollOpenerEl(e.target);
			if (opener) {
				lastCourseEnrollOpener = opener;
			}
		},
		true
	);

	modal.addEventListener('hidden.bs.modal', () => {
		lastCourseEnrollOpener = null;
		delete modal.dataset.eduPendingDefaultStartDate;
		destroyModalDatepickers();
		setModalDateInputsDisabled(false);
	});

	modal.addEventListener('shown.bs.modal', (event) => {
		const trigger =
			lastCourseEnrollOpener ||
			findCourseEnrollOpenerEl(event.relatedTarget) ||
			(event.relatedTarget && event.relatedTarget.nodeType === 1 ? event.relatedTarget : null);
		lastCourseEnrollOpener = null;

		const form = modal.querySelector('form.course-enroll-form');
		if (!form || !trigger) {
			delete modal.dataset.eduPendingDefaultStartDate;
			return;
		}

		const preferredYmd = getPreferredDefaultStartYmd(trigger);
		if (preferredYmd) {
			modal.dataset.eduPendingDefaultStartDate = preferredYmd;
		} else {
			delete modal.dataset.eduPendingDefaultStartDate;
		}

		const title = trigger.getAttribute('data-course-title');
		const postType = trigger.getAttribute('data-post-type');
		const courseId = trigger.getAttribute('data-course-id');

		if (title !== null) {
			const titleInput = form.querySelector('input[name="course-title"]');
			if (titleInput) {
				titleInput.value = title;
			}
		}
		if (postType !== null) {
			const postTypeInput = form.querySelector('input[name="post-type"]');
			if (postTypeInput) {
				postTypeInput.value = postType;
			}
		}
		if (courseId !== null) {
			const courseIdInput = form.querySelector('input[name="course-id"]');
			if (courseIdInput) {
				courseIdInput.value = courseId;
			}
		}

		const allowedRaw = trigger.getAttribute('data-edu-allowed-dates');
		if (allowedRaw) {
			try {
				const inlineDates = JSON.parse(allowedRaw);
				if (Array.isArray(inlineDates)) {
					modal.dataset.eduEnrollCourseId = courseId || '';
					applyModalDates(inlineDates.length ? inlineDates : []);
					return;
				}
			} catch {
				/* invalid JSON */
			}
		}

		if (courseId && window.eduCourseDatesAjax && window.eduCourseDatesAjax.ajaxUrl) {
			modal.dataset.eduEnrollCourseId = courseId;
			if (Object.prototype.hasOwnProperty.call(datesCache, courseId)) {
				applyModalDates(datesCache[courseId]);
				return;
			}
			applyModalDates('__loading__');
			const requestedId = courseId;
			fetchAllowedDates(courseId).then((dates) => {
				if (modal.dataset.eduEnrollCourseId !== requestedId) {
					return;
				}
				if (dates === null) {
					applyModalDates(null);
					return;
				}
				datesCache[requestedId] = dates;
				applyModalDates(dates);
			});
		} else {
			modal.dataset.eduEnrollCourseId = '';
			applyModalDates(null);
		}
	});
})();

