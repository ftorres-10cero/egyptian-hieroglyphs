/**
 * Guardado del bloque "Jeroglíficos egipcios (MdC)".
 *
 * El HTML guardado es estático: un contenedor con un `<span class="hierojax">`
 * por línea, con la codificación Unicode ya convertida. En el frontend,
 * hierojax convierte esos spans en SVG. No se necesita JavaScript del bloque
 * en la página pública: solo el runtime de hierojax.
 */
import { useBlockProps } from '@wordpress/block-editor';

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
		'data-sep': String( sep ),
		'data-linesize': String( linesize ),
		'data-shadepattern': shade,
		...( separated ? { 'data-separated': 'true' } : {} ),
		style: {
			fontSize: `${ fontSize }px`,
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
