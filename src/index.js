/**
 * External dependencies
 */
import forEach from 'lodash/forEach';
import PREFIX from './utils/prefix';

/**
 * Editor plugins
 */
import * as seo from './seo';

/**
 * WordPress dependencies
 */
const { registerPlugin } = wp.plugins;

/**
 * Registering a new plugin module for WordPress.
 */
export function registerPlugins() {
	forEach( [ seo ], ( plugin ) => {
		if ( ! plugin ) {
			return;
		}

		const { name, settings } = plugin;

		registerPlugin( `${ PREFIX }-${ name }`, {
			...settings,
		} );
	} );
}
registerPlugins();
