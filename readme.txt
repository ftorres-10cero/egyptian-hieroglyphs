=== Egyptian Hieroglyphs (MdC) ===
Contributors: ftorres
Tags: hieroglyphs, egyptian, egyptology, mdc, transliteration, block, shortcode
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Renderiza jeroglíficos egipcios desde transliteración en notación Manuel de Codage (MdC) como SVG, con un bloque de Gutenberg y un shortcode.

== Description ==

Escribe una frase en [Manuel de Codage (MdC)](https://es.wikipedia.org/wiki/Manuel_de_Codage) — el estándar usado por egiptólogos para codificar jeroglíficos — y el plugin la renderiza como jeroglíficos reales (SVG) usando [HieroJax](https://nederhof.github.io/hierojax/) y la fuente [NewGardiner](https://github.com/nederhof/newgardiner) (SIL OFL 1.1).

**Características:**

* **Bloque de Gutenberg** "Jeroglíficos egipcios (MdC)" con vista previa en vivo mientras escribes el MdC.
* Conversión automática **MdC → Unicode** en el editor (soporta cuadraturas con `:` y `*`, yuxtaposición, signos perdidos, sombreado, rojo/negro, etc.).
* Opciones por bloque: tamaño de los signos, alineación, color, dirección (derecha→izquierda para egipcio clásico), separación entre signos, sombreado y altura de línea.
* **Shortcode `[hiero]`** para el editor clásico: `[hiero]𓂋𓄿[/hiero]` (codificación Unicode jeroglífica).
* **Herramienta de conversión** en Herramientas → Conversor MdC: convierte MdC a Unicode con un clic y cópialo al portapapeles.
* Solo se cargan los scripts en páginas que contienen jeroglíficos; el parser MdC (1,2 MB) se carga únicamente en el editor.
* Los signos se renderizan como SVG: nítidos a cualquier tamaño y copiables/pegables.

**Ejemplo de MdC:** `nTr:r` renderiza el signo "dios" (𓊹) sobre la "r" (𓂋) en un cuadro vertical; `ra:Z1*t:Z1` combina cuadros con separadores vertical y horizontal.

**Créditos:** motor de render y conversor de [HieroJax](https://github.com/nederhof/hierojax) (GPL-3.0) y fuente [NewGardiner](https://github.com/nederhof/newgardiner) (SIL OFL 1.1), ambos de Mark-Jan Nederhof (Universidad de St Andrews). Ver `licenses/` para los textos completos.

== Installation ==

1. Sube la carpeta `egyptian-hieroglyphs` a `/wp-content/plugins/` (o instala el zip desde Plugins → Añadir nuevo).
2. Activa el plugin.
3. En el editor de una entrada, añade el bloque "Jeroglíficos egipcios (MdC)" y escribe tu texto en MdC.
4. Para el editor clásico, usa `[hiero]` con codificación Unicode jeroglífica (genera el Unicode con Herramientas → Conversor MdC).

== Frequently Asked Questions ==

= ¿Qué es Manuel de Codage (MdC)? =

Es un estándar de codificación de jeroglíficos egipcios basado en la lista de signos de Gardiner, muy usado en egiptología. Cada signo se identifica por un código (p. ej. `nTr` = dios, `ra` = sol) y se combina con separadores: `:` agrupa en vertical, `*` en horizontal, `-` yuxtapone.

= ¿Cómo escribo un cartucho? =

La conversión automática de esta versión no conserva los corchetes de cartucho. Activa "Editar la codificación Unicode directamente" en el panel Avanzado del bloque y usa los caracteres `[` … `]` (o `⟨` … `⟩`) alrededor de la codificación del nombre.

= ¿El shortcode acepta MdC? =

No: el shortcode espera la codificación Unicode jeroglífica ya convertida. Usa el bloque (que convierte MdC automáticamente) o la herramienta Herramientas → Conversor MdC para obtener el Unicode.

= ¿Por qué veo el carácter � en la vista previa? =

Algunos códigos MdC corresponden a signos que Unicode aún no ha codificado; se muestran como �. Revisa el código o usa la codificación Unicode manual.

= ¿En qué páginas se cargan los scripts? =

Solo en páginas que contienen el bloque o el shortcode. La fuente (2,6 MB) y el runtime (885 KB) no se cargan en el resto del sitio.

== Screenshots ==

1. Bloque de Gutenberg con vista previa en vivo del MdC.
2. Opciones del bloque (tamaño, dirección, color, sombreado).
3. Herramienta de conversión MdC → Unicode.

== Changelog ==

= 1.0.1 =
* El conversor del panel arranca con un ejemplo de MdC y vista previa generada.

= 1.0.0 =
* Primera versión en producción: instalada y verificada en ftorres.es (bloque + shortcode + render SVG).

= 0.1.0 =
* Primera versión: bloque Gutenberg, shortcode [hiero], herramienta de conversión, i18n, licencias.
