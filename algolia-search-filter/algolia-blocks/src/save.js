/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const { selectedPostType, pagination, hitsPerPage } = attributes;
	return (
		<div { ...useBlockProps.save() }
			className="algolia-template"
			data-selected-post-type={selectedPostType} 
			data-pagination={pagination}
			data-hits-per-page={hitsPerPage}
			>
				
				<div class="search-wrapper outer-container">
					<div class="search-panel">
					<div class="search-panel__filters">
					</div>
					<InnerBlocks.Content />
					
					</div>
				</div>
		</div>
	);
}