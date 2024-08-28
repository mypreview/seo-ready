( function ( wp, lodash ) {
	'use strict';

	// Check if the wp object exists.
	if ( ! wp ) {
		return;
	}

	const { times, noop } = lodash;
	const { createElement: el } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { ButtonBlockAppender, InspectorControls, RichText, useBlockProps, useInnerBlocksProps } = wp.blockEditor;
	const { useSelect } = wp.data;
	const { SelectControl } = wp.components;
	const { __ } = wp.i18n;

	registerBlockType( 'seo-ready/faq', {
		apiVersion: 2,
		title: __( 'FAQ', 'seo-ready' ),
		description: __( 'This block displays a list of frequently asked questions.', 'seo-ready' ),
		keywords: [ 'faq', 'question', 'answer' ],
		icon: 'editor-help',
		attributes: {
			tagName: {
				type: 'string',
				default: 'h2',
				selector: 'summary',
			},
			heading: {
				type: 'string',
				selector: 'heading',
			},
		},
		category: 'design',
		supports: {
			align: [ 'full', 'wide' ],
			multiple: false,
			html: false,
			color: {
				background: true,
				text: true,
				link: false,
			},
			layout: {
				allowSwitching: false,
				allowInheriting: false,
				allowEditing: false,
				default: {
					type: 'flex',
					flexWrap: 'nowrap',
					flexDirection: 'row',
				},
			},
			spacing: {
				margin: [ 'top', 'bottom' ],
				padding: true,
			},
		},
		edit: ( { attributes, clientId, setAttributes } ) => {
			const { heading, tagName } = attributes;
			const isSelectedBlockInRoot = useSelect(
				( select ) => {
					const { isBlockSelected, hasSelectedInnerBlock } = select( 'core/block-editor' );

					return isBlockSelected( clientId ) || hasSelectedInnerBlock( clientId, true );
				},
				[ clientId ]
			);
			const blockProps = useBlockProps( { className: 'is-layout-flow' } );
			const { children } = useInnerBlocksProps(
				{},
				{
					allowedBlocks: [ 'core/details' ],
					renderAppender: noop,
					template: times( 2, () => [ 'core/details', {} ] ),
					templateInsertUpdatesSelection: true,
					__experimentalCaptureToolbars: true,
				}
			);

			return el(
				'div',
				blockProps,
				( isSelectedBlockInRoot || heading ) &&
					el(
						'div',
						{ className: 'wp-block-seo-ready-faq__heading' },
						el( RichText, {
							identifier: 'heading',
							'aria-label': __( 'Write heading', 'seo-ready' ),
							placeholder: __( 'Write heading…', 'seo-ready' ),
							allowedFormats: [],
							withoutInteractiveFormatting: true,
							tagName,
							value: heading,
							onChange: ( newHeading ) => setAttributes( { heading: newHeading } ),
						} )
					),
				children,
				isSelectedBlockInRoot && el( ButtonBlockAppender, { rootClientId: clientId } ),
				el(
					InspectorControls,
					{ group: 'advanced' },
					el( SelectControl, {
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						label: __( 'Heading tag', 'seo-ready' ),
						help: __( 'Select the heading tag for the FAQ block.', 'seo-ready' ),
						options: [
							{ label: 'p', value: 'p' },
							{ label: 'H2', value: 'h2' },
							{ label: 'H3', value: 'h3' },
							{ label: 'H4', value: 'h4' },
						],
						value: tagName,
						onChange: ( newTagName ) => setAttributes( { tagName: newTagName } ),
					} )
				)
			);
		},
		save: ( { attributes } ) => {
			const { heading, tagName } = attributes;
			const blockProps = useBlockProps.save( { id: 'faq', className: 'is-layout-flow' } );
			const { children } = useInnerBlocksProps.save();

			return el(
				'div',
				blockProps,
				heading &&
					el(
						'div',
						{ className: 'wp-block-seo-ready-faq__heading' },
						el( RichText.Content, { tagName, value: heading } )
					),
				children
			);
		},
	} );
} )( window.wp, window.lodash );
