document.addEventListener('DOMContentLoaded', function() {
    alert('related-filters.js');
const { algoliasearch, instantsearch } = window;


const searchClient = algoliasearch(algoliaSearchData.app_id, algoliaSearchData.api_key);
const algoliaTemplate = document.querySelector('.widget-filter-loop');
const indexPostType = algoliaTemplate.getAttribute('data-post-type');
const indexPrefix = algoliaSearchData.index_prefix;
const indexHandle = indexPrefix + '_' + indexPostType;
const selectedFacet = algoliaTemplate.getAttribute('data-facet');
const limitTo = algoliaTemplate.getAttribute('data-limit');



const search = instantsearch({
  indexName: indexHandle,
  searchClient,
  future: { preserveSharedStateOnUnmount: true }
});


search.start();