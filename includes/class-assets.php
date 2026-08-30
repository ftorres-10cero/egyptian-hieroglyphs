<?php
/**
 * Gestión de assets y shortcode del plugin Egyptian Hieroglyphs (MdC).
 *
 * @package EgyptianHieroglyphs
 */

namespace EgyptianHieroglyphs;

defined( 'ABSPATH' ) || exit;

/**
 * Assets del plugin.
 */
class Assets {

	/**
	 * Evita encolar el frontend más de una vez por petición.
	 *
	 * @var bool
	 */
	private static $frontend_enqueued = false;

	/**
	 * Registra los scripts y estilos del plugin.
	 */
	public static function register() {
		// Runtime de hierojax (render SVG en frontend y editor).
		wp_register_script(
			'ftorres-hiero-runtime',
			FT_HIERO_PLUGIN_URL . 'assets/hierojax.js',
			array(),
			FT_HIERO_VERSION,
			true
		);

		// Conversor MdC -> Unicode (solo editor/admin).
		// IMPORTANTE: mdcconversion.js ya incluye el core completo de hierojax
		// (renderer + parser). No debe cargarse junto a hierojax.js en la misma
		// página: ambos declaran clases globales (`Shapes`, `Group`, ...) y el
		// segundo script lanza "Identifier has already been declared".
		wp_register_script(
			'ftorres-hiero-mdc',
			FT_HIERO_PLUGIN_URL . 'assets/mdcconversion.js',
			array(),
			FT_HIERO_VERSION,
			true
		);

		// CSS de hierojax (editor y frontend).
		wp_register_style(
			'ftorres-hiero-css',
			FT_HIERO_PLUGIN_URL . 'assets/hierojax.css',
			array(),
			FT_HIERO_VERSION
		);

		// La fuente NewGardiner se carga con URL absoluta (ver VENDOR-NOTES.md).
		wp_add_inline_script(
			'ftorres-hiero-runtime',
			self::font_url_inline_script(),
			'before'
		);
		// El editor y el conversor cargan solo mdcconversion.js: la URL de la
		// fuente también debe fijarse antes de ese script (si no, usa la URL
		// relativa y la fuente no carga en el admin).
		wp_add_inline_script(
			'ftorres-hiero-mdc',
			self::font_url_inline_script(),
			'before'
		);
	}

	/**
	 * Encola los assets del editor de bloques.
	 */
	public static function enqueue_editor() {
		if ( ! wp_script_is( 'ftorres-hiero-runtime', 'registered' ) ) {
			self::register();
		}

		$asset_file = FT_HIERO_PLUGIN_DIR . 'build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		$deps = array_merge(
			$asset['dependencies'],
			array( 'ftorres-hiero-mdc' )
		);

		wp_enqueue_script(
			'ftorres-hiero-block',
			FT_HIERO_PLUGIN_URL . 'build/index.js',
			$deps,
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'ftorres-hiero-block-editor',
			FT_HIERO_PLUGIN_URL . 'build/index.css',
			array( 'ftorres-hiero-css' ),
			$asset['version']
		);

