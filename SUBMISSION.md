# Envío a WordPress.org

El plugin está preparado para el [directorio de plugins de WordPress.org](https://wordpress.org/plugins/developers/). Pasos:

## 1. Requisitos previos

- Cuenta en [wordpress.org](https://login.wordpress.org/) (distinta de la del blog; es la cuenta del autor del plugin).
  - Usuario: **ftorresalva** (coincide con `Contributors` del `readme.txt`).
  - Correo de la cuenta: **ftorres@10cero.es**.
- El zip final: `egyptian-hieroglyphs.zip` (se genera con `npm run plugin-zip` o se descarga del release de GitHub). **Versión actual: 1.5.8.**

## 2. Envío

1. Entra en https://wordpress.org/plugins/developers/add-plugin/ con tu cuenta.
2. Sube `egyptian-hieroglyphs.zip`.
3. El equipo de revisión comprobará: licencias (GPL-2.0-or-later + componentes GPL-3.0/OFL incluidos en `licenses/`), `readme.txt`, estándares de código, seguridad y ausencia de enlaces externos en el render.

## 3. Tras la aprobación

- El directorio se rellena por SVN. El repositorio git ya tiene todo el código; el flujo de importación SVN es:
  `svn co https://plugins.svn.wordpress.org/egyptian-hieroglyphs/` y copiar el contenido del zip (sin `node_modules`, `src`, `.phpcs-tools`).
- Subir **capturas de pantalla** (al menos 1; recomendado 3) en el SVN: `assets/screenshot-1.png` … `screenshot-3.png` (1200×900 recomendado). Las capturas deben mostrarse en el readme como:
  `1. Bloque de Gutenberg con vista previa en vivo del MdC.` etc. (ya está así).
- Icono y banner (opcionales): `assets/icon-256x256.png`, `assets/banner-772x250.png`.

## 4. Capturas de pantalla pendientes

Hacerlas con el plugin instalado en ftorres.es (o en un WP de pruebas):

1. **Bloque en el editor**: post con el bloque "Jeroglíficos egipcios (MdC)" y la vista previa en vivo (ej.: `Htp-di-nswt` y `nTr:r`).
2. **Opciones del bloque**: panel lateral con tamaño, dirección (hrl), color y sombreado.
3. **Constructor de textos MdC**: Ajustes → Jeroglíficos Egipcios, con la paleta de signos y la vista previa (la antigua herramienta "Herramientas → Conversor MdC" se retiró del menú en 1.5.6; el conversor sigue accesible por URL directa `admin.php?page=ftorres-hiero-converter`).

## 6. Estado actual (preparación completada)

- **Auditoría de estándares WPCS (manual)**: sin salidas sin escapar, guardas `ABSPATH` en todos los ficheros, funciones con prefijo `ft_hiero_`, cadenas con i18n, sin funciones obsoletas ni SQL directo. (PHPCS no se pudo ejecutar localmente: faltan las extensiones PHP xmlwriter/simplexml; los revisores de WP.org lo ejecutan en su infraestructura.)
- **Capturas**: 3 listas en `screenshots/` (render frontend con cartuchos, editor Gutenberg, herramienta de conversión).
- **Demo pública**: post de ejemplo con todos los casos (signos, cuadraturas, cartuchos horizontales y verticales, rotaciones, shortcodes en textos y tabla completa de 316 signos) en https://www.ftorres.es/miscelanea/jeroglificos-egipcios-prueba-del-plugin-mdc/
- **Importación SVN**: script `svn-import.sh` listo (slugs/capturas/trunk/tags), versión 1.5.8.

Pendiente solo de la cuenta en wordpress.org y de la aprobación del envío inicial.

## 5. Recordatorios

- El nombre/slug del directorio será `egyptian-hieroglyphs` (verificar disponibilidad al enviar; si está ocupado, se puede cambiar el slug — el texto `egyptian-hieroglyphs/hiero` del bloque no depende del slug).
- La versión de `readme.txt` (Stable tag) debe coincidir con la del plugin: `1.5.8`.
- Actualizar `Tested up to` con la última versión de WordPress antes de cada envío.
- El zip actualizado se genera con `npm run plugin-zip` (ya incluye la 1.5.8 y los assets).
