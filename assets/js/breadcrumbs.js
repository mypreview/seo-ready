( function ( wp, lodash ) {
	'use strict';

	// Check if the wp object exists.
	if ( ! wp ) {
		return;
	}

	const { map, reverse } = lodash;
	const { createElement: el, Fragment, useMemo } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { useSelect } = wp.data;
	const { Disabled, ExternalLink, PanelBody, TextControl, ToggleControl } = wp.components;
	const { decodeEntities } = wp.htmlEntities;
	const { SVG, Rect } = wp.primitives;
	const { __, sprintf } = wp.i18n;

	registerBlockType( 'seo-ready/breadcrumbs', {
		apiVersion: 2,
		title: __( 'Breadcrumbs', 'seo-ready' ),
		description: __(
			'This block displays trail of breadcrumb tracks to help visitors show exactly where they are on the website.',
			'seo-ready'
		),
		keywords: [ 'menu', 'navigation', 'path', 'trail' ],
		icon: el(
			SVG,
			{ width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none' },
			el( Rect, { x: '4', y: '10.5', width: '6', height: '3', rx: '1.5', fill: 'currentColor' } ),
			el( Rect, { x: '12', y: '10.5', width: '3', height: '3', rx: '1.5', fill: 'currentColor' } ),
			el( Rect, { x: '17', y: '10.5', width: '3', height: '3', rx: '1.5', fill: 'currentColor' } )
		),
		attributes: {
			delimiter: {
				type: 'string',
				default: '→',
			},
			hideCurrentPageTrail: {
				type: 'boolean',
				default: false,
			},
			hideLeadingDelimiter: {
				type: 'boolean',
				default: true,
			},
			hideSiteTitle: {
				type: 'boolean',
				default: false,
			},
			siteTitleOverride: {
				type: 'string',
				default: '',
			},
		},
		category: 'design',
		usesContext: [ 'postId', 'postType' ],
		supports: {
			align: [ 'full', 'wide' ],
			multiple: false,
			html: false,
			color: {
				background: true,
				text: true,
				link: true,
			},
			layout: {
				allowSwitching: false,
				allowInheriting: false,
				allowEditing: false,
				default: {
					type: 'flex',
					flexWrap: 'nowrap',
				},
			},
			spacing: {
				margin: [ 'top', 'bottom' ],
				padding: true,
			},
			typography: {
				fontSize: true,
				lineHeight: true,
				__experimentalFontFamily: true,
				__experimentalFontStyle: true,
				__experimentalFontWeight: true,
				__experimentalTextTransform: true,
				__experimentalDefaultControls: {
					fontSize: true,
				},
			},
		},
		edit: ( { attributes, context: { postId, postType }, setAttributes } ) => {
			const {
				delimiter,
				hideCurrentPageTrail,
				hideLeadingDelimiter,
				hideSiteTitle,
				siteTitleOverride,
				backgroundColor,
				style,
			} = attributes;
			const blockProps = useBlockProps( {
				className: 'is-layout-flex',
				style: {
					boxSizing: 'border-box',
					listStyleType: 'none',
					paddingLeft: 0,
					margin: 0,
					padding: backgroundColor || style?.color?.background ? '1.25em 2.375em' : 0,
				},
			} );

			const { siteTitle, categories, currentPost, parents } = useSelect(
				( select ) => {
					const { getEntityRecord, getEditedEntityRecord } = select( 'core' );
					const siteData = getEntityRecord( 'root', '__unstableBase' );
					const _currentPost = getEditedEntityRecord( 'postType', postType, postId );

					if ( ! _currentPost ) {
						return {
							siteTitle: decodeEntities( siteData?.name ),
							categories: [],
							currentPost: null,
							parents: [],
						};
					}

					const getParentEntities = ( initialId, entityType ) => {
						const parentEntities = [];
						let currentParentId = initialId;

						while ( currentParentId ) {
							const parent = getEntityRecord(
								entityType,
								entityType === 'postType' ? postType : 'category',
								currentParentId
							);
							if ( ! parent ) break;
							parentEntities.push( parent );
							currentParentId = parent.parent || null;
						}

						return parentEntities;
					};

					const parentCategories = getParentEntities( _currentPost?.categories?.[ 0 ], 'taxonomy' );
					const parentEntities = getParentEntities( _currentPost?.parent, 'postType' );

					return {
						siteTitle: decodeEntities( siteData?.name ),
						categories: parentCategories,
						currentPost: _currentPost,
						parents: reverse( parentEntities ),
					};
				},
				[ postId, postType ]
			);

			const trails = useMemo( () => {
				let _trails = [];

				if ( parents?.length ) {
					_trails = map( parents, ( parent ) => parent?.title?.rendered || ' ' );
				} else if ( categories?.length ) {
					_trails = map( categories, ( category ) => category?.name || ' ' );
				} else {
					_trails = [ __( 'Top-level page', 'seo-ready' ), __( 'Child page', 'seo-ready' ) ];
				}

				if ( ! hideSiteTitle && siteTitle ) {
					_trails.unshift( siteTitleOverride || siteTitle );
				}

				if ( ! hideCurrentPageTrail ) {
					_trails.push( currentPost?.title || __( 'Current page', 'seo-ready' ) );
				}

				return _trails;
			}, [ categories, parents, hideCurrentPageTrail, hideSiteTitle, siteTitle, siteTitleOverride ] );

			return el(
				Fragment,
				{},
				el(
					'ol',
					blockProps,
					map( trails, ( crumbTitle, index ) =>
						el(
							'li',
							{ key: index, className: 'wp-block-seo-ready-breadcrumbs__crumb' },
							delimiter &&
								index === 0 &&
								! hideSiteTitle &&
								! hideLeadingDelimiter &&
								el(
									'span',
									{
										className: 'wp-block-seo-ready-breadcrumbs__delimiter',
										style: { marginRight: 'var(--wp--style--block-gap, 0.5em)' },
									},
									delimiter
								),
							el(
								Disabled,
								{},
								el( 'a', { href: '#', onClick: ( event ) => event.preventDefault() }, crumbTitle ),
								delimiter &&
									index < trails.length - 1 &&
									el(
										'span',
										{
											className: 'wp-block-seo-ready-breadcrumbs__delimiter',
											style: { marginLeft: 'var(--wp--style--block-gap, 0.5em)' },
										},
										delimiter
									)
							)
						)
					)
				),
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ initialOpen: true, title: __( 'Display Settings', 'seo-ready' ) },
						el( TextControl, {
							autoComplete: 'off',
							autoCapitalize: 'none',
							label: __( 'Delimiter', 'seo-ready' ),
							onChange: ( value ) => setAttributes( { delimiter: value } ),
							placeholder: sprintf(
								/* translators: %s: Default delimiter. */ __( 'e.g. %s', 'seo-ready' ),
								'→'
							),
							value: delimiter || '',
						} ),
						delimiter &&
							el( ToggleControl, {
								checked: Boolean( hideLeadingDelimiter ),
								label: sprintf(
									/* translators: %s: Show or Hide. */ __( '%s leading delimiter?', 'seo-ready' ),
									hideLeadingDelimiter ? 'Show' : 'Hide'
								),
								onChange: () => setAttributes( { hideLeadingDelimiter: ! hideLeadingDelimiter } ),
							} ),
						el( ToggleControl, {
							checked: Boolean( hideCurrentPageTrail ),
							label: sprintf(
								/* translators: %s: Show or Hide. */
								__( '%s current page title?', 'seo-ready' ),
								hideCurrentPageTrail ? 'Show' : 'Hide'
							),
							onChange: () => setAttributes( { hideCurrentPageTrail: ! hideCurrentPageTrail } ),
						} ),
						el( ToggleControl, {
							checked: Boolean( hideSiteTitle ),
							label: sprintf(
								/* translators: %s: Show or Hide. */ __( '%s site title?', 'seo-ready' ),
								hideSiteTitle ? 'Show' : 'Hide'
							),
							onChange: () => setAttributes( { hideSiteTitle: ! hideSiteTitle } ),
						} ),
						! hideSiteTitle &&
							el( TextControl, {
								autoComplete: 'off',
								autoCapitalize: 'none',
								label: __( 'Site Title Override', 'seo-ready' ),
								onChange: ( value ) => setAttributes( { siteTitleOverride: value } ),
								placeholder: __( 'e.g. Home', 'seo-ready' ),
								value: siteTitleOverride || '',
							} )
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
		save: ( { attributes } ) => {
			const { backgroundColor, style } = attributes;
			const blockProps = useBlockProps.save( {
				id: 'breadcrumb',
				className: 'is-layout-flex',
				style: {
					boxSizing: 'border-box',
					listStyleType: 'none',
					paddingLeft: 0,
					margin: 0,
					padding: backgroundColor || style?.color?.background ? '1.25em 2.375em' : null,
				},
			} );

			return el( 'ol', blockProps );
		},
	} );
} )( window.wp, window.lodash );
