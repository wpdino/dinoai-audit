(function ($) {
	'use strict';

	function getConfig() {
		return window.dinoaiAdmin || {};
	}

	function setStatus($el, message, type) {
		$el.removeClass('is-success is-error is-loading');
		if (type) {
			$el.addClass('is-' + type);
		}
		$el.text(message || '');
	}

	function apiFetch(path, options) {
		if (window.wp && wp.apiFetch) {
			return wp.apiFetch(
				$.extend(
					{
						path: '/dinoai/v1' + path
					},
					options || {}
				)
			);
		}

		return $.ajax({
			url: getConfig().restUrl.replace(/\/$/, '') + path,
			method: (options && options.method) || 'GET',
			contentType: 'application/json',
			data: options && options.data ? JSON.stringify(options.data) : undefined,
			beforeSend: function (xhr) {
				xhr.setRequestHeader('X-WP-Nonce', wp.apiFetch && wp.apiFetch.nonce ? wp.apiFetch.nonce : '');
			}
		});
	}

	function friendlyError(error) {
		if (error && error.message) {
			return error.message;
		}
		return (window.wp && wp.i18n)
			? wp.i18n.__('DinoAI could not complete the request. Please check your AI provider settings and try again.', 'dinoai')
			: 'DinoAI could not complete the request. Please check your AI provider settings and try again.';
	}

	function activateTab(tabName) {
		if (!$('#tab-' + tabName).length) {
			return;
		}

		$('.wpdino-tab-btn, .wpdino-tab-content').removeClass('active');
		$('.wpdino-tab-btn').attr('aria-selected', 'false');

		$('.wpdino-tab-btn[data-tab="' + tabName + '"]')
			.addClass('active')
			.attr('aria-selected', 'true');
		$('#tab-' + tabName).addClass('active');
	}

	function requestedTab() {
		var hash = window.location.hash.replace('#', '');
		var params = new URLSearchParams(window.location.search);
		var queryTab = params.get('tab');

		if (hash && $('#tab-' + hash).length) {
			return hash;
		}

		if (queryTab && $('#tab-' + queryTab).length) {
			return queryTab;
		}

		return '';
	}

	function initTabs() {
		var tab = requestedTab();

		if (tab) {
			activateTab(tab);
		} else if (!$('.wpdino-tab-btn.active').length) {
			$('.wpdino-tab-btn:first').addClass('active');
			var first = $('.wpdino-tab-btn:first').data('tab');
			$('#tab-' + first).addClass('active');
		}

		$(document).on('click', '.wpdino-tab-btn', function (e) {
			e.preventDefault();
			var next = $(this).data('tab');
			activateTab(next);

			if (history.pushState) {
				history.pushState(null, null, '#' + next);
			} else {
				window.location.hash = next;
			}
		});

		$('.wpdino-tab-btn').on('keydown', function (e) {
			if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
				return;
			}

			e.preventDefault();
			var tabs = $('.wpdino-tab-btn');
			var current = tabs.index(this);
			var next = e.key === 'ArrowRight' ? current + 1 : current - 1;

			if (next >= tabs.length) {
				next = 0;
			}
			if (next < 0) {
				next = tabs.length - 1;
			}

			tabs.eq(next).focus().click();
		});
	}

	function bulletMask(value) {
		return value ? '•'.repeat(value.length) : '';
	}

	function initPasswordToggle() {
		$('.wpdino-password-input').each(function () {
			var $input = $(this);
			var actual = $input.attr('data-actual-value') || '';

			$input.data('actual-value', actual);
			$input.data('visible', actual === '');
			$input.data('masked', actual !== '');
		});

		$(document).on('click', '.wpdino-password-toggle', function (e) {
			e.preventDefault();
			var $toggle = $(this);
			var $input = $($toggle.data('target'));

			if (!$input.length) {
				return;
			}

			var visible = $input.data('visible') === true;

			if (visible) {
				var actual = $input.val();
				$input.data('actual-value', actual);
				$input.data('visible', false);
				$input.val(bulletMask(actual));
				$toggle.removeClass('active').attr('aria-pressed', 'false');
				return;
			}

			$input.val($input.data('actual-value') || '');
			$input.data('visible', true);
			$toggle.addClass('active').attr('aria-pressed', 'true');
		});

		$(document).on('keydown', '.wpdino-password-input', function (e) {
			var $input = $(this);

			if ($input.data('visible') === true) {
				return;
			}

			var current = $input.data('actual-value') || '';
			var key = e.key;

			if (key === 'Tab' || e.ctrlKey || e.metaKey || e.altKey) {
				return;
			}

			if (key === 'Backspace') {
				e.preventDefault();
				current = current.slice(0, -1);
				$input.data('actual-value', current);
				$input.val(bulletMask(current));
				return;
			}

			if (key === 'Delete') {
				e.preventDefault();
				var deletePos = this.selectionStart || 0;
				current = current.slice(0, deletePos) + current.slice(deletePos + 1);
				$input.data('actual-value', current);
				$input.val(bulletMask(current));
				return;
			}

			if (key.length !== 1) {
				return;
			}

			e.preventDefault();
			var cursor = this.selectionStart || 0;
			current = current.slice(0, cursor) + key + current.slice(cursor);
			$input.data('actual-value', current);
			$input.val(bulletMask(current));
			setTimeout(function () {
				$input[0].setSelectionRange(cursor + 1, cursor + 1);
			}, 0);
		});

		$(document).on('paste', '.wpdino-password-input', function (e) {
			var $input = $(this);

			if ($input.data('visible') === true) {
				return;
			}

			e.preventDefault();
			var pasted = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
			var current = $input.data('actual-value') || '';
			var cursor = this.selectionStart || 0;
			current = current.slice(0, cursor) + pasted + current.slice(cursor);
			$input.data('actual-value', current);
			$input.val(bulletMask(current));
		});

		$(document).on('input', '.wpdino-password-input', function () {
			var $input = $(this);

			if ($input.data('visible') === true) {
				$input.data('actual-value', $input.val());
			}
		});
	}

	function initNotices() {
		$(document).on('click', '.wpdino-notice-dismiss', function (e) {
			e.preventDefault();
			$(this).closest('.wpdino-admin-notice').fadeOut(200);
		});
	}

	function keepActiveTabOnSave() {
		$(document).on('submit', '.wpdino-form', function () {
			$(this).find('.wpdino-password-input').each(function () {
				var $input = $(this);
				var actual = $input.data('actual-value');

				if (typeof actual !== 'undefined') {
					$input.val(actual);
				}
			});

			var tab = $('.wpdino-tab-btn.active').data('tab') || 'general';
			var $referer = $(this).find('input[name="_wp_http_referer"]');

			if (!$referer.length) {
				return;
			}

			try {
				var current = $referer.val();
				var url = new URL(current, window.location.origin);
				url.searchParams.set('tab', tab);
				url.hash = '';
				$referer.val(url.pathname + url.search);
			} catch (err) {
				// Leave the WordPress referer unchanged if it cannot be parsed.
			}
		});
	}

	$(function () {
		initTabs();
		initPasswordToggle();
		initNotices();
		keepActiveTabOnSave();

		var $test = $('#dinoai-test-connection');
		var $testStatus = $('#dinoai-test-status');

		$test.on('click', function () {
			var $button = $(this);
			$button.prop('disabled', true);
			setStatus($testStatus, (wp.i18n ? wp.i18n.__('Testing…', 'dinoai') : 'Testing…'), 'loading');

			apiFetch('/test-connection', { method: 'POST', data: {} })
				.then(function (response) {
					setStatus(
						$testStatus,
						(response && response.message) || (wp.i18n ? wp.i18n.__('Connection successful.', 'dinoai') : 'Connection successful.'),
						'success'
					);
				})
				.catch(function (error) {
					setStatus($testStatus, friendlyError(error), 'error');
				})
				.finally(function () {
					$button.prop('disabled', false);
				});
		});

		var $audit = $('#dinoai-run-audit');
		var $auditStatus = $('#dinoai-audit-status');
		var $results = $('#dinoai-audit-results');

		$audit.on('click', function () {
			var $button = $(this);
			$button.prop('disabled', true);
			setStatus($auditStatus, (wp.i18n ? wp.i18n.__('Running basic audit…', 'dinoai') : 'Running basic audit…'), 'loading');

			apiFetch('/audit', { method: 'GET' })
				.then(function (data) {
					setStatus($auditStatus, '', '');
					renderAudit(data);
				})
				.catch(function (error) {
					setStatus($auditStatus, friendlyError(error), 'error');
				})
				.finally(function () {
					$button.prop('disabled', false);
				});
		});

		function escapeHtml(text) {
			return $('<div>').text(text || '').html();
		}

		function categoryIcon(category) {
			var map = {
				seo: 'dashicons-search',
				content: 'dashicons-edit',
				accessibility: 'dashicons-universal-access-alt',
				technical: 'dashicons-admin-tools'
			};
			return map[category] || 'dashicons-flag';
		}

		function categoryLabel(category) {
			var map = {
				seo: wp.i18n ? wp.i18n.__('SEO', 'dinoai') : 'SEO',
				content: wp.i18n ? wp.i18n.__('Content', 'dinoai') : 'Content',
				accessibility: wp.i18n ? wp.i18n.__('Accessibility', 'dinoai') : 'Accessibility',
				technical: wp.i18n ? wp.i18n.__('Technical', 'dinoai') : 'Technical'
			};
			return map[category] || category;
		}

		function severityLabel(severity) {
			if (severity === 'error') {
				return wp.i18n ? wp.i18n.__('Error', 'dinoai') : 'Error';
			}
			if (severity === 'warning') {
				return wp.i18n ? wp.i18n.__('Warning', 'dinoai') : 'Warning';
			}
			return wp.i18n ? wp.i18n.__('Notice', 'dinoai') : 'Notice';
		}

		function severityIcon(severity) {
			if (severity === 'error') {
				return 'dashicons-warning';
			}
			if (severity === 'warning') {
				return 'dashicons-flag';
			}
			return 'dashicons-info';
		}

		function scoreBand(score) {
			if (score >= 80) {
				return { slug: 'good', label: wp.i18n ? wp.i18n.__('Looking good', 'dinoai') : 'Looking good' };
			}
			if (score >= 60) {
				return { slug: 'fair', label: wp.i18n ? wp.i18n.__('Room to improve', 'dinoai') : 'Room to improve' };
			}
			return { slug: 'poor', label: wp.i18n ? wp.i18n.__('Needs attention', 'dinoai') : 'Needs attention' };
		}

		function renderAudit(data) {
			if (!data || typeof data.score === 'undefined') {
				$results.html(
					'<div class="dinoai-empty">' +
					'<span class="dinoai-tile-icon" aria-hidden="true"><span class="dashicons dashicons-chart-bar"></span></span>' +
					'<h3>' + escapeHtml(wp.i18n ? wp.i18n.__('No audit yet', 'dinoai') : 'No audit yet') + '</h3>' +
					'<p>' + escapeHtml(wp.i18n ? wp.i18n.__('Run a basic audit to see your site score, category breakdown, and recommended next steps.', 'dinoai') : 'Run a basic audit to see your site score, category breakdown, and recommended next steps.') + '</p>' +
					'</div>'
				);
				return;
			}

			var score = parseInt(data.score, 10) || 0;
			var band = scoreBand(score);
			var radius = 52;
			var circumference = 2 * Math.PI * radius;
			var offset = circumference * (1 - Math.max(0, Math.min(100, score)) / 100);
			var issueCount = typeof data.count !== 'undefined' ? parseInt(data.count, 10) : (data.issues || []).length;
			var countLabel = wp.i18n
				? wp.i18n.sprintf(
					wp.i18n._n('%d finding to review', '%d findings to review', issueCount, 'dinoai'),
					issueCount
				)
				: issueCount + ' findings to review';

			var cats = '';
			$.each(data.categories || {}, function (key, value) {
				var catValue = parseInt(value, 10) || 0;
				cats += '<li><div class="dinoai-cat-head">';
				cats += '<span class="dinoai-cat-icon dashicons ' + categoryIcon(key) + '" aria-hidden="true"></span>';
				cats += '<span>' + escapeHtml(categoryLabel(key)) + '</span>';
				cats += '<strong>' + catValue + '</strong></div>';
				cats += '<div class="dinoai-cat-bar" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + catValue + '" aria-label="' + escapeHtml(categoryLabel(key)) + '">';
				cats += '<span style="width:' + catValue + '%;"></span></div></li>';
			});

			var rank = { error: 0, warning: 1, notice: 2 };
			var sorted = (data.issues || []).slice().sort(function (a, b) {
				var left = rank[a.severity] !== undefined ? rank[a.severity] : 9;
				var right = rank[b.severity] !== undefined ? rank[b.severity] : 9;
				return left - right;
			});

			var issues = '';
			$.each(sorted, function (_, issue) {
				var severity = issue.severity || 'notice';
				var category = issue.category || '';
				issues += '<article class="dinoai-finding dinoai-finding--' + escapeHtml(severity) + '">';
				issues += '<div class="dinoai-tile-icon dinoai-finding-icon" aria-hidden="true"><span class="dashicons ' + categoryIcon(category) + '"></span></div>';
				issues += '<div class="dinoai-finding-body"><header class="dinoai-finding-header">';
				issues += '<h4>' + escapeHtml(issue.title) + '</h4>';
				issues += '<div class="dinoai-finding-tags">';
				issues += '<span class="dinoai-chip">' + escapeHtml(categoryLabel(category)) + '</span>';
				issues += '<span class="dinoai-chip dinoai-chip--' + escapeHtml(severity) + '">';
				issues += '<span class="dashicons ' + severityIcon(severity) + '" aria-hidden="true"></span> ';
				issues += escapeHtml(severityLabel(severity)) + '</span></div></header>';
				issues += '<p class="dinoai-finding-desc">' + escapeHtml(issue.description) + '</p>';
				issues += '<div class="dinoai-finding-action"><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span><div>';
				issues += '<span class="dinoai-finding-action-label">' + escapeHtml(wp.i18n ? wp.i18n.__('Next step', 'dinoai') : 'Next step') + '</span>';
				issues += '<p>' + escapeHtml(issue.recommendation) + '</p></div></div></div></article>';
			});

			var html = '<div class="dinoai-health dinoai-health--' + band.slug + '">';
			html += '<div class="dinoai-score-panel"><div class="dinoai-score-hero">';
			html += '<div class="dinoai-score-ring" role="img" aria-label="' + escapeHtml((wp.i18n ? wp.i18n.sprintf(wp.i18n.__('Site score %d of 100', 'dinoai'), score) : 'Site score ' + score + ' of 100')) + '">';
			html += '<svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">';
			html += '<circle class="dinoai-score-track" cx="60" cy="60" r="' + radius + '"></circle>';
			html += '<circle class="dinoai-score-fill" cx="60" cy="60" r="' + radius + '" stroke-dasharray="' + circumference.toFixed(2) + '" stroke-dashoffset="' + offset.toFixed(2) + '"></circle>';
			html += '</svg><div class="dinoai-score-ring-value"><strong>' + score + '</strong><span>/ 100</span></div></div>';
			html += '<div class="dinoai-score-copy"><p class="dinoai-score-kicker">' + escapeHtml(wp.i18n ? wp.i18n.__('DinoAI Site Score', 'dinoai') : 'DinoAI Site Score') + '</p>';
			html += '<h3 class="dinoai-score-label">' + escapeHtml(band.label) + '</h3>';
			html += '<p class="dinoai-issue-count">' + escapeHtml(countLabel) + '</p></div></div>';
			html += '<ul class="dinoai-score-cats">' + cats + '</ul></div>';

			if (issues) {
				html += '<div class="dinoai-findings"><div class="dinoai-findings-head">';
				html += '<h3>' + escapeHtml(wp.i18n ? wp.i18n.__('Findings', 'dinoai') : 'Findings') + '</h3>';
				html += '<p>' + escapeHtml(wp.i18n ? wp.i18n.__('Each item includes a next step. Nothing is changed until you act.', 'dinoai') : 'Each item includes a next step. Nothing is changed until you act.') + '</p></div>';
				html += '<div class="dinoai-issues">' + issues + '</div></div>';
			}

			if (data.disclaimer) {
				html += '<p class="dinoai-audit-note">' + escapeHtml(data.disclaimer) + '</p>';
			}

			html += '</div>';
			$results.html(html);
		}
	});
})(jQuery);
