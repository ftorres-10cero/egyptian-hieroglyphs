# Egyptian Hieroglyphs (MdC) — WordPress plugin

Render Egyptian hieroglyphs from [Manuel de Codage (MdC)](https://es.wikipedia.org/wiki/Manuel_de_Codage) transliteration as crisp SVG, with a Gutenberg block and a shortcode.

Built on [HieroJax](https://nederhof.github.io/hierojax/) (GPL-3.0) and the [NewGardiner](https://github.com/nederhof/newgardiner) font (SIL OFL 1.1) by Mark-Jan Nederhof (University of St Andrews).

## Features

- **Gutenberg block** "Egyptian Hieroglyphs (MdC)" with a live SVG preview while you type MdC.
- Automatic **MdC → Unicode** conversion in the editor (quadrats with `:` and `*`, juxtaposition, lost signs, shading, red/black, enclosures, …).
- Per-block options: sign size, alignment, color, direction (right-to-left for classical Egyptian), sign separation, shading, line height.
- **`[hiero]` shortcode** for the classic editor: `[hiero]𓂋𓄿[/hiero]` (Unicode hieroglyphic encoding).
- **Admin converter tool** (Tools → Conversor MdC): one-click MdC → Unicode, copy to clipboard.
- Assets are only loaded on pages that contain hieroglyphs; the 1.2 MB MdC parser is loaded **only in the editor**.
- Signs render as SVG: sharp at any size and copyable/selectable.

## Usage

### Block

Insert the block and type MdC text, for example:

```
nTr:r        (god sign 𓊹 over r 𓂋, vertical quadrat)
ra:Z1*t:Z1   (sun + Z1, horizontal group + Z1)
Htp-di-nswt  (funerary formula)
```

### Shortcode

The shortcode takes the **Unicode hieroglyphic encoding** (one fragment per line). Generate it with Tools → Conversor MdC or with the block's "Ver codificación Unicode" option:

```
[hiero fontsize="42" align="center"]𓂋𓄿𓐰𓏤[/hiero]
```

Shortcode attributes: `fontsize` (8–200), `align` (left|center|right), `color` (hex), `dir` (ltr|hrl), `sep` (0–0.5), `shade` (uniform|hatching), `linesize` (0.5–3), `separated` (true|false).

## Development

```bash
npm install          # installs @wordpress/scripts (use --cache if needed)
npm run build        # build the block into /build
npm test             # MdC conversion tests + jsdom frontend smoke test
npm run plugin-zip   # create the installable zip (uses .distignore)
```

### Vendor components

`assets/` contains patched copies of HieroJax (`hierojax.js`, `mdcconversion.js`, `hierojax.css`) and the `NewGardiner.otf` font. The only patch is the font URL (relative → configurable via `window.ftorresHieroFontUrl`), required because WordPress pages live at URLs where a relative `url()` in `FontFace` cannot resolve. See `VENDOR-NOTES.md` for details and re-apply instructions.

### Known upstream quirks

- Enclosure/cartouche bracket characters (`[& … &]`) are dropped by the upstream converter's `toString()` in this build. Workaround: edit the Unicode encoding directly (block → Advanced) or use the admin converter output.
- MdC codes not yet mapped to Unicode produce U+FFFD (�); the editor warns about them.

## License

- Plugin code: GPL-2.0-or-later (see `licenses/GPL-2.0.txt`).
- HieroJax (JS/CSS): GPL-3.0 (see `licenses/hierojax-GPL-3.0.txt`).
- NewGardiner font: SIL OFL 1.1 (see `licenses/NewGardiner-OFL-1.1.txt`).

© ftorres.es
