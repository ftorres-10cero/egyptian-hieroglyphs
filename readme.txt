=== Egyptian Hieroglyphs (MdC) ===
Contributors: ftorres
Tags: hieroglyphs, egyptian, egyptology, mdc, transliteration, block, shortcode
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.5.3
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

Usa la sintaxis de cerramiento del conversor: `<` … `>`. Ejemplos:

* `<- mn-xpr-ra ->` → cartucho de Menkheperre (𓍹…𓍺)
* `<- ra-ms-sw ->` → cartucho de Ramsés
* `<S- anx ->` → anillo shen

Los corchetes `[& … &]` son signos de corchete literales, no cartuchos.

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

= 1.5.3 =
* Nueva dirección VERTICAL (vlr/vrl) en el bloque: los cartuchos se dibujan en vertical (óvalo alto) con el texto apilado.

= 1.5.2 =
* Corrección: la URL de la fuente NewGardiner ahora se fija también en el editor y en el conversor (antes solo en el frontend), de modo que las vistas previas del admin renderizan SVG correctamente (rotaciones, cartuchos, sombreado).

= 1.5.1 =
* Los assets se cargan también cuando el contenido usa jeroglíficos sin el bloque (HTML con spans .hierojax).
* El editor de bloques renderiza los jeroglíficos del contenido (bloque Classic) como SVG.

= 1.5.0 =
* Ayuda exhaustiva: cartuchos en todas sus variantes (incluido el cartucho vertical) y orientación/rotación/sombreado de signos.

= 1.4.0 =
* Nuevos idiomas: francés (fr_FR), alemán (de_DE) e italiano (it_IT), además del inglés (en_US). El plugin se muestra en el idioma del panel de WordPress.

= 1.3.1 =
* Corrección: los ejemplos de jeroglíficos de la ayuda se renderizan ahora como SVG (hierojax) a tamaño legible, en lugar de texto pequeño.

= 1.3.0 =
* Ayuda ampliada: guía paso a paso «Cómo escribir jeroglíficos» y referencia completa de la sintaxis (tags).
* El editor muestra los textareas de codificación Unicode con la fuente NewGardiner.

= 1.2.1 =
* Corrección: los ejemplos de jeroglíficos de la ayuda (Ajustes → Jeroglíficos Egipcios) ahora cargan la fuente NewGardiner y se muestran correctamente.

= 1.2.0 =
* Ayuda extensa en Ajustes → Jeroglíficos Egipcios (sintaxis MdC, cartuchos, ejemplos, shortcode, FAQ).
* Preparado para multidioma: plantilla POT y traducción al inglés (en_US) incluidas; todas las cadenas (incluido el JS) son traducibles.

= 1.1.1 =
* Corrección: las opciones por defecto se registran también para REST (/wp/v2/settings), necesario para que el editor de bloques las lea.

= 1.1.0 =
* Ajustes del plugin (Ajustes → Jeroglíficos (MdC)): tamaño y color por defecto para bloques nuevos y shortcode; cada bloque se sigue personalizando individualmente.

= 1.0.3 =
* Ejemplos completos con cartuchos: sintaxis `<…>` documentada en el bloque, el conversor y el readme.

= 1.0.2 =
* Corrección: el editor y el conversor cargaban dos veces el core de hierojax (hierojax.js + mdcconversion.js), lo que rompía la conversión MdC. Ahora solo se carga mdcconversion.js (incluye renderer y parser).

= 1.0.1 =
* El conversor del panel arranca con un ejemplo de MdC y vista previa generada.

= 1.0.0 =
* Primera versión en producción: instalada y verificada en ftorres.es (bloque + shortcode + render SVG).

= 0.1.0 =
* Primera versión: bloque Gutenberg, shortcode [hiero], herramienta de conversión, i18n, licencias.
