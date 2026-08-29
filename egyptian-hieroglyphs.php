<?php
/**
 * Egyptian Hieroglyphs (MdC)
 *
 * @package   EgyptianHieroglyphs
 * @author    ftorres.es
 * @license   GPL-2.0-or-later
 * @link      https://www.ftorres.es
 *
 * @wordpress-plugin
 * Plugin Name:       Egyptian Hieroglyphs (MdC)
 * Plugin URI:        https://www.ftorres.es
 * Description:       Renderiza jeroglíficos egipcios desde transliteración en notación Manuel de Codage (MdC) como SVG, mediante HieroJax y la fuente NewGardiner. Incluye un bloque de Gutenberg y un shortcode.
 * Version:           1.1.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            ftorres.es
 * Author URI:        https://www.ftorres.es
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       egyptian-hieroglyphs
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'FT_HIERO_VERSION', '1.1.0' );
define( 'FT_HIERO_PLUGIN_FILE', __FILE__ );
define( 'FT_HIERO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FT_HIERO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once FT_HIERO_PLUGIN_DIR . 'includes/class-assets.php';
require_once FT_HIERO_PLUGIN_DIR . 'includes/class-admin-tool.php';
require_once FT_HIERO_PLUGIN_DIR . 'includes/class-settings.php';

/**
 * Registra scripts/estilos del plugin (handles) en init.
 */
function ft_hiero_register_assets() {
	EgyptianHieroglyphs\Assets::register();
}
add_action( 'init', 'ft_hiero_register_assets' );

/**
 * Carga el dominio de traducción.
 */
function ft_hiero_load_textdomain() {
	load_plugin_textdomain( 'egyptian-hieroglyphs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'ft_hiero_load_textdomain' );

/**
 * Registra el bloque de Gutenberg.
 */
function ft_hiero_register_block() {
	$block_json = FT_HIERO_PLUGIN_DIR . 'build/block.json';
	if ( file_exists( $block_json ) ) {
		register_block_type( $block_json );
	}
}
add_action( 'init', 'ft_hiero_register_block' );

/**
 * Carga los assets del editor (conversor MdC + runtime hierojax + bloque).
 */
function ft_hiero_enqueue_editor_assets() {
	EgyptianHieroglyphs\Assets::enqueue_editor();
}
add_action( 'enqueue_block_editor_assets', 'ft_hiero_enqueue_editor_assets' );

/**
 * Encola los assets del frontend solo cuando el bloque aparece en el contenido.
 *
 * @param string $block_content Contenido renderizado del bloque.
 * @param array  $block         Datos del bloque.
 * @return string Contenido sin modificar.
 */
function ft_hiero_maybe_enqueue_frontend( $block_content, $block ) {
	if ( isset( $block['blockName'] ) && 'egyptian-hieroglyphs/hiero' === $block['blockName'] ) {
		EgyptianHieroglyphs\Assets::enqueue_frontend();
	}
	return $block_content;
}
add_filter( 'render_block', 'ft_hiero_maybe_enqueue_frontend', 10, 2 );

/**
 * Registra el shortcode [hiero].
 */
function ft_hiero_register_shortcode() {
	add_shortcode( 'hiero', array( 'EgyptianHieroglyphs\\Assets', 'render_shortcode' ) );
}
add_action( 'init', 'ft_hiero_register_shortcode' );

/**
 * Activa la herramienta de conversión del panel de administración.
 */
EgyptianHieroglyphs\AdminTool::init();

/**
 * Activa la página de ajustes (tamaño y color por defecto).
 */
EgyptianHieroglyphs\Settings::init();
