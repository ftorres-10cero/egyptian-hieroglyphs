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
		// Carga la fuente NewGardiner para que los ejemplos de la ayuda se vean bien.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Encola la fuente, el estilo y la herramienta de composición solo en la
	 * página de ajustes.
	 *
	 * @param string $hook Hook de la pantalla admin.
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'settings_page_ftorres-hiero-settings' !== $hook ) {
			return;
		}
		if ( ! wp_style_is( 'ftorres-hiero-css', 'registered' ) ) {
			Assets::register();
		}
		wp_enqueue_style( 'ftorres-hiero-css' );
		// Renderiza los ejemplos de la ayuda como SVG (igual que el conversor).
		wp_enqueue_script( 'ftorres-hiero-runtime' );
		wp_add_inline_script(
			'ftorres-hiero-runtime',
			'window.addEventListener("DOMContentLoaded",function(){if(typeof hierojax!=="undefined"){hierojax.processFragments();}});',
			'after'
		);

		// Constructor de textos MdC: conversor + catálogo de signos + UI.
		wp_enqueue_script( 'ftorres-hiero-mdc' );
		wp_enqueue_style(
			'ftorres-hiero-composer',
			FT_HIERO_PLUGIN_URL . 'assets/admin-composer.css',
			array(),
			FT_HIERO_VERSION
		);
		wp_enqueue_script(
			'ftorres-hiero-composer',
			FT_HIERO_PLUGIN_URL . 'assets/admin-composer.js',
			array( 'ftorres-hiero-mdc' ),
			FT_HIERO_VERSION,
			true
		);

		$catalog_path = FT_HIERO_PLUGIN_DIR . 'assets/sign-catalog.json';
		$catalog      = file_exists( $catalog_path ) ? file_get_contents( $catalog_path ) : '[]';
		$i18n         = array(
			'noSigns'         => __( 'Sin signos en esta categoría.', 'egyptian-hieroglyphs' ),
			'previewHint'     => __( 'La vista previa aparecerá aquí…', 'egyptian-hieroglyphs' ),
			'converterMissing' => __( 'El conversor MdC no está disponible.', 'egyptian-hieroglyphs' ),
		);
		wp_add_inline_script(
			'ftorres-hiero-composer',
			'window.ftHieroSigns = ' . $catalog . ';' .
			'window.ftHieroI18n = ' . wp_json_encode( $i18n ) . ';',
			'before'
		);
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

			<?php self::render_composer(); ?>

			<hr />

			<?php self::render_help(); ?>
		</div>
		<?php
	}

	/**
	 * Constructor de textos MdC: paleta de signos + botones de estructura.
	 */
	public static function render_composer() {
		$td = 'egyptian-hieroglyphs';
		?>
		<h2><?php esc_html_e( 'Constructor de textos MdC', $td ); ?></h2>
		<p>
			<?php
			esc_html_e(
				'Elige los signos de la paleta (lista de Gardiner) y usa los botones para insertar cartuchos, cuadraturas, grupos y separadores. El texto resultante se puede usar directamente en el bloque o en el shortcode [hiero].',
				$td
			);
			?>
		</p>

		<div class="ft-hiero-composer">
			<div class="composer-toolbar">
				<button type="button" class="button button-small" id="ft-hiero-act-cartouche" title="<?php esc_attr_e( 'Inserta un cartucho vacío', $td ); ?>"><?php esc_html_e( 'Cartucho', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-cartouche-open" title="<?php esc_attr_e( 'Abre un cartucho', $td ); ?>"><?php esc_html_e( 'Abrir cartucho', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-cartouche-close" title="<?php esc_attr_e( 'Cierra un cartucho', $td ); ?>"><?php esc_html_e( 'Cerrar cartucho', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-shen" title="<?php esc_attr_e( 'Inserta un anillo shen', $td ); ?>"><?php esc_html_e( 'Shen', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-quadrat" title="<?php esc_attr_e( 'Cuadratura vertical (apila signos)', $td ); ?>"><?php esc_html_e( 'Cuadratura «:»', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-group" title="<?php esc_attr_e( 'Grupo horizontal', $td ); ?>"><?php esc_html_e( 'Grupo «*»', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-juxtapose" title="<?php esc_attr_e( 'Yuxtapone signos', $td ); ?>"><?php esc_html_e( 'Yuxta «-»', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-space" title="<?php esc_attr_e( 'Separa palabras', $td ); ?>"><?php esc_html_e( 'Espacio', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-fragment" title="<?php esc_attr_e( 'Termina el fragmento actual', $td ); ?>"><?php esc_html_e( 'Fin fragm. «!»', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-red" title="<?php esc_attr_e( 'Marca el texto en rojo', $td ); ?>"><?php esc_html_e( 'Rojo «\\red»', $td ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-newline" title="<?php esc_attr_e( 'Nueva línea (fragmento)', $td ); ?>"><?php esc_html_e( 'Nueva línea', $td ); ?></button>
			</div>

			<div class="composer-layout">
				<div class="composer-palette">
					<select id="ft-hiero-composer-cats"></select>
					<input type="search" id="ft-hiero-composer-search"
						placeholder="<?php esc_attr_e( 'Buscar código o transliteración…', $td ); ?>" />
					<div class="sign-grid" id="ft-hiero-composer-grid"></div>
				</div>

				<div class="composer-workspace">
					<label for="ft-hiero-composer-mdc"><strong><?php esc_html_e( 'Texto en notación MdC', $td ); ?></strong></label>
					<textarea id="ft-hiero-composer-mdc" rows="5"
						placeholder="<?php esc_attr_e( 'nTr anx', $td ); ?>"></textarea>
					<div class="composer-actions">
						<button type="button" class="button" id="ft-hiero-copy-mdc"><?php esc_html_e( 'Copiar MdC', $td ); ?></button>
						<button type="button" class="button" id="ft-hiero-copy-uni"><?php esc_html_e( 'Copiar Unicode', $td ); ?></button>
						<button type="button" class="button" id="ft-hiero-composer-clear"><?php esc_html_e( 'Limpiar', $td ); ?></button>
					</div>
					<div class="preview-box" id="ft-hiero-composer-preview"></div>
				</div>
			</div>
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

		<h3><?php esc_html_e( 'Cómo escribir jeroglíficos', $td ); ?></h3>
		<ol style="max-width:760px; list-style: decimal; padding-left: 1.6em;">
			<li>
				<?php
				printf(
					/* translators: %s is the block name. */
					esc_html__( 'En el editor de entradas, añade el bloque «%s».', $td ),
					'<strong>Jeroglíficos egipcios (MdC)</strong>'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Escribe en el cuadro «Texto en notación MdC» los códigos de los signos, combinándolos con los separadores de la referencia siguiente.',
					$td
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'La vista previa se genera al instante. En el panel lateral del bloque ajusta tamaño, color, dirección, separación y sombreado.',
					$td
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Publica la entrada: los jeroglíficos se renderizan como SVG nítido, ampliable y copiable para tus lectores.',
					$td
				);
				?>
			</li>
		</ol>
		<p>
			<?php
			esc_html_e(
				'Los signos siguen la lista de Gardiner: cada uno tiene un código (p. ej. nTr = dios, ra = sol) y se combinan con separadores para formar cuadraturas, grupos y palabras.',
				$td
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Referencia de la sintaxis (tags)', $td ); ?></h3>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tag', $td ); ?></th>
					<th><?php esc_html_e( 'Qué hace', $td ); ?></th>
					<th><?php esc_html_e( 'Ejemplo', $td ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>A1</code>, <code>nTr</code>, <code>ra</code>…</td>
					<td><?php esc_html_e( 'Código de un signo (lista de Gardiner). Sin separador, los signos se suceden en la línea.', $td ); ?></td>
					<td><code>nTr anx</code></td>
				</tr>
				<tr>
					<td><code>:</code></td>
					<td><?php esc_html_e( 'Apila signos en vertical (cuadratura)', $td ); ?></td>
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
					<td><code>Htp-di</code></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'espacio', $td ); ?></td>
					<td><?php esc_html_e( 'Separa palabras', $td ); ?></td>
					<td><code>nTr anx</code></td>
				</tr>
				<tr>
					<td><code>!</code></td>
					<td><?php esc_html_e( 'Termina el fragmento actual', $td ); ?></td>
					<td><code>nTr:r!anx</code></td>
				</tr>
				<tr>
					<td><code>&lt;</code> … <code>&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho / cerramiento', $td ); ?></td>
					<td><code>&lt;- mn-xpr-ra -&gt;</code></td>
				</tr>
				<tr>
					<td><code>&lt;S- … -&gt;</code></td>
					<td><?php esc_html_e( 'Anillo shen', $td ); ?></td>
					<td><code>&lt;S- anx -&gt;</code></td>
				</tr>
				<tr>
					<td><code>&amp;</code></td>
					<td><?php esc_html_e( 'Superpone un signo sobre otro', $td ); ?></td>
					<td><code>A1&amp;A2</code></td>
				</tr>
				<tr>
					<td><code>nTr\r1</code>, <code>nTr\R90</code>…</td>
					<td><?php esc_html_e( 'Rota el signo anterior (sufijo tras el signo)', $td ); ?></td>
					<td><code>nTr\r2</code></td>
				</tr>
				<tr>
					<td><code>anx\red</code></td>
					<td><?php esc_html_e( 'Marca el texto en rojo (papiros; sufijo)', $td ); ?></td>
					<td><code>anx\red</code></td>
				</tr>
				<tr>
					<td><code>[&amp; … &amp;]</code></td>
					<td><?php esc_html_e( 'Corchetes literales (no es un cartucho)', $td ); ?></td>
					<td><code>[&amp;nTr&amp;]</code></td>
				</tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Cartuchos (todas las variantes)', $td ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'Los cartuchos (nombres reales dentro de un óvalo) se escriben con los cerramientos < y >. El óvalo se adapta al contenido: si apilas signos con ":", el cartucho se vuelve vertical.',
				$td
			);
			?>
		</p>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tag', $td ); ?></th>
					<th><?php esc_html_e( 'Qué hace', $td ); ?></th>
					<th><?php esc_html_e( 'Resultado', $td ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>&lt;- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho simple', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓋹𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- mn-xpr-ra -&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho con un nombre real (Menkheperre)', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓏠𓆣𓇳𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- nTr:r -&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho con una cuadratura (más alto)', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓊹𓐰𓂋𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- nTr:r:x:t -&gt;</code></td>
					<td><?php esc_html_e( 'CARTUCHO VERTICAL: signos apilados; el óvalo crece en altura', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓊹𓐰𓂋𓐰𓐍𓐰𓏏𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- Htp-di -&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho con dos palabras yuxtapuestas', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓊵𓏙𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;S- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Anillo shen', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓉘𓐼𓋹𓐽𓊂</span></td>
				</tr>
				<tr>
					<td><code>&lt;F- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Cerramiento amurallado', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊈𓐾𓋹𓐿𓊉</span></td>
				</tr>
				<tr>
					<td><code>&lt;H- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho, variante H', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓉘𓐼𓋹𓐽𓉜</span></td>
				</tr>
				<tr>
					<td><code>&lt;-s-anx-&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho con un signo inicial dentro (complemento fonético: s, b, h, 1…)', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓋴𓋹𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;-b-anx-&gt;</code></td>
					<td><?php esc_html_e( 'Cartucho con b inicial', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓃀𓋹𓐽𓍺</span></td>
				</tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Orientación, rotación y sombreado', $td ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'Para cambiar la orientación de toda la línea usa la opción "Dirección" del bloque (derecha → izquierda, como el egipcio clásico). Para rotar o sombrear un signo concreto, los modificadores van DESPUÉS del signo (sufijo):',
				$td
			);
			?>
		</p>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tag', $td ); ?></th>
					<th><?php esc_html_e( 'Qué hace', $td ); ?></th>
					<th><?php esc_html_e( 'Resultado', $td ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>nTr\r1</code>, <code>\r2</code>, <code>\r3</code></td>
					<td><?php esc_html_e( 'Rota el signo anterior (variantes de la fuente)', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹︂</span></td>
				</tr>
				<tr>
					<td><code>nTr\R90</code>, <code>\R180</code>, <code>\R270</code></td>
					<td><?php esc_html_e( 'Rota el signo anterior 90°, 180° o 270°', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹︁</span></td>
				</tr>
				<tr>
					<td><code>nTr\h</code>, <code>nTr\v</code>, <code>nTr\t1</code></td>
					<td><?php esc_html_e( 'Transposiciones horizontal, vertical y de reordenación', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹𓑀</span></td>
				</tr>
				<tr>
					<td><code>nTr\shading1234</code></td>
					<td><?php esc_html_e( 'Sombreado del signo anterior (1-4 = esquinas)', $td ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹𓑕</span></td>
				</tr>
			</tbody>
		</table>
		<p>
			<?php
			printf(
				/* translators: %s is an example. */
				esc_html__( 'También puedes marcar un fragmento en rojo (papiros) escribiendo %s al final del texto.', $td ),
				'<code>\\red</code>'
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
				<tr><td><code>nTr:r</code></td><td><span class="hierojax" style="font-size:32px;">𓊹𓐰𓂋</span></td></tr>
				<tr><td><code>ra:Z1*t:Z1</code></td><td><span class="hierojax" style="font-size:32px;">𓇳𓐰𓏤𓐱𓏏𓐰𓏤</span></td></tr>
				<tr><td><code>&lt;- mn-xpr-ra -&gt;</code></td><td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓏠𓆣𓇳𓐽𓍺</span></td></tr>
				<tr><td><code>&lt;- ra-ms-sw -&gt;</code></td><td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓇳𓄟𓇓𓐽𓍺</span></td></tr>
				<tr><td><code>&lt;S- anx -&gt;</code></td><td><span class="hierojax" style="font-size:32px;">𓉘𓐼𓋹𓐽𓊂</span></td></tr>
				<tr><td><code>i*w</code></td><td><span class="hierojax" style="font-size:32px;">𓇋𓐱𓅱</span></td></tr>
			</tbody>
		</table>
		<p>
			<?php
			printf(
				/* translators: %s is the composer tool name. */
				esc_html__( 'Si no recuerdas la codificación Unicode de un fragmento, usa el %s de esta misma página para componer el texto y copiarlo.', $td ),
				'<strong>' . esc_html__( 'Constructor de textos MdC', $td ) . '</strong>'
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Shortcode [hiero]', $td ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'El shortcode se usa en el editor clásico. Acepta la transliteración MdC directamente (se convierte al vuelo) o la codificación Unicode jeroglífica, y se integra en el texto sin romperlo:',
				$td
			);
			?>
		</p>
		<pre style="max-width:760px; background:#f6f7f7; padding:10px; border:1px solid #ccd0d4; overflow:auto;">El rey [hiero]ra-ms-sw[/hiero] erigió un templo.   ← MdC, se convierte solo</pre>
		<pre style="max-width:760px; background:#f6f7f7; padding:10px; border:1px solid #ccd0d4; overflow:auto;">[hiero fontsize="42" align="center" color="#8B0000"]anx[/hiero]</pre>
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
