# Publicar una nueva versión en WordPress.org

Plugin: https://wordpress.org/plugins/egyptian-hieroglyphs-mdc
SVN: https://plugins.svn.wordpress.org/egyptian-hieroglyphs-mdc (usuario `ftorresalva`, contraseña SVN de *Account & Security*, no la de login)

1. Subir la versión en los dos sitios (deben coincidir):
   - `egyptian-hieroglyphs.php` → cabecera `Version:`
   - `readme.txt` → `Stable tag:` y nueva entrada en `== Changelog ==`
2. Si cambió `src/`, recompilar: `npm run build`.
3. Si cambian cadenas traducibles, regenerar `.pot`/`.po`/`.mo`/`.json` en `languages/`.
4. Commit y push en git.
5. Preparar SVN: `./svn-import.sh`
   - Lee la versión de `Stable tag`, actualiza el checkout en `~/svn-egyptian-hieroglyphs-mdc/svn`,
     copia el plugin a `trunk/` y `tags/<versión>/`, y capturas/icono/banner a `assets/`.
6. Revisar: `svn status ~/svn-egyptian-hieroglyphs-mdc/svn`
   - Ficheros eliminados del plugin salen como `!`: quitarlos con `svn rm`.
7. Subir:
   ```
   svn commit ~/svn-egyptian-hieroglyphs-mdc/svn -m "Release <versión>" --username ftorresalva
   ```
8. Comprobar: `curl -s 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug=egyptian-hieroglyphs-mdc' | head -c 200`
   debe mostrar la nueva versión (puede tardar unos minutos).

Nunca modificar un tag ya publicado: cada cambio va en una versión nueva.
