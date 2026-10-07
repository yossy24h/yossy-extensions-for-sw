( function () {
	'use strict';

	function init() {
		const ctas = document.querySelectorAll( '.yefsw-fixed-cta[data-scroll-threshold]' );
		if ( ! ctas.length ) {
			return;
		}

		let ticking = false;
		const schedule = window.requestAnimationFrame || function ( callback ) {
			return window.setTimeout( callback, 16 );
		};

		function update() {
			ctas.forEach( function ( cta ) {
				const threshold = Math.max( 0, parseInt( cta.dataset.scrollThreshold, 10 ) || 0 );
				cta.classList.toggle( 'is-visible', window.scrollY >= threshold );
			} );
			ticking = false;
		}

		function requestUpdate() {
			if ( ticking ) {
				return;
			}
			ticking = true;
			schedule( update );
		}

		update();
		window.addEventListener( 'scroll', requestUpdate, { passive: true } );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	} else {
		init();
	}
} )();
