/**
 * External dependencies
 */
import { isPlainObject, escape, split, toUpper, slice, join } from 'lodash';
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
const { PanelBody, ExternalLink, ToggleControl, TextControl, TextareaControl } = wp.components;

export default compose(
	applyWithSelect,
	applyWithDispatch
)(
	class Render extends Component {
		state = {
			defaults: {
				title: '',
				keywords: '',
				description: '',
				canonical: '',
				noindex: false,
				nofollow: false,
			},
		};

		render() {
			const { defaults } = this.state;
			const { postType, getMeta, setMeta } = this.props;
			const { seo_ready: seo } = getMeta;
			const metas = isPlainObject( seo ) ? seo : defaults;
			const { title, keywords, description, canonical, noindex, nofollow } = metas;
			const target = sprintf( '%1$s-%2$s-settings', PREFIX, postType );
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
									/* translators: %s: Post type name. */
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
								value={ title }
								onChange={ ( value ) =>
									setMeta( 'seo_ready', {
										...metas,
										title: escape( value ),
									} )
								}
								label={ __( 'Title', 'seo-ready' ) }
								help={
									/* translators: %s: Post type name. */ sprintf(
										__( 'Alternative title of this %s for search engines to index.', 'seo-ready' ),
										postType
									)
								}
							/>
							<TextControl
								type="text"
								value={ keywords }
								label={ __( 'Keywords', 'seo-ready' ) }
								onChange={ ( value ) =>
									setMeta( 'seo_ready', {
										...metas,
										keywords: escape( join( split( value, /[ ,]+/gi ), ',' ) ),
									} )
								}
								help={
									/* translators: %s: Post type name. */ sprintf(
										__(
											'Describe the content of this %s shortly by entering keywords written in lower case, and separated with a comma.',
											'seo-ready'
										),
										postType
									)
								}
							/>
							<TextareaControl
								value={ description }
								label={ __( 'Description', 'seo-ready' ) }
								onChange={ ( value ) =>
									setMeta( 'seo_ready', {
										...metas,
										description: escape( value ),
									} )
								}
								help={
									/* translators: %s: Post type name. */ sprintf(
										__(
											'Specify a short description text which should describe the content of this %s for search engines.',
											'seo-ready'
										),
										postType
									)
								}
							/>
							<TextControl
								type="url"
								value={ canonical }
								label={ __( 'Canonical link', 'seo-ready' ) }
								onChange={ ( value ) =>
									setMeta( 'seo_ready', {
										...metas,
										canonical: escape( value ),
									} )
								}
								help={
									/* translators: %s: Post type name. */ sprintf(
										__(
											'Indicates to search engines that a master copy of this %s exists, and avoids duplicate content index.',
											'seo-ready'
										),
										postType
									)
								}
							/>
							<ToggleControl
								checked={ !! noindex }
								onChange={ () =>
									setMeta( 'seo_ready', {
										...metas,
										noindex: ! noindex,
									} )
								}
								label={
									/* translators: %s: Post type name. */ sprintf(
										__( 'Do not show this %s in search results?', 'seo-ready' ),
										postType
									)
								}
							/>
							<ToggleControl
								checked={ !! nofollow }
								onChange={ () =>
									setMeta( 'seo_ready', {
										...metas,
										nofollow: ! nofollow,
									} )
								}
								label={
									/* translators: %s: Post type name. */ sprintf(
										__( 'Do not follow the links on this %s?', 'seo-ready' ),
										postType
									)
								}
							/>
						</PanelBody>
					</PluginSidebar>
				</Fragment>
			);
		}
	}
);
