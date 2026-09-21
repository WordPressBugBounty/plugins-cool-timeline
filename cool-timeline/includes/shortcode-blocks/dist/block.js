/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/block.js"
/*!**********************!*\
  !*** ./src/block.js ***!
  \**********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./icons */ "./src/icons.js");
/* harmony import */ var _images_timeline_png__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./images/timeline.png */ "./src/images/timeline.png");
/* harmony import */ var _ssr_preview__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ssr-preview */ "./src/ssr-preview.js");
/* harmony import */ var _settings_ui__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./settings-ui */ "./src/settings-ui.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
/**
 * Cool Timeline Free — shortcode block + Pro-aligned inspector.
 */
;



var __ = wp.i18n.__;
var registerBlockType = wp.blocks.registerBlockType;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps;
var _wp$element = wp.element,
  useEffect = _wp$element.useEffect,
  useRef = _wp$element.useRef;
var ServerSideRender = wp.serverSideRender;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  RangeControl = _wp$components.RangeControl,
  TextControl = _wp$components.TextControl;

/**
 * Register block
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (registerBlockType('cool-timleine/shortcode-block', {
  apiVersion: 3,
  title: __('Cool Timeline Shortcode'),
  description: __('Cool Timeline Shortcode Generator.'),
  category: 'layout',
  icon: _icons__WEBPACK_IMPORTED_MODULE_0__["default"],
  keywords: [__('cool timeline'), __('timeline shortcode'), __('cool timeline block')],
  attributes: {
    layout: {
      type: 'string',
      "default": 'default'
    },
    skin: {
      type: 'string',
      "default": 'default'
    },
    postperpage: {
      type: 'string',
      "default": '10'
    },
    slideToShow: {
      type: 'string',
      "default": ''
    },
    dateformat: {
      type: 'string',
      "default": 'F j'
    },
    icons: {
      type: 'string',
      "default": 'NO'
    },
    animation: {
      type: 'string',
      "default": 'none'
    },
    storycontent: {
      type: 'string',
      "default": 'short'
    },
    order: {
      type: 'string',
      "default": 'DESC'
    },
    isPreview: {
      type: 'boolean',
      "default": false
    },
    timelineWidth: {
      type: 'string',
      "default": 'custom'
    },
    timelineCustomWidth: {
      type: 'string',
      "default": '1140'
    },
    showTitle: {
      type: 'string',
      "default": 'show'
    },
    titleLink: {
      type: 'string',
      "default": 'enable'
    },
    contentLength: {
      type: 'string',
      "default": '50'
    },
    readMoreVisibility: {
      type: 'string',
      "default": 'show'
    },
    readMoreText: {
      type: 'string',
      "default": 'Read More'
    },
    readMoreTarget: {
      type: 'string',
      "default": '_self'
    },
    yearLabel: {
      type: 'string',
      "default": ''
    }
  },
  edit: function edit(props) {
    var blockProps = useBlockProps();
    var attributes = props.attributes,
      setAttributes = props.setAttributes,
      isSelected = props.isSelected,
      clientId = props.clientId;
    var ssrContainerRef = useRef(null);
    var isSsrEmpty = (0,_ssr_preview__WEBPACK_IMPORTED_MODULE_2__.useCtlEmptySsrDetector)(ssrContainerRef);
    useEffect(function () {
      if ('' === attributes.slideToShow) {
        setAttributes({
          slideToShow: '4'
        });
      }
    }, []);
    var dateFormatOptions = [{
      value: 'F j',
      label: 'F j'
    }, {
      value: 'F j Y',
      label: 'F j Y'
    }, {
      value: 'Y-m-d',
      label: 'Y-m-d'
    }, {
      value: 'm/d/Y',
      label: 'm/d/Y'
    }, {
      value: 'd/m/Y',
      label: 'd/m/Y'
    }, {
      value: 'F j Y g:i A',
      label: 'F j Y g:i A'
    }, {
      value: 'Y',
      label: 'Y'
    }];
    var animationOptions = [{
      value: 'none',
      label: __('None')
    }, {
      value: 'fade-up',
      label: __('Fade In Up')
    }];
    var isHorizontal = attributes.layout === 'horizontal';
    var orderHint = attributes.order === 'DESC' ? __('2017 → 1900') : __('1900 → 2017');
    var freeSkin = ['default', 'clean'].includes(attributes.skin) ? attributes.skin : 'default';
    var layoutSettings = React.createElement("div", {
      className: "ctl-sg__tab"
    }, React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Layout')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.LayoutCards, {
      layouts: [{
        value: 'default',
        label: __('Vertical'),
        icon: 'vertical'
      }, {
        value: 'horizontal',
        label: __('Horizontal'),
        icon: 'horizontal'
      }, {
        value: 'one-side',
        label: __('One Side'),
        icon: 'one-side'
      }, {
        value: 'compact',
        label: __('Compact'),
        icon: 'compact'
      }].concat(_toConsumableArray(attributes.layout === 'simple' ? [{
        value: 'simple',
        label: __('Simple'),
        icon: 'simple'
      }] : [])),
      value: attributes.layout,
      onChange: function onChange(value) {
        setAttributes(_objectSpread({
          layout: value
        }, value === 'horizontal' ? {
          skin: 'default'
        } : {}));
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Choose how stories are arranged on the page.'))), React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Timeline Width')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      options: [{
        label: __('Full-width'),
        value: 'full'
      }, {
        label: __('Custom'),
        value: 'custom'
      }],
      value: 'contained' === attributes.timelineWidth ? 'custom' : attributes.timelineWidth || 'custom',
      onChange: function onChange(value) {
        return setAttributes({
          timelineWidth: value
        });
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Full-width spans the content area; Custom sets a max width.'))), 'full' !== attributes.timelineWidth && React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.RangeWithReset, {
      label: __('Custom Width'),
      hint: __('Timeline max width. (px)'),
      value: attributes.timelineCustomWidth !== '' ? parseInt(attributes.timelineCustomWidth, 10) : 1140,
      onChange: function onChange(value) {
        return setAttributes({
          timelineCustomWidth: value !== undefined ? String(value) : '1140'
        });
      },
      onReset: function onReset() {
        return setAttributes({
          timelineCustomWidth: '1140'
        });
      },
      isResetDisabled: attributes.timelineCustomWidth === '1140' || attributes.timelineCustomWidth === '' || attributes.timelineCustomWidth === undefined,
      min: 400,
      max: 1920,
      step: 10
    }), !isHorizontal && React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Skin')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      value: freeSkin,
      onChange: function onChange(value) {
        return setAttributes({
          skin: value
        });
      },
      options: [{
        value: 'default',
        label: __('Default')
      }].concat(_toConsumableArray(attributes.skin === 'clean' ? [{
        value: 'clean',
        label: __('Clean')
      }] : []), [{
        value: 'dark',
        label: __('Dark'),
        locked: true
      }, {
        value: 'light',
        label: __('Light'),
        locked: true
      }])
    })), React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Story Marker')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      value: attributes.icons === 'YES' ? 'YES' : 'NO',
      onChange: function onChange(value) {
        return setAttributes({
          icons: value
        });
      },
      options: [{
        value: 'NO',
        label: __('Dot')
      }, {
        value: 'YES',
        label: __('Icon')
      }]
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Icon shown on the timeline line.'))), isHorizontal && React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Slides To Show')), React.createElement(RangeControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: parseInt(attributes.slideToShow, 10) || 4,
      onChange: function onChange(value) {
        return setAttributes({
          slideToShow: String(value || 1)
        });
      },
      min: 1,
      max: 10,
      step: 1
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Stories visible at once. Autoplay & starting story are in Pro.'))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlAccordionGroup, {
      defaultOpenId: null
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "design-pro",
      label: __('Design'),
      icon: "display",
      badge: __('Pro')
    }, React.createElement("div", {
      className: "ctl-sg__design-panel"
    }, React.createElement("div", {
      className: "ctl-sg__design-current"
    }, React.createElement("span", {
      className: "ctl-sg__design-current-label"
    }, __('Current')), React.createElement("span", {
      className: "ctl-sg__design-current-value"
    }, __('Default')), React.createElement("span", {
      className: "ctl-sg__design-current-tag"
    }, __('Free'))), React.createElement("p", {
      className: "ctl-sg__pro-box-desc"
    }, __('Pro adds Flat, Classic, Elegant, Clean, Modern, and Minimal presets.')), React.createElement("button", {
      type: "button",
      className: "ctl-sg__pro-cta",
      onClick: _settings_ui__WEBPACK_IMPORTED_MODULE_3__.openGoPro
    }, __('Unlock designs'), React.createElement("span", {
      "aria-hidden": "true"
    }, " \u2192")))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "appearance-pro",
      label: __('Connector & Spacing'),
      icon: "connector",
      badge: __('Pro')
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('More control over timeline appearance'),
      features: [__('Connector style and arrow size'), __('Story padding, spacing & corner radius'), __('Marker position on the line')]
    }))));
    var contentSettings = React.createElement("div", {
      className: "ctl-sg__tab"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlAccordionGroup, {
      defaultOpenId: "order"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "categories-pro",
      label: __('Categories'),
      icon: "filter",
      badge: __('Pro')
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('Filter stories by category'),
      features: [__('Multi-category selection'), __('Frontend category filters'), __('Post timeline (any post type)')]
    })), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "order",
      label: __('Story Order'),
      icon: "source"
    }, React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Order')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      value: attributes.order,
      onChange: function onChange(value) {
        return setAttributes({
          order: value
        });
      },
      options: [{
        value: 'ASC',
        label: __('Oldest')
      }, {
        value: 'DESC',
        label: __('Newest')
      }]
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, orderHint)), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProLockedField, {
      label: __('Timeline Based On'),
      hint: __('Date-based or custom story order (drag & drop).')
    }, React.createElement(SelectControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: "default",
      options: [{
        value: 'default',
        label: __('Default (Date Based)')
      }, {
        value: 'custom',
        label: __('Custom Order')
      }],
      onChange: _settings_ui__WEBPACK_IMPORTED_MODULE_3__.openGoPro
    }))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "date",
      label: __('Story Date'),
      icon: "year"
    }, React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Date Format')), React.createElement(SelectControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: attributes.dateformat,
      options: dateFormatOptions,
      onChange: function onChange(value) {
        return setAttributes({
          dateformat: value
        });
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('How the story date appears.'))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProLockedField, {
      label: __('Custom Date Format'),
      hint: __('Use any PHP date format string.')
    }, React.createElement(SelectControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: "",
      options: [{
        value: '',
        label: __('d/m/Y · Custom…')
      }],
      onChange: _settings_ui__WEBPACK_IMPORTED_MODULE_3__.openGoPro
    })), React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Year Label')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      options: [{
        label: __('Default'),
        value: ''
      }, {
        label: __('Show'),
        value: 'show'
      }, {
        label: __('Hide'),
        value: 'hide'
      }],
      value: attributes.yearLabel || '',
      onChange: function onChange(value) {
        return setAttributes({
          yearLabel: value
        });
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Only for Vertical and One sided layout.')))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "story-content",
      label: __('Story Content'),
      icon: "display"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.StoryTitleSettingsFields, {
      attributes: attributes,
      setAttributes: setAttributes
    }), React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Content Type')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      options: [{
        label: __('Short'),
        value: 'short'
      }, {
        label: __('Full'),
        value: 'full'
      }, {
        label: __('Hide'),
        value: 'hide'
      }],
      value: attributes.storycontent,
      onChange: function onChange(value) {
        return setAttributes({
          storycontent: value
        });
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Short shows an excerpt. Full shows the complete story. Hide removes the description.'))), 'full' !== attributes.storycontent && 'hide' !== attributes.storycontent && React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Content Length')), React.createElement(TextControl, {
      __next40pxDefaultSize: true,
      value: attributes.contentLength,
      onChange: function onChange(value) {
        return setAttributes({
          contentLength: value !== undefined && value !== null ? String(value) : ''
        });
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Number of words shown in the excerpt.'))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ReadMoreSettingsFields, {
      attributes: attributes,
      setAttributes: setAttributes
    })), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "pagination-pro",
      label: __('Pagination'),
      icon: "load"
    }, React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Pagination Type')), React.createElement(SelectControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: "default",
      options: [{
        label: __('Default'),
        value: 'default'
      }, {
        label: __('Off (Pro)'),
        value: 'off'
      }, {
        label: __('Ajax Load More (Pro)'),
        value: 'ajax_load_more'
      }],
      onChange: function onChange(value) {
        if (value !== 'default') {
          (0,_settings_ui__WEBPACK_IMPORTED_MODULE_3__.openGoPro)();
        }
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Off shows all stories at once.'))), React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Pagination Design')), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.SegmentedControl, {
      className: "ctl-sg__segmented--wrap",
      value: "style-1",
      onChange: function onChange(value) {
        if (value !== 'style-1') {
          (0,_settings_ui__WEBPACK_IMPORTED_MODULE_3__.openGoPro)();
        }
      },
      options: [{
        label: __('Classic'),
        value: 'style-1'
      }, {
        label: __('Minimal'),
        value: 'style-2',
        locked: true
      }, {
        label: __('Circle'),
        value: 'style-3',
        locked: true
      }, {
        label: __('Joined'),
        value: 'style-4',
        locked: true
      }]
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Preset look for the numeric pagination.'))), React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Stories Per Page')), React.createElement(RangeControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: parseInt(attributes.postperpage, 10) || 10,
      onChange: function onChange(value) {
        return setAttributes({
          postperpage: String(value || 1)
        });
      },
      min: 1,
      max: 50,
      step: 1
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Number of stories loaded at a time. The editor preview shows up to 3 stories, even if this value is set higher.'))))));
    var extraSettings = React.createElement("div", {
      className: "ctl-sg__tab"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlAccordionGroup, {
      defaultOpenId: !isHorizontal ? 'animation' : 'year-pro'
    }, !isHorizontal && React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "animation",
      label: __('Animation'),
      icon: "play"
    }, React.createElement("div", {
      className: "ctl-sg__field"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldLabel, null, __('Entrance Animation')), React.createElement(SelectControl, {
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
      value: attributes.animation,
      options: animationOptions,
      onChange: function onChange(value) {
        return setAttributes({
          animation: value
        });
      }
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('How stories appear as visitors scroll.'))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('More entrance effects in Pro'),
      features: [__('Zoom, flip, slide & more variants')]
    })), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "year-pro",
      label: __('Year Navigation Menu'),
      icon: "year",
      badge: __('Pro')
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('Jump between years instantly'),
      features: [__('Clickable year navigation menu'), __('Left, right, or bottom position'), __('3 navigation styles')]
    }), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.FieldHint, null, __('Year labels (show/hide) are already available in Timeline Settings.'))), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "filters-pro",
      label: __('Category Filters'),
      icon: "filter",
      badge: __('Pro')
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('Let visitors filter the timeline'),
      features: [__('Category filter bar'), __('Choose which categories appear')]
    })), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "scroll-pro",
      label: __('Scroll Fill'),
      icon: "connector",
      badge: __('Pro')
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('Highlight progress as users scroll'),
      features: [__('Animated line fill'), __('Works on vertical & horizontal layouts')]
    })), React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlToggleSection, {
      id: "spacing-pro",
      label: __('Spacing & Size'),
      icon: "spacing",
      badge: __('Pro')
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.ProUpsell, {
      title: __('Fine-tune every spacing control'),
      features: [__('Story padding & spacing'), __('Corner radius'), __('Marker position on the line')]
    }))));
    return [!!isSelected && React.createElement(InspectorControls, {
      key: "inspector"
    }, React.createElement(_settings_ui__WEBPACK_IMPORTED_MODULE_3__.CtlSettingsShell, {
      tabs: [{
        name: 'layout',
        title: __('Layout'),
        className: 'ctl-sg__tab-btn',
        content: layoutSettings
      }, {
        name: 'content',
        title: __('Content'),
        className: 'ctl-sg__tab-btn',
        content: contentSettings
      }, {
        name: 'extra',
        title: __('Extra'),
        className: 'ctl-sg__tab-btn',
        content: extraSettings
      }]
    })), attributes.isPreview ? React.createElement("img", {
      key: "ctl-preview-image",
      src: _images_timeline_png__WEBPACK_IMPORTED_MODULE_1__["default"],
      alt: ""
    }) : React.createElement("div", _extends({
      key: props.clientId
    }, blockProps, {
      onClickCapture: function onClickCapture(event) {
        var target = event.target;
        if (target && target.closest && target.closest('a')) {
          event.preventDefault();
          event.stopPropagation();
        }
      }
    }), React.createElement("div", {
      ref: ssrContainerRef
    }, React.createElement(ServerSideRender, {
      block: "cool-timleine/shortcode-block",
      attributes: (0,_ssr_preview__WEBPACK_IMPORTED_MODULE_2__.getCtlSsrAttributes)(attributes),
      httpMethod: "POST",
      LoadingResponsePlaceholder: _ssr_preview__WEBPACK_IMPORTED_MODULE_2__.CtlSsrLoadingPlaceholder
    })), isSsrEmpty && React.createElement(_ssr_preview__WEBPACK_IMPORTED_MODULE_2__.CtlEmptyStoryNotice, {
      clientId: clientId,
      description: __('Try the Cool Timeline Block to build your timeline directly here — no separate story required.'),
      showCreateStory: true
    }))];
  },
  save: function save() {
    return null;
  },
  example: {
    attributes: {
      isPreview: true
    }
  }
}));

/***/ },

