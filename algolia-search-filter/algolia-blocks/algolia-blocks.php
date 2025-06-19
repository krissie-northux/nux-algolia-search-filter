<?php
/**
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_algolia_blocks_block_init() {
	register_block_type( __DIR__ . '/build' );

	$post_types_options = get_option('algolia_filter_search_post_types', array());
	
	if ( ! is_array($post_types_options) ) {
		$post_types_options = array( $post_types_options );
	}
	$taxonomies_options = array();
	foreach($post_types_options as $post_type){
		$taxonomies = get_object_taxonomies($post_type);
		
		foreach($taxonomies as $taxonomy){
			
			if ( get_option('algolia_subpage_setting__' . $post_type . '__' . $taxonomy) ) {
				
				$terms = get_terms(array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				));
				$term_options = array();
				foreach($terms as $term){
					$term_options[] = ["slug" => $term->slug, "name" => $term->name];
				}
				$taxonomies_options[$post_type][] = ["slug" => $taxonomy, "name" => get_taxonomy($taxonomy)->labels->name, "terms" => $term_options];
			}
		}
	}

	wp_localize_script('create-block-algolia-blocks-editor-script', 'algoliaFilterSearch', array(
        'postTypes' => $post_types_options
    ));
	register_block_type( __DIR__ . '/build/algolia-hidden-filter' );
	register_block_type( __DIR__ . '/build/algolia-filter' );
	wp_localize_script('create-block-algolia-filter-editor-script', 'algoliaFilter', array(
		'taxonomies' => $taxonomies_options
    ));

	register_block_type( __DIR__ . '/build/algolia-results' );
	register_block_type( __DIR__ . '/build/algolia-search' );
	register_block_type( __DIR__ . '/build/algolia-clearfilters' );
	$post_types_options = get_option('algolia_filter_search_post_types', array());
	
	if ( ! is_array($post_types_options) ) {
		$post_types_options = array( $post_types_options );
	}
	$taxonomies_options = array();
	foreach($post_types_options as $post_type){
		$taxonomies = get_object_taxonomies($post_type);
		
		foreach($taxonomies as $taxonomy){
			
			if ( get_option('algolia_subpage_setting__' . $post_type . '__' . $taxonomy) ) {
				
				$terms = get_terms(array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				));
				$term_options = array();
				foreach($terms as $term){
					$term_options[] = ["slug" => $term->slug, "name" => $term->name];
				}
				$taxonomies_options[$post_type][] = ["slug" => $taxonomy, "name" => get_taxonomy($taxonomy)->labels->name, "terms" => $term_options];
			}
		}
	}
	wp_localize_script('create-block-algolia-hidden-filter-editor-script', 'algoliaFilter', array(
		'taxonomies' => $taxonomies_options,
		'postTypes' => $post_types_options
    ));
}
add_action( 'init', 'create_block_algolia_blocks_block_init' );

add_filter('the_content','enqueue_my_awesome_script_if_there_is_block');

function enqueue_my_awesome_script_if_there_is_block($content = ""){
  if(has_block('create-block/algolia-blocks')){
	
		//Be aware that for this to work, the load_in_footer MUST be set to true, as 
   		//the scripts for the header are already echoed out at this point
        wp_enqueue_script('algolia-search','https://cdn.jsdelivr.net/npm/algoliasearch@4.24.0/dist/algoliasearch-lite.umd.js',array(),'5.19.0',true);
		wp_enqueue_script('algolia-instantsearch','https://cdn.jsdelivr.net/npm/instantsearch.js@4.75.5',array(),'4.75.5',true);
		wp_enqueue_script('algolia-block-js', ALGOLIASF_CORE_URL .'algolia-blocks/instantsearch-app/src/app.js',array('algolia-instantsearch','algolia-search'),'1.0.0',true);
		//wp_enqueue_script('algolia-block-core-js', ALGOLIASF_CORE_URL .'algolia-blocks/instantsearch-app/dist/instantsearch-app.3464ddca.js',array('algolia-instantsearch','algolia-block-js'),'1.0.0',true);
		$algolia_app_id = get_option('algolia_app_id');
		$algolia_api_key = get_option('algolia_api_key');
		$index_prefix = get_option('index_prefix');
		wp_localize_script('algolia-block-js', 'algoliaSearchData', array(
			'app_id' => $algolia_app_id,
			'api_key' => $algolia_api_key,
			'index_prefix' => $index_prefix
		));

		wp_enqueue_style('algolia-ui-css', 'https://cdn.jsdelivr.net/npm/instantsearch.css@8.5.1/themes/reset-min.css', array(), '1.0.0');
     }
	 
   return $content;
}
