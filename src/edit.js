/**
 * Componente de edición del bloque "Jeroglíficos egipcios (MdC)".
 *
 * Muestra un área de texto con la notación MdC y una vista previa en vivo
 * (SVG) generada con HieroJax. Opciones: tamaño, color, dirección, separación,
 * sombreado y altura de línea. Permite editar directamente la codificación
 * Unicode avanzada cuando la conversión automática no es suficiente
 * (p. ej. cartuchos).
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	BaseControl,
	ColorPalette,
	Notice,
	PanelBody,
	RangeControl,
	SelectControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { getHierojax, mdcToUnicode } from './mdc-converter';

const SHADE_OPTIONS = [
	{ label: __( 'Uniforme', 'egyptian-hieroglyphs' ), value: 'uniform' },
	{ label: __( 'Rayado (hatching)', 'egyptian-hieroglyphs' ), value: 'hatching' },
];

const DIR_OPTIONS = [
	{ label: __( 'Izquierda → derecha', 'egyptian-hieroglyphs' ), value: 'ltr' },
	{
		label: __( 'Derecha → izquierda (jeroglífico)', 'egyptian-hieroglyphs' ),
		value: 'hrl',
	},
];

const ALIGN_OPTIONS = [
	{ label: __( 'Izquierda', 'egyptian-hieroglyphs' ), value: 'left' },
	{ label: __( 'Centro', 'egyptian-hieroglyphs' ), value: 'center' },
	{ label: __( 'Derecha', 'egyptian-hieroglyphs' ), value: 'right' },
];

const FALLBACK_DEFAULTS = { fontSize: 36, color: '' };

let siteDefaultsPromise = null;

/**
 * Valores por defecto del plugin (Ajustes -> Jeroglíficos (MdC)).
 *
 * @return {Promise<{fontSize: number, color: string}>}
 */
