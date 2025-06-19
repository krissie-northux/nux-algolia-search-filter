/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InnerBlocks, useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { selectedPostType, pagination, hitsPerPage } = attributes;
	
	const options = algoliaFilterSearch.postTypes.map(postType => ({
		label: postType,
		value: postType
	}));

	const pageOptions = [
		{ label: "Off", value: "off" },
		{ label: "Paginate", value: "paginate" },
		{ label: "Load More", value: "loadMore" }
	];

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title="Algolia Settings">
					
					<SelectControl
						label="Select Post Type"
						value={selectedPostType}
						options={options}
						onChange={(value) => setAttributes({ selectedPostType: value })}
					/>
					<SelectControl
						label="Paginate Results"
						value={ pagination }
						options={ pageOptions }
						onChange={(value) => setAttributes({ pagination: value })}
					/>
					<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label="Posts per page"
							help="The number of posts you want to show on the page. If pagination is disabled, this will be the total number of posts shown."
							value={ hitsPerPage }
							onChange={(value) => setAttributes({ hitsPerPage: value })}
						/>
				</PanelBody>
			</InspectorControls>
			
			<div>
				<h3>Algolia Template Block</h3>
				<p>Displays: {selectedPostType}</p>
			</div>
			<div 
			className="algolia-template"
			data-selected-post-type={selectedPostType} 
			data-pagination={pagination}
			data-hits-per-page={hitsPerPage}
			>
				<div className="search-wrapper outer-container">
					<div className="search-panel">
					<div className="search-panel__filters">
					</div>
					<InnerBlocks   />
					
					</div>
				</div>
			</div>
		</div>
	);
}

