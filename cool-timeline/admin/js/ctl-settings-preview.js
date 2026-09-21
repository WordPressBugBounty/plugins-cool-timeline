( function( $ ) {
	'use strict';

	var optionPrefix = 'cool_timeline_settings';
	var fontRequests = {};
	var fieldCache = {};

	function getField( selector ) {
		if ( ! fieldCache[ selector ] ) {
			fieldCache[ selector ] = $( selector );
		}

		return fieldCache[ selector ];
	}

	function option( key, fallback ) {
		var $field = getField( '[name="' + optionPrefix + '[' + key + ']"]' );
		var value = '';

		if ( $field.is( ':radio' ) ) {
			value = $field.filter( ':checked' ).val();
		} else if ( $field.length ) {
			value = $field.val();
		}

		return value === undefined || value === null || value === '' ? fallback : value;
	}

	function typographyOption( group, key, fallback ) {
		var $field = getField( '[name="' + optionPrefix + '[' + group + '][' + key + ']"]' );
		var value = $field.length ? $field.val() : '';

		return value === undefined || value === null || value === '' ? fallback : value;
	}

	function requestGoogleFont( family, weight ) {
		var request;

		if ( ! family || fontRequests[ family + weight ] || typeof window.WebFont !== 'object' ) {
			return;
		}

		request = family + ( weight ? ':' + weight : '' );
		fontRequests[ family + weight ] = true;
		window.WebFont.load( { google: { families: [ request ] } } );
	}

	function applyTypography( $elements, group, defaults ) {
		var family = typographyOption( group, 'font-family', defaults.family );
		var backup = typographyOption( group, 'backup-font-family', defaults.backup );
		var weight = typographyOption( group, 'font-weight', defaults.weight );
		var style = typographyOption( group, 'font-style', 'normal' );
		var size = typographyOption( group, 'font-size', defaults.size );
		var lineHeight = typographyOption( group, 'line-height', defaults.lineHeight );
		var textTransform = typographyOption( group, 'text-transform', '' );
		var letterSpacing = typographyOption( group, 'letter-spacing', '' );
		var textAlign = typographyOption( group, 'text-align', '' );
		var unit = typographyOption( group, 'unit', 'px' );
		var fontStack = family;
		var styles;

		if ( backup ) {
			fontStack += ', ' + backup;
		}

		// CSF "Default" stores an empty string — treat that as none so a prior
		// lowercase/uppercase choice does not stick on the live preview.
		styles = {
			'font-family': fontStack,
			'font-size': size ? size + unit : '',
			'font-style': style,
			'font-weight': weight,
			'line-height': lineHeight ? lineHeight + unit : '',
			'text-transform': textTransform || 'none',
			'letter-spacing': letterSpacing !== '' ? letterSpacing + unit : '',
			'text-align': textAlign || ''
		};

		$elements.css( styles );

		if ( typographyOption( group, 'type', '' ) === 'google' ) {
			requestGoogleFont( family, weight );
		}
	}

	function updatePreview() {
		var $previews = $( '.ctl-settings-preview' ).filter( ':visible' );
		var circleColor;

		if ( ! $previews.length ) {
			return;
		}

		// Free Style Settings expose a smaller color set — map what exists, keep Pro-like defaults for the rest.
		circleColor = option( 'circle_border_color', '#38aab7' );

		$previews.each( function() {
			var $preview = $( this );
			var startsFromRight = option( 'first_story_position', 'right' ) === 'right';

			$preview.toggleClass( 'ctl-settings-preview--starts-right', startsFromRight );

			this.style.setProperty( '--ctl-preview-background', option( 'content_bg_color', '#ffffff' ) );
			this.style.setProperty( '--ctl-preview-content', '#666666' );
			this.style.setProperty( '--ctl-preview-caption', '#ffffff' );
			this.style.setProperty( '--ctl-preview-title', '#ffffff' );
			this.style.setProperty( '--ctl-preview-year-background', circleColor );
			this.style.setProperty( '--ctl-preview-year-color', '#ffffff' );
			this.style.setProperty( '--ctl-preview-line', option( 'line_color', '#025149' ) );
			this.style.setProperty( '--ctl-preview-line-fill', circleColor );
			this.style.setProperty( '--ctl-preview-first', option( 'first_post', '#29b246' ) );
			this.style.setProperty( '--ctl-preview-second', option( 'second_post', '#ce792f' ) );
			/* Dates follow alternating story colors (same as front-end .ctl-label-big). */
			this.style.setProperty( '--ctl-preview-date', option( 'first_post', '#29b246' ) );
			this.style.setProperty( '--ctl-preview-read-more-radius', '10px' );

			applyTypography( $preview.find( '.ctl-settings-preview__date' ), 'ctl_date_typo', {
				family: 'Maven Pro', backup: 'sans-serif', weight: '700', size: '21', lineHeight: ''
			} );
			applyTypography( $preview.find( '.ctl-settings-preview__card h4' ), 'post_title_typo', {
				family: 'Maven Pro', backup: 'sans-serif', weight: '700', size: '20', lineHeight: ''
			} );
			applyTypography( $preview.find( '.ctl-settings-preview__content' ), 'post_content_typo', {
				family: 'Maven Pro', backup: 'sans-serif', weight: '400', size: '16', lineHeight: ''
			} );
			applyTypography( $preview.find( '.ctl-settings-preview__caption' ), 'post_content_typo', {
				family: 'Maven Pro', backup: 'sans-serif', weight: '400', size: '16', lineHeight: ''
			} );
		} );
	}

	function scheduleUpdate() {
		if ( scheduleUpdate.pending ) {
			return;
		}

		scheduleUpdate.pending = true;
		window.requestAnimationFrame( function() {
			scheduleUpdate.pending = false;
			updatePreview();
		} );
	}

	$( function() {
		if ( ! $( '.ctl-settings-preview' ).length ) {
			return;
		}

		updatePreview();
		$( '#csf-form' ).on( 'input change', ':input', scheduleUpdate );

		$( document ).on( 'click', '.wp-color-result, .wp-picker-clear, .wp-picker-default', function() {
			window.setTimeout( scheduleUpdate, 0 );
		} );

		$( window ).on( 'hashchange csf.hashchange', function() {
			window.setTimeout( scheduleUpdate, 0 );
		} );
	} );
}( jQuery ) );
