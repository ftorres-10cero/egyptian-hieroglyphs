# Egyptian Hieroglyphs (MdC) — WordPress plugin

<img src="assets/logo_hiero_150.png" alt="Egyptian Hieroglyphs (MdC) logo" width="150" height="150" align="left" style="margin-right: 16px;">

Render Egyptian hieroglyphs from [Manuel de Codage (MdC)](https://es.wikipedia.org/wiki/Manuel_de_Codage) transliteration as crisp SVG, with a Gutenberg block and a shortcode.

Built on [HieroJax](https://nederhof.github.io/hierojax/) (GPL-3.0) and the [NewGardiner](https://github.com/nederhof/newgardiner) font (SIL OFL 1.1) by Mark-Jan Nederhof (University of St Andrews).


## Features

- **Gutenberg block** "Egyptian Hieroglyphs (MdC)" with a live SVG preview while you type MdC.
- Automatic **MdC → Unicode** conversion in the editor (quadrats with `:` and `*`, juxtaposition, lost signs, shading, red/black, enclosures, …).
- Per-block options: sign size, alignment, color, direction (right-to-left for classical Egyptian), sign separation, shading, line height.
- **`[hiero]` shortcode** for the classic editor: `[hiero]𓂋𓄿[/hiero]` (Unicode hieroglyphic encoding).
- **MdC composer** (Settings → Egyptian Hieroglyphs): visual sign palette (Gardiner list) with buttons for cartouches, quadrats and groups, live preview and copy-to-clipboard.
- Assets are only loaded on pages that contain hieroglyphs; the 1.2 MB MdC parser is loaded **only in the editor**.
- Signs render as SVG: sharp at any size and copyable/selectable.

## Default settings

**Settings → Egyptian Hieroglyphs (MdC)** lets you define the default **size** (px) and **color** of the signs. They apply to:

- **New blocks** you insert (each block can customize them afterwards from its options panel — the change does not affect already-saved blocks).
- The **`[hiero]` shortcode** when no `fontsize`/`color` are given.

## Usage

### How to write hieroglyphs (step by step)

1. In the post editor, add the **"Egyptian Hieroglyphs (MdC)"** block.
2. In the **"MdC notation"** box, type the sign codes (Gardiner list) combined with the separators below — the preview renders instantly.
3. In the block sidebar adjust size, color, direction, separation and shading.
4. Publish: readers see crisp, selectable SVG.

Example text you can type:

```
nTr:r            god sign 𓊹 over r 𓂋 (vertical quadrat)
ra:Z1*t:Z1       sun + Z1, horizontal group + Z1
<- mn-xpr-ra ->  royal cartouche: Menkheperre (𓍹…𓍺)
<- ra-ms-sw ->   royal cartouche: Rameses
```

**MdC syntax reference (the "tags"):**

| Tag | What it does | Example |
|---|---|---|
| `A1`, `nTr`, `ra`… | sign code (Gardiner list); signs follow each other | `nTr anx` |
| `:` | stacks signs vertically (quadrat) | `nTr:r` → 𓊹𓐰𓂋 |
| `*` | groups signs horizontally | `ra:Z1*t:Z1` → 𓇳𓐰𓏤𓐱𓏏𓐰𓏤 |
| `-` | juxtaposes signs on the line | `Htp-di` |
| space | separates words | `nTr anx` |
| `!` | ends the current fragment | `nTr:r!anx` |
| `<` `>` | **cartouche / enclosure** | `<- mn-xpr-ra ->` → 𓍹𓐼𓏠𓆣𓇳𓐽𓍺 |
| `<S- … ->` | shen ring | `<S- anx ->` |
| `<H- … ->` | cartouche (H variant) | `<H- nTr:r ->` |
| `&` | overlays one sign on another | `A1&A2` |
| `\r1`, `\r2`, `\t1`… | rotations/transpositions of the next sign | `\r2-nTr` |
| `\red` | marks the text red (papyri) | `\red-anx` |
| `[& … &]` | literal brackets (not a cartouche) | `[&nTr&]` |

The full guide (with cartouche section, complete examples and shortcode reference) is in the plugin settings: **Settings → Egyptian Hieroglyphs**. A live demo with all 35 examples is at the test article.

### Shortcode

The shortcode accepts both the **Unicode hieroglyphic encoding** and plain **MdC transliteration** (it is converted automatically in the browser), so you can write:

```
El rey [hiero]ra-ms-sw[/hiero] erigió un templo.   ← MdC, converted on the fly
[hiero]𓂋𓄿𓐰𓏤[/hiero]                             ← or Unicode directly
[hiero fontsize="42" color="#8B0000"]anx[/hiero]   ← with attributes
```

The hieroglyphs are rendered inline (they do not break the surrounding text). Shortcode attributes: `fontsize` (8–200), `align` (left|center|right), `color` (hex), `dir` (ltr|hrl), `sep` (0–0.5), `shade` (uniform|hatching), `linesize` (0.5–3), `separated` (true|false).

## Development

```bash
npm install          # installs @wordpress/scripts (use --cache if needed)
npm run build        # build the block into /build
npm test             # MdC conversion tests + jsdom frontend smoke test
npm run plugin-zip   # create the installable zip (uses .distignore)
```

### Vendor components

`assets/` contains patched copies of HieroJax (`hierojax.js`, `mdcconversion.js`, `hierojax.css`) and the `NewGardiner.otf` font. The only patch is the font URL (relative → configurable via `window.ftorresHieroFontUrl`), required because WordPress pages live at URLs where a relative `url()` in `FontFace` cannot resolve. See `docs/VENDOR-NOTES.md` for details and re-apply instructions.

### Known upstream quirks

- Enclosure/cartouche bracket characters (`[& … &]`) are dropped by the upstream converter's `toString()` in this build. Workaround: edit the Unicode encoding directly (block → Advanced) or use the admin converter output.
- MdC codes not yet mapped to Unicode produce U+FFFD (�); the editor warns about them.

## License

- Plugin code: GPL-2.0-or-later (see `licenses/GPL-2.0.txt`).
- HieroJax (JS/CSS): GPL-3.0 (see `licenses/hierojax-GPL-3.0.txt`).
- NewGardiner font: SIL OFL 1.1 (see `licenses/NewGardiner-OFL-1.1.txt`).

## Contact

Author: ftorres.es · [ftorres@10cero.es](mailto:ftorres@10cero.es)

© ftorres.es