function getSiteDefaults() {
	if ( ! siteDefaultsPromise ) {
		siteDefaultsPromise = apiFetch( { path: '/wp/v2/settings' } )
			.then( ( settings ) => ( {
				fontSize:
					Number( settings.ft_hiero_default_font_size ) > 0
						? Number( settings.ft_hiero_default_font_size )
						: FALLBACK_DEFAULTS.fontSize,
				color:
					typeof settings.ft_hiero_default_color === 'string'
						? settings.ft_hiero_default_color
						: FALLBACK_DEFAULTS.color,
			} ) )
			.catch( () => ( { ...FALLBACK_DEFAULTS } ) );
	}
	return siteDefaultsPromise;
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		mdc,
		unicode,
		fontSize,
		textAlign,
		color,
		dir,
		sep,
		shade,
		linesize,
		separated,
		showSource,
	} = attributes;

	const previewRef = useRef( null );
	const [ errors, setErrors ] = useState( [] );
	const [ warnings, setWarnings ] = useState( [] );
	const [ showUnicode, setShowUnicode ] = useState( false );

	const blockProps = useBlockProps( {
		style: { textAlign },
	} );

	// Aplica los valores por defecto del plugin al insertar el bloque
	// (solo si el bloque no tiene un valor explícito guardado).
	useEffect( () => {
		const updates = {};
		if ( fontSize === undefined || fontSize === null ) {
			updates.fontSize = FALLBACK_DEFAULTS.fontSize;
		}
		if ( color === undefined || color === null ) {
			updates.color = FALLBACK_DEFAULTS.color;
		}
		if ( Object.keys( updates ).length ) {
			setAttributes( updates );
		}
		getSiteDefaults().then( ( defaults ) => {
			const withDefaults = {};
			if ( fontSize === undefined || fontSize === null ) {
				withDefaults.fontSize = defaults.fontSize;
			}
			if ( color === undefined || color === null ) {
				withDefaults.color = defaults.color;
			}
			if ( Object.keys( withDefaults ).length ) {
				setAttributes( withDefaults );
			}
		} );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	// Conversión automática MdC -> Unicode (con debounce) salvo en modo "fuente".
	useEffect( () => {
		if ( showSource ) {
			return;
		}
		if ( ! mdc || ! mdc.trim() ) {
			setAttributes( { unicode: '' } );
			setErrors( [] );
			setWarnings( [] );
			return;
		}
		const timeout = setTimeout( () => {
			const result = mdcToUnicode( mdc );
			setAttributes( { unicode: result.unicode } );
			setErrors( result.errors );
			setWarnings( result.warnings );
		}, 400 );
		return () => clearTimeout( timeout );
	}, [ mdc, showSource, setAttributes ] );

	// Renderiza la vista previa después de cada cambio de contenido u opciones.
	useEffect( () => {
		const hierojax = getHierojax();
		const el = previewRef.current;
		if ( hierojax && el ) {
			// Elimina SVG previos para evitar duplicados.
			el.querySelectorAll( '.hierojax svg' ).forEach( ( svg ) => svg.remove() );
			hierojax.processFragmentsIn( el );
		}
	}, [ unicode, fontSize, color, dir, sep, shade, linesize, separated, showSource ] );

	const lines = ( unicode || '' )
		.split( '\n' )
		.filter( ( line ) => line.trim() !== '' );

	const commonSpanProps = {
		className: 'hierojax',
		'data-type': 'svg',
		'data-dir': dir,
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
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Opciones de visualización', 'egyptian-hieroglyphs' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Tamaño de los signos', 'egyptian-hieroglyphs' ) }
						value={ fontSize ?? FALLBACK_DEFAULTS.fontSize }
						onChange={ ( value ) => setAttributes( { fontSize: value } ) }
						min={ 12 }
						max={ 96 }
						step={ 1 }
					/>
					<SelectControl
						label={ __( 'Alineación', 'egyptian-hieroglyphs' ) }
						value={ textAlign }
						options={ ALIGN_OPTIONS }
						onChange={ ( value ) => setAttributes( { textAlign: value } ) }
					/>
					<SelectControl
						label={ __( 'Dirección', 'egyptian-hieroglyphs' ) }
						help={ __(
							'El egipcio clásico se lee de derecha a izquierda.',
							'egyptian-hieroglyphs'
						) }
						value={ dir }
						options={ DIR_OPTIONS }
						onChange={ ( value ) => setAttributes( { dir: value } ) }
					/>
					<BaseControl
						label={ __( 'Color de los signos', 'egyptian-hieroglyphs' ) }
					>
						<ColorPalette
							value={ color || undefined }
							onChange={ ( value ) => setAttributes( { color: value || '' } ) }
						/>
					</BaseControl>
					<RangeControl
						label={ __( 'Separación entre signos', 'egyptian-hieroglyphs' ) }
						value={ sep }
						onChange={ ( value ) => setAttributes( { sep: value } ) }
						min={ 0 }
						max={ 0.3 }
						step={ 0.01 }
					/>
					<RangeControl
						label={ __( 'Altura de línea', 'egyptian-hieroglyphs' ) }
						value={ linesize }
						onChange={ ( value ) => setAttributes( { linesize: value } ) }
						min={ 0.5 }
						max={ 2 }
						step={ 0.05 }
					/>
					<SelectControl
						label={ __( 'Sombreado', 'egyptian-hieroglyphs' ) }
						value={ shade }
						options={ SHADE_OPTIONS }
						onChange={ ( value ) => setAttributes( { shade: value } ) }
					/>
					<ToggleControl
						label={ __( 'Permitir saltos de línea largos', 'egyptian-hieroglyphs' ) }
						checked={ separated }
						onChange={ ( value ) => setAttributes( { separated: value } ) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Avanzado', 'egyptian-hieroglyphs' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __(
							'Editar la codificación Unicode directamente',
							'egyptian-hieroglyphs'
						) }
						help={ __(
							'Útil para cartuchos u otros detalles que la conversión automática no conserva.',
							'egyptian-hieroglyphs'
						) }
						checked={ showSource }
						onChange={ ( value ) => setAttributes( { showSource: value } ) }
					/>
					<ToggleControl
						label={ __( 'Ver codificación Unicode', 'egyptian-hieroglyphs' ) }
						checked={ showUnicode }
						onChange={ setShowUnicode }
					/>
					{ showUnicode && (
						<TextareaControl
							className="ft-hiero-unicode-input"
							label={ __( 'Codificación Unicode generada', 'egyptian-hieroglyphs' ) }
							readOnly
							value={ unicode }
							rows={ 6 }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ showSource ? (
					<TextareaControl
						className="ft-hiero-unicode-input"
						label={ __( 'Codificación Unicode (avanzado)', 'egyptian-hieroglyphs' ) }
						help={ __(
							'Escribe la codificación jeroglífica Unicode directamente (caracteres U+13000+ y controles de cuadratura).',
							'egyptian-hieroglyphs'
						) }
						value={ unicode }
						onChange={ ( value ) => setAttributes( { unicode: value } ) }
						rows={ 5 }
					/>
				) : (
					<TextareaControl
						label={ __( 'Texto en notación MdC', 'egyptian-hieroglyphs' ) }
						help={ __(
							'Códigos de signos (nTr, ra, anx…) con separadores: ":" (vertical), "*" (horizontal), "-" (yuxtaposición). Cartuchos con "<" y ">" (ej.: <- mn-xpr-ra ->). Guía completa en Ajustes → Jeroglíficos Egipcios.',
							'egyptian-hieroglyphs'
						) }
						value={ mdc }
						onChange={ ( value ) => setAttributes( { mdc: value } ) }
						rows={ 5 }
					/>
				) }

				{ errors.length > 0 && (
					<Notice status="error" isDismissible={ false }>
						{ errors.map( ( error, i ) => (
							<div key={ i }>{ error }</div>
						) ) }
					</Notice>
				) }

				{ warnings.length > 0 && (
					<Notice status="warning" isDismissible={ false }>
						{ warnings.map( ( warning, i ) => (
							<div key={ i }>{ warning }</div>
						) ) }
					</Notice>
				) }

				<div ref={ previewRef } className="ftorres-hiero-preview">
					{ lines.length === 0 ? (
						<p className="ftorres-hiero-placeholder">
							{ __(
								'La vista previa aparecerá aquí. Escribe una frase en MdC arriba.',
								'egyptian-hieroglyphs'
							) }
						</p>
					) : (
						lines.map( ( line, i ) => (
							<span key={ i } { ...commonSpanProps }>
								{ line }
							</span>
						) )
					) }
				</div>
			</div>
		</>
	);
}
