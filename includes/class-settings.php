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
		// Constructor de textos MdC + ejemplos de la ayuda.
		// IMPORTANTE: se carga SOLO mdcconversion.js (incluye el renderer
		// completo de hierojax + el parser). Nunca junto a hierojax.js:
		// ambos declaran las mismas clases globales y el segundo falla.
		wp_enqueue_script( 'ftorres-hiero-mdc' );
		wp_add_inline_script(
			'ftorres-hiero-mdc',
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
			'noSigns'         => __( 'No signs in this category.', 'egyptian-hieroglyphs-mdc' ),
			'previewHint'     => __( 'The preview will appear here…', 'egyptian-hieroglyphs-mdc' ),
			'converterMissing' => __( 'The MdC converter is not available.', 'egyptian-hieroglyphs-mdc' ),
			// Categorías de la lista de signos de Gardiner.
			'cats'             => array(
				'A' => __( 'Man and his occupations', 'egyptian-hieroglyphs-mdc' ),
				'B' => __( 'Woman and her occupations', 'egyptian-hieroglyphs-mdc' ),
				'C' => __( 'Deities and beings', 'egyptian-hieroglyphs-mdc' ),
				'D' => __( 'Parts of the human body', 'egyptian-hieroglyphs-mdc' ),
				'E' => __( 'Mammals', 'egyptian-hieroglyphs-mdc' ),
				'F' => __( 'Parts of mammals', 'egyptian-hieroglyphs-mdc' ),
				'G' => __( 'Birds', 'egyptian-hieroglyphs-mdc' ),
				'H' => __( 'Parts of birds', 'egyptian-hieroglyphs-mdc' ),
				'I' => __( 'Amphibians and reptiles', 'egyptian-hieroglyphs-mdc' ),
				'K' => __( 'Fishes and parts of fishes', 'egyptian-hieroglyphs-mdc' ),
				'L' => __( 'Invertebrates', 'egyptian-hieroglyphs-mdc' ),
				'M' => __( 'Trees and plants', 'egyptian-hieroglyphs-mdc' ),
				'N' => __( 'Sky, earth and water', 'egyptian-hieroglyphs-mdc' ),
				'O' => __( 'Buildings and parts of buildings', 'egyptian-hieroglyphs-mdc' ),
				'P' => __( 'Ships and parts of ships', 'egyptian-hieroglyphs-mdc' ),
				'Q' => __( 'Furniture and utensils', 'egyptian-hieroglyphs-mdc' ),
				'R' => __( 'Temple furniture and sacred emblems', 'egyptian-hieroglyphs-mdc' ),
				'S' => __( 'Crowns, dress and weapons', 'egyptian-hieroglyphs-mdc' ),
				'T' => __( 'Warfare and hunting', 'egyptian-hieroglyphs-mdc' ),
				'U' => __( 'Agriculture, crafts and professions', 'egyptian-hieroglyphs-mdc' ),
				'V' => __( 'Rope, fibre, baskets and vessels', 'egyptian-hieroglyphs-mdc' ),
				'W' => __( 'Vessels of stone and earthenware', 'egyptian-hieroglyphs-mdc' ),
				'X' => __( 'Loaves and cakes', 'egyptian-hieroglyphs-mdc' ),
				'Y' => __( 'Writing, games and music', 'egyptian-hieroglyphs-mdc' ),
				'Z' => __( 'Strokes and geometrical figures', 'egyptian-hieroglyphs-mdc' ),
			),
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
			__( 'Egyptian Hieroglyphs (MdC)', 'egyptian-hieroglyphs-mdc' ),
			__( 'Egyptian Hieroglyphs', 'egyptian-hieroglyphs-mdc' ),
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
			<h1><?php esc_html_e( 'Egyptian Hieroglyphs (MdC) — settings', 'egyptian-hieroglyphs-mdc' ); ?></h1>
			<p>
				<?php
				esc_html_e(
					'Defaults applied to new blocks and the [hiero] shortcode. Each block can customize size and color individually.',
					'egyptian-hieroglyphs-mdc'
				);
				?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ft_hiero' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( self::OPT_FONT_SIZE ); ?>">
								<?php esc_html_e( 'Sign size', 'egyptian-hieroglyphs-mdc' ); ?>
							</label>
						</th>
						<td>
							<input type="number" min="8" max="200" step="1"
								name="<?php echo esc_attr( self::OPT_FONT_SIZE ); ?>"
								id="<?php echo esc_attr( self::OPT_FONT_SIZE ); ?>"
								value="<?php echo esc_attr( (string) self::default_font_size() ); ?>" class="small-text" />
							<p class="description">
								<?php esc_html_e( 'Height of the signs in pixels (8–200).', 'egyptian-hieroglyphs-mdc' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( self::OPT_COLOR ); ?>">
								<?php esc_html_e( 'Sign color', 'egyptian-hieroglyphs-mdc' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								name="<?php echo esc_attr( self::OPT_COLOR ); ?>"
								id="<?php echo esc_attr( self::OPT_COLOR ); ?>"
								value="<?php echo esc_attr( self::default_color() ); ?>"
								class="regular-text" placeholder="#000000" />
							<p class="description">
								<?php esc_html_e( 'Color in hexadecimal format. Empty = inherits the theme color.', 'egyptian-hieroglyphs-mdc' ); ?>
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
		?>
		<h2><?php esc_html_e( 'MdC text composer', 'egyptian-hieroglyphs-mdc' ); ?></h2>
		<p>
			<?php
			esc_html_e(
				'Pick signs from the palette (Gardiner list) and use the buttons to insert cartouches, quadrats, groups and separators. The resulting text can be used directly in the block or in the [hiero] shortcode.',
				'egyptian-hieroglyphs-mdc'
			);
			?>
		</p>

		<div class="ft-hiero-composer">
			<div class="composer-toolbar">
				<button type="button" class="button button-small" id="ft-hiero-act-cartouche" title="<?php esc_attr_e( 'Insert an empty cartouche', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Cartouche', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-cartouche-open" title="<?php esc_attr_e( 'Open a cartouche', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Open cartouche', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-cartouche-close" title="<?php esc_attr_e( 'Close a cartouche', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Close cartouche', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-shen" title="<?php esc_attr_e( 'Insert a shen ring', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Shen', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-quadrat" title="<?php esc_attr_e( 'Vertical quadrat (stacks signs)', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Quadrat «:»', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-group" title="<?php esc_attr_e( 'Horizontal group', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Group «*»', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-juxtapose" title="<?php esc_attr_e( 'Juxtaposes signs', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Juxta «-»', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-space" title="<?php esc_attr_e( 'Separates words', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Space', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-fragment" title="<?php esc_attr_e( 'Ends the current fragment', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'End frag. «!»', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-red" title="<?php esc_attr_e( 'Marks the text red', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'Red «\\red»', 'egyptian-hieroglyphs-mdc' ); ?></button>
				<button type="button" class="button button-small" id="ft-hiero-act-newline" title="<?php esc_attr_e( 'New line (fragment)', 'egyptian-hieroglyphs-mdc' ); ?>"><?php esc_html_e( 'New line', 'egyptian-hieroglyphs-mdc' ); ?></button>
			</div>

			<div class="composer-layout">
				<div class="composer-palette">
					<select id="ft-hiero-composer-cats"></select>
					<input type="search" id="ft-hiero-composer-search"
						placeholder="<?php esc_attr_e( 'Search code or transliteration…', 'egyptian-hieroglyphs-mdc' ); ?>" />
					<div class="sign-grid" id="ft-hiero-composer-grid"></div>
				</div>

				<div class="composer-workspace">
					<label for="ft-hiero-composer-mdc"><strong><?php esc_html_e( 'Text in MdC notation', 'egyptian-hieroglyphs-mdc' ); ?></strong></label>
					<textarea id="ft-hiero-composer-mdc" rows="5"
						placeholder="<?php esc_attr_e( 'nTr anx', 'egyptian-hieroglyphs-mdc' ); ?>"></textarea>
					<div class="composer-actions">
						<button type="button" class="button" id="ft-hiero-copy-mdc"><?php esc_html_e( 'Copy MdC', 'egyptian-hieroglyphs-mdc' ); ?></button>
						<button type="button" class="button" id="ft-hiero-copy-uni"><?php esc_html_e( 'Copy Unicode', 'egyptian-hieroglyphs-mdc' ); ?></button>
						<button type="button" class="button" id="ft-hiero-composer-clear"><?php esc_html_e( 'Clear', 'egyptian-hieroglyphs-mdc' ); ?></button>
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
		?>
		<h2><?php esc_html_e( 'Help', 'egyptian-hieroglyphs-mdc' ); ?></h2>

		<h3><?php esc_html_e( 'How to write hieroglyphs', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<ol style="max-width:760px; list-style: decimal; padding-left: 1.6em;">
			<li>
				<?php
				printf(
					/* translators: %s is the block name. */
					esc_html__( 'In the post editor, add the "%s" block.', 'egyptian-hieroglyphs-mdc' ),
					'<strong>' . esc_html__( 'Egyptian Hieroglyphs (MdC)', 'egyptian-hieroglyphs-mdc' ) . '</strong>'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Type the sign codes in the "MdC notation" box, combining them with the separators from the reference below.',
					'egyptian-hieroglyphs-mdc'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'The preview renders instantly. In the block sidebar adjust size, color, direction, separation and shading.',
					'egyptian-hieroglyphs-mdc'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Publish the post: the hieroglyphs render as crisp, scalable, selectable SVG for your readers.',
					'egyptian-hieroglyphs-mdc'
				);
				?>
			</li>
		</ol>
		<p>
			<?php
			esc_html_e(
				'Signs follow the Gardiner list: each has a code (e.g. nTr = god, ra = sun) combined with separators to build quadrats, groups and words.',
				'egyptian-hieroglyphs-mdc'
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Syntax reference (tags)', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tag', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'What it does', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'Example', 'egyptian-hieroglyphs-mdc' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>A1</code>, <code>nTr</code>, <code>ra</code>…</td>
					<td><?php esc_html_e( 'Code of a sign (Gardiner list). Without a separator, signs follow each other on the line.', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>nTr anx</code></td>
				</tr>
				<tr>
					<td><code>:</code></td>
					<td><?php esc_html_e( 'Stacks signs vertically (quadrat)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>nTr:r</code></td>
				</tr>
				<tr>
					<td><code>*</code></td>
					<td><?php esc_html_e( 'Groups signs horizontally', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>ra:Z1*t:Z1</code></td>
				</tr>
				<tr>
					<td><code>-</code></td>
					<td><?php esc_html_e( 'Juxtaposes signs on the same line', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>Htp-di</code></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'space', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><?php esc_html_e( 'Separates words', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>nTr anx</code></td>
				</tr>
				<tr>
					<td><code>!</code></td>
					<td><?php esc_html_e( 'Ends the current fragment', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>nTr:r!anx</code></td>
				</tr>
				<tr>
					<td><code>&lt;</code> … <code>&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche / enclosure', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>&lt;- mn-xpr-ra -&gt;</code></td>
				</tr>
				<tr>
					<td><code>&lt;S- … -&gt;</code></td>
					<td><?php esc_html_e( 'Shen ring', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>&lt;S- anx -&gt;</code></td>
				</tr>
				<tr>
					<td><code>&amp;</code></td>
					<td><?php esc_html_e( 'Overlays one sign on another', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>A1&amp;A2</code></td>
				</tr>
				<tr>
					<td><code>nTr\r1</code>, <code>nTr\R90</code>…</td>
					<td><?php esc_html_e( 'Rotates the previous sign (suffix after the sign)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>nTr\r2</code></td>
				</tr>
				<tr>
					<td><code>anx\red</code></td>
					<td><?php esc_html_e( 'Marks the text red (papyri; suffix)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>anx\red</code></td>
				</tr>
				<tr>
					<td><code>[&amp; … &amp;]</code></td>
					<td><?php esc_html_e( 'Literal brackets (not a cartouche)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><code>[&amp;nTr&amp;]</code></td>
				</tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Cartouches (all variants)', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'Cartouches (royal names inside an oval) are written with the < and > enclosures. The oval adapts to the content: if you stack signs with ":", the cartouche becomes vertical.',
				'egyptian-hieroglyphs-mdc'
			);
			?>
		</p>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tag', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'What it does', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'Result', 'egyptian-hieroglyphs-mdc' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>&lt;- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Simple cartouche', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓋹𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- mn-xpr-ra -&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche with a royal name (Menkheperre)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓏠𓆣𓇳𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- nTr:r -&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche with a quadrat (taller)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓊹𓐰𓂋𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- nTr:r:x:t -&gt;</code></td>
					<td><?php esc_html_e( 'VERTICAL CARTOUCHE: stacked signs; the oval grows taller', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓊹𓐰𓂋𓐰𓐍𓐰𓏏𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;- Htp-di -&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche with two juxtaposed words', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓊵𓏙𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;S- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Shen ring', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓉘𓐼𓋹𓐽𓊂</span></td>
				</tr>
				<tr>
					<td><code>&lt;F- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Walled enclosure', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊈𓐾𓋹𓐿𓊉</span></td>
				</tr>
				<tr>
					<td><code>&lt;H- anx -&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche, H variant', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓉘𓐼𓋹𓐽𓉜</span></td>
				</tr>
				<tr>
					<td><code>&lt;-s-anx-&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche with an initial sign inside (phonetic complement: s, b, h, 1…)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓋴𓋹𓐽𓍺</span></td>
				</tr>
				<tr>
					<td><code>&lt;-b-anx-&gt;</code></td>
					<td><?php esc_html_e( 'Cartouche with initial b', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓍹𓐼𓃀𓋹𓐽𓍺</span></td>
				</tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Orientation, rotation and shading', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'To change the orientation of the whole line, use the block\'s "Direction" option (right to left, like classical Egyptian). To rotate or shade a single sign, the modifiers go AFTER the sign (suffix):',
				'egyptian-hieroglyphs-mdc'
			);
			?>
		</p>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tag', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'What it does', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'Result', 'egyptian-hieroglyphs-mdc' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>nTr\r1</code>, <code>\r2</code>, <code>\r3</code></td>
					<td><?php esc_html_e( 'Rotates the previous sign (font variants)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹︂</span></td>
				</tr>
				<tr>
					<td><code>nTr\R90</code>, <code>\R180</code>, <code>\R270</code></td>
					<td><?php esc_html_e( 'Rotates the previous sign 90°, 180° or 270°', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹︁</span></td>
				</tr>
				<tr>
					<td><code>nTr\h</code>, <code>nTr\v</code>, <code>nTr\t1</code></td>
					<td><?php esc_html_e( 'Horizontal, vertical and reordering transpositions', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹𓑀</span></td>
				</tr>
				<tr>
					<td><code>nTr\shading1234</code></td>
					<td><?php esc_html_e( 'Shades the previous sign (1-4 = corners)', 'egyptian-hieroglyphs-mdc' ); ?></td>
					<td><span class="hierojax" style="font-size:32px;">𓊹𓑕</span></td>
				</tr>
			</tbody>
		</table>
		<p>
			<?php
			printf(
				/* translators: %s is an example. */
				esc_html__( 'You can also mark a fragment in red (papyri) by typing %s at the end of the text.', 'egyptian-hieroglyphs-mdc' ),
				'<code>\\red</code>'
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Complete examples', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'MdC text', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'Result', 'egyptian-hieroglyphs-mdc' ); ?></th>
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
				esc_html__( 'If you don\'t remember the Unicode encoding of a fragment, use the %s on this same page to compose the text and copy it.', 'egyptian-hieroglyphs-mdc' ),
				'<strong>' . esc_html__( 'MdC text composer', 'egyptian-hieroglyphs-mdc' ) . '</strong>'
			);
			?>
		</p>

		<h3><?php esc_html_e( 'Shortcode [hiero]', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'The shortcode is used in the classic editor. It accepts MdC transliteration directly (converted on the fly) or Unicode hieroglyphic encoding, and flows inline with the text:',
				'egyptian-hieroglyphs-mdc'
			);
			?>
		</p>
		<pre style="max-width:760px; background:#f6f7f7; padding:10px; border:1px solid #ccd0d4; overflow:auto;"><?php esc_html_e( 'The king [hiero]ra-ms-sw[/hiero] built a temple.   ← MdC, converted automatically', 'egyptian-hieroglyphs-mdc' ); ?></pre>
		<pre style="max-width:760px; background:#f6f7f7; padding:10px; border:1px solid #ccd0d4; overflow:auto;">[hiero fontsize="42" align="center" color="#8B0000"]anx[/hiero]</pre>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'Values', 'egyptian-hieroglyphs-mdc' ); ?></th>
					<th><?php esc_html_e( 'Description', 'egyptian-hieroglyphs-mdc' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td><code>fontsize</code></td><td>8–200</td><td><?php esc_html_e( 'Sign size (default: the one from settings).', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>color</code></td><td>#rrggbb</td><td><?php esc_html_e( 'Sign color (empty = inherits the theme).', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>align</code></td><td>left · center · right</td><td><?php esc_html_e( 'Block alignment.', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>dir</code></td><td>ltr · hrl</td><td><?php esc_html_e( 'Direction: left to right or right to left.', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>sep</code></td><td>0 – 0.5</td><td><?php esc_html_e( 'Sign separation.', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>shade</code></td><td>uniform · hatching</td><td><?php esc_html_e( 'Shading pattern for damaged signs.', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>linesize</code></td><td>0.5 – 3</td><td><?php esc_html_e( 'Line height (multiplier).', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
				<tr><td><code>separated</code></td><td>true · false</td><td><?php esc_html_e( 'Allow line breaks in long fragments.', 'egyptian-hieroglyphs-mdc' ); ?></td></tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Tips and FAQ', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<ul style="max-width:760px; list-style: disc; padding-left: 1.4em;">
			<li>
				<?php
				printf(
					/* translators: %s is a path. */
					esc_html__( 'The NewGardiner font is served from the plugin itself. If the host prevented including it (upload limit), the plugin loads it automatically from the official HieroJax CDN (%s).', 'egyptian-hieroglyphs-mdc' ),
					'<code>nederhof.github.io/hierojax</code>'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'If an MdC code is not yet encoded in Unicode, the preview shows the � character and the editor warns. Check the code or use the manual Unicode encoding.',
					'egyptian-hieroglyphs-mdc'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'Scripts and the font are only loaded on pages containing hieroglyphs, so the rest of the site stays fast.',
					'egyptian-hieroglyphs-mdc'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %s is a menu path. */
					esc_html__( 'Each block can be customized individually from its options panel; the defaults on this page only affect new blocks and the shortcode.', 'egyptian-hieroglyphs-mdc' ),
					''
				);
				?>
			</li>
		</ul>

		<h3><?php esc_html_e( 'Credits', 'egyptian-hieroglyphs-mdc' ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %1$s and %2$s are project names with links. */
				esc_html__( 'Rendering engine and converter: %1$s (GPL-3.0). Font: %2$s (SIL OFL 1.1). Both by Mark-Jan Nederhof (University of St Andrews).', 'egyptian-hieroglyphs-mdc' ),
				'<a href="https://github.com/nederhof/hierojax" target="_blank" rel="noopener">HieroJax</a>',
				'<a href="https://github.com/nederhof/newgardiner" target="_blank" rel="noopener">NewGardiner</a>'
			);
			?>
		</p>
		<?php
	}
}
