#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Genera screenshots/portada-articulo.png (850x500) — portada del articulo del plugin.

Replica la plantilla de las portadas previas de ftorres.es (pfc-2003, deepseek,
opencode, claude-code):
- Fondo claro #F1F0F7 con texto transliterado pálido + bloques de jeroglíficos
  y anillos decorativos — TODO se dibuja primero.
- La sección diagonal oscura #231A46 (0,0)-(458,0)-(167,500)-(0,500) se dibuja
  ENCIMA del fondo: es completamente opaca y NO deja ver textos ni jeroglíficos
  de la zona clara a través de ella.
- Icono: recuadro redondeado 260x260 en (560,110)-(820,370), fondo del color de
  marca, logo (W de WordPress blanca + escarabajo rojo encima) centrado.
- Píldora inferior oscura (47,47,63) centrada en x=615 con ejemplo MdC.
- Título y descripción dentro de la sección oscura.
"""
from PIL import Image, ImageDraw, ImageFont

W, H = 850, 500
BG = (241, 240, 247)          # #F1F0F7
DARK = (35, 26, 70)           # #231A46
BRAND = (124, 92, 231)        # #7C5CE7 púrpura de marca (estilo previos)
RED = (198, 40, 40)           # #C62828
RED_DARK = (139, 0, 0)        # #8B0000
PALE = (120, 112, 150)        # texto transliterado del fondo
GLYPH = (80, 70, 120)         # jeroglíficos del fondo
PILL = (47, 47, 63)           # #2F2F3F píldora inferior
LIB_BOLD = "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf"
LIB_REG = "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf"
LIB_ITAL = "/usr/share/fonts/truetype/liberation/LiberationSans-Italic.ttf"
DEJ_MONO = "/usr/share/fonts/truetype/dejavu/DejaVuSansMono-Bold.ttf"
NEWG = "/home/ftorres/Escritorio/ftorres.es/plugins/egyptian-hieroglyphs/assets/fonts/NewGardiner.otf"

img = Image.new("RGB", (W, H), BG)
d = ImageDraw.Draw(img)


def font(path, size):
    return ImageFont.truetype(path, size)


# ---------------------------------------------------------------------------
# 1) FONDO CLARO: texto transliterado intercalado con bloques de jeroglíficos
# ---------------------------------------------------------------------------
pale_font = font(LIB_ITAL, 16)
glyph_font = font(NEWG, 30)
# texto transliterado pálido (zona clara: derecha e inferior)
for (y, txt) in [(90, "nTr anx ra"), (130, "Htp-di-nswt"), (170, "mn-xpr-ra"),
                 (210, "ra-ms-sw"), (250, "sA-ra"), (300, "nTr-anx"),
                 (390, "anx"), (420, "sA")]:
    d.text((610, y), txt, font=pale_font, fill=PALE)
# bloques de jeroglíficos intercalados (zona clara)
for (x, y, txt) in [(610, 105, "nTr:r"), (700, 145, "ra:Z1"), (760, 185, "Htp-di"),
                    (620, 225, "anx"), (700, 260, "mn-xpr"), (790, 300, "ra"),
                    (630, 350, "<- ra ->"), (700, 395, "sA"), (780, 430, "anx"),
                    (620, 460, "nTr")]:
    d.text((x, y), txt, font=glyph_font, fill=GLYPH)

# ---------------------------------------------------------------------------
# 2) Anillos y puntos decorativos (debajo del polígono: el polígono los tapa
#    dentro de su área, por lo que la sección diagonal queda limpia)
# ---------------------------------------------------------------------------
ring = Image.new("RGBA", (W, H), (0, 0, 0, 0))
rd = ImageDraw.Draw(ring)
rd.ellipse((-150, 60, 260, 470), outline=(255, 255, 255, 120), width=3)
rd.ellipse((470, 330, 560, 420), outline=(35, 26, 70, 90), width=2)
img = Image.alpha_composite(img.convert("RGBA"), ring).convert("RGB")
d = ImageDraw.Draw(img)

for (px, py, r) in [(455, 90, 5), (500, 250, 3), (612, 300, 4), (810, 330, 3)]:
    d.ellipse((px - r, py - r, px + r, py + r), fill=PALE)

# ---------------------------------------------------------------------------
# 3) SECCIÓN DIAGONAL OSCURA — se dibuja ENCIMA del fondo, opaca.
#    Misma geometría que las portadas previas: (0,0)-(458,0)-(167,500)-(0,500)
# ---------------------------------------------------------------------------
poly = [(0, 0), (458, 0), (167, 500), (0, 500)]
d.polygon(poly, fill=DARK)

# ---------------------------------------------------------------------------
# 4) Icono: recuadro redondeado con fondo de color (estilo de las portadas
#    previas) + W de WordPress blanca + perfil del escarabajo rojo encima
# ---------------------------------------------------------------------------
ix, iy, isz, rad = 560, 110, 260, 50
d.rounded_rectangle((ix, iy, ix + isz, iy + isz), radius=rad, fill=BRAND)

# 4a) W de WordPress — blanca, centrada
w_font = font(LIB_BOLD, 118)
wbox = d.textbbox((0, 0), "W", font=w_font)
wx = ix + (isz - (wbox[2] - wbox[0])) // 2 - wbox[0]
wy = iy + (isz - (wbox[3] - wbox[1])) // 2 - wbox[1] - 8
d.text((wx, wy), "W", font=w_font, fill=(255, 255, 255))

# 4b) Perfil del escarabajo (U+131A3) en rojo translúcido, ENCIMA de la W
scarab_font = font(NEWG, 104)
sbox = d.textbbox((0, 0), "\U000131A3", font=scarab_font)
sw = sbox[2] - sbox[0]
sh = sbox[3] - sbox[1]
sx = ix + (isz - sw) // 2 - sbox[0]
sy = iy + (isz - sh) // 2 - sbox[1] - 12
overlay = Image.new("RGBA", (W, H), (0, 0, 0, 0))
od = ImageDraw.Draw(overlay)
od.text((sx, sy), "\U000131A3", font=scarab_font, fill=(198, 40, 40, 205))
img = Image.alpha_composite(img.convert("RGBA"), overlay).convert("RGB")
d = ImageDraw.Draw(img)

# ---------------------------------------------------------------------------
# 5) Título y descripción dentro de la sección oscura
# ---------------------------------------------------------------------------
t_font = font(LIB_BOLD, 42)
d.text((34, 84), "Jeroglíficos", font=t_font, fill=(255, 255, 255))
d.text((34, 134), "Egipcios", font=t_font, fill=(255, 255, 255))
mdc_font = font(LIB_BOLD, 30)
d.text((34, 200), "(MdC)", font=mdc_font, fill=RED_DARK)

desc_lines = [
    "Bloque de Gutenberg y shortcode [hiero]",
    "para escribir jeroglíficos egipcios con",
    "notación Manuel de Codage (MdC).",
]
dsc_font = font(LIB_REG, 17)
yy = 260
for line in desc_lines:
    d.text((34, yy), line, font=dsc_font, fill=(210, 205, 225))
    yy += 24

# ---------------------------------------------------------------------------
# 6) Píldora inferior — centrada en x=615 (como las portadas previas)
# ---------------------------------------------------------------------------
ex = "$ [hiero] <- mn-xpr-ra ->"
ex_font = font(DEJ_MONO, 15)
eb = d.textbbox((0, 0), ex, font=ex_font)
ew = eb[2] - eb[0]
eh = eb[3] - eb[1]
pill_w = ew + 40
pill_h = 34
pill_x = 615 - pill_w // 2
pill_y = 450 - pill_h // 2
d.rounded_rectangle((pill_x, pill_y, pill_x + pill_w, pill_y + pill_h),
                    radius=pill_h // 2, fill=PILL)
d.text((pill_x + 20, pill_y + (pill_h - eh) // 2), ex, font=ex_font,
       fill=(255, 253, 230))

img.save("/home/ftorres/Escritorio/ftorres.es/plugins/egyptian-hieroglyphs/screenshots/portada-articulo.png")
print("portada generada OK")
