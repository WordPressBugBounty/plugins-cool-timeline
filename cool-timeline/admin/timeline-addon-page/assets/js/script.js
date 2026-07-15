jQuery(document).ready(function ($) {

	var $allPluginBtns = function () {
		return $('.ctl-install-plugin, .cool-plugins-addon.plugin-downloader, .cool-plugins-addon.plugin-activator');
	};

	function disableAllBtns() {
		$allPluginBtns().not('[disabled]').prop('disabled', true).addClass('ctl-btn-processing');
	}

	function enableAllBtns() {
		$allPluginBtns().prop('disabled', false).removeClass('ctl-btn-processing');
	}

	function getAjaxUrl() {
		return (typeof cp_events !== 'undefined' && cp_events.ajax_url) ? cp_events.ajax_url : '';
	}

	function getInstallAction() {
		return (typeof cp_events !== 'undefined' && cp_events.install_action) ? cp_events.install_action : 'ctl_dashboard_install_plugin';
	}

	function getInstallNonce($btn) {
		return (typeof cp_events !== 'undefined' && cp_events.install_nonce) ? cp_events.install_nonce : $btn.data('nonce') || $btn.attr('data-action-nonce');
	}

	function getProcessingText($btn) {
		return $btn.hasClass('ctl-btn-activate') ? 'Activating...' : 'Installing...';
	}

	function getDefaultButtonText($btn) {
		return $btn.hasClass('ctl-btn-activate') ? 'Activate Now' : 'Install Now';
	}

	function scheduleReload() {
		requestAnimationFrame(function () {
			setTimeout(function () {
				window.location.reload();
			}, 1200);
		});
	}

	function onInstallSuccess($btn) {
		$btn.prop('disabled', false).removeClass('ctl-btn-processing');
		$btn.text('Activated Successfully!');
		scheduleReload();
	}

	function showErrorNotice(message) {
		if (message) {
			$('<div/>').addClass('ctl-error-notice').text(message).appendTo('body');
		}
	}

	function getResponseMessage(response) {
		if (response && response.data) {
			return response.data.errorMessage || response.data.message || '';
		}

		return '';
	}

	function parseFirstJsonObject(str) {
		var response = null;
		var lastParsed = null;
		var idx = 0;

		while ((idx = str.indexOf('{', idx)) !== -1) {
			try {
				response = JSON.parse(str.substring(idx));
				lastParsed = response;
				if (response && response.success === true) {
					break;
				}
				response = null;
			} catch (e) {}
			idx += 1;
		}

		return {
			response: response,
			lastParsed: lastParsed
		};
	}

	function didReceiveActivationHtml(str) {
		var trim;

		if (str.length <= 2000) {
			return false;
		}

		trim = str.trim();
		return trim.indexOf('<!') === 0 || trim.indexOf('<html') !== -1 || trim.indexOf('<!DOCTYPE') !== -1;
	}

	function getFailureMessageFromXhr(xhr) {
		var str;
		var start;
		var data;

		if (!xhr || !xhr.responseText) {
			return '';
		}

		try {
			str = xhr.responseText;
			start = str.indexOf('{');
			if (start === -1) {
				return '';
			}
			data = JSON.parse(str.substring(start));
			return getResponseMessage(data);
		} catch (e) {}

		return '';
	}

	function resetButtonAfterFailure($btn, label) {
		enableAllBtns();
		$btn.text(label);
	}

	function showElementorDependencyNotice($btn) {
		var msg = cp_events.elementor_required_msg || 'Elementor plugin is required. Please install and activate it first.';
		var $card = $btn.closest('.ctl-card');
		var $notice = $('<p/>').addClass('ctl-dependency-notice').text(msg);

		$card.find('.ctl-dependency-notice').remove();
		$btn.closest('.ctl-card-footer').after($notice);
		$btn.prop('disabled', true).addClass('ctl-btn-processing');

		setTimeout(function () {
			$notice.fadeOut(300, function () {
				$(this).remove();
			});
			$btn.prop('disabled', false).removeClass('ctl-btn-processing');
		}, 6000);
	}

	function shouldBlockInstall($btn, slug) {
		if ($btn.hasClass('ctl-btn-activate') && typeof cp_events !== 'undefined' && !cp_events.divi_active && cp_events.divi_slugs && cp_events.divi_slugs.indexOf(slug) !== -1) {
			return true;
		}

		if (typeof cp_events !== 'undefined' && !cp_events.elementor_active && cp_events.elementor_slugs && cp_events.elementor_slugs.indexOf(slug) !== -1) {
			showElementorDependencyNotice($btn);
			return true;
		}

		return false;
	}

	function installPlugin($btn) {
		var slug = $btn.data('slug') || $btn.attr('data-plugin-slug');
		var nonce = getInstallNonce($btn);
		var ajaxUrl = getAjaxUrl();
		var action = getInstallAction();

		if (!slug || !nonce || !ajaxUrl || shouldBlockInstall($btn, slug)) {
			return;
		}

		disableAllBtns();
		$btn.text(getProcessingText($btn));

		$.ajax({
			type: 'POST',
			url: ajaxUrl,
			dataType: 'text',
			data: {
				action: action,
				wp_nonce: nonce,
				slug: slug,
				pagenow: typeof window.pagenow !== 'undefined' ? window.pagenow : ''
			}
		}).done(function (raw) {
			var str = typeof raw === 'string' ? raw : '';
			var parsed;
			var message;

			if (didReceiveActivationHtml(str)) {
				onInstallSuccess($btn);
				return;
			}

			parsed = parseFirstJsonObject(str);
			if (parsed.response && parsed.response.success) {
				onInstallSuccess($btn);
				return;
			}

			message = getResponseMessage(parsed.response || parsed.lastParsed);
			resetButtonAfterFailure($btn, getDefaultButtonText($btn));
			showErrorNotice(message);
		}).fail(function (xhr) {
			resetButtonAfterFailure($btn, getDefaultButtonText($btn));
			showErrorNotice(getFailureMessageFromXhr(xhr));
		});
	}

	function activatePlugin($btn) {
		var nonce = $btn.attr('data-action-nonce');
		var pluginSlug = $btn.attr('data-plugin-slug');
		var ajaxUrl = getAjaxUrl();

		if (!pluginSlug || !nonce || !ajaxUrl) {
			return;
		}

		disableAllBtns();
		$btn.text('Activating...');

		$.ajax({
			type: 'POST',
			url: ajaxUrl,
			data: {
				action: 'ctl_dashboard_install_plugin',
				wp_nonce: (typeof cp_events !== 'undefined' && cp_events.install_nonce) ? cp_events.install_nonce : nonce,
				slug: pluginSlug
			}
		}).done(function (response) {
			if (response && response.success) {
				onInstallSuccess($btn);
				return;
			}

			resetButtonAfterFailure($btn, 'Activate');
		}).fail(function () {
			resetButtonAfterFailure($btn, 'Activate');
		});
	}

	// Single action: install or activate (WordPress core installer; backend handles both).
	$(document).on('click', '.ctl-install-plugin, .cool-plugins-addon.plugin-downloader, .cool-plugins-addon.plugin-activator', function () {
		var $btn = $(this);
		if ($btn.prop('disabled')) {
			return;
		}

		installPlugin($btn);
	});

	// Legacy: separate activate action (if old markup still sends it).
	$(document).on('click', '.plugin-activator[data-plugin-id][data-action-nonce]', function () {
		var $btn = $(this);
		if ($btn.hasClass('ctl-install-plugin')) {
			return;
		}

		activatePlugin($btn);
	});

	$('.plugins-list').each(function () {
		var $this = $(this);
		var message = $this.attr('data-empty-message');
		if ($this.children('.plugin-block').length === 0 && $this.children('.ctl-card').length === 0 && message) {
			$('<div/>').addClass('empty-message').text(message).appendTo($this);
		}
	});
});
