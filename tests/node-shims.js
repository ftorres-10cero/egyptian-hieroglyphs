/**
 * Shims mínimos para cargar mdcconversion.js en Node (tests).
 *
 * Uso: require('./node-shims')(); const m = require('../assets/mdcconversion.js');
 */
module.exports = function applyShims() {
	class FontFaceStub {
		constructor( family, src ) {
			this.family = family;
			this.src = src;
		}
		load() {
			return Promise.resolve( this );
		}
	}
	global.FontFace = FontFaceStub;
	global.document = {
		fonts: { add() {} },
		getElementById: () => null,
		getElementsByClassName: () => [],
		createElement: () => ( {} ),
		createElementNS: () => ( {} ),
	};
	global.window = global;
	global.navigator = { platform: 'node' };
};
