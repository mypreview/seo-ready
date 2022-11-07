/**
 * External dependencies
 */
import { Icon } from '@mypreview/unicorn-react-components';

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { Render } from './components';
import icons from './assets/icons.json';

/**
 * Registering a new plugin module for WordPress.
 */
registerPlugin( 'seo-ready', {
	/**
	 * @see    ./assets/icons.json
	 */
	icon: <Icon d={ icons?.plugin } />,

	/**
	 * @see    ./components/Render.js
	 */
	render: Render,
} );
