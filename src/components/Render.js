/**
 * External dependencies
 */
import { PanelUpsell } from '@mypreview/unicorn-js-upsell';
import { useGetPostMeta, useUpdatePostMeta } from '@mypreview/unicorn-react-hooks';
import { split, join } from 'lodash';

/**
 * WordPress dependencies
 */
import { ExternalLink, PanelBody, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { PluginSidebar } from '@wordpress/edit-post';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Constants from '../constants';

/**
 * Registers new controls within the editor sidebar panel.
 *
 * @return    {JSX.Element}    The component to be rendered.
 */
function Render() {
	const {
		metaValue,
		postId,
		postType: { key: postType, label: postTypeLabel },
	} = useGetPostMeta( Constants.META_KEY );
	const setMeta = useUpdatePostMeta( Constants.META_KEY, metaValue, postId, postType );
	const { title, keywords, description, canonical, noindex, nofollow } = metaValue;

	return (
		<PluginSidebar
			name="seo-ready-settings"
			title={ sprintf(
				/* translators: %s: Post type name */
				__( '%s SEO', 'seo-ready' ),
				postTypeLabel
			) }
		>
			<PanelBody initialOpen={ true }>
				<p>
					{ sprintf(
						/* translators: %s: Post type name. */
						__( 'The following controls will enable you to alter SEO settings of this particular %s.', 'seo-ready' ),
						postTypeLabel
					) }
				</p>
				<ExternalLink href="https://wordpress.org/support/article/search-engine-optimization">
					{ __( 'Learn more about search engine optimization', 'external link', 'seo-ready' ) }
				</ExternalLink>
			</PanelBody>
			<PanelBody initialOpen>
				<TextControl
					autoComplete="off"
					help={
						/* translators: %s: Post type name. */ sprintf(
							__( 'Alternative title of this "%s" for search engines to index.', 'seo-ready' ),
							postTypeLabel
						)
					}
					label={ __( 'Title', 'seo-ready' ) }
					onChange={ ( value ) => setMeta( 'title', value ) }
					value={ title || '' }
				/>
				<TextControl
					autoComplete="off"
					help={
						/* translators: %s: Post type name. */ sprintf(
							__(
								'Describe the content of this "%s" shortly by entering keywords written in lower case, and separated with a comma.',
								'seo-ready'
							),
							postTypeLabel
						)
					}
					label={ __( 'Keywords', 'seo-ready' ) }
					onChange={ ( value ) => setMeta( 'keywords', join( split( value, /[ ,]+/gi ), ',' ), ',' ) }
					value={ keywords }
				/>
				<TextareaControl
					help={
						/* translators: %s: Post type name. */ sprintf(
							__( 'Specify a short description text which should describe the content of this "%s" for search engines.', 'seo-ready' ),
							postTypeLabel
						)
					}
					label={ __( 'Description', 'seo-ready' ) }
					onChange={ ( value ) => setMeta( 'description', value ) }
					value={ description }
				/>
				<TextControl
					autoComplete="off"
					help={
						/* translators: %s: Post type name. */ sprintf(
							__( 'Indicates to search engines that a master copy of this "%s" exists, and avoids duplicate content index.', 'seo-ready' ),
							postTypeLabel
						)
					}
					label={ __( 'Canonical link', 'seo-ready' ) }
					onChange={ ( value ) => setMeta( 'canonical', value ) }
					type="url"
					value={ canonical }
				/>
				<ToggleControl
					checked={ Boolean( noindex ) }
					label={ /* translators: %s: Post type name. */ sprintf( __( 'Do not show this "%s" in search results?', 'seo-ready' ), postTypeLabel ) }
					onChange={ () => setMeta( 'noindex', ! Boolean( noindex ) ) }
				/>
				<ToggleControl
					checked={ Boolean( nofollow ) }
					label={ /* translators: %s: Post type name. */ sprintf( __( 'Do not follow the links on this "%s"?', 'seo-ready' ), postTypeLabel ) }
					onChange={ () => setMeta( 'nofollow', ! Boolean( nofollow ) ) }
				/>
			</PanelBody>
			<PanelUpsell pluginSlug="seo-ready" />
		</PluginSidebar>
	);
}

export default Render;
