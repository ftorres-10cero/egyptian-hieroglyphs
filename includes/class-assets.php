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
	 */
	public static function enqueue_frontend() {
		if ( self::$frontend_enqueued ) {
			return;
		}
		self::$frontend_enqueued = true;

		if ( ! wp_script_is( 'ftorres-hiero-runtime', 'registered' ) ) {
			self::register();
		}

		wp_enqueue_script( 'ftorres-hiero-runtime' );
		wp_enqueue_style( 'ftorres-hiero-css' );
		wp_enqueue_style(
			'ftorres-hiero-block-style',
			FT_HIERO_PLUGIN_URL . 'build/style-index.css',
			array(),
			FT_HIERO_VERSION
		);

		wp_add_inline_script(
			'ftorres-hiero-runtime',
			self::frontend_process_script(),
			'after'
		);
	}

	/**
	 * Shortcode [hiero]...[/hiero]: contenido en codificación Unicode
	 * (una línea por fragmento). El texto MdC se convierte con el bloque o con
	 * la herramienta del administrador.
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

		self::enqueue_frontend();

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

		$lines = preg_split( '/\r\n|\r|\n/', $content );
		$html  = '<div class="ftorres-hiero" style="text-align:' . esc_attr( $align ) . ';">';
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$html .= '<span' . $span_attrs . '>' . esc_html( $line ) . '</span>';
		}
		$html .= '</div>';

		return $html;
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
	 * @return string
	 */
	private static function frontend_process_script() {
		return 'window.addEventListener("DOMContentLoaded",function(){if(typeof hierojax!=="undefined"){hierojax.processFragments();}});';
	}
}
