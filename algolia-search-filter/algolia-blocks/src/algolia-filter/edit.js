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
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
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
export default function Edit({ attributes, setAttributes, context }) {
	const { filterType, filterTarget } = attributes;

	const selectedPostType = context['create-block/algolia-filter'];

	const options = algoliaFilter.taxonomies[selectedPostType].map(facet => ({
		label: facet.name,
		value: facet.slug
	}));
	console.log('options', options);

	const typeOptions = [
		{ label: "Checkboxes", value: "refinementList" },
		{ label: "Menu", value: "menu" },
		{ label: "Select", value: "menuSelect" },
	]

	const findLabel = (value) => {
		const option = options.find(option => option.value === value);
		return option ? option.label : '';
	}
	return (
		<div { ...useBlockProps() }
		data-facet={filterTarget}
		data-type={filterType}
		>
			<InspectorControls>
				<PanelBody title="Algolia Settings">
					
					<SelectControl
						label="Select Filter Type"
						value={filterType}
						options={typeOptions}
						onChange={(value) => setAttributes({ filterType: value })}
					/>

					<SelectControl
						label="Select Facet"
						value={filterTarget}
						options={options}
						onChange={(value) => setAttributes({ filterTarget: value })}
					/>
				</PanelBody>
			</InspectorControls>
			<div>
				<h3>Algolia Filter Block</h3>
				<p>Displays: {findLabel(filterTarget)}</p>
			</div>
			<div className="widget-filter"></div>
		</div>
	);
}
