=== Egyptian Hieroglyphs (MdC) ===
Contributors: ftorresalva
Tags: hieroglyphs, egyptian, egyptology, transliteration, shortcode
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Render Egyptian hieroglyphs from Manuel de Codage (MdC) transliteration as SVG, with a Gutenberg block and a shortcode.

== Description ==

Type a phrase in [Manuel de Codage (MdC)](https://en.wikipedia.org/wiki/Manuel_de_Codage), the standard Egyptologists use to encode hieroglyphs, and the plugin renders it as real hieroglyphs (SVG) using [HieroJax](https://nederhof.github.io/hierojax/) and the [NewGardiner](https://github.com/nederhof/newgardiner) font (SIL OFL 1.1).

**Features:**

* **Gutenberg block** "Egyptian Hieroglyphs (MdC)" with a live preview while you type MdC.
* Automatic **MdC → Unicode** conversion in the editor (supports quadrats with `:` and `*`, juxtaposition, lost signs, shading, red/black, etc.).
* Per-block options: sign size, alignment, color, direction (right-to-left for classical Egyptian), sign spacing, shading and line height.
* **`[hiero]` shortcode** for the classic editor: accepts MdC transliteration or Unicode hieroglyphic encoding, e.g. `[hiero]ra-ms-sw[/hiero]` or `[hiero]𓂋𓄿[/hiero]`, and flows inline with the surrounding text.
* **MdC text composer** in Settings → Egyptian Hieroglyphs: sign palette (Gardiner list), buttons for cartouches, quadrats and groups, live preview and copy of the result.
* Scripts are only loaded on pages that contain hieroglyphs; the MdC parser (1.2 MB) is only loaded in the editor.
* Signs are rendered as SVG: sharp at any size, and they can be copied and pasted.

**MdC example:** `nTr:r` renders the "god" sign (𓊹) above the "r" (𓂋) in a vertical quadrat; `ra:Z1*t:Z1` combines quadrats with vertical and horizontal separators.

**Credits:** rendering engine and converter by [HieroJax](https://github.com/nederhof/hierojax) (GPL-3.0) and the [NewGardiner](https://github.com/nederhof/newgardiner) font (SIL OFL 1.1), both by Mark-Jan Nederhof (University of St Andrews). See `licenses/` for the full texts.

**Source code and build:** the block editor script in `build/` is compiled from the human-readable sources in `src/` (included in this plugin) with [@wordpress/scripts](https://www.npmjs.com/package/@wordpress/scripts). The full source, including the build tooling, is publicly available at [github.com/ftorres-10cero/egyptian-hieroglyphs](https://github.com/ftorres-10cero/egyptian-hieroglyphs). To rebuild: `npm install && npm run build`. Third-party files in `assets/` (`hierojax.js`, `hierojax.css`, `mdcconversion.js`, NewGardiner font) are unminified upstream files from [HieroJax](https://github.com/nederhof/hierojax) and [NewGardiner](https://github.com/nederhof/newgardiner).

The plugin's home page, with a live demo, is at [ftorres.es](https://www.ftorres.es/miscelanea/jeroglificos-egipcios-prueba-del-plugin-mdc/).

== Installation ==

1. Upload the `egyptian-hieroglyphs` folder to `/wp-content/plugins/` (or install the zip from Plugins → Add New).
2. Activate the plugin.
3. In the post editor, add the "Egyptian Hieroglyphs (MdC)" block and type your text in MdC.
4. In the classic editor, use `[hiero]` with MdC transliteration or Unicode hieroglyphic encoding (you can compose the text with the MdC text composer in the settings).

== Frequently Asked Questions ==

= What is Manuel de Codage (MdC)? =

It is an encoding standard for Egyptian hieroglyphs based on Gardiner's sign list, widely used in Egyptology. Each sign is identified by a code (e.g. `nTr` = god, `ra` = sun) and signs are combined with separators: `:` stacks vertically, `*` groups horizontally, `-` juxtaposes.

= How do I write a cartouche? =

Use the converter's enclosure syntax: `<` … `>`. Examples:

* `<- mn-xpr-ra ->` → cartouche of Menkheperre (𓍹…𓍺)
* `<- ra-ms-sw ->` → cartouche of Ramesses
* `<S- anx ->` → shen ring

The brackets `[& … &]` are literal bracket signs, not cartouches.

= Does the shortcode accept MdC? =

Yes. The shortcode accepts MdC transliteration directly (it is converted on the fly in the browser) or Unicode hieroglyphic encoding. You can compose the text with the MdC text composer in the settings.

= Why do I see the � character in the preview? =

Some MdC codes correspond to signs that Unicode has not encoded yet; they are shown as �. Check the code or use manual Unicode encoding.

= On which pages are the scripts loaded? =

Only on pages that contain the block or the shortcode. The font (2.6 MB) and the runtime (885 KB) are not loaded anywhere else on the site.

== Screenshots ==

1. Gutenberg block with a live MdC preview.
2. Block options (size, direction, color, shading).
3. MdC text composer in Settings → Egyptian Hieroglyphs (sign palette and preview).

== Upgrade Notice ==

= 1.5.9 =
English is now the source language; Spanish, French, German and Italian translations included.

== Changelog ==

= 1.5.9 =

* English is now the source language of all strings (PHP, JavaScript and block.json); Spanish is shipped as a translation (es_ES), alongside French, German and Italian.
* Fixed the text domain of the block editor and converter scripts (`egyptian-hieroglyphs-mdc`) and load their JavaScript translations.
* Gardiner sign categories in the MdC text composer are now translatable.
* readme.txt translated into English.

= 1.5.8 =

* Fixed the MdC text composer on the settings page (it loaded two conflicting hierojax core scripts): now only mdcconversion.js is loaded.
* The MdC converter is now a hidden page (reachable through the direct URL `admin.php?page=ftorres-hiero-converter`), with no item in the Tools menu.
* Action links in the plugin list: "Settings" and the plugin's "Website".

= 1.5.7 =

* Action links in the plugin list ("Settings" and "Website").

= 1.5.6 =

* Removed the "Tools → MdC converter" menu; conversion is done from the MdC text composer in the settings (the converter is still reachable through its direct URL).

= 1.5.5 =

* MdC text composer in Settings → Egyptian Hieroglyphs: Gardiner sign palette, buttons for cartouches, quadrats and groups, live preview and copy of the result.
* Updated i18n (Spanish, English, French, German and Italian).

= 1.5.4 =

* The `[hiero]` shortcode now accepts MdC transliteration directly (converted on the fly in the browser), in addition to Unicode hieroglyphic encoding, and flows inline with the text.

= 1.5.3 =
* New VERTICAL direction (vlr/vrl) in the block: cartouches are drawn vertically (tall oval) with the text stacked.

= 1.5.2 =
* Fix: the NewGardiner font URL is now also set in the editor and the converter (previously only on the front end), so admin previews render SVG correctly (rotations, cartouches, shading).

= 1.5.1 =
* Assets are also loaded when the content uses hieroglyphs without the block (HTML with .hierojax spans).
* The block editor renders hieroglyphs in the content (Classic block) as SVG.

= 1.5.0 =
* Comprehensive help: cartouches in all their variants (including the vertical cartouche) and sign orientation/rotation/shading.

= 1.4.0 =
* New languages: French (fr_FR), German (de_DE) and Italian (it_IT), in addition to English (en_US). The plugin is shown in the language of the WordPress dashboard.

= 1.3.1 =
* Fix: hieroglyph examples in the help are now rendered as SVG (hierojax) at a readable size instead of small text.

= 1.3.0 =
* Extended help: step-by-step guide "How to write hieroglyphs" and full syntax reference (tags).
* The editor shows the Unicode encoding textareas with the NewGardiner font.

= 1.2.1 =
* Fix: hieroglyph examples in the help (Settings → Egyptian Hieroglyphs) now load the NewGardiner font and display correctly.

= 1.2.0 =
* Extensive help in Settings → Egyptian Hieroglyphs (MdC syntax, cartouches, examples, shortcode, FAQ).
* Ready for translation: POT template and English (en_US) translation included; all strings (including JS) are translatable.

= 1.1.1 =
* Fix: default options are also registered for REST (/wp/v2/settings), which the block editor needs to read them.

= 1.1.0 =
* Plugin settings (Settings → Egyptian Hieroglyphs): default size and color for new blocks and the shortcode; each block can still be customized individually.

= 1.0.3 =
* Full examples with cartouches: `<…>` syntax documented in the block, the converter and the readme.

= 1.0.2 =
* Fix: the editor and the converter loaded the hierojax core twice (hierojax.js + mdcconversion.js), which broke MdC conversion. Now only mdcconversion.js is loaded (it includes the renderer and the parser).

= 1.0.1 =
* The dashboard converter starts with an MdC example and a generated preview.

= 1.0.0 =
* First production release: installed and verified on ftorres.es (block + shortcode + SVG rendering).

= 0.1.0 =
* First version: Gutenberg block, [hiero] shortcode, conversion tool, i18n, licenses.
