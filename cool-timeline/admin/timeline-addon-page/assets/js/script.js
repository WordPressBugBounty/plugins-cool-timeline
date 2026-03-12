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

	// Single action: install or activate (WordPress core installer; backend handles both).
	$(document).on('click', '.ctl-install-plugin, .cool-plugins-addon.plugin-downloader, .cool-plugins-addon.plugin-activator', function () {
		var $btn = $(this);
		if ($btn.prop('disabled')) {
			return;
		}
		var slug = $btn.data('slug') || $btn.attr('data-plugin-slug');
		var nonce = (typeof cp_events !== 'undefined' && cp_events.install_nonce) ? cp_events.install_nonce : $btn.data('nonce') || $btn.attr('data-action-nonce');
		var action = (typeof cp_events !== 'undefined' && cp_events.install_action) ? cp_events.install_action : 'ctl_dashboard_install_plugin';

	if (!slug || !nonce) {
		return;
	}

	var ajaxUrl = (typeof cp_events !== 'undefined' && cp_events.ajax_url) ? cp_events.ajax_url : '';
	if (!ajaxUrl) {
		return;
	}

	// Divi dependency check: block only "Activate Now" when Divi theme is inactive.
	// Allow "Install Now" to proceed (it may still auto-activate server-side).
	if ($btn.hasClass('ctl-btn-activate') && typeof cp_events !== 'undefined' && !cp_events.divi_active && cp_events.divi_slugs && cp_events.divi_slugs.indexOf(slug) !== -1) {
		return;
	}

	// Elementor dependency check: block install/activate and show inline message if Elementor is not active.
	if (typeof cp_events !== 'undefined' && !cp_events.elementor_active && cp_events.elementor_slugs && cp_events.elementor_slugs.indexOf(slug) !== -1) {
		var msg = cp_events.elementor_required_msg || 'Elementor plugin is required. Please install and activate it first.';
		var $card = $btn.closest('.ctl-card');
		$card.find('.ctl-dependency-notice').remove();
		var $notice = $('<p class="ctl-dependency-notice">' + msg + '</p>');
		$btn.closest('.ctl-card-footer').after($notice);
		$btn.prop('disabled', true).addClass('ctl-btn-processing');
		setTimeout(function () {
			$notice.fadeOut(300, function () { $(this).remove(); });
			$btn.prop('disabled', false).removeClass('ctl-btn-processing');
		}, 6000);
		return;
	}

	// Disable all plugin buttons while the request is in flight.
	disableAllBtns();
		$btn.text($btn.hasClass('ctl-btn-activate') ? 'Activating...' : 'Installing...');

		// Use 'text' and parse JSON manually so leading output (BOM/whitespace/notices) doesn't break the first response.
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
			// Some plugins redirect on activation (e.g. to a welcome page). The XHR then gets HTML instead of JSON.
			// If we got a large HTML response, activation likely succeeded — reload to show updated state.
		if (str.length > 2000) {
			var trim = str.trim();
			if (trim.indexOf('<!') === 0 || trim.indexOf('<html') !== -1 || trim.indexOf('<!DOCTYPE') !== -1) {
				$btn.prop('disabled', false).removeClass('ctl-btn-processing');
				$btn.text('Activated Successfully!');
				requestAnimationFrame(function () {
					setTimeout(function () { window.location.reload(); }, 1200);
				});
				return;
			}
		}
			var response = null;
			var lastParsed = null;
			var idx = 0;
			// When other code outputs JSON before ours, parse from each '{' until we find our object (has success: true).
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
		if (response && response.success) {
			$btn.prop('disabled', false).removeClass('ctl-btn-processing');
			$btn.text('Activated Successfully!');
			requestAnimationFrame(function () {
				setTimeout(function () { window.location.reload(); }, 1200);
			});
			return;
		}
			var msg = '';
			var forMsg = response || lastParsed;
			if (forMsg && forMsg.data) {
				msg = forMsg.data.errorMessage || forMsg.data.message || '';
			}
			// Re-enable all buttons on failure.
			enableAllBtns();
			$btn.text($btn.hasClass('ctl-btn-activate') ? 'Activate Now' : 'Install Now');
			if (msg) {
				alert(msg);
			}
		}).fail(function (xhr) {
			enableAllBtns();
			$btn.text($btn.hasClass('ctl-btn-activate') ? 'Activate Now' : 'Install Now');
			var msg = '';
			if (xhr && xhr.responseText) {
				try {
					var str = xhr.responseText;
					var start = str.indexOf('{');
					if (start !== -1) {
						var data = JSON.parse(str.substring(start));
						if (data && data.data) {
							msg = data.data.errorMessage || data.data.message || '';
						}
					}
				} catch (e) {}
			}
			if (msg) {
				alert(msg);
			}
		});
	});

	// Legacy: separate activate action (if old markup still sends it).
	$(document).on('click', '.plugin-activator[data-plugin-id][data-action-nonce]', function () {
		var $btn = $(this);
		if ($btn.hasClass('ctl-install-plugin')) {
			return; // already handled above
		}
		var nonce = $btn.attr('data-action-nonce');
		var pluginSlug = $btn.attr('data-plugin-slug');
		var pluginFile = $btn.attr('data-plugin-id');
		var pluginTag = $btn.attr('data-plugin-tag') || 'timeline';
		var ajaxUrl = (typeof cp_events !== 'undefined' && cp_events.ajax_url) ? cp_events.ajax_url : '';
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
			$btn.prop('disabled', false).removeClass('ctl-btn-processing');
			$btn.text('Activated Successfully!');
			requestAnimationFrame(function () {
				setTimeout(function () { window.location.reload(); }, 1200);
			});
		} else {
				enableAllBtns();
				$btn.text('Activate');
			}
		}).fail(function () {
			enableAllBtns();
			$btn.text('Activate');
		});
	});

	$('.plugins-list').each(function () {
		var $this = $(this);
		var message = $this.attr('data-empty-message');
		if ($this.children('.plugin-block').length === 0 && $this.children('.ctl-card').length === 0 && message) {
			$this.append('<div class="empty-message">' + message + '</div>');
		}
	});
});
