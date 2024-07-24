/* eslint-disable camelcase, react-hooks/rules-of-hooks */
( function ( wp, lodash ) {
	'use strict';

	if ( ! wp ) {
		return;
	}

	const META_KEY = 'seo_ready';
	const { defaultTo, get, merge } = lodash;
	const { createElement: el, Fragment, useCallback } = wp.element;
	const { useSelect, useDispatch } = wp.data;
	const { registerPlugin } = wp.plugins;
	const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
	const { MediaUpload } = wp.blockEditor;
	const { SVG, Path } = wp.primitives;
	const {
		Button,
		ExternalLink,
		PanelBody,
		RangeControl,
		SelectControl,
		TabPanel,
		TextControl,
		TextareaControl,
		ToggleControl,
	} = wp.components;
	const { __, sprintf } = wp.i18n;

	registerPlugin( 'seo-ready', {
		icon: el(
			SVG,
			{ width: 24, height: 24, viewBox: '0 0 19 6', fill: 'none' },
			el( 'rect', { x: '0.5', width: '3', height: '2' } ),
			el( 'rect', { x: '15.5', y: '4', width: '3', height: '2' } ),
			el( Path, { d: 'M4 0H5C5.55228 0 6 0.447715 6 1V6H5C4.44772 6 4 5.55228 4 5V0Z' } ),
			el( Path, {
				d: 'M8.5 0H9.5C10.0523 0 10.5 0.447715 10.5 1V6H9.5C8.94772 6 8.5 5.55228 8.5 5V0Z',
			} ),
			el( Path, {
				d: 'M13 0H14C14.5523 0 15 0.447715 15 1V6H14C13.4477 6 13 5.55228 13 5V0Z',
			} ),
			el( Path, { d: 'M8 3.82843L6.5 5.32843L6.5 2.5L8 1L8 3.82843Z' } ),
			el( Path, { d: 'M12.5 3.82843L11 5.32843L11 2.5L12.5 1L12.5 3.82843Z' } )
		),
		render: () => {
			// Leave early if we're not on a post type edit screen.
			if ( ! wp.data.select( 'core/editor' ).getCurrentPostType() ) {
				return;
			}

			const { postId, postMeta, postType, postTypeLabel } = useSelect( ( select ) => {
				const { getCurrentPostId, getCurrentPostType, getEditedPostAttribute, getPostTypeLabel } =
					select( 'core/editor' );

				return {
					postId: getCurrentPostId(),
					postMeta: getEditedPostAttribute( 'meta' ),
					postType: getCurrentPostType(),
					postTypeLabel: getPostTypeLabel(),
				};
			} );

			const metaValue = defaultTo( get( postMeta, META_KEY ), postMeta );
			const { editEntityRecord } = useDispatch( 'core' );
			const setMeta = useCallback(
				( key, value ) => {
					editEntityRecord( 'postType', postType, postId, {
						meta: { [ META_KEY ]: merge( {}, metaValue, { [ key ]: value } ) },
					} );
				},
				[ metaValue ]
			);
			const {
				title,
				keywords,
				description,
				canonical,
				redirect,
				redirect_delay,
				noindex,
				nofollow,
				schema_type,
				schema_article_type,
				facebook_image,
				facebook_title,
				facebook_description,
				twitter_image,
				twitter_title,
				twitter_description,
			} = metaValue;

			const { facebookImg, twitterImg } = useSelect(
				( select ) => {
					const { getMedia } = select( 'core' );
					return {
						facebookImg: getMedia( facebook_image ),
						twitterImg: getMedia( twitter_image ),
					};
				},
				[ facebook_image, twitter_image ]
			);

			return el(
				Fragment,
				{},
				el(
					PluginSidebarMoreMenuItem,
					{
						target: 'seo-ready-settings',
					},
					__( 'SEO Ready', 'seo-ready' )
				),
				el(
					PluginSidebar,
					{
						name: 'seo-ready-settings',
						title: sprintf(
							/* translators: %s: Post type name */
							__( '%s SEO', 'seo-ready' ),
							postTypeLabel
						),
					},
					el(
						TabPanel,
						{
							tabs: [
								{ name: 'seo', title: __( 'SEO', 'seo-ready' ) },
								{ name: 'schema', title: __( 'Schema', 'seo-ready' ) },
								{ name: 'social', title: __( 'Social', 'seo-ready' ) },
							],
						},
						( { name } ) =>
							'seo' === name
								? el(
										PanelBody,
										{ initialOpen: true },
										el( TextControl, {
											autoComplete: 'off',
											help: /* translators: %s: Post type name. */ sprintf(
												__(
													'Alternative title of this "%s" for search engines to index.',
													'seo-ready'
												),
												postTypeLabel
											),
											label: __( 'Title', 'seo-ready' ),
											onChange: ( value ) => setMeta( 'title', value ),
											value: title || '',
										} ),
										el( TextareaControl, {
											help: /* translators: %s: Post type name. */ sprintf(
												__(
													'Describe the content of this "%s" shortly by entering keywords written in lower case, and separated with a comma.',
													'seo-ready'
												),
												postTypeLabel
											),
											label: __( 'Keywords', 'seo-ready' ),
											onChange: ( value ) => setMeta( 'keywords', value ),
											value: keywords || '',
										} ),
										el( TextareaControl, {
											help: /* translators: %s: Post type name. */ sprintf(
												__(
													'Specify a short description text which should describe the content of this "%s" for search engines.',
													'seo-ready'
												),
												postTypeLabel
											),
											label: __( 'Description', 'seo-ready' ),
											onChange: ( value ) => setMeta( 'description', value ),
											value: description || '',
										} ),
										el( TextControl, {
											type: 'url',
											autoComplete: 'off',
											help: /* translators: %s: Post type name. */ sprintf(
												__(
													'Indicates to search engines that a master copy of this "%s" exists, and avoids duplicate content index.',
													'seo-ready'
												),
												postTypeLabel
											),
											label: __( 'Canonical URL', 'seo-ready' ),
											onChange: ( value ) => setMeta( 'canonical', value ),
											value: canonical || '',
										} ),
										el( TextControl, {
											type: 'url',
											autoComplete: 'off',
											help: /* translators: %s: Post type name. */ sprintf(
												__(
													'Redirects visitors to the specified URL instead of the current URL of this "%s".',
													'seo-ready'
												),
												postTypeLabel
											),
											label: __( 'Redirect URL', 'seo-ready' ),
											onChange: ( value ) => setMeta( 'redirect', value ),
											value: redirect || '',
										} ),
										redirect &&
											el( RangeControl, {
												label: __( 'Redirect Delay', 'seo-ready' ),
												help: __(
													'The time in seconds before the redirect occurs.',
													'seo-ready'
												),
												value: redirect_delay || 0,
												onChange: ( value ) => setMeta( 'redirect_delay', value ),
												min: 0,
												max: 10,
												step: 1,
											} ),
										el( ToggleControl, {
											checked: Boolean( noindex ),
											label: /* translators: %s: Post type name. */ sprintf(
												__( 'Do not show this "%s" in search results?', 'seo-ready' ),
												postTypeLabel
											),
											onChange: () => setMeta( 'noindex', ! Boolean( noindex ) ),
										} ),
										el( ToggleControl, {
											checked: Boolean( nofollow ),
											label: /* translators: %s: Post type name. */ sprintf(
												__( 'Do not follow the links on this "%s"?', 'seo-ready' ),
												postTypeLabel
											),
											onChange: () => setMeta( 'nofollow', ! Boolean( nofollow ) ),
										} )
								  )
								: 'schema' === name
								? el(
										PanelBody,
										{ initialOpen: true },
										el( SelectControl, {
											label: __( 'Schema Type', 'seo-ready' ),
											options: [
												{
													label: __( 'Default for Pages (Web Page)', 'seo-ready' ),
													value: 'WebPage',
												},
												{
													label: __( 'Item Page', 'seo-ready' ),
													value: 'ItemPage',
												},
												{
													label: __( 'About Page', 'seo-ready' ),
													value: 'AboutPage',
												},
												{
													label: __( 'FAQ Page', 'seo-ready' ),
													value: 'FAQPage',
												},
												{
													label: __( 'QA Page', 'seo-ready' ),
													value: 'QAPage',
												},
												{
													label: __( 'Profile Page', 'seo-ready' ),
													value: 'ProfilePage',
												},
												{
													label: __( 'Contact Page', 'seo-ready' ),
													value: 'ContactPage',
												},
												{
													label: __( 'Collection Page', 'seo-ready' ),
													value: 'CollectionPage',
												},
												{
													label: __( 'Checkout Page', 'seo-ready' ),
													value: 'CheckoutPage',
												},
												{
													label: __( 'Search Results Page', 'seo-ready' ),
													value: 'SearchResultsPage',
												},
											],
											onChange: ( value ) => setMeta( 'schema_type', value ),
											value: schema_type || '',
										} ),
										el( SelectControl, {
											label: __( 'Article Type', 'seo-ready' ),
											options: [
												{
													label: __( 'Default for Pages (None)', 'seo-ready' ),
													value: '',
												},
												{
													label: __( 'Article', 'seo-ready' ),
													value: 'Article',
												},
												{
													label: __( 'Blog Post', 'seo-ready' ),
													value: 'BlogPosting',
												},
												{
													label: __( 'News Article', 'seo-ready' ),
													value: 'NewsArticle',
												},
												{
													label: __( 'Satirical Article', 'seo-ready' ),
													value: 'SatiricalArticle',
												},
												{
													label: __( 'Tech Article', 'seo-ready' ),
													value: 'TechArticle',
												},
												{
													label: __( 'Report', 'seo-ready' ),
													value: 'Report',
												},
											],
											onChange: ( value ) => setMeta( 'schema_article_type', value ),
											value: schema_article_type || '',
										} )
								  )
								: 'social' === name
								? el(
										Fragment,
										{},
										el(
											PanelBody,
											{ initialOpen: true, title: __( 'Facebook', 'seo-ready' ) },
											el(
												'div',
												{ className: 'editor-post-featured-image' },
												el(
													'div',
													{
														className: 'editor-post-featured-image__container',
														style: { marginBottom: 20 },
													},
													el( MediaUpload, {
														multiple: false,
														disableDropZone: true,
														allowedTypes: [ 'image' ],
														onSelect: ( { id } ) => setMeta( 'facebook_image', id ),
														value: facebook_image || '',
														render: ( { open } ) =>
															facebookImg
																? el(
																		Fragment,
																		{},
																		el( 'img', {
																			onClick: open,
																			src: facebookImg.source_url,
																			alt: facebookImg.alt_text,
																			style: {
																				maxWidth: '100%',
																				cursor: 'pointer',
																			},
																		} ),
																		el(
																			Button,
																			{
																				isLink: true,
																				isSmall: true,
																				isDestructive: true,
																				style: { display: 'block' },
																				onClick: () =>
																					setMeta( 'facebook_image', 0 ),
																			},
																			__( 'Remove Facebook image', 'seo-ready' )
																		)
																  )
																: el(
																		Button,
																		{
																			onClick: open,
																			className:
																				'editor-post-featured-image__toggle',
																		},
																		__( 'Set Facebook image', 'seo-ready' )
																  ),
													} )
												)
											),
											el( TextControl, {
												autoComplete: 'off',
												help: /* translators: %s: Post type name. */ sprintf(
													__( 'Alternative title of this "%s" for Facebook.', 'seo-ready' ),
													postTypeLabel
												),
												label: __( 'Title', 'seo-ready' ),
												onChange: ( value ) => setMeta( 'facebook_title', value ),
												value: facebook_title || '',
											} ),
											el( TextareaControl, {
												help: /* translators: %s: Post type name. */ sprintf(
													__(
														'Describe the content of this "%s" shortly for Facebook.',
														'seo-ready'
													),
													postTypeLabel
												),
												label: __( 'Description', 'seo-ready' ),
												onChange: ( value ) => setMeta( 'facebook_description', value ),
												value: facebook_description || '',
											} )
										),
										el(
											PanelBody,
											{ initialOpen: true, title: __( 'Twitter', 'seo-ready' ) },
											el(
												'div',
												{ className: 'editor-post-featured-image' },
												el(
													'div',
													{
														className: 'editor-post-featured-image__container',
														style: { marginBottom: 20 },
													},
													el( MediaUpload, {
														multiple: false,
														disableDropZone: true,
														allowedTypes: [ 'image' ],
														onSelect: ( { id } ) => setMeta( 'twitter_image', id ),
														value: twitter_image || '',
														render: ( { open } ) =>
															twitterImg
																? el(
																		Fragment,
																		{},
																		el( 'img', {
																			onClick: open,
																			src: twitterImg.source_url,
																			alt: twitterImg.alt_text,
																			style: {
																				maxWidth: '100%',
																				cursor: 'pointer',
																			},
																		} ),
																		el(
																			Button,
																			{
																				isLink: true,
																				isSmall: true,
																				isDestructive: true,
																				style: { display: 'block' },
																				onClick: () =>
																					setMeta( 'twitter_image', 0 ),
																			},
																			__( 'Remove Twitter image', 'seo-ready' )
																		)
																  )
																: el(
																		Button,
																		{
																			onClick: open,
																			className:
																				'editor-post-featured-image__toggle',
																		},
																		__( 'Set Twitter image', 'seo-ready' )
																  ),
													} )
												)
											),
											el( TextControl, {
												autoComplete: 'off',
												help: /* translators: %s: Post type name. */ sprintf(
													__( 'Alternative title of this "%s" for Twitter.', 'seo-ready' ),
													postTypeLabel
												),
												label: __( 'Title', 'seo-ready' ),
												onChange: ( value ) => setMeta( 'twitter_title', value ),
												value: twitter_title || '',
											} ),
											el( TextareaControl, {
												help: /* translators: %s: Post type name. */ sprintf(
													__(
														'Describe the content of this "%s" shortly for Twitter.',
														'seo-ready'
													),
													postTypeLabel
												),
												label: __( 'Description', 'seo-ready' ),
												onChange: ( value ) => setMeta( 'twitter_description', value ),
												value: twitter_description || '',
											} )
										)
								  )
								: el( PanelBody, { initialOpen: true }, 'Other' )
					),
					el(
						PanelBody,
						{
							initialOpen: false,
							title: sprintf(
								/* translators: %s: Emoji. */
								__( '%s More', 'seo-ready' ),
								'⚡'
							),
						},
						el(
							'p',
							{},
							el( 'p', {}, el( 'strong', {}, __( 'Liked the idea behind this plugin?', 'seo-ready' ) ) ),
							el( Fragment, {}, __( 'Share your experience by leaving this plugin ', 'seo-ready' ) ),
							el(
								ExternalLink,
								{ href: 'https://wordpress.org/support/plugin/seo-ready/reviews/?filter=5#new-post' },
								/* translators: %s: Emoji. */
								sprintf( __( '5 stars %s if you like it.', 'seo-ready' ), '⭐⭐⭐⭐⭐' )
							)
						),
						el(
							'p',
							{},
							__( 'You can also make a small ', 'seo-ready' ),
							el(
								ExternalLink,
								{ href: 'https://www.buymeacoffee.com/mahdiyazdani' },
								__( 'donation', 'seo-ready' )
							),
							__( ' so I can continue maintaining and evolving my projects and new ones.', 'seo-ready' )
						)
					)
				)
			);
		},
	} );
} )( window.wp, window.lodash );
