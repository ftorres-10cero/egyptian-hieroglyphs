# Notas sobre los componentes de terceros (vendor)

Este plugin empaqueta componentes de [HieroJax](https://github.com/nederhof/hierojax)
y de la fuente [NewGardiner](https://github.com/nederhof/newgardiner),
ambos de Mark-Jan Nederhof (Universidad de St Andrews).

## Licencias

| Componente | Licencia | Fichero |
|---|---|---|
| `assets/hierojax.js` | GPL-3.0 | `licenses/hierojax-GPL-3.0.txt` |
| `assets/mdcconversion.js` | GPL-3.0 | `licenses/hierojax-GPL-3.0.txt` |
| `assets/hierojax.css` | GPL-3.0 | `licenses/hierojax-GPL-3.0.txt` |
| `assets/fonts/NewGardiner.otf` | SIL OFL 1.1 | `licenses/NewGardiner-OFL-1.1.txt` |

No se modifica ninguna licencia: el código del plugin se distribuye como
GPLv2 o posterior, compatible con los componentes GPL-3.0 y OFL incluidos.

## Parches aplicados a los ficheros vendor

### 1. URL de la fuente (imprescindible en WordPress)

**Ficheros:** `assets/hierojax.js` y `assets/mdcconversion.js` (línea ~4569).

**Problema:** el código original crea la fuente con una URL relativa:

```js
this.fonts = [ new FontFace('Hieroglyphic', 'url(NewGardiner.otf)') ];
```

`url()` en `FontFace` se resuelve contra la URL de la página, no contra la del
script. En WordPress la página vive en `/ruta-del-post/`, así que la fuente
nunca cargaría y hierojax acabaría mostrando un alert tras varios reintentos.

**Parche:** la URL se lee de un global configurable:

```js
this.fonts = [ new FontFace('Hieroglyphic', (window.ftorresHieroFontUrl || 'url(NewGardiner.otf)')) ];
```

El plugin define `window.ftorresHieroFontUrl` con la URL absoluta de la fuente
antes de cargar el script (ver `includes/class-assets.php`). En entornos sin el
plugin (uso directo de hierojax en una página junto a `NewGardiner.otf`) se
mantiene el comportamiento original.

**Cómo re-aplicar tras una actualización de vendor:** re-descargar los
ficheros desde https://nederhof.github.io/hierojax/ y volver a ejecutar el
`sed` de este fichero:

```bash
sed -i "s|'url(NewGardiner.otf)'|(window.ftorresHieroFontUrl || 'url(NewGardiner.otf)')|" assets/hierojax.js assets/mdcconversion.js
```

## Quirks conocidos del conversor MdC→Unicode (upstream)

- **Cartuchos y cerramientos:** la sintaxis de cerramiento de este conversor usa
  **`<` … `>`** (con códigos opcionales `S/F/H`, `b/m/e`, `0-3` y guiones),
  no `[& … &]`. Ejemplos:
  - `<- mn-xpr-ra ->` → cartucho con el nombre de Menkheperre (𓍹…𓍺)
  - `<S- anx ->` → anillo shen
  - `<H- nTr:r ->` → cartucho de variante H
  Los corchetes `[& … &]`, `[{ … }]`, `[[ … ]]`, etc. son **signos de corchete
  literales** (no cerramientos) y se representan como caracteres de corchete en
  la codificación Unicode. Para cerramientos siempre hay que usar `<…>`.
- **Signos no mapeados a Unicode:** códigos MdC desconocidos o signos que
  Unicode aún no ha codificado producen U+FFFD (�) en la salida. El editor del
  bloque avisa si la conversión contiene U+FFFD.
- **Marcas de color rojo:** los *toggles* `\red`/`\black` se convierten, pero
  la salida puede contener U+FFFD para el marcador según el contexto.
