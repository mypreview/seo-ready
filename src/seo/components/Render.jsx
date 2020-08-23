/**
 * External dependencies
 */
import { toUpper, slice, join } from 'lodash';
import applyWithSelect from './../utils/with-select';
import applyWithDispatch from './../utils/with-dispatch';
import PREFIX from './../../utils/prefix';

/**
 * WordPress dependencies
 */
const { __, sprintf } = wp.i18n;
const { compose } = wp.compose;
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
const { Fragment, Component } = wp.element;
const { URLInput } = wp.blockEditor;
const { PanelBody, ExternalLink, ToggleControl, TextControl, TextareaControl, BaseControl } = wp.components;

export default compose(
	applyWithSelect,
	applyWithDispatch
)(
	class Render extends Component {
		render() {
			const { postType, getMeta, setMeta } = this.props;
			const target = sprintf( '%s-%s-settings', PREFIX, postType );
			// Makes the first letter of the post-type name uppercase.
			const postTypeLbl = toUpper( postType.charAt( 0 ) ) + join( slice( postType, 1 ), '' );

			return (
				<Fragment>
					<PluginSidebarMoreMenuItem target={ target }>
						{ sprintf(
							/* translators: %s: Post type name */
							__( '%s SEO', 'seo-ready' ),
							postTypeLbl
						) }
					</PluginSidebarMoreMenuItem>
					<PluginSidebar
						name={ target }
						title={ sprintf(
							/* translators: %s: Post type name */
							__( '%s SEO', 'seo-ready' ),
							postTypeLbl
						) }
					>
						<PanelBody initialOpen={ true }>
							<p>
								{ sprintf(
									__(
										'The following controls will enable you to alter SEO settings of this particular %s.',
										'seo-ready'
									),
									postType
								) }
							</p>
							<ExternalLink href="https://wordpress.org/support/article/search-engine-optimization">
								{ __( 'Learn more about search engine optimization', 'external link', 'seo-ready' ) }
							</ExternalLink>
						</PanelBody>
						<PanelBody initialOpen={ true }>
							<TextControl
								type="text"
								label={ __( 'Title', 'seo-ready' ) }
								help={ sprintf(
									__( 'Alternative title of this %s for search engines to index.', 'seo-ready' ),
									postType
								) }
							/>
							<TextControl
								type="text"
								label={ __( 'Keywords', 'seo-ready' ) }
								help={ sprintf(
									__(
										'Describe the content of this %s shortly by entering keywords written in lower case, and separated with a comma.',
										'seo-ready'
									),
									postType
								) }
							/>
							<TextareaControl
								label={ __( 'Description', 'seo-ready' ) }
								help={ sprintf(
									__(
										'Specify a short description text which should describe the content of this %s for search engines.',
										'seo-ready'
									),
									postType
								) }
							/>
							<TextControl
								type="url"
								label={ __( 'Canonical link', 'seo-ready' ) }
								help={ sprintf(
									__(
										'Indicates to search engines that a master copy of this %s exists, and avoids duplicate content index.',
										'seo-ready'
									),
									postType
								) }
							/>
							<ToggleControl
								label={ sprintf(
									__( 'Do not show this %s in search results?', 'seo-ready' ),
									postType
								) }
							/>
							<ToggleControl
								label={ sprintf( __( 'Do not follow the links on this %s?', 'seo-ready' ), postType ) }
							/>
						</PanelBody>
					</PluginSidebar>
				</Fragment>
			);
		}
	}
);
