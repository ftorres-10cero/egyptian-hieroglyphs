/**
 * Guardado del bloque "Jeroglíficos egipcios (MdC)".
 *
 * El HTML guardado es estático: un contenedor con un `<span class="hierojax">`
 * por línea, con la codificación Unicode ya convertida. En el frontend,
 * hierojax convierte esos spans en SVG. No se necesita JavaScript del bloque
 * en la página pública: solo el runtime de hierojax.
 */
import { useBlockProps } from '@wordpress/block-editor';

// Fallback si el bloque se guarda sin valores explícitos
// (los valores por defecto del plugin se aplican en el editor).
const FALLBACK_FONT_SIZE = 36;

export default function save( { attributes } ) {
	const {
		unicode,
		fontSize,
		textAlign,
		color,
		dir,
		sep,
		shade,
		linesize,
		separated,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'ftorres-hiero',
		style: { textAlign },
	} );

	const lines = ( unicode || '' )
		.split( '\n' )
		.filter( ( line ) => line.trim() !== '' );

	const spanProps = {
		className: 'hierojax',
		'data-type': 'svg',
		...( dir === 'hrl' ? { 'data-dir': 'hrl' } : {} ),
		'data-sep': String( sep ?? 0.1 ),
		'data-linesize': String( linesize ?? 1 ),
		'data-shadepattern': shade ?? 'uniform',
		...( separated ? { 'data-separated': 'true' } : {} ),
		style: {
			fontSize: `${ fontSize ?? FALLBACK_FONT_SIZE }px`,
			...( color ? { color } : {} ),
		},
	};

	return (
		<div { ...blockProps }>
			{ lines.map( ( line, i ) => (
				<span key={ i } { ...spanProps }>
					{ line }
				</span>
			) ) }
		</div>
	);
}
