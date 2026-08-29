/**
 * Conversor MdC -> Unicode de la página de administración.
 *
 * Depende de los scripts clásicos `hierojax.js` (global léxico `hierojax`) y
 * `mdcconversion.js` (global `window.mdcsyntax`).
 */
( function () {
	'use strict';

	var input = document.getElementById( 'ft-hiero-input' );
	var convertBtn = document.getElementById( 'ft-hiero-convert' );
	var output = document.getElementById( 'ft-hiero-output' );
	var preview = document.getElementById( 'ft-hiero-preview' );
	var errors = document.getElementById( 'ft-hiero-errors' );
	var warnings = document.getElementById( 'ft-hiero-warnings' );
	var copyBtn = document.getElementById( 'ft-hiero-copy' );

	if ( ! input || ! convertBtn || ! output || ! preview || ! errors || ! warnings || ! copyBtn ) {
		return;
	}

	function convert() {
		errors.hidden = true;
		warnings.hidden = true;

		var lines = input.value.split( '\n' );
		var out = [];
		var errs = [];

		for ( var i = 0; i < lines.length; i++ ) {
			var line = lines[ i ];
			if ( ! line.trim() ) {
				continue;
			}
			try {
				var parsed = window.mdcsyntax.parse( line + '\n' );
				var parts = parsed && parsed.parts ? parsed.parts : [];
				var s = '';
				for ( var j = 0; j < parts.length; j++ ) {
					var part = parts[ j ];
					if ( ! part ) {
						continue;
					}
					if ( typeof part.cutByColor === 'function' ) {
						s += part.cutByColor().map( function ( f ) { return f.toString(); } ).join( '' );
					} else {
						s += String( part );
					}
				}
				out.push( s );
			} catch ( e ) {
				errs.push( ( e && e.message ? e.message : String( e ) ).split( '\n' )[ 0 ] );
			}
		}

		var joined = out.join( '\n' );
		output.value = joined;

		if ( errs.length ) {
			errors.textContent = errs.join( ' | ' );
			errors.hidden = false;
		}
		if ( /\uFFFD/.test( joined ) ) {
			warnings.textContent = wp.i18n.__(
				'La conversión contiene signos no disponibles en Unicode (�). Revisa los códigos MdC.',
				'egyptian-hieroglyphs'
			);
			warnings.hidden = false;
		}

		preview.innerHTML = '';
		var frags = out.filter( function ( s ) { return s.trim() !== ''; } );
		if ( ! frags.length ) {
			preview.textContent = '\u2014';
			return;
		}
		var div = document.createElement( 'div' );
		div.className = 'ftorres-hiero';
		for ( var k = 0; k < frags.length; k++ ) {
			var span = document.createElement( 'span' );
			span.className = 'hierojax';
			span.setAttribute( 'data-type', 'svg' );
			span.textContent = frags[ k ];
			div.appendChild( span );
		}
		preview.appendChild( div );
		if ( typeof hierojax !== 'undefined' ) {
			hierojax.processFragmentsIn( preview );
		}
	}

	convertBtn.addEventListener( 'click', convert );

	copyBtn.addEventListener( 'click', function () {
		var label = wp.i18n.__(
			'Copiar al portapapeles',
			'egyptian-hieroglyphs'
		);
		var done = function () {
			copyBtn.textContent = '\u2713';
			window.setTimeout( function () { copyBtn.textContent = label; }, 1500 );
		};
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( output.value ).then( done ).catch( function () {
				output.select();
				document.execCommand( 'copy' );
				done();
			} );
		} else {
			output.select();
			document.execCommand( 'copy' );
			done();
		}
	} );

	// Conversión inicial con el contenido de ejemplo.
	convert();
} )();
