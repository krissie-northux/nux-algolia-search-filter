// blocks/algolia-template-block.js
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

registerBlockType('algolia-search-filter/algolia-template', {
    title: 'Algolia Template',
    icon: 'search',
    category: 'widgets',
    attributes: {
        indexName: {
            type: 'string',
            default: ''
        },
        template: {
            type: 'string',
            default: ''
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const { indexName, template } = attributes;

        return (
            <div>
                <InspectorControls>
                    <PanelBody title="Algolia Settings">
                        <TextControl
                            label="Index Name"
                            value={indexName}
                            onChange={(value) => setAttributes({ indexName: value })}
                        />
                        <TextControl
                            label="Template"
                            value={template}
                            onChange={(value) => setAttributes({ template: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div>
                    <h3>Algolia Template Block</h3>
                    <p>Index Name: {indexName}</p>
                    <p>Template: {template}</p>
                </div>
            </div>
        );
    },
    save: ({ attributes }) => {
        const { indexName, template } = attributes;
        return (
            <div
                className="algolia-template"
                data-index-name={indexName}
                data-template={template}
            ></div>
        );
    }
});