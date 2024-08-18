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
	const { PanelBody, TextControl, ToggleControl } = wp.components;
	const { decodeEntities } = wp.htmlEntities;
	const { SVG, Path } = wp.primitives;
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
			color: {
				gradients: false,
				link: true,
				__experimentalDefaultControls: {
					background: true,
					text: true,
				},
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
				__experimentalFontWeight: true,
				__experimentalFontStyle: true,
				__experimentalTextTransform: true,
				__experimentalTextDecoration: true,
				__experimentalLetterSpacing: true,
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
					padding: backgroundColor || style?.color?.background ? '1.25em 2.375em' : null,
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
									hideLeadingDelimiter ? 'Hide' : 'Show'
								),
								onChange: () => setAttributes( { hideLeadingDelimiter: ! hideLeadingDelimiter } ),
							} ),
						el( ToggleControl, {
							checked: Boolean( hideCurrentPageTrail ),
							label: sprintf(
								/* translators: %s: Show or Hide. */
								__( '%s current page title?', 'seo-ready' ),
								hideCurrentPageTrail ? 'Hide' : 'Show'
							),
							onChange: () => setAttributes( { hideCurrentPageTrail: ! hideCurrentPageTrail } ),
						} ),
						el( ToggleControl, {
							checked: Boolean( hideSiteTitle ),
							label: sprintf(
								/* translators: %s: Show or Hide. */ __( '%s site title?', 'seo-ready' ),
								hideSiteTitle ? 'Hide' : 'Show'
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