/***/ "./src/icons.js"
/*!**********************!*\
  !*** ./src/icons.js ***!
  \**********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
var CtlIcon = function CtlIcon() {
  return React.createElement("svg", {
    width: "100%",
    height: "100%",
    viewBox: "0 0 62 62",
    version: "1.1",
    xmlns: "http://www.w3.org/2000/svg",
    xmlnsXlink: "http://www.w3.org/1999/xlink",
    xmlSpace: "preserve",
    xmlnsserif: "http://www.serif.com/",
    style: {
      fillRule: "evenodd",
      clipRule: "evenodd",
      strokeLinecap: "round",
      strokeLinejoin: "round",
      strokeMiterlimit: 1.5
    }
  }, React.createElement("g", {
    id: "icon-only",
    serifid: "icon only"
  }, React.createElement("g", {
    id: "icon-only1",
    serifid: "icon-only"
  }, React.createElement("g", {
    id: "icon"
  }, React.createElement("rect", {
    x: "29.146",
    y: "-0.042",
    width: "3.149",
    height: "61.44"
  }), React.createElement("path", {
    d: "M18.433,21.461l-0.007,-4.311l5.77,-4.905l-5.766,-4.923l0.003,-4.293l-18.433,-0l-0,18.432l18.433,0",
    style: {
      fill: "#f12945"
    }
  }), React.createElement("circle", {
    cx: "30.72",
    cy: "12.245",
    r: "3.046",
    style: {
      fill: "#fff",
      stroke: "#000",
      strokeWidth: 2.18 + "px"
    }
  }), React.createElement("path", {
    d: "M43.007,21.461l0.007,4.312l-5.77,4.905l5.766,4.922l-0.003,4.294l18.433,-0l0,-18.433l-18.433,0",
    style: {
      fill: "#01c5bd"
    }
  }), React.createElement("circle", {
    cx: "30.72",
    cy: "30.678",
    r: "3.046",
    style: {
      fill: "#fff",
      stroke: "#000",
      strokeWidth: 2.18 + "px"
    }
  }), React.createElement("path", {
    d: "M18.433,58.326l-0.007,-4.311l5.77,-4.905l-5.766,-4.923l0.003,-4.293l-18.433,-0l-0,18.432l18.433,0",
    style: {
      fill: "#f12945"
    }
  }), React.createElement("circle", {
    cx: "30.72",
    cy: "49.11",
    r: "3.046",
    style: {
      fill: "#fff",
      stroke: "#000",
      strokeWidth: 2.18 + "px"
    }
  })))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (CtlIcon);

/***/ },

