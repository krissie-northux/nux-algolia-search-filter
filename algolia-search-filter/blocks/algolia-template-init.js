// blocks/algolia-template-init.js
document.addEventListener('DOMContentLoaded', function() {
    const algoliaTemplates = document.querySelectorAll('.algolia-template');
    algoliaTemplates.forEach(template => {
        const indexName = template.getAttribute('data-index-name');
        const templateContent = template.getAttribute('data-template');

        // Initialize Algolia with the indexName and templateContent
        // Example: Using Algolia InstantSearch.js
        const search = instantsearch({
            indexName: indexName,
            searchClient: algoliasearch('YourApplicationID', 'YourSearchOnlyAPIKey'),
        });

        search.addWidgets([
            instantsearch.widgets.searchBox({
                container: template,
                templates: {
                    default: templateContent,
                },
            }),
            // Add other widgets as needed
        ]);

        search.start();
    });
});