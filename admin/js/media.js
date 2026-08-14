(function ($) {
	'use strict';

	function config() {
		return window.dinoaiMedia || {};
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

	function addButton($container, attachmentId, $altField) {
		if (!$container.length || $container.find('.dinoai-media-generate').length) {
			return;
		}

		var $wrap = $('<p class="dinoai-media-tools" />');
		var $button = $('<button type="button" class="button dinoai-media-generate" />').text(__('Generate alt text'));
		var $status = $('<span class="dinoai-test-status" aria-live="polite" />');
		$wrap.append($button).append(' ').append($status);
		$container.append($wrap);

		$button.on('click', function () {
			if (!config().configured) {
				$status.text(__('No AI provider configured. Connect an AI provider in DinoAI → Settings.'));
				return;
			}
			if (!config().supportsVision) {
				$status.text(__('The selected AI model cannot analyze images. Choose a vision-capable model such as gpt-4o-mini in DinoAI settings.'));
				return;
			}

			$button.prop('disabled', true);
			$status.text(__('Generating…'));

			wp.apiFetch({
				path: '/dinoai/v1/image-alt',
				method: 'POST',
				data: { attachment_id: attachmentId }
			})
				.then(function (response) {
					var alt = (response && response.alt_text) || '';
					if ($altField && $altField.length) {
						$altField.val(alt).trigger('change');
					}
					$status.text(__('Draft inserted. Save the attachment to keep this alt text.'));
				})
				.catch(function (error) {
					$status.text(friendlyError(error));
				})
				.finally(function () {
					$button.prop('disabled', false);
				});
		});
	}

	$(function () {
		var $alt = $('#attachment_alt');
		if ($alt.length) {
			var id = parseInt($('#post_ID').val(), 10) || 0;
			addButton($alt.closest('.setting, p, td, .attachment-alt'), id, $alt);
		}
	});

	if (window.wp && wp.media && wp.media.view && wp.media.view.Attachment && wp.media.view.Attachment.Details) {
		var Original = wp.media.view.Attachment.Details;
		wp.media.view.Attachment.Details = Original.extend({
			render: function () {
				Original.prototype.render.apply(this, arguments);
				var id = this.model && this.model.get ? this.model.get('id') : 0;
				var $alt = this.$el.find('input[data-setting="alt"], textarea[data-setting="alt"], #attachment-details-two-column-alt-text, #attachment-details-alt-text');
				addButton(this.$el.find('.setting[data-setting="alt"], .attachment-compat, .attachment-info').first().length ? this.$el.find('.setting[data-setting="alt"]').first() : this.$el, id, $alt);
				return this;
			}
		});
	}
})(jQuery);
