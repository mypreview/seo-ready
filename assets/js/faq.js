( function ( wp, lodash ) {
	'use strict';

	// Check if the wp object exists.
	if ( ! wp ) {
		return;
	}

	const { times, noop } = lodash;
	const { createElement: el } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { ButtonBlockAppender, useBlockProps, useInnerBlocksProps } = wp.blockEditor;
	const { useSelect } = wp.data;
	const { __ } = wp.i18n;

	registerBlockType( 'seo-ready/faq', {
		apiVersion: 2,
		title: __( 'FAQ', 'seo-ready' ),
		description: __( 'This block displays a list of frequently asked questions.', 'seo-ready' ),
		keywords: [ 'faq', 'question', 'answer' ],
		icon: 'editor-help',
		attributes: {},
		category: 'design',
		supports: {
			align: [ 'full', 'wide' ],
			multiple: false,
			html: false,
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
		},
		edit: ( { clientId } ) => {
			const isSelectedBlockInRoot = useSelect(
				( select ) => {
					const { isBlockSelected, hasSelectedInnerBlock } = select( 'core/block-editor' );

					return isBlockSelected( clientId ) || hasSelectedInnerBlock( clientId, true );
				},
				[ clientId ]
			);
			const blockProps = useBlockProps( { className: 'is-layout-constrained' } );
			const { children } = useInnerBlocksProps(
				{},
				{
					allowedBlocks: [ 'core/details' ],
					renderAppender: noop,
					template: times( 2, () => [ 'core/details', {} ] ),
					templateInsertUpdatesSelection: true,
				}
			);

			return el(
				'div',
				blockProps,
				children,
				isSelectedBlockInRoot && el( ButtonBlockAppender, { rootClientId: clientId } )
			);
		},
		save: () => {
			const blockProps = useBlockProps.save( { id: 'faq', className: 'is-layout-constrained' } );
			const { children } = useInnerBlocksProps.save();

			return el( 'div', blockProps, children );
		},
	} );
} )( window.wp, window.lodash );