/***/ "./src/settings-ui.js"
/*!****************************!*\
  !*** ./src/settings-ui.js ***!
  \****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CtlAccordionGroup: () => (/* binding */ CtlAccordionGroup),
/* harmony export */   CtlSettingsShell: () => (/* binding */ CtlSettingsShell),
/* harmony export */   CtlToggleSection: () => (/* binding */ CtlToggleSection),
/* harmony export */   FieldHint: () => (/* binding */ FieldHint),
/* harmony export */   FieldLabel: () => (/* binding */ FieldLabel),
/* harmony export */   GO_PRO_URL: () => (/* binding */ GO_PRO_URL),
/* harmony export */   LayoutCards: () => (/* binding */ LayoutCards),
/* harmony export */   LayoutIcon: () => (/* binding */ LayoutIcon),
/* harmony export */   ProLockedField: () => (/* binding */ ProLockedField),
/* harmony export */   ProMarketingBox: () => (/* binding */ ProMarketingBox),
/* harmony export */   ProUpsell: () => (/* binding */ ProUpsell),
/* harmony export */   RangeWithReset: () => (/* binding */ RangeWithReset),
/* harmony export */   ReadMoreSettingsFields: () => (/* binding */ ReadMoreSettingsFields),
/* harmony export */   SectionHeading: () => (/* binding */ SectionHeading),
/* harmony export */   SegmentedControl: () => (/* binding */ SegmentedControl),
/* harmony export */   StoryTitleSettingsFields: () => (/* binding */ StoryTitleSettingsFields),
/* harmony export */   openGoPro: () => (/* binding */ openGoPro)
/* harmony export */ });
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./icons */ "./src/icons.js");
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/**
 * Shared shortcode-generator inspector UI helpers (Free).
 * Mirrors Cool Timeline Pro chrome, with locks + Go Pro marketing.
 */
