/**
 * Re-init timeline JS after Gutenberg ServerSideRender injects HTML.
 * - Compact: Masonry + left/right classification
 * - Horizontal: Swiper (desktop slidesPerView so narrow canvas matches frontend)
 */
( function () {
	// The parent editor page and the canvas iframe both enqueue this file, and
	// each window needs its own copy because jQuery/Masonry/Swiper are per
	// document. Guard so a double enqueue in one window cannot attach two
	// observers.
	if ( window.ctlEditorSsrInit ) {
		return;
	}
	window.ctlEditorSsrInit = true;

	var timer = null;
	var observedDocs = [];
	var docSyncTimer = null;
	var DOC_SYNC_INTERVAL = 1000;

	/**
	 * WordPress injects block styles into the iframed canvas but runs scripts in
	 * the parent, so `document` here is usually the editor page and the timeline
	 * markup is inside the canvas iframe. Collect every same-origin document so
	 * the markup is found wherever it actually lives.
	 */
	function getRuntimeDocuments() {
		var docs = [ document ];
		var iframes = document.querySelectorAll( 'iframe' );
		Array.prototype.forEach.call( iframes, function ( iframe ) {
			try {
				if ( iframe.contentDocument && iframe.contentDocument.body ) {
					docs.push( iframe.contentDocument );
				}
			} catch ( err ) {
				// Cross-origin iframe; ignore.
			}
		} );
		return docs;
	}

	/**
	 * jQuery and Masonry live on the window that owns the nodes. Running the
	 * parent's copy against iframe nodes hides the instance from the iframe, so
	 * compact never packs and the stories fall back to the plain float stack -
	 * which reads as the vertical both-sided layout.
	 */
	function getDocJQuery( rootDoc ) {
		try {
			var win = rootDoc && rootDoc.defaultView;
			if ( win && win.jQuery && typeof win.jQuery.fn.masonry === 'function' ) {
				return win.jQuery;
			}
		} catch ( err ) {
			// Ignore.
		}
		if ( typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.masonry === 'function' ) {
			return window.jQuery;
		}
		return typeof window.jQuery !== 'undefined' ? window.jQuery : null;
	}

	function getDocSwiper( rootDoc ) {
		try {
			var win = rootDoc && rootDoc.defaultView;
			if ( win && win.Swiper ) {
				return win.Swiper;
			}
		} catch ( err ) {
			// Ignore.
		}
		return window.Swiper;
	}

	function applyImportantPosition( element, rootDoc ) {
		if ( ! element || ! element.style ) {
			return;
		}
		// Mobile compact is a one-column stack seated at `left: 4%` in CSS.
		// Masonry still writes left:0; promoting that to !important parked
		// stories (and their icons) left of the rail while the line stayed at 4%.
		var view = ( rootDoc && rootDoc.defaultView ) || window;
		var isMobile =
			view &&
			typeof view.matchMedia === 'function' &&
			view.matchMedia( '(max-width: 767px)' ).matches;
		if ( isMobile ) {
			element.style.removeProperty( 'left' );
		} else if ( element.style.left ) {
			element.style.setProperty( 'left', element.style.left, 'important' );
		}
		if ( element.style.top ) {
			element.style.setProperty( 'top', element.style.top, 'important' );
		}
		if ( element.style.position ) {
			element.style.setProperty( 'position', element.style.position, 'important' );
		}
	}

	function reinitCompact( rootDoc ) {
		var $ = getDocJQuery( rootDoc );
		if ( ! $ ) {
			return;
		}
		var grids = $( '.ctl-compact-wrapper .ctl-timeline-container', rootDoc );
		if ( ! grids.length || typeof grids.masonry !== 'function' ) {
			return;
		}

		grids.each( function () {
			var $grid = $( this );
			if ( $grid.data( 'masonry' ) ) {
				$grid.masonry( 'destroy' );
			}
		} );

		var leftReminder = 0;
		var rightReminder = 0;
		var grid = grids.masonry( {
			itemSelector: '.ctl-story',
			initLayout: false,
		} );

		var finalize = function () {
			leftReminder = 0;
			rightReminder = 0;
			grid.find( '.ctl-story' ).each( function ( index, element ) {
				applyImportantPosition( element, rootDoc );

				var leftPos = parseFloat( element.style.left );
				if ( Number.isNaN( leftPos ) ) {
					leftPos = element.offsetLeft;
				}
				if ( leftPos <= 0 ) {
					var leftCls = leftReminder % 2 === 0 ? 'ctl-left-odd' : 'ctl-left-even';
					var leftPrev = leftCls === 'ctl-left-odd' ? 'ctl-left-even' : 'ctl-left-odd';
					$( element )
						.removeClass( 'ctl-story-right ctl-right-even ctl-right-odd ' + leftPrev )
						.addClass( 'ctl-story-left ' + leftCls );
					leftReminder++;
				} else {
					var rightCls = rightReminder % 2 === 0 ? 'ctl-right-odd' : 'ctl-right-even';
					var rightPrev = rightCls === 'ctl-right-odd' ? 'ctl-right-even' : 'ctl-right-odd';
					$( element )
						.removeClass( 'ctl-story-left ctl-left-odd ctl-left-even ' + rightPrev )
						.addClass( 'ctl-story-right ' + rightCls );
					rightReminder++;
				}

				var prev = $( element ).prev();
				if ( ! prev.length ) {
					return;
				}
				var topPosDiff =
					( parseFloat( element.style.top ) || 0 ) -
					( parseFloat( prev[ 0 ].style.top ) || 0 );
				var iconWrp = element.querySelector( '.ctl-icon,.ctl-icondot' );
				if ( ! iconWrp ) {
					return;
				}
				var expectedSize = iconWrp.offsetHeight + 12;
				if ( topPosDiff < expectedSize ) {
					var gapSize = expectedSize - topPosDiff - 5;
					$( element )
						.removeClass( 'ctl-compact-up' )
						.addClass( 'ctl-compact-down' )
						.css( '--ctw-compact-top-spacing', gapSize + 'px' );
					prev.removeClass( 'ctl-compact-down' ).addClass( 'ctl-compact-up' );
				}
			} );
			$( '.ctl-compact-wrapper .ctl-icon' ).addClass( 'showit' );
			$( '.ctl-compact-wrapper .ctl-title' ).addClass( 'showit-after' );
		};

		var runLayout = function () {
			grid.masonry( 'layout' );
			setTimeout( finalize, 0 );
		};

		grid.on( 'layoutComplete.ctlEditor', finalize );

		// Always schedule delayed layouts — editor SSR + cached images can finish
		// before desktop compact widths apply, leaving a one-column stack.
		runLayout();
		setTimeout( runLayout, 150 );
		setTimeout( runLayout, 500 );
		setTimeout( runLayout, 1200 );

		if ( typeof grid.imagesLoaded === 'function' ) {
			grid.imagesLoaded().progress( runLayout ).always( runLayout );
		}
	}

	function parseItems( $wrap ) {
		var raw = $wrap.attr( 'data-items' );
		if ( raw === undefined || raw === null || raw === '' ) {
			return 4;
		}
		var parsed = parseInt( raw, 10 );
		return Number.isNaN( parsed ) || parsed < 1 ? 4 : parsed;
	}

	function reinitHorizontal( rootDoc ) {
		var $ = getDocJQuery( rootDoc );
		var SwiperCtor = getDocSwiper( rootDoc );
		if ( ! $ || typeof SwiperCtor === 'undefined' ) {
			return;
		}

		$( '.ctl-horizontal-wrapper', rootDoc ).each( function () {
			var $wrap = $( this );
			var el = $wrap.find( '.ctl-slider-container' )[ 0 ];
			if ( ! el ) {
				return;
			}

			var showSlides = parseItems( $wrap );
			var nextButton = $wrap.find( '.ctl-button-next' )[ 0 ];
			var prevButton = $wrap.find( '.ctl-button-prev' )[ 0 ];
			var slideCount = $wrap.find( '.ctl-slider-container .swiper-slide' ).length;

			// Avoid destroy/recreate loops from Swiper's own DOM mutations.
			if (
				el.swiper &&
				el.swiper.params &&
				el.swiper.params.slidesPerView === showSlides &&
				el.swiper.slides &&
				el.swiper.slides.length === slideCount
			) {
				if ( typeof el.swiper.updateAutoHeight === 'function' ) {
					el.swiper.updateAutoHeight( 0 );
				}
				return;
			}

			if ( el.swiper ) {
				el.swiper.destroy( true, true );
			}

			// Editor canvas is often <1024px; use desktop slidesPerView so preview matches frontend.
			var attribute = {
				slidesPerGroup: 1,
				slidesPerView: showSlides,
				observer: true,
				observeParents: true,
				watchOverflow: true,
				autoHeight: true,
			};

			if ( nextButton && prevButton ) {
				attribute.navigation = {
					nextEl: nextButton,
					prevEl: prevButton,
				};
			}

			var lineFill = $wrap.data( 'line-filling' )
				? $wrap.find( '.ctl-line-fill' )[ 0 ]
				: null;
			if ( lineFill ) {
				attribute.pagination = {
					el: lineFill,
					type: 'progressbar',
				};
			}

			// eslint-disable-next-line no-new
			new SwiperCtor( el, attribute );

			var updateHeight = function () {
				if ( el.swiper && typeof el.swiper.updateAutoHeight === 'function' ) {
					el.swiper.updateAutoHeight( 0 );
					// autoHeight ignores slide margin-top (date chips above the line).
					var activeSlide =
						el.querySelector( '.swiper-slide-active' ) || el.querySelector( '.swiper-slide' );
					if ( activeSlide && el.swiper.height ) {
						var marginTop = parseFloat( window.getComputedStyle( activeSlide ).marginTop ) || 0;
						if ( marginTop > 0 ) {
							el.style.setProperty( 'height', el.swiper.height + marginTop + 'px', 'important' );
						}
					}
				} else if ( el.swiper && typeof el.swiper.update === 'function' ) {
					el.swiper.update();
				}
			};
			updateHeight();
			if ( typeof $wrap.imagesLoaded === 'function' ) {
				$wrap.imagesLoaded().always( updateHeight );
			} else {
				setTimeout( updateHeight, 300 );
				setTimeout( updateHeight, 1000 );
			}
		} );
	}

	function docHasTimeline( rootDoc ) {
		try {
			return !! (
				rootDoc.querySelector( '.ctl-compact-wrapper .ctl-timeline-container' ) ||
				rootDoc.querySelector( '.ctl-horizontal-wrapper .ctl-slider-container' )
			);
		} catch ( err ) {
			return false;
		}
	}

	function reinitAll() {
		getRuntimeDocuments().forEach( function ( rootDoc ) {
			try {
				if ( rootDoc.querySelector( '.ctl-compact-wrapper .ctl-timeline-container' ) ) {
					reinitCompact( rootDoc );
				}
				if ( rootDoc.querySelector( '.ctl-horizontal-wrapper .ctl-slider-container' ) ) {
					reinitHorizontal( rootDoc );
				}
			} catch ( err ) {
				// Document went away mid-pass (canvas swap); the next tick picks it up.
			}
		} );
	}

	function scheduleReinit() {
		if ( ! getRuntimeDocuments().some( docHasTimeline ) ) {
			return;
		}
		clearTimeout( timer );
		timer = setTimeout( reinitAll, 80 );
	}

	function observeDoc( rootDoc ) {
		if ( ! rootDoc || ! rootDoc.body || observedDocs.indexOf( rootDoc ) !== -1 ) {
			return;
		}
		observedDocs.push( rootDoc );
		var observer = new MutationObserver( scheduleReinit );
		observer.observe( rootDoc.body, { childList: true, subtree: true } );
		if ( rootDoc.defaultView ) {
			rootDoc.defaultView.addEventListener( 'resize', scheduleReinit );
		}
	}

	/**
	 * The canvas iframe is created after this script runs, and is replaced
	 * whenever the editor remounts it, so poll for documents nothing is
	 * observing yet rather than assuming the set is fixed at start-up.
	 */
	function syncDocuments() {
		var docs = getRuntimeDocuments();
		var before = observedDocs.length;
		docs.forEach( observeDoc );
		if ( observedDocs.length !== before ) {
			scheduleReinit();
		}
	}

	function start() {
		if ( ! document.body ) {
			return;
		}
		syncDocuments();
		scheduleReinit();
		clearInterval( docSyncTimer );
		docSyncTimer = setInterval( syncDocuments, DOC_SYNC_INTERVAL );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
