( function ( $ ) {
	'use strict';

	$( function () {
		var $notices = $( '.cph-header-notices' );
		var $screenMeta = $( '#screen-meta' );
		var $screenMetaLinks = $( '#screen-meta-links' );

		if ( $notices.length && $screenMetaLinks.length && ! $screenMetaLinks.data( 'ctlRelocated' ) ) {
			var $bar = $( '<div class="cph-screen-meta-bar" />' );
			$bar.append( $screenMeta ).append( $screenMetaLinks );
			$notices.after( $bar );
			$screenMetaLinks.data( 'ctlRelocated', true );
		}

		$( document ).on( 'click', '.cpfm-see-terms', function ( event ) {
			event.preventDefault();

			var $link = $( this );
			var $box = $link.closest( '.ctl-advance-privacy-desc' ).find( '.ctl-advance-terms-box' );
			var hideTerms = ( typeof ctlAdvanceSettings !== 'undefined' ) ? ctlAdvanceSettings.i18n.hideTerms : 'Hide terms';
			var showTerms = ( typeof ctlAdvanceSettings !== 'undefined' ) ? ctlAdvanceSettings.i18n.showTerms : 'See terms';

			if ( ! $box.length ) {
				return;
			}

			$box.toggleClass( 'is-visible' );
			$link.text( $box.hasClass( 'is-visible' ) ? hideTerms : showTerms );
		} );
	} );
}( jQuery ) );
