document.addEventListener('DOMContentLoaded', function() {

const { instantsearch, algoliasearch } = window;

const { history } = window.instantsearch.routers;

const searchClient = algoliasearch(algoliaSearchData.app_id, algoliaSearchData.api_key);
const algoliaTemplate = document.querySelector('.algolia-template');
const indexPostType = algoliaTemplate.getAttribute('data-selected-post-type');
const indexPrefix = algoliaSearchData.index_prefix;
const indexHandle = indexPrefix + '_' + indexPostType;
const usePagination = algoliaTemplate.getAttribute('data-pagination');

const hitsPerPage = algoliaTemplate.getAttribute('data-hits-per-page');

const hitTemplate = (hit, { html, components, sendEvent }) => html`
        <a href="${hit.permalink}">
        <img src="${hit.featured_image}" alt="${hit.post_title}" />
        <h3>${components.Highlight({hit, attribute: "post_title"})}</h3>
        <h4>${(video_speaker => {
            if (video_speaker) {
              return video_speaker.join(', ');
            } else {
              return "";
            }
        })(hit.video_speaker)}</h4>
        <span class="button--watch-now">Watch Now</span>
        </a>
    `;

const widgets = document.querySelectorAll('.wp-block-create-block-algolia-filter');
const searchBox = document.querySelector('.wp-block-create-block-algolia-search');
const clearFilters = document.querySelector('.wp-block-create-block-algolia-clearfilters');
var searchParameters = {};



const search = instantsearch({
  indexName: indexHandle,
  insights: true,
  searchClient,
  future: { preserveSharedStateOnUnmount: true },
  routing: {
    router: history(),
  }
});

if ( typeof document.querySelector('.wp-block-create-block-algolia-hidden-filter') !== 'undefined' && document.querySelector('.wp-block-create-block-algolia-hidden-filter') !== null ) {
  const restrictFacet = document.querySelector('.wp-block-create-block-algolia-hidden-filter').getAttribute('data-restrict-facet');
  const restrictSelected = document.querySelector('.wp-block-create-block-algolia-hidden-filter').getAttribute('data-restrict-selected');
  searchParameters = {

      filters: [restrictFacet+':'+restrictSelected]

  }
  console.log('searchParameters', searchParameters);
search.addWidgets([
  instantsearch.widgets.configure({
    hitsPerPage: hitsPerPage,
    facetFilters: searchParameters.filters
  }),
]);
}  else {
  console.log('searchParameters', searchParameters);
  search.addWidgets([
    instantsearch.widgets.configure({
      hitsPerPage: hitsPerPage
    }),
  ]);
}


if (searchBox) {
  search.addWidgets([
    instantsearch.widgets.searchBox({
      container: '#searchbox',
      placeholder: 'Search',
    }),
  ]);
}

if (clearFilters) {
  search.addWidgets([
    instantsearch.widgets.clearRefinements({
      container: '#clear-refinements',
      templates: {
        resetLabel({ hasRefinements }, { html }) {
          return html`<span>${hasRefinements ? 'Clear Filters' : 'No refinements'}</span>`;
        },
      },
    }),
   /*instantsearch.widgets.currentRefinements({
      container: '#current-refinements',
    }),*/
  ]);
}

switch(usePagination) {
  case 'off':
    search.addWidgets([
      instantsearch.widgets.hits({
        container: '#infinite-hits',
        templates: {item: hitTemplate}
      }),
    ]);
    break;
  case 'paginate':
    search.addWidgets([
      instantsearch.widgets.hits({
        container: '#infinite-hits',
        templates: {item: hitTemplate }
      }),
      instantsearch.widgets.pagination({
        container: '#pagination',
      }),
    ]);
    break;
  case 'loadMore':
    search.addWidgets([
      instantsearch.widgets.infiniteHits({
        container: '#infinite-hits',
        showPrevious: true,
        templates: {
          item: hitTemplate,
          showMoreText(data, { html }) {
            return html`<span class="button">Load More</span>`;
          },
          showPreviousText(data, { html }) {
            return html`<span class="button">Load Previous</span>`;
          }
        }
      }),
    ]);
    break;
  default:
    break;
}

widgets.forEach(widget => {
  const filterTarget = widget.getAttribute('data-facet');
  const filterType = widget.getAttribute('data-type');
  const container = widget.querySelector('.widget-filter');

  switch(filterType) {
    case 'refinementList':
      search.addWidgets([
        instantsearch.widgets.refinementList({
          container: container,
          attribute: filterTarget,
          searchable: true,
          searchableIsAlwaysActive: false,
          showMore: true,
          showMoreLimit: 200,
          searchablePlaceholder: 'Find...',
        })
      ]);
      break;
    case 'rangeSlider':
      search.addWidgets([
        instantsearch.widgets.rangeSlider({
          container: container,
          attribute: filterTarget,
        })
      ]);
      break;
    case 'menu':
      console.log('menu');
      search.addWidgets([
        instantsearch.widgets.menu({
          container: container,
          attribute: filterTarget,
          templates: {
            item(data, { html }) {
              
              const { label, count, url, cssClasses } = data;
              const uniqueClass = data.value.replace(/[^a-zA-Z0-9]/g, "").toLowerCase();
        
              return html`
                <a class="${cssClasses.link} icon--${uniqueClass}" href="${url}">
                  <span class="${cssClasses.label}">${label}</span>
                </a>
              `;
            },
          },
        })
      ]);
      
      break;
    default:
      break;
  }
  console.log('filterTarget', filterTarget, filterType);
});
/*
search.addWidgets([
  instantsearch.widgets.trendingItems({
    container: '#hits',
    facetName: 'video_event',
    facetValue: "CURRENT'23",
    templates: {
      item(recommendation, { html }) {
        return html`
          <h2>${recommendation.name}</h2>
          <p>${recommendation.description}</p>
        `;
      },
    },
  })
]);*/


search.start();

//create a mobile menu of the filters
if ( document.querySelector('.js-mobile-menu') ) {
  const mobileFilters = document.querySelector('.js-mobile-menu');
  const filterButton = document.createElement('button');
  const closeFilters = document.createElement('button');
  const filterHeading = document.createElement('h2');
  filterButton.classList.add('js-mobile-menu-button');
  closeFilters.classList.add('js-mobile-menu-close');
  filterHeading.textContent = 'Filters';
  filterButton.textContent = 'Filters';
  mobileFilters.prepend(filterButton);
  mobileFilters.prepend(filterHeading);
  mobileFilters.append(closeFilters);

  filterButton.addEventListener('click', () => {
    mobileFilters.classList.toggle('active');
  });
  closeFilters.addEventListener('click', () => {
    mobileFilters.classList.toggle('active');
  });
}
});