#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Genera screenshots/portada-articulo.png (850x500) — portada del articulo del plugin.

Diseño:
- Sección oscura diagonal #231A46 a la izquierda con el título, "(MdC)" y la descripción.
- Fondo claro #F1F0F7 con texto transliterado pálido intercalado con bloques de
  jeroglíficos (NewGardiner).
- Icono (derecha): cuadrado redondeado con fondo macizo #231A46, W de WordPress en
  blanco y el perfil (contorno) del jeroglífico escarabajo en rojo encima.
- Anillos decorativos, puntos y píldora inferior con ejemplo MdC.
"""
from PIL import Image, ImageDraw, ImageFont

W, H = 850, 500
BG = (241, 240, 247)          # #F1F0F7
DARK = (35, 26, 70)           # #231A46
RED = (198, 40, 40)           # #C62828
RED_DARK = (139, 0, 0)        # #8B0000
PALE = (120, 112, 150)        # texto transliterado del fondo
LIB_BOLD = "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf"
LIB_REG = "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf"
LIB_ITAL = "/usr/share/fonts/truetype/liberation/LiberationSans-Italic.ttf"
DEJ_MONO = "/usr/share/fonts/truetype/dejavu/DejaVuSansMono-Bold.ttf"
NEWG = "/home/ftorres/Escritorio/ftorres.es/plugins/egyptian-hieroglyphs/assets/fonts/NewGardiner.otf"

img = Image.new("RGB", (W, H), BG)
d = ImageDraw.Draw(img)


def font(path, size):
    return ImageFont.truetype(path, size)


def rounded(draw, box, radius, fill):
    draw.rounded_rectangle(box, radius=radius, fill=fill)


# ---------------------------------------------------------------------------
# 1) Sección oscura diagonal (polígono)
# ---------------------------------------------------------------------------
poly = [(0, 0), (560, 0), (300, 500), (0, 500)]
d.polygon(poly, fill=DARK)

# ---------------------------------------------------------------------------
# 2) Decoración: anillos y puntos (sobre toda la imagen)
# ---------------------------------------------------------------------------
ring = Image.new("RGBA", (W, H), (0, 0, 0, 0))
rd = ImageDraw.Draw(ring)
# anillo grande izquierdo, semi-transparente, cortado por el polígono oscuro
rd.ellipse((-150, 60, 260, 470), outline=(255, 255, 255, 60), width=3)
# anillo pequeño sobre la sección clara
rd.ellipse((470, 330, 560, 420), outline=(35, 26, 70, 90), width=2)
img = Image.alpha_composite(img.convert("RGBA"), ring).convert("RGB")
d = ImageDraw.Draw(img)

# puntos decorativos (sección clara)
for (px, py, r) in [(455, 90, 5), (560, 155, 4), (500, 250, 3), (612, 300, 4), (810, 330, 3)]:
    d.ellipse((px - r, py - r, px + r, py + r), fill=(35, 26, 70, 90) if False else (120, 112, 150))

# ---------------------------------------------------------------------------
# 3) Fondo claro: texto transliterado intercalado con bloques de jeroglíficos
# ---------------------------------------------------------------------------
# Columna derecha (x >= 580) y franja inferior derecha (x >= 340) quedan claras.
pale_font = font(LIB_ITAL, 16)
glyph_font = font(NEWG, 30)
translit_lines = [
    (610, "nTr anx ra"),
    (650, "Htp-di-nswt"),
    (690, "mn-xpr-ra"),
    (730, "ra-ms-sw"),
    (770, "sA-ra"),
]
glyph_blocks = [
    (610, 88, "nTr:r"),
    (655, 128, "ra:Z1"),
    (700, 168, "Htp-di"),
    (745, 208, "anx"),
    (790, 248, "mn-xpr"),
    (630, 340, "<- ra ->"),
    (690, 380, "sA"),
    (760, 420, "anx"),
    (600, 440, "nTr"),
]
for (y, txt) in translit_lines:
    d.text((610, y), txt, font=pale_font, fill=PALE)
for (x, y, txt) in glyph_blocks:
    d.text((x, y), txt, font=glyph_font, fill=(80, 70, 120))

# ---------------------------------------------------------------------------
# 4) Icono (derecha): fondo macizo + W de WordPress blanca + escarabajo rojo
# ---------------------------------------------------------------------------
ix, iy, isz, rad = 596, 40, 220, 46
d.rounded_rectangle((ix, iy, ix + isz, iy + isz), radius=rad, fill=DARK)

# 4a) W de WordPress — trazo grueso en blanco, centrada
w_font = font(LIB_BOLD, 118)
wbox = d.textbbox((0, 0), "W", font=w_font)
wx = ix + (isz - (wbox[2] - wbox[0])) // 2 - wbox[0]
wy = iy + (isz - (wbox[3] - wbox[1])) // 2 - wbox[1] - 8  # un poco arriba del centro
d.text((wx, wy), "W", font=w_font, fill=(255, 255, 255))

# 4b) Perfil del escarabajo (U+131A3) en rojo translúcido, ENCIMA de la W:
#     se combina con la W (ambos reconocibles) en un solo símbolo.
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
# 6) Píldora inferior con ejemplo MdC
# ---------------------------------------------------------------------------
ex = "$ [hiero] <- mn-xpr-ra ->"
ex_font = font(DEJ_MONO, 15)
eb = d.textbbox((0, 0), ex, font=ex_font)
ew = eb[2] - eb[0]
eh = eb[3] - eb[1]
pill_w = ew + 36
pill_h = eh + 18
pill_x = 30
pill_y = H - pill_h - 26
d.rounded_rectangle((pill_x, pill_y, pill_x + pill_w, pill_y + pill_h),
                    radius=pill_h // 2, fill=(245, 244, 250),
                    outline=(120, 112, 150), width=1)
d.text((pill_x + 18, pill_y + 9), ex, font=ex_font, fill=(35, 26, 70))

img.save("/home/ftorres/Escritorio/ftorres.es/plugins/egyptian-hieroglyphs/screenshots/portada-articulo.png")
print("portada generada OK")
