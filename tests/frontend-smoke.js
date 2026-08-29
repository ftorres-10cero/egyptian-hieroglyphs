/**
 * Prueba de humo del frontend sin navegador (jsdom).
 *
 * Valida el pipeline real del plugin:
 *   1. markup guardado por el bloque (span .hierojax con Unicode)
 *   2. carga de assets/hierojax.js con la URL de fuente parcheada
 *   3. hierojax.processFragments() convierte los spans en SVG
 *
 * Uso: node tests/frontend-smoke.js
 */
const assert = require( 'node:assert' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { JSDOM } = require( 'jsdom' );

const PLUGIN_DIR = path.join( __dirname, '..' );
const HIEROJAX_SRC = fs.readFileSync(
	path.join( PLUGIN_DIR, 'assets/hierojax.js' ),
	'utf8'
);

const SAVED_BLOCK_HTML =
	'<div class="wp-block-ftorres-hiero ftorres-hiero" style="text-align:center;">' +
	'<span class="hierojax" data-type="svg" data-sep="0.1" data-linesize="1" data-shadepattern="uniform" style="font-size:36px;">\u{131F3}\u{13430}\u{133E4}\u{13431}\u{133CF}\u{13430}\u{133E4}</span>' +
	'<span class="hierojax" data-type="svg" data-sep="0.1" data-linesize="1" data-shadepattern="uniform" style="font-size:36px;">\u{132B9}\u{13430}\u{1308B}</span>' +
	'</div>';

const dom = new JSDOM(
	`<!DOCTYPE html><html><head></head><body>${ SAVED_BLOCK_HTML }</body></html>`,
	{ url: 'https://example.test/articulo/', runScripts: 'dangerously' }
);

const { window } = dom;
const doc = window.document;

// Inyecta un script inline (se ejecuta en el contexto del documento).
function injectScript( code ) {
	const script = doc.createElement( 'script' );
	script.textContent = code;
	doc.head.appendChild( script );
	return script;
}

// Preludio de shims (jsdom no implementa FontFace ni innerText).
injectScript( `
	var __capturedFontSrc = null;
	var FontFace = class FontFaceStub {
		constructor( family, src ) {
			this.family = family;
			this.src = src;
			__capturedFontSrc = src;
		}
		load() { return Promise.resolve( this ); }
	};
	document.fonts = { add: function() {} };
	if ( ! ('innerText' in HTMLElement.prototype) ) {
		Object.defineProperty( HTMLElement.prototype, 'innerText', {
			get: function() { return this.textContent; },
			configurable: true,
		} );
	}
	// jsdom no implementa canvas; hierojax lo usa para medir glifos.
	// Stub con métricas aproximadas (solo para la prueba de humo).
	HTMLCanvasElement.prototype.getContext = function() {
		var ctx = {};
		return new Proxy( ctx, {
			get: function( target, prop ) {
				if ( prop === 'measureText' ) {
					return function( t ) { return { width: 12 * String( t ).length }; };
				}
				if ( prop === 'getImageData' ) {
					return function( x, y, w, h ) {
						return { data: new Uint8ClampedArray( w * h * 4 ) };
					};
				}
				if ( prop === 'canvas' ) { return null; }
				if ( [ 'font', 'fillStyle', 'strokeStyle', 'textAlign', 'lineWidth', 'globalAlpha' ].indexOf( prop ) >= 0 ) {
					return '';
				}
				return function() {};
			},
			set: function() { return true; },
		} );
	};
	window.ftorresHieroFontUrl = 'url(https://example.test/wp-content/plugins/egyptian-hieroglyphs/assets/fonts/NewGardiner.otf)';
` );

// Carga del runtime tal como lo haría el navegador (script clásico).
injectScript( HIEROJAX_SRC );

const capturedFontSrc = window.eval( '__capturedFontSrc' );
assert.ok(
	capturedFontSrc && capturedFontSrc.includes( 'wp-content/plugins/egyptian-hieroglyphs' ),
	'La fuente debe cargarse desde la URL absoluta del plugin, se obtuvo: ' + capturedFontSrc
);
console.log( '✓ La URL de la fuente usa el override del plugin' );

// Ejecución equivalente al script inline del frontend.
// (jsdom ya disparó DOMContentLoaded durante el parseo, así que se llama
// directamente; el evento en sí es comportamiento estándar del navegador.)
// waitForFonts procesa de forma asíncrona (setTimeout), por eso se espera.
window.eval( 'hierojax.processFragments();' );

setTimeout( () => {
	const spans = doc.querySelectorAll( '.hierojax' );
	assert.strictEqual( spans.length, 2, 'Deben existir 2 spans hierojax' );

	spans.forEach( ( span ) => {
		const svg = span.querySelector( 'svg' );
		assert.ok(
			svg,
			'Cada span debe contener un SVG tras processFragments. innerHTML actual: ' +
				span.innerHTML.slice( 0, 200 )
		);
		const tspan = svg.querySelector( 'tspan' );
		assert.ok( tspan, 'El SVG debe contener texto (tspan)' );
	} );

	console.log( '✓ Los spans se convierten en SVG con signos' );
	console.log( 'PRUEBA DE HUMO FRONTEND: OK' );
}, 150 );