		// Procesa los spans .hierojax del contenido (p. ej. en el bloque Classic),
		// para que los jeroglíficos se vean bien también en el editor.
		wp_add_inline_script(
			'ftorres-hiero-block',
			'window.addEventListener("DOMContentLoaded",function(){' .
				'var f=function(){if(typeof hierojax!=="undefined"){' .
					'var p=false;document.querySelectorAll(".hierojax").forEach(function(s){if(!s.querySelector("svg")){p=true;}});' .
					'if(p){hierojax.processFragments();}' .
				'}};f();setTimeout(f,1500);setTimeout(f,4000);' .
			'});',
			'after'
		);
	}

	/**
	 * Encola los assets del frontend (solo cuando hay contenido jeroglífico).
	 *
	 * @param bool $needs_mdc Si se necesitan shortcodes con transliteración MdC
	 *                        (se carga mdcconversion.js, que ya incluye el
	 *                        renderer completo de hierojax).
	 */
	public static function enqueue_frontend( $needs_mdc = false ) {
		if ( self::$frontend_enqueued ) {
			return;
		}
		self::$frontend_enqueued = true;

		if ( ! wp_script_is( 'ftorres-hiero-runtime', 'registered' ) ) {
			self::register();
		}

		if ( $needs_mdc ) {
			// mdcconversion.js incluye renderer + parser; nunca junto a hierojax.js.
			wp_enqueue_script( 'ftorres-hiero-mdc' );
			wp_add_inline_script(
				'ftorres-hiero-mdc',
				self::frontend_process_script( true ),
				'after'
			);
		} else {
			wp_enqueue_script( 'ftorres-hiero-runtime' );
			wp_add_inline_script(
				'ftorres-hiero-runtime',
				self::frontend_process_script( false ),
				'after'
			);
		}

		wp_enqueue_style( 'ftorres-hiero-css' );
		wp_enqueue_style(
			'ftorres-hiero-block-style',
			FT_HIERO_PLUGIN_URL . 'build/style-index.css',
			array(),
			FT_HIERO_VERSION
		);
	}

	/**
	 * Shortcode [hiero]...[/hiero].
	 *
	 * Acepta dos formatos de contenido:
	 *  - Codificación Unicode jeroglífica (caracteres U+13000+), una línea por
	 *    fragmento — se renderiza directamente.
	 *  - Transliteración MdC (nTr, ra-ms-sw, <- mn-xpr-ra -> …) — se convierte
	 *    a Unicode en el cliente (requiere mdcconversion.js en el frontend).
	 *
	 * El resultado se envuelve en un <span> inline para integrarse en el texto
	 * (no rompe el párrafo como un <div>).
	 *
	 * @param array|string $atts    Atributos del shortcode.
	 * @param string|null  $content Contenido encerrado.
	 * @return string HTML renderizado.
	 */
	public static function render_shortcode( $atts, $content = null ) {
		$defaults = array(
			'fontsize'  => Settings::default_font_size(),
			'align'     => 'left',
			'color'     => Settings::default_color(),
			'dir'       => 'ltr',
			'sep'       => 0.1,
			'shade'     => 'uniform',
			'linesize'  => 1,
			'separated' => false,
		);

		$atts = shortcode_atts( $defaults, $atts, 'hiero' );

		$content = trim( (string) $content );
		if ( '' === $content ) {
			return '';
		}

		$fontsize  = max( 8, min( 200, absint( $atts['fontsize'] ) ) );
		$align     = in_array( $atts['align'], array( 'left', 'center', 'right' ), true ) ? $atts['align'] : 'left';
		$dir       = ( 'hrl' === $atts['dir'] ) ? 'hrl' : 'ltr';
		$shade     = ( 'hatching' === $atts['shade'] ) ? 'hatching' : 'uniform';
		$sep       = max( 0, min( 0.5, (float) $atts['sep'] ) );
		$linesize  = max( 0.5, min( 3, (float) $atts['linesize'] ) );
		$color     = sanitize_hex_color( $atts['color'] );
		$separated = rest_sanitize_boolean( $atts['separated'] );

		$style = 'font-size:' . $fontsize . 'px;';
		if ( $color ) {
			$style .= 'color:' . $color . ';';
		}

		$span_attrs  = ' class="hierojax" data-type="svg"';
		$span_attrs .= ' data-sep="' . esc_attr( (string) $sep ) . '"';
		$span_attrs .= ' data-linesize="' . esc_attr( (string) $linesize ) . '"';
		$span_attrs .= ' data-shadepattern="' . esc_attr( $shade ) . '"';
		if ( 'hrl' === $dir ) {
			$span_attrs .= ' data-dir="hrl"';
		}
		if ( $separated ) {
			$span_attrs .= ' data-separated="true"';
		}
		$span_attrs .= ' style="' . esc_attr( $style ) . '"';

		$lines     = preg_split( '/\r\n|\r|\n/', $content );
		$needs_mdc = false;
		// Wrapper inline: se integra en la línea sin interrumpir el texto.
		$html = '<span class="ftorres-hiero" style="display:inline-block;text-align:' . esc_attr( $align ) . ';">';
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( self::looks_like_unicode( $line ) ) {
				$html .= '<span' . $span_attrs . '>' . esc_html( $line ) . '</span>';
			} else {
				// Transliteración MdC: se convierte en el cliente (data-mdc).
				$needs_mdc = true;
				$html     .= '<span' . $span_attrs . ' data-mdc="' . esc_attr( $line ) . '">' . esc_html( $line ) . '</span>';
			}
		}
		$html .= '</span>';

		self::enqueue_frontend( $needs_mdc );

		return $html;
	}

	/**
	 * ¿El texto es codificación Unicode jeroglífica (U+13000–U+134FF)?
	 *
	 * @param string $text
	 * @return bool
	 */
	private static function looks_like_unicode( $text ) {
		return (bool) preg_match( '/[\x{13000}-\x{134FF}]/u', $text );
	}

	/**
	 * Script inline que fija la URL absoluta de la fuente NewGardiner.
	 *
	 * Usa la fuente empaquetada si existe; si no (p. ej. instalaciones que no
	 * pudieron incluir el .otf por límites de subida), cae a la copia oficial
	 * en la página de proyecto de HieroJax.
	 *
	 * @return string
	 */
	private static function font_url_inline_script() {
		if ( file_exists( FT_HIERO_PLUGIN_DIR . 'assets/fonts/NewGardiner.otf' ) ) {
			$font_url = esc_url_raw(
				plugins_url( 'assets/fonts/NewGardiner.otf', FT_HIERO_PLUGIN_FILE )
			);
		} else {
			$font_url = 'https://nederhof.github.io/hierojax/NewGardiner.otf';
		}
		return 'window.ftorresHieroFontUrl = ' . wp_json_encode( 'url(' . $font_url . ')' ) . ';';
	}

	/**
	 * Script inline que procesa los fragmentos hierojax al cargar la página.
	 *
	 * Si $with_mdc es true, primero convierte los spans con data-mdc
	 * (transliteración MdC) a codificación Unicode usando mdcsyntax, y después
	 * renderiza todos los fragmentos con hierojax.
	 *
	 * @param bool $with_mdc
	 * @return string
	 */
	private static function frontend_process_script( $with_mdc = false ) {
		if ( $with_mdc ) {
			return 'window.addEventListener("DOMContentLoaded",function(){' .
				'if(typeof mdcsyntax!=="undefined"){' .
					'document.querySelectorAll(".hierojax[data-mdc]").forEach(function(s){' .
						'var mdc=s.getAttribute("data-mdc");if(!mdc)return;' .
						'try{var parsed=mdcsyntax.parse(mdc+"\n");' .
							'var parts=parsed&&parsed.parts?parsed.parts:[];' .
							'var uni=parts.map(function(p){return p&&typeof p.cutByColor==="function"?p.cutByColor().map(function(f){return f.toString()}).join(""):String(p);}).join("");' .
							'if(uni){s.textContent=uni;s.removeAttribute("data-mdc");}' .
						'}catch(e){}});' .
				'}' .
				'if(typeof hierojax!=="undefined"){hierojax.processFragments();}' .
			'});';
		}
		return 'window.addEventListener("DOMContentLoaded",function(){if(typeof hierojax!=="undefined"){hierojax.processFragments();}});';
	}
}
