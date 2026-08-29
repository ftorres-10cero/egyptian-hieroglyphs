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
			__( 'Jeroglíficos egipcios (MdC)', 'egyptian-hieroglyphs' ),
			__( 'Jeroglíficos Egipcios', 'egyptian-hieroglyphs' ),
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
			<h1><?php esc_html_e( 'Jeroglíficos egipcios (MdC) — ajustes', 'egyptian-hieroglyphs' ); ?></h1>
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

			<hr />

			<?php self::render_help(); ?>
		</div>
		<?php
	}

	/**
	 * Ayuda extensa de la página de ajustes (todo traducible).
	 */
	public static function render_help() {
		$td = 'egyptian-hieroglyphs';
		?>
		<h2><?php esc_html_e( 'Ayuda', $td ); ?></h2>

		<h3><?php esc_html_e( 'Primeros pasos', $td ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'Este plugin renderiza jeroglíficos egipcios a partir de texto escrito en Manuel de Codage (MdC), el estándar usado por egiptólogos para codificar jeroglíficos. Escribe el MdC en el bloque "Jeroglíficos egipcios (MdC)" del editor y la vista previa se genera al instante como SVG.',
				$td
			);
			?>
		</p>
		<p>
			<?php
			esc_html_e(
				'Los signos siguen la lista de Gardiner: cada uno tiene un código (p. ej. nTr = dios, ra = sol) y se combinan con separadores para formar cuadraturas, grupos y palabras.',
				$td
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Sintaxis de MdC', $td ); ?></h3>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Sintaxis', $td ); ?></th>
					<th><?php esc_html_e( 'Significado', $td ); ?></th>
					<th><?php esc_html_e( 'Ejemplo', $td ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>:</code></td>
					<td><?php esc_html_e( 'Agrupa signos en vertical (cuadratura)', $td ); ?></td>
					<td><code>nTr:r</code></td>
				</tr>
				<tr>
					<td><code>*</code></td>
					<td><?php esc_html_e( 'Agrupa signos en horizontal', $td ); ?></td>
					<td><code>ra:Z1*t:Z1</code></td>
				</tr>
				<tr>
					<td><code>-</code></td>
					<td><?php esc_html_e( 'Yuxtapone signos en la misma línea', $td ); ?></td>
					<td><code>Htp-di-nswt</code></td>
				</tr>
				<tr>
					<td><code>&lt;</code> … <code>&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho / cerramiento', $td ); ?></td>
					<td><code>&lt;- mn-xpr-ra -&gt;</code></td>
				</tr>
				<tr>
					<td><code>[&amp; … &amp;]</code></td>
					<td><?php esc_html_e( 'Corchetes literales (no cartucho)', $td ); ?></td>
					<td><code>[&amp;nTr&amp;]</code></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'espacio', $td ); ?></td>
					<td><?php esc_html_e( 'Separa palabras', $td ); ?></td>
					<td><code>Htp-di-nswt Wsjr</code></td>
				</tr>
				<tr>
					<td><code>\red</code></td>
					<td><?php esc_html_e( 'Marca el texto en rojo (papiros)', $td ); ?></td>
					<td><code>\red-anx</code></td>
				</tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Cartuchos', $td ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %1$s and %2$s are MdC examples, %3$s is the rendered cartouche. */
				esc_html__( 'Los cartuchos (nombres reales dentro de un óvalo) se escriben con %1$s. Ejemplo: %2$s renderiza %3$s. Usa %4$s para el anillo shen.', $td ),
				'<code>&lt;…&gt;</code>',
				'<code>&lt;- mn-xpr-ra -&gt;</code>',
				'<strong>𓍹𓐼𓏠𓆣𓇳𓐽𓍺</strong>',
				'<code>&lt;S- anx -&gt;</code>'
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Ejemplos completos', $td ); ?></h3>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Texto MdC', $td ); ?></th>
					<th><?php esc_html_e( 'Resultado', $td ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td><code>nTr:r</code></td><td><?php echo esc_html( '𓊹𓐰𓂋' ); ?></td></tr>
				<tr><td><code>ra:Z1*t:Z1</code></td><td><?php echo esc_html( '𓇳𓐰𓏤𓐱𓏏𓐰𓏤' ); ?></td></tr>
				<tr><td><code>&lt;- mn-xpr-ra -&gt;</code></td><td><?php echo esc_html( '𓍹𓐼𓏠𓆣𓇳𓐽𓍺' ); ?></td></tr>
				<tr><td><code>&lt;- ra-ms-sw -&gt;</code></td><td><?php echo esc_html( '𓍹𓐼𓇳𓄟𓇓𓐽𓍺' ); ?></td></tr>
				<tr><td><code>&lt;S- anx -&gt;</code></td><td><?php echo esc_html( '𓉘𓐼𓋹𓐽𓊂' ); ?></td></tr>
				<tr><td><code>i*w</code></td><td><?php echo esc_html( '𓇋𓐱𓅱' ); ?></td></tr>
			</tbody>
		</table>
		<p>
			<?php
			printf(
				/* translators: %s is a tool menu name. */
				esc_html__( 'Si no recuerdas la codificación Unicode de un fragmento, usa la herramienta %s para convertir MdC a Unicode con un clic y copiarlo.', $td ),
				'<strong>' . esc_html__( 'Herramientas → Conversor MdC', $td ) . '</strong>'
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Shortcode [hiero]', $td ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'El shortcode se usa en el editor clásico y acepta la codificación Unicode jeroglífica (una línea por fragmento):',
				$td
			);
			?>
		</p>
		<pre style="max-width:760px; background:#f6f7f7; padding:10px; border:1px solid #ccd0d4; overflow:auto;">[hiero fontsize="42" align="center"]𓂋𓄿𓐰𓏤[/hiero]</pre>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Atributo', $td ); ?></th>
					<th><?php esc_html_e( 'Valores', $td ); ?></th>
					<th><?php esc_html_e( 'Descripción', $td ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td><code>fontsize</code></td><td>8–200</td><td><?php esc_html_e( 'Tamaño de los signos (por defecto: el de los ajustes).', $td ); ?></td></tr>
				<tr><td><code>color</code></td><td>#rrggbb</td><td><?php esc_html_e( 'Color de los signos (vacío = hereda el tema).', $td ); ?></td></tr>
				<tr><td><code>align</code></td><td>left · center · right</td><td><?php esc_html_e( 'Alineación del bloque.', $td ); ?></td></tr>
				<tr><td><code>dir</code></td><td>ltr · hrl</td><td><?php esc_html_e( 'Dirección: izquierda→derecha o derecha→izquierda.', $td ); ?></td></tr>
				<tr><td><code>sep</code></td><td>0 – 0.5</td><td><?php esc_html_e( 'Separación entre signos.', $td ); ?></td></tr>
				<tr><td><code>shade</code></td><td>uniform · hatching</td><td><?php esc_html_e( 'Patrón de sombreado de los signos dañados.', $td ); ?></td></tr>
				<tr><td><code>linesize</code></td><td>0.5 – 3</td><td><?php esc_html_e( 'Altura de línea (multiplicador).', $td ); ?></td></tr>
				<tr><td><code>separated</code></td><td>true · false</td><td><?php esc_html_e( 'Permitir saltos de línea en fragmentos largos.', $td ); ?></td></tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Consejos y preguntas frecuentes', $td ); ?></h3>
		<ul style="max-width:760px; list-style: disc; padding-left: 1.4em;">
			<li>
				<?php
				printf(
					/* translators: %s is a path. */
					esc_html__( 'La fuente NewGardiner se sirve desde el propio plugin. Si el hosting no permitió incluirla (límite de subida), el plugin la carga automáticamente desde la CDN oficial de HieroJax (%s).', $td ),
					'<code>nederhof.github.io/hierojax</code>'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Si un código MdC no está aún codificado en Unicode, la vista previa muestra el carácter � y el editor avisa. Revisa el código o usa la codificación Unicode manual.',
					$td
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Los scripts y la fuente solo se cargan en páginas que contienen jeroglíficos, para no ralentizar el resto del sitio.',
					$td
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %s is a menu path. */
					esc_html__( 'El bloque se personaliza por cada inserción desde su panel de opciones; los valores por defecto de esta página solo afectan a bloques nuevos y al shortcode.', $td ),
					''
				);
				?>
			</li>
		</ul>

		<h3><?php esc_html_e( 'Créditos', $td ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %1$s and %2$s are project names with links. */
				esc_html__( 'Motor de render y conversor: %1$s (GPL-3.0). Fuente: %2$s (SIL OFL 1.1). Ambos de Mark-Jan Nederhof (Universidad de St Andrews).', $td ),
				'<a href="https://github.com/nederhof/hierojax" target="_blank" rel="noopener">HieroJax</a>',
				'<a href="https://github.com/nederhof/newgardiner" target="_blank" rel="noopener">NewGardiner</a>'
			);
			?>
		</p>
		<?php
	}
}
