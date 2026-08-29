<?php
/**
 * Ajustes del plugin: tamaño y color por defecto de los jeroglíficos.
 *
 * @package EgyptianHieroglyphs
 */

namespace EgyptianHieroglyphs;

defined( 'ABSPATH' ) || exit;

/**
 * Página de ajustes y opciones por defecto.
 */
class Settings {

	/**
	 * Opción: tamaño de signo por defecto.
	 */
	const OPT_FONT_SIZE = 'ft_hiero_default_font_size';

	/**
	 * Opción: color por defecto (vacío = hereda el del tema).
	 */
	const OPT_COLOR = 'ft_hiero_default_color';

	/**
	 * Registra la página y las opciones.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		// Las peticiones REST no lanzan admin_init; necesario para /wp/v2/settings.
		add_action( 'rest_api_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Añade la página de ajustes.
	 */
	public static function add_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'Jeroglíficos (MdC)', 'egyptian-hieroglyphs' ),
			__( 'Jeroglíficos (MdC)', 'egyptian-hieroglyphs' ),
			'manage_options',
			'ftorres-hiero-settings',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Registra las opciones (visibles en el REST /wp/v2/settings para el editor).
	 */
	public static function register_settings() {
		register_setting(
			'ft_hiero',
			self::OPT_FONT_SIZE,
			array(
				'type'              => 'number',
				'default'           => 36,
				'sanitize_callback' => array( __CLASS__, 'sanitize_font_size' ),
				'show_in_rest'      => true,
			)
		);

		register_setting(
			'ft_hiero',
			self::OPT_COLOR,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( __CLASS__, 'sanitize_color' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Sanitiza el tamaño de fuente.
	 *
	 * @param mixed $value Valor enviado.
	 * @return int
	 */
	public static function sanitize_font_size( $value ) {
		return max( 8, min( 200, absint( $value ) ) );
	}

	/**
	 * Sanitiza el color (vacío o hex válido).
	 *
	 * @param mixed $value Valor enviado.
	 * @return string
	 */
	public static function sanitize_color( $value ) {
		$value = sanitize_hex_color( (string) $value );
		return $value ? $value : '';
	}

	/**
	 * Tamaño por defecto (int).
	 *
	 * @return int
	 */
	public static function default_font_size() {
		return absint( get_option( self::OPT_FONT_SIZE, 36 ) );
	}

	/**
	 * Color por defecto ('' = hereda).
	 *
	 * @return string
	 */
	public static function default_color() {
		return (string) get_option( self::OPT_COLOR, '' );
	}

	/**
	 * Renderiza la página de ajustes.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Jeroglíficos (MdC) — ajustes', 'egyptian-hieroglyphs' ); ?></h1>
			<p>
				<?php
				esc_html_e(
					'Valores por defecto aplicados a los bloques nuevos y al shortcode [hiero]. Cada bloque puede personalizar tamaño y color individualmente.',
					'egyptian-hieroglyphs'
				);
				?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ft_hiero' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( self::OPT_FONT_SIZE ); ?>">
								<?php esc_html_e( 'Tamaño de los signos', 'egyptian-hieroglyphs' ); ?>
							</label>
						</th>
						<td>
							<input type="number" min="8" max="200" step="1"
								name="<?php echo esc_attr( self::OPT_FONT_SIZE ); ?>"
								id="<?php echo esc_attr( self::OPT_FONT_SIZE ); ?>"
								value="<?php echo esc_attr( (string) self::default_font_size() ); ?>" class="small-text" />
							<p class="description">
								<?php esc_html_e( 'Tamaño en píxeles de la altura de los signos (8–200).', 'egyptian-hieroglyphs' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( self::OPT_COLOR ); ?>">
								<?php esc_html_e( 'Color de los signos', 'egyptian-hieroglyphs' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								name="<?php echo esc_attr( self::OPT_COLOR ); ?>"
								id="<?php echo esc_attr( self::OPT_COLOR ); ?>"
								value="<?php echo esc_attr( self::default_color() ); ?>"
								class="regular-text" placeholder="#000000" />
							<p class="description">
								<?php esc_html_e( 'Color en formato hexadecimal. Vacío = hereda el color del tema.', 'egyptian-hieroglyphs' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
