#!/usr/bin/env bash
# Importación SVN a WordPress.org para "Egyptian Hieroglyphs (MdC)".
#
# Requisitos:
#   1. Cuenta en wordpress.org (la del autor del plugin, ftorresalva).
#   2. El plugin aceptado en el directorio (https://wordpress.org/plugins/developers/add-plugin/)
#      y el slug confirmado (por defecto: egyptian-hieroglyphs-mdc).
#   3. Credenciales SVN de wordpress.org (usuario y contraseña de la cuenta).
#
# Uso:
#   ./svn-import.sh            # usa el slug por defecto
#   SLUG=mi-slug ./svn-import.sh
#
# Copia el código a trunk/ y tags/, y los assets del directorio
# (capturas, icono y banner) a assets/ con los nombres que exige WordPress.org:
#   assets/screenshot-1.png ... screenshot-3.png
#   assets/icon-256x256.png
#   assets/banner-772x250.png
set -euo pipefail

SLUG="${SLUG:-egyptian-hieroglyphs-mdc}"
VERSION="1.5.8"
PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"   # raíz del plugin
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

echo "==> Slug: $SLUG | Versión: $VERSION"
echo "==> Obteniendo el repositorio SVN de $SLUG"
svn co "https://plugins.svn.wordpress.org/$SLUG" "$TMP/svn" || {
  echo "ERROR: no se pudo hacer checkout. ¿El plugin ya fue aceptado y el slug es correcto?" >&2
  exit 1
}

echo "==> Limpiando trunk y copiando el contenido del plugin"
rm -rf "$TMP/svn/trunk"/*
# Copia los archivos del plugin a trunk/ (excluye icono, banner y logos, que
# son assets del directorio SVN o del blog, no parte del plugin distribuible).
cp -r "$PLUGIN_DIR"/build "$TMP/svn/trunk/"
cp -r "$PLUGIN_DIR"/includes "$TMP/svn/trunk/"
cp -r "$PLUGIN_DIR"/languages "$TMP/svn/trunk/"
cp -r "$PLUGIN_DIR"/licenses "$TMP/svn/trunk/"
cp "$PLUGIN_DIR"/egyptian-hieroglyphs.php "$TMP/svn/trunk/"
cp "$PLUGIN_DIR"/readme.txt "$TMP/svn/trunk/"
cp "$PLUGIN_DIR"/README.md "$TMP/svn/trunk/"
# assets del plugin (runtime + fuentes), SIN los assets de directorio/logo
mkdir -p "$TMP/svn/trunk/assets"
cp -r "$PLUGIN_DIR"/assets/fonts "$TMP/svn/trunk/assets/"
for f in hierojax.css hierojax.js mdcconversion.js admin-converter.js admin-composer.js admin-composer.css sign-catalog.json; do
  [ -f "$PLUGIN_DIR/assets/$f" ] && cp "$PLUGIN_DIR/assets/$f" "$TMP/svn/trunk/assets/"
done

echo "==> Tag de la versión"
mkdir -p "$TMP/svn/tags/$VERSION"
cp -r "$TMP/svn/trunk"/* "$TMP/svn/tags/$VERSION/"

echo "==> Assets del directorio (capturas, icono, banner)"
mkdir -p "$TMP/svn/assets"

# Capturas (nombres que exige WordPress.org)
[ -f "$PLUGIN_DIR/screenshots/screenshot-1-frontend.png" ] && cp "$PLUGIN_DIR/screenshots/screenshot-1-frontend.png" "$TMP/svn/assets/screenshot-1.png"
[ -f "$PLUGIN_DIR/screenshots/screenshot-2-editor.png" ] && cp "$PLUGIN_DIR/screenshots/screenshot-2-editor.png" "$TMP/svn/assets/screenshot-2.png"
[ -f "$PLUGIN_DIR/screenshots/screenshot-3-converter.png" ] && cp "$PLUGIN_DIR/screenshots/screenshot-3-converter.png" "$TMP/svn/assets/screenshot-3.png"

# Icono y banner (nombres que exige WordPress.org)
[ -f "$PLUGIN_DIR/assets/icon-256x256.png" ] && cp "$PLUGIN_DIR/assets/icon-256x256.png" "$TMP/svn/assets/icon-256x256.png"
[ -f "$PLUGIN_DIR/assets/banner-772x250.png" ] && cp "$PLUGIN_DIR/assets/banner-772x250.png" "$TMP/svn/assets/banner-772x250.png"

echo "==> Añadiendo ficheros nuevos (svn add) y subiendo"
cd "$TMP/svn"
svn add --force trunk tags assets >/dev/null || true
svn status
echo ""
echo "Comprueba el estado y sube con:"
echo "  svn commit -m 'Import $SLUG $VERSION'"
echo "  (pedirá usuario ftorresalva y contraseña de wordpress.org)"
