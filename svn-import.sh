#!/usr/bin/env bash
# Importación SVN a WordPress.org para "Egyptian Hieroglyphs (MdC)".
#
# Requisitos:
#   1. Cuenta en wordpress.org (la del autor del plugin).
#   2. El plugin aceptado en el directorio (https://wordpress.org/plugins/developers/add-plugin/)
#      y el slug confirmado (por defecto: egyptian-hieroglyphs).
#   3. Credenciales SVN de wordpress.org (usuario y contraseña de la cuenta).
#
# Uso:
#   ./svn-import.sh            # usa el slug por defecto
#   SLUG=mi-slug ./svn-import.sh
#
# Nota: si el slug final difiere, cambiar también el textdomain en el código
# (debe coincidir con el slug en WordPress.org).
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
cp -r "$PLUGIN_DIR"/{assets,build,includes,languages,licenses,egyptian-hieroglyphs.php,readme.txt,README.md} "$TMP/svn/trunk/"
rm -rf "$TMP/svn/trunk/assets/fonts/NewGardiner.otf"   # la fuente se gestiona igual en SVN; se sube con svn add

echo "==> Tag de la versión"
mkdir -p "$TMP/svn/tags/$VERSION"
cp -r "$TMP/svn/trunk"/* "$TMP/svn/tags/$VERSION/"

echo "==> Assets del directorio (capturas, icono, banner) si existen"
mkdir -p "$TMP/svn/assets"
[ -f "$PLUGIN_DIR/screenshots/screenshot-1-frontend.png" ] && cp "$PLUGIN_DIR/screenshots/screenshot-1-frontend.png" "$TMP/svn/assets/screenshot-1.png"
[ -f "$PLUGIN_DIR/screenshots/screenshot-2-editor.png" ] && cp "$PLUGIN_DIR/screenshots/screenshot-2-editor.png" "$TMP/svn/assets/screenshot-2.png"
[ -f "$PLUGIN_DIR/screenshots/screenshot-3-converter.png" ] && cp "$PLUGIN_DIR/screenshots/screenshot-3-converter.png" "$TMP/svn/assets/screenshot-3.png"

echo "==> Añadiendo ficheros nuevos (svn add) y subiendo"
cd "$TMP/svn"
svn add --force trunk tags assets >/dev/null || true
svn status
echo ""
echo "Comprueba el estado y sube con:"
echo "  svn commit -m 'Import $SLUG $VERSION'"
echo "  (pedirá usuario y contraseña de wordpress.org)"
