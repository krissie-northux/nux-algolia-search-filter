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
export default function Edit({ attributes, setAttributes }) {
	
	const { selectedFilter, restrictBy, selectedPostType, restrictBySelected } = attributes;
		const postTypeOptions = algoliaFilter.postTypes.map(postType => ({
			label: postType,
			value: postType
		}));
	
	
		const options = algoliaFilter.taxonomies[selectedPostType].map(facet => ({
			label: facet.name,
			value: facet.slug
		}));
		var terms = [];
		if (restrictBy) {
			// find the terms for the selected restrictBy
	
	
			algoliaFilter.taxonomies[selectedPostType].map(facet => {
				console.log(facet);
				if ( facet.slug === restrictBy ) {
					
					facet.terms.map(term => {
						terms.push({
							label: term.name,
							value: term.id
						});
						console.log(terms);
					})
				}
			});
		}
		
	
		const findLabel = (value, options) => {
			const option = options.find(option => option.value === value);
			return option ? option.label : '';
		}
		return (
			<div { ...useBlockProps() }
			data-post-type={selectedPostType}
			data-facet={selectedFilter}
			data-limit={restrictBy}
			data-limitselected={restrictBySelected}
			>
				<InspectorControls>
					<PanelBody title="Algolia Settings">
	
						<SelectControl
							label="Post Type"
							value={selectedPostType}
							options={postTypeOptions}
							onChange={(value) => setAttributes({ selectedPostType: value })}
						/>
						
						<SelectControl
							label="Restrict By"
							value={restrictBy}
							options={options}
							onChange={(value) => setAttributes({ restrictBy: value })}
						/>
						{restrictBy && (
							<SelectControl
								label="Restrict By Selected"
								value={restrictBySelected}
								options={terms}
								onChange={(value) => setAttributes({ restrictBySelected: value })}
							/>
						)}
	
						
					</PanelBody>
				</InspectorControls>
				<div>
					<h3>Algolia Hidden Filter</h3>
					<p>Displays: {findLabel(selectedFilter, options)}, limited to {findLabel(restrictBySelected, terms)}</p>
				</div>
				<div className="widget-filter-loop" ></div>
			</div>
		);
}
