/**
 * Pruebas de la conversión MdC -> Unicode (headless, sin navegador).
 *
 * Uso: node tests/mdc-conversion.js
 */
const assert = require( 'node:assert' );
require( './node-shims' )();
const mdc = require( '../assets/mdcconversion.js' );

/**
 * Convierte texto MdC (una o varias líneas) a Unicode, línea a línea.
 * @param {string} text
 * @return {string}
 */
function convert( text ) {
	return text
		.split( '\n' )
		.filter( ( line ) => line.trim() !== '' )
		.map( ( line ) => {
			const parsed = mdc.parse( line + '\n' );
			const parts = parsed && parsed.parts ? parsed.parts : [];
			return parts
				.map( ( part ) =>
					part && typeof part.cutByColor === 'function'
						? part.cutByColor().map( ( f ) => f.toString() ).join( '' )
						: String( part )
				)
				.join( '' );
		} )
		.join( '\n' );
}

const cases = [
	// Signo simple (N5 = sol, código MdC "ra").
	[ 'ra', '\u{131F3}' ],
	// Signo "dios" (A40).
	[ 'nTr', '\u{132B9}' ],
	// Cuadraturas con separadores vertical (:) y horizontal (*).
	[ 'nTr:r', '\u{132B9}\u{13430}\u{1308B}' ],
	[ 'ra:Z1*t:Z1', '\u{131F3}\u{13430}\u{133E4}\u{13431}\u{133CF}\u{13430}\u{133E4}' ],
	// Yuxtaposición simple.
	[ 'i*w', '\u{131CB}\u{13431}\u{13171}' ],
	// Varias líneas -> una salida por línea.
	[ 'nfr\nanx', '𓄤\n𓋹' ],
];

for ( const [ input, expected ] of cases ) {
	const out = convert( input );
	assert.strictEqual(
		out,
		expected,
		`MdC "${ input }" debe convertir a ${ JSON.stringify( expected ) }, se obtuvo ${ JSON.stringify( out ) }`
	);
	console.log( `✓ "${ input }" -> ${ JSON.stringify( out ) }` );
}

// Errores de sintaxis deben lanzar excepción.
assert.throws( () => mdc.parse( '[ra\n' ), 'El MdC inválido debe lanzar error' );
console.log( '✓ El MdC inválido lanza error de sintaxis' );

// Frases usadas por el shortcode [hiero] (transliteración MdC -> Unicode).
// Nota: mn-xpr-ra y nb-mAat-ra incluyen signos no mapeados a Unicode en
// algunos entornos; aquí solo validamos las frases estables del artículo.
const shortcode_cases = [
	[ 'ra-ms-sw', '𓇳𓄟𓇓' ],
	[ 'nTr anx', '𓊹𓋹' ],
	[ 'anx', '𓋹' ],
	[ 'nTr aA', '𓊹𓉻' ],
];

for ( const [ input, expected ] of shortcode_cases ) {
	const out = convert( input );
	assert.strictEqual(
		out,
		expected,
		`Shortcode MdC "${ input }" debe convertir a ${ JSON.stringify( expected ) }, se obtuvo ${ JSON.stringify( out ) }`
	);
	console.log( `✓ [hiero]${ input }[/hiero] -> ${ JSON.stringify( out ) }` );
}

console.log( 'PRUEBAS DE CONVERSIÓN MdC (incluye shortcode): OK' );
