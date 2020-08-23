/**
 * Internal dependencies & components
 */
import render from './components/Render.jsx';
import icons from '../utils/icons';

/**
 * Meta-data for registering editor plugin.
 */
const name = 'seo';

/**
 * Block settings
 */
const settings = {
	render,
	icon: icons.analytics,
};

export { name, settings };