;
var __ = wp.i18n.__;
var _wp$element = wp.element,
  useState = _wp$element.useState,
  Children = _wp$element.Children,
  cloneElement = _wp$element.cloneElement,
  isValidElement = _wp$element.isValidElement;
var _wp$components = wp.components,
  Button = _wp$components.Button,
  ButtonGroup = _wp$components.ButtonGroup,
  TabPanel = _wp$components.TabPanel,
  Tooltip = _wp$components.Tooltip,
  ExternalLink = _wp$components.ExternalLink,
  RangeControl = _wp$components.RangeControl,
  TextControl = _wp$components.TextControl;
var GO_PRO_URL = 'https://cooltimeline.com/plugin/cool-timeline-pro/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=ctl_shortcode';
var REVIEW_URL = 'https://wordpress.org/plugins/cool-timeline/#reviews';
var openGoPro = function openGoPro() {
  window.open(GO_PRO_URL, '_blank', 'noopener,noreferrer');
};
var ReviewStars = function ReviewStars() {
  return React.createElement("span", {
    className: "ctl-sg__review-stars",
    "aria-hidden": "true"
  }, [0, 1, 2, 3, 4].map(function (i) {
    return React.createElement("svg", {
      key: i,
      viewBox: "0 0 20 20",
      width: "12",
      height: "12"
    }, React.createElement("path", {
      d: "M10 1.5l2.4 5.1 5.6.7-4.1 3.8 1.1 5.5L10 14.1 5 16.6l1.1-5.5L2 7.3l5.6-.7L10 1.5z",
      fill: "currentColor"
    }));
  }));
};
var LockGlyph = function LockGlyph(_ref) {
  var _ref$size = _ref.size,
    size = _ref$size === void 0 ? 10 : _ref$size;
  return React.createElement("svg", {
    viewBox: "0 0 16 16",
    width: size,
    height: size,
    "aria-hidden": "true"
  }, React.createElement("path", {
    fill: "currentColor",
    d: "M4.5 7V5.2a3.5 3.5 0 0 1 7 0V7H13a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1.5zm1.5 0h4V5.2a2 2 0 1 0-4 0V7z"
  }));
};
var FieldLabel = function FieldLabel(_ref2) {
  var children = _ref2.children;
  return React.createElement("div", {
    className: "ctl-sg__label"
  }, children);
};
var FieldHint = function FieldHint(_ref3) {
  var children = _ref3.children;
  return children ? React.createElement("p", {
    className: "ctl-sg__hint"
  }, children) : null;
};
var SectionHeading = function SectionHeading(_ref4) {
  var children = _ref4.children;
  return React.createElement("div", {
    className: "ctl-sg__section-heading",
    role: "presentation"
  }, React.createElement("span", null, children));
};
var AccordionIcon = function AccordionIcon(_ref5) {
  var type = _ref5.type;
  if (type === 'filter') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M4 5h16l-6.2 7.4V19l-3.6 2v-8.6L4 5z",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.8",
      strokeLinejoin: "round"
    }));
  }
  if (type === 'connector') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M5 12h11M13 7l5 5-5 5",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "2",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }));
  }
  if (type === 'spacing') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M8 5v14M16 5v14M10.5 12H13.5",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.8",
      strokeLinecap: "round"
    }), React.createElement("circle", {
      cx: "12",
      cy: "12",
      r: "1.4",
      fill: "currentColor"
    }));
  }
  if (type === 'play') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M8 6.5v11l10-5.5L8 6.5z",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.8",
      strokeLinejoin: "round"
    }));
  }
  if (type === 'source') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M6 4.5h9l3 3V19.5H6z",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.7",
      strokeLinejoin: "round"
    }), React.createElement("path", {
      d: "M15 4.5V8h3",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.7",
      strokeLinejoin: "round"
    }), React.createElement("path", {
      d: "M9 12h6M9 15.5h4",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.7",
      strokeLinecap: "round"
    }));
  }
  if (type === 'display') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M2.5 12s3.2-5.5 9.5-5.5S21.5 12 21.5 12s-3.2 5.5-9.5 5.5S2.5 12 2.5 12z",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.7"
    }), React.createElement("circle", {
      cx: "12",
      cy: "12",
      r: "2.4",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.7"
    }));
  }
  if (type === 'load') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M12 4v10M8.5 10.5L12 14l3.5-3.5",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.8",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), React.createElement("path", {
      d: "M5 18h14",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.8",
      strokeLinecap: "round"
    }));
  }
  if (type === 'pro') {
    return React.createElement("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M5 16.5L7.5 8l4.5 5 4.5-5L19 16.5H5z",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.7",
      strokeLinejoin: "round"
    }));
  }
  return React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: "18",
    height: "18",
    "aria-hidden": "true"
  }, React.createElement("path", {
    d: "M7 3v2M17 3v2M4.5 8h15M6 5.5h12A1.5 1.5 0 0 1 19.5 7v12A1.5 1.5 0 0 1 18 20.5H6A1.5 1.5 0 0 1 4.5 19V7A1.5 1.5 0 0 1 6 5.5z",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "1.75",
    strokeLinejoin: "round"
  }), React.createElement("path", {
    d: "M8 12h2v2H8zm3.5 0h2v2h-2zm3.5 0h2v2h-2zM8 15.5h2v2H8zm3.5 0h2v2h-2z",
    fill: "currentColor"
  }));
};
var CtlToggleSection = function CtlToggleSection(_ref6) {
  var id = _ref6.id,
    label = _ref6.label,
    _ref6$icon = _ref6.icon,
    icon = _ref6$icon === void 0 ? 'year' : _ref6$icon,
    _ref6$badge = _ref6.badge,
    badge = _ref6$badge === void 0 ? null : _ref6$badge,
    _ref6$defaultOpen = _ref6.defaultOpen,
    defaultOpen = _ref6$defaultOpen === void 0 ? false : _ref6$defaultOpen,
    openProp = _ref6.open,
    onToggle = _ref6.onToggle,
    children = _ref6.children;
  var _useState = useState(defaultOpen),
    _useState2 = _slicedToArray(_useState, 2),
    internalOpen = _useState2[0],
    setInternalOpen = _useState2[1];
  var isControlled = typeof openProp === 'boolean';
  var open = isControlled ? openProp : internalOpen;
  var toggle = function toggle() {
    if (isControlled) {
      if (onToggle) {
        onToggle(id);
      }
      return;
    }
    setInternalOpen(function (value) {
      return !value;
    });
  };
  return React.createElement("div", {
    className: 'ctl-sg__acc' + (open ? ' is-open' : '') + (badge ? ' has-pro-badge' : '')
  }, React.createElement("button", {
    type: "button",
    className: "ctl-sg__acc-head",
    "aria-expanded": open,
    onClick: toggle
  }, React.createElement("span", {
    className: "ctl-sg__acc-icon"
  }, React.createElement(AccordionIcon, {
    type: icon
  })), React.createElement("span", {
    className: "ctl-sg__acc-copy"
  }, React.createElement("span", {
    className: "ctl-sg__acc-title-row"
  }, React.createElement("span", {
    className: "ctl-sg__acc-title"
  }, label), badge ? React.createElement("span", {
    className: "ctl-sg__pro-badge"
  }, badge) : null)), React.createElement("span", {
    className: "ctl-sg__acc-chevron",
    "aria-hidden": "true"
  }, React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: "18",
    height: "18"
  }, React.createElement("path", {
    d: "M7 10l5 5 5-5",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  })))), open && React.createElement("div", {
    className: "ctl-sg__acc-body"
  }, children));
};
var CtlAccordionGroup = function CtlAccordionGroup(_ref7) {
  var label = _ref7.label,
    _ref7$defaultOpenId = _ref7.defaultOpenId,
    defaultOpenId = _ref7$defaultOpenId === void 0 ? null : _ref7$defaultOpenId,
    children = _ref7.children;
  var _useState3 = useState(defaultOpenId),
    _useState4 = _slicedToArray(_useState3, 2),
    openId = _useState4[0],
    setOpenId = _useState4[1];
  var onToggle = function onToggle(id) {
    setOpenId(function (current) {
      return current === id ? null : id;
    });
  };
  return React.createElement("div", {
    className: "ctl-sg__acc-group"
  }, label ? React.createElement("div", {
    className: "ctl-sg__acc-group-label"
  }, label) : null, React.createElement("div", {
    className: "ctl-sg__acc-stack"
  }, Children.map(children, function (child) {
    if (!isValidElement(child)) {
      return child;
    }
    var childId = child.props.id || child.props.label;
    return cloneElement(child, {
      id: childId,
      open: openId === childId,
      onToggle: onToggle
    });
  })));
};
var SegmentedControl = function SegmentedControl(_ref8) {
  var options = _ref8.options,
    value = _ref8.value,
    onChange = _ref8.onChange,
    _ref8$className = _ref8.className,
    className = _ref8$className === void 0 ? '' : _ref8$className;
  return React.createElement(ButtonGroup, {
    className: 'ctl-sg__segmented' + (className ? ' ' + className : '')
  }, options.map(function (opt) {
    return React.createElement(Button, {
      key: opt.value,
      isSmall: true,
      className: (value === opt.value ? 'is-active' : '') + (opt.locked ? ' is-locked' : ''),
      onClick: function onClick() {
        if (opt.locked) {
          openGoPro();
          return;
        }
        onChange(opt.value);
      }
    }, opt.label, opt.locked ? React.createElement("span", {
      className: "ctl-sg__lock",
      "aria-hidden": "true"
    }, React.createElement(LockGlyph, null)) : null);
  }));
};
var LayoutIcon = function LayoutIcon(_ref9) {
  var type = _ref9.type;
  if (type === 'horizontal') {
    return React.createElement("svg", {
      viewBox: "0 0 48 36",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M6 18h36",
      stroke: "currentColor",
      strokeWidth: "2",
      fill: "none"
    }), React.createElement("circle", {
      cx: "14",
      cy: "18",
      r: "3",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "24",
      cy: "18",
      r: "3",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "34",
      cy: "18",
      r: "3",
      fill: "currentColor"
    }), React.createElement("rect", {
      x: "10",
      y: "6",
      width: "8",
      height: "6",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }), React.createElement("rect", {
      x: "20",
      y: "24",
      width: "8",
      height: "6",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }), React.createElement("rect", {
      x: "30",
      y: "6",
      width: "8",
      height: "6",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }));
  }
  if (type === 'one-side') {
    return React.createElement("svg", {
      viewBox: "0 0 48 36",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M14 4v28",
      stroke: "currentColor",
      strokeWidth: "2",
      fill: "none"
    }), React.createElement("circle", {
      cx: "14",
      cy: "10",
      r: "3",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "14",
      cy: "20",
      r: "3",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "14",
      cy: "30",
      r: "3",
      fill: "currentColor"
    }), React.createElement("rect", {
      x: "22",
      y: "6",
      width: "16",
      height: "7",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }), React.createElement("rect", {
      x: "22",
      y: "16",
      width: "16",
      height: "7",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }), React.createElement("rect", {
      x: "22",
      y: "26",
      width: "16",
      height: "7",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }));
  }
  if (type === 'compact') {
    return React.createElement("svg", {
      viewBox: "0 0 48 36",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M24 4v28",
      stroke: "currentColor",
      strokeWidth: "2",
      fill: "none"
    }), React.createElement("circle", {
      cx: "24",
      cy: "10",
      r: "2.5",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "24",
      cy: "20",
      r: "2.5",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "24",
      cy: "28",
      r: "2.5",
      fill: "currentColor"
    }), React.createElement("rect", {
      x: "6",
      y: "6",
      width: "12",
      height: "10",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }), React.createElement("rect", {
      x: "30",
      y: "14",
      width: "12",
      height: "10",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.55"
    }));
  }
  if (type === 'simple') {
    return React.createElement("svg", {
      viewBox: "0 0 48 36",
      "aria-hidden": "true"
    }, React.createElement("path", {
      d: "M24 6v24",
      stroke: "currentColor",
      strokeWidth: "2",
      fill: "none"
    }), React.createElement("circle", {
      cx: "24",
      cy: "12",
      r: "2.5",
      fill: "currentColor"
    }), React.createElement("circle", {
      cx: "24",
      cy: "24",
      r: "2.5",
      fill: "currentColor"
    }), React.createElement("rect", {
      x: "8",
      y: "8",
      width: "11",
      height: "8",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.4"
    }), React.createElement("rect", {
      x: "29",
      y: "20",
      width: "11",
      height: "8",
      rx: "1.5",
      fill: "currentColor",
      opacity: "0.4"
    }));
  }
  return React.createElement("svg", {
    viewBox: "0 0 48 36",
    "aria-hidden": "true"
  }, React.createElement("path", {
    d: "M24 4v28",
    stroke: "currentColor",
    strokeWidth: "2",
    fill: "none"
  }), React.createElement("circle", {
    cx: "24",
    cy: "10",
    r: "3",
    fill: "currentColor"
  }), React.createElement("circle", {
    cx: "24",
    cy: "20",
    r: "3",
    fill: "currentColor"
  }), React.createElement("circle", {
    cx: "24",
    cy: "30",
    r: "3",
    fill: "currentColor"
  }), React.createElement("rect", {
    x: "6",
    y: "6",
    width: "12",
    height: "7",
    rx: "1.5",
    fill: "currentColor",
    opacity: "0.55"
  }), React.createElement("rect", {
    x: "30",
    y: "16",
    width: "12",
    height: "7",
    rx: "1.5",
    fill: "currentColor",
    opacity: "0.55"
  }), React.createElement("rect", {
    x: "6",
    y: "26",
    width: "12",
    height: "7",
    rx: "1.5",
    fill: "currentColor",
    opacity: "0.55"
  }));
};
var LayoutCards = function LayoutCards(_ref0) {
  var layouts = _ref0.layouts,
    value = _ref0.value,
    onChange = _ref0.onChange;
  return React.createElement("div", {
    className: "ctl-sg__layout-grid"
  }, layouts.map(function (layout) {
    var isActive = value === layout.value;
    var card = React.createElement("button", {
      type: "button",
      key: layout.value,
      className: 'ctl-sg__layout-card' + (isActive ? ' is-active' : '') + (layout.locked ? ' is-locked' : ''),
      onClick: function onClick() {
        if (layout.locked) {
          openGoPro();
          return;
        }
        onChange(layout.value);
      }
    }, layout.locked ? React.createElement("span", {
      className: "ctl-sg__layout-lock",
      "aria-label": __('Pro')
    }, React.createElement(LockGlyph, {
      size: 12
    })) : null, React.createElement("span", {
      className: "ctl-sg__layout-icon"
    }, React.createElement(LayoutIcon, {
      type: layout.icon || layout.value
    })), React.createElement("span", {
      className: "ctl-sg__layout-name"
    }, layout.label));
    return layout.locked ? React.createElement(Tooltip, {
      key: layout.value,
      text: __('Available in Pro')
    }, card) : card;
  }));
};

