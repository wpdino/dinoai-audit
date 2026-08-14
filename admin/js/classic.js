(function ($) {
	'use strict';

	function config() {
		return window.dinoaiAdmin || {};
	}

	function __(text) {
		return window.wp && wp.i18n ? wp.i18n.__(text, 'dinoai') : text;
	}

	function friendlyError(error) {
		if (error && error.message) {
			return error.message;
		}
		return __('DinoAI could not complete the request. Please check your AI provider settings and try again.');
	}

	$(function () {
		var $root = $('[data-dinoai-classic]');
		if (!$root.length) {
			return;
		}

		var $input = $('#dinoai-classic-input');
		var $result = $('#dinoai-classic-result');
		var $status = $root.find('.dinoai-classic-status');
		var $copy = $('#dinoai-classic-copy');
		var postId = (config().postId || 0);

		$root.on('click', '.dinoai-classic-action', function () {
			var promptKey = $(this).data('prompt-key');
			var input = $.trim($input.val() || '');
			var $buttons = $root.find('.dinoai-classic-action');

			if (!input) {
				$status.text(__('Select or provide some content first.'));
				return;
			}

			$buttons.prop('disabled', true);
			$status.text(__('Working…'));

			wp.apiFetch({
				path: '/dinoai/v1/generate',
				method: 'POST',
				data: {
					prompt_key: promptKey,
					input: input,
					post_id: postId
				}
			})
				.then(function (response) {
					$result.val((response && response.text) || '');
					$copy.prop('disabled', !$result.val());
					$status.text('');
				})
				.catch(function (error) {
					$status.text(friendlyError(error));
				})
				.finally(function () {
					$buttons.prop('disabled', false);
				});
		});

		$copy.on('click', function () {
			var text = $result.val();
			if (!text) {
				return;
			}
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text);
			} else {
				$result.trigger('select');
				document.execCommand('copy');
			}
			$status.text(__('Copied.'));
		});
	});
})(jQuery);
