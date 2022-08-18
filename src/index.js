/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { Icon, Render } from './components';

/**
 * Registering a new plugin module for WordPress.
 */
registerPlugin( 'seo-ready', {
	/**
	 * @see    ./components/Icon.js
	 */
	icon: Icon,
	/**
	 * @see    ./components/Render.js
	 */
	render: Render,
} );