/**
 * Locked Pro control row — looks like a Free field but opens upgrade.
 */
var ProLockedField = function ProLockedField(_ref1) {
  var label = _ref1.label,
    hint = _ref1.hint,
    children = _ref1.children;
  return React.createElement("div", {
    className: "ctl-sg__field ctl-sg__field--pro",
    onClick: openGoPro,
    role: "presentation"
  }, React.createElement("div", {
    className: "ctl-sg__pro-field-head"
  }, React.createElement(FieldLabel, null, label), React.createElement("span", {
    className: "ctl-sg__pro-badge"
  }, __('Pro'))), React.createElement("div", {
    className: "ctl-sg__pro-field-control"
  }, children), hint ? React.createElement(FieldHint, null, hint) : null);
};

/**
 * Compact Pro feature list + CTA used inside upsell accordions.
 */
var ProUpsell = function ProUpsell(_ref10) {
  var title = _ref10.title,
    _ref10$features = _ref10.features,
    features = _ref10$features === void 0 ? [] : _ref10$features;
  return React.createElement("div", {
    className: "ctl-sg__pro-upsell"
  }, title ? React.createElement("p", {
    className: "ctl-sg__pro-upsell-title"
  }, title) : null, features.length > 0 ? React.createElement("ul", {
    className: "ctl-sg__pro-feature-list"
  }, features.map(function (feature) {
    return React.createElement("li", {
      key: feature
    }, React.createElement("span", {
      className: "ctl-sg__pro-check",
      "aria-hidden": "true"
    }, React.createElement("svg", {
      viewBox: "0 0 16 16",
      width: "12",
      height: "12"
    }, React.createElement("path", {
      d: "M3.5 8.5l3 3 6-7",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "1.8",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }))), feature);
  })) : null, React.createElement("button", {
    type: "button",
    className: "ctl-sg__pro-cta",
    onClick: openGoPro
  }, __('Unlock with Pro'), React.createElement("span", {
    "aria-hidden": "true"
  }, " \u2192")));
};

