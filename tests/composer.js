/**
 * Prueba del constructor de textos MdC (página de ajustes).
 *
 * Valida con jsdom:
 *   1. la paleta se rellena con los signos del catálogo,
 *   2. los botones insertan texto en el cursor,
 *   3. la vista previa convierte MdC -> Unicode (mdcsyntax).
 *
 * Uso: node tests/composer.js
 */
const assert = require( 'node:assert' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { JSDOM } = require( 'jsdom' );
require( './node-shims' )();

const PLUGIN_DIR = path.join( __dirname, '..' );
const CATALOG = JSON.parse(
	fs.readFileSync( path.join( PLUGIN_DIR, 'assets/sign-catalog.json' ), 'utf8' )
);
const COMPOSER_SRC = fs.readFileSync(
	path.join( PLUGIN_DIR, 'assets/admin-composer.js' ),
	'utf8'
);
const PARSER_SRC = fs.readFileSync(
	path.join( PLUGIN_DIR, 'assets/mdcconversion.js' ),
	'utf8'
);

const dom = new JSDOM(
	`<!DOCTYPE html><html><body>
		<select id="ft-hiero-composer-cats"></select>
		<input type="search" id="ft-hiero-composer-search" />
		<div class="sign-grid" id="ft-hiero-composer-grid"></div>
		<textarea id="ft-hiero-composer-mdc"></textarea>
		<div id="ft-hiero-composer-preview"></div>
		<button id="ft-hiero-act-cartouche"></button>
		<button id="ft-hiero-act-quadrat"></button>
		<button id="ft-hiero-copy-mdc"></button>
		<button id="ft-hiero-copy-uni"></button>
		<button id="ft-hiero-composer-clear"></button>
	</body></html>`,
	{ runScripts: 'outside-only' }
);

const win = dom.window;
const doc = win.document;
win.ftHieroSigns = CATALOG;
win.ftHieroI18n = {
	noSigns: 'sin signos',
	previewHint: 'preview',
	converterMissing: 'falta',
};
win.addEventListener = ( ev, fn ) => { if ( ev === 'DOMContentLoaded' ) fn(); };
doc.addEventListener = ( ev, fn ) => { if ( ev === 'DOMContentLoaded' ) fn(); };
win.FontFace = function ( family, src ) {
	this.family = family;
	this.src = src;
	this.load = () => Promise.resolve( { family } );
};
win.document.fonts = { add: () => {} };

// Cargar el parser real (mdcconversion.js incluye hierojax completo).
win.eval(
	PARSER_SRC.replace(
		/const hierojax = new HieroJax\(\);/,
		'window.__hiero = new HieroJax(); const hierojax = window.__hiero;'
	)
);
win.mdcsyntax = win.eval( 'mdcsyntax' );
win.hierojax = win.eval( 'window.__hiero' );
win.hierojax.processFragmentsIn = ( el ) => {
	el.__fragments = el.querySelectorAll( '.hierojax' ).length;
};

win.eval( COMPOSER_SRC );

const cats = doc.getElementById( 'ft-hiero-composer-cats' );
const grid = doc.getElementById( 'ft-hiero-composer-grid' );
const ta = doc.getElementById( 'ft-hiero-composer-mdc' );
const preview = doc.getElementById( 'ft-hiero-composer-preview' );

// 1. Paleta: categorías y signos.
assert.strictEqual( cats.options.length, 25, 'Debe haber 25 categorías' );
assert.ok(
	grid.querySelectorAll( '.ft-hiero-sign' ).length > 0,
	'La paleta de la categoría A debe tener signos'
	);
console.log( `✓ Paleta: ${ cats.options.length } categorías, ${ CATALOG.length } signos en el catálogo` );

// 2. Botones insertan texto en el cursor.
const sign = grid.querySelector( '.ft-hiero-sign' );
const code = sign.getAttribute( 'title' ).split( ' ' )[ 0 ];
sign.click();
assert.strictEqual( ta.value, code, 'Clic en un signo debe insertar su código' );
doc.getElementById( 'ft-hiero-act-cartouche' ).click();
assert.ok( ta.value.includes( '<-' ), 'El botón de cartucho debe insertar «<-»' );
doc.getElementById( 'ft-hiero-act-quadrat' ).click();
assert.ok( ta.value.includes( ':' ), 'El botón de cuadratura debe insertar «:»' );
console.log( `✓ Botones insertan en el cursor: "${ ta.value }"` );

// 3. Vista previa: MdC -> Unicode.
ta.value = 'nTr anx\n<- ra-ms-sw ->';
ta.dispatchEvent( new win.Event( 'input' ) );
const spans = preview.querySelectorAll( '.hierojax' );
assert.strictEqual( spans.length, 2, 'Debe haber 2 fragmentos en la vista previa' );
assert.strictEqual( spans[ 0 ].textContent, '𓊹𓋹', 'nTr anx debe convertir a 𓊹𓋹' );
assert.ok(
	spans[ 1 ].textContent.includes( '𓇳' ),
	'El cartucho <- ra-ms-sw -> debe contener 𓇳'
);
assert.strictEqual( preview.__fragments, 2, 'hierojax debe procesar 2 fragmentos' );
console.log( '✓ Vista previa: nTr anx -> 𓊹𓋹, <- ra-ms-sw -> cartucho' );

console.log( 'PRUEBAS DEL CONSTRUCTOR MdC: OK' );
