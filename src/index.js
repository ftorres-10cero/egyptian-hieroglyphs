/**
 * Registro del bloque "Jeroglíficos egipcios (MdC)".
 */
import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import './index.scss';
import Edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save,
} );
