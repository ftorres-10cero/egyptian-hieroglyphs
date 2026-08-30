<?php
/**
 * Herramienta de conversión MdC -> Unicode en el panel de administración.
 *
 * @package EgyptianHieroglyphs
 */

namespace EgyptianHieroglyphs;

defined( 'ABSPATH' ) || exit;

/**
 * Página de administración "Conversor MdC".
 */
class AdminTool {

	/**
	 * Hook de menú.
	 */
	const MENU_SLUG = 'ftorres-hiero-converter';

	/**
	 * Registra los assets de la página del conversor.
	 *
	 * Nota: el menú "Herramientas → Conversor MdC" se eliminó a petición del
	 * usuario; la conversión MdC se realiza desde el constructor de la página
	 * de ajustes. Esta clase se conserva por compatibilidad (el archivo sigue
	 * cargándose), pero ya no registra ninguna página de administración.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Encola los assets solo en la página del conversor.
	 *
	 * @param string $hook Hook actual de la pantalla admin.
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'tools_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		if ( ! wp_script_is( 'ftorres-hiero-mdc', 'registered' ) ) {
			Assets::register();
		}

		// mdcconversion.js incluye renderer + parser; nunca junto a hierojax.js.
		wp_enqueue_script( 'ftorres-hiero-mdc' );
		wp_enqueue_style( 'ftorres-hiero-css' );
		wp_enqueue_script(
			'ftorres-hiero-admin-converter',
			FT_HIERO_PLUGIN_URL . 'assets/admin-converter.js',
			array( 'ftorres-hiero-mdc' ),
			FT_HIERO_VERSION,
			true
		);
	}

	/**
	 * Renderiza la página del conversor.
	 */
	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Conversor de Manuel de Codage (MdC) a Unicode jeroglífico', 'egyptian-hieroglyphs' ); ?></h1>
			<p>
				<?php
				esc_html_e(
					'Escribe una frase en notación MdC (una línea por fragmento) y conviértela a la codificación Unicode que renderiza hierojax. Usa el resultado en el shortcode [hiero] o como referencia para el bloque.',
					'egyptian-hieroglyphs'
				);
				?>
			</p>

			<div id="ft-hiero-converter">
				<h2><?php esc_html_e( 'Texto en MdC', 'egyptian-hieroglyphs' ); ?></h2>
				<textarea id="ft-hiero-input" rows="6" class="large-text code" spellcheck="false">nTr:r&#10;<- mn-xpr-ra ->&#10;ra:Z1*t:Z1</textarea>
				<p>
					<button type="button" id="ft-hiero-convert" class="button button-primary">
						<?php esc_html_e( 'Convertir', 'egyptian-hieroglyphs' ); ?>
					</button>
				</p>

				<div id="ft-hiero-errors" class="notice notice-error inline" hidden></div>
				<div id="ft-hiero-warnings" class="notice notice-warning inline" hidden></div>

				<h2><?php esc_html_e( 'Vista previa', 'egyptian-hieroglyphs' ); ?></h2>
				<div id="ft-hiero-preview" style="font-size:36px; padding:1em; background:#fff; border:1px solid #ccd0d4;"></div>

				<h2><?php esc_html_e( 'Codificación Unicode', 'egyptian-hieroglyphs' ); ?></h2>
				<textarea id="ft-hiero-output" rows="6" class="large-text code" readonly spellcheck="false"></textarea>
				<p>
					<button type="button" id="ft-hiero-copy" class="button">
						<?php esc_html_e( 'Copiar al portapapeles', 'egyptian-hieroglyphs' ); ?>
					</button>
				</p>
			</div>
		</div>
		<?php
	}
}