/**
 * Always-visible Pro marketing card with option chips (Design / Skin teasers).
 */
var ProMarketingBox = function ProMarketingBox(_ref11) {
  var _ref11$badge = _ref11.badge,
    badge = _ref11$badge === void 0 ? __('Pro') : _ref11$badge,
    title = _ref11.title,
    description = _ref11.description,
    _ref11$options = _ref11.options,
    options = _ref11$options === void 0 ? [] : _ref11$options;
  return React.createElement("div", {
    className: "ctl-sg__pro-box"
  }, React.createElement("div", {
    className: "ctl-sg__pro-box-head"
  }, React.createElement("span", {
    className: "ctl-sg__pro-badge"
  }, badge), React.createElement("strong", {
    className: "ctl-sg__pro-box-title"
  }, title)), description ? React.createElement("p", {
    className: "ctl-sg__pro-box-desc"
  }, description) : null, options.length > 0 ? React.createElement("div", {
    className: "ctl-sg__pro-chip-grid"
  }, options.map(function (opt) {
    return React.createElement("button", {
      type: "button",
      key: opt.value || opt.label,
      className: "ctl-sg__pro-chip",
      onClick: openGoPro
    }, React.createElement("span", null, opt.label), React.createElement("span", {
      className: "ctl-sg__lock",
      "aria-hidden": "true"
    }, React.createElement("svg", {
      viewBox: "0 0 16 16",
      width: "10",
      height: "10"
    }, React.createElement("path", {
      fill: "currentColor",
      d: "M4.5 7V5.2a3.5 3.5 0 0 1 7 0V7H13a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1.5zm1.5 0h4V5.2a2 2 0 1 0-4 0V7z"
    }))));
  })) : null, React.createElement("button", {
    type: "button",
    className: "ctl-sg__pro-cta",
    onClick: openGoPro
  }, __('Unlock with Pro'), React.createElement("span", {
    "aria-hidden": "true"
  }, " \u2192")));
};
var CtlSettingsShell = function CtlSettingsShell(_ref12) {
  var _ref12$title = _ref12.title,
    title = _ref12$title === void 0 ? __('Cool timeline') : _ref12$title,
    _ref12$subtitle = _ref12.subtitle,
    subtitle = _ref12$subtitle === void 0 ? __('Shortcode generator') : _ref12$subtitle,
    _ref12$initialTabName = _ref12.initialTabName,
    initialTabName = _ref12$initialTabName === void 0 ? 'layout' : _ref12$initialTabName,
    tabs = _ref12.tabs;
  var storageKey = 'ctlFreeSgReviewOpen';
  var _useState5 = useState(function () {
      try {
        var stored = window.localStorage.getItem(storageKey);
        if (stored === '0') {
          return false;
        }
        if (stored === '1') {
          return true;
        }
      } catch (e) {
        // Ignore storage errors.
      }
      return false;
    }),
    _useState6 = _slicedToArray(_useState5, 2),
    reviewOpen = _useState6[0],
    setReviewOpen = _useState6[1];
  var toggleReview = function toggleReview() {
    setReviewOpen(function (open) {
      var next = !open;
      try {
        window.localStorage.setItem(storageKey, next ? '1' : '0');
      } catch (e) {
        // Ignore storage errors.
      }
      return next;
    });
  };
  return React.createElement("div", {
    className: "ctl-sg"
  }, React.createElement("div", {
    className: "ctl-sg__header"
  }, React.createElement("span", {
    className: "ctl-sg__brand-icon",
    "aria-hidden": "true"
  }, React.createElement(_icons__WEBPACK_IMPORTED_MODULE_0__["default"], null)), React.createElement("div", {
    className: "ctl-sg__brand-text"
  }, React.createElement("strong", null, title), React.createElement("span", null, subtitle))), React.createElement(TabPanel, {
    className: "ctl-sg__tabs",
    activeClass: "is-active",
    initialTabName: initialTabName,
    tabs: tabs
  }, function (tab) {
    return React.createElement("div", {
      className: "ctl-sg__body"
    }, tab.content);
  }), React.createElement("div", {
    className: 'ctl-sg__review' + (reviewOpen ? ' is-open' : '')
  }, React.createElement("button", {
    type: "button",
    className: "ctl-sg__review-toggle",
    "aria-expanded": reviewOpen,
    onClick: toggleReview
  }, React.createElement("span", {
    className: "ctl-sg__review-toggle-main"
  }, React.createElement(ReviewStars, null), React.createElement("span", {
    className: "ctl-sg__review-toggle-label"
  }, __('Enjoying Cool Timeline?'))), React.createElement("span", {
    className: "ctl-sg__review-chevron",
    "aria-hidden": "true"
  }, React.createElement("svg", {
    viewBox: "0 0 24 24",
    width: "16",
    height: "16"
  }, React.createElement("path", {
    d: "M7 10l5 5 5-5",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  })))), reviewOpen && React.createElement("div", {
    className: "ctl-sg__review-body"
  }, React.createElement("p", {
    className: "ctl-sg__review-copy"
  }, __('A quick review helps others discover Cool Timeline.')), React.createElement("div", {
    className: "ctl-sg__review-actions"
  }, React.createElement("span", {
    className: "ctl-sg__review-meta"
  }, __('4.7 · 300+ reviews')), React.createElement(ExternalLink, {
    className: "ctl-sg__review-link",
    href: REVIEW_URL
  }, __('Leave a review'))))));
};

