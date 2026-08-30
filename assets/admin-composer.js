/**
 * Constructor de textos MdC — herramienta de la página de ajustes.
 *
 * Permite componer una transliteración eligiendo signos de una paleta visual
 * (lista de Gardiner) e insertando separadores y cerramientos con botones:
 * cartuchos, cuadraturas, grupos, yuxtaposición, etc. Muestra la vista previa
 * en vivo (MdC -> Unicode -> SVG) y permite copiar el resultado.
 *
 * Dependencias (encoladas por Settings::enqueue_assets):
 *  - mdcconversion.js  -> expone mdcsyntax (parser) y hierojax (render).
 *  - sign-catalog.json -> catálogo de signos inyectado en window.ftHieroSigns.
 */
( function () {
	'use strict';

	if ( typeof window.ftHieroSigns === 'undefined' ) {
		return;
	}

	var signs = window.ftHieroSigns;
	var CATS = {
		A: 'Hombre y ocupaciones',
		B: 'Mujer y ocupaciones',
		C: 'Deidades y seres',
		D: 'Partes del cuerpo humano',
		E: 'Mamíferos',
		F: 'Partes de mamíferos',
		G: 'Aves',
		H: 'Partes de aves',
		I: 'Anfibios y reptiles',
		K: 'Peces y partes',
		L: 'Invertebrados',
		M: 'Árboles y plantas',
		N: 'Cielo, tierra y agua',
		O: 'Edificios y partes',
		P: 'Barcos y partes',
		Q: 'Mobiliario y utensilios',
		R: 'Templos y objetos sagrados',
		S: 'Coronas, vestidos y armas',
		T: 'Armas de guerra y caza',
		U: 'Agricultura, artesanía y oficios',
		V: 'Cuerdas, fibras, cestas y vasijas',
		W: 'Vasijas de piedra y barro',
		X: 'Panes y pasteles',
		Y: 'Escritura, juegos y música',
		Z: 'Trazo y figuras geométricas',
	};

	var $ = function ( id ) {
		return document.getElementById( id );
	};

	/**
	 * Inserta texto en el área de MdC en la posición del cursor.
	 */
	function insertText( text ) {
		var ta = $( 'ft-hiero-composer-mdc' );
		if ( ! ta ) {
			return;
		}
		var start = ta.selectionStart || ta.value.length;
		var end = ta.selectionEnd || ta.value.length;
		ta.value = ta.value.slice( 0, start ) + text + ta.value.slice( end );
		var pos = start + text.length;
		ta.focus();
		ta.setSelectionRange( pos, pos );
		ta.dispatchEvent( new Event( 'input' ) );
	}

	/**
	 * Inserta el código del signo seleccionado (paleta).
	 */
	function insertSign( code ) {
		insertText( code );
	}

	/**
	 * Renderiza la paleta de signos de una categoría.
	 */
	function renderPalette( cat ) {
		var grid = $( 'ft-hiero-composer-grid' );
		if ( ! grid ) {
			return;
		}
		grid.innerHTML = '';
		var list = signs.filter( function ( s ) {
			return s.g === cat;
		} );
		if ( list.length === 0 ) {
			grid.innerHTML = '<p style="color:#757575;">' +
				window.ftHieroI18n.noSigns + '</p>';
			return;
		}
		list.forEach( function ( s ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'button button-small ft-hiero-sign';
			btn.title = s.c + ( s.t ? ' · ' + s.t : '' );
			btn.innerHTML =
				'<span class="ft-hiero-sign-glyph">' + s.u + '</span>' +
				'<span class="ft-hiero-sign-code">' + s.c + '</span>';
			btn.addEventListener( 'click', function () {
				insertSign( s.c );
			} );
			grid.appendChild( btn );
		} );
		grid.scrollTop = 0;
	}

	/**
	 * Vista previa: convierte el MdC a Unicode y renderiza SVG en vivo.
	 */
	function updatePreview() {
		var ta = $( 'ft-hiero-composer-mdc' );
		var preview = $( 'ft-hiero-composer-preview' );
		if ( ! ta || ! preview ) {
			return;
		}
		var text = ta.value.trim();
		preview.innerHTML = '';
		if ( ! text ) {
			preview.innerHTML = '<p style="color:#757575;font-style:italic;">' +
				window.ftHieroI18n.previewHint + '</p>';
			return;
		}
		if ( typeof mdcsyntax === 'undefined' ) {
			preview.innerHTML = '<p style="color:#b32d2e;">' +
				window.ftHieroI18n.converterMissing + '</p>';
			return;
		}
		var lines = text.split( '\n' );
		var frags = [];
		lines.forEach( function ( line ) {
			line = line.trim();
			if ( ! line ) {
				return;
			}
			try {
				var parsed = mdcsyntax.parse( line + '\n' );
				var parts = parsed && parsed.parts ? parsed.parts : [];
				var uni = parts
					.map( function ( p ) {
						return p && typeof p.cutByColor === 'function'
							? p.cutByColor().map( function ( f ) { return f.toString(); } ).join( '' )
							: String( p );
					} )
					.join( '' );
				frags.push( uni );
			} catch ( e ) {
				frags.push( '' );
			}
		} );
		frags.forEach( function ( uni ) {
			if ( ! uni ) {
				return;
			}
			var span = document.createElement( 'span' );
			span.className = 'hierojax';
			span.setAttribute( 'data-type', 'svg' );
			span.setAttribute( 'data-sep', '0.1' );
			span.setAttribute( 'data-linesize', '1' );
			span.setAttribute( 'data-shadepattern', 'uniform' );
			span.style.fontSize = '40px';
			span.textContent = uni;
			preview.appendChild( span );
		} );
		if ( typeof hierojax !== 'undefined' ) {
			hierojax.processFragmentsIn( preview );
		}
	}

	/**
	 * Inicia la herramienta cuando el DOM está listo.
	 */
	function init() {
		var cats = $( 'ft-hiero-composer-cats' );
		if ( ! cats ) {
			return;
		}

		// Selector de categorías.
		Object.keys( CATS ).forEach( function ( cat ) {
			var opt = document.createElement( 'option' );
			opt.value = cat;
			opt.textContent = cat + ' — ' + CATS[ cat ];
			cats.appendChild( opt );
		} );
		cats.addEventListener( 'change', function () {
			renderPalette( cats.value );
		} );
		renderPalette( cats.value || 'A' );

		// Búsqueda por código o transliteración.
		var search = $( 'ft-hiero-composer-search' );
		if ( search ) {
			search.addEventListener( 'input', function () {
				var q = search.value.trim().toLowerCase();
				var grid = $( 'ft-hiero-composer-grid' );
				if ( ! grid ) {
					return;
				}
				if ( ! q ) {
					renderPalette( cats.value );
					return;
				}
				grid.innerHTML = '';
				signs
					.filter( function ( s ) {
						return (
							s.c.toLowerCase().indexOf( q ) === 0 ||
							( s.t && s.t.toLowerCase().indexOf( q ) === 0 )
						);
					} )
					.slice( 0, 60 )
					.forEach( function ( s ) {
						var btn = document.createElement( 'button' );
						btn.type = 'button';
						btn.className = 'button button-small ft-hiero-sign';
						btn.title = s.c + ( s.t ? ' · ' + s.t : '' );
						btn.innerHTML =
							'<span class="ft-hiero-sign-glyph">' + s.u + '</span>' +
							'<span class="ft-hiero-sign-code">' + s.c + '</span>';
						btn.addEventListener( 'click', function () {
							insertSign( s.c );
						} );
						grid.appendChild( btn );
					} );
			} );
		}

		// Botones de estructura (cartuchos, cuadraturas, etc.).
		var actions = {
			'ft-hiero-act-cartouche': '<-  ->',
			'ft-hiero-act-cartouche-open': '<- ',
			'ft-hiero-act-cartouche-close': ' ->',
			'ft-hiero-act-shen': '<S-  ->',
			'ft-hiero-act-quadrat': ':',
			'ft-hiero-act-group': '*',
			'ft-hiero-act-juxtapose': '-',
			'ft-hiero-act-space': ' ',
			'ft-hiero-act-fragment': '!',
			'ft-hiero-act-red': '\\red',
			'ft-hiero-act-newline': '\n',
		};
		Object.keys( actions ).forEach( function ( id ) {
			var btn = $( id );
			if ( btn ) {
				btn.addEventListener( 'click', function () {
					insertText( actions[ id ] );
				} );
			}
		} );

		// Copiar MdC y copiar Unicode.
		var copyMdc = $( 'ft-hiero-copy-mdc' );
		if ( copyMdc ) {
			copyMdc.addEventListener( 'click', function () {
				var ta = $( 'ft-hiero-composer-mdc' );
				if ( ta && ta.value ) {
					navigator.clipboard.writeText( ta.value );
				}
			} );
		}
		var copyUni = $( 'ft-hiero-copy-uni' );
		if ( copyUni ) {
			copyUni.addEventListener( 'click', function () {
				var ta = $( 'ft-hiero-composer-mdc' );
				if ( ! ta || ! ta.value ) {
					return;
				}
				var unis = [];
				ta.value.split( '\n' ).forEach( function ( line ) {
					line = line.trim();
					if ( ! line ) {
						return;
					}
					try {
						var parsed = mdcsyntax.parse( line + '\n' );
						var parts = parsed && parsed.parts ? parsed.parts : [];
						var uni = parts
							.map( function ( p ) {
								return p && typeof p.cutByColor === 'function'
									? p.cutByColor().map( function ( f ) { return f.toString(); } ).join( '' )
									: String( p );
							} )
							.join( '' );
						unis.push( uni );
					} catch ( e ) {}
				} );
				if ( unis.length ) {
					navigator.clipboard.writeText( unis.join( '\n' ) );
				}
			} );
		}

		// Limpiar.
		var clear = $( 'ft-hiero-composer-clear' );
		if ( clear ) {
			clear.addEventListener( 'click', function () {
				$( 'ft-hiero-composer-mdc' ).value = '';
				$( 'ft-hiero-composer-mdc' ).dispatchEvent( new Event( 'input' ) );
			} );
		}

		// Vista previa en vivo.
		var ta = $( 'ft-hiero-composer-mdc' );
		if ( ta ) {
			ta.addEventListener( 'input', updatePreview );
		}
		updatePreview();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
