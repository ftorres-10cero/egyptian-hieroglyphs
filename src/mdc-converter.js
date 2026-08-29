/**
 * Conversor de texto Manuel de Codage (MdC) a codificación Unicode jeroglífica.
 *
 * Depende del parser incluido en `assets/mdcconversion.js` (HieroJax, GPL-3.0),
 * que se carga en el editor como script clásico y expone `window.mdcsyntax`.
 */

/**
 * Convierte texto MdC (una o varias líneas) a codificación Unicode.
 *
 * @param {string} mdcText Texto en notación Manuel de Codage.
 * @return {{unicode: string, errors: string[], warnings: string[]}} Unicode resultante,
 *         errores de sintaxis y avisos (p. ej. signos no mapeados).
 */
export function mdcToUnicode( mdcText ) {
	const unicode = [];
	const errors = [];
	const warnings = [];

	if ( ! mdcText || ! mdcText.trim() ) {
		return { unicode: '', errors, warnings };
	}

	if ( typeof window === 'undefined' || ! window.mdcsyntax ) {
		return {
			unicode: '',
			errors: [ 'El conversor MdC no está disponible (window.mdcsyntax).' ],
			warnings,
		};
	}

	const lines = mdcText.split( '\n' );

	for ( const line of lines ) {
		if ( ! line.trim() ) {
			continue;
		}
		try {
			const parsed = window.mdcsyntax.parse( line + '\n' );
			const parts = parsed && parsed.parts ? parsed.parts : [];
			let lineOut = '';
			for ( const part of parts ) {
				if ( ! part ) {
					continue;
				}
				if ( typeof part.cutByColor === 'function' ) {
					const fragments = part.cutByColor() || [];
					lineOut += fragments.map( ( f ) => f.toString() ).join( '' );
				} else {
					lineOut += String( part );
				}
			}
			unicode.push( lineOut );
		} catch ( err ) {
			const msg = err && err.message ? err.message : String( err );
			errors.push( msg.split( '\n' )[ 0 ] );
		}
	}

	const joined = unicode.join( '\n' );
	if ( /\uFFFD/.test( joined ) ) {
		warnings.push(
			'La conversión contiene signos no disponibles en Unicode (�). ' +
				'Revisa los códigos MdC o edita la codificación Unicode directamente.'
		);
	}

	return { unicode: joined, errors, warnings };
}

/**
 * Acceso seguro al objeto `hierojax` (definido por assets/hierojax.js).
 *
 * En scripts clásicos `hierojax` es una variable global de ámbito léxico
 * (`const`), por lo que puede no colgar de `window`. Esta función cubre ambos casos.
 *
 * @return {?Object} Instancia de HieroJax o null si no está cargada.
 */
export function getHierojax() {
	if ( typeof window !== 'undefined' && window.hierojax ) {
		return window.hierojax;
	}
	if ( typeof hierojax !== 'undefined' ) {
		return hierojax;
	}
	return null;
}
