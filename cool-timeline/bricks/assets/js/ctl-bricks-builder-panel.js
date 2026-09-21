/**
 * Bricks builder panel helpers for Story Timeline.
 *
 * - Lock Choose Media PRO-only options in select controls.
 * - Keep at least one story in the repeater so fields stay editable.
 * - New stories start empty (placeholders), not as a clone of the first story.
 * - Title and description can be typed directly on the builder canvas.
 */
(function () {
	'use strict';

	var PRO_LABEL = 'PRO only';
	var BUILDER_DATA = (typeof ctlBricksFreeBuilderData !== 'undefined' && ctlBricksFreeBuilderData)
		? ctlBricksFreeBuilderData
		: {};
	var STORIES_SELECTORS = [
		'[data-control-key="stories"][data-control="repeater"]',
		'[data-controlkey="stories"][data-control="repeater"]',
		'[data-control="repeater"][data-controlkey="stories"]'
	];
	var STORY_CONTENT_FIELDS = ['title', 'description', 'dateLabel', 'subLabel'];
	var cachedVueGlobals = null;
	var isAddingStory = false;
	var cachedPreviewDoc = null;
	var inlineEditingActive = false;
	var descriptionSyncLock = false;
	var storyFieldSyncLock = false;
	var inlineFieldSyncTimer = 0;
	var inlineFieldSyncTarget = null;
	var boundInlineDocs = [];

	function controlLabelText(control) {
		var label = control && control.querySelector('label, .control-label, .label');
		return label ? (label.textContent || '').trim().toLowerCase() : '';
	}

	function migrateYearLabelDefaultOff(element) {
		if (!element || !element.settings) {
			return false;
		}
		if (element.settings._twpbYearLabelDefaultOff) {
			return false;
		}
		if (Array.isArray(element.settings.stories)) {
			element.settings.stories.forEach(function (story) {
				if (!story || typeof story !== 'object') {
					return;
				}
				story.showYearLabel = false;
			});
		}
		element.settings._twpbYearLabelDefaultOff = true;
		return true;
	}

	function getStoriesControl() {
		for (var i = 0; i < STORIES_SELECTORS.length; i++) {
			var control = document.querySelector(STORIES_SELECTORS[i]);
			if (control) {
				return control;
			}
		}

		return null;
	}

	function getStoryItems(storiesControl) {
		if (!storiesControl) {
			return [];
		}

		return Array.prototype.slice.call(storiesControl.querySelectorAll('.repeater-item'));
	}

	function isDeleteActionButton(button) {
		if (!button) {
			return false;
		}

		var actions = button.closest('.actions');
		if (!actions) {
			return false;
		}

		var actionItems = actions.querySelectorAll('li');
		if (!actionItems.length) {
			return false;
		}

		return button === actionItems[actionItems.length - 1].querySelector('button');
	}

	function defaultStoryIcon() {
		return { library: 'themify', icon: 'ti-time' };
	}

	function emptyStoryTemplate() {
		var fallback = BUILDER_DATA.emptyStory;
		var story = {
			ctl_story_tab: 'content',
			showYearLabel: false,
			year: '2000',
			chooseMedia: 'image',
			iconType: 'icon',
			icon: defaultStoryIcon(),
			title: '',
			description: '',
			dateLabel: '',
			subLabel: ''
		};

		if (fallback && typeof fallback === 'object') {
			Object.keys(fallback).forEach(function (key) {
				if (key === 'title' || key === 'description' || key === 'dateLabel' || key === 'subLabel'
					|| key === 'image' || key === 'gallery' || key === 'videoUrl' || key === 'iconImage') {
					return;
				}
				story[key] = fallback[key];
			});
		}

		return story;
	}

	function getVueContext() {
		try {
			var gp = cachedVueGlobals;

			if (!gp) {
				var candidates = [
					document.querySelector('#bricks-builder'),
					document.querySelector('#bricks-builder-app'),
					document.querySelector('#app'),
					document.querySelector('#bricks-panel'),
					document.querySelector('.brx-body'),
					document.body,
					document.documentElement
				].filter(Boolean);

				for (var i = 0; i < candidates.length && !gp; i++) {
					var node = candidates[i];
					var app = node.__vue_app__ || node.__vue__ || null;
					if (app) {
						gp = (app.config && app.config.globalProperties) || app;
					}

					var component = node.__vueParentComponent || node.__vue__ || null;
					if (!gp && component) {
						gp = (component.appContext && component.appContext.config && component.appContext.config.globalProperties)
							|| component.proxy
							|| component;
					}
				}

				if (!gp) {
					return null;
				}
				cachedVueGlobals = gp;
			}

			var store = gp.$store || gp.store || null;
			var state = (store && store.state) || gp.$_state || null;
			if (!state) {
				return null;
			}

			var activeElement = state.activeElement
				|| (state.builder && state.builder.activeElement)
				|| gp.activeElement
				|| null;
			var activeId = state.activeElementId
				|| (state.builder && state.builder.activeElementId)
				|| (activeElement && activeElement.id)
				|| (typeof activeElement === 'string' ? activeElement : null)
				|| null;

			return {
				store: store,
				state: state,
				activeId: activeId,
				element: isTimelineElement(activeElement) ? activeElement : findStoreElement({ state: state }, activeId),
				gp: gp
			};
		} catch (e) {
			return null;
		}
	}

	function isTimelineElement(el) {
		return el && el.name === 'timeline-story';
	}

	function findStoreElement(ctx, elementId) {
		if (!ctx || !ctx.state || !elementId) {
			return null;
		}
		var elements = (ctx.state.page && ctx.state.page.content) || ctx.state.elements || ctx.state.content || null;
		if (!elements) {
			return null;
		}
		if (Array.isArray(elements)) {
			return elements.find(function (el) { return el && el.id === elementId; }) || null;
		}
		if (typeof elements === 'object') {
			return elements[elementId] || null;
		}
		return null;
	}

	function resolveTimelineElement(ctx) {
		var element = ctx && ctx.element ? ctx.element : null;
		if (!isTimelineElement(element) && window.bricksData && window.bricksData.activeElement) {
			element = window.bricksData.activeElement;
		}
		if (!isTimelineElement(element)) {
			return null;
		}
		var storeEl = findStoreElement(ctx, element.id);
		return storeEl || element;
	}

	function deepCloneStories(stories) {
		try {
			return JSON.parse(JSON.stringify(stories || []));
		} catch (e) {
			return Array.isArray(stories) ? stories.slice() : [];
		}
	}

	function updateStoreElementSettings(ctx, elementId, settings) {
		var el = findStoreElement(ctx, elementId);
		if (!el) {
			return null;
		}

		var nextSettings = Object.assign({}, el.settings || {}, settings);
		if (Array.isArray(settings.stories)) {
			nextSettings.stories = deepCloneStories(settings.stories);
		}
		el.settings = nextSettings;

		if (ctx && ctx.state) {
			var patchItem = function (item) {
				if (!item || item.id !== elementId) {
					return item;
				}
				return Object.assign({}, item, { settings: Object.assign({}, nextSettings) });
			};
			var patchArray = function (arr) {
				return Array.isArray(arr) ? arr.map(patchItem) : arr;
			};

			if (ctx.state.page && Array.isArray(ctx.state.page.content)) {
				ctx.state.page.content = patchArray(ctx.state.page.content);
			}
			if (Array.isArray(ctx.state.content)) {
				ctx.state.content = patchArray(ctx.state.content);
			}
			if (ctx.state.activeElement && ctx.state.activeElement.id === elementId) {
				ctx.state.activeElement = Object.assign({}, ctx.state.activeElement, {
					settings: Object.assign({}, nextSettings)
				});
			}
		}

		if (window.bricksData && window.bricksData.activeElement && window.bricksData.activeElement.id === elementId) {
			window.bricksData.activeElement.settings = Object.assign({}, nextSettings);
		}
		return el;
	}

	function getStoryRepeaterItems() {
		var repeater = getStoriesControl();
		if (!repeater) {
			return [];
		}
		var list = repeater.querySelector('.repeater-items, .repeater-list, [data-repeater-items]');
		if (list) {
			var scoped = list.querySelectorAll(':scope > .repeater-item, :scope > li.repeater-item');
			if (scoped.length) {
				return scoped;
			}
		}
		return repeater.querySelectorAll('.repeater-item');
	}

	function findStoryRepeaterItemByIndex(index) {
		if (index < 0) {
			return null;
		}
		var items = getStoryRepeaterItems();
		return items[index] || null;
	}

	function repeaterItemIndexInStories(node) {
		var repeater = getStoriesControl();
		if (!repeater || !node || !node.closest) {
			return -1;
		}
		var item = node.closest('.repeater-item');
		if (!item || !repeater.contains(item)) {
			return -1;
		}
		return Array.prototype.indexOf.call(getStoryRepeaterItems(), item);
	}

	function findStoryControlInItem(item, fieldKey) {
		if (!item || !fieldKey) {
			return null;
		}
		var selectors = [
			'[data-control-key="' + fieldKey + '"]',
			'[data-controlkey="' + fieldKey + '"]'
		];
		for (var i = 0; i < selectors.length; i++) {
			var node = item.querySelector(selectors[i]);
			if (node) {
				return node.closest('.control, [data-control]') || node;
			}
		}

		var labelMap = {
			title: 'title',
			dateLabel: 'label / date',
			subLabel: 'sub label',
			description: 'description'
		};
		var want = labelMap[fieldKey];
		if (!want) {
			return null;
		}

		var controls = item.querySelectorAll('.control, [data-control]');
		for (var c = 0; c < controls.length; c++) {
			var label = controlLabelText(controls[c]);
			if (label === want) {
				return controls[c];
			}
		}
		return null;
	}

	function findPanelTextInput(control) {
		if (!control) {
			return null;
		}
		return control.querySelector('input[type="text"]')
			|| control.querySelector('input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="number"])')
			|| control.querySelector('textarea:not([id*="mce"])');
	}

	function writePanelTextInput(input, value) {
		if (!input) {
			return false;
		}
		value = value == null ? '' : String(value);
		if (String(input.value || '') === value) {
			return false;
		}

		storyFieldSyncLock = true;
		try {
			var desc = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');
			if (desc && desc.set && input.tagName === 'INPUT') {
				desc.set.call(input, value);
			} else {
				input.value = value;
			}
		} catch (e1) {
			input.value = value;
		}
		input.dispatchEvent(new Event('input', { bubbles: true }));
		input.dispatchEvent(new Event('change', { bubbles: true }));
		window.setTimeout(function () {
			storyFieldSyncLock = false;
		}, 0);
		return true;
	}

	function normalizeStoryFieldValue(field, value) {
		if (field === 'description') {
			return String(value || '').trim();
		}
		return String(value || '').replace(/\s+/g, ' ').trim();
	}

	function patchStoryFieldAtIndex(ctx, element, index, field, value, source) {
		if (!ctx || !element || !element.settings || !Array.isArray(element.settings.stories) || !element.settings.stories[index]) {
			return false;
		}

		value = normalizeStoryFieldValue(field, value);
		var stories = element.settings.stories;
		var nextStories = stories.map(function (story, i) {
			var copy = deepCloneStories([story])[0];
			if (i === index) {
				copy[field] = value;
			}
			return copy;
		});

		element.settings.stories = nextStories;
		var storeEl = findStoreElement(ctx, element.id) || element;
		storeEl.settings = Object.assign({}, storeEl.settings || {}, element.settings);
		storeEl.settings.stories = deepCloneStories(nextStories);

		commitElement(ctx, storeEl, { redraw: false });

		if (source === 'panel') {
			syncCanvasStoryField(index, field, value);
		} else {
			syncPanelStoryField(index, field, value);
		}
		return true;
	}

	function commitElement(ctx, element, options) {
		if (!ctx || !element || !element.settings) {
			return;
		}
		options = options || {};
		var redraw = options.redraw !== false;

		if (Array.isArray(element.settings.stories)) {
			element.settings.stories = deepCloneStories(element.settings.stories);
		}

		var settingsCopy = Object.assign({}, element.settings);
		if (Array.isArray(settingsCopy.stories)) {
			settingsCopy.stories = deepCloneStories(settingsCopy.stories);
		}

		updateStoreElementSettings(ctx, element.id, settingsCopy);

		try {
			if (ctx.store && typeof ctx.store.commit === 'function') {
				ctx.store.commit('updateElement', {
					id: element.id,
					settings: settingsCopy,
					element: element
				});
			}
		} catch (e1) { /* ignore */ }
		try {
			if (ctx.gp && typeof ctx.gp.$_updateElement === 'function') {
				ctx.gp.$_updateElement(element);
			}
		} catch (e2) { /* ignore */ }
		if (redraw) {
			try {
				if (typeof window.bricksRedraw === 'function') {
					window.bricksRedraw();
				}
			} catch (e3) { /* ignore */ }
		}
	}

	function getPreviewDocument() {
		if (cachedPreviewDoc && cachedPreviewDoc.body && cachedPreviewDoc.querySelector('.ctl-wrapper')) {
			return cachedPreviewDoc;
		}

		var selectors = [
			'#bricks-builder-iframe',
			'iframe#bricks-builder-iframe',
			'iframe[name="bricks-builder"]',
			'#bricks-preview-iframe'
		];
		for (var s = 0; s < selectors.length; s++) {
			try {
				var named = document.querySelector(selectors[s]);
				if (named && named.contentDocument && named.contentDocument.body) {
					cachedPreviewDoc = named.contentDocument;
					return cachedPreviewDoc;
				}
			} catch (e0) { /* ignore */ }
		}

		var iframes = document.querySelectorAll('iframe');
		for (var i = 0; i < iframes.length; i++) {
			try {
				var iframeDoc = iframes[i].contentDocument;
				if (iframeDoc && iframeDoc.body && iframeDoc.querySelector('.ctl-wrapper')) {
					cachedPreviewDoc = iframeDoc;
					return cachedPreviewDoc;
				}
			} catch (e1) { /* ignore */ }
		}

		return document;
	}

	function inlineFieldFromNode(node) {
		if (!node || !node.closest) {
			return null;
		}
		if (node.nodeType === 3) {
			node = node.parentElement;
		}
		return node.closest ? node.closest('[data-ctl-inline]') : null;
	}

	function stripInlineHtml(value) {
		return String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
	}

	function readInlineValue(el) {
		if (!el) {
			return '';
		}
		var field = el.getAttribute('data-ctl-inline') || '';
		var placeholder = el.getAttribute('data-ctl-placeholder') || '';
		var value = '';
		if (field === 'description') {
			value = stripInlineHtml(el.innerHTML) ? String(el.innerHTML || '').trim() : '';
		} else {
			value = (el.textContent || '').replace(/\s+/g, ' ').trim();
		}
		if (field === 'year') {
			return value;
		}
		if (placeholder && (value === placeholder || stripInlineHtml(value) === placeholder)) {
			return '';
		}
		return value;
	}

	function syncInlinePlaceholderClass(el) {
		if (!el) {
			return;
		}
		var placeholder = el.getAttribute('data-ctl-placeholder') || '';
		var text = (el.textContent || '').replace(/\s+/g, ' ').trim();
		if (!text || text === placeholder) {
			el.classList.add('ctl-placeholder');
		} else {
			el.classList.remove('ctl-placeholder');
		}
	}

	function persistInlineStoryField(el) {
		if (!el) {
			return;
		}
		var field = el.getAttribute('data-ctl-inline') || '';
		if (field !== 'title' && field !== 'description' && field !== 'dateLabel' && field !== 'subLabel' && field !== 'year') {
			return;
		}

		var storyEl = el.closest('[data-ctl-story-index]');
		var index = storyEl ? parseInt(storyEl.getAttribute('data-ctl-story-index'), 10) : NaN;
		if (isNaN(index) || index < 0) {
			return;
		}

		var value = readInlineValue(el);
		syncInlinePlaceholderClass(el);

		var ctx = getVueContext();
		var element = resolveTimelineElement(ctx);
		if (!element || !element.settings) {
			return;
		}
		if (!Array.isArray(element.settings.stories) || !element.settings.stories[index]) {
			return;
		}

		var story = element.settings.stories[index];
		var current = normalizeStoryFieldValue(field, story[field] || '');
		if (current === value) {
			syncPanelStoryField(index, field, value);
			return;
		}

		patchStoryFieldAtIndex(ctx, element, index, field, value, 'canvas');
	}

	function isCanvasSyncStoryField(field) {
		return field === 'title' || field === 'description' || field === 'dateLabel' || field === 'subLabel';
	}

	function syncPanelStoryField(index, field, value) {
		if (field === 'description') {
			syncPanelDescription(index, value);
			return;
		}
		if (field === 'title' || field === 'dateLabel' || field === 'subLabel') {
			syncPanelTextField(index, field, value);
		}
	}

	function syncPanelTextField(index, fieldKey, value) {
		var item = findStoryRepeaterItemByIndex(index);
		if (!item) {
			return false;
		}
		var control = findStoryControlInItem(item, fieldKey);
		if (!control) {
			return false;
		}
		var input = findPanelTextInput(control);
		if (!input) {
			return false;
		}
		return writePanelTextInput(input, value || '');
	}

	function syncCanvasStoryField(index, field, value) {
		if (field === 'description') {
			syncCanvasDescription(index, value);
			return true;
		}
		var doc = getPreviewDocument();
		if (!doc) {
			return false;
		}
		var el = doc.querySelector('[data-ctl-story-index="' + index + '"] [data-ctl-inline="' + field + '"]');
		if (!el) {
			return false;
		}
		var placeholder = el.getAttribute('data-ctl-placeholder') || '';
		value = String(value || '').trim();
		if (!value || (placeholder && value === placeholder)) {
			el.textContent = placeholder || '';
			el.classList.add('ctl-placeholder');
		} else {
			el.textContent = value;
			el.classList.remove('ctl-placeholder');
		}
		return true;
	}

	function persistStoryFieldFromPanel(index, field, value) {
		var ctx = getVueContext();
		var element = resolveTimelineElement(ctx);
		if (!element || !element.settings || !Array.isArray(element.settings.stories) || !element.settings.stories[index]) {
			return;
		}

		var current = normalizeStoryFieldValue(field, element.settings.stories[index][field] || '');
		value = normalizeStoryFieldValue(field, value);
		if (current === value) {
			syncCanvasStoryField(index, field, value);
			return;
		}

		patchStoryFieldAtIndex(ctx, element, index, field, value, 'panel');
	}

	function syncAllCanvasStoryFieldsFromStore(element) {
		if (!element || !element.settings || !Array.isArray(element.settings.stories)) {
			return;
		}
		var doc = getPreviewDocument();
		var activeInline = doc && doc.activeElement && doc.activeElement.getAttribute
			? doc.activeElement.getAttribute('data-ctl-inline')
			: '';
		var activeStory = doc && doc.activeElement && doc.activeElement.closest
			? doc.activeElement.closest('[data-ctl-story-index]')
			: null;
		var activeStoryIndex = activeStory ? parseInt(activeStory.getAttribute('data-ctl-story-index'), 10) : -1;

		element.settings.stories.forEach(function (story, index) {
			STORY_CONTENT_FIELDS.forEach(function (field) {
				if (activeInline === field && activeStoryIndex === index) {
					return;
				}
				syncCanvasStoryField(index, field, story[field] || '');
			});
		});
	}

	function selectPlaceholderContents(el, doc) {
		if (!el || !doc) {
			return;
		}
		var placeholder = el.getAttribute('data-ctl-placeholder') || '';
		var text = (el.textContent || '').replace(/\s+/g, ' ').trim();
		if (text !== placeholder && !el.classList.contains('ctl-placeholder')) {
			return;
		}
		try {
			var range = doc.createRange();
			range.selectNodeContents(el);
			var win = doc.defaultView || window;
			var sel = win.getSelection ? win.getSelection() : null;
			if (!sel) {
				return;
			}
			sel.removeAllRanges();
			sel.addRange(range);
		} catch (e) { /* ignore */ }
	}

	function bindCanvasInlineEditing() {
		var doc = getPreviewDocument();
		if (!doc || !doc.body || boundInlineDocs.indexOf(doc) !== -1) {
			return;
		}
		if (!doc.querySelector('.ctl-wrapper, .ctl-title, .ctl-description')) {
			return;
		}
		boundInlineDocs.push(doc);

		doc.addEventListener('mousedown', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			event.stopPropagation();
			event.stopImmediatePropagation();
		}, true);

		doc.addEventListener('pointerdown', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			event.stopPropagation();
			event.stopImmediatePropagation();
		}, true);

		doc.addEventListener('click', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			event.stopPropagation();
			event.stopImmediatePropagation();
			if (doc.activeElement !== el) {
				try { el.focus(); } catch (e) { /* ignore */ }
			}
		}, true);

		doc.addEventListener('focusin', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			inlineEditingActive = true;
			selectPlaceholderContents(el, doc);
		}, true);

		doc.addEventListener('focusout', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			window.setTimeout(function () {
				persistInlineStoryField(el);
				if (!inlineFieldFromNode(doc.activeElement)) {
					inlineEditingActive = false;
				}
			}, 0);
		}, true);

		doc.addEventListener('input', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			syncInlinePlaceholderClass(el);
			var field = el.getAttribute('data-ctl-inline') || '';
			if (!isCanvasSyncStoryField(field)) {
				return;
			}
			inlineFieldSyncTarget = el;
			window.clearTimeout(inlineFieldSyncTimer);
			inlineFieldSyncTimer = window.setTimeout(function () {
				if (inlineFieldSyncTarget) {
					persistInlineStoryField(inlineFieldSyncTarget);
				}
				inlineFieldSyncTarget = null;
			}, 200);
		}, true);

		doc.addEventListener('keydown', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			event.stopPropagation();
			var field = el.getAttribute('data-ctl-inline') || '';
			if ((field === 'title' || field === 'dateLabel' || field === 'subLabel' || field === 'year') && event.key === 'Enter') {
				event.preventDefault();
				try { el.blur(); } catch (e) { /* ignore */ }
			}
		}, true);

		doc.addEventListener('paste', function (event) {
			var el = inlineFieldFromNode(event.target);
			if (!el) {
				return;
			}
			var field = el.getAttribute('data-ctl-inline') || '';
			if (field !== 'title' && field !== 'dateLabel' && field !== 'subLabel' && field !== 'year') {
				return;
			}
			event.preventDefault();
			var clip = event.clipboardData || (doc.defaultView && doc.defaultView.clipboardData);
			var text = clip && clip.getData ? clip.getData('text/plain') : '';
			text = String(text || '').replace(/\s+/g, ' ').trim();
			try { doc.execCommand('insertText', false, text); } catch (e) { /* ignore */ }
		}, true);
	}

	function storyHasContent(story) {
		if (!story || typeof story !== 'object') {
			return false;
		}
		if (String(story.title || '').trim()) {
			return true;
		}
		if (String(story.description || '').replace(/<[^>]*>/g, '').trim()) {
			return true;
		}
		if (story.image && (story.image.id || story.image.url)) {
			return true;
		}
		if (String(story.dateLabel || '').trim() || String(story.subLabel || '').trim()) {
			return true;
		}
		return false;
	}

	function storySignature(story) {
		if (!story || typeof story !== 'object') {
			return '';
		}
		return JSON.stringify({
			t: story.title || '',
			d: story.description || '',
			i: (story.image && (story.image.id || story.image.url)) || '',
			y: story.year || '',
			l: story.dateLabel || '',
			s: story.subLabel || ''
		});
	}

	function isStoriesAddButton(target) {
		if (!target || !target.closest) {
			return false;
		}
		var btn = target.closest('button, .add-item, .bricks-add-item');
		if (!btn) {
			return false;
		}
		var control = btn.closest('[data-control="stories"], [data-controlkey="stories"], [data-control-key="stories"]');
		if (!control) {
			control = btn.closest('[data-control="repeater"]');
			if (!control) {
				return false;
			}
			var key = control.getAttribute('data-controlkey')
				|| control.getAttribute('data-control-key')
				|| '';
			if (key !== 'stories') {
				return false;
			}
		}
		if (btn.closest('.repeater-header, .repeater-item .actions')) {
			return false;
		}
		if (btn.classList.contains('add-item') || btn.classList.contains('bricks-add-item')) {
			return true;
		}
		if (btn.closest('.repeater-footer, .repeater-bottom, .control-footer, .repeater-item-add')) {
			return true;
		}
		var label = (btn.textContent || btn.getAttribute('aria-label') || '').toLowerCase();
		return label.indexOf('add story') !== -1 || label.indexOf('add item') !== -1;
	}

	function isStoriesDuplicateButton(target) {
		if (!target || !target.closest) {
			return false;
		}
		var btn = target.closest('button, [role="button"]');
		if (!btn) {
			return false;
		}

		var control = btn.closest('[data-control="stories"], [data-controlkey="stories"], [data-control-key="stories"]');
		if (!control) {
			control = btn.closest('[data-control="repeater"]');
			if (!control) {
				return false;
			}
			var key = control.getAttribute('data-controlkey')
				|| control.getAttribute('data-control-key')
				|| '';
			if (key !== 'stories') {
				return false;
			}
		}

		if (!btn.closest('.repeater-item .actions, .repeater-item .action, .item-actions')) {
			return false;
		}

		var label = (btn.textContent || btn.getAttribute('aria-label') || btn.getAttribute('title') || '').toLowerCase();
		if (label.indexOf('duplicate') !== -1 || label.indexOf('copy item') !== -1 || label.indexOf('clone') !== -1) {
			return true;
		}
		if (btn.classList.contains('duplicate') || btn.querySelector('.duplicate, [class*="duplicate"]')) {
			return true;
		}

		return false;
	}

	function isDefaultDemoStory(story) {
		if (!story || typeof story !== 'object') {
			return false;
		}
		var title = String(story.title || '').trim();
		if (title === 'Amazon is born' || title === 'Amazon Prime debuts' || title === 'Amazon acquires Audible') {
			return true;
		}
		var url = story.image && story.image.url ? String(story.image.url) : '';
		return /amazon[123]\.(jpg|png)/i.test(url);
	}

	function findAddedStoryIndex(beforeSignatures, afterSigs) {
		var beforeCounts = {};
		var i;

		beforeSignatures.forEach(function (sig) {
			beforeCounts[sig] = (beforeCounts[sig] || 0) + 1;
		});

		for (i = 0; i < afterSigs.length; i++) {
			var sig = afterSigs[i];
			if (!beforeCounts[sig]) {
				return i;
			}
			beforeCounts[sig]--;
		}

		return afterSigs.length - 1;
	}

	function getDemoStories() {
		var demos = BUILDER_DATA.demoStories;
		return Array.isArray(demos) ? demos : [];
	}

	function eachTimelineInStore(ctx, callback) {
		if (!ctx || !ctx.state || typeof callback !== 'function') {
			return;
		}
		var seen = {};

		function walk(node) {
			if (!node || typeof node !== 'object') {
				return;
			}
			if (Array.isArray(node)) {
				node.forEach(walk);
				return;
			}
			if (node.id) {
				if (seen[node.id]) {
					return;
				}
				seen[node.id] = true;
			}
			if (isTimelineElement(node)) {
				callback(node);
			}
			if (node.children) {
				walk(node.children);
			}
			if (node.elements) {
				walk(node.elements);
			}
			if (!node.id && !node.name && !node.settings) {
				Object.keys(node).forEach(function (key) {
					walk(node[key]);
				});
			}
		}

		walk(ctx.state.page && ctx.state.page.content);
		walk(ctx.state.elements);
		walk(ctx.state.content);
	}

	function seedDemoStoriesForElement(ctx, element) {
		if (!element) {
			return false;
		}
		if (!element.settings) {
			element.settings = {};
		}
		if (element.settings._twpbDemoStoriesSeeded) {
			return false;
		}
		var demos = getDemoStories();
		if (!demos.length) {
			return false;
		}

		var stories = element.settings.stories;
		var isBlankSingle = Array.isArray(stories)
			&& stories.length === 1
			&& !storyHasContent(stories[0])
			&& !isDefaultDemoStory(stories[0]);
		var needsSeed = !Array.isArray(stories) || stories.length === 0 || isBlankSingle;
		if (!needsSeed) {
			return false;
		}

		element.settings.stories = deepCloneStories(demos);
		element.settings._twpbDemoStoriesSeeded = true;
		commitElement(ctx, element);
		return true;
	}

	var seedDemosTimer = 0;
	function scheduleSeedDemos() {
		if (seedDemosTimer) {
			return;
		}
		seedDemosTimer = window.setTimeout(function () {
			seedDemosTimer = 0;
			sanitizeAllTimelineStories();
		}, 0);
	}

	function sanitizeTimelineStories(ctx, element) {
		if (!element) {
			return;
		}
		ctx = ctx || getVueContext();
		seedDemoStoriesForElement(ctx, element);
	}

	function sanitizeAllTimelineStories(ctx) {
		ctx = ctx || getVueContext();
		eachTimelineInStore(ctx, function (el) {
			sanitizeTimelineStories(ctx, el);
		});
		var active = resolveTimelineElement(ctx);
		if (active) {
			sanitizeTimelineStories(ctx, active);
		}
	}

	function blankAddedStory(beforeLen, beforeSignatures) {
		var ctx = getVueContext();
		var element = resolveTimelineElement(ctx);
		if (!element || !element.settings || !Array.isArray(element.settings.stories)) {
			return false;
		}
		var after = element.settings.stories;
		if (after.length <= beforeLen) {
			return false;
		}

		var afterSigs = after.map(storySignature);
		var newIndex = findAddedStoryIndex(beforeSignatures, afterSigs);
		var addedStory = after[newIndex];
		if (!addedStory) {
			return false;
		}

		var blank = emptyStoryTemplate();
		if (addedStory.id) {
			blank.id = addedStory.id;
		}

		var stories = after.slice();
		stories[newIndex] = blank;
		element.settings.stories = stories;
		commitElement(ctx, element);
		return true;
	}

	function watchAddEmptyStory() {
		var pendingSnapshot = null;
		var blankingGeneration = 0;
		var skipBlankUntil = 0;

		function cancelPendingBlanking() {
			blankingGeneration++;
			pendingSnapshot = null;
		}

		function markDuplicateAction() {
			skipBlankUntil = Date.now() + 1500;
			cancelPendingBlanking();
		}

		function runFallback(snapshot) {
			if (!snapshot) {
				return;
			}
			var generation = ++blankingGeneration;

			function applyBlank() {
				if (generation !== blankingGeneration) {
					return;
				}
				if (Date.now() < skipBlankUntil) {
					return;
				}
				blankAddedStory(snapshot.beforeLen, snapshot.beforeSignatures);
			}
			window.setTimeout(applyBlank, 0);
			window.setTimeout(applyBlank, 50);
			window.setTimeout(applyBlank, 120);
			window.setTimeout(applyBlank, 250);
			window.setTimeout(applyBlank, 500);
			window.setTimeout(applyBlank, 900);
		}

		function captureStoriesSnapshot() {
			var ctx = getVueContext();
			var element = resolveTimelineElement(ctx);
			if (!element || !element.settings || !Array.isArray(element.settings.stories)) {
				return { beforeLen: 0, beforeSignatures: [] };
			}
			return {
				beforeLen: element.settings.stories.length,
				beforeSignatures: element.settings.stories.map(storySignature)
			};
		}

		document.addEventListener('mousedown', function (event) {
			if (isStoriesDuplicateButton(event.target)) {
				markDuplicateAction();
				return;
			}
			if (!isStoriesAddButton(event.target)) {
				return;
			}
			pendingSnapshot = captureStoriesSnapshot();
		}, true);

		document.addEventListener('click', function (event) {
			if (isStoriesDuplicateButton(event.target)) {
				markDuplicateAction();
				return;
			}
			if (!isStoriesAddButton(event.target)) {
				return;
			}
			if (Date.now() < skipBlankUntil) {
				return;
			}
			runFallback(pendingSnapshot || captureStoriesSnapshot());
			pendingSnapshot = null;
		}, true);
	}

	function ensureMinimumStory() {
		if (inlineEditingActive) {
			return;
		}
		var ctx = getVueContext();
		var element = resolveTimelineElement(ctx);
		if (element && element.settings) {
			var changed = false;
			if (!Array.isArray(element.settings.stories) || !element.settings.stories.length) {
				element.settings.stories = [emptyStoryTemplate()];
				changed = true;
			}
			if (migrateYearLabelDefaultOff(element)) {
				changed = true;
			}
			if (changed) {
				commitElement(ctx, element);
				return;
			}
		}

		var storiesControl = getStoriesControl();
		if (!storiesControl) {
			return;
		}

		var items = getStoryItems(storiesControl);

		items.forEach(function (item) {
			var deleteButton = item.querySelector('.repeater-header .actions li:last-child button');
			var deleteItem = deleteButton ? deleteButton.closest('li') : null;
			var lockDelete = items.length <= 1;

			if (deleteButton) {
				deleteButton.disabled = lockDelete;
				deleteButton.setAttribute('aria-disabled', lockDelete ? 'true' : 'false');
			}

			if (deleteItem) {
				deleteItem.classList.toggle('ctl-story-delete-locked', lockDelete);
			}
		});

		if (items.length || isAddingStory) {
			return;
		}

		var addButton = storiesControl.querySelector('.add-item, .bricks-add-item');
		if (!addButton) {
			return;
		}

		isAddingStory = true;
		addButton.click();
		window.setTimeout(function () {
			isAddingStory = false;
			ensureMinimumStory();
		}, 60);
	}

	function blockLockedStoryDelete(event) {
		var storiesControl = getStoriesControl();
		if (!storiesControl) {
			return;
		}

		var button = event.target && event.target.closest
			? event.target.closest('.repeater-header .actions li button')
			: null;

		if (!button || !storiesControl.contains(button) || !isDeleteActionButton(button)) {
			return;
		}

		if (getStoryItems(storiesControl).length <= 1) {
			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();
		}
	}

	function isProOption(li) {
		return (li.textContent || '').indexOf(PRO_LABEL) !== -1;
	}

	function lockProOptions(root) {
		var scope = root && root.querySelectorAll ? root : document;

		scope.querySelectorAll('[data-control="select"] .dropdown > li').forEach(function (li) {
			if (!isProOption(li)) {
				li.classList.remove('ctl-pro-only-option');
				li.removeAttribute('aria-disabled');
				return;
			}

			li.classList.add('ctl-pro-only-option');
			li.setAttribute('aria-disabled', 'true');
		});
	}

	function blockProOptionEvent(event) {
		var li = event.target && event.target.closest
			? event.target.closest('[data-control="select"] .dropdown > li')
			: null;

		if (!li || !isProOption(li)) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		event.stopImmediatePropagation();
	}

	function repeaterItemIndex(node) {
		var index = repeaterItemIndexInStories(node);
		if (index >= 0) {
			return index;
		}
		var item = node && node.closest ? node.closest('.repeater-item') : null;
		if (!item || !item.parentElement) {
			return -1;
		}
		var items = item.parentElement.querySelectorAll(':scope > .repeater-item');
		if (!items.length) {
			items = item.parentElement.querySelectorAll('.repeater-item');
		}
		return Array.prototype.indexOf.call(items, item);
	}

	function isDescriptionControl(control) {
		if (!control) {
			return false;
		}
		var key = (control.getAttribute('data-controlkey') || control.getAttribute('data-control-key') || '').toLowerCase();
		if (key === 'description') {
			return true;
		}
		return controlLabelText(control) === 'description';
	}

	function findDescriptionControl(node) {
		var el = node;
		while (el && el !== document.body) {
			if (el.nodeType === 1 && isDescriptionControl(el)) {
				return el;
			}
			if (el.classList && el.classList.contains('repeater-item')) {
				break;
			}
			el = el.parentElement;
		}
		return null;
	}

	function panelEditorTextarea(control) {
		if (!control) {
			return null;
		}
		return control.querySelector('textarea[id], textarea[name]');
	}

	function readPanelEditorHtml(control) {
		if (!control) {
			return '';
		}
		var textarea = panelEditorTextarea(control);
		if (textarea && window.tinymce) {
			var ed = window.tinymce.get(textarea.id);
			if (ed) {
				return String(ed.getContent() || '').trim();
			}
		}
		if (textarea) {
			return String(textarea.value || '').trim();
		}
		var ce = control.querySelector('[contenteditable="true"]');
		if (ce) {
			return String(ce.innerHTML || '').trim();
		}
		return '';
	}

	function writePanelEditorHtml(control, html) {
		if (!control) {
			return false;
		}
		html = html || '';
		var current = readPanelEditorHtml(control);
		if (current === html) {
			return false;
		}

		descriptionSyncLock = true;
		var textarea = panelEditorTextarea(control);
		if (textarea && window.tinymce) {
			var ed = window.tinymce.get(textarea.id);
			if (ed) {
				ed.setContent(html);
				ed.fire('change');
				ed.fire('input');
				window.setTimeout(function () {
					descriptionSyncLock = false;
				}, 0);
				return true;
			}
		}
		if (textarea) {
			textarea.value = html;
			textarea.dispatchEvent(new Event('input', { bubbles: true }));
			textarea.dispatchEvent(new Event('change', { bubbles: true }));
			window.setTimeout(function () {
				descriptionSyncLock = false;
			}, 0);
			return true;
		}
		var ce = control.querySelector('[contenteditable="true"]');
		if (ce) {
			ce.innerHTML = html;
			ce.dispatchEvent(new Event('input', { bubbles: true }));
			window.setTimeout(function () {
				descriptionSyncLock = false;
			}, 0);
			return true;
		}
		descriptionSyncLock = false;
		return false;
	}

	function syncPanelDescription(index, html) {
		var item = findStoryRepeaterItemByIndex(index);
		if (!item) {
			return false;
		}
		var control = findStoryControlInItem(item, 'description');
		if (!control) {
			control = item.querySelector('[data-control-key="description"], [data-controlkey="description"]');
			if (!control) {
				var controls = item.querySelectorAll('.control');
				for (var i = 0; i < controls.length; i++) {
					if (isDescriptionControl(controls[i])) {
						control = controls[i];
						break;
					}
				}
			}
		}
		return writePanelEditorHtml(control, html);
	}

	function syncCanvasDescription(index, html) {
		var doc = getPreviewDocument();
		if (!doc) {
			return false;
		}
		var desc = doc.querySelector('[data-ctl-story-index="' + index + '"] [data-ctl-inline="description"]');
		if (!desc) {
			return false;
		}
		var active = doc.activeElement;
		if (active === desc || (active && desc.contains(active))) {
			return true;
		}
		var placeholder = desc.getAttribute('data-ctl-placeholder') || 'Add description here';
		if (!stripInlineHtml(html)) {
			desc.innerHTML = placeholder;
			desc.classList.add('ctl-placeholder');
		} else {
			desc.innerHTML = html;
			desc.classList.remove('ctl-placeholder');
		}
		return true;
	}

	function persistDescriptionFromPanel(index, html) {
		persistStoryFieldFromPanel(index, 'description', html);
	}

	function isStoryTextFieldControl(control) {
		if (!control) {
			return false;
		}
		var key = (control.getAttribute('data-controlkey') || control.getAttribute('data-control-key') || '').toLowerCase();
		return key === 'title' || key === 'datelabel' || key === 'sublabel';
	}

	function storyTextFieldKeyFromControl(control) {
		var key = (control.getAttribute('data-controlkey') || control.getAttribute('data-control-key') || '').toLowerCase();
		if (key === 'datelabel') {
			return 'dateLabel';
		}
		if (key === 'sublabel') {
			return 'subLabel';
		}
		if (key === 'title') {
			return 'title';
		}
		return '';
	}

	function findStoryTextFieldControl(node) {
		var el = node;
		while (el && el !== document.body) {
			if (el.nodeType === 1 && isStoryTextFieldControl(el)) {
				return el;
			}
			if (el.classList && el.classList.contains('repeater-item')) {
				break;
			}
			el = el.parentElement;
		}
		return null;
	}

	function readPanelTextInput(control) {
		if (!control) {
			return '';
		}
		var input = findPanelTextInput(control);
		return input ? String(input.value || '').trim() : '';
	}

	function bindPanelDescriptionEditors() {
		if (!window.tinymce || !window.tinymce.editors) {
			return;
		}
		window.tinymce.editors.forEach(function (ed) {
			if (!ed || ed._ctlDescBound) {
				return;
			}
			var textarea = ed.getElement ? ed.getElement() : null;
			if (!textarea || !findDescriptionControl(textarea)) {
				return;
			}
			ed._ctlDescBound = true;
			ed.on('change input Undo Redo SetContent', function () {
				if (descriptionSyncLock || storyFieldSyncLock) {
					return;
				}
				var index = repeaterItemIndexInStories(textarea);
				if (index < 0) {
					index = repeaterItemIndex(textarea);
				}
				if (index < 0) {
					return;
				}
				var control = findDescriptionControl(textarea);
				persistDescriptionFromPanel(index, readPanelEditorHtml(control));
			});
		});
	}

	function watchStoryDescriptionPanel() {
		function handlePanelDescription(event) {
			if (descriptionSyncLock || storyFieldSyncLock) {
				return;
			}
			var panel = document.getElementById('bricks-panel');
			if (!panel || !panel.contains(event.target)) {
				return;
			}
			var control = findDescriptionControl(event.target);
			if (!control) {
				return;
			}
			var index = repeaterItemIndexInStories(event.target);
			if (index < 0) {
				index = repeaterItemIndex(event.target);
			}
			if (index < 0) {
				return;
			}
			window.setTimeout(function () {
				if (descriptionSyncLock || storyFieldSyncLock) {
					return;
				}
				persistDescriptionFromPanel(index, readPanelEditorHtml(control));
			}, 0);
		}

		function handlePanelTextField(event) {
			if (storyFieldSyncLock || descriptionSyncLock) {
				return;
			}
			var input = event.target;
			if (!input || input.tagName !== 'INPUT') {
				return;
			}
			if (input.type === 'checkbox' || input.type === 'radio' || input.type === 'hidden' || input.type === 'number') {
				return;
			}
			var panel = document.getElementById('bricks-panel');
			if (!panel || !panel.contains(input)) {
				return;
			}
			var control = findStoryTextFieldControl(input);
			if (!control) {
				return;
			}
			var field = storyTextFieldKeyFromControl(control);
			if (!field) {
				return;
			}
			var index = repeaterItemIndexInStories(input);
			if (index < 0) {
				index = repeaterItemIndex(input);
			}
			if (index < 0) {
				return;
			}
			window.setTimeout(function () {
				if (storyFieldSyncLock || descriptionSyncLock) {
					return;
				}
				persistStoryFieldFromPanel(index, field, readPanelTextInput(control));
			}, 0);
		}

		document.addEventListener('input', handlePanelDescription, true);
		document.addEventListener('change', handlePanelDescription, true);
		document.addEventListener('input', handlePanelTextField, true);
		document.addEventListener('change', handlePanelTextField, true);
		window.setInterval(bindPanelDescriptionEditors, 500);
	}

	function watchStoryYearPanel() {
		var yearRedrawTimer = 0;

		function isYearTextControl(control) {
			if (!control) {
				return false;
			}
			var key = (control.getAttribute('data-controlkey') || control.getAttribute('data-control-key') || '').toLowerCase();
			if (key === 'year') {
				return true;
			}
			return controlLabelText(control).indexOf('year / label text') !== -1;
		}

		function findYearTextControl(node) {
			var el = node;
			while (el && el !== document.body) {
				if (el.nodeType === 1 && isYearTextControl(el)) {
					return el;
				}
				if (el.classList && el.classList.contains('repeater-item')) {
					break;
				}
				el = el.parentElement;
			}
			return null;
		}

		function isYearToggleControl(control) {
			if (!control) {
				return false;
			}
			var key = (control.getAttribute('data-controlkey') || control.getAttribute('data-control-key') || '').toLowerCase();
			if (key === 'showyearlabel') {
				return true;
			}
			return controlLabelText(control).indexOf('year / label (top)') !== -1;
		}

		function findYearToggleControl(node) {
			var el = node;
			while (el && el !== document.body) {
				if (el.nodeType === 1 && isYearToggleControl(el)) {
					return el;
				}
				if (el.classList && el.classList.contains('repeater-item')) {
					break;
				}
				el = el.parentElement;
			}
			return null;
		}

		function handleYearToggle(event) {
			var input = event.target;
			if (!input || !input.closest) {
				return;
			}
			var panel = document.getElementById('bricks-panel');
			if (!panel || !panel.contains(input)) {
				return;
			}
			if (!findYearToggleControl(input)) {
				return;
			}

			var index = repeaterItemIndex(input);
			if (index < 0) {
				return;
			}

			window.setTimeout(function () {
				var ctx = getVueContext();
				var element = resolveTimelineElement(ctx);
				if (!element || !element.settings || !Array.isArray(element.settings.stories) || !element.settings.stories[index]) {
					return;
				}
				var toggleControl = findYearToggleControl(input);
				var box = toggleControl && toggleControl.querySelector('input[type="checkbox"]');
				var on = false;
				if (box) {
					on = !!box.checked;
				} else {
					var sw = input.closest('[role="switch"], .bricks-switch');
					sw = sw || (toggleControl && toggleControl.querySelector('[role="switch"], .bricks-switch'));
					if (sw) {
						var aria = sw.getAttribute('aria-checked');
						if (aria === 'true' || aria === 'false') {
							on = aria === 'true';
						}
					}
				}
				element.settings.stories[index].showYearLabel = on;
				updateStoreElementSettings(ctx, element.id, element.settings);
				try {
					if (ctx.store && typeof ctx.store.commit === 'function') {
						ctx.store.commit('updateElement', {
							id: element.id,
							settings: Object.assign({}, element.settings),
							element: element
						});
					}
				} catch (e1) { /* ignore */ }
				window.clearTimeout(yearRedrawTimer);
				yearRedrawTimer = window.setTimeout(function () {
					try {
						if (typeof window.bricksRedraw === 'function') {
							window.bricksRedraw();
						}
					} catch (e2) { /* ignore */ }
				}, 80);
			}, 50);
		}

		function handle(event) {
			var input = event.target;
			if (!input || !input.closest) {
				return;
			}
			var panel = document.getElementById('bricks-panel');
			if (!panel || !panel.contains(input)) {
				return;
			}
			if (input.isContentEditable) {
				return;
			}
			if (input.type === 'checkbox' || (input.closest && input.closest('[role="switch"], .bricks-switch'))) {
				return;
			}

			var control = findYearTextControl(input);
			if (!control) {
				return;
			}

			var index = repeaterItemIndex(input);
			if (index < 0) {
				return;
			}

			var ctx = getVueContext();
			var element = resolveTimelineElement(ctx);
			if (!element || !element.settings || !Array.isArray(element.settings.stories) || !element.settings.stories[index]) {
				return;
			}

			var value = String(input.value || '').trim();
			var story = element.settings.stories[index];
			var changed = false;
			if (String(story.year || '') !== value) {
				story.year = value;
				changed = true;
			}
			if (!changed) {
				return;
			}

			updateStoreElementSettings(ctx, element.id, element.settings);
			try {
				if (ctx.store && typeof ctx.store.commit === 'function') {
					ctx.store.commit('updateElement', {
						id: element.id,
						settings: Object.assign({}, element.settings),
						element: element
					});
				}
			} catch (e1) { /* ignore */ }

			var doc = getPreviewDocument();
			var badge = doc && doc.querySelector('.ctl-year[data-ctl-story-index="' + index + '"] .ctl-year-text-inner');
			if (badge) {
				badge.textContent = value || '2000';
			} else if (value && typeof window.bricksRedraw === 'function') {
				window.clearTimeout(yearRedrawTimer);
				yearRedrawTimer = window.setTimeout(function () {
					try { window.bricksRedraw(); } catch (e2) { /* ignore */ }
				}, 120);
			}
		}

		document.addEventListener('input', handle, true);
		document.addEventListener('change', handle, true);
		document.addEventListener('change', handleYearToggle, true);
		document.addEventListener('click', handleYearToggle, true);
	}

	function tagPluginControlGroups() {
		var panel = document.getElementById('bricks-panel');
		if (!panel || !panel.querySelectorAll) return;
		var map = {
			'timeline stories': 'stories',
			'layout settings': 'layout',
			'line': 'line',
			'icon box / dot': 'iconBox',
			'labels / date / year box': 'labels',
			'content background / border': 'contentBox',
			'title / description': 'storyContent'
		};
		panel.querySelectorAll('.control-group').forEach(function (group) {
			var title = group.querySelector('.control-group-title');
			if (!title) return;
			var clone = title.cloneNode(true);
			clone.querySelectorAll('.icon, .arrow, .badge, svg, i, button').forEach(function (el) {
				el.remove();
			});
			var text = (clone.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
			if (map[text]) group.setAttribute('data-twpb-group', map[text]);
		});
		insertPluginSettingsLabel(panel);
	}

	function insertPluginSettingsLabel(panel) {
		if (!panel) return;
		var styleKeys = {
			line: 1,
			iconBox: 1,
			labels: 1,
			contentBox: 1,
			storyContent: 1
		};
		var first = null;
		panel.querySelectorAll('.control-group[data-twpb-group]').forEach(function (group) {
			if (first) return;
			if (styleKeys[group.getAttribute('data-twpb-group')]) {
				first = group;
			}
		});
		var existing = panel.querySelectorAll('.twpb-plugin-settings-label');
		if (!first) {
			existing.forEach(function (el) { el.remove(); });
			return;
		}
		var inGroup = first.firstElementChild && first.firstElementChild.classList.contains('twpb-plugin-settings-label')
			? first.firstElementChild
			: null;
		if (inGroup) {
			existing.forEach(function (el) {
				if (el !== inGroup) el.remove();
			});
			return;
		}
		existing.forEach(function (el) { el.remove(); });
		var label = document.createElement('div');
		label.className = 'twpb-plugin-settings-label';
		label.setAttribute('role', 'presentation');
		label.textContent = 'Cool Timeline Style Settings';
		first.insertBefore(label, first.firstChild);
	}

	document.addEventListener('mousedown', blockProOptionEvent, true);
	document.addEventListener('click', blockProOptionEvent, true);
	document.addEventListener('pointerdown', blockProOptionEvent, true);
	document.addEventListener('mousedown', blockLockedStoryDelete, true);
	document.addEventListener('click', blockLockedStoryDelete, true);
	document.addEventListener('pointerdown', blockLockedStoryDelete, true);

	lockProOptions(document);
	ensureMinimumStory();
	tagPluginControlGroups();
	sanitizeAllTimelineStories();
	window.setTimeout(sanitizeAllTimelineStories, 50);
	window.setTimeout(sanitizeAllTimelineStories, 250);
	window.setTimeout(sanitizeAllTimelineStories, 600);
	watchAddEmptyStory();
	watchStoryYearPanel();
	watchStoryDescriptionPanel();
	bindCanvasInlineEditing();
	window.setInterval(function () {
		bindCanvasInlineEditing();
		if (!inlineEditingActive) {
			ensureMinimumStory();
			sanitizeAllTimelineStories();
			tagPluginControlGroups();
			syncAllCanvasStoryFieldsFromStore(resolveTimelineElement(getVueContext()));
		}
	}, 250);

	[
		'bricks/ajax/load_page/completed',
		'bricks/ajax/pagination/completed',
		'bricks/ajax/nodes_added',
		'bricks/ajax/render_element',
		'bricks/ajax/end'
	].forEach(function (evt) {
		document.addEventListener(evt, function () {
			scheduleSeedDemos();
			window.setTimeout(scheduleSeedDemos, 50);
			window.setTimeout(scheduleSeedDemos, 250);
		});
	});

	try {
		var bootCtx = getVueContext();
		if (bootCtx && bootCtx.store && typeof bootCtx.store.subscribe === 'function') {
			bootCtx.store.subscribe(function () {
				scheduleSeedDemos();
			});
		}
	} catch (e) { /* ignore */ }

	if (typeof MutationObserver !== 'undefined') {
		var observer = new MutationObserver(function (mutations) {
			var needsLock = false;

			mutations.forEach(function (mutation) {
				if (mutation.addedNodes && mutation.addedNodes.length) {
					needsLock = true;
				}
			});

			if (needsLock) {
				lockProOptions(document);
				ensureMinimumStory();
				tagPluginControlGroups();
			}
		});

		observer.observe(document.documentElement, {
			childList: true,
			subtree: true,
		});
	}
})();