/**
 * Label on its own row; Reset sits beside the range control (not the label).
 */
var RangeWithReset = function RangeWithReset(_ref13) {
  var label = _ref13.label,
    hint = _ref13.hint,
    value = _ref13.value,
    onChange = _ref13.onChange,
    onReset = _ref13.onReset,
    isResetDisabled = _ref13.isResetDisabled,
    min = _ref13.min,
    max = _ref13.max,
    _ref13$step = _ref13.step,
    step = _ref13$step === void 0 ? 1 : _ref13$step;
  return React.createElement("div", {
    className: "ctl-sg__field"
  }, label ? React.createElement(FieldLabel, null, label) : null, React.createElement("div", {
    className: "ctl-sg__range-option"
  }, React.createElement("div", {
    className: "ctl-sg__range-option__control"
  }, React.createElement(RangeControl, {
    __next40pxDefaultSize: true,
    value: value,
    onChange: onChange,
    min: min,
    max: max,
    step: step
  })), React.createElement(Button, {
    isSmall: true,
    isSecondary: true,
    className: "ctl-sg__range-option__reset",
    onClick: onReset,
    disabled: isResetDisabled
  }, __('Reset'))), hint ? React.createElement(FieldHint, null, hint) : null);
};

/**
 * Story Content → Title fields (Story + Content shortcode blocks).
 */
var StoryTitleSettingsFields = function StoryTitleSettingsFields(_ref14) {
  var attributes = _ref14.attributes,
    setAttributes = _ref14.setAttributes;
  return /*#__PURE__*/React.createElement(React.Fragment, null, React.createElement("div", {
    className: "ctl-sg__field"
  }, React.createElement(FieldLabel, null, __('Title')), React.createElement(SegmentedControl, {
    options: [{
      label: __('Show'),
      value: 'show'
    }, {
      label: __('Hide'),
      value: 'hide'
    }],
    value: attributes.showTitle || 'show',
    onChange: function onChange(value) {
      return setAttributes({
        showTitle: value
      });
    }
  }), React.createElement(FieldHint, null, __('Show or hide the story title.'))), 'show' === (attributes.showTitle || 'show') && React.createElement("div", {
    className: "ctl-sg__field"
  }, React.createElement(FieldLabel, null, __('Title Link')), React.createElement(SegmentedControl, {
    options: [{
      label: __('Enable'),
      value: 'enable'
    }, {
      label: __('Disable'),
      value: 'disable'
    }],
    value: attributes.titleLink || 'enable',
    onChange: function onChange(value) {
      return setAttributes({
        titleLink: value
      });
    }
  }), React.createElement(FieldHint, null, __('Make the story title clickable.'))));
};

/**
 * Story Content → Read More fields (Story + Content shortcode blocks).
 */
var ReadMoreSettingsFields = function ReadMoreSettingsFields(_ref15) {
  var attributes = _ref15.attributes,
    setAttributes = _ref15.setAttributes;
  return /*#__PURE__*/React.createElement(React.Fragment, null, React.createElement("div", {
    className: "ctl-sg__field"
  }, React.createElement(FieldLabel, null, __('Read More')), React.createElement(SegmentedControl, {
    options: [{
      label: __('Show'),
      value: 'show'
    }, {
      label: __('Hide'),
      value: 'hide'
    }],
    value: attributes.readMoreVisibility || 'show',
    onChange: function onChange(value) {
      return setAttributes({
        readMoreVisibility: value
      });
    }
  }), React.createElement(FieldHint, null, __('Adds a link to open the full story.'))), 'show' === (attributes.readMoreVisibility || 'show') && /*#__PURE__*/React.createElement(React.Fragment, null, React.createElement("div", {
    className: "ctl-sg__field"
  }, React.createElement(FieldLabel, null, __('Read More Text')), React.createElement(TextControl, {
    __next40pxDefaultSize: true,
    value: attributes.readMoreText || '',
    placeholder: __('Read More'),
    onChange: function onChange(value) {
      return setAttributes({
        readMoreText: value
      });
    }
  }), React.createElement(FieldHint, null, __('Custom label for the Read More.'))), React.createElement("div", {
    className: "ctl-sg__field"
  }, React.createElement(FieldLabel, null, __('Open Read More in')), React.createElement(SegmentedControl, {
    options: [{
      label: __('Same Tab'),
      value: '_self'
    }, {
      label: __('New Tab'),
      value: '_blank'
    }],
    value: attributes.readMoreTarget || '_self',
    onChange: function onChange(value) {
      return setAttributes({
        readMoreTarget: value
      });
    }
  }))));
};

/***/ },

/***/ "./src/ssr-preview.js"
/*!****************************!*\
  !*** ./src/ssr-preview.js ***!
  \****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CtlEmptyStoryNotice: () => (/* binding */ CtlEmptyStoryNotice),
/* harmony export */   CtlSsrLoadingPlaceholder: () => (/* binding */ CtlSsrLoadingPlaceholder),
/* harmony export */   getCtlSsrAttributes: () => (/* binding */ getCtlSsrAttributes),
/* harmony export */   useCtlEmptySsrDetector: () => (/* binding */ useCtlEmptySsrDetector)
/* harmony export */ });
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./icons */ "./src/icons.js");
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/**
 * Shared ServerSideRender helpers for timeline blocks.
 */
;
var _wp$components = wp.components,
  Spinner = _wp$components.Spinner,
  Button = _wp$components.Button;
var _wp$element = wp.element,
  useEffect = _wp$element.useEffect,
  useState = _wp$element.useState;
var __ = wp.i18n.__;
var SSR_OMIT_KEYS = ['isPreview'];

/**
 * Drop editor-only attributes so SSR requests stay smaller and re-fetch less often.
 * Coerce numbers to strings to match PHP attribute schemas (POST is strict; GET was not).
 *
 * @param {Object} attributes Block attributes.
 * @return {Object} Attributes safe to send to block-renderer.
 */
function getCtlSsrAttributes(attributes) {
  var ssrAttributes = {};
  Object.keys(attributes || {}).forEach(function (key) {
    if (SSR_OMIT_KEYS.includes(key)) {
      return;
    }
    var value = attributes[key];
    if (typeof value === 'number' && !Number.isNaN(value)) {
      ssrAttributes[key] = String(value);
    } else {
      ssrAttributes[key] = value;
    }
  });
  return ssrAttributes;
}

/**
 * Keep the previous preview visible while a new SSR request is in flight.
 *
 * @param {Object} props
 * @param {*} props.children Previous rendered markup from ServerSideRender.
 * @return {JSX.Element}
 */
function CtlSsrLoadingPlaceholder(_ref) {
  var children = _ref.children;
  return React.createElement("div", {
    className: "ctl-block-ssr-loading",
    style: {
      position: 'relative'
    }
  }, children ? React.createElement("div", {
    className: "ctl-block-ssr-loading__content",
    style: {
      opacity: 0.55
    }
  }, children) : React.createElement("div", {
    style: {
      padding: '16px',
      display: 'flex',
      justifyContent: 'center'
    }
  }, React.createElement(Spinner, null)));
}

/**
 * Watches the SSR container for the PHP "no-content" empty state and reports
 * it back, since ServerSideRender injects raw HTML with no callback hook.
 *
 * @param {Object} containerRef Ref on the element wrapping ServerSideRender.
 * @return {boolean} Whether the current SSR response is the empty state.
 */
function useCtlEmptySsrDetector(containerRef) {
  var _useState = useState(false),
    _useState2 = _slicedToArray(_useState, 2),
    isEmpty = _useState2[0],
    setIsEmpty = _useState2[1];
  useEffect(function () {
    var node = containerRef.current;
    if (!node) {
      return;
    }
    var checkEmpty = function checkEmpty() {
      return setIsEmpty(!!node.querySelector('.no-content'));
    };
    checkEmpty();
    var observer = new MutationObserver(checkEmpty);
    observer.observe(node, {
      childList: true,
      subtree: true
    });
    return function () {
      return observer.disconnect();
    };
  }, [containerRef]);
  return isEmpty;
}

// One increment per bucket per editor page load — a reload starts a fresh set.
var trackedOnboardingBuckets = new Set();

/**
 * Best-effort ping of the onboarding framework's telemetry endpoint (see
 * ctlOnboardingTrack localized in ctl-block.php). Silently no-ops if unavailable.
 *
 * @param {string} event  Telemetry event name.
 * @param {string} bucket Bucket to increment under that event.
 */
function trackOnboardingEvent(event, bucket) {
  if (typeof ctlOnboardingTrack === 'undefined') {
    return;
  }
  var dedupeKey = event + '.' + bucket;
  if (trackedOnboardingBuckets.has(dedupeKey)) {
    return;
  }
  trackedOnboardingBuckets.add(dedupeKey);
  try {
    var body = new FormData();
    body.append('action', ctlOnboardingTrack.action);
    body.append('nonce', ctlOnboardingTrack.nonce);
    body.append('event', event);
    body.append('bucket', bucket);
    fetch(ctlOnboardingTrack.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body
    });
  } catch (e) {
    // Telemetry is non-critical; swallow any failure.
  }
}

/**
 * Editor-only nudge shown when a query-based timeline block has no stories
 * to preview: offers to insert the InnerBlocks-based Cool Timeline Block.
 *
 * @param {Object}  props
 * @param {string}  props.clientId          Client ID of the current block.
 * @param {string}  props.description       Block-specific line explaining the alternative.
 * @param {boolean} [props.showCreateStory] Whether to also offer a "Create New Story" link.
 * @return {JSX.Element}
 */
function CtlEmptyStoryNotice(_ref2) {
  var clientId = _ref2.clientId,
    description = _ref2.description,
    _ref2$showCreateStory = _ref2.showCreateStory,
    showCreateStory = _ref2$showCreateStory === void 0 ? false : _ref2$showCreateStory;
  var insertContentTimelineBlock = function insertContentTimelineBlock() {
    var createBlock = wp.blocks.createBlock;
    var _wp$data$select = wp.data.select('core/block-editor'),
      getBlockRootClientId = _wp$data$select.getBlockRootClientId,
      getBlockIndex = _wp$data$select.getBlockIndex;
    var _wp$data$dispatch = wp.data.dispatch('core/block-editor'),
      insertBlock = _wp$data$dispatch.insertBlock;
    var rootClientId = getBlockRootClientId(clientId);
    var index = getBlockIndex(clientId, rootClientId) + 1;
    insertBlock(createBlock('cp-timeline/content-timeline'), index, rootClientId, true);
    trackOnboardingEvent('cta_clicked', 'empty-state-insert-block');
  };
  var adminURL = typeof ctlUrl !== 'undefined' && ctlUrl.adminURL ? ctlUrl.adminURL : '/wp-admin/';
  return React.createElement("div", {
    className: "ctl-block-empty-promo"
  }, React.createElement("span", {
    className: "ctl-block-empty-promo__icon",
    "aria-hidden": "true"
  }, React.createElement(_icons__WEBPACK_IMPORTED_MODULE_0__["default"], null)), React.createElement("div", {
    className: "ctl-block-empty-promo__body"
  }, React.createElement("p", {
    className: "ctl-block-empty-promo__title"
  }, __('Sorry, you have not added any story yet')), React.createElement("p", {
    className: "ctl-block-empty-promo__text"
  }, description), React.createElement("div", {
    className: "ctl-block-empty-promo__actions"
  }, React.createElement(Button, {
    variant: "primary",
    icon: "insert",
    onClick: insertContentTimelineBlock
  }, __('Insert Cool Timeline Block')), showCreateStory && React.createElement(Button, {
    variant: "tertiary",
    icon: "plus-alt2",
    onClick: function onClick() {
      window.open(adminURL + 'post-new.php?post_type=cool_timeline', '_blank', 'noopener,noreferrer');
      trackOnboardingEvent('cta_clicked', 'empty-state-create-story');
    }
  }, __('Create New Story')))));
}

/***/ },

/***/ "./src/images/timeline.png"
/*!*********************************!*\
  !*** ./src/images/timeline.png ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__webpack_require__.p + "images/27d3f3c782dabdb08d424daa7396b72e.png");

/***/ },

/***/ "./src/style.scss"
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/publicPath */
/******/ 	(() => {
/******/ 		let scriptUrl;
/******/ 		if (__webpack_require__.g.importScripts) scriptUrl = __webpack_require__.g.location + "";
/******/ 		const document = __webpack_require__.g.document;
/******/ 		if (!scriptUrl && document) {
/******/ 			if (document.currentScript?.tagName.toUpperCase() === 'SCRIPT')
/******/ 				scriptUrl = document.currentScript.src;
/******/ 			if (!scriptUrl) {
/******/ 				const scripts = document.getElementsByTagName("script");
/******/ 				if(scripts.length) {
/******/ 					let i = scripts.length - 1;
/******/ 					while (i > -1 && (!scriptUrl || !/^http(s?):/.test(scriptUrl))) scriptUrl = scripts[i--].src;
/******/ 				}
/******/ 			}
/******/ 		}
/******/ 		// When supporting browsers where an automatic publicPath is not supported you must specify an output.publicPath manually via configuration
/******/ 		// or pass an empty string ("") and set the __webpack_public_path__ variable from your code to use your own logic.
/******/ 		if (!scriptUrl) throw new Error("Automatic publicPath is not supported in this browser");
/******/ 		scriptUrl = scriptUrl.replace(/^blob:/, "").replace(/#.*$/, "").replace(/\?.*$/, "").replace(/\/[^\/]+$/, "/");
/******/ 		__webpack_require__.p = scriptUrl + "../";
/******/ 	})();
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _block_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block.js */ "./src/block.js");
// Include stylesheet


// Import Click to Tweet Block

})();

/******/ })()
;
//# sourceMappingURL=block.js.map